<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class My_Twilio_API_Client {

    protected $account_sid;
    protected $auth_token;

    public function __construct() {
        $this->account_sid = get_option('iberonumeros_twilio_account_sid', '');
        $this->auth_token  = get_option('iberonumeros_twilio_auth_token', '');
    }

    /**
     * Buscar números disponibles según país, capacidades y criterio de búsqueda.
     * @param string $country Código del país (ej: 'US')
     * @param array $capabilities Ej: ['voice' => true, 'sms' => true, 'mms' => false, 'fax' => false]
     * @param string $search_criteria String a buscar en el número. Ej: '212'
     * @return array Lista de números disponibles o arreglo vacío si no hay resultados.
     */
    public function search_numbers($country, $capabilities = [], $search_criteria = '', $match_type = 'contains') {
        // Endpoint para buscar números locales
        $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$this->account_sid}/AvailablePhoneNumbers/{$country}/Local.json";
    
        $params = [];
        if (!empty($search_criteria)) {
            $params['Contains'] = $search_criteria;
        }
    
        // Capacidades:
        // Solo enviamos parámetros para las capacidades seleccionadas (true).
        // Si no se selecciona una capacidad, no enviamos nada para esa capacidad.
        if (isset($capabilities['voice']) && $capabilities['voice'] === true) {
            $params['VoiceEnabled'] = 'true';
        }
        if (isset($capabilities['sms']) && $capabilities['sms'] === true) {
            $params['SmsEnabled'] = 'true';
        }
        if (isset($capabilities['mms']) && $capabilities['mms'] === true) {
            $params['MmsEnabled'] = 'true';
        }
        if (isset($capabilities['fax']) && $capabilities['fax'] === true) {
            $params['FaxEnabled'] = 'true';
        }
    
        $response = $this->request('GET', $endpoint, $params);
    
        if (is_wp_error($response)) {
            return [];
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        if (isset($data['available_phone_numbers']) && is_array($data['available_phone_numbers'])) {
            foreach ($data['available_phone_numbers'] as &$number) {
                // Añadir el tipo de número manualmente
                $number['number_type'] = 'local'; // Asumimos que todos son locales en esta búsqueda.
            }
            return $data['available_phone_numbers'];
        }

    
        return [];
    }
    
    /*Checa el estado de la cuenta*/
    public function get_account_info() {
        if (empty($this->account_sid) || empty($this->auth_token)) {
            return new WP_Error('no_credentials', 'Twilio credentials not set.');
        }
        $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$this->account_sid}.json";
        $response = $this->request('GET', $endpoint, []);
        if (is_wp_error($response)) {
            return $response;
        }
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (isset($data['sid']) && $data['sid'] == $this->account_sid) {
            return $data; // Devuelve la info de la cuenta
        }
        return new WP_Error('connection_error', 'Failed to verify Twilio account.');
    }
    
    /**
     * Comprar un número telefónico.
     * @param string $phone_number Número en formato E.164 (ej: '+12125551234')
     * @return mixed Devuelve la respuesta decodificada si es exitosa, o WP_Error si hay error.
     */
    public function buy_number($phone_number) {
        $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$this->account_sid}/IncomingPhoneNumbers.json";

        $params = [
            'PhoneNumber' => $phone_number
        ];

        $response = $this->request('POST', $endpoint, $params);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['sid'])) {
            // Significa que se compró con éxito
            return $data;
        }

        return new WP_Error('twilio_purchase_error', 'Failed to purchase the phone number.', $data);
    }

    /**
     * Método genérico para hacer requests a la API de Twilio.
     * @param string $method 'GET' o 'POST'
     * @param string $endpoint URL de Twilio.
     * @param array $args Parámetros query (GET) o form-data (POST).
     */
    protected function request($method, $endpoint, $args = []) {
        if (empty($this->account_sid) || empty($this->auth_token)) {
            return new WP_Error('no_credentials', 'Twilio credentials not set.');
        }

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->account_sid . ':' . $this->auth_token)
        ];

        $options = [
            'headers' => $headers
        ];

        if ($method === 'GET') {
            $url = add_query_arg($args, $endpoint);
            $response = wp_remote_get($url, $options);
        } else {
            // POST
            $options['body'] = $args;
            $response = wp_remote_post($endpoint, $options);
        }

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code > 299) {
            return new WP_Error('twilio_error', 'Twilio API returned an error', [
                'status_code' => $code,
                'body' => wp_remote_retrieve_body($response)
            ]);
        }

        return $response;
    }
    
    public function get_countries() {
        // Intentamos usar un transient para cachear la lista por 24 horas
        $cached = get_transient('iberonumeros_countries_list');
        if ($cached !== false) {
            return $cached;
        }
    
        $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$this->account_sid}/AvailablePhoneNumbers.json";
        $response = $this->request('GET', $endpoint, []);
    
        if (is_wp_error($response)) {
            return $response;
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        if (!isset($data['countries']) || !is_array($data['countries'])) {
            return new WP_Error('no_countries', 'No countries data found.');
        }
    
        // Creamos un array asociativo code => name
        $countries = [];
        foreach ($data['countries'] as $c) {
            if (isset($c['country_code']) && isset($c['country'])) {
                $countries[$c['country_code']] = $c['country'];
            }
        }
    
        // Guardamos en el transient 24 horas
        set_transient('iberonumeros_countries_list', $countries, 24 * HOUR_IN_SECONDS);
    
        return $countries;
    }

/**
 * Obtener precios de números telefónicos desde Twilio.
 * @param string $country Código del país (ej: 'US').
 * @return array Arreglo con los precios organizados por tipo de número.
 */
public function get_pricing($country = 'US') {
    $endpoint = "https://pricing.twilio.com/v1/PhoneNumbers/Countries/$country";

    // Autorización con credenciales
    $args = [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($this->account_sid . ':' . $this->auth_token),
        ],
    ];

    // Realizar la solicitud a la API de Twilio
    $response = wp_remote_get($endpoint, $args);

    // Verificar si la respuesta tiene errores
    if (is_wp_error($response)) {
        #error_log('Error al conectar con Twilio: ' . $response->get_error_message());
        return [];
    }

    // Decodificar la respuesta
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Registrar la respuesta para depuración
    #error_log('Respuesta de Twilio: ' . print_r($data, true));

    // Verificar si la estructura de datos es correcta
    if (isset($data['phone_number_prices']) && is_array($data['phone_number_prices'])) {
        // Organizar los precios por tipo de número (number_type)
        $pricing_by_type = [];
        foreach ($data['phone_number_prices'] as $price) {
            if (isset($price['number_type'])) {
                $pricing_by_type[$price['number_type']] = [
                    'base_price' => $price['base_price'] ?? 0,
                    'current_price' => $price['current_price'] ?? 0,
                ];
                 // Registrar cada tipo de precio para depuración
                #error_log('Procesado: ' . print_r($pricing_by_type[$price['number_type']], true));
    
            }
        }
        return $pricing_by_type;
    }

    // Si no se encuentra la clave phone_number_prices, retornar un arreglo vacío
    #error_log('Estructura de datos inesperada de Twilio: ' . print_r($data, true));

    return [];
}


    
}

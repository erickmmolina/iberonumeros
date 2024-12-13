<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class My_Twilio_Frontend_Handler {

    public static function search_numbers() {
        check_ajax_referer('iberonumeros_nonce', 'nonce');
    
        $country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : 'US';
        $search_criteria = isset($_POST['search_criteria']) ? sanitize_text_field($_POST['search_criteria']) : '';
    
        $capabilities = [
            'voice' => isset($_POST['voice']) ? filter_var($_POST['voice'], FILTER_VALIDATE_BOOLEAN) : true,
            'sms'   => isset($_POST['sms']) ? filter_var($_POST['sms'], FILTER_VALIDATE_BOOLEAN) : true,
            'mms'   => isset($_POST['mms']) ? filter_var($_POST['mms'], FILTER_VALIDATE_BOOLEAN) : false,
            'fax'   => isset($_POST['fax']) ? filter_var($_POST['fax'], FILTER_VALIDATE_BOOLEAN) : false,
        ];
    
        $api = new My_Twilio_API_Client();
        $numbers = $api->search_numbers($country, $capabilities, $search_criteria);
        $pricing = $api->get_pricing($country);
    
        $price_increment = get_option('iberonumeros_price_increment', 0);
    
        foreach ($numbers as &$number) {
            $number_price = 0; // Precio por defecto en caso de error.
        
            // Verificar si el número tiene definido un "number_type".
            if (isset($number['number_type'])) {
                $number_type = $number['number_type'];
        
                // Buscar precio en los datos procesados de Twilio.
                if (isset($pricing[$number_type])) {
                    $number_price = $pricing[$number_type]['base_price'];
                }
            }
        
            // Aplicar el porcentaje configurado al precio.
            $number['price'] = $number_price * (1 + $price_increment / 100);
        }
    
        // Obtener el número de WhatsApp configurado en el backend
        $whatsapp_number = get_option('iberonumeros_whatsapp_number', '');
    
        wp_send_json_success([
            'numbers' => $numbers,
            'whatsapp_number' => $whatsapp_number, // Pasar el número de WhatsApp al frontend
        ]);
    }
    

    public static function buy_number() {
        check_ajax_referer('iberonumeros_nonce', 'nonce');

        $phone_number = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
        if (empty($phone_number)) {
            wp_send_json_error(['message' => 'No phone number provided']);
        }

        $api = new My_Twilio_API_Client();
        $result = $api->buy_number($phone_number);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        // Exitoso
        wp_send_json_success(['message' => 'Number purchased successfully!', 'data' => $result]);
    }
    public static function get_countries() {
        check_ajax_referer('iberonumeros_nonce', 'nonce');
    
        $api = new My_Twilio_API_Client();
        $countries = $api->get_countries();
    
        if (is_wp_error($countries)) {
            wp_send_json_error(['message' => $countries->get_error_message()]);
        }
    
        wp_send_json_success(['countries' => $countries]);
    }
    
}

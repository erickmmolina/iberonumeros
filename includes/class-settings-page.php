<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class My_Twilio_Settings_Page {

    public function init() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'admin_styles']);
    }

    public function admin_styles() {
        // Añadimos un poco de CSS al backend
        wp_enqueue_style('iberonumeros-admin-css', IBERONUMEROS_PLUGIN_URL . 'assets/css/admin-style.css', [], IBERONUMEROS_VERSION);
    }

    public function register_settings() {
        register_setting('iberonumeros_settings_group', 'iberonumeros_twilio_account_sid');
        register_setting('iberonumeros_settings_group', 'iberonumeros_twilio_auth_token');
        register_setting('iberonumeros_settings_group', 'iberonumeros_whatsapp_number'); // Número de WhatsApp
        register_setting('iberonumeros_settings_group', 'iberonumeros_price_increment'); // Incremento porcentual
    }

    public function add_menu_page() {
        add_options_page(
            __('Iberonumeros Settings', 'iberonumeros'),
            __('Iberonumeros', 'iberonumeros'),
            'manage_options',
            'iberonumeros-settings',
            [$this, 'render_settings_page']
        );
    }

    protected function check_connection() {
        $api = new My_Twilio_API_Client();
        $info = $api->get_account_info();
        if (is_wp_error($info)) {
            return ['status' => 'error', 'message' => $info->get_error_message()];
        }
        return ['status' => 'success', 'message' => __('Connected to Twilio!', 'iberonumeros')];
    }

    public function render_settings_page() {
        $connection = $this->check_connection();

        $price_increment = get_option('iberonumeros_price_increment', '20');
        ?>
        <div class="wrap iberonumeros-settings-wrap">
            <h1><?php esc_html_e('Iberonumeros Settings', 'iberonumeros'); ?></h1>
            
            <div class="iberonumeros-connection-status">
                <?php if ($connection['status'] === 'success'): ?>
                    <div class="iberonumeros-connection-success"><?php echo esc_html($connection['message']); ?></div>
                <?php else: ?>
                    <div class="iberonumeros-connection-error"><?php echo esc_html($connection['message']); ?></div>
                <?php endif; ?>
            </div>

            <form method="post" action="options.php" class="iberonumeros-form">
                <?php settings_fields('iberonumeros_settings_group'); ?>
                <?php do_settings_sections('iberonumeros_settings_group'); ?>
                
                <table class="form-table iberonumeros-form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Twilio Account SID', 'iberonumeros'); ?></th>
                        <td><input type="text" name="iberonumeros_twilio_account_sid" value="<?php echo esc_attr(get_option('iberonumeros_twilio_account_sid')); ?>" class="regular-text" /></td>
                    </tr>
                     
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Twilio Auth Token', 'iberonumeros'); ?></th>
                        <td><input type="text" name="iberonumeros_twilio_auth_token" value="<?php echo esc_attr(get_option('iberonumeros_twilio_auth_token')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Price Increment (%)', 'iberonumeros'); ?></th>
                        <td><input type="text" name="iberonumeros_price_increment" value="<?php echo esc_attr($price_increment); ?>" class="small-text" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Número de WhatsApp', 'iberonumeros'); ?></th>
                        <td><input type="text" name="iberonumeros_whatsapp_number" value="<?php echo esc_attr(get_option('iberonumeros_whatsapp_number', '')); ?>" class="regular-text" /></td>
                    </tr>
                </table>

                <p><?php esc_html_e('Note: Prices are fetched dynamically from Twilio. The "Price Increment" field allows you to add a percentage to the fetched price.', 'iberonumeros'); ?></p>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

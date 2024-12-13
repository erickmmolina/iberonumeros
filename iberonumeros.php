<?php
/**
 * Plugin Name: Iberonumeros
 * Plugin URI:  https://example.com
 * Description: Permite la compra de números telefónicos de Twilio desde el frontend mediante un widget de Elementor.
 * Version:     2.0.0
 * Author:      By Iberochat | Visita nuestra página (ibero.chat)
 * Author URI:  https://ibero.chat
 * License:     GPL2
 * Text Domain: iberonumeros
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'IBERONUMEROS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IBERONUMEROS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'IBERONUMEROS_VERSION', '1.0.3' );

require_once IBERONUMEROS_PLUGIN_DIR . 'includes/class-settings-page.php';
require_once IBERONUMEROS_PLUGIN_DIR . 'includes/class-twilio-api-client.php';
require_once IBERONUMEROS_PLUGIN_DIR . 'includes/class-frontend-handler.php';

// Crear la instancia de la página de ajustes
$settings_page = new My_Twilio_Settings_Page();
$settings_page->init();

// AJAX
add_action('wp_ajax_nopriv_iberonumeros_search_numbers', ['My_Twilio_Frontend_Handler', 'search_numbers']);
add_action('wp_ajax_iberonumeros_search_numbers', ['My_Twilio_Frontend_Handler', 'search_numbers']);

add_action('wp_ajax_nopriv_iberonumeros_buy_number', ['My_Twilio_Frontend_Handler', 'buy_number']);
add_action('wp_ajax_iberonumeros_buy_number', ['My_Twilio_Frontend_Handler', 'buy_number']);

add_action('wp_ajax_iberonumeros_get_countries', ['My_Twilio_Frontend_Handler', 'get_countries']);
add_action('wp_ajax_nopriv_iberonumeros_get_countries', ['My_Twilio_Frontend_Handler', 'get_countries']);


// Chequeo de Elementor
// (Podríamos omitir el desactivado automático para simplificar)
add_action('plugins_loaded', 'iberonumeros_check_elementor_dependency');

function iberonumeros_check_elementor_dependency() {
    if (!did_action('elementor/loaded')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('Iberonumeros requires Elementor to be installed and active.', 'iberonumeros'); ?></p>
            </div>
            <?php
        });
    }
}

add_action('elementor/widgets/widgets_registered', function() {
    if ( did_action('elementor/loaded') ) {
        require_once IBERONUMEROS_PLUGIN_DIR . 'includes/class-elementor-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new My_Twilio_Elementor_Widget() );
    }
});

// Enqueue frontend assets
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style( 'iberonumeros-frontend', IBERONUMEROS_PLUGIN_URL . 'assets/css/style.css', [], IBERONUMEROS_VERSION );
    wp_enqueue_script( 'iberonumeros-frontend', IBERONUMEROS_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], IBERONUMEROS_VERSION, true );
    wp_localize_script('iberonumeros-frontend', 'iberonumerosAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('iberonumeros_nonce')
    ]);
});

// Añadir enlace a la página de ajustes desde la página de plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=iberonumeros-settings') . '">' . __('Settings', 'iberonumeros') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

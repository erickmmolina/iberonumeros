<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class My_Twilio_Elementor_Widget extends Widget_Base {

    public function get_name() {
        return 'my_twilio_number_purchase_widget';
    }

    public function get_title() {
        return __('Iberonumeros', 'iberonumeros');
    }

    public function get_icon() {
        return 'eicon-phone';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        // Controles de contenido
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Settings', 'iberonumeros'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'default_country',
            [
                'label' => __('Default Country ISO Code', 'iberonumeros'),
                'type' => Controls_Manager::TEXT,
                'default' => 'CL',
                'description' => __('Enter the ISO country code for the default country (e.g. "US", "CL", "ES").', 'iberonumeros'),
            ]
        );

        $this->end_controls_section();

        // =========================
        // Sección de estilo: Contenedor principal
        // =========================
        $this->start_controls_section(
            'section_style_container',
            [
                'label' => __('Container', 'iberonumeros'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'container_background_color',
            [
                'label' => __('Background Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-widget' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_input_border',
                'selector' => '{{WRAPPER}} .iberonumeros-widget',
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'iberonumeros'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();

        // =========================
        // Sección de estilo: Campo de país
        // =========================
        $this->start_controls_section(
            'section_style_country',
            [
                'label' => __('Country Selector', 'iberonumeros'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'country_label_color',
            [
                'label' => __('Label Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-country-selector label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'country_label_typography',
                'selector' => '{{WRAPPER}} .iberonumeros-country-selector label',
            ]
        );

        $this->add_control(
            'country_input_color',
            [
                'label' => __('Input Text Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-country-input' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'country_input_background',
            [
                'label' => __('Input Background', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-country-input' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'country_input_border',
                'selector' => '{{WRAPPER}} .iberonumeros-country-input',
            ]
        );

        $this->add_responsive_control(
            'country_input_padding',
            [
                'label' => __('Input Padding', 'iberonumeros'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-country-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // =========================
        // Sección de estilo: Checkboxes
        // =========================
        $this->start_controls_section(
            'section_style_checkboxes',
            [
                'label' => __('Capabilities Checkboxes', 'iberonumeros'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'checkbox_label_color',
            [
                'label' => __('Label Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-search-form label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // =========================
        // Sección de estilo: Botón de búsqueda
        // =========================
        $this->start_controls_section(
            'section_style_search_button',
            [
                'label' => __('Search Button', 'iberonumeros'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'search_button_color',
            [
                'label' => __('Text Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-search-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'search_button_bg_color',
            [
                'label' => __('Background Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-search-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'search_button_border',
                'selector' => '{{WRAPPER}} .iberonumeros-search-btn',
            ]
        );

        $this->add_responsive_control(
            'search_button_padding',
            [
                'label' => __('Padding', 'iberonumeros'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-search-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // =========================
        // Sección de estilo: Mensajes (errores, etc.)
        // =========================
        $this->start_controls_section(
            'section_style_messages',
            [
                'label' => __('Messages', 'iberonumeros'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'message_text_color',
            [
                'label' => __('Text Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-messages .iberonumeros-message' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // =========================
        // Sección de estilo: Resultados (Tabla)
        // =========================
        $this->start_controls_section(
            'section_style_results_table',
            [
                'label' => __('Results Table', 'iberonumeros'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_header_bg_color',
            [
                'label' => __('Header Background Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-table thead th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_text_color',
            [
                'label' => __('Header Text Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-table thead th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_cell_text_color',
            [
                'label' => __('Cell Text Color', 'iberonumeros'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .iberonumeros-table tbody td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $default_country = !empty($settings['default_country']) ? $settings['default_country'] : 'US';
        ?>
        <div class="iberonumeros-widget">
            <form class="iberonumeros-search-form">
                <div class="iberonumeros-country-selector">
                    <label><?php esc_html_e('Country:', 'iberonumeros'); ?></label>
                    <div class="iberonumeros-country-dropdown-container">
                        <input type="text" class="iberonumeros-country-input" placeholder="<?php esc_attr_e('Search country...', 'iberonumeros'); ?>" autocomplete="off" />
                        <input type="hidden" name="country" value="<?php echo esc_attr($default_country); ?>">
                        <div class="iberonumeros-country-dropdown" style="display: none;"></div>
                    </div>
                </div>

                <label><input type="checkbox" name="voice" checked> <?php esc_html_e('Voice', 'iberonumeros'); ?></label>
                <label><input type="checkbox" name="sms" checked> <?php esc_html_e('SMS', 'iberonumeros'); ?></label>
                <label><input type="checkbox" name="mms"> <?php esc_html_e('MMS', 'iberonumeros'); ?></label>
                <label><input type="checkbox" name="fax"> <?php esc_html_e('Fax', 'iberonumeros'); ?></label>
        
                <input type="text" name="search_criteria" placeholder="<?php esc_attr_e('Search by digits or phrase', 'iberonumeros'); ?>">
        
                <button type="button" class="iberonumeros-search-btn"><?php esc_html_e('Search', 'iberonumeros'); ?></button>
            </form>
            <div class="iberonumeros-messages"></div>
            <div class="iberonumeros-results"></div>
        </div>
        <script>
        // Pasar el default_country a JS para seleccionar automáticamente
        var iberonumerosDefaultCountry = '<?php echo esc_js($default_country); ?>';
        </script>
        <?php
    }
}

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Invoice_Management_Settings')) {

    class Invoice_Management_Settings {

        private static $instance;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            add_action('admin_menu', array($this, 'add_settings_page'));
            add_action('admin_init', array($this, 'register_settings'));
        }

        public function add_settings_page() {
            add_options_page(
                __('Invoice Settings', 'invoice-management'), // Page title
                __('Invoice Settings', 'invoice-management'), // Menu title
                'manage_options',                             // Capability
                'invoice-management-settings',                // Menu slug
                array($this, 'render_settings_page_html')     // Callback function
            );
        }

        public function render_settings_page_html() {
            // Check user capabilities
            if (!current_user_can('manage_options')) {
                return;
            }

            // Admin notices (success, error)
            settings_errors('invoice_management_messages');

            // Include the settings page HTML
            // Ensure this path is correct. We are in admin/class-invoice-settings.php
            // So, partials/settings-page-html.php is one level up then into partials.
            $partial_path = plugin_dir_path(__FILE__) . 'partials/settings-page-html.php';
            if (file_exists($partial_path)) {
                include $partial_path;
            } else {
                echo '<div class="wrap"><h1>' . esc_html__('Invoice Settings', 'invoice-management') . '</h1>';
                echo '<p>Error: Settings page template not found.</p></div>';
            }
        }

        public function register_settings() {
            // Register a setting group
            register_setting(
                'invoice_management_options_group', // Option group
                'invoice_management_company_details', // Option name
                array($this, 'sanitize_company_details') // Sanitize callback
            );

            // Add settings section for Company Details
            add_settings_section(
                'invoice_management_company_section', // ID
                __('Company Details', 'invoice-management'), // Title
                array($this, 'company_section_callback'), // Callback
                'invoice-management-settings' // Page slug where to display
            );

            // Add settings fields
            add_settings_field(
                'company_name', // ID
                __('Company Name', 'invoice-management'), // Title
                array($this, 'render_text_field'), // Callback to render the field
                'invoice-management-settings', // Page
                'invoice_management_company_section', // Section
                array( // Arguments for the callback
                    'label_for' => 'company_name',
                    'option_name' => 'invoice_management_company_details',
                    'field_id' => 'company_name',
                    'description' => __('Enter your company name.', 'invoice-management')
                )
            );

            add_settings_field(
                'company_address',
                __('Company Address', 'invoice-management'),
                array($this, 'render_textarea_field'),
                'invoice-management-settings',
                'invoice_management_company_section',
                array(
                    'label_for' => 'company_address',
                    'option_name' => 'invoice_management_company_details',
                    'field_id' => 'company_address',
                    'description' => __('Enter your company address.', 'invoice-management')
                )
            );

            add_settings_field(
                'company_email',
                __('Company Email', 'invoice-management'),
                array($this, 'render_email_field'),
                'invoice-management-settings',
                'invoice_management_company_section',
                array(
                    'label_for' => 'company_email',
                    'option_name' => 'invoice_management_company_details',
                    'field_id' => 'company_email',
                    'description' => __('Enter your company email address.', 'invoice-management')
                )
            );

            add_settings_field(
                'company_phone',
                __('Company Phone', 'invoice-management'),
                array($this, 'render_text_field'),
                'invoice-management-settings',
                'invoice_management_company_section',
                array(
                    'label_for' => 'company_phone',
                    'option_name' => 'invoice_management_company_details',
                    'field_id' => 'company_phone',
                    'description' => __('Enter your company phone number.', 'invoice-management')
                )
            );

            add_settings_field(
                'company_logo',
                __('Company Logo URL', 'invoice-management'),
                array($this, 'render_text_field'), // Simple text field for URL for now
                'invoice-management-settings',
                'invoice_management_company_section',
                array(
                    'label_for' => 'company_logo',
                    'option_name' => 'invoice_management_company_details',
                    'field_id' => 'company_logo',
                    'description' => __('Enter the URL for your company logo.', 'invoice-management')
                    // Later, we can enhance this to a media uploader.
                )
            );

            add_settings_field(
                'company_vat_number',
                __('VAT/Tax Number', 'invoice-management'),
                array($this, 'render_text_field'),
                'invoice-management-settings',
                'invoice_management_company_section',
                array(
                    'label_for' => 'company_vat_number',
                    'option_name' => 'invoice_management_company_details',
                    'field_id' => 'company_vat_number',
                    'description' => __('Enter your company VAT or Tax ID.', 'invoice-management')
                )
            );
        }

        public function sanitize_company_details($input) {
            $sanitized_input = array();
            if (isset($input['company_name'])) {
                $sanitized_input['company_name'] = sanitize_text_field($input['company_name']);
            }
            if (isset($input['company_address'])) {
                $sanitized_input['company_address'] = sanitize_textarea_field($input['company_address']);
            }
            if (isset($input['company_email'])) {
                $sanitized_input['company_email'] = sanitize_email($input['company_email']);
            }
            if (isset($input['company_phone'])) {
                $sanitized_input['company_phone'] = sanitize_text_field($input['company_phone']);
            }
            if (isset($input['company_logo'])) {
                $sanitized_input['company_logo'] = esc_url_raw($input['company_logo']);
            }
            if (isset($input['company_vat_number'])) {
                $sanitized_input['company_vat_number'] = sanitize_text_field($input['company_vat_number']);
            }
            // Add more sanitization as needed
            return $sanitized_input;
        }

        public function company_section_callback() {
            echo '<p>' . esc_html__('Enter your company details below. These will be used on invoices and quotes.', 'invoice-management') . '</p>';
        }

        public function render_text_field($args) {
            $option_name = $args['option_name'];
            $field_id = $args['field_id'];
            $options = get_option($option_name);
            $value = isset($options[$field_id]) ? $options[$field_id] : '';
            echo "<input type='text' id='{$args['label_for']}' name='{$option_name}[{$field_id}]' value='" . esc_attr($value) . "' class='regular-text'>";
            if (!empty($args['description'])) {
                echo "<p class='description'>" . esc_html($args['description']) . "</p>";
            }
        }

        public function render_email_field($args) {
            $option_name = $args['option_name'];
            $field_id = $args['field_id'];
            $options = get_option($option_name);
            $value = isset($options[$field_id]) ? $options[$field_id] : '';
            echo "<input type='email' id='{$args['label_for']}' name='{$option_name}[{$field_id}]' value='" . esc_attr($value) . "' class='regular-text'>";
            if (!empty($args['description'])) {
                echo "<p class='description'>" . esc_html($args['description']) . "</p>";
            }
        }

        public function render_textarea_field($args) {
            $option_name = $args['option_name'];
            $field_id = $args['field_id'];
            $options = get_option($option_name);
            $value = isset($options[$field_id]) ? $options[$field_id] : '';
            echo "<textarea id='{$args['label_for']}' name='{$option_name}[{$field_id}]' rows='5' class='large-text'>" . esc_textarea($value) . "</textarea>";
            if (!empty($args['description'])) {
                echo "<p class='description'>" . esc_html($args['description']) . "</p>";
            }
        }
    }
}

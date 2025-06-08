<?php
/**
 * Plugin Name:       Invoice Management System
 * Plugin URI:        https://example.com/plugins/invoice-management-system/
 * Description:       A plugin to manage clients, quotes, and invoices.
 * Version:           1.0.0
 * Author:            Your Name / Company
 * Author URI:        https://example.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       invoice-management
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define Plugin Version
if (!defined('INVOICE_MANAGEMENT_VERSION')) {
    define('INVOICE_MANAGEMENT_VERSION', '1.0.0');
}

// Define Plugin Directory Path
if (!defined('INVOICE_MANAGEMENT_PLUGIN_DIR')) {
    define('INVOICE_MANAGEMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Define Plugin Directory URL
if (!defined('INVOICE_MANAGEMENT_PLUGIN_URL')) {
    define('INVOICE_MANAGEMENT_PLUGIN_URL', plugin_dir_url(__FILE__));
}

/**
 * Activation and Deactivation Hooks
 *
 * These files would contain functions that run when the plugin is activated or deactivated.
 * For example, setting up custom database tables on activation, or cleaning up on deactivation.
 */
// require_once INVOICE_MANAGEMENT_PLUGIN_DIR . 'includes/class-invoice-management-activator.php';
// require_once INVOICE_MANAGEMENT_PLUGIN_DIR . 'includes/class-invoice-management-deactivator.php';

// register_activation_hook(__FILE__, ['Invoice_Management_Activator', 'activate']);
// register_deactivation_hook(__FILE__, ['Invoice_Management_Deactivator', 'deactivate']);


/**
 * Placeholder for including other core plugin files.
 *
 * As the plugin grows, you would include files for:
 * - Admin area functionality (menus, settings pages, metaboxes)
 * - Client-side functionality (shortcodes, public views if any)
 * - Core logic (CPTs, taxonomies, helper functions)
 * - Classes for handling specific objects (Clients, Quotes, Invoices)
 */
// require_once INVOICE_MANAGEMENT_PLUGIN_DIR . 'admin/admin-init.php';
// require_once INVOICE_MANAGEMENT_PLUGIN_DIR . 'includes/core-functions.php';
// require_once INVOICE_MANAGEMENT_PLUGIN_DIR . 'public/public-init.php'; // Or 'client/client-init.php'


/**
 * Initialize the plugin.
 *
 * This function is hooked to 'plugins_loaded', which ensures that it runs
 * after all active plugins are loaded.
 */
function invoice_management_init() {
    // Load plugin text domain for internationalization.
    load_plugin_textdomain(
        'invoice-management',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );

    // Placeholder: Initialize main plugin class or load key components
    // For example:
    // if (class_exists('Invoice_Management_Main')) {
    //     Invoice_Management_Main::get_instance();
    // }

    // Or include files that need to run after plugins are loaded
    // require_once INVOICE_MANAGEMENT_PLUGIN_DIR . 'includes/class-invoice-management-cpts.php';
    // require_once INVOICE_MANAGEMENT_PLUGIN_DIR . 'includes/class-invoice-management-ajax.php';

    // For now, just a simple action to show it's loaded
    // add_action('admin_notices', function() {
    //     echo '<div class="notice notice-success is-dismissible"><p>Invoice Management System plugin initialized.</p></div>';
    // });
}
add_action('plugins_loaded', 'invoice_management_init');

/**
 * Placeholder for the main plugin class.
 *
 * Often, WordPress plugins are structured around a main class that acts as a singleton.
 * This helps to organize code and avoid naming conflicts.
 */
/*
if (!class_exists('Invoice_Management_Main')) {
    class Invoice_Management_Main {

        private static $instance;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // Initialize plugin components here
            // $this->load_dependencies();
            // $this->define_admin_hooks();
            // $this->define_public_hooks();
        }

        private function load_dependencies() {
            // Include files here
        }

        private function define_admin_hooks() {
            // Add admin-specific actions and filters
        }

        private function define_public_hooks() {
            // Add public-specific actions and filters
        }
    }
}
*/

// Example: How you might add a top-level admin menu (placeholder)
/*
function invoice_management_add_admin_menu() {
    add_menu_page(
        __('Invoice Management', 'invoice-management'), // Page title
        __('Invoicing', 'invoice-management'),          // Menu title
        'manage_options',                               // Capability
        'invoice-management',                           // Menu slug
        'invoice_management_main_page_callback',        // Callback function
        'dashicons-schedule',                           // Icon URL
        26                                              // Position
    );
}
// add_action('admin_menu', 'invoice_management_add_admin_menu');

function invoice_management_main_page_callback() {
    echo '<h1>' . esc_html__('Invoice Management System', 'invoice-management') . '</h1>';
    echo '<p>' . esc_html__('Welcome to the main page of the Invoice Management System. Further functionality will be built out here.', 'invoice-management') . '</p>';
}
*/

?>

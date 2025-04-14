<?php
/**
* Plugin Name: Catalog Mode & Product Inquiry for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/woocommerce-catalog-mode/
 * Description: Convert WooCommerce to catalog mode by hiding prices/cart buttons and adding product inquiry forms.
 * Version: 1.1.1
 * Author: Abubakr
 * Author URI: https://abucoder.in/
 * License: GPLv2 or later
  * Text Domain: catalog-mode-product-inquiry-for-woocommerce
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin path
define('WC_CATALOG_MODE_PATH', plugin_dir_path(__FILE__));
define('WC_CATALOG_MODE_URL', plugin_dir_url(__FILE__));

// Include dependencies
require_once WC_CATALOG_MODE_PATH . 'includes/inquiry-handler.php';
require_once WC_CATALOG_MODE_PATH . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'hooks.php';

// HPOS compatibility declaration (safe for all WC versions)
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Admin notice for HPOS compatibility
 */
add_action('admin_notices', 'wccm_hpos_admin_notice');
function wccm_hpos_admin_notice() {
    // Only show if WooCommerce is active
    if (!class_exists('WooCommerce')) return;
    
    // Check for HPOS using version-safe methods
    $hpos_enabled = false;
    if (function_exists('wc_get_container')) {
        $features_util = wc_get_container()->get('\Automattic\WooCommerce\Utilities\FeaturesUtil');
        if (method_exists($features_util, 'custom_orders_table_usage_is_enabled')) {
            $hpos_enabled = $features_util->custom_orders_table_usage_is_enabled();
        }
    } elseif (function_exists('wc_get_orders')) {
        // Fallback check for older versions
        $hpos_enabled = get_option('woocommerce_custom_orders_table_enabled') === 'yes';
    }

    if ($hpos_enabled) {
        echo '<div class="notice notice-info">';
        echo '<p><strong>' . esc_html__('WooCommerce Catalog Mode', 'woocommerce-catalog-mode') . '</strong>: ';
        echo esc_html__('Compatible with High-Performance Order Storage.', 'woocommerce-catalog-mode');
        echo '</p></div>';
    }
}

// Enqueue styles & scripts
function wc_catalog_mode_enqueue_scripts() {
    wp_enqueue_style(
        'wc-catalog-style',
        WC_CATALOG_MODE_URL . 'assets/style.css',
        [],
        filemtime(WC_CATALOG_MODE_PATH . 'assets/style.css')
    );

    wp_enqueue_script(
        'wc-catalog-script',
        WC_CATALOG_MODE_URL . 'assets/script.js',
        ['jquery'],
        filemtime(WC_CATALOG_MODE_PATH . 'assets/script.js'),
        true
    );

    wp_localize_script('wc-catalog-script', 'wcCatalogAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'wc_catalog_mode_enqueue_scripts');

/**
 * Complete catalog mode implementation
 */
function wc_catalog_mode_hide_add_to_cart_completely() {
    if (is_admin()) return;

    // Remove from all locations
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    
    // Disable purchasing at core level
    add_filter('woocommerce_is_purchasable', '__return_false', 999);
    add_filter('woocommerce_variation_is_purchasable', '__return_false', 999);
    
    // Hide quantity inputs
    add_filter('woocommerce_is_sold_individually', '__return_true', 999);
}
add_action('wp', 'wc_catalog_mode_hide_add_to_cart_completely', 999);
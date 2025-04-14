<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize all plugin hooks
 */
add_action('woocommerce_init', 'wccm_setup_hooks');
function wccm_setup_hooks() {
    // Register button display hooks
    add_action('woocommerce_single_product_summary', 'wccm_maybe_render_inquiry_button', 30);
    add_action('woocommerce_after_single_product_summary', 'wccm_maybe_render_after_summary', 15);
    
    // HPOS compatibility check (version-safe)
    if (function_exists('wc_get_container')) {
        $features_util = wc_get_container()->get('\Automattic\WooCommerce\Utilities\FeaturesUtil');
        if (method_exists($features_util, 'custom_orders_table_usage_is_enabled') && 
            $features_util->custom_orders_table_usage_is_enabled()) {
            // HPOS-specific adjustments
            add_filter('woocommerce_order_data_store', function($store) {
                if (class_exists('WC_Order_Data_Store_CPT')) {
                    return new WC_Order_Data_Store_CPT();
                }
                return $store;
            }, 999);
        }
    }
}

/**
 * Render inquiry button in product summary (after price)
 */
function wccm_maybe_render_inquiry_button() {
    if ('after_price' === get_option('wccm_button_location', 'after_price') && is_product()) {
        wccm_render_enquiry_button();
    }
}

/**
 * Render button after product summary
 */
function wccm_maybe_render_after_summary() {
    if ('after_summary' === get_option('wccm_button_location') && is_product()) {
        wccm_render_enquiry_button();
    }
}

/**
 * Render the enquiry button and modal
 */
function wccm_render_enquiry_button() {
    static $modal_rendered = false;
    
    $button_text = get_option('wc_catalog_mode_button_text', __('Enquiry Now', 'wc-catalog-mode'));
    $display_as_link = get_option('wccm_display_as_link', '0');

    // Render button/link
    if ('1' === $display_as_link) {
        echo '<a href="#inquiry-form" class="wccm-enquiry-link inquiry-trigger" role="button">' 
            . esc_html($button_text) 
            . '</a>';
    } else {
        echo '<button type="button" class="wccm-enquiry-btn button alt inquiry-trigger" aria-controls="inquiry-modal">' 
            . esc_html($button_text) 
            . '</button>';
    }

    // Render modal once per page
    if (!$modal_rendered) {
        $modal_path = WC_CATALOG_MODE_PATH . 'templates/inquiry-modal.php';
        if (file_exists($modal_path)) {
            include $modal_path;
            $modal_rendered = true;
        }
    }
}
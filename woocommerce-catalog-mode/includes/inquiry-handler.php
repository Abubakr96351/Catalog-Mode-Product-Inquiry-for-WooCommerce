<?php
if (!defined('ABSPATH')) {
    exit;
}

// AJAX handlers
add_action('wp_ajax_wc_catalog_mode_inquiry', 'wc_catalog_mode_handle_inquiry');
add_action('wp_ajax_nopriv_wc_catalog_mode_inquiry', 'wc_catalog_mode_handle_inquiry');

function wc_catalog_mode_handle_inquiry() {
    $name       = sanitize_text_field($_POST['name'] ?? '');
    $email      = sanitize_email($_POST['email'] ?? '');
    $phone      = sanitize_text_field($_POST['phone'] ?? '');
    $message    = sanitize_textarea_field($_POST['message'] ?? '');
    $product_id = intval($_POST['product_id'] ?? 0);
    $variation  = sanitize_text_field($_POST['selected_variation'] ?? '');
    $send_copy  = isset($_POST['send_copy']) && $_POST['send_copy'] == '1';

    // Settings
    $subject        = get_option('wccm_subject', 'Product Inquiry');
    $from_email     = get_option('wccm_from_email', get_option('admin_email'));
    $send_admin     = get_option('wccm_send_to_admin', 1);
    $send_author    = get_option('wccm_send_to_author', 0);

    if (empty($name) || empty($email) || empty($message) || !$product_id) {
        wp_send_json_error(['message' => 'All fields are required.']);
    }

    $product = get_post($product_id);
    if (!$product) {
        wp_send_json_error(['message' => 'Invalid product.']);
    }

    // Build email
    $product_title = $product->post_title;
    $product_link  = get_permalink($product_id);

    $body = "New Product Inquiry:\n\n";
    $body .= "Name: $name\n";
    $body .= "Email: $email\n";
    if (!empty($phone)) {
        $body .= "Phone: $phone\n";
    }
    $body .= "Message: $message\n";
    $body .= "Product: $product_title\n";
    $body .= "Link: $product_link\n";

    if (!empty($variation)) {
        $body .= "Selected Variations:\n$variation\n";
    }

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . sanitize_email($from_email)
    ];

    $mail_sent = false;

    // Send to admin if enabled
    if ($send_admin) {
        $mail_sent = wp_mail(get_option('admin_email'), $subject . ': ' . $product_title, $body, $headers);
    }

    // Send to product author if enabled
    if ($send_author) {
        $author_email = get_the_author_meta('user_email', $product->post_author);
        if ($author_email) {
            wp_mail($author_email, $subject . ': ' . $product_title, $body, $headers);
        }
    }

    // Send copy to user if requested
    if ($send_copy) {
        wp_mail($email, 'Copy of your product inquiry', $body, $headers);
    }

    if ($mail_sent) {
        wp_send_json_success(['message' => 'Your inquiry has been sent successfully.']);
    } else {
        wp_send_json_error(['message' => 'Failed to send inquiry email.']);
    }
}

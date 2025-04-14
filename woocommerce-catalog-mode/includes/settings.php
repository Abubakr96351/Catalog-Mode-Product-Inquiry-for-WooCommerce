<?php
if (!defined('ABSPATH')) exit;

// Register plugin settings
function wc_catalog_mode_register_settings() {
    add_option('wccm_subject', 'Product Inquiry');
    add_option('wccm_send_to_admin', 1);
    add_option('wccm_send_to_author', 0);
    add_option('wccm_from_email', get_option('admin_email'));
    add_option('wccm_show_copy_checkbox', 1);
    add_option('wccm_show_phone', 1);
    add_option('wccm_require_phone', 0);
    add_option('wccm_button_location', 'after_cart'); // after_cart or after_summary
    add_option('wccm_display_as_link', 0);

    // Update all register_setting() calls to include sanitization callbacks
// Register and sanitize all settings
register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_subject', 
    [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Product Inquiry'
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_send_to_admin', 
    [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 1
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_send_to_author', 
    [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 0
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_from_email', 
    [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default' => get_option('admin_email')
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_show_copy_checkbox', 
    [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 1
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_show_phone', 
    [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 1
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_require_phone', 
    [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 0
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_button_location', 
    [
        'type' => 'string',
        'sanitize_callback' => function($value) {
            return in_array($value, ['after_cart', 'after_summary']) ? $value : 'after_cart';
        },
        'default' => 'after_cart'
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wccm_display_as_link', 
    [
        'type' => 'integer',
        'sanitize_callback' => 'absint',
        'default' => 0
    ]
);

register_setting(
    'wc_catalog_mode_settings_group', 
    'wc_catalog_mode_button_text', 
    [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Request a Quote'
    ]
);

}
add_action('admin_init', 'wc_catalog_mode_register_settings');

// Add settings menu
function wc_catalog_mode_settings_menu() {
    add_options_page(
        'WC Catalog Mode Settings',
        'WC Catalog Mode',
        'manage_options',
        'wc-catalog-mode',
        'wc_catalog_mode_settings_page'
    );
}
add_action('admin_menu', 'wc_catalog_mode_settings_menu');

// Render settings page
function wc_catalog_mode_settings_page() {
?>
    <div class="wrap">
        <h1>WC Catalog Mode Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('wc_catalog_mode_settings_group'); ?>
            <h2>Email Settings</h2>
            <table class="form-table">
                <tr>
                    <th><label for="wccm_subject">Default Subject</label></th>
                    <td><input type="text" name="wccm_subject" value="<?php echo esc_attr(get_option('wccm_subject')); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="wccm_send_to_admin">Send to Admin</label></th>
                    <td><input type="checkbox" name="wccm_send_to_admin" value="1" <?php checked(1, get_option('wccm_send_to_admin'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wccm_send_to_author">Send to Product Author</label></th>
                    <td><input type="checkbox" name="wccm_send_to_author" value="1" <?php checked(1, get_option('wccm_send_to_author'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wccm_from_email">From Email</label></th>
                    <td><input type="email" name="wccm_from_email" value="<?php echo esc_attr(get_option('wccm_from_email')); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>Form Settings</h2>
            <table class="form-table">
                <tr>
                    <th><label for="wccm_show_copy_checkbox">Show "Send me a copy"</label></th>
                    <td><input type="checkbox" name="wccm_show_copy_checkbox" value="1" <?php checked(1, get_option('wccm_show_copy_checkbox'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wccm_show_phone">Show Telephone Field</label></th>
                    <td><input type="checkbox" name="wccm_show_phone" value="1" <?php checked(1, get_option('wccm_show_phone'), true); ?>></td>
                </tr>
                <tr>
                    <th><label for="wccm_require_phone">Make Telephone Field Mandatory</label></th>
                    <td><input type="checkbox" name="wccm_require_phone" value="1" <?php checked(1, get_option('wccm_require_phone'), true); ?>></td>
                </tr>
            </table>

            <h2>Enquiry Button Settings</h2>
            <table class="form-table">
                <tr>
                    <th><label for="wccm_button_location">Button Location</label></th>
                    <td>
                        
                <select name="wccm_button_location">
                    <option value="after_price" <?php selected(get_option('wccm_button_location'), 'after_price'); ?>>
                        <?php _e('After Product Price', 'wc-catalog-mode'); ?>
                    </option>
                    <option value="after_summary" <?php selected(get_option('wccm_button_location'), 'after_summary'); ?>>
                        <?php _e('After Product Summary', 'wc-catalog-mode'); ?>
                    </option>
                </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="wccm_display_as_link">Display as Link</label></th>
                    <td><input type="checkbox" name="wccm_display_as_link" value="1" <?php checked(get_option('wccm_display_as_link'), 1); ?> /></td>
                </tr>
                <tr>
                <th scope="row"><label for="wc_catalog_mode_button_text">Enquiry Button Text</label></th>
                <td>
                    <input type="text" id="wc_catalog_mode_button_text" name="wc_catalog_mode_button_text" value="<?php echo esc_attr(get_option('wc_catalog_mode_button_text', 'Request a Quote')); ?>" class="regular-text" />
                    <p class="description">Enter the label text for the enquiry button or link.</p>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

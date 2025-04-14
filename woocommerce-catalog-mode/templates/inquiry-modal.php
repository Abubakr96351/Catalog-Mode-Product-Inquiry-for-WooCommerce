<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="inquiry-modal" class="inquiry-modal" style="display:none;">
    <div class="inquiry-modal-content">
        <span class="close-modal">&times;</span>
        <h2>Send a Product Inquiry</h2>
        <?php
            $show_phone = get_option('wccm_show_phone');
            $require_phone = get_option('wccm_require_phone');
            $show_copy_checkbox = get_option('wccm_show_copy_checkbox');
        ?>

        <form id="inquiry-form">
            <input type="hidden" id="product_id" value="<?php the_ID(); ?>">
            <input type="hidden" id="product_name" value="<?php the_title(); ?>">

            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>

            <?php if ($show_phone): ?>
                <label for="phone">Telephone <?php echo $require_phone ? '*' : ''; ?></label>
                <input type="tel" id="phone" name="phone" <?php echo $require_phone ? 'required' : ''; ?>>
            <?php endif; ?>

            <label for="message">Message *</label>
            <textarea id="message" name="message" required></textarea>

            <?php if ($show_copy_checkbox): ?>
                <label class="send_copy_label"><input type="checkbox" id="send_copy" name="send_copy"> Send me a copy</label>
            <?php endif; ?>

            <button type="submit" class="submit">Submit</button>
        </form>
    </div>
</div>
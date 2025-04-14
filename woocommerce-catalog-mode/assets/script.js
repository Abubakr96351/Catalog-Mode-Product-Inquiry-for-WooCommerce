jQuery(document).ready(function ($) {
    // Modal handling with improved selectors
    $(document).on('click', '#inquiry-button, .inquiry-button, .inquiry-button-link, .inquiry-trigger', function () {
        $("#inquiry-modal").fadeIn();
    });

    // Enhanced modal closing (click outside, close button, or ESC key)
    $(".close-modal").click(function () {
        $("#inquiry-modal").fadeOut();
    });
    
    $(document).mouseup(function (e) {
        if ($(e.target).closest(".inquiry-modal-content").length === 0 && 
            $(e.target).closest("#inquiry-button, .inquiry-button, .inquiry-button-link").length === 0) {
            $("#inquiry-modal").fadeOut();
        }
    });
    
    $(document).keyup(function (e) {
        if (e.key === "Escape") {
            $("#inquiry-modal").fadeOut();
        }
    });

    // Prevent modal content clicks from closing modal
    $(".inquiry-modal-content").click(function (e) {
        e.stopPropagation();
    });

    // Enhanced variation handling
    $('form.variations_form').on('change', '.variations select', function () {
        let selectedText = [];
        let variationId = $('input[name="variation_id"]').val() || '';

        $('.variations select').each(function () {
            const $select = $(this);
            const label = $select.closest('.variations_form')
                              .find(`label[for='${$select.attr('id')}']`)
                              .text()
                              .trim()
                              .replace(':', ''); // Clean up label
            const value = $select.find('option:selected').text().trim();
            
            if (value && !value.includes('Choose an option')) {
                selectedText.push(`${label}: ${value}`);
            }
        });

        $('#selected_variation').val(selectedText.join(', '));
    });

    // Form submission with better validation and feedback
    $("#inquiry-form").submit(function (e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find("button[type='submit']");
        const originalBtnText = $submitBtn.text();
        
        $submitBtn.prop("disabled", true).text("Sending...");

        // Client-side validation
        if (!$("#name").val() || !$("#email").val() || !$("#message").val()) {
            alert("Please fill in all required fields.");
            $submitBtn.prop("disabled", false).text(originalBtnText);
            return false;
        }

        // Prepare form data including all relevant fields
        const formData = {
            action: "wc_catalog_mode_inquiry",
            product_id: $("#product_id").val(),
            product_name: $("#product_name").val(),
            name: $("#name").val(),
            email: $("#email").val(),
            phone: $("#phone").val(),
            message: $("#message").val(),
            selected_variation: $("#selected_variation").val(),
            send_copy: $("#send_copy").is(":checked") ? 1 : 0
        };

        // AJAX submission
        $.ajax({
            type: "POST",
            url: wcCatalogAjax.ajax_url,
            data: formData,
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Success handling
                    $form[0].reset();
                    $("#inquiry-modal").fadeOut();
                    
                    // Show success message (consider using a toast notification instead)
                    alert(response.data.message);
                } else {
                    // Error handling
                    const errorMsg = response.data.message || 
                                   (response.data.errors ? Object.values(response.data.errors).join('\n') : 
                                   "Something went wrong. Please try again.");
                    alert(errorMsg);
                }
            },
            error: function (xhr) {
                console.error("Inquiry Error:", xhr.responseText);
                alert("An error occurred while sending your inquiry. Please try again.");
            },
            complete: function () {
                $submitBtn.prop("disabled", false).text(originalBtnText);
            }
        });
    });
});
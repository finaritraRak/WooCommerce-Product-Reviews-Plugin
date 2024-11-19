jQuery(document).ready(function ($) {
    $('#prp-review-form').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.post(prp_ajax.ajax_url, {
            action: 'prp_add_review',
            ...formData,
        })
            .done(function (response) {
                if (response.success) {
                    $('#prp-response').html('<p style="color: green;">' + response.data + '</p>');
                    $('#prp-review-form')[0].reset();
                } else {
                    $('#prp-response').html('<p style="color: red;">' + response.data + '</p>');
                }
            })
            .fail(function () {
                $('#prp-response').html('<p style="color: red;">Erreur lors de l\'envoi de l\'avis.</p>');
            });
    });
});

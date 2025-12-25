$(document).ready(function() {
    $('#payment-form').on('submit', function(e) {
        e.preventDefault();

        let btn = $('#pay-btn');
        let loading = $('#loading');
        let form = $(this);
        let url = form.data('url');

        btn.prop('disabled', true);
        loading.show();

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.redirect_url) {
                    $('#checkout-section').hide();
                    $('#payment-iframe-container').show();
                    $('#payment-iframe').attr('src', response.redirect_url);

                    $('html, body').animate({
                        scrollTop: $("#payment-iframe-container").offset().top
                    }, 500);
                } else {
                    alert('Payment initiation failed. Please try again.');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Payment initialization failed';
                alert(msg);
                btn.prop('disabled', false);
                loading.hide();
            }
        });
    });
});

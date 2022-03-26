<script>
    (function($){
        function remove_custom_notice(){
            $('.woocommerce-error li').each(function(){
                var error_text = $(this).text().trim();
                if (error_text == 'custom_notice'){
                    $(this).css('display', 'none');
                }
            });
        }
        $(document).ready(function(){
            remove_custom_notice();
            $(document.body).on('checkout_error', function () {
                var error_count = $('.woocommerce-error li').length;
                if (error_count == 1) { // Validation Passed (Just the Fake Error Exists)
                    $('.woocommerce-error').remove();
                    if($("[name=payment_method]:checked").val() === "acima_credit") {
                        var form = $( 'form.checkout' );
                        var arr = form.serializeArray();
                        $.ajax({
                            type: 'POST',
                            url: "/wp-admin/admin-ajax.php",
                            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                            enctype: 'multipart/form-data',
                            data: {
                                'action': 'ajax_order',
                                'fields': arr,
                                'user_id': <?php echo get_current_user_id(); ?>,
                            },
                            success: function (result) {
                                var data = JSON.parse(result);
                                if (data.result && data.result === "success") {
                                    window.history.replaceState({},
                                        document.title, data.redirect);
                                    const urlSearchParams = new URLSearchParams(window.location.search);
                                    const params = Object.fromEntries(urlSearchParams.entries());
                                    params.action = "init_acima_iframe";
                                    $.ajax({
                                        type: 'GET',
                                        url: "/wp-admin/admin-ajax.php",
                                        data: params,
                                        success: function (iframeHTML) {
                                            console.log(iframeHTML);
                                            document.querySelector("body").innerHTML += iframeHTML;
                                            if (typeof AcimaCreditCheckout == "object") {
                                                AcimaCredit.log = function (str) {
                                                    console.log('*** ACIMA CREDIT');
                                                    console.log(str);
                                                    if(str.type && str.type === "ACIMA_ECOM_IFRAME_CLOSE"){
                                                        window.history.replaceState({},
                                                            document.title, "/checkout/");
                                                    }
                                                };
                                                AcimaCreditCheckout.iframe = document.getElementById('acima-credit-iframe-checkout');
                                                AcimaCredit.log('onDOMContentLoaded');
                                                var body = document.getElementsByTagName('body')[0];
                                                body.classList.add('frozen');
                                                AcimaCreditCheckout.start();
                                            }
                                        }
                                    })
                                } else {
                                    alert("Error");
                                }
                            },
                            error: function (error) {
                                console.log(error); // For testing (to be removed)
                            }
                        });
                    }else{
                        $('#confirm-order-flag').val('');
                        $('#place_order').trigger('click');
                    }
                }else{
                    remove_custom_notice();
                }
            });

            var checkout_form = $( 'form.checkout' );
            checkout_form.on( 'checkout_place_order', function(e) {
                if ($('#confirm-order-flag').length == 0) {
                    checkout_form.append('<input type="hidden" id="confirm-order-flag" name="confirm-order-flag" value="1">');
                }
                return true;
            });

        });
    })(jQuery);
</script>
<?php
add_action('wp_ajax_ajax_order', 'submited_ajax_order_data');
add_action( 'wp_ajax_nopriv_ajax_order', 'submited_ajax_order_data' );
function submited_ajax_order_data() {
    if( isset($_POST['fields']) && ! empty($_POST['fields']) ) {

        $order    = new WC_Order();
        $cart     = WC()->cart;
        $checkout = WC()->checkout;
        $data     = [];

        // Loop through posted data array transmitted via jQuery
        foreach( $_POST['fields'] as $values ){
            // Set each key / value pairs in an array
            $data[$values['name']] = $values['value'];
        }

        $cart_hash          = md5( json_encode( wc_clean( $cart->get_cart_for_session() ) ) . $cart->total );
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        // Loop through the data array
        foreach ( $data as $key => $value ) {
            // Use WC_Order setter methods if they exist
            if ( is_callable( array( $order, "set_{$key}" ) ) ) {
                $order->{"set_{$key}"}( $value );

                // Store custom fields prefixed with wither shipping_ or billing_
            } elseif ( ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) )
                && ! in_array( $key, array( 'shipping_method', 'shipping_total', 'shipping_tax' ) ) ) {
                $order->update_meta_data( '_' . $key, $value );
            }
        }

        $order->set_created_via( 'checkout' );
        $order->set_cart_hash( $cart_hash );
        $order->set_customer_id( apply_filters( 'woocommerce_checkout_customer_id', isset($_POST['user_id']) ? $_POST['user_id'] : '' ) );
        $order->set_currency( get_woocommerce_currency() );
        $order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
        $order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
        $order->set_customer_user_agent( wc_get_user_agent() );
        $order->set_customer_note( isset( $data['order_comments'] ) ? $data['order_comments'] : '' );
        $order->set_payment_method( isset( $available_gateways[ $data['payment_method'] ] ) ? $available_gateways[ $data['payment_method'] ]  : $data['payment_method'] );
        $order->set_shipping_total( $cart->get_shipping_total() );
        $order->set_discount_total( $cart->get_discount_total() );
        $order->set_discount_tax( $cart->get_discount_tax() );
        $order->set_cart_tax( $cart->get_cart_contents_tax() + $cart->get_fee_tax() );
        $order->set_shipping_tax( $cart->get_shipping_tax() );
        $order->set_total( $cart->get_total( 'edit' ) );

        $checkout->create_order_line_items( $order, $cart );
        $checkout->create_order_fee_lines( $order, $cart );
        $checkout->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ), WC()->shipping->get_packages() );
        $checkout->create_order_tax_lines( $order, $cart );
        $checkout->create_order_coupon_lines( $order, $cart );

        /**
         * Action hook to adjust order before save.
         * @since 3.0.0
         */
        do_action( 'woocommerce_checkout_create_order', $order, $data );

        // Save the order.
        $order_id = $order->save();

        do_action( 'woocommerce_checkout_update_order_meta', $order_id, $data );

        $order_id = intval($order_id);

        if($order_id > 0){
            $acima = new WC_Gateway_Acima_Credit();
            $query_vars = $acima->process_payment($order_id);
            echo json_encode($query_vars);
        }
    }
    die();
}


add_action('wp_ajax_init_acima_iframe', 'init_acima_iframe');
add_action( 'wp_ajax_nopriv_init_acima_iframe', 'init_acima_iframe' );
function init_acima_iframe(){
    $wc_settings = (object)get_option('woocommerce_acima_credit_settings');
    WC_Gateway_Acima_Credit_Template_Engine::render('checkout-iframe', array(
        'ACIMA_CREDIT_IFRAME_URL' => $wc_settings->api_url
    ));
    exit();
}

function add_fake_error($posted) {
    if ($_POST['confirm-order-flag'] == "1") {
        wc_add_notice( __( "custom_notice", 'fake_error' ), 'error');
    }
}
add_action('woocommerce_after_checkout_validation', 'add_fake_error');
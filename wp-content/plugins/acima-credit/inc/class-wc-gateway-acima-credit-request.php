<?php

/**
 * Acima Credit Payment Gateway Request
 *
 * Provides the Acima Payment Gateway Request
 *
 * @class       WC_Gateway_Acima_Credit_Request
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
class WC_Gateway_Acima_Credit_Request {
    public function __construct() {}

    /**
    * Return the checkout url containing the query strings for the purchased order
    *
    * @since 2.0.0
    * @param number $order_id
    * @return string
    */
    public function get_checkout_url($order_id, $thank_you_url) {
        global $woocommerce;

        return add_query_arg( array(
            'acima-credit' => '1',
            'order' => $order_id,
            'nonce' => wp_create_nonce('acima-credit-checkout-' . $order_id),
            'redirect' => $thank_you_url
        ), $woocommerce->cart->get_checkout_url() );
    }
}

<?php

require_once(dirname(__FILE__) . '/class-wc-gateway-acima-credit-order-parser.php');

/**
 * Acima Credit Payment Gateway Checkout Successful
 *
 * Process Ajax Requests
 *
 * @class       WC_Gateway_Acima_Credit_Checkout_Successful
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
function ajax_acima_credit_checkout_successful() {
    class WC_Gateway_Acima_Credit_Checkout_Successful {

        private $debug = FALSE;
        private $debugMsg = '';

        public function __construct() {
            $this->debug = TRUE;
            $this->debugMsg = '';
        }

        public function init() {
            /**
            * Save POST variables to local
            */
            $this->order_id = sanitize_text_field( $_POST['order'] );
            $this->nonce = sanitize_text_field( $_POST['nonce'] );
            $this->lease_id = sanitize_text_field( $_POST['lease_id'] );
            $this->checkout_token = sanitize_text_field( $_POST['checkout_token'] );

            $this->check_nonce();
        }

        /**
        * Verify nonce sent is correct
        */
        public function check_nonce() {
            $success = FALSE;

            if (wp_verify_nonce( $this->nonce, 'acima-credit-checkout-' . $this->order_id )){
                $this->close_order();
                $success = TRUE;
            }

            echo json_encode(array(
                'success' => $success
            ));
            wp_die();
        }

        /**
        * Close order on WooCommerce and set payment as complete
        */
        public function close_order() {
            global $woocommerce;

            $order = new WC_Order($this->order_id);

            /**
            * Mark the order as paid
            */
            $order->payment_complete();

            /**
            * Add order notes about Acima Credit payment
            */
            $order->add_order_note( __('Acima Credit payment completed.', 'woocommerce') );
            $order->add_order_note( __('Acima Credit - Lease ID: ' . $this->lease_id, 'woocommerce') );
            $order->add_order_note( __('Acima Credit - Checkout Token: ' . $this->checkout_token, 'woocommerce') );

            update_post_meta($this->order_id, '_acima_credit_lease_id', $this->lease_id);
            update_post_meta($this->order_id, '_acima_credit_checkout_token', $this->checkout_token);
        }
    }
    $acima_credit_checkout_successful = new WC_Gateway_Acima_Credit_Checkout_Successful();
    $acima_credit_checkout_successful->init();
}
add_action('wp_ajax_acima_credit_checkout_successful', 'ajax_acima_credit_checkout_successful');
add_action('wp_ajax_nopriv_acima_credit_checkout_successful', 'ajax_acima_credit_checkout_successful');

/**
 * Acima Credit Payment Gateway Customer Info
 *
 * Return Customer Info for specified Order
 *
 * @class       WC_Gateway_Acima_Credit_Customer_Info
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
function ajax_acima_credit_customer_info() {
    class WC_Gateway_Acima_Credit_Customer_Info {
        public function __construct() {}

        public function init() {

            /**
            * Save POST variables to local
            */
            $this->order_id = sanitize_text_field( $_POST['order'] );
            $this->nonce = sanitize_text_field( $_POST['nonce'] );

            $this->check_nonce();
        }

        /**
        * Verify nonce sent is correct
        */
        public function check_nonce() {
            if (wp_verify_nonce( $this->nonce, 'acima-credit-checkout-' . $this->order_id )){
                $this->get_customer_info();
            };
        }

        /**
        * Get customer info by order id
        */
        public function get_customer_info() {
            $data = array(
                'type' => 'customer',
                'success' => TRUE,
                'data' => WC_Gateway_Acima_Credit_Order_Parser::parse_customer($this->order_id)
            );
            echo json_encode($data);
            wp_die();
        }
    }
    $acima_credit_customer_info = new WC_Gateway_Acima_Credit_Customer_Info();
    $acima_credit_customer_info->init();
}
add_action('wp_ajax_acima_credit_customer_info', 'ajax_acima_credit_customer_info');
add_action('wp_ajax_nopriv_acima_credit_customer_info', 'ajax_acima_credit_customer_info');

/**
 * Acima Credit Payment Gateway Order Info
 *
 * Return Order Info for specified Order Id
 *
 * @class       WC_Gateway_Acima_Credit_Order_Info
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
function ajax_acima_credit_order_info() {
    class WC_Gateway_Acima_Credit_Order_Info {
        public function __construct() {}

        public function init() {

            /**
            * Save POST variables to local
            */
            $this->order_id = sanitize_text_field( $_POST['order'] );
            $this->nonce = sanitize_text_field( $_POST['nonce'] );

            $this->check_nonce();
        }

        /**
        * Verify nonce sent is correct
        */
        public function check_nonce() {
            if (wp_verify_nonce( $this->nonce, 'acima-credit-checkout-' . $this->order_id )){
                $this->get_order_info();
            };
        }

        /**
        * Get order info
        */
        public function get_order_info() {
            $data = array(
                'type' => 'order',
                'success' => TRUE,
                'data' => WC_Gateway_Acima_Credit_Order_Parser::parse_order($this->order_id)
            );
            echo json_encode($data);
            wp_die();
        }
    }
    $acima_credit_order_info = new WC_Gateway_Acima_Credit_Order_Info();
    $acima_credit_order_info->init();
};

add_action('wp_ajax_acima_credit_order_info', 'ajax_acima_credit_order_info');
add_action('wp_ajax_nopriv_acima_credit_order_info', 'ajax_acima_credit_order_info');

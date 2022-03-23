<?php

/**
 * Acima Credit Payment Gateway iFrame
 *
 * Loads the iframe on checkout page
 *
 * @class       WC_Gateway_Acima_Credit_IFrame
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
function init_acima_credit_iframe() {
    class WC_Gateway_Acima_Credit_IFrame {
        public function __construct() {}

        public function init() {
            $ac = !empty($_GET['acima-credit']) ? $_GET['acima-credit'] : '';
            $oi = !empty($_GET['order']) ? $_GET['order'] : '';
            $nc = !empty($_GET['nonce']) ? $_GET['nonce'] : '';
            $this->acima_credit = sanitize_text_field( $ac );
            $this->order_id = sanitize_text_field( $oi );
            $this->nonce = sanitize_text_field( $nc );
            $this->wc_settings = (object)get_option('woocommerce_acima_credit_settings');
            if ('1' == $this->acima_credit) {
                $this->check_nonce();
            }
        }

        public function check_nonce() {
            if (wp_verify_nonce( $this->nonce, 'acima-credit-checkout-' . $this->order_id )){
                $this->check_is_ajax();
            }
        }

        public function check_is_ajax() {
            if (!defined('DOING_AJAX') || (defined('DOING_AJAX') && !DOING_AJAX)) {
                $this->render_iframe();
            }
        }

        public function render_iframe() {
            WC_Gateway_Acima_Credit_Template_Engine::render('checkout-iframe', array(
                'ACIMA_CREDIT_IFRAME_URL' => $this->wc_settings->api_url
            ));
        }
    }
    $acima_credit_iframe = new WC_Gateway_Acima_Credit_IFrame();
    $acima_credit_iframe->init();
}

add_action('init', 'init_acima_credit_iframe');

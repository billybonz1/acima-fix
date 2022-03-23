<?php

require_once(dirname(__FILE__) . '/class-wc-gateway-acima-credit-order-parser.php');

/**
 * Acima Credit Payment Gateway
 *
 * Provides the Acima Payment Gateway
 *
 * @class       WC_Gateway_Acima_Credit
 * @extends     WC_Payment_Gateway
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
function init_acima_credit_payment_gateway() {
    class WC_Gateway_Acima_Credit extends WC_Payment_Gateway {

        /**
        * Constructor method
        */
        public function __construct() {
            /**
            * Unique ID for the gateway.
            */
            $this->id = 'acima_credit';

            /**
            * If you want to show an image next to the gateway’s name on the frontend, enter a URL to an image.
            */
            $this->icon = '';

            /**
            * Bool. Can be set to true if you want payment fields to show on the checkout (if doing a direct integration).
            */
            $this->has_fields = false;

            /**
            * Button label to replace the default "Place order"
            */
            $this->order_button_text  = __( 'PROCEED WITH ACIMA LEASING', 'woocommerce' );

            /**
            * Title of the payment method shown on the admin page.
            */
            $this->method_title = __('Acima Leasing', 'wc-gateway-acima-credit');

            /**
            * Description for the payment method shown on the admin page.
            */
            $this->method_description = __('This plugin adds the Acima Credit payment option to your WooCommerce store.', 'wc-gateway-acima-credit');

            /**
            * Load default settings
            */
            $this->init_form_fields();
            $this->init_settings();

            /**
            * Set gateway variables
            */
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->api_key = $this->get_option('api_key');
            $this->api_url = $this->get_option('api_url');
            $this->merchant_id = $this->get_option('merchant_id');

            /**
            * Add a save hook for the settings
            */
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
        * These are the options that will be shown in admin on the gateway settings page
        */
        public function init_form_fields() {
            $this->form_fields = array(
                /**
                * Enable or disable Acima Credit
                */
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable Acima Leasing', 'woocommerce' ),
                    'default' => 'yes'
                ),
                /**
                * Title for front-end during checkout
                */
                'title' => array(
                    'title' => __( 'Title', 'woocommerce' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                    'default' => __( 'Acima Leasing', 'woocommerce' ),
                    'desc_tip' => true
                ),
                /**
                * Description for front-end during checkout
                */
                'description' => array(
                    'title' => __( 'Description', 'woocommerce' ),
                    'type' => 'text',
                    'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
                    'default' => __('Pay via Acima Leasing.', 'woocommerce'),
                    'desc_tip' => true
                ),
                /**
                * API Key
                */
                'api_key' => array(
                    'title' => __( 'API Key', 'wc-gateway-acima-credit' ),
                    'type' => 'text',
                    'description' => __('Contact Acima Credit Support Team if you don\'t have an API Key.', 'wc-gateway-acima-credit'),
                    'default' => '',
                    'desc_tip' => true
                ),
                /**
                * API URL
                */
                'api_url' => array(
                    'title' => __( 'API URL', 'wc-gateway-acima-credit' ),
                    'type' => 'text',
                    'description' => __('Acima Credit API URL in the https://website.com/ format.', 'wc-gateway-acima-credit'),
                    'default' => '',
                    'desc_tip' => true
                ),
                /**
                * Merchant ID
                */
                'merchant_id' => array(
                    'title' => __( 'Merchant ID', 'wc-gateway-acima-credit' ),
                    'type' => 'text',
                    'description' => __('Contact Acima Credit Support Team if you don\'t have a Merchant ID.', 'wc-gateway-acima-credit'),
                    'default' => '',
                    'desc_tip' => true
                )
            );
        }

        public function get_icon() {
            return '&nbsp;&nbsp;&nbsp;&nbsp;<small>The No Credit Option.&nbsp;&nbsp;<a href="https://www.acimacredit.com/customer" target="_blank">Learn More</a></small>';
        }

        public function get_description() {
            if (is_admin()) {
                return $this->description;
            } else
            {
                return '<img src="'.plugin_dir_url( __DIR__ ) . 'public/images/AcimaLogo.png'.'"><br><br>' . $this->description;
            }
        }

        /**
        * Handling payment and processing the order
        */
        public function process_payment($order_id) {
            include_once( dirname( __FILE__ ) . '/class-wc-gateway-acima-credit-request.php' );

            global $woocommerce;

            $order = new WC_Order($order_id);

            $acima_credit_request = new WC_Gateway_Acima_Credit_Request();

            /**
            * Mark the order as on-hold
            */
            //$order->update_status('pending-payment', __( 'Awaiting Acima Credit payment', 'woocommerce' ));

            /**
            * Reduce stock levels
            */
            //$order->reduce_order_stock();

            /**
            * Remove products from cart
            */
            //$woocommerce->cart->empty_cart();

            /**
            * Return success page redirect
            */
            $thank_you_url = $this->get_return_url( $order );
            return array(
                'result' => 'success',
                //'redirect' => $this->get_return_url( $order )
                'redirect' => $acima_credit_request->get_checkout_url($order_id, $thank_you_url)
            );
        }
    }
}
add_action('plugins_loaded', 'init_acima_credit_payment_gateway');

/**
 * Include Acima Credit in the available gateways
 *
 * @since 1.0.0
 * @param array $gateways All available WC gateways
 * @return array $gateways All WC gateways + Acima Credit Gateway
 */
function wc_acima_credit_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_Acima_Credit';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_acima_credit_add_to_gateways' );


/**
 * Add hook for completed orders
 *
 * @since 1.1.0
 * @param $orderId Completed Order ID
 */
function wc_acima_credit_order_completed($orderID) {
    class WC_Gateway_Acima_Credit_Order_Completed {

        private $debug = FALSE;
        private $debugMsg = '';

        public function __construct() {
            $this->debug = TRUE;
            $this->debugMsg = '';
        }

        public function init($orderID) {
            $this->order_id = $orderID;
            $this->lease_id = get_post_meta($this->order_id, '_acima_credit_lease_id', true);
            $this->checkout_token = get_post_meta($this->order_id, '_acima_credit_checkout_token', true);
            $this->wc_settings = (object)get_option('woocommerce_acima_credit_settings');

            $this->finalize_order();
        }

        /**
        * Finalize the order through the API
        */
        private function finalize_order() {
            $orderInfo = array();
            $orderInfo['details'] = WC_Gateway_Acima_Credit_Order_Parser::parse_order($this->order_id);
            $orderInfo['checkoutToken'] = $this->checkout_token;

            $this->callAPI($orderInfo);
        }


        public function callAPI($data) {

            /**
            * Call /merchants/:merchant-id/leases/:lease-id/finalize
            */
            // API Key: $this->wc_settings->api_key
            $curl = curl_init();

            $header = array();
            $header[] = 'Content-type: application/json';
            $header[] = 'Api-Token: ' . $this->wc_settings->api_key;

            $url = $this->formatURL($this->wc_settings->api_url) . '/merchants/' . $this->wc_settings->merchant_id . '/leases/' . $this->lease_id . '/finalize';

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $result = json_decode(curl_exec($curl));

            $this->debugMsg = json_encode($result);
        }

        private function formatURL($url) {
            return trim($url, '/');
        }
    }

    $payment_method = get_post_meta($orderID, '_payment_method', true);

    if ($payment_method === 'acima_credit') {
        $acima_credit_order_completed = new WC_Gateway_Acima_Credit_Order_Completed();
        $acima_credit_order_completed->init($orderID);
    }
}
add_action('woocommerce_order_status_completed', 'wc_acima_credit_order_completed');

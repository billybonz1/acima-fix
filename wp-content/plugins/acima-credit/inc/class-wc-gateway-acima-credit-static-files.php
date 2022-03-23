<?php

/**
 * Acima Credit Payment Gateway Static Files
 *
 * Enqueue static files for the Acima Credit Plugin
 *
 * @class       WC_Gateway_Acima_Credit_Static_Files
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
class WC_Gateway_Acima_Credit_Static_Files {
    /**
    * Enqueue the static files
    *
    * @since 2.0.0
    */
    public static function enqueue_files() {
        /**
        * Register external files for admin
        */
        if (is_admin()) {
            /**
            * Static files for admin panel
            */
        } else {
            /**
            * Static files for front-end
            */
            wp_enqueue_style( 'acima-credit-css', plugins_url( '/public/css/acima-credit.css', dirname(__FILE__ )) );
            wp_enqueue_style( 'acima-credit-checkout-css', plugins_url( '/public/css/checkout.css', dirname(__FILE__ )) );
            wp_enqueue_style( 'acima-credit-pre-approval-css', plugins_url( '/public/css/pre-approval.css', dirname(__FILE__ )) );
            wp_register_script( 'acima-credit-js', plugins_url( '/public/js/acima-credit.js', dirname(__FILE__ )) );
            wp_register_script( 'acima-credit-checkout-js', plugins_url( '/public/js/checkout.js', dirname(__FILE__ )) );
            wp_register_script( 'acima-credit-pre-approval-js', plugins_url( '/public/js/pre-approval.js', dirname(__FILE__ )) );

            wp_enqueue_script( 'acima-credit-js' );
            wp_enqueue_script( 'acima-credit-checkout-js' );
            wp_enqueue_script( 'acima-credit-pre-approval-js' );
        }
    }
}

add_action( 'wp_enqueue_scripts', array('WC_Gateway_Acima_Credit_Static_Files', 'enqueue_files') );

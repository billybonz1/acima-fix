<?php

/**
 * Acima Credit Payment Gateway Head
 *
 * Inject Head values
 *
 * @class       WC_Gateway_Acima_Credit_Head
 * @version     2.0.1
 * @author      Acima Credit, Inc
 */

function output_frontend_values() {
    $acimaSettings = get_option('woocommerce_acima_credit_settings');

    WC_Gateway_Acima_Credit_Template_Engine::render('merchant-id', array(
        'MERCHANT_ID' => $acimaSettings['merchant_id']
    ));
}

/**
* Add action to display the keys
*/
add_action( 'wp_head', 'output_frontend_values');

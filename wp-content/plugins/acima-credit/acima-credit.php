<?php

/**
 * Plugin Name: Acima Credit Payment Gateway
 * Plugin URI: https://github.com/acima-credit/ecom-woocommerce
 * Description: Acima Credit Payment Gateway for WooCommerce.
 * Author: Acima Credit, Inc
 * Author URI: https://github.com/acima-credit/
 * Version: 2.0.1
 * Text Domain: acima-credit-payment-gateway
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Gateway-Acima-Credit
 * @author    Acima Credit, Inc
 * @category  Admin
 * @copyright Copyright (c) 2015-2017, Acima Credit, Inc. and WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined('ABSPATH') or exit;


/**
* Check if WooCommerce is active
*/
if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

include_once( dirname( __FILE__ ) . '/inc/class-wc-gateway-acima-credit-head.php' );

include_once( dirname( __FILE__ ) . '/inc/class-wc-gateway-acima-credit.php' );

include_once( dirname( __FILE__ ) . '/inc/class-wc-gateway-acima-credit-static-files.php' );

include_once( dirname( __FILE__ ) . '/inc/class-wc-gateway-acima-credit-iframe.php' );

include_once( dirname( __FILE__ ) . '/inc/class-wc-gateway-acima-credit-template-engine.php' );

include_once( dirname( __FILE__ ) . '/inc/class-wc-gateway-acima-credit-custom-shortcodes.php' );

include_once( dirname( __FILE__ ) . '/inc/class-wc-gateway-acima-credit-ajax-requests.php' );

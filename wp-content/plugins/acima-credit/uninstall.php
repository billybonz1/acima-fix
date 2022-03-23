<?php
/**
 * Runs on Uninstall of Acima Credit Payment Gateway
 *
 * @package   WC-Gateway-Acima-Credit
 * @author    Acima Credit, Inc
 * @category  Admin
 * @copyright Copyright (c) 2015-2017, Acima Credit, Inc. and WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// Check that we should be doing this
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit; // Exit if accessed directly
}

delete_option('woocommerce_acima_credit_settings');
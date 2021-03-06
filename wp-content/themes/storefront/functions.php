<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */


if(isset($_GET['sift'])){
//    file_put_contents(dirname(__FILE__)."/test.txt", file_get_contents('php://input'), FILE_APPEND);
//    exit();
    $input = file_get_contents('php://input');
    $json = json_decode($input);
    if(isset($json->entity) && isset($json->entity->id)){
        $user_id = (int)explode("_", $json->entity->id)[1];
        if($json->decision->id === "looks_bad_payment_abuse") {
            update_user_meta($user_id, "blocked", 1);
        }else if($json->decision->id === "looks_ok_payment_abuse"){
            update_user_meta($user_id, "blocked", 0);
        }
    }
    exit();
}

function redirect_blocked(){
    $meta = get_user_meta(get_current_user_id(), "blocked");
    if($meta[0] == "1"){
        wp_redirect("/blocked/");
    }
}
add_action("woocommerce_before_checkout_form", "redirect_blocked");


/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */


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


// New order status AFTER woo 2.2
add_action( 'init', 'register_my_new_order_statuses' );

function register_my_new_order_statuses() {
    register_post_status( 'wc-blocked', array(
        'label'                     => _x( 'Blocked', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Blocked <span class="count">(%s)</span>', 'Blocked<span class="count">(%s)</span>', 'woocommerce' )
    ) );
}

add_filter( 'wc_order_statuses', 'my_new_wc_order_statuses' );




// Register in wc_order_statuses.
function my_new_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-blocked'] = _x( 'Blocked', 'Order status', 'woocommerce' );

    return $order_statuses;
}

add_action('woocommerce_order_status_changed','woo_order_status_change_custom');
function woo_order_status_change_custom($order_id) {
    $order = new WC_Order( $order_id );
    $user_id = $order->get_user_id();
    if($order->get_status() === "blocked" && $user_id > 0){
        update_user_meta($user_id, "blocked", 1);
    }
}
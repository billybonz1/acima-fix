<?php

/**
 * Acima Credit Payment Gateway Order Parser
 *
 * Parse Order Information
 *
 * @class       WC_Gateway_Acima_Credit_Checkout_Successful
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
class WC_Gateway_Acima_Credit_Order_Parser {

  /**
  * Parse order to API required format
  */
  public static function parse_order($orderId) {
    $order = new WC_Order($orderId);
    $data = array();
    $data['id'] = $orderId;
    $data['lineItems'] = array();

    foreach($order->get_items() as $item_id => $item_data){
      $product        = $item_data->get_product();
      $productPrice   = $order->get_item_subtotal($item_data);
      $productId      = (string) $product->get_id();
      $productName    = $product->get_name();
      $quantity       = (int) $item_data->get_quantity();
      $unitPrice      = (int) number_format($productPrice * 100, 0, '.','');
      array_push($data['lineItems'], array(
          'productName' => $productName,
          'quantity'    => $quantity,
          'unitPrice'   => $unitPrice,
          'productId'   => $productId
      ));
    }

    if (version_compare (WC_VERSION, '3.0', '<')) {
      $data['shipping']         = (int) number_format($order->shipping_total * 100, 0, '.','');
      $data['discounts']        = (int) number_format($order->discount_total * 100, 0, '.','');
      $data['salesTax']         = (int) number_format($order->total_tax * 100, 0, '.','');
      $data['cartTotalWithTax'] = (int) number_format($order->total * 100, 0, '.','');
    } else {
      $data['shipping']         = (int) number_format($order->get_shipping_total() * 100, 0, '.','');
      $data['discounts']        = (int) number_format($order->get_discount_total() * 100, 0, '.','');
      $data['salesTax']         = (int) number_format($order->get_total_tax() * 100, 0, '.',''); // for entire cart
      $data['cartTotalWithTax'] = (int) number_format($order->get_total() * 100, 0, '.',''); // Everything: tax, shipping, discounts
    }

    return $data;
  }


  /**
  * Parse customer to API required format
  */
  public static function parse_customer($orderId) {
      $order = new WC_Order($orderId);

      if (version_compare (WC_VERSION, '3.0', '<')) {
        $data = array(
            'firstName' => $order->billing_first_name,
            'lastName' => $order->billing_last_name,
            'phone' => $order->billing_phone,
            'address' => array(
                'street1' => $order->billing_address_1,
                'street2' => $order->billing_address_2,
                'city' => $order->billing_city,
                'state' => $order->billing_state,
                'zipCode' => $order->billing_postcode
            ),
            'email' => $order->billing_email
        );
      } else {
        $data = array(
            'firstName' => $order->get_billing_first_name(),
            'lastName' => $order->get_billing_last_name(),
            'phone' => $order->get_billing_phone(),
            'address' => array(
                'street1' => $order->get_billing_address_1(),
                'street2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'zipCode' => $order->get_billing_postcode()
            ),
            'email' => $order->get_billing_email()
        );
      }

      return $data;
  }
}

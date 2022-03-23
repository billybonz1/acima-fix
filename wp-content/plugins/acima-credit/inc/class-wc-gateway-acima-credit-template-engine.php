<?php

/**
 * Acima Credit Payment Gateway Template Engine
 *
 * Render HTML Files
 *
 * @class       WC_Gateway_Acima_Credit_Template_Engine
 * @version     2.0.1
 * @package     WooCommerce/Classes/Payment
 * @author      Acima Credit, Inc
 */
 class WC_Gateway_Acima_Credit_Template_Engine {
     public static function render( $filename = '', $params = array() ) {
         if ($filename) {
             $html = file_get_contents( dirname( __FILE__ ) . '/../views/' . $filename . '.html' );

             foreach ($params as $param => $value) {
                 $html = str_replace("%{$param}%", $value, $html);
             }

             echo $html;
         }
     }
 }

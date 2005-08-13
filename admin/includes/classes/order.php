<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: order.php,v 1.4 2005/08/13 17:06:18 spiderr Exp $
//

  class order {
    var $info, $totals, $products, $customer, $delivery;

    function order($order_id) {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      $this->query($order_id);
    }

    function query($order_id) {
      global $db;
      $order = $db->Execute("select *
                             from " . TABLE_ORDERS . " o INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(o.`customers_id`=uu.`user_id`)
                             where orders_id = '" . (int)$order_id . "'");


      $totals = $db->Execute("select title, text, class
                              from " . TABLE_ORDERS_TOTAL . "
                              where orders_id = '" . (int)$order_id . "'
                              order by sort_order");

      while (!$totals->EOF) {
        $this->totals[] = array('title' => $totals->fields['title'],
                                'text' => $totals->fields['text'],
                                'class' => $totals->fields['class']);
        $totals->MoveNext();
      }

	  $this->info = $order->fields;
      $this->info['total'] = $order->fields['order_total'];
      $this->info['tax'] = $order->fields['order_tax'];

      $this->customer = array('name' => $order->fields['customers_name'],
                              'company' => $order->fields['customers_company'],
                              'street_address' => $order->fields['customers_street_address'],
                              'suburb' => $order->fields['customers_suburb'],
                              'city' => $order->fields['customers_city'],
                              'postcode' => $order->fields['customers_postcode'],
                              'state' => $order->fields['customers_state'],
                              'country' => $order->fields['customers_country'],
                              'format_id' => $order->fields['customers_address_format_id'],
                              'telephone' => $order->fields['customers_telephone'],
                              'email_address' => $order->fields['customers_email_address']);

      $this->delivery = array('name' => $order->fields['delivery_name'],
                              'company' => $order->fields['delivery_company'],
                              'street_address' => $order->fields['delivery_street_address'],
                              'suburb' => $order->fields['delivery_suburb'],
                              'city' => $order->fields['delivery_city'],
                              'postcode' => $order->fields['delivery_postcode'],
                              'state' => $order->fields['delivery_state'],
                              'country' => $order->fields['delivery_country'],
                              'format_id' => $order->fields['delivery_address_format_id']);

      $this->billing = array('name' => $order->fields['billing_name'],
                             'company' => $order->fields['billing_company'],
                             'street_address' => $order->fields['billing_street_address'],
                             'suburb' => $order->fields['billing_suburb'],
                             'city' => $order->fields['billing_city'],
                             'postcode' => $order->fields['billing_postcode'],
                             'state' => $order->fields['billing_state'],
                             'country' => $order->fields['billing_country'],
                             'format_id' => $order->fields['billing_address_format_id']);

      $index = 0;
      $orders_products = $db->Execute("select orders_products_id, products_name, products_model,
                                              products_price, products_tax, products_quantity,
                                              final_price, onetime_charges,
                                              product_is_free
                                       from " . TABLE_ORDERS_PRODUCTS . "
                                       where orders_id = '" . (int)$order_id . "'");

      while (!$orders_products->EOF) {
// convert quantity to proper decimals - account history
          if (QUANTITY_DECIMALS != 0) {
            $fix_qty = $orders_products->fields['products_quantity'];
            switch (true) {
            case (!strstr($fix_qty, '.')):
              $new_qty = $fix_qty;
              break;
            default:
              $new_qty = preg_replace('/[0]+$/', '', $orders_products->fields['products_quantity']);
              break;
            }
          } else {
            $new_qty = $orders_products->fields['products_quantity'];
          }

          $new_qty = round($new_qty, QUANTITY_DECIMALS);

          if ($new_qty == (int)$new_qty) {
            $new_qty = (int)$new_qty;
          }

        $this->products[$index] = array('quantity' => $new_qty,
                                        'name' => $orders_products->fields['products_name'],
                                        'model' => $orders_products->fields['products_model'],
                                        'tax' => $orders_products->fields['products_tax'],
                                        'price' => $orders_products->fields['products_price'],
                                        'onetime_charges' => $orders_products->fields['onetime_charges'],
                                        'final_price' => $orders_products->fields['final_price'],
                                        'product_is_free' => $orders_products->fields['product_is_free']);

        $subindex = 0;
        $attributes = $db->Execute("select products_options, products_options_values, options_values_price,
                                           price_prefix,
                                           product_attribute_is_free
                                    from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                                    where orders_id = '" . (int)$order_id . "'
                                    and orders_products_id = '" . (int)$orders_products->fields['orders_products_id'] . "'");
        if ($attributes->RecordCount()>0) {
          while (!$attributes->EOF) {
            $this->products[$index]['attributes'][$subindex] = array('option' => $attributes->fields['products_options'],
                                                                     'value' => $attributes->fields['products_options_values'],
                                                                     'prefix' => $attributes->fields['price_prefix'],
                                                                     'price' => $attributes->fields['options_values_price'],
                                                                     'product_attribute_is_free' =>$attributes->fields['product_attribute_is_free']);

            $subindex++;
            $attributes->MoveNext();
          }
        }
        $index++;
        $orders_products->MoveNext();
      }
    }
  }
?>
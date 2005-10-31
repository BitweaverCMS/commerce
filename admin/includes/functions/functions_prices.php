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
// $Id: functions_prices.php,v 1.7 2005/10/31 16:20:00 lsces Exp $
//
//


////
// Return a products quantity minimum and units display
  function zen_get_products_quantity_min_units_display($product_id, $include_break = true, $shopping_cart_msg = false) {
    $check_min = zen_get_products_quantity_order_min($product_id);
    $check_units = zen_get_products_quantity_order_units($product_id);

    $the_min_units='';

    if ($check_min > 1 or $check_units > 1) {
      if ($check_min > 1) {
        $the_min_units .= PRODUCTS_QUANTITY_MIN_TEXT_LISTING . '&nbsp;' . $check_min;
      }
      if ($check_units > 1) {
        $the_min_units .= ($the_min_units ? ' ' : '' ) . PRODUCTS_QUANTITY_UNIT_TEXT_LISTING . '&nbsp;' . $check_units;
      }

      if (($check_min > 0 or $check_units > 0) and !zen_get_products_quantity_mixed($product_id)) {
        if ($include_break == true) {
          $the_min_units .= '<br />' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_OFF : TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART);
        } else {
          $the_min_units .= '&nbsp;&nbsp;' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_OFF : TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART);
        }
      } else {
        if ($include_break == true) {
          $the_min_units .= '<br />' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_ON : TEXT_PRODUCTS_MIX_ON_SHOPPING_CART);
        } else {
          $the_min_units .= '&nbsp;&nbsp;' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_ON : TEXT_PRODUCTS_MIX_ON_SHOPPING_CART);
        }
      }
    }

    // quantity max
    $check_max = zen_get_products_quantity_order_max($product_id);

    if ($check_max != 0) {
      if ($include_break == true) {
        $the_min_units .= ($the_min_units != '' ? '<br />' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
      } else {
        $the_min_units .= ($the_min_units != '' ? '&nbsp;&nbsp;' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
      }
    }

    return $the_min_units;
  }


////
// look up discount in sale makers - attributes only can have discounts if set as percentages
// this gets the discount amount this does not determin when to apply the discount
  function zen_get_products_sale_discount($product_id = false, $categories_id = false, $display_type = false) {
    global $currencies;
    global $db;

// get products category
    if ($categories_id == true) {
      $check_category = $categories_id;
    } else {
      $check_category = zen_get_products_category_id($product_id);
    }

    $deduction_type_array = array(array('id' => '0', 'text' => DEDUCTION_TYPE_DROPDOWN_0),
                                  array('id' => '1', 'text' => DEDUCTION_TYPE_DROPDOWN_1),
                                  array('id' => '2', 'text' => DEDUCTION_TYPE_DROPDOWN_2));

    $sale_maker_discount = 0;
    $salemaker_sales = $db->Execute("select `sale_id`, `sale_status`, `sale_name`, `sale_categories_all`, `sale_deduction_value`, `sale_deduction_type`, `sale_pricerange_from`, `sale_pricerange_to`, `sale_specials_condition`, `sale_categories_selected`, `sale_date_start`, `sale_date_end`, `sale_date_added`, `sale_date_last_modified`, `sale_date_status_change` from " . TABLE_SALEMAKER_SALES . " where `sale_status`='1'");
    while (!$salemaker_sales->EOF) {
      $categories = explode(',', $salemaker_sales->fields['sale_categories_all']);
  	  while (list($key,$value) = each($categories)) {
	      if ($value == $check_category) {
  	      $sale_maker_discount = $salemaker_sales->fields['sale_deduction_value'];
	        $sale_maker_discount_type = $salemaker_sales->fields['sale_deduction_type'];
	        break;
        }
      }
      $salemaker_sales->MoveNext();
    }

    switch(true) {
      // percentage discount only
      case ($sale_maker_discount_type == 1):
        $sale_maker_discount = (1 - ($sale_maker_discount / 100));
        break;
      case ($sale_maker_discount_type == 0 and $display_type == true):
        $sale_maker_discount = $sale_maker_discount;
        break;
      case ($sale_maker_discount_type == 0 and $display_type == false):
        $sale_maker_discount = $sale_maker_discount;
        break;
      case ($sale_maker_discount_type == 2 and $display_type == true):
        $sale_maker_discount = $sale_maker_discount;
        break;
      default:
        $sale_maker_discount = 1;
        break;
    }

    if ($display_type == true) {
      if ($sale_maker_discount != 1 and $sale_maker_discount !=0) {
        switch(true) {
          case ($sale_maker_discount_type == 0):
            $sale_maker_discount = $currencies->format($sale_maker_discount) . ' ' . $deduction_type_array[$sale_maker_discount_type]['text'];
            break;
          case ($sale_maker_discount_type == 2):
            $sale_maker_discount = $currencies->format($sale_maker_discount) . ' ' . $deduction_type_array[$sale_maker_discount_type]['text'];
            break;
          case ($sale_maker_discount_type == 1):
            $sale_maker_discount = number_format( (1.00 - $sale_maker_discount),2,".","") . ' ' . $deduction_type_array[$sale_maker_discount_type]['text'];
            break;
        }
      } else {
        $sale_maker_discount = '';
      }
    }
    return $sale_maker_discount;
  }

////
// compute discount based on qty
  function zen_get_products_discount_price_qty($product_id, $check_qty, $check_amount=0) {
    global $db;
      $product_id = (int)$product_id;
      $products_query = $db->Execute("select `products_discount_type`, `products_discount_type_from`, `products_priced_by_attribute` from " . TABLE_PRODUCTS . " where `products_id` ='" . $product_id . "'");
      $products_discounts_query = $db->Execute("select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where `products_id` ='" . $product_id . "' and `discount_qty` <='" . $check_qty . "' order by `discount_qty` desc");

      $display_price = zen_get_products_base_price($product_id);
      $display_specials_price = zen_get_products_special_price($product_id, true);

      switch ($products_query->fields['products_discount_type']) {
        // none
        case ($products_discounts_query->EOF):
          //no discount applies
          $discounted_price = zen_get_products_actual_price($product_id);
          break;
        case '0':
          $discounted_price = zen_get_products_actual_price($product_id);
          break;
        // percentage discount
        case '1':
          if ($products_query->fields['products_discount_type_from'] == '0') {
            // priced by attributes
            if ($check_amount != 0) {
              $discounted_price = $check_amount - ($check_amount * ($products_discounts_query->fields['discount_price']/100));
//echo 'ID#' . $product_id . ' Amount is: ' . $check_amount . ' discount: ' . $discounted_price . '<br />';
//echo 'I SEE 2 for ' . $products_query->fields['products_discount_type'] . ' - ' . $products_query->fields['products_discount_type_from'] . ' - '. $check_amount . ' new: ' . $discounted_price . ' qty: ' . $check_qty;
            } else {
              $discounted_price = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
            }
          } else {
            if (!$display_specials_price) {
              // priced by attributes
              if ($check_amount != 0) {
                $discounted_price = $check_amount - ($check_amount * ($products_discounts_query->fields['discount_price']/100));
              } else {
                $discounted_price = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
              }
            } else {
              $discounted_price = $display_specials_price - ($display_specials_price * ($products_discounts_query->fields['discount_price']/100));
            }
          }

          break;
        // actual price
        case '2':
          if ($products_query->fields['products_discount_type_from'] == '0') {
            $discounted_price = $products_discounts_query->fields['discount_price'];
          } else {
            $discounted_price = $products_discounts_query->fields['discount_price'];
          }
          break;
        // amount offprice
        case '3':
          if ($products_query->fields['products_discount_type_from'] == '0') {
            $discounted_price = $display_price - $products_discounts_query->fields['discount_price'];
          } else {
            if (!$display_specials_price) {
              $discounted_price = $display_price - $products_discounts_query->fields['discount_price'];
            } else {
              $discounted_price = $display_specials_price - $products_discounts_query->fields['discount_price'];
            }
          }
          break;
      }

      return $discounted_price;
  }

?>
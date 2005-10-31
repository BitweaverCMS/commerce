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
// $Id: functions_prices.php,v 1.7 2005/10/31 16:19:57 lsces Exp $
//
//
////
/*  function zen_get_products_special_price($product_id, $specials_price_only=false) {
    global $db;
    $product = $db->Execute("select products_price, products_model, products_priced_by_attribute from " . TABLE_PRODUCTS . " where `products_id` = '" . (int)$product_id . "'");

    if ($product->RecordCount() > 0) {
//  	  $product_price = $product->fields['products_price'];
  	  $product_price = zen_get_products_base_price($product_id);
    } else {
  	  return false;
    }

    $specials = $db->Execute("select `specials_new_products_price` from " . TABLE_SPECIALS . " where `products_id` = '" . (int)$product_id . "' and status='1'");
    if ($specials->RecordCount() > 0) {
//      if ($product->fields['products_priced_by_attribute'] == 1) {
    	  $special_price = $specials->fields['specials_new_products_price'];
    } else {
  	  $special_price = false;
    }

    if(substr($product->fields['products_model'], 0, 4) == 'GIFT') {    //Never apply a salededuction to Ian Wilson's Giftvouchers
      if (zen_not_null($special_price)) {
        return $special_price;
      } else {
        return false;
      }
    }

// return special price only
    if ($specials_price_only==true) {
      if (zen_not_null($special_price)) {
        return $special_price;
      } else {
        return false;
      }
    } else {
// get sale price

// changed to use master_categories_id
//      $product_to_categories = $db->Execute("select `categories_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `products_id` = '" . (int)$product_id . "'");
//      $category = $product_to_categories->fields['categories_id'];

      $product_to_categories = $db->Execute("select `master_categories_id` from " . TABLE_PRODUCTS . " where `products_id` = '" . $product_id . "'");
      $category = $product_to_categories->fields['master_categories_id'];

      $sale = $db->Execute("select sale_specials_condition, sale_deduction_value, sale_deduction_type from " . TABLE_SALEMAKER_SALES . " where sale_categories_all like '%," . $category . ",%' and sale_status = '1' and (sale_date_start <= now() or sale_date_start = '0001-01-01') and (sale_date_end >= now() or sale_date_end = '0001-01-01') and (sale_pricerange_from <= '" . $product_price . "' or sale_pricerange_from = '0') and (sale_pricerange_to >= '" . $product_price . "' or sale_pricerange_to = '0')");
      if ($sale->RecordCount() < 1) {
         return $special_price;
      }

      if (!$special_price) {
        $tmp_special_price = $product_price;
      } else {
        $tmp_special_price = $special_price;
      }
      switch ($sale->fields['sale_deduction_type']) {
        case 0:
          $sale_product_price = $product_price - $sale->fields['sale_deduction_value'];
          $sale_special_price = $tmp_special_price - $sale->fields['sale_deduction_value'];
          break;
        case 1:
          $sale_product_price = $product_price - (($product_price * $sale->fields['sale_deduction_value']) / 100);
          $sale_special_price = $tmp_special_price - (($tmp_special_price * $sale->fields['sale_deduction_value']) / 100);
          break;
        case 2:
          $sale_product_price = $sale->fields['sale_deduction_value'];
          $sale_special_price = $sale->fields['sale_deduction_value'];
          break;
        default:
          return $special_price;
      }

      if ($sale_product_price < 0) {
        $sale_product_price = 0;
      }

      if ($sale_special_price < 0) {
        $sale_special_price = 0;
      }

      if (!$special_price) {
        return number_format($sale_product_price, 4, '.', '');
    	} else {
        switch($sale->fields['sale_specials_condition']){
          case 0:
            return number_format($sale_product_price, 4, '.', '');
            break;
          case 1:
            return number_format($special_price, 4, '.', '');
            break;
          case 2:
            return number_format($sale_special_price, 4, '.', '');
            break;
          default:
            return number_format($special_price, 4, '.', '');
        }
      }
    }
  }
*/


////
// Return a products quantity minimum and units display
  function zen_get_products_quantity_min_units_display($product_id, $include_break = true, $shopping_cart_msg = false) {
    $check_min = zen_get_products_quantity_order_min($product_id);
    $check_units = zen_get_products_quantity_order_units($product_id);

    $the_min_units='';

    if ($check_min != 1 or $check_units != 1) {
      if ($check_min != 1) {
        $the_min_units .= PRODUCTS_QUANTITY_MIN_TEXT_LISTING . '&nbsp;' . $check_min;
      }
      if ($check_units != 1) {
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

// NOT USED
echo '<br />' . 'I SHOULD use zen_get_discount_calc' . '<br />';

/*

0 = flat amount off base price with a special
1 = Percentage off base price with a special
2 = New Price with a special

5 = No Sale or Skip Products with Special

special options + option * 10
0 = Ignore special and apply to Price
1 = Skip Products with Specials switch to 5
2 = Apply to Special Price

If a special exist * 10

0+7 + 0+10 = flat apply to price = 17 or 170
0+7 + 1+10 = flat skip Specials = 5 or 50
0+7 + 2+10 = flat apply to special = 27 or 270

1+7 + 0+10 = Percentage apply to price = 18 or 180
1+7 + 1+10 = Percentage skip Specials = 5 or 50
1+7 + 2+10 = Percentage apply to special = 20 or 200

2+7 + 0+10 = New Price apply to price = 19 or 190
2+7 + 1+10 = New Price skip Specials = 5 or 50
2+7 + 2+10 = New Price apply to Special = 21 or 210

*/

/*
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
*/

  }

////
// compute discount based on qty
  function zen_get_products_discount_price_qty($product_id, $check_qty, $check_amount=0) {
    global $db, $cart;
      $new_qty = $_SESSION['cart']->in_cart_mixed_discount_quantity($product_id);
      // check for discount qty mix
      if ($new_qty > $check_qty) {
        $check_qty = $new_qty;
      }
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


////
// salemaker categories array
  function zen_parse_salemaker_categories($clist) {
    $clist_array = explode(',', $clist);

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($clist_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($clist_array[$i], $tmp_array)) {
        $tmp_array[] = $clist_array[$i];
      }
    }
    return $tmp_array;
  }

////
// update salemaker product prices per category per product
  function zen_update_salemaker_product_prices($salemaker_id) {
    global $db;
    $zv_categories = $db->Execute("select `sale_categories_selected` from " . TABLE_SALEMAKER_SALES . " where `sale_id` = '" . $salemaker_id . "'");

    $za_salemaker_categories = zen_parse_salemaker_categories($zv_categories->fields['sale_categories_selected']);
    $n = sizeof($za_salemaker_categories);
    for ($i=0; $i<$n; $i++) {
      $update_products_price = $db->Execute("select `products_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `categories_id`='" . $za_salemaker_categories[$i] . "'");
      while (!$update_products_price->EOF) {
        zen_update_products_price_sorter($update_products_price->fields['products_id']);
        $update_products_price->MoveNext();
      }
    }
  }

?>

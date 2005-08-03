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
// $Id: shopping_cart.php,v 1.6 2005/08/03 13:04:38 spiderr Exp $
//

  class shoppingCart {
    var $contents, $total, $weight, $cartID, $content_type, $free_shipping_item, $free_shipping_weight, $free_shipping_price;

    function shoppingCart() {
      $this->reset();
    }

    function restore_contents() {
      global $db;

      if (!$_SESSION['customer_id']) return false;

// insert current cart contents in database
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
//          $products_id = urldecode($products_id);
          $qty = $this->contents[$products_id]['qty'];
          $product_query = "select products_id
                            from " . TABLE_CUSTOMERS_BASKET . "
                            where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                            and products_id = '" . zen_db_input($products_id) . "'";

          $product = $db->Execute($product_query);

          if ($product->RecordCount()<=0) {
            $sql = "insert into " . TABLE_CUSTOMERS_BASKET . "
                                (customers_id, products_id, customers_basket_quantity,
                                 customers_basket_date_added)
                                 values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
                                 $qty . "', '" . date('Ymd') . "')";

            $db->Execute($sql);

            if (isset($this->contents[$products_id]['attributes'])) {
              reset($this->contents[$products_id]['attributes']);
              while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {

                //clr 031714 udate query to include attribute value. This is needed for text attributes.
                $attr_value = $this->contents[$products_id]['attributes_values'][$option];
//                zen_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id, products_options_value_text) values ('" . (int)$customer_id . "', '" . zen_db_input($products_id) . "', '" . (int)$option . "', '" . (int)$value . "', '" . zen_db_input($attr_value) . "')");
                $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $value);
                if ($attr_value) {
          $attr_value = zen_db_input($attr_value);
        }
                $sql = "insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                    (customers_id, products_id, products_options_id,
                                     products_options_value_id, products_options_value_text, products_options_sort_order)
                                     values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
                                     $option . "', '" . $value . "', '" . $attr_value . "', '" . $products_options_sort_order . "')";

                $db->Execute($sql);
              }
            }
          } else {
            $sql = "update " . TABLE_CUSTOMERS_BASKET . "
                    set customers_basket_quantity = '" . $qty . "'
                    where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                    and products_id = '" . zen_db_input($products_id) . "'";

            $db->Execute($sql);

          }
        }
      }

// reset per-session cart contents, but not the database contents
      $this->reset(false);

      $products_query = "select products_id, customers_basket_quantity
                         from " . TABLE_CUSTOMERS_BASKET . "
                         where customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $products = $db->Execute($products_query);

      while (!$products->EOF) {
        $this->contents[$products->fields['products_id']] = array('qty' => $products->fields['customers_basket_quantity']);
// attributes
// set contents in sort order

        //CLR 020606 update query to pull attribute value_text. This is needed for text attributes.
//        $attributes_query = zen_db_query("select products_options_id, products_options_value_id, products_options_value_text from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . zen_db_input($products['products_id']) . "'");

        $order_by = ' order by LPAD(products_options_sort_order,11,"0")';

        $attributes = $db->Execute("select products_options_id, products_options_value_id, products_options_value_text
                             from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                             where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                             and products_id = '" . zen_db_input($products->fields['products_id']) . "' " . $order_by);

        while (!$attributes->EOF) {
          $this->contents[$products->fields['products_id']]['attributes'][$attributes->fields['products_options_id']] = $attributes->fields['products_options_value_id'];
          //CLR 020606 if text attribute, then set additional information
          if ($attributes->fields['products_options_value_id'] == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
            $this->contents[$products->fields['products_id']]['attributes_values'][$attributes->fields['products_options_id']] = $attributes->fields['products_options_value_text'];
          }
          $attributes->MoveNext();
        }
        $products->MoveNext();
      }

      $this->cleanup();
    }

    function reset($reset_database = false) {
      global $db, $gBitUser;

      $this->contents = array();
      $this->total = 0;
      $this->weight = 0;
      $this->content_type = false;

// shipping adjustment
      $this->free_shipping_item = 0;
      $this->free_shipping_price = 0;
      $this->free_shipping_weight = 0;

      if( $gBitUser->isRegistered() && ($reset_database == true)) {
        $sql = "delete from " . TABLE_CUSTOMERS_BASKET . " where `customers_id` = ?";
        $db->query($sql, array( $gBitUser->mUserId ) );

        $sql = "delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = ?";
        $db->query($sql, array( $gBitUser->mUserId ) );
      }

      unset($this->cartID);
      $_SESSION['cartID'] = '';
    }

    function add_cart($products_id, $qty = '1', $attributes = '', $notify = true) {
      global $db;
      $products_id = zen_get_uprid($products_id, $attributes);
      if ($notify == true) {
        $_SESSION['new_products_id_in_cart'] = $products_id;
      }

      if ($this->in_cart($products_id)) {
        $this->update_quantity($products_id, $qty, $attributes);
      } else {
        $this->contents[] = array($products_id);
        $this->contents[$products_id] = array('qty' => $qty);
// insert into database
        if ($_SESSION['customer_id']) {
          $sql = "insert into " . TABLE_CUSTOMERS_BASKET . "
                              (customers_id, products_id, customers_basket_quantity,
                              customers_basket_date_added)
                              values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
                              $qty . "', '" . date('Ymd') . "')";

          $db->Execute($sql);
        }

        if (is_array($attributes)) {
          reset($attributes);
          while (list($option, $value) = each($attributes)) {
            //CLR 020606 check if input was from text box.  If so, store additional attribute information
            //CLR 020708 check if text input is blank, if so do not add to attribute lists
            //CLR 030228 add htmlspecialchars processing.  This handles quotes and other special chars in the user input.
            $attr_value = NULL;
            $blank_value = FALSE;
            if (strstr($option, TEXT_PREFIX)) {
              if (trim($value) == NULL) {
                $blank_value = TRUE;
              } else {
                $option = substr($option, strlen(TEXT_PREFIX));
                $attr_value = stripslashes($value);
                $value = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
                $this->contents[$products_id]['attributes_values'][$option] = $attr_value;
              }
            }

            if (!$blank_value) {
              if (is_array($value) ) {
                reset($value);
                while (list($opt, $val) = each($value)) {
                  $this->contents[$products_id]['attributes'][$option.'_chk'.$val] = $val;
                }
              } else {
                $this->contents[$products_id]['attributes'][$option] = $value;
              }
// insert into database
            //CLR 020606 update db insert to include attribute value_text. This is needed for text attributes.
            //CLR 030228 add zen_db_input() processing
              if ($_SESSION['customer_id']) {

//              if (zen_session_is_registered('customer_id')) zen_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id, products_options_value_text) values ('" . (int)$customer_id . "', '" . zen_db_input($products_id) . "', '" . (int)$option . "', '" . (int)$value . "', '" . zen_db_input($attr_value) . "')");
                if (is_array($value) ) {
                  reset($value);
                  while (list($opt, $val) = each($value)) {
                    $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $opt);
                    $sql = "insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                        (customers_id, products_id, products_options_id, products_options_value_id, products_options_sort_order)
                                        values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
                                        (int)$option.'_chk'.$val . "', '" . $val . "',  '" . $products_options_sort_order . "')";

                    $db->Execute($sql);
                  }
                } else {
                  if ($attr_value) {
                    $attr_value = zen_db_input($attr_value);
                  }
                  $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $value);
                  $sql = "insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                                      (customers_id, products_id, products_options_id, products_options_value_id, products_options_value_text, products_options_sort_order)
                                      values ('" . (int)$_SESSION['customer_id'] . "', '" . zen_db_input($products_id) . "', '" .
                                      (int)$option . "', '" . $value . "', '" . $attr_value . "', '" . $products_options_sort_order . "')";

                  $db->Execute($sql);
                }
              }
            }
          }
        }
      }
      $this->cleanup();

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
      $this->cartID = $this->generate_cart_id();
    }

    function update_quantity($products_id, $quantity = '', $attributes = '') {
      global $db;

      if (empty($quantity)) return true; // nothing needs to be updated if theres no quantity, so we return true..

      $this->contents[$products_id] = array('qty' => $quantity);
// update database
      if ($_SESSION['customer_id']) {
        $sql = "update " . TABLE_CUSTOMERS_BASKET . "
                set customers_basket_quantity = '" . $quantity . "'
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                and products_id = '" . zen_db_input($products_id) . "'";

        $db->Execute($sql);

      }

      if (is_array($attributes)) {
        reset($attributes);
        while (list($option, $value) = each($attributes)) {
          //CLR 020606 check if input was from text box.  If so, store additional attribute information
          //CLR 030108 check if text input is blank, if so do not update attribute lists
          //CLR 030228 add htmlspecialchars processing.  This handles quotes and other special chars in the user input.
          $attr_value = NULL;
          $blank_value = FALSE;
          if (strstr($option, TEXT_PREFIX)) {
            if (trim($value) == NULL) {
              $blank_value = TRUE;
            } else {
              $option = substr($option, strlen(TEXT_PREFIX));
              $attr_value = stripslashes($value);
              $value = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
              $this->contents[$products_id]['attributes_values'][$option] = $attr_value;
            }
          }

          if (!$blank_value) {
            if (is_array($value) ) {
              reset($value);
              while (list($opt, $val) = each($value)) {
                $this->contents[$products_id]['attributes'][$option.'_chk'.$val] = $val;
              }
            } else {
              $this->contents[$products_id]['attributes'][$option] = $value;
            }
// update database
            //CLR 020606 update db insert to include attribute value_text. This is needed for text attributes.
            //CLR 030228 add zen_db_input() processing
//          if (zen_session_is_registered('customer_id')) zen_db_query("update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " set products_options_value_id = '" . (int)$value . "', products_options_value_text = '" . zen_db_input($attr_value) . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . zen_db_input($products_id) . "' and products_options_id = '" . (int)$option . "'");

            if ($attr_value) {
              $attr_value = zen_db_input($attr_value);
            }
            if (is_array($value) ) {
              reset($value);
              while (list($opt, $val) = each($value)) {
                $products_options_sort_order= zen_get_attributes_options_sort_order(zen_get_prid($products_id), $option, $opt);
                $sql = "update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                        set products_options_value_id = '" . $val . "'
                        where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                        and products_id = '" . zen_db_input($products_id) . "'
                        and products_options_id = '" . (int)$option.'_chk'.$val . "'";

                $db->Execute($sql);
              }
            } else {
              if ($_SESSION['customer_id']) {
                $sql = "update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                        set products_options_value_id = '" . $value . "', products_options_value_text = '" . $attr_value . "'
                        where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                        and products_id = '" . zen_db_input($products_id) . "'
                        and products_options_id = '" . (int)$option . "'";

                $db->Execute($sql);
              }
            }
          }
        }
      }
    }

    function cleanup() {
      global $db;

      reset($this->contents);
      while (list($key,) = each($this->contents)) {
        if (empty( $this->contents[$key]['qty'] ) || $this->contents[$key]['qty'] <= 0) {
          unset($this->contents[$key]);
// remove from database
          if ($_SESSION['customer_id']) {
            $sql = "delete from " . TABLE_CUSTOMERS_BASKET . "
                    where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                    and products_id = '" . $key . "'";

            $db->Execute($sql);

            $sql = "delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                    where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                    and products_id = '" . $key . "'";

            $db->Execute($sql);
          }
        }
      }
    }

    function count_contents() {  // get total number of items in cart
      $total_items = 0;
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $total_items += $this->get_quantity($products_id);
        }
      }

      return $total_items;
    }

    function get_quantity($products_id) {
      if (isset($this->contents[$products_id])) {
        return $this->contents[$products_id]['qty'];
      } else {
        return 0;
      }
    }

    function in_cart($products_id) {
//  die($products_id);
      if (isset($this->contents[$products_id])) {
        return true;
      } else {
        return false;
      }
    }

    function remove($products_id) {
      global $db;
//die($products_id);
      //CLR 030228 add call zen_get_uprid to correctly format product ids containing quotes
//      $products_id = zen_get_uprid($products_id, $attributes);
      unset($this->contents[$products_id]);
// remove from database
      if ($_SESSION['customer_id']) {

//        zen_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . zen_db_input($products_id) . "'");

        $sql = "delete from " . TABLE_CUSTOMERS_BASKET . "
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                and products_id = '" . zen_db_input($products_id) . "'";

        $db->Execute($sql);

//        zen_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . zen_db_input($products_id) . "'");

        $sql = "delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
                where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                and products_id = '" . zen_db_input($products_id) . "'";

        $db->Execute($sql);

      }

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
      $this->cartID = $this->generate_cart_id();
    }

    function remove_all() {
      $this->reset();
    }

    function get_product_id_list() {
      $product_id_list = '';
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $product_id_list .= ', ' . zen_db_input($products_id);
        }
      }

      return substr($product_id_list, 2);
    }

// calculates totals
    function calculate() {
      global $db;
      $this->total = 0;
      $this->weight = 0;

// shipping adjustment
      $this->free_shipping_item = 0;
      $this->free_shipping_price = 0;
      $this->free_shipping_weight = 0;

      if (!is_array($this->contents)) return 0;

      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
        $qty = $this->contents[$products_id]['qty'];

// products price
        $product_query = "select products_id, products_price, products_tax_class_id, products_weight,
                          products_priced_by_attribute, product_is_always_free_ship, products_discount_type, products_discount_type_from,
                          products_virtual, products_model
                          from " . TABLE_PRODUCTS . "
                          where products_id = '" . (int)$products_id . "'";

        if ($product = $db->Execute($product_query)) {
          $prid = $product->fields['products_id'];
          $products_tax = zen_get_tax_rate($product->fields['products_tax_class_id']);
          $products_price = $product->fields['products_price'];

          // adjusted count for free shipping
          if ($product->fields['product_is_always_free_ship'] != 1 and $product->fields['products_virtual'] != 1) {
            $products_weight = $product->fields['products_weight'];
          } else {
            $products_weight = 0;
          }

          $special_price = zen_get_products_special_price($prid);
          if ($special_price and $product->fields['products_priced_by_attribute'] == 0) {
            $products_price = $special_price;
          } else {
            $special_price = 0;
          }

          if (zen_get_products_price_is_free($product->fields['products_id'])) {
            // no charge
            $products_price = 0;
          }

// adjust price for discounts when priced by attribute
          if ($product->fields['products_priced_by_attribute'] == '1' and zen_has_product_attributes($product->fields['products_id'], 'false')) {
            // reset for priced by attributes
//            $products_price = $products->fields['products_price'];
            if ($special_price) {
              $products_price = $special_price;
            } else {
              $products_price = $product->fields['products_price'];
            }
          } else {
// discount qty pricing
            if( !empty($product->fields['products_discount_type'] ) ) {
              $products_price = zen_get_products_discount_price_qty($product->fields['products_id'], $qty);
            }
          }

// shipping adjustments
          if (($product->fields['product_is_always_free_ship'] == 1) or ($product->fields['products_virtual'] == 1) or (ereg('^GIFT', addslashes($product->fields['products_model'])))) {
            $this->free_shipping_item += $qty;
            $this->free_shipping_price += zen_add_tax($products_price, $products_tax) * $qty;
            $this->free_shipping_weight += ($qty * $products_weight);
          }

          $this->total += zen_add_tax($products_price, $products_tax) * $qty;
          $this->weight += ($qty * $products_weight);
        }

// attributes price
        if (isset($this->contents[$products_id]['attributes'])) {
          reset($this->contents[$products_id]['attributes']);
          while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
/*
                                      products_attributes_id, options_values_price, price_prefix,
                                      attributes_display_only, product_attribute_is_free,
                                      attributes_discounted
*/

            $attribute_price_query = "select *
                                      from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                      where products_id = '" . (int)$prid . "'
                                      and options_id = '" . (int)$option . "'
                                      and options_values_id = '" . (int)$value . "'";

            $attribute_price = $db->Execute($attribute_price_query);

            $new_attributes_price = 0;
            $discount_type_id = '';
            $sale_maker_discount = '';

// bottom total
//            if ($attribute_price->fields['product_attribute_is_free']) {
            if ($attribute_price->fields['product_attribute_is_free'] == '1' and zen_get_products_price_is_free((int)$prid)) {
              // no charge for attribute
            } else {
// + or blank adds
              if ($attribute_price->fields['price_prefix'] == '-') {
                if ($attribute_price->fields['attributes_discounted'] == '1') {
// calculate proper discount for attributes
                  $new_attributes_price = zen_get_discount_calc($product->fields['products_id'], $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
                  $this->total -= $qty * zen_add_tax( ($new_attributes_price), $products_tax);
                } else {
                  $this->total -= $qty * zen_add_tax($attribute_price->fields['options_values_price'], $products_tax);
                }
              } else {
                if ($attribute_price->fields['attributes_discounted'] == '1') {
// calculate proper discount for attributes
                  $new_attributes_price = zen_get_discount_calc($product->fields['products_id'], $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
                  $this->total += $qty * zen_add_tax( ($new_attributes_price), $products_tax);
                } else {
                  $this->total += $qty * zen_add_tax($attribute_price->fields['options_values_price'], $products_tax);
                }
              }

////////////////////////////////////////////////
// calculate additional attribute charges
              $chk_price = zen_get_products_base_price($products_id);
              $chk_special = zen_get_products_special_price($products_id, false);
// products_options_value_text
              if (zen_get_attributes_type($attribute_price->fields['products_attributes_id']) == PRODUCTS_OPTIONS_TYPE_TEXT) {
                  $text_words = zen_get_word_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_words_free'], $attribute_price->fields['attributes_price_words']);
                  $text_letters = zen_get_letters_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_letters_free'], $attribute_price->fields['attributes_price_letters']);

                  $this->total += $qty * zen_add_tax($text_letters, $products_tax);
                  $this->total += $qty * zen_add_tax($text_words, $products_tax);
              }

// attributes_price_factor
              $added_charge = 0;
              if ($attribute_price->fields['attributes_price_factor'] > 0) {
                $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_price_factor'], $attribute_price->fields['attributes_pf_offset']);

                $this->total += $qty * zen_add_tax($added_charge, $products_tax);
              }
// attributes_qty_prices
              $added_charge = 0;
              if ($attribute_price->fields['attributes_qty_prices'] != '') {
                $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices'], $qty);

                $this->total += $qty * zen_add_tax($added_charge, $products_tax);
              }

//// one time charges
// attributes_price_onetime
              if ($attribute_price->fields['attributes_price_onetime'] > 0) {
                $this->total += zen_add_tax($attribute_price->fields['attributes_price_onetime'], $products_tax);
              }
// attributes_pf_onetime
              $added_charge = 0;
              if ($attribute_price->fields['attributes_pf_onetime'] > 0) {
                $chk_price = zen_get_products_base_price($products_id);
                $chk_special = zen_get_products_special_price($products_id, false);
                $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_pf_onetime'], $attribute_price->fields['attributes_pf_onetime_offset']);

                $this->total += zen_add_tax($added_charge, $products_tax);
              }
// attributes_qty_prices_onetime
              $added_charge = 0;
              if ($attribute_price->fields['attributes_qty_prices_onetime'] != '') {
                $chk_price = zen_get_products_base_price($products_id);
                $chk_special = zen_get_products_special_price($products_id, false);
                $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices_onetime'], $qty);
                $this->total += zen_add_tax($added_charge, $products_tax);
              }
////////////////////////////////////////////////
            }
          }
        } // attributes price

// attributes weight
        if (isset($this->contents[$products_id]['attributes'])) {
          reset($this->contents[$products_id]['attributes']);
          while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
            $attribute_weight_query = "select products_attributes_wt, products_attributes_wt_pfix
                                       from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                       where products_id = '" . (int)$prid . "'
                                       and options_id = '" . (int)$option . "'
                                       and options_values_id = '" . (int)$value . "'";

            $attribute_weight = $db->Execute($attribute_weight_query);

          // adjusted count for free shipping
          if ($product->fields['product_is_always_free_ship'] != 1) {
            $new_attributes_weight = $attribute_weight->fields['products_attributes_wt'];
          } else {
            $new_attributes_weight = 0;
          }

// + or blank adds
            if ($attribute_weight->fields['products_attributes_wt_pfix'] == '-') {
              $this->weight -= $qty * $new_attributes_weight;
            } else {
              $this->weight += $qty * $new_attributes_weight;
            }
          }
        } // attributes weight

      }
    }

    function attributes_price($products_id) {
      global $db;

      $attributes_price = 0;
      $qty = $this->contents[$products_id]['qty'];

      if (isset($this->contents[$products_id]['attributes'])) {

        reset($this->contents[$products_id]['attributes']);
        while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {

          $attribute_price_query = "select *
                                    from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                    where products_id = '" . (int)$products_id . "'
                                    and options_id = '" . (int)$option . "'
                                    and options_values_id = '" . (int)$value . "'";

          $attribute_price = $db->Execute($attribute_price_query);

          $new_attributes_price = 0;
          $discount_type_id = '';
          $sale_maker_discount = '';

//          if ($attribute_price->fields['product_attribute_is_free']) {
          if ($attribute_price->fields['product_attribute_is_free'] == '1' and zen_get_products_price_is_free((int)$products_id)) {
            // no charge
          } else {
// + or blank adds
            if ($attribute_price->fields['price_prefix'] == '-') {
// calculate proper discount for attributes
              if ($attribute_price->fields['attributes_discounted'] == '1') {
                $discount_type_id = '';
                $sale_maker_discount = '';
                $new_attributes_price = zen_get_discount_calc($products_id, $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
                $attributes_price -= ($new_attributes_price);
              } else {
                $attributes_price -= $attribute_price->fields['options_values_price'];
              }
            } else {
              if ($attribute_price->fields['attributes_discounted'] == '1') {
// calculate proper discount for attributes
                $discount_type_id = '';
                $sale_maker_discount = '';
                $new_attributes_price = zen_get_discount_calc($products_id, $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);
                $attributes_price += ($new_attributes_price);
              } else {
                $attributes_price += $attribute_price->fields['options_values_price'];
              }
            }

//////////////////////////////////////////////////
// calculate additional charges
// products_options_value_text
              if (zen_get_attributes_type($attribute_price->fields['products_attributes_id']) == PRODUCTS_OPTIONS_TYPE_TEXT) {
                  $text_words = zen_get_word_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_words_free'], $attribute_price->fields['attributes_price_words']);
                  $text_letters = zen_get_letters_count_price($this->contents[$products_id]['attributes_values'][$attribute_price->fields['options_id']], $attribute_price->fields['attributes_price_letters_free'], $attribute_price->fields['attributes_price_letters']);
                  $attributes_price += $text_letters;
                  $attributes_price += $text_words;
              }
// attributes_price_factor
              $added_charge = 0;
              if ($attribute_price->fields['attributes_price_factor'] > 0) {
                $chk_price = zen_get_products_base_price($products_id);
                $chk_special = zen_get_products_special_price($products_id, false);
                $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_price_factor'], $attribute_price->fields['attributes_pf_offset']);
                $attributes_price += $added_charge;
              }
// attributes_qty_prices
              $added_charge = 0;
              if ($attribute_price->fields['attributes_qty_prices'] != '') {
                $chk_price = zen_get_products_base_price($products_id);
                $chk_special = zen_get_products_special_price($products_id, false);
                $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices'], $this->contents[$products_id]['qty']);
                $attributes_price += $added_charge;
              }

//////////////////////////////////////////////////
          }
// Validate Attributes
          if ($attribute_price->fields['attributes_display_only']) {
            $_SESSION['valid_to_checkout'] = false;
            $_SESSION['cart_errors'] .= zen_get_products_name($attribute_price->fields['products_id'], $_SESSION['languages_id'])  . ERROR_PRODUCT_OPTION_SELECTION . '<br />';
          }
/*
//// extra testing not required on text attribute this is done in application_top before it gets to the cart
          if ($attribute_price->fields['attributes_required']) {
            $_SESSION['valid_to_checkout'] = false;
            $_SESSION['cart_errors'] .= zen_get_products_name($attribute_price->fields['products_id'], $_SESSION['languages_id'])  . ERROR_PRODUCT_OPTION_SELECTION . '<br />';
          }
*/
        }
      }

      return $attributes_price;
    }


// one time attribute prices
// add to tpl_shopping_cart/orders
    function attributes_price_onetime_charges($products_id, $qty) {
      global $db;

      $attributes_price_onetime = 0;

      if (isset($this->contents[$products_id]['attributes'])) {

        reset($this->contents[$products_id]['attributes']);
        while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {

          $attribute_price_query = "select *
                                    from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                    where products_id = '" . (int)$products_id . "'
                                    and options_id = '" . (int)$option . "'
                                    and options_values_id = '" . (int)$value . "'";

          $attribute_price = $db->Execute($attribute_price_query);

          $new_attributes_price = 0;
          $discount_type_id = '';
          $sale_maker_discount = '';

//          if ($attribute_price->fields['product_attribute_is_free']) {
          if ($attribute_price->fields['product_attribute_is_free'] == '1' and zen_get_products_price_is_free((int)$products_id)) {
            // no charge
          } else {
            $discount_type_id = '';
            $sale_maker_discount = '';
            $new_attributes_price = zen_get_discount_calc($products_id, $attribute_price->fields['products_attributes_id'], $attribute_price->fields['options_values_price'], $qty);

//////////////////////////////////////////////////
// calculate additional one time charges
//// one time charges
// attributes_price_onetime
              if ($attribute_price->fields['attributes_price_onetime'] > 0) {
if ((int)$products_id != $products_id) {
  die('I DO NOT MATCH ' . $products_id);
}
                $attributes_price_onetime += $attribute_price->fields['attributes_price_onetime'];
              }
// attributes_pf_onetime
              $added_charge = 0;
              if ($attribute_price->fields['attributes_pf_onetime'] > 0) {
                $chk_price = zen_get_products_base_price($products_id);
                $chk_special = zen_get_products_special_price($products_id, false);
                $added_charge = zen_get_attributes_price_factor($chk_price, $chk_special, $attribute_price->fields['attributes_pf_onetime'], $attribute_price->fields['attributes_pf_onetime_offset']);

                $attributes_price_onetime += $added_charge;
              }
// attributes_qty_prices_onetime
              $added_charge = 0;
              if ($attribute_price->fields['attributes_qty_prices_onetime'] != '') {
                $chk_price = zen_get_products_base_price($products_id);
                $chk_special = zen_get_products_special_price($products_id, false);
                $added_charge = zen_get_attributes_qty_prices_onetime($attribute_price->fields['attributes_qty_prices_onetime'], $qty);
                $attributes_price_onetime += $added_charge;
              }

//////////////////////////////////////////////////
          }
        }
      }

      return $attributes_price_onetime;
    }


    function attributes_weight($products_id) {
      global $db;

      $attribute_weight = 0;

      if (isset($this->contents[$products_id]['attributes'])) {
        reset($this->contents[$products_id]['attributes']);
        while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
          $attribute_weight_query = "select products_attributes_wt, products_attributes_wt_pfix
                                    from " . TABLE_PRODUCTS_ATTRIBUTES . "
                                    where products_id = '" . (int)$products_id . "'
                                    and options_id = '" . (int)$option . "'
                                    and options_values_id = '" . (int)$value . "'";

          $attribute_weight_info = $db->Execute($attribute_weight_query);

          // adjusted count for free shipping
          $product = $db->Execute("select products_id, product_is_always_free_ship
                          from " . TABLE_PRODUCTS . "
                          where products_id = '" . (int)$products_id . "'");

          if ($product->fields['product_is_always_free_ship'] != 1) {
            $new_attributes_weight = $attribute_weight_info->fields['products_attributes_wt'];
          } else {
            $new_attributes_weight = 0;
          }

// + or blank adds
          if ($attribute_weight_info->fields['products_attributes_wt_pfix'] == '-') {
            $attribute_weight -= $new_attributes_weight;
          } else {
            $attribute_weight += $attribute_weight_info->fields['products_attributes_wt'];
          }
        }
      }

      return $attribute_weight;
    }


    function get_products($check_for_valid_cart = false) {
      global $db;

      if (!is_array($this->contents)) return false;

      $products_array = array();
      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
        if( $product = CommerceProduct::getProduct( $products_id ) ) {

          $prid = $product['products_id'];
          $products_price = $product['products_price'];
//fix here
/*
          $special_price = zen_get_products_special_price($prid);
          if ($special_price) {
            $products_price = $special_price;
          }
*/
          $special_price = zen_get_products_special_price($prid);
          if ($special_price and $product['products_priced_by_attribute'] == 0) {
            $products_price = $special_price;
          } else {
            $special_price = 0;
          }

          if (zen_get_products_price_is_free($product['products_id'])) {
            // no charge
            $products_price = 0;
          }

// adjust price for discounts when priced by attribute
          if ($product['products_priced_by_attribute'] == '1' and zen_has_product_attributes($product['products_id'], 'false')) {
            // reset for priced by attributes
//            $products_price = $product['products_price'];
            if ($special_price) {
              $products_price = $special_price;
            } else {
              $products_price = $product['products_price'];
            }
          } else {
// discount qty pricing
            if ( !empty( $product->fields['products_discount_type'] ) ) {
              $products_price = zen_get_products_discount_price_qty($product['products_id'], $this->contents[$products_id]['qty']);
            }
          }
            if ($check_for_valid_cart == true) {
                $check_quantity = $this->contents[$products_id]['qty'];
                $check_quantity_min = $product['products_quantity_order_min'];
              // Check quantity min
                if ($new_check_quantity = $this->in_cart_mixed($prid) ) {
                  $check_quantity = $new_check_quantity;
                }

                $fix_once = 0;
                if ($check_quantity < $check_quantity_min) {
                  $fix_once ++;
                  $_SESSION['valid_to_checkout'] = false;
                  $_SESSION['cart_errors'] .= ERROR_PRODUCT . $product['products_name'] . ERROR_PRODUCT_QUANTITY_MIN_SHOPPING_CART . ERROR_PRODUCT_QUANTITY_ORDERED . $check_quantity  . ' <span class="alertBlack">' . zen_get_products_quantity_min_units_display((int)$prid, false, true) . '</span> ' . '<br />';
                }

              // Check Quantity Units if not already an error on Quantity Minimum
                if ($fix_once == 0) {
                  $check_units = $product['products_quantity_order_units'];
                  if ( fmod($check_quantity,$check_units) != 0 ) {
                    $_SESSION['valid_to_checkout'] = false;
                    $_SESSION['cart_errors'] .= ERROR_PRODUCT . $product['products_name'] . ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART . ERROR_PRODUCT_QUANTITY_ORDERED . $check_quantity  . ' <span class="alertBlack">' . zen_get_products_quantity_min_units_display((int)$prid, false, true) . '</span> ' . '<br />';
                  }
                }

              // Verify Valid Attributes
            }

          //clr 030714 update $products_array to include attribute value_text. This is needed for text attributes.

// convert quantity to proper decimals
          if (QUANTITY_DECIMALS != 0) {
//          $new_qty = round($new_qty, QUANTITY_DECIMALS);

            $fix_qty = $this->contents[$products_id]['qty'];
            switch (true) {
            case (!strstr($fix_qty, '.')):
              $new_qty = $fix_qty;
              break;
            default:
              $new_qty = preg_replace('/[0]+$/','',$this->contents[$products_id]['qty']);
              break;
            }
          } else {
            $new_qty = $this->contents[$products_id]['qty'];
          }

          $new_qty = round($new_qty, QUANTITY_DECIMALS);

          if ($new_qty == (int)$new_qty) {
            $new_qty = (int)$new_qty;
          }

          $products_array[] = array('id' => $products_id,
                                    'name' => $product['products_name'],
                                    'model' => $product['products_model'],
                                    'image' => $product['products_image'],
                                    'image_url' => $product['products_image_url'],
                                    'price' => ($product['product_is_free'] =='1' ? 0 : $products_price),
//                                    'quantity' => $this->contents[$products_id]['qty'],
                                    'quantity' => $new_qty,
                                    'weight' => $product['products_weight'] + $this->attributes_weight($products_id),
// fix here
                                    'final_price' => ($products_price + $this->attributes_price($products_id)),
                                    'onetime_charges' => ($this->attributes_price_onetime_charges($products_id, $new_qty)),
                                    'tax_class_id' => $product['products_tax_class_id'],
                                    'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : ''),
                                    'attributes_values' => (isset($this->contents[$products_id]['attributes_values']) ? $this->contents[$products_id]['attributes_values'] : ''),
                                    'products_priced_by_attribute' => $product['products_priced_by_attribute'],
                                    'product_is_free' => $product['product_is_free'],
                                    'products_discount_type' => $product['products_discount_type'],
                                    'products_discount_type_from' => $product['products_discount_type_from']);
        }
      }

      return $products_array;
    }

    function show_total() {
      $this->calculate();

      return $this->total;
    }

    function show_weight() {
      $this->calculate();

      return $this->weight;
    }

    function generate_cart_id($length = 5) {
      return zen_create_random_value($length, 'digits');
    }

    function get_content_type($gv_only = 'false') {
      global $db;

      $this->content_type = false;
      $gift_voucher = 0;

//      if ( (DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0) ) {
      if ( $this->count_contents() > 0 ) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $free_ship_check = $db->Execute("select products_virtual, products_model, products_price from " . TABLE_PRODUCTS . " where products_id = '" . zen_get_prid($products_id) . "'");
          $virtual_check = false;
          if (ereg('^GIFT', addslashes($free_ship_check->fields['products_model']))) {
            $gift_voucher += ($free_ship_check->fields['products_price'] + $this->attributes_price($products_id)) * $this->contents[$products_id]['qty'];
          }
          if (isset($this->contents[$products_id]['attributes'])) {
            reset($this->contents[$products_id]['attributes']);
            while (list(, $value) = each($this->contents[$products_id]['attributes'])) {
              $virtual_check_query = "select count(*) as total
                                      from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, "
                                             . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                      where pa.products_id = '" . (int)$products_id . "'
                                      and pa.options_values_id = '" . (int)$value . "'
                                      and pa.products_attributes_id = pad.products_attributes_id";

              $virtual_check = $db->Execute($virtual_check_query);

              if ($virtual_check->fields['total'] > 0) {
                switch ($this->content_type) {
                  case 'physical':
                    $this->content_type = 'mixed';
                      if ($gv_only == 'true') {
                        return $gift_voucher;
                      } else {
                        return $this->content_type;
                      }
                    break;
                  default:
                    $this->content_type = 'virtual';
                    break;
                }
              } else {
                switch ($this->content_type) {
                  case 'virtual':
                    if ($free_ship_check->fields['products_virtual'] == '1') {
                      $this->content_type = 'virtual';
                    } else {
                      $this->content_type = 'mixed';
                      if ($gv_only == 'true') {
                        return $gift_voucher;
                      } else {
                        return $this->content_type;
                      }
                    }
                    break;
                  case 'physical':
                    if ($free_ship_check->fields['products_virtual'] == '1') {
                      $this->content_type = 'mixed';
                      if ($gv_only == 'true') {
                        return $gift_voucher;
                      } else {
                        return $this->content_type;
                      }
                    } else {
                      $this->content_type = 'physical';
                    }
                    break;
                  default:
                    if ($free_ship_check->fields['products_virtual'] == '1') {
                      $this->content_type = 'virtual';
                    } else {
                      $this->content_type = 'physical';
                    }
                }
              }
            }
          } else {
            switch ($this->content_type) {
              case 'virtual':
                if ($free_ship_check->fields['products_virtual'] == '1') {
                  $this->content_type = 'virtual';
                } else {
                  $this->content_type = 'mixed';
                  if ($gv_only == 'true') {
                    return $gift_voucher;
                  } else {
                    return $this->content_type;
                  }
                }
                break;
              case 'physical':
                if ($free_ship_check->fields['products_virtual'] == '1') {
                  $this->content_type = 'mixed';
                  if ($gv_only == 'true') {
                    return $gift_voucher;
                  } else {
                    return $this->content_type;
                  }
                 } else {
                  $this->content_type = 'physical';
                 }
                break;
              default:
                if ($free_ship_check->fields['products_virtual'] == '1') {
                  $this->content_type = 'virtual';
                 } else {
                  $this->content_type = 'physical';
                 }
            }
          }
        }
      } else {
        $this->content_type = 'physical';
      }

      if ($gv_only == 'true') {
        return $gift_voucher;
      } else {
        return $this->content_type;
      }
    }

    function unserialize($broken) {
      for(reset($broken);$kv=each($broken);) {
        $key=$kv['key'];
        if (gettype($this->$key)!="user function")
        $this->$key=$kv['value'];
      }
    }

// check mixed min/units
    function in_cart_mixed($products_id) {
      global $db;
      // if nothing is in cart return 0
      if (!is_array($this->contents)) return 0;

		if( is_array( $products_id ) ) {
			$products_id = current( $products_id );
		}
      // check if mixed is on
//      $product = $db->Execute("select products_id, products_quantity_mixed from " . TABLE_PRODUCTS . " where products_id='" . (int)$products_id . "' limit 1");
      $product = $db->Execute("select products_id, products_quantity_mixed from " . TABLE_PRODUCTS . " where products_id='" . $products_id . "' limit 1");

      // if mixed attributes is off return qty for current attribute selection
      if ($product->fields['products_quantity_mixed'] == '0') {
        return $this->get_quantity($products_id);
      }

      // compute total quantity regardless of attributes
      $in_cart_mixed_qty = 0;
      $chk_products_id= zen_get_prid($products_id);

      // reset($this->contents); // breaks cart
      $check_contents = $this->contents;
      while (list($products_id, ) = each($check_contents)) {
        $test_id = zen_get_prid($products_id);
        if ($test_id == $chk_products_id) {
          $in_cart_mixed_qty += $check_contents[$products_id]['qty'];
        }
      }
      return $in_cart_mixed_qty;
    }

// check mixed discount_quantity
    function in_cart_mixed_discount_quantity($products_id) {
      global $db;
      // if nothing is in cart return 0
      if (!is_array($this->contents)) return 0;

      // check if mixed is on
//      $product = $db->Execute("select products_id, products_mixed_discount_quantity from " . TABLE_PRODUCTS . " where products_id='" . (int)$products_id . "' limit 1");
      $product = $db->Execute("select products_id, products_mixed_discount_quantity from " . TABLE_PRODUCTS . " where products_id='" . zen_get_prid($products_id) . "' limit 1");

      // if mixed attributes is off return qty for current attribute selection
      if ($product->fields['products_mixed_discount_quantity'] == '0') {
        return $this->get_quantity($products_id);
      }

      // compute total quantity regardless of attributes
      $in_cart_mixed_qty_discount_quantity = 0;
      $chk_products_id= zen_get_prid($products_id);

      // reset($this->contents); // breaks cart
      $check_contents = $this->contents;
      while (list($products_id, ) = each($check_contents)) {
        $test_id = zen_get_prid($products_id);
        if ($test_id == $chk_products_id) {
          $in_cart_mixed_qty_discount_quantity += $check_contents[$products_id]['qty'];
        }
      }
      return $in_cart_mixed_qty_discount_quantity;
    }

// $check_what is the fieldname example: 'products_is_free'
// $check_value is the value being tested for - default is 1
// Syntax: $_SESSION['cart']->in_cart_check('product_is_free','1');
    function in_cart_check($check_what, $check_value='1') {
      global $db;
      // if nothing is in cart return 0
      if (!is_array($this->contents)) return 0;

      // compute total quantity for field
      $in_cart_check_qty=0;

      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
        $testing_id = zen_get_prid($products_id);
        // check if field it true
        $product_check = $db->Execute("select " . $check_what . " as check_it from " . TABLE_PRODUCTS . " where products_id='" . $testing_id . "' limit 1");
        if ($product_check->fields['check_it'] == $check_value) {
          $in_cart_check_qty += $this->contents[$products_id]['qty'];
        }
      }
      return $in_cart_check_qty;
    }

// gift voucher only
    function gv_only() {
      $gift_voucher = $this->get_content_type(true);
      return $gift_voucher;
    }

// shipping adjustment
    function free_shipping_items() {
      $this->calculate();

      return $this->free_shipping_item;
    }

    function free_shipping_prices() {
      $this->calculate();

      return $this->free_shipping_price;
    }

    function free_shipping_weight() {
      $this->calculate();

      return $this->free_shipping_weight;
    }

  }
?>

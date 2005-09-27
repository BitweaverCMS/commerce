<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
// $Id: products_discount_prices.php,v 1.2 2005/09/27 22:33:53 spiderr Exp $
//

  require(DIR_FS_MODULES . 'require_languages.php');

// if customer authorization is on do not show discounts

  $zc_hidden_discounts_on = false;
  $zc_hidden_discounts_text = '';
  switch (true) {
    case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
    // customer must be logged in to browse
    $zc_hidden_discounts_on = true;
    $zc_hidden_discounts_text = 'MUST LOGIN';
    break;
    case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
    // customer may browse but no prices
    $zc_hidden_discounts_on = true;
    $zc_hidden_discounts_text = TEXT_LOGIN_FOR_PRICE_PRICE;
    break;
    case (CUSTOMERS_APPROVAL == '3' and TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM != ''):
    // customer may browse but no prices
    $zc_hidden_discounts_on = true;
    $zc_hidden_discounts_text = TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
    break;
    case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customer_id'] == ''):
    // customer must be logged in to browse
    $zc_hidden_discounts_on = true;
    $zc_hidden_discounts_text = TEXT_AUTHORIZATION_PENDING_PRICE;
    break;
    case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customers_authorization'] > '0'):
    // customer must be logged in to browse
    $zc_hidden_discounts_on = true;
    $zc_hidden_discounts_text = TEXT_AUTHORIZATION_PENDING_PRICE;
    break;
    default:
    // proceed normally
    break;
  }

// hide discounts is on
  if ($zc_hidden_discounts_on) {
?>
  <table border="0" cellspacing="2" cellpadding="2" class="plainBox" align="center">
    <tr>
      <td colspan="1" align="center">
      <?php echo TEXT_HEADER_DISCOUNTS_OFF; ?>
      </td>
    </tr>

    <tr>
      <td colspan="1" align="center">
      <?php echo $zc_hidden_discounts_text; ?>
      </td>
    </tr>
  </table>
<?php
  } else {
// create products discount table

  $products_discounts_query = $db->Execute("select * from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id='" . $products_id_current . "' and discount_qty !=0 " . " order by discount_qty");

  $discount_col_cnt = DISCOUNT_QUANTITY_PRICES_COLUMN;
?>
  <table border="0" cellspacing="2" cellpadding="2" class="plainBox" align="center">
    <tr>
      <td colspan="<?php echo $discount_col_cnt; ?>" align="center">
<?php
  switch ($products_discount_type) {
    case '1':
      echo TEXT_HEADER_DISCOUNT_PRICES_PERCENTAGE;
      break;
    case '2':
      echo TEXT_HEADER_DISCOUNT_PRICES_ACTUAL_PRICE;
      break;
    case '3':
      echo TEXT_HEADER_DISCOUNT_PRICES_AMOUNT_OFF;
      break;
  }
?>
      </td>
    </tr>

    <tr>
<?php
  $display_price = zen_get_products_base_price($products_id_current);
  $display_specials_price = zen_get_products_special_price($products_id_current, true);

// set first price value
  if ($display_specials_price == false) {
    $show_price = $display_price;
  } else {
    $show_price = $display_specials_price;
  }

  switch (true) {
    case ($products_discounts_query->fields['discount_qty'] <= 2):
      $show_qty = '1';
      break;
    default:
      $products_discount_query = $db->Execute("select products_quantity_order_min from " . TABLE_PRODUCTS . " where products_id='" . $products_id_current . "'");
      $show_qty = $products_discount_query->fields['products_quantity_order_min'] . '-' . number_format($products_discounts_query->fields['discount_qty']-1);
      break;
  }
?>
  <td class="main" align="center"><?php echo $show_qty . '<br />' . $currencies->display_price($show_price, zen_get_tax_rate($products_tax_class_id)); ?></td>

<?php
//$discounted_price = $products_discounts_query->fields['discount_price'];
// $currencies->display_price($discounted_price, zen_get_tax_rate(1), 1)
  $display_price = zen_get_products_base_price($products_id_current);
  $display_specials_price = zen_get_products_special_price($products_id_current, true);
  $disc_cnt = 1;
  while (!$products_discounts_query->EOF) {
      $disc_cnt++;
      switch ($products_discount_type) {
        // none
        case '0':
          $discounted_price = 0;
          break;
        // percentage discount
        case '1':
          if ($products_discount_type_from == '0') {
            $discounted_price = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
          } else {
            if (!$display_specials_price) {
              $discounted_price = $display_price - ($display_price * ($products_discounts_query->fields['discount_price']/100));
            } else {
              $discounted_price = $display_specials_price - ($display_specials_price * ($products_discounts_query->fields['discount_price']/100));
            }
          }

          break;
        // actual price
        case '2':
          if ($products_discount_type_from == '0') {
            $discounted_price = $products_discounts_query->fields['discount_price'];
          } else {
            $discounted_price = $products_discounts_query->fields['discount_price'];
          }
          break;
        // amount offprice
        case '3':
          if ($products_discount_type_from == '0') {
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

      $show_qty = number_format($products_discounts_query->fields['discount_qty']);
      $products_discounts_query->MoveNext();
      if ($products_discounts_query->EOF) {
        $show_qty .= '+';
      } else {
        if (($products_discounts_query->fields['discount_qty']-1) != $show_qty) {
          $show_qty .= '-' . number_format($products_discounts_query->fields['discount_qty']-1);
        }
      }
      echo '<td class="main" align="center">' . $show_qty . '<br />' . $currencies->display_price($discounted_price, zen_get_tax_rate($products_tax_class_id)) . '</td>';
      if ($discount_col_cnt == $disc_cnt and !$products_discounts_query->EOF) {
        echo '</tr><tr>';
        $disc_cnt=0;
      }
    }
?>
    </tr>
<?php
  if (zen_has_product_attributes($products_id_current)) {
?>
    <tr>
      <td colspan="<?php echo $discount_col_cnt; ?>" align="center">
        <?php echo TEXT_FOOTER_DISCOUNT_QUANTITIES; ?>
      </td>
    </tr>
<?php } ?>
  </table>
<?php } // hide discounts ?>
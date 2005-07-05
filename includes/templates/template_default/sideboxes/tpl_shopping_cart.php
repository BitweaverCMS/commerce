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
// $Id: tpl_shopping_cart.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

  if ($_SESSION['cart']->count_contents() > 0) {
  
  $id = shoppingcart;
  $content ="";
  if ($_SESSION['cart']->count_contents() > 0) {
    $content = '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $content .= '<tr><td align="right" valign="top" class="infoboxcontents">';

      if (($_SESSION['new_products_id_in_cart']) && ($_SESSION['new_products_id_in_cart'] == $products[$i]['id'])) {
        $content .= '<span class="newItemInCart">';
      } else {
        $content .= '<span class="infoboxcontents">';
      }

      $content .= $products[$i]['quantity'] . '&nbsp;x&nbsp;</span></td><td valign="top" class="infoboxcontents"><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">';

      if (($_SESSION['new_products_id_in_cart']) && ($_SESSION['new_products_id_in_cart'] == $products[$i]['id'])) {
        $content .= '<span class="newItemInCart">';
      } else {
        $content .= '<span class="infoboxcontents">';
      }

      $content .= $products[$i]['name'] . '</span></a></td></tr>';

      if (($_SESSION['new_products_id_in_cart']) && ($_SESSION['new_products_id_in_cart'] == $products[$i]['id'])) {
        $_SESSION['new_products_id_in_cart'] = '';
      }
    }
    $content .= '</table>';

  if ($_SESSION['customer_id']) {
    $gv_query = "select amount 
                 from " . TABLE_COUPON_GV_CUSTOMER . " 
                 where customer_id = '" . $_SESSION['customer_id'] . "'";

    $gv_result = $db->Execute($gv_query);

    if ($gv_result->fields['amount'] > 0 ) {
      $content .= zen_draw_separator();
      $content .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . VOUCHER_BALANCE . '</td><td class="smalltext" align="right" valign="bottom">' . $currencies->format($gv_result->fields['amount']) . '</td></tr></table>';
      $content .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext"><a href="'. zen_href_link(FILENAME_GV_SEND) . '">' . BOX_SEND_TO_FRIEND . '</a></td></tr></table>';
    }
  }
  if ($_SESSION['gv_id']) {
    $gv_query = "select coupon_amount 
                 from " . TABLE_COUPONS . " 
                 where coupon_id = '" . $_SESSION['gv_id'] . "'";

    $coupon = $db->Execute($gv_query);
    $content .= zen_draw_separator();
    $content .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . VOUCHER_REDEEMED . '</td><td class="smalltext" align="right" valign="bottom">' . $currencies->format($coupon->fields['coupon_amount']) . '</td></tr></table>';

  }
  if ($_SESSION['cc_id']) {
    $content .= zen_draw_separator();
    $content .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . CART_COUPON . '</td><td class="smalltext" align="right" valign="bottom">' . '<a href="javascript:couponpopupWindow(\'' . zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $_SESSION['cc_id']) . '\')">' . CART_COUPON_INFO . '</a>' . '</td></tr></table>';
  }

  } else {
    $content = BOX_SHOPPING_CART_EMPTY;
  }

  if ($_SESSION['cart']->count_contents() > 0) {
    $content .= zen_draw_separator();
    $content .= $currencies->format($_SESSION['cart']->show_total());
  }
  } else {
      $content = '';

  }
?>
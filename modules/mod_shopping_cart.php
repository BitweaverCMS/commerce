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
// $Id: mod_shopping_cart.php,v 1.6 2005/08/19 17:12:34 spiderr Exp $
//
	global $db, $gBitProduct, $currencies;

if( !empty( $_SESSION['cart'] ) && is_object( $_SESSION['cart'] ) ) {
  switch (true) {
    case (SHOW_SHOPPING_CART_BOX_STATUS == '0'):
      $show_shopping_cart_box = true;
      break;
    case (SHOW_SHOPPING_CART_BOX_STATUS == '1'):
      if( $_SESSION['cart']->count_contents() > 0 || ($gBitUser->isRegistered() && (zen_user_has_gv_account( $gBitUser->mUserId ) > 0) ) ) {
        $show_shopping_cart_box = true;
      } else {
        $show_shopping_cart_box = false;
      }
      break;
    case (SHOW_SHOPPING_CART_BOX_STATUS == '2'):
      if ( ( ($_SESSION['cart']->count_contents() > 0) || (zen_user_has_gv_account($_SESSION['customer_id']) > 0) ) && ($_GET['main_page'] != FILENAME_SHOPPING_CART) ) {
        $show_shopping_cart_box = true;
      } else {
        $show_shopping_cart_box = false;
      }
      break;
    }


  if ($show_shopping_cart_box == true) {
  if ($_SESSION['cart']->count_contents() > 0) {

  $id = 'shoppingcart';
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

      $content .= $products[$i]['quantity'] . '&nbsp;x&nbsp;</span></td><td valign="top" class="infoboxcontents"><a href="' . CommerceProduct::getDisplayUrl( $products[$i]['id'] ) . '">';

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
  if( !empty( $_SESSION['gv_id'] ) ) {
    $gv_query = "select coupon_amount
                 from " . TABLE_COUPONS . "
                 where coupon_id = '" . $_SESSION['gv_id'] . "'";

    $coupon = $db->Execute($gv_query);
    $content .= zen_draw_separator();
    $content .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . VOUCHER_REDEEMED . '</td><td class="smalltext" align="right" valign="bottom">' . $currencies->format($coupon->fields['coupon_amount']) . '</td></tr></table>';

  }
  if( !empty( $_SESSION['cc_id'] ) ) {
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
  $gBitSmarty->assign( 'sideboxShoppingCartContent', $content );
	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle', tra( 'Shopping Cart' ) );
	}
  }
}
?>

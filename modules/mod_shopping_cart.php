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
// $Id$
//

global $gBitDb, $gBitProduct, $currencies, $gBitUser, $gBitCustomer;
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceVoucher.php' );

if( !empty( $gBitCustomer->mCart ) && is_object( $gBitCustomer->mCart ) ) {
  switch (true) {
    case (SHOW_SHOPPING_CART_BOX_STATUS == '0'):
      $show_shopping_cart_box = true;
      break;
    case (SHOW_SHOPPING_CART_BOX_STATUS == '1'):
      if( $gBitCustomer->mCart->count_contents() > 0 || ($gBitUser->isRegistered() && !empty( $gBitCustomer ) &&  ($gBitCustomer->getGiftBalance() > 0) ) ) {
        $show_shopping_cart_box = true;
      } else {
        $show_shopping_cart_box = false;
      }
      break;
    case (SHOW_SHOPPING_CART_BOX_STATUS == '2'):
      if ( ( ($gBitCustomer->mCart->count_contents() > 0) || ($gBitCustomer->getGiftBalance() > 0) ) && ($_GET['main_page'] != FILENAME_SHOPPING_CART) ) {
        $show_shopping_cart_box = true;
      } else {
        $show_shopping_cart_box = false;
      }
      break;
    }


  if ($show_shopping_cart_box == true) {
  if ($gBitCustomer->mCart->count_contents() > 0) {

  $id = 'shoppingcart';
  $content ="";
  if ($gBitCustomer->mCart->count_contents() > 0) {
    $content = '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
    foreach( array_keys( $gBitCustomer->mCart->contents ) as $productKey ) {
      $product = $gBitCustomer->mCart->getProductObject( $productKey );

      $productId = zen_get_prid( $productKey );
      $content .= '<tr><td align="right" valign="top" class="infoboxcontents">';

      if( !empty( $_SESSION['new_products_id_in_cart'] ) && ($_SESSION['new_products_id_in_cart'] == $productId)) {
        $content .= '<span class="newItemInCart">';
      } else {
        $content .= '<span class="infoboxcontents">';
      }

      $content .= $gBitCustomer->mCart->contents[$productKey]['products_quantity'] . '&nbsp;x&nbsp;</span></td><td valign="top" class="infoboxcontents"><a href="' . CommerceProduct::getDisplayUrlFromId( zen_get_prid( $productId ) ) . '">';

      if ( !empty( $_SESSION['new_products_id_in_cart'] ) && ($_SESSION['new_products_id_in_cart'] == $productId)) {
        $content .= '<span class="newItemInCart">';
      } else {
        $content .= '<span class="infoboxcontents">';
      }

      $content .= $product->getTitle() . '</span></a></td></tr>';

      if ( !empty( $_SESSION['new_products_id_in_cart'] ) && ($_SESSION['new_products_id_in_cart'] == $productId)) {
        $_SESSION['new_products_id_in_cart'] = '';
      }
    }
    $content .= '</table>';

  if( $gvBalance = CommerceVoucher::getGiftAmount() ) {
      $content .= '<table style="margin-top:10px;" cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . tra( 'Gift Certificate Balance' ) . '</td><td class="smalltext" align="right" valign="bottom">' . $gvBalance . '</td></tr></table>';
      $content .= '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext"><a href="'. zen_href_link(FILENAME_GV_SEND) . '">' . tra( 'Send Gift Certificate' ) . '</a></td></tr></table>';
  }
  if( $couponAmount = CommerceVoucher::getCouponAmount() ) {
    $content .= '<table style="margin-top:10px;" cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . VOUCHER_REDEEMED . '</td><td class="smalltext" align="right" valign="bottom">' . $couponAmount . '</td></tr></table>';
  }
  if( !empty( $_SESSION['cc_id'] ) ) {
    $content .= '<table style="margin-top:10px;" cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . CART_COUPON . '</td><td class="smalltext" align="right" valign="bottom">' . '<a href="javascript:couponpopupWindow(\'' . zen_href_link(FILENAME_POPUP_COUPON_HELP, 'cID=' . $_SESSION['cc_id']) . '\')">' . CART_COUPON_INFO . '</a>' . '</td></tr></table>';
  }

  } else {
    $content = BOX_SHOPPING_CART_EMPTY;
  }

  if ($gBitCustomer->mCart->count_contents() > 0) {
    $content .= zen_draw_separator();
    $content .= $currencies->format($gBitCustomer->mCart->show_total());
  }
  } else {
      $content = '';

  }
  $_template->tpl_vars['sideboxShoppingCartContent'] = new Smarty_variable( $content );
	if( empty( $moduleTitle ) ) {
		$_template->tpl_vars['moduleTitle'] = new Smarty_variable( tra( 'Shopping Cart' ) );
	}
  }
}
?>

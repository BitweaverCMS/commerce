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
// $Id: tpl_gv_faq_default.php,v 1.1 2005/07/05 05:59:04 bitweaver Exp $
//
?>
<h1><?php echo HEADING_TITLE; ?></h1>
<?php echo TEXT_INFORMATION; ?>
<h2><?php echo SUB_HEADING_TITLE; ?></h2>
<?php echo SUB_HEADING_TEXT; ?> 
<?php
// only show when there is a GV balance
  $gv_query = "select amount
               from " . TABLE_COUPON_GV_CUSTOMER . "
               where customer_id = '" . $_SESSION['customer_id'] . "'";
  $gv_result = $db->Execute($gv_query);

  if ($gv_result->fields['amount'] > 0 ) {
?>
   <h2><?php echo BOX_HEADING_GIFT_VOUCHER; ?><h2>
   
<?php
// ADDED FOR CREDIT CLASS GV END ADDITION
  if ($_SESSION['customer_id']) {
    $gv_query = "select amount
                 from " . TABLE_COUPON_GV_CUSTOMER . "
                 where customer_id = '" . $_SESSION['customer_id'] . "'";
    $gv_result = $db->Execute($gv_query);

    if ($gv_result->fields['amount'] > 0 ) {

?>

 <p><?php echo VOUCHER_BALANCE; ?><?php echo $currencies->format($gv_result->fields['amount']); ?><?php echo '<a href="' . zen_href_link(FILENAME_GV_SEND) . '">' . BOX_SEND_TO_FRIEND . '</a>'; ?></p>
<?php
  }
?>
<?php
    }
  }
  if ($_SESSION['gv_id']) {
    $gv_query = "select coupon_amount
                 from " . TABLE_COUPONS . "
                 where coupon_id = '" . $_SESSION['gv_id'] . "'";

    $coupon = $db->Execute($gv_query);
?>
  <p><?php echo VOUCHER_REDEEMED; ?><?php echo $currencies->format($coupon->fields['coupon_amount']); ?></p>
<?php
  }
?>

<?php $back = sizeof($_SESSION['navigation']->path)-2; ?>
<div class="row">
<span class="right"><?php echo '<a href="' . zen_href_link($_SESSION['navigation']->path[$back]['page'], zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']) . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></span>
</div>
<br class="clear" />
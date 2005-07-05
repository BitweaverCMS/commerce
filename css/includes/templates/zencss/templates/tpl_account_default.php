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
// $Id: tpl_account_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<h1><?php echo HEADING_TITLE; ?></h1>
<?php if ($messageStack->size('account') >0) { echo $messageStack->output('account'); }
	  if (zen_count_customer_orders() > 0) { ?>

<h2><?php echo OVERVIEW_PREVIOUS_ORDERS; ?></h2>
<?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . OVERVIEW_SHOW_ALL_ORDERS . '</a>'; ?> 
<?php require(DIR_WS_BLOCKS . 'blk_previous_orders.php'); ?>
<?php } ?>

<h2><?php echo MY_ACCOUNT_TITLE; ?></h2>
<ul>
  <li><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . MY_ACCOUNT_INFORMATION . '</a>'; ?></li>
  <li><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a>'; ?></li>
  <li><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL') . '">' . MY_ACCOUNT_PASSWORD . '</a>'; ?></li>
</ul>
<h2><?php echo EMAIL_NOTIFICATIONS_TITLE; ?></h2>
<ul>
  <li><?php echo ' <a href="' . zen_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEWSLETTERS . '</a>'; ?></li>
  <li><?php echo ' <a href="' . zen_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_PRODUCTS . '</a>'; ?></li>
</ul>
<?php
// only show when there is a GV balance
  $gv_query = "select amount
               from " . TABLE_COUPON_GV_CUSTOMER . "
               where customer_id = '" . $_SESSION['customer_id'] . "'";
  $gv_result = $db->Execute($gv_query);

  if ($gv_result->fields['amount'] > 0 ) {
?>

<h2><?php echo BOX_HEADING_GIFT_VOUCHER; ?></h2>

<?php
// ADDED FOR CREDIT CLASS GV END ADDITION
  if ($_SESSION['customer_id']) {
    $gv_query = "select amount
                 from " . TABLE_COUPON_GV_CUSTOMER . "
                 where customer_id = '" . $_SESSION['customer_id'] . "'";
    $gv_result = $db->Execute($gv_query);

    if ($gv_result->fields['amount'] > 0 ) {

?>

<p><?php echo VOUCHER_BALANCE; ?>:
<?php echo $currencies->format($gv_result->fields['amount']); ?></p>
<?php echo '<a href="' . zen_href_link(FILENAME_GV_SEND) . '">' . BOX_SEND_TO_FRIEND . '</a>'; ?>
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
<p><?php echo VOUCHER_REDEEMED; ?>:
<?php echo $currencies->format($coupon->fields['coupon_amount']); ?></p>
<?php
  }
?>
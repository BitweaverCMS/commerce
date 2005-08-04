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
// $Id: tpl_account.php,v 1.1 2005/08/04 07:01:02 spiderr Exp $
//
// Variables passed to this page from header_php.php
//
// $customer_has_gv_balance
// $customer_gv_balance
//
?>

<table  width="100%" border="0" cellspacing="2" cellpadding="2" class="centerColumn">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <?php
    if ($messageStack->size('account') >0) {
  ?>
  <tr>
    <td class="main" colspan="2"><?php echo $messageStack->output('account'); ?></td>
  </tr>
  <?php
    }

    if (zen_count_customer_orders() > 0) {
  ?>
  <tr>
    <td class="plainBoxHeading"><?php echo OVERVIEW_PREVIOUS_ORDERS; ?></td>
    <td class="main" align="right" valign="bottom"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . OVERVIEW_SHOW_ALL_ORDERS . '</a>'; ?></td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="2" cellpadding="2">
	<?php require(DIR_WS_BLOCKS . 'blk_previous_orders.php'); ?>
      </table>
    </td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo MY_ACCOUNT_TITLE; ?></td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="2" cellpadding="2">
        <tr>
          <td class="main">
            <?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ACCOUNT_ARROW) . ' <a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a>'; ?>
          </td>
        </tr>
<?php
  if (SHOW_NEWSLETTER_UNSUBSCRIBE_LINK=='true') {
?>
        <tr>
          <td class="main" colspan="2">
            <?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ACCOUNT_ARROW) . ' <a href="' . zen_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEWSLETTERS . '</a>'; ?>
          </td>
        </tr>
<?php } ?>
<?php
  if (CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1') {
?>
        <tr>
          <td class="main" colspan="2">
            <?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ACCOUNT_ARROW) . ' <a href="' . zen_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_PRODUCTS . '</a>'; ?>
          </td>
        </tr>
<?php } ?>
      </table>
    </td>
  </tr>
<?php
// only show when there is a GV balance
  if ($customer_has_gv_balance ) {
?>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo BOX_HEADING_GIFT_VOUCHER; ?></td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="2" cellpadding="2">
        <tr>
          <td class="main"><?php echo VOUCHER_BALANCE; ?></td>
          <td class="main"><?php echo $customer_gv_balance; ?></td>
          <td class="main" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_GV_SEND) . '">' . BOX_SEND_TO_FRIEND . '</a>'; ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php
  }
?>
</table>

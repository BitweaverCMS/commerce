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
//// $Id: tpl_gv_faq.php,v 1.1 2005/08/04 07:01:34 spiderr Exp $
//
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="main" height="10px"></td>
  </tr>
  <tr>
    <td class="main"><?php echo TEXT_INFORMATION; ?></td>
  </tr>
  <tr>
    <td class="main"><strong><?php echo SUB_HEADING_TITLE; ?></strong></td>
  </tr>
  <tr>
    <td class="main"><?php echo SUB_HEADING_TEXT; ?></td>
  </tr>
<?php
// only show when there is a GV balance
  if ($has_gv_balance ) {
?>
  <tr>
    <td class="main" height="10px"></td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo BOX_HEADING_GIFT_VOUCHER; ?></td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="2" cellpadding="2">
        <tr>
          <td class="main"><?php echo VOUCHER_BALANCE; ?></td>
          <td class="main"><?php echo $currencies->format($gv_result->fields['amount']); ?></td>
          <td class="main" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_GV_SEND) . '">' . BOX_SEND_TO_FRIEND . '</a>'; ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php
  }
  if ($_SESSION['gv_id']) {
?>
  <tr>
    <td class="main"><?php echo VOUCHER_REDEEMED; ?></td>
    <td class="main" align="right" valign="bottom"><?php echo $currencies->format($coupon->fields['coupon_amount']); ?></td>
  </tr>
<?php
  }
?>

  <tr>
    <td class="main" height="10px"></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
  </tr>
</table>

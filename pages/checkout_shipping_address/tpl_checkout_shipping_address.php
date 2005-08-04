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
// $Id: tpl_checkout_shipping_address.php,v 1.1 2005/08/04 07:01:27 spiderr Exp $
//
?>
<?php echo zen_draw_form('checkout_address', zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), 'post', 'onsubmit="return check_form_optional(checkout_address);"'); ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="3"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
  if ($messageStack->size('checkout_address') > 0) {
?>
  <tr>
    <td class="main" colspan="3"><?php echo $messageStack->output('checkout_address'); ?></td>
  </tr>
<?php
  }

  if ($process == false || $error == true) {
?>
  <tr>
    <td class="main" align="center" valign="top"><?php echo TITLE_SHIPPING_ADDRESS . '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ARROW_SOUTH_EAST); ?></td>
    <td class="main" width="35%" valign="top"><?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'); ?></td>
    <td class="main" valign="top"><?php if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) echo TEXT_CREATE_NEW_SHIPPING_ADDRESS; ?></td>
  </tr>
<?php
  if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
?>
  <tr>
    <td class="plainBox" colspan="3"><?php require(DIR_WS_MODULES . 'checkout_new_address.php'); ?></td>
  </tr>
<?php
    }
    if ($addresses_count > 1) {
?>
  <tr>
    <td class="plainBoxHeading" colspan="3"><?php echo TABLE_HEADING_ADDRESS_BOOK_ENTRIES; ?></td>
  </tr>
  <tr>
    <td class="main" valign="top" colspan="2"><?php echo TEXT_SELECT_OTHER_SHIPPING_DESTINATION; ?></td>
    <td class="main" valign="top" align="right"><?php echo TITLE_PLEASE_SELECT . '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ARROW_EAST_SOUTH); ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
      require(DIR_WS_BLOCKS . 'blk_checkout_shipping_address.php');
    }
  }

?>
  <tr>
    <td class="main" colspan="2"><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></td>
    <td class="main" align="right"><?php echo zen_draw_hidden_field('action', 'submit') . zen_image_submit(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT); ?></td>
  </tr>
<?php
  if ($process == true) {
?>
  <tr>
    <td class="main" colspan="3"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
  </tr>
<?php
  }
?>
</table></form>

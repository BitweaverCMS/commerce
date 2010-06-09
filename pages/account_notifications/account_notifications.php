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
// $Id$
//
?>
<?php echo zen_draw_form('account_notifications', zen_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL')) . zen_draw_hidden_field('action', 'process'); ?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <?php echo MY_NOTIFICATIONS_DESCRIPTION; ?>
    </td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="2">
      <?php echo GLOBAL_NOTIFICATIONS_TITLE; ?>
    </td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="1" cellpadding="1">
        <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="checkBox('product_global')">
          <td width="15" valign="bottom"><?php echo zen_draw_checkbox_field('product_global', '1', (($global->fields['global_product_notifications'] == '1') ? true : false), 'onclick="checkBox(\'product_global\')"'); ?></td>
          <td class="plainBoxHeading"><?php echo GLOBAL_NOTIFICATIONS_TITLE; ?></td>
        </tr>
        <tr>
          <td width="15">&nbsp;</td>
          <td class="main"><?php echo GLOBAL_NOTIFICATIONS_DESCRIPTION; ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php
  if ($global->fields['global_product_notifications'] != '1') {
?>
  <tr>
    <td class="plainBoxHeading" colspan="2">
      <?php echo NOTIFICATIONS_TITLE; ?>
    </td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="1" cellpadding="1">
<?php

    if ($flag_products_check) {
?>
        <tr>
          <td colspan="2"><?php echo NOTIFICATIONS_DESCRIPTION; ?></td>
        </tr>
<?php
      require(DIR_FS_BLOCKS . 'blk_account_notifications.php');
    } else {
?>
        <tr>
          <td class="main"><?php echo NOTIFICATIONS_NON_EXISTING; ?></td>
        </tr>
<?php
    }
?>
      </table>
    </td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
    <td align="right"><?php echo zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT); ?></td>
  </tr>
</table></form>

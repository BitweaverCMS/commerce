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
// $Id: tpl_account_password_default.php,v 1.2 2005/07/14 04:55:15 spiderr Exp $
//
?>
<?php echo zen_draw_form('account_password', zen_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'), 'post', 'onsubmit="return check_form(account_password);"') . zen_draw_hidden_field('action', 'process'); ?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
<?php
  if ($messageStack->size('account_password') > 0) {
?>
  <tr>
    <td colspan="2"><?php echo $messageStack->output('account_password'); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="plainBox" colspan="2">
      <table  width="100%" border="0" cellspacing="2" cellpadding="2">
        <tr>
          <td class="main"><?php echo ENTRY_PASSWORD_CURRENT; ?></td>
          <td class="main">
            <?php echo zen_draw_password_field('password_current') . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_CURRENT_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CURRENT_TEXT . '&nbsp;&nbsp;&nbsp;' . FORM_REQUIRED_INFORMATION . '</span>': ''); ?>
          </td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_PASSWORD_NEW; ?></td>
          <td class="main">
            <?php echo zen_draw_password_field('password_new') . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_NEW_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_NEW_TEXT . '</span>': ''); ?>
          </td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
          <td class="main">
            <?php echo zen_draw_password_field('password_confirmation') . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="left">
      <?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?>
    </td>
    <td align="right">
      <?php echo zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?>
    </td>
  </tr>
</table></form>

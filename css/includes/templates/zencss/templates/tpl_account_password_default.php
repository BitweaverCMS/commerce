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
// $Id: tpl_account_password_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<?php echo zen_draw_form('account_password', zen_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'), 'post', 'onsubmit="return check_form(account_password);"') . zen_draw_hidden_field('action', 'process'); ?> 
<h1><?php echo HEADING_TITLE; ?></h1>
<p><?php echo FORM_REQUIRED_INFORMATION ?></p>
<?php
  if ($messageStack->size('account_password') > 0) {
?>
<?php echo $messageStack->output('account_password'); ?> 
<?php
  }
?>
<div class="formrow">
  <label><?php echo ENTRY_PASSWORD_CURRENT; ?></label>
  <?php echo zen_draw_password_field('password_current') . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_CURRENT_TEXT) ? '<span class="inputrequirement">' . ENTRY_PASSWORD_CURRENT_TEXT . '</span>': ''); ?> 
</div>
<div class="formrow">
  <label><?php echo ENTRY_PASSWORD_NEW; ?></label>
  <?php echo zen_draw_password_field('password_new') . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_NEW_TEXT) ? '<span class="inputrequirement">' . ENTRY_PASSWORD_NEW_TEXT . '</span>': ''); ?> 
</div>
<div class="formrow">
  <label><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></label>
  <?php echo zen_draw_password_field('password_confirmation') . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputrequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?> 
</div>
<br class="clear" />
  <?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?> </form> 

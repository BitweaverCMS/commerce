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
// $Id: tpl_password_forgotten_default.php,v 1.1 2005/07/05 05:59:03 bitweaver Exp $
//
?>
<?php echo zen_draw_form('password_forgotten', zen_href_link(FILENAME_PASSWORD_FORGOTTEN, 'action=process', 'SSL')); ?> 
<h1><?php echo HEADING_TITLE; ?></h1>
<?php
  if ($messageStack->size('password_forgotten') > 0) {
?>
<?php echo $messageStack->output('password_forgotten'); ?> 
<?php
  }
?>
<p><?php echo TEXT_MAIN; ?></p>
<div class="formrow"><label><?php echo ENTRY_EMAIL_ADDRESS . '</label>' . zen_draw_input_field('email_address'); ?></div> 
<?php echo '<a href="' . zen_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?>
</form> 

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
// $Id: tpl_tell_a_friend_default.php,v 1.1 2005/07/05 05:59:04 bitweaver Exp $
//
?>
<?php echo zen_draw_form('email_friend', zen_href_link(FILENAME_TELL_A_FRIEND, 'action=process&products_id=' . $_GET['products_id'])); ?> 
 
<h1><?php echo sprintf(HEADING_TITLE, $product_info->fields['products_name']); ?></h1>
<?php if ($messageStack->size('friend') > 0) { ?>
<?php echo $messageStack->output('friend'); ?> 
<?php } ?>
<p><?php echo FORM_REQUIRED_INFORMATION; ?></p>
<fieldset>
	<legend><?php echo FORM_TITLE_CUSTOMER_DETAILS; ?></legend>
	<div class="formrow"><label><?php echo FORM_FIELD_CUSTOMER_NAME; ?></label> <?php echo zen_draw_input_field('from_name'); ?> </div>
	<div class="formrow"><label><?php echo FORM_FIELD_CUSTOMER_EMAIL; ?></label><?php echo zen_draw_input_field('from_email_address'); ?></div>
	<br class="clear" />
</fieldset>

<fieldset>
	<legend><?php echo FORM_TITLE_FRIEND_DETAILS; ?></legend>
	<div class="formrow">
		<label><?php echo FORM_FIELD_FRIEND_NAME; ?></label>
		<?php echo zen_draw_input_field('to_name') . ' <span class="inputrequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>'; ?> 
	</div>
	<div class="formrow">
		<label><?php echo FORM_FIELD_FRIEND_EMAIL; ?></label>
		<?php echo zen_draw_input_field('to_email_address') . ' <span class="inputrequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>'; ?>
	</div>
	<div class="formrow">
		<label><?php echo FORM_TITLE_FRIEND_MESSAGE; ?></label>
		<?php echo zen_draw_textarea_field('message', 'soft', 40, 8); ?> 
	</div>
	<br class="clear" />
</fieldset>
<p><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['products_id']) . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> </p>

<div class="formrow"><label>&nbsp</label><input type="submit" value="submit" name="Submit" /></div>
</form> 
<div class="cleargap">&nbsp;</div>


</form> 

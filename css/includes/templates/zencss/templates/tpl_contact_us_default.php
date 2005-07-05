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
// $Id: tpl_contact_us_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<?php echo zen_draw_form('contact_us', zen_href_link(FILENAME_CONTACT_US, 'action=send')); ?> 
	<h1><?php echo HEADING_TITLE; ?></h1>
	<?php  if ($messageStack->size('contact') > 0) { ?>
		<?php echo $messageStack->output('contact'); ?> 
	<?php }
	if (isset($_GET['action']) && ($_GET['action'] == 'success')) { ?>
		<?php echo TEXT_SUCCESS; ?> 
		<?php $back = sizeof($_SESSION['navigation']->path)-2; ?>
		<?php echo '<a href="' . zen_href_link($_SESSION['navigation']->path[$back]['page'], zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']) . '">' . zen_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?>
	<?php } else { ?>
		<fieldset>
			<legend>Contact Form</legend>
			<div class="formrow"><label><?php echo ENTRY_NAME; ?></label><?php echo zen_draw_input_field('name'); ?></div>
			<div class="formrow"><label><?php echo ENTRY_EMAIL; ?></label><?php echo zen_draw_input_field('email'); ?></div>
			<div class="formrow" style="text-align:left"><?php echo ENTRY_ENQUIRY; ?>
			<?php echo zen_draw_textarea_field('enquiry', 'soft', 5, 7); ?></div>
		</fieldset>
				
		<div class="row">
			<?php $back = sizeof($_SESSION['navigation']->path)-2; ?>
			<span class="left"><?php echo '<a href="' . zen_href_link($_SESSION['navigation']->path[$back]['page'], zen_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']) . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></span> 
			<span class="right"><?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></span> 
		</div>
	<?php } ?>
	<br class="clear" />
</form>

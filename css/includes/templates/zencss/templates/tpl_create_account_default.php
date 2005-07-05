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
// $Id: tpl_create_account_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
    <?php echo zen_draw_form('create_account', zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'onsubmit="return check_form(create_account);"') . zen_draw_hidden_field('action', 'process'); ?>
<h1><?php echo HEADING_TITLE; ?></h1>
<p class="smallText"><?php echo sprintf(TEXT_ORIGIN_LOGIN, zen_href_link(FILENAME_LOGIN, zen_get_all_get_params(), 'SSL')); ?></p>
<?php if ($messageStack->size('create_account') > 0) { echo $messageStack->output('create_account'); } ?>
<fieldset>
	<legend><?php echo CATEGORY_PERSONAL; ?></legend>
	<p class="inputrequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></p>
	<?php if (ACCOUNT_GENDER == 'true') { ?>
		<div class="formrow inline"><label><?php echo ENTRY_GENDER . '</label>' .  zen_draw_radio_field('gender', 'm') . '&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('gender', 'f') . '&nbsp;' . FEMALE . (zen_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputrequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></div>
	<?php } ?>
	<div class="formrow"><label><?php echo ENTRY_FIRST_NAME . '</label>' .  zen_draw_input_field('firstname') . (zen_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputrequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></div>
	<div class="formrow"><label><?php echo ENTRY_LAST_NAME . '</label>' .  zen_draw_input_field('lastname') . (zen_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputrequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?></div>
	<?php if (ACCOUNT_DOB == 'true') { ?>
		<div class="formrow"><label><?php echo ENTRY_DATE_OF_BIRTH . '</label>' .  zen_draw_input_field('dob') . (zen_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputrequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': ''); ?></div>
	<?php } ?>
	<div class="formrow"><label><?php echo ENTRY_EMAIL_ADDRESS . '</label>' .  zen_draw_input_field('email_address') . (zen_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputrequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></div> 
	<div class="formrow"><label><?php echo ENTRY_PASSWORD . '</label>' .  zen_draw_password_field('password') . (zen_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputrequirement">' . ENTRY_PASSWORD_TEXT . '</span>': ''); ?></div>
	<div class="formrow"><label><?php echo ENTRY_PASSWORD_CONFIRMATION . '</label>' . zen_draw_password_field('confirmation') . (zen_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputrequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></div>
	<br class="clear" />
</fieldset>

<?php if (ACCOUNT_COMPANY == 'true') { ?>
<fieldset>
	<legend><?php echo CATEGORY_COMPANY; ?></legend>
	<div class="formrow"><label><?php echo ENTRY_COMPANY . '</label>' .  zen_draw_input_field('company') . (zen_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputrequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></div><br style="clear:both" />
</fieldset>
<?php  } ?>

<fieldset>
<legend><?php echo CATEGORY_ADDRESS; ?></legend>
	<?php require(DIR_WS_BLOCKS . 'blk_address_format_us_css.php'); ?>
</fieldset>

<fieldset>
	<legend><?php echo CATEGORY_CONTACT; ?></legend>
	<div class="formrow"><label><?php echo ENTRY_TELEPHONE_NUMBER . '</label>' . zen_draw_input_field('telephone') . (zen_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputrequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>' : ''); ?> </div>
	<div class="formrow"><label><?php echo ENTRY_FAX_NUMBER . '</label>' .  zen_draw_input_field('fax') . (zen_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputrequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?> </div>
	<br class="clear" />
</fieldset>

<fieldset>
	<legend><?php echo CATEGORY_OPTIONS; ?></legend>
	<div class="formrow"><label><?php echo ENTRY_NEWSLETTER . '</label>' . zen_draw_checkbox_field('newsletter', '1') . (zen_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputrequirement">' . ENTRY_NEWSLETTER_TEXT . '</span>': ''); ?> </div>
	<br class="clear" />
</fieldset>

<div class="formrow"><label>&nbsp</label><?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
</form> 
<div class="cleargap">&nbsp;</div>
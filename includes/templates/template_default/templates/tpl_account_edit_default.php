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
// $Id: tpl_account_edit_default.php,v 1.1 2005/07/05 05:59:04 bitweaver Exp $
//
?>
<?php echo zen_draw_form('account_edit', zen_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'), 'post', 'onsubmit="return check_form(account_edit);"') . zen_draw_hidden_field('action', 'process'); ?> 
<h1><?php echo HEADING_TITLE; ?></h1>
<?php
  if ($messageStack->size('account_edit') > 0) {
?>
<?php echo $messageStack->output('account_edit'); ?> 
<?php
  }
?>
<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($account->fields['customers_gender'] == 'm') ? true : false;
    }
    $female = !$male;
?>
<label><?php echo ENTRY_GENDER; ?></label>
<?php echo zen_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (zen_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?> 
<?php
  }
?>
<div class="formrow">
  <label><?php echo ENTRY_FIRST_NAME; ?></label>
  <?php echo zen_draw_input_field('firstname', $account->fields['customers_firstname']) . '&nbsp;' . (zen_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputrequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?> 
</div>
<div class="formrow">
  <label><?php echo ENTRY_LAST_NAME; ?></label>
  <?php echo zen_draw_input_field('lastname', $account->fields['customers_lastname']) . '&nbsp;' . (zen_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputrequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?> 
</div>
<?php
  if (ACCOUNT_DOB == 'true') {
?>
<div class="formrow">
  <label><?php echo ENTRY_DATE_OF_BIRTH; ?></label>
  <?php echo zen_draw_input_field('dob', zen_date_short($account->fields['customers_dob'])) . '&nbsp;' . (zen_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputrequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': ''); ?> 
</div>
<?php
  }
?>
<div class="formrow">
  <label><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
  <?php echo zen_draw_input_field('email_address', $account->fields['customers_email_address']) . '&nbsp;' . (zen_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputrequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?> 
</div>
<div class="formrow">
  <label><?php echo ENTRY_TELEPHONE_NUMBER; ?></label>
  <?php echo zen_draw_input_field('telephone', $account->fields['customers_telephone']) . '&nbsp;' . (zen_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputrequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?> 
</div>
<div class="formrow">
  <label><?php echo ENTRY_FAX_NUMBER; ?></label>
  <?php echo zen_draw_input_field('fax', $account->fields['customers_fax']) . '&nbsp;' . (zen_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputrequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?> 
</div>
<?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?> </form> 

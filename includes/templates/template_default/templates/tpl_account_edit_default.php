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
// $Id: tpl_account_edit_default.php,v 1.2 2005/07/14 04:55:15 spiderr Exp $
//
?>
<?php echo zen_draw_form('account_edit', zen_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'), 'post', 'onsubmit="return check_form(account_edit);"') . zen_draw_hidden_field('action', 'process'); ?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
<?php
  if ($messageStack->size('account_edit') > 0) {
?>
  <tr>
    <td colspan="2"><?php echo $messageStack->output('account_edit'); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" cellspacing="2" cellpadding="2">
<?php
  if (ACCOUNT_GENDER == 'true') {
?>
        <tr>
          <td class="main"><?php echo ENTRY_GENDER; ?></td>
          <td class="main"><?php echo zen_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (zen_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  }
?>
        <tr>
          <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
          <td class="main"><?php echo zen_draw_input_field('firstname', $account->fields['customers_firstname']) . '&nbsp;' . (zen_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
          <td class="main"><?php echo zen_draw_input_field('lastname', $account->fields['customers_lastname']) . '&nbsp;' . (zen_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  if (ACCOUNT_DOB == 'true') {
?>
        <tr>
          <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
          <td class="main"><?php echo zen_draw_input_field('dob', zen_date_short($account->fields['customers_dob'])) . '&nbsp;' . (zen_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  }
?>
        <tr>
          <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
          <td class="main"><?php echo zen_draw_input_field('email_address', $account->fields['customers_email_address']) . '&nbsp;' . (zen_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
          <td class="main"><?php echo zen_draw_input_field('telephone', $account->fields['customers_telephone']) . '&nbsp;' . (zen_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
          <td class="main"><?php echo zen_draw_input_field('fax', $account->fields['customers_fax']) . '&nbsp;' . (zen_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?></td>
        </tr>
	    <tr>
        <td class="main" valign="top"><?php echo ENTRY_EMAIL_PREFERENCE; ?></td>
        <td class="main"><?php echo zen_draw_radio_field('email_format', 'HTML', $email_pref_html) . '&nbsp;' . ENTRY_EMAIL_HTML_DISPLAY . '&nbsp;&nbsp;' .
		                            zen_draw_radio_field('email_format', 'TEXT', $email_pref_text) . '&nbsp;' . ENTRY_EMAIL_TEXT_DISPLAY . '&nbsp;&nbsp;'; ?></td>
      </tr>
<?php
  if (CUSTOMERS_REFERRAL_STATUS == 2 and $customers_referral == '') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CUSTOMERS_REFERRAL; ?></td>
            <td class="main">
              <?php echo zen_draw_input_field('customers_referral', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_referral', 15)); ?>
            </td>
          </tr>
<?php } ?>
<?php
  if (CUSTOMERS_REFERRAL_STATUS == 2 and $customers_referral != '') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CUSTOMERS_REFERRAL; ?></td>
            <td class="main">
              <?php echo $customers_referral; zen_draw_hidden_field('customers_referral', $customers_referral); ?>
            </td>
          </tr>
<?php } ?>

      </table>
    </td>
  </tr>
  <tr>
    <td class="main"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK , BUTTON_BACK_ALT) . '</a>'; ?></td>
    <td align="right"><?php echo zen_image_submit(BUTTON_IMAGE_UPDATE , BUTTON_UPDATE_ALT); ?></td>
  </tr>
</table></form>

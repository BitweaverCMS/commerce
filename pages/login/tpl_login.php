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
// $Id: tpl_login.php,v 1.2 2005/09/27 22:33:56 spiderr Exp $
//
?>
<?php echo zen_draw_form('login', zen_href_link(FILENAME_LOGIN, 'action=process', 'SSL')); ?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="main">
<?php
  if ($messageStack->size('login') > 0) {
?>
    <?php echo $messageStack->output('login'); ?>

<?php
  }
  if ($_SESSION['cart']->count_contents() > 0) {
?>
    <?php echo TEXT_VISITORS_CART; ?>
<?php
  }
?>
    </td>
  </tr>
  <tr>
    <td class="plainBoxHeading">
      <?php echo HEADING_RETURNING_CUSTOMER; ?>
    </td>
  </tr>
  <tr>
    <td class="plainBox">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
          <td class="main">
            <?php echo zen_draw_input_field('email_address', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', '40')); ?>
          </td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_PASSWORD; ?></td>
          <td class="main">
            <?php echo zen_draw_password_field('password', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_password')); ?>
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td class="main">
            <?php echo '<a href="' . zen_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td align="right"><?php echo zen_image_submit(BUTTON_IMAGE_LOGIN, BUTTON_LOGIN_ALT); ?></td>
  </tr></form>

  <tr>
    <td class="plainBoxHeading"><?php echo HEADING_NEW_CUSTOMER; ?></td>
  </tr>
  <tr>
    <td class="plainBox"><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION; ?></td>
  </tr>

<?php
  if ($messageStack->size('create_account') > 0) {
?>
  <tr>
    <td class="plainBox"><?php echo $messageStack->output('create_account'); ?></td>
  </tr>
<?php
  }
?>
<?php echo zen_draw_form('create_account', zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'onsubmit="return check_form(create_account);"') . zen_draw_hidden_field('action', 'process') . zen_draw_hidden_field('email_pref_html', 'email_format'); ?>
<?php
  if (DISPLAY_PRIVACY_CONDITIONS == 'true') {
?>
  <tr>
    <td class="plainBoxHeading"><?php echo TABLE_HEADING_PRIVACY_CONDITIONS; ?></td>
  </tr>
  <tr>
    <td class="plainBox"><?php echo TEXT_PRIVACY_CONDITIONS_DESCRIPTION . '<br /><br />' . zen_draw_checkbox_field('privacy_conditions', '1', false, 'id="privacy"') . '<label for="privacy">&nbsp;' . TEXT_PRIVACY_CONDITIONS_CONFIRM . '</label>'; ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="plainBoxHeading">
      <?php echo CATEGORY_PERSONAL; ?><div align="right"><span class="inputRequirement"><?php echo FORM_REQUIRED_INFORMATION; ?></span></div>
    </td>
  </tr>
  <tr>
    <td class="plainBox">
      <table border="0" cellspacing="0" cellpadding="2">
<?php
  if (ACCOUNT_GENDER == 'true') {
?>
        <tr>
          <td class="main"><?php echo ENTRY_GENDER; ?></td>
          <td class="main"><?php echo zen_draw_radio_field('gender', 'm') . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . zen_draw_radio_field('gender', 'f') . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (zen_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  }
?>
        <tr>
          <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
          <td class="main"><?php echo zen_draw_input_field('firstname', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_firstname', '40')) . '&nbsp;' . (zen_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
          <td class="main"><?php echo zen_draw_input_field('lastname', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_lastname', '40')) . '&nbsp;' . (zen_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  if (ACCOUNT_DOB == 'true') {
?>
        <tr>
          <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
          <td class="main"><?php echo zen_draw_input_field('dob') . '&nbsp;' . (zen_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  }
?>
        <tr>
          <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
          <td class="main"><?php echo zen_draw_input_field('email_address', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_email_address', '40')) . '&nbsp;' . (zen_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  if ($sniffer->phpBB['installed'] == true) {
?>
        <tr>
          <td class="main"><?php echo ENTRY_NICK; ?></td>
          <td class="main"><?php echo zen_draw_input_field('nick') . '&nbsp;' . (zen_not_null(ENTRY_NICK) ? '<span class="inputRequirement">' . ENTRY_NICK_TEXT . '</span>': ''); ?></td>
        </tr>
<?php
  }
?>
        <tr>
          <td class="main"><?php echo ENTRY_PASSWORD; ?></td>
          <td class="main"><?php echo zen_draw_password_field('password', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_password', '20')) . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_TEXT . '</span>': ''); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
          <td class="main"><?php echo zen_draw_password_field('confirmation', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_password', '20')) . '&nbsp;' . (zen_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
  <tr>
    <td class="plainBoxHeading"><?php echo CATEGORY_COMPANY; ?></td>
  </tr>
  <tr>
    <td class="plainBox">
      <table border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td class="main"><?php echo ENTRY_COMPANY; ?></td>
          <td class="main"><?php echo zen_draw_input_field('company', '', zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_company', '40')) . '&nbsp;' . (zen_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="plainBoxHeading"><?php echo CATEGORY_ADDRESS; ?></td>
  </tr>
  <tr>
    <td class="plainBox"><?php require(DIR_FS_BLOCKS . 'blk_address_format_us.php'); ?></td>
  </tr>
  <tr>
    <td class="plainBoxHeading"><?php echo CATEGORY_CONTACT; ?></td>
  </tr>

  <tr>
    <td class="plainBox">
      <table border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
          <td class="main"><?php echo zen_draw_input_field('telephone', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_telephone', '40')) . '&nbsp;' . (zen_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
          <td class="main"><?php echo zen_draw_input_field('fax', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_fax', '40')) . '&nbsp;' . (zen_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?></td>
        </tr>
      </table>
    </td>
  </tr>

  <tr>
    <td class="plainBoxHeading"><?php echo CATEGORY_OPTIONS; ?></td>
  </tr>
  <tr>
    <td class="plainBox">
      <table border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main" valign="top"><?php echo ENTRY_EMAIL_PREFERENCE; ?></td>
        <td class="main"><?php echo zen_draw_radio_field('email_format', 'HTML', (ACCOUNT_EMAIL_PREFERENCE == '1' ? true : false)) . '&nbsp;' . ENTRY_EMAIL_HTML_DISPLAY . '&nbsp;&nbsp;' .
		                            zen_draw_radio_field('email_format', 'TEXT', (ACCOUNT_EMAIL_PREFERENCE == '1' ? false : true)) . '&nbsp;' . ENTRY_EMAIL_TEXT_DISPLAY . '&nbsp;&nbsp;'; ?></td>
      </tr>
<?php
  if (ACCOUNT_NEWSLETTER_STATUS != 0) {
?>
        <tr>
          <td class="main"><?php echo ENTRY_NEWSLETTER; ?></td>
          <td class="main"><?php echo zen_draw_checkbox_field('newsletter', '1', (ACCOUNT_NEWSLETTER_STATUS == '1' ? false : true)) . '&nbsp;' . (zen_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputRequirement">' . ENTRY_NEWSLETTER_TEXT . '</span>': ''); ?></td>
        </tr>
<?php } ?>

<?php
  if (CUSTOMERS_REFERRAL_STATUS == 2) {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CUSTOMERS_REFERRAL; ?></td>
            <td class="main">
              <?php echo zen_draw_input_field('customers_referral', '', zen_set_field_length(TABLE_CUSTOMERS, 'customers_referral', 15)); ?>
            </td>
          </tr>
<?php } ?>

      </table>
    </td>
  </tr>

  <tr>
    <td align="right"><?php echo zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?></td>
  </tr>
</table></form>

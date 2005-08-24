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
// $Id: header_php.php,v 1.4 2005/08/24 15:06:37 lsces Exp $
//
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_MODULES . 'require_languages.php');
  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    if (ACCOUNT_GENDER == 'true') $gender = zen_db_prepare_input($_POST['gender']);
    $firstname = zen_db_prepare_input($_POST['firstname']);
    $lastname = zen_db_prepare_input($_POST['lastname']);
    if (ACCOUNT_DOB == 'true') $dob = zen_db_prepare_input($_POST['dob']);
    $email_address = zen_db_prepare_input($_POST['email_address']);
    $telephone = zen_db_prepare_input($_POST['telephone']);
    $fax = zen_db_prepare_input($_POST['fax']);
    $email_format = zen_db_prepare_input($_POST['email_format']);

    if (CUSTOMERS_REFERRAL_STATUS == '2' and $_POST['customers_referral'] != '') $customers_referral = zen_db_prepare_input($_POST['customers_referral']);

    $error = false;

    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('account_edit', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB == 'true') {
      if (!checkdate(substr(zen_date_raw($dob), 4, 2), substr(zen_date_raw($dob), 6, 2), substr(zen_date_raw($dob), 0, 4))) {
        $error = true;

        $messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
      }
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
    }

    if (!zen_validate_email($email_address)) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    $check_email_query = "select count(*) as `total`
                          from   " . TABLE_CUSTOMERS . "
                          where      `customers_email_address` = '" . zen_db_input($email_address) . "'
                          and        `customers_id` != '" . (int)$_SESSION['customer_id'] . "'";

    $check_email = $db->Execute($check_email_query);

    if ($check_email->fields['total'] > 0) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    if ($error == false) {
      $sql_data_array = array('customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax,
                              'customers_email_format' => $email_format);

      if ((CUSTOMERS_REFERRAL_STATUS == '2' and $customers_referral != '')) $sql_data_array['customers_referral'] = $customers_referral;
      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = zen_date_raw($dob);

      $db->associateInsert(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$_SESSION['customer_id'] . "'");

      $sql = "update " . TABLE_CUSTOMERS_INFO . "
              set        date_account_last_modified = now()
              where      customers_info_id = '" . (int)$_SESSION['customer_id'] . "'";

      $db->Execute($sql);

      $sql_data_array = array('entry_firstname' => $firstname,
                              'entry_lastname' => $lastname);

      $db->associateInsert(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int)$_SESSION['customer_id'] . "' and address_book_id = '" . (int)$_SESSION['customer_default_address_id'] . "'");

// reset the session variables
      $_SESSION['customer_first_name'] = $firstname;

      $messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');

      zen_redirect(zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
  }

  $account_query = "select customers_gender, customers_firstname, customers_lastname,
                           customers_dob, customers_email_address, customers_telephone,
                           customers_fax, customers_email_format, customers_referral
                    from   " . TABLE_CUSTOMERS . "
                    where  customers_id = '" . (int)$_SESSION['customer_id'] . "'";

  $account = $db->Execute($account_query);
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($account->fields['customers_gender'] == 'm') ? true : false;
    }
    $female = !$male;
  }

  $customers_referral = $account->fields['customers_referral'];

  if (isset($customers_email_format)) {
    $email_pref_html = (($customers_email_format == 'HTML') ? true : false);
    $email_pref_none = (($customers_email_format == 'NONE') ? true : false);
    $email_pref_optout = (($customers_email_format == 'OUT')  ? true : false);
    $email_pref_text = (($email_pref_html || $email_pref_none || $email_pref_out) ? false : true);  // if not in any of the others, assume TEXT
  } else {
    $email_pref_html = (($account->fields['customers_email_format'] == 'HTML') ? true : false);
    $email_pref_none = (($account->fields['customers_email_format'] == 'NONE') ? true : false);
    $email_pref_optout = (($account->fields['customers_email_format'] == 'OUT')  ? true : false);
    $email_pref_text = (($email_pref_html || $email_pref_none || $email_pref_out) ? false : true);  // if not in any of the others, assume TEXT
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>

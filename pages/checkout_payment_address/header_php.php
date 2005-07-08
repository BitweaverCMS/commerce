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
// $Id: header_php.php,v 1.3 2005/07/08 06:13:00 spiderr Exp $
//
// if the customer is not logged on, redirect them to the login page
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() <= 0) {
    zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
  }

  require(DIR_WS_MODULES . 'require_languages.php');

  $error = false;
  $process = false;
  if (isset($_POST['action']) && ($_POST['action'] == 'submit')) {
// process a new billing address
    if (zen_not_null($_POST['firstname']) && zen_not_null($_POST['lastname']) && zen_not_null($_POST['street_address'])) {
      $process = true;

      if (ACCOUNT_GENDER == 'true') $gender = zen_db_prepare_input($_POST['gender']);
      if (ACCOUNT_COMPANY == 'true') $company = zen_db_prepare_input($_POST['company']);
      $firstname = zen_db_prepare_input($_POST['firstname']);
      $lastname = zen_db_prepare_input($_POST['lastname']);
      $street_address = zen_db_prepare_input($_POST['street_address']);
      if (ACCOUNT_SUBURB == 'true') $suburb = zen_db_prepare_input($_POST['suburb']);
      $postcode = zen_db_prepare_input($_POST['postcode']);
      $city = zen_db_prepare_input($_POST['city']);
      $country = zen_db_prepare_input($_POST['country']);
      if (ACCOUNT_STATE == 'true') {
        if (isset($_POST['zone_id'])) {
          $zone_id = zen_db_prepare_input($_POST['zone_id']);
        } else {
          $zone_id = false;
        }
        $state = zen_db_prepare_input($_POST['state']);
      }

      if (ACCOUNT_GENDER == 'true') {
        if ( ($gender != 'm') && ($gender != 'f') ) {
          $error = true;

          $messageStack->add('checkout_address', ENTRY_GENDER_ERROR);
        }
      }

      if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', ENTRY_FIRST_NAME_ERROR);
      }

      if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', ENTRY_LAST_NAME_ERROR);
      }

      if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', ENTRY_STREET_ADDRESS_ERROR);
      }

      if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', ENTRY_POST_CODE_ERROR);
      }

      if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
        $error = true;

        $messageStack->add('checkout_address', ENTRY_CITY_ERROR);
      }

      if (ACCOUNT_STATE == 'true') {
        $zone_id = 0;
        $check_query = "select count(*) as total
                        from " . TABLE_ZONES . "
                        where zone_country_id = '" . (int)$country . "'";

        $check = $db->Execute($check_query);

        $entry_state_has_zones = ($check->fields['total'] > 0);
        if ($entry_state_has_zones == true) {
          $zone_query = "select distinct zone_id from " . TABLE_ZONES . "
                         where zone_country_id = '" . (int)$country . "'
                         and (zone_name like '" . zen_db_input($state) . "%'
                         or zone_code like '%" . zen_db_input($state) . "%')";

          $zone = $db->Execute($zone_query);

          if ($zone->RecordCount() == 1) {
            $zone_id = $zone->fields['zone_id'];
          } else {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_STATE_ERROR_SELECT);
          }
        } else {
          if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
            $error = true;

            $messageStack->add('checkout_address', ENTRY_STATE_ERROR);
          }
        }
      }

      if ( (is_numeric($country) == false) || ($country < 1) ) {
        $error = true;

        $messageStack->add('checkout_address', ENTRY_COUNTRY_ERROR);
      }

      if ($error == false) {
        $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
                                'entry_firstname' => $firstname,
                                'entry_lastname' => $lastname,
                                'entry_street_address' => $street_address,
                                'entry_postcode' => $postcode,
                                'entry_city' => $city,
                                'entry_country_id' => $country);

        if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
        if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
        if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;
        if (ACCOUNT_STATE == 'true') {
          if ($zone_id > 0) {
            $sql_data_array['entry_zone_id'] = $zone_id;
            $sql_data_array['entry_state'] = '';
          } else {
            $sql_data_array['entry_zone_id'] = '0';
            $sql_data_array['entry_state'] = $state;
          }
        }

        $sql = "select * from " . TABLE_ADDRESS_BOOK . " where customers_id = '-1'";
        $rs = $db->Execute($sql);
        $sql_data_array['customers_id'] = (int)$_SESSION['customer_id'];
//        $insertSQL = $db->GetInsertSQL($rs, $sql_data_array);
//        $rs = $db->Execute($insertSQL);

        $db->associateInsert(TABLE_ADDRESS_BOOK, $sql_data_array);

        $_SESSION['billto'] = zen_db_insert_id( TABLE_ADDRESS_BOOK, 'address_book_id' );

        $_SESSION['payment'] = '';

        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      }
// process the selected billing destination
    } elseif (isset($_POST['address'])) {
      $reset_payment = false;
      if ($_SESSION['billto']) {
        if ($_SESSION['billto'] != $_POST['address']) {
          if ($_SESSION['payment']) {
            $reset_payment = true;
          }
        }
      }
      $_SESSION['billto'] = $_POST['address'];

      $check_address_query = "select count(*) as total from " . TABLE_ADDRESS_BOOK . "
                              where customers_id = '" . $_SESSION['customer_id'] . "'
                              and address_book_id = '" . $_SESSION['billto'] . "'";

      $check_address = $db->Execute($check_address_query);

      if ($check_address->fields['total'] == '1') {
        if ($reset_payment == true) $_SESSION['payment'] = '';
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      } else {
        $_SESSION['billto'] = '';
      }
// no addresses to select from - customer decided to keep the current assigned address
    } else {
      $_SESSION['billto'] = $_SESSION['customer_default_address_id'];

      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }

// if no billing destination address was selected, use their own address as default
  if (!$_SESSION['billto']) {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);

  $addresses_count = zen_count_customer_address_book_entries();
?>

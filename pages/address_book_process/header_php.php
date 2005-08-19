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
// $Id: header_php.php,v 1.5 2005/08/19 18:51:01 spiderr Exp $
//
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_MODULES . 'require_languages.php');
  if (isset($_GET['action']) && ($_GET['action'] == 'deleteconfirm') && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $sql = "delete from   " . TABLE_ADDRESS_BOOK . "
                   where  address_book_id = '" . (int)$_GET['delete'] . "'
                   and    customers_id = '" . (int)$_SESSION['customer_id'] . "'";

    $db->Execute($sql);

    $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');

    zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  }

// error checking when updating or adding an entry
  $process = false;
  if (isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update'))) {
  	$_REQUEST['address'] = $_REQUEST['edit'];
	if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
      $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
	} else {
		foreach( $gBitCustomer->mErrors as $errString ) {
			$messageStack->add_session( 'addressbook', $errString );
		}
	}
/*
    $process = true;
    $error = false;

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

        $messageStack->add('addressbook', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_LAST_NAME_ERROR);
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_CITY_ERROR);
    }

    if (!is_numeric($country)) {
      $error = true;

      $messageStack->add('addressbook', ENTRY_COUNTRY_ERROR);
    }

    if (ACCOUNT_STATE == 'true') {
      $zone_id = 0;
      $check_query = "select count(*) as total
                      from   " . TABLE_ZONES . "
                      where  zone_country_id = '" . (int)$country . "'";

      $check = $db->Execute($check_query);

      $entry_state_has_zones = ($check->fields['total'] > 0);
      if ($entry_state_has_zones == true) {
        $zone_query = "select distinct zone_id
                       from   " . TABLE_ZONES . "
                       where  zone_country_id = '" . (int)$country . "'
                       and    (zone_name like '" . zen_db_input($state) . "%'
                       or     zone_code like '%" . zen_db_input($state) . "%')";

        $zone = $db->Execute($zone_query);

        if ($zone->RecordCount() == 1) {
          $zone_id = $zone->fields['zone_id'];
        } else {
          $error = true;

          $messageStack->add('addressbook', ENTRY_STATE_ERROR_SELECT);
        }
      } else {
        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('addressbook', ENTRY_STATE_ERROR);
        }
      }
    }

    if ($error == false) {
      $sql_data_array = array('entry_firstname' => $firstname,
                              'entry_lastname' => $lastname,
                              'entry_street_address' => $street_address,
                              'entry_postcode' => $postcode,
                              'entry_city' => $city,
                              'entry_country_id' => (int)$country);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
      if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
      if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;
      if (ACCOUNT_STATE == 'true') {
		if( !empty( $zone_id ) && is_numeric( $zone_id ) ) {
          $sql_data_array['entry_zone_id'] = $zone_id;
          $sql_data_array['entry_state'] = NULL;
        } else {
          $sql_data_array['entry_zone_id'] = NULL;
          $sql_data_array['entry_state'] = $state;
        }
      }

      if ($_POST['action'] == 'update') {

      $db->associateInsert(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id = '" . (int)$_GET['edit'] . "' and customers_id ='" . (int)$_SESSION['customer_id'] . "'");

// reregister session variables
        if ( (isset($_POST['primary']) && ($_POST['primary'] == 'on')) || ($_GET['edit'] == $_SESSION['customer_default_address_id']) ) {
          $_SESSION['customer_first_name'] = $firstname;
          $_SESSION['customer_country_id'] = $country;
			if( !empty( $zone_id ) && is_numeric( $zone_id ) ) {
				$_SESSION['customer_zone_id'] = $zone_id;
			}
          $_SESSION['customer_default_address_id'] = (int)$_GET['edit'];

          $sql_data_array = array('customers_firstname' => $firstname,
                                  'customers_lastname' => $lastname,
                                  'customers_default_address_id' => (int)$_GET['edit']);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
          $db->associateInsert(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$_SESSION['customer_id'] . "'");
        }
      } else {

        $sql_data_array['customers_id'] = (int)$_SESSION['customer_id'];
        $db->associateInsert(TABLE_ADDRESS_BOOK, $sql_data_array);

        $new_address_book_id = zen_db_insert_id( TABLE_ADDRESS_BOOK, 'address_book_id' );

// reregister session variables
        if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) {
          $_SESSION['customer_first_name'] = $firstname;
          $_SESSION['customer_country_id'] = $country;
          $_SESSION['customer_zone_id'] = (($zone_id > 0) ? (int)$zone_id : '0');
          if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) $_SESSION['customer_default_address_id'] = $new_address_book_id;

          $sql_data_array = array('customers_firstname' => $firstname,
                                  'customers_lastname' => $lastname);

          if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
          if (isset($_POST['primary']) && ($_POST['primary'] == 'on')) $sql_data_array['customers_default_address_id'] = $new_address_book_id;

          $db->associateInsert(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$_SESSION['customer_id'] . "'");
        }
      }
    }
*/
  }

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $entry_query = "select entry_gender, entry_company, entry_firstname, entry_lastname,
                           entry_street_address, entry_suburb, entry_postcode, entry_city,
                           entry_state, entry_zone_id, entry_country_id
                    from   " . TABLE_ADDRESS_BOOK . "
                    where  customers_id = '" . (int)$_SESSION['customer_id'] . "'
                    and    address_book_id = '" . (int)$_GET['edit'] . "'";

    $entry = $db->Execute($entry_query);

    if ($entry->RecordCount()<=0) {
      $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }

  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if ($_GET['delete'] == $_SESSION['customer_default_address_id']) {
      $messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    } else {
      $check_query = "select count(*) as total
                      from " . TABLE_ADDRESS_BOOK . "
                      where address_book_id = '" . (int)$_GET['delete'] . "'
                      and customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $check = $db->Execute($check_query);

      if ($check->fields['total'] < 1) {
        $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

        zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
      }
    }
  } else {
    $entry = array();
  }

  if (!isset($_GET['delete']) && !isset($_GET['edit'])) {
    if (zen_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
      $messageStack->add_session('addressbook', ERROR_ADDRESS_BOOK_FULL);

      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $breadcrumb->add(NAVBAR_TITLE_MODIFY_ENTRY);
  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $breadcrumb->add(NAVBAR_TITLE_DELETE_ENTRY);
  } else {
    $breadcrumb->add(NAVBAR_TITLE_ADD_ENTRY);
  }
?>

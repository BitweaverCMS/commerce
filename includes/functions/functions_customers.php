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
// $Id: functions_customers.php,v 1.1 2005/07/05 05:59:00 bitweaver Exp $
//
//
////
// Returns the address_format_id for the given country
// TABLES: countries;
  function zen_get_address_format_id($country_id) {
    global $db;
    $address_format_query = "select address_format_id as format_id
                             from " . TABLE_COUNTRIES . "
                             where countries_id = '" . (int)$country_id . "'";

    $address_format = $db->Execute($address_format_query);

    if ($address_format->RecordCount() > 0) {
      return $address_format->fields['format_id'];
    } else {
      return '1';
    }
  }

////
// Return a formatted address
// TABLES: address_format
  function zen_address_format($address_format_id, $address, $html, $boln, $eoln) {
    global $db;
    $address_format_query = "select address_format as format
                             from " . TABLE_ADDRESS_FORMAT . "
                             where address_format_id = '" . (int)$address_format_id . "'";

    $address_format = $db->Execute($address_format_query);
    $company = zen_output_string_protected($address['company']);
    if (isset($address['firstname']) && zen_not_null($address['firstname'])) {
      $firstname = zen_output_string_protected($address['firstname']);
      $lastname = zen_output_string_protected($address['lastname']);
    } elseif (isset($address['name']) && zen_not_null($address['name'])) {
      $firstname = zen_output_string_protected($address['name']);
      $lastname = '';
    } else {
      $firstname = '';
      $lastname = '';
    }
    $street = zen_output_string_protected($address['street_address']);
    $suburb = zen_output_string_protected($address['suburb']);
    $city = zen_output_string_protected($address['city']);
    $state = zen_output_string_protected($address['state']);
    if (isset($address['country_id']) && zen_not_null($address['country_id'])) {
      $country = zen_get_country_name($address['country_id']);

      if (isset($address['zone_id']) && zen_not_null($address['zone_id'])) {
        $state = zen_get_zone_code($address['country_id'], $address['zone_id'], $state);
      }
    } elseif (isset($address['country']) && zen_not_null($address['country'])) {
      if (is_array($address['country'])) {
        $country = zen_output_string_protected($address['country']['countries_name']);
      } else {       
      $country = zen_output_string_protected($address['country']);
      }
    } else {
      $country = '';
    }
    $postcode = zen_output_string_protected($address['postcode']);
    $zip = $postcode;

    if ($html) {
// HTML Mode
      $HR = '<hr>';
      $hr = '<hr>';
      if ( ($boln == '') && ($eoln == "\n") ) { // Values not specified, use rational defaults
        $CR = '<br />';
        $cr = '<br />';
        $eoln = $cr;
      } else { // Use values supplied
        $CR = $eoln . $boln;
        $cr = $CR;
      }
    } else {
// Text Mode
      $CR = $eoln;
      $cr = $CR;
      $HR = '----------------------------------------';
      $hr = '----------------------------------------';
    }

    $statecomma = '';
    $streets = $street;
    if ($suburb != '') $streets = $street . $cr . $suburb;
    if ($country == '') {
      if (is_array($address['country'])) {
        $country = zen_output_string_protected($address['country']['countries_name']);
      } else {       
      $country = zen_output_string_protected($address['country']);
      }
    }
    if ($state != '') $statecomma = $state . ', ';

    $fmt = $address_format->fields['format'];
    eval("\$address_out = \"$fmt\";");

    if ( (ACCOUNT_COMPANY == 'true') && (zen_not_null($company)) ) {
      $address_out = $company . $cr . $address_out;
    }

    return $address_out;
  }

////
// Return a formatted address
// TABLES: customers, address_book
  function zen_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    global $db;
    $address_query = "select entry_firstname as firstname, entry_lastname as lastname,
                             entry_company as company, entry_street_address as street_address,
                             entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                             entry_state as state, entry_zone_id as zone_id,
                             entry_country_id as country_id
                      from " . TABLE_ADDRESS_BOOK . "
                      where customers_id = '" . (int)$customers_id . "'
                      and address_book_id = '" . (int)$address_id . "'";

    $address = $db->Execute($address_query);

    $format_id = zen_get_address_format_id($address->fields['country_id']);
    return zen_address_format($format_id, $address->fields, $html, $boln, $eoln);
  }

////
// Return a customer greeting
  function zen_customer_greeting() {

    if ($_SESSION['customer_first_name'] && $_SESSION['customer_id']) {
      $greeting_string = sprintf(TEXT_GREETING_PERSONAL, zen_output_string_protected($_SESSION['customer_first_name']), zen_href_link(FILENAME_PRODUCTS_NEW));
    } else {
      $greeting_string = sprintf(TEXT_GREETING_GUEST, zen_href_link(FILENAME_LOGIN, '', 'SSL'), zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
    }

    return $greeting_string;
  }

  function zen_count_customer_orders($id = '', $check_session = true) {
    global $db;

    if (is_numeric($id) == false) {
      if ($_SESSION['customer_id']) {
        $id = $_SESSION['customer_id'];
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( ($_SESSION['customer_id'] == false) || ($id != $_SESSION['customer_id']) ) {
        return 0;
      }
    }

    $orders_check_query = "select count(*) as total
                           from " . TABLE_ORDERS . "
                           where customers_id = '" . (int)$id . "'";

    $orders_check = $db->Execute($orders_check_query);

    return $orders_check->fields['total'];
  }

  function zen_count_customer_address_book_entries($id = '', $check_session = true) {
    global $db;

    if (is_numeric($id) == false) {
      if ($_SESSION['customer_id']) {
        $id = $_SESSION['customer_id'];
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( ($_SESSION['customer_id'] == false) || ($id != $_SESSION['customer_id']) ) {
        return 0;
      }
    }

    $addresses_query = "select count(*) as total
                        from " . TABLE_ADDRESS_BOOK . "
                        where customers_id = '" . (int)$id . "'";

    $addresses = $db->Execute($addresses_query);

    return $addresses->fields['total'];
  }
?>
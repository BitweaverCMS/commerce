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
// $Id: functions_customers.php,v 1.4 2005/11/10 13:46:53 spiderr Exp $
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
// TABLES: customers, address_book
  function zen_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    global $db;
    $address_query = "select entry_firstname as firstname, entry_lastname as lastname,
                             entry_company as company, entry_street_address as street_address,
                             entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                             entry_state as state, entry_zone_id as zone_id,
                             entry_country_id as country_id
                      from " . TABLE_ADDRESS_BOOK . "
                      where `customers_id` = '" . (int)$customers_id . "'
                      and address_book_id = '" . (int)$address_id . "'";

    $address = $db->Execute($address_query);

    $format_id = zen_get_address_format_id($address->fields['country_id']);
    return zen_address_format($format_id, $address->fields, $html, $boln, $eoln);
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

    $orders_check_query = "select count(*) as `total`
                           from " . TABLE_ORDERS . "
                           where `customers_id` = '" . (int)$id . "'";

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

    $addresses_query = "select count(*) as `total`
                        from " . TABLE_ADDRESS_BOOK . "
                        where `customers_id` = '" . (int)$id . "'";

    $addresses = $db->Execute($addresses_query);

    return $addresses->fields['total'];
  }
?>

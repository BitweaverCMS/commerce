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
// $Id$
//
//
////
// Returns the address_format_id for the given country
// TABLES: countries;
function zen_get_address_format_id($country_id) {
	global $gBitDb;
	$address_format_query = "SELECT `address_format_id` as `format_id` FROM " . TABLE_COUNTRIES . " WHERE `countries_id` = ?";

	if( !($ret = $gBitDb->getOne($address_format_query, array( (int)$country_id ) )) ) {
		$ret = '1';
	}
	return $ret;
}


////
// Return a formatted address
// TABLES: customers, address_book
  function zen_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    global $gBitDb;
	$ret = '';
    $address_query = "SELECT `entry_firstname` as `firstname`, `entry_lastname` as `lastname`,
                             `entry_company` as `company`, `entry_street_address` as `street_address`,
                             `entry_suburb` as `suburb`, `entry_city` as `city`, `entry_postcode` as `postcode`,
                             `entry_state` as `state`, `entry_zone_id` as `zone_id`,
                             `entry_country_id` as `country_id`
                      FROM " . TABLE_ADDRESS_BOOK . "
                      WHERE `customers_id` = ? AND `address_book_id` = ?";

    if( ($address = $gBitDb->getRow($address_query, array( (int)$customers_id, (int)$address_id ) )) ) {
    	$format_id = zen_get_address_format_id($address['country_id']);
	    $ret = zen_address_format( $address, $html, $boln, $eoln);
	}
	return $ret;
  }
	// This is a common smarty function and needs to be easily available
	global $gBitSmarty;
	$gBitSmarty->registerPlugin( 'modifier', 'zen_address_label', 'zen_address_label');

  function zen_count_customer_orders($id = '', $check_session = true) {
    global $gBitDb;

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

    $orders_check = $gBitDb->Execute($orders_check_query);

    return $orders_check->fields['total'];
  }

  function zen_count_customer_address_book_entries($id = '', $check_session = true) {
    global $gBitDb;

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

    $addresses = $gBitDb->Execute($addresses_query);

    return $addresses->fields['total'];
  }
?>

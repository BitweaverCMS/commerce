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
// $Id: address_new.php,v 1.1 2005/07/13 20:24:06 spiderr Exp $
//
	global $smarty, $db;
	$smarty ->assign( 'collectGender', defined( 'ACCOUNT_GENDER' ) && ACCOUNT_GENDER == 'true' );
	$smarty ->assign( 'collectCompany', defined( 'ACCOUNT_COMPANY' ) && ACCOUNT_COMPANY == 'true' );
	$smarty ->assign( 'collectSuburb', defined( 'ACCOUNT_SUBURB' ) && ACCOUNT_SUBURB == 'true' );
	if( defined( 'ACCOUNT_STATE' ) && ACCOUNT_STATE == 'true' ) {
		$smarty ->assign( 'collectState', TRUE );
		if ($process == true) {
			if ($entry_state_has_zones == true) {
				$zones_array = array();
				$zones_query = "SELECT zone_name from " . TABLE_ZONES . " WHERE zone_country_id = ? ORDER BY zone_name";
				if( $zones_values = $db->query($zones_query, array( $country ) ) ) {
					while (!$zones_values->EOF) {
						$zones_array[] = array('id' => $zones_values->fields['zone_name'], 'text' => $zones_values->fields['zone_name']);
						$zones_values->MoveNext();
					}
				}
				$statePullDown = zen_draw_pull_down_menu('state', $zones_array);
			} else {
				$statePullDown = zen_draw_input_field('state');
			}
		} else {
			$statePullDown = zen_draw_input_field('state', zen_get_zone_name($entry['entry_country_id'], $entry['entry_zone_id'], $entry['entry_state']));
		}
		$smarty->assign( 'statePullDown', $statePullDown );
	}

	$smarty->assign( 'countryPullDown', zen_get_country_list('country', $entry['country_id'] ) );

	if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
		$smarty ->assign( 'primaryCheck', TRUE );
	}

	$addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
								entry_company as company, entry_street_address as street_address,
								entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
								entry_state as state, entry_zone_id as zone_id,
								entry_country_id as country_id, c.*
						from " . TABLE_ADDRESS_BOOK . " ab INNER JOIN " . TABLE_COUNTRIES . " c ON( ab.entry_country_id=c.countries_id )
						where customers_id = ?";

	if( $rs = $db->query( $addresses_query, array( $_SESSION['customer_id'] ) ) ) {
		$addresses = $rs->GetRows();
		$smarty->assign( 'addresses', $addresses );
	}

	if( !empty( $_SESSION['sendto'] ) ) {
		$smarty->assign( 'sendToAddressId', $_SESSION['sendto'] );
	}

	print $smarty->fetch( 'bitpackage:bitcommerce/address_new.tpl' );
?>


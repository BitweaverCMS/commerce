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
// $Id: new_address.php,v 1.1 2005/07/11 07:11:39 spiderr Exp $
//
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

	print $smarty->fetch( 'bitpackage:bitcommerce/new_address.tpl' );
?>

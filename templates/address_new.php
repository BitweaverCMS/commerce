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
// $Id: address_new.php,v 1.3 2005/07/15 09:24:13 spiderr Exp $
//
if( empty( $entry ) ) {
	$entry = $_REQUEST;
}
	global $smarty, $db, $gBitCustomer;
	$smarty ->assign( 'collectGender', defined( 'ACCOUNT_GENDER' ) && ACCOUNT_GENDER == 'true' );
	$smarty ->assign( 'collectCompany', defined( 'ACCOUNT_COMPANY' ) && ACCOUNT_COMPANY == 'true' );
	$smarty ->assign( 'collectSuburb', defined( 'ACCOUNT_SUBURB' ) && ACCOUNT_SUBURB == 'true' );
	if( defined( 'ACCOUNT_STATE' ) && ACCOUNT_STATE == 'true' ) {
		$smarty->assign( 'collectState', TRUE );
		if ( !empty( $entry['country_id'] ) ) {
			if( $zones = CommerceCustomer::getCountryZones( $entry['country_id'] ) ) {
				$statePullDown = zen_draw_pull_down_menu('state', $zones);
			} else {
				$statePullDown = zen_draw_input_field('state', zen_get_zone_name($entry['country_id'], $entry['entry_zone_id'], $entry['entry_state']));
			}
		} else {
			$statePullDown = zen_draw_input_field('state');
		}
		$smarty->assign( 'statePullDown', $statePullDown );
	}

	$smarty->assign( 'countryPullDown', zen_get_country_list('country_id', $entry['country_id'] ) );

	if ((isset($_GET['edit']) && ($_SESSION['customer_default_address_id'] != $_GET['edit'])) || (isset($_GET['edit']) == false) ) {
		$smarty ->assign( 'primaryCheck', TRUE );
	}

	if( $addresses = CommerceCustomer::getAddresses( $_SESSION['customer_id'] ) ) {
		$smarty->assign( 'addresses', $addresses );
	}

	if( !empty( $_SESSION['sendto'] ) ) {
		$smarty->assign( 'sendToAddressId', $_SESSION['sendto'] );
	}

	print $smarty->fetch( 'bitpackage:bitcommerce/address_new.tpl' );
?>


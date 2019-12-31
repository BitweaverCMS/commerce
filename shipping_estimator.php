<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce	 |
// | Copyright (c) 2003 Edwin Bekaert (edwin@ednique.com)|
// | Customized by: Linda McGrath osCommerce@WebMakers.com|
// | * This now handles Free Shipping for orders over $total as defined in the Admin|
// |	* This now shows Free Shipping on Virtual products			 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
// $Id$
//

require_once( '../kernel/setup_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_user_inc.php' );

global $gBitDb, $gBitUser, $gBitCustomer, $currencies;

// Could be placed in english.php
// shopping cart quotes
// shipping cost
require('includes/classes/http_client.php'); // shipping in basket

// include the order class (uses the sendto !)
require_once(BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php');
$order = new order;

if( $gBitUser->isRegistered() &&  $addresses = $gBitCustomer->getAddresses() ) {
	$gBitSmarty->assign_by_ref( 'addresses', $addresses );
}

// Explicit country requested takes priority, even if registered.
if( (isset( $_REQUEST['address_id'] ) && $_REQUEST['address_id'] == 'custom' || empty( $_REQUEST['address_id'] ) ) && (!empty( $_REQUEST['country_id'] ) || !$gBitUser->isRegistered()) ) {
	if( isset( $_REQUEST['address_id'] ) && $_REQUEST['address_id'] == 'custom' ) {
		$_SESSION['cart_address_id'] = 'custom';
	}
	if( !empty( $_REQUEST['country_id'] ) ) {
		if( !empty( $_SESSION['cart_country_id'] ) && $_SESSION['cart_country_id'] != $_REQUEST['country_id'] ) {
			$_SESSION['cart_zip_code'] = NULL;
			$_REQUEST['zip_code'] = NULL;
		}
		$_SESSION['cart_country_id'] = $_REQUEST['country_id'];
	} elseif( empty( $_SESSION['cart_country_id'] ) ) {
		$_SESSION['cart_country_id'] = STORE_COUNTRY;
	}

	// user not logged in, country is selected
	$countryInfo = zen_get_countries($_SESSION['cart_country_id']);
	$order->delivery = array(	
								'country' => array(
									'countries_id' => $countryInfo['countries_id'], 
									'title' => $countryInfo['countries_name'], 
									'countries_iso_code_2' => $countryInfo['countries_iso_code_2'], 
									'countries_iso_code_3' =>	$countryInfo['countries_iso_code_3']
								),
							 	'country_id' => $countryInfo['countries_id'],
								//add state zone_id
								'format_id' => $countryInfo['address_format_id']
							);
	// Check for form zip code
	if( !empty( $_REQUEST['zip_code'] ) ) {
		$_SESSION['cart_zip_code'] = $_REQUEST['zip_code'];
	}

	$order->delivery['postcode'] = !empty( $_SESSION['cart_zip_code'] ) ? $_SESSION['cart_zip_code'] : NULL;

	// Check for form state 
	if( isset( $_REQUEST['zone_id'] ) ) {
		$_SESSION['cart_zone_id'] = $_REQUEST['zone_id'];
	}
	if( !empty( $_SESSION['cart_zone_id'] ) ) {
		$order->delivery['zone_id'] = (int)$_SESSION['cart_zone_id'];
	}

	// used as a check below for existence of states
	$stateMenu = zen_get_country_zone_list( 'zone_id', $order->delivery['country']['countries_id'], !empty( $_SESSION['cart_zone_id'] ) ? $_SESSION['cart_zone_id'] : NULL );
	$gBitSmarty->assign_by_ref( 'stateMenu', $stateMenu );
} elseif( !empty( $addresses ) && (empty( $_REQUEST['address_id'] ) || $_REQUEST['address_id'] != 'custom') ) {
	if( !empty( $_REQUEST['address_id'] ) ) {
		$_SESSION['cart_address_id'] = $_REQUEST['address_id'];
	} elseif( empty( $_SESSION['cart_address_id'] ) && !empty( $addresses ) ) {
		// no selected address yet, snag the first one
		reset( $addresses );
		$first = current( $addresses );
		$_SESSION['cart_address_id'] = $gBitCustomer->getField( 'customers_default_address_id', $first['address_book_id'] );
	}

} else {

}

$gBitSmarty->assign_by_ref( 'countryMenu', zen_get_country_list( 'country_id', $order->delivery['country']['countries_id'], 'onChange="updateShippingQuote(this.form);"' ) );

// set the cost to be able to calculate free shipping
$order->info = array('total' => $gBitCustomer->mCart->show_total(), // TAX ????
				//	 'currency' => $currency,
				//	 'currency_value'=> $currencies->currencies[$currency]['value']
					);

$freeShipping = FALSE;

// check free shipping based on order $total
if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
	$pass = false; 
	switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
		case 'national':
			if ($order->delivery['country_id'] == STORE_COUNTRY) {
				$pass = true; 
			}
			break;
		case 'international':
			if ($order->delivery['country_id'] != STORE_COUNTRY) {
				$pass = true; 
			}
			break;
		case 'both':
			$pass = true; 
			break;
	}
	if ( ($pass == true) && ($gBitCustomer->mCart->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
		$freeShipping = true;
		include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/order_total/ot_shipping.php');
	}
}

if($gBitCustomer->mCart->get_content_type() == 'virtual') {
	// virtual products need a free shipping
	$order->info['shipping_method'] = CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS;
	$order->info['shipping_cost'] = 0;
} elseif($freeShipping) {
	$order->info['shipping_method'] = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
	$order->info['shipping_cost'] = 0;
} elseif( !empty( $order->delivery['country']['countries_id'] ) ) {
	require( BITCOMMERCE_PKG_PATH.'classes/CommerceShipping.php');
	// weight and count needed for shipping !
	if( $gBitProduct->isValid() && !empty( $_REQUEST['cart_quantity'] ) ) {
		$weight = $gBitProduct->getWeight( $_REQUEST['cart_quantity'], $_REQUEST['id'] );
		$order->subtotal = $gBitProduct->getPurchasePrice( $_REQUEST['cart_quantity'], $_REQUEST['id'] );
	} elseif( $gBitCustomer->mCart->count_contents() > 0 ) {
		$weight = $gBitCustomer->mCart->show_weight();
	} else {
		$weight = 0;
	}
	$shipping = new CommerceShipping();

	if( empty( $stateMenu ) || !empty( $order->delivery['postcode'] ) ) {
eb( $weight ); // TODO FIX
		$gBitSmarty->assign_by_ref( 'quotes', $shipping->quote( $weight ) );
	}
	$order->subtotal = $gBitCustomer->mCart->show_total();
}

$gBitSmarty->assign( 'freeShipping', $freeShipping );
// end of shipping cost
// end free shipping based on order total

if( $gBitThemes->isAjaxRequest() ) {
	$gBitSmarty->display( 'bitpackage:bitcommerce/shipping_estimator_inc.tpl' );
} else {
	$gBitThemes->loadAjax( 'jquery', array( 'ui/ui.core.js' ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/popup_shipping_estimator.tpl' );
}

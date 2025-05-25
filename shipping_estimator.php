<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2020 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( '../kernel/includes/setup_inc.php' );
if( !empty( $_REQUEST['address_id'] ) ) {
	$_SESSION['sendto'] = $_REQUEST['address_id'];
}

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_user_inc.php' );
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceShoppingCart.php' );

global $gBitDb, $gBitUser, $gBitCustomer, $currencies;
$shoppingCart = new CommerceShoppingCart();

if( $gBitUser->isRegistered() &&  $addresses = $gBitCustomer->getAddresses() ) {
	$gBitSmarty->assignByRef( 'addresses', $addresses );
}

// Explicit country requested takes priority, even if registered.
if( (isset( $_REQUEST['address_id'] ) && $_REQUEST['address_id'] == 'custom' || empty( $_REQUEST['address_id'] ) ) && (!empty( $_REQUEST['country_id'] ) || !$gBitUser->isRegistered()) ) {
	if( isset( $_REQUEST['address_id'] ) && $_REQUEST['address_id'] == 'custom' ) {
		$_SESSION['sendto'] = 'custom';
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
	if( $countryInfo = zen_get_countries($_SESSION['cart_country_id']) ) {
		$shoppingCart->delivery = array(	
									'countries_id' => $countryInfo['countries_id'], 
									'title' => $countryInfo['countries_name'], 
									'countries_iso_code_2' => $countryInfo['countries_iso_code_2'], 
									'countries_iso_code_3' =>	$countryInfo['countries_iso_code_3'],
									'country_id' => $countryInfo['countries_id'],
									//add state zone_id
									'format_id' => $countryInfo['address_format_id']
								);
		// used as a check below for existence of states
		$stateMenu = zen_get_country_zone_list( 'zone_id', $shoppingCart->delivery['countries_id'], !empty( $_SESSION['cart_zone_id'] ) ? $_SESSION['cart_zone_id'] : NULL );
		$gBitSmarty->assignByRef( 'stateMenu', $stateMenu );
	}
	// Check for form zip code
	if( !empty( $_REQUEST['zip_code'] ) ) {
		$_SESSION['cart_zip_code'] = $_REQUEST['zip_code'];
	}

	$shoppingCart->delivery['postcode'] = !empty( $_SESSION['cart_zip_code'] ) ? $_SESSION['cart_zip_code'] : NULL;

	// Check for form state 
	if( isset( $_REQUEST['zone_id'] ) ) {
		$_SESSION['cart_zone_id'] = $_REQUEST['zone_id'];
	}
	if( !empty( $_SESSION['cart_zone_id'] ) ) {
		$shoppingCart->delivery['zone_id'] = (int)$_SESSION['cart_zone_id'];
	}

} elseif( !empty( $addresses ) && (empty( $_REQUEST['address_id'] ) || $_REQUEST['address_id'] != 'custom') ) {
	if( empty( $_SESSION['sendto'] ) && !empty( $addresses ) ) {
		// no selected address yet, snag the first one
		reset( $addresses );
		$first = current( $addresses );
		$_SESSION['sendto'] = $gBitCustomer->getField( 'customers_default_address_id', $first['address_book_id'] );
	}
} else {

}

$delivery = $shoppingCart->getDelivery();
$gBitSmarty->assign( 'countryMenu', zen_get_country_list( 'country_id', BitBase::getParameter( $delivery, 'countries_id', STORE_COUNTRY ), 'onChange="updateShippingQuote(this.form);"' ) );

// set the cost to be able to calculate free shipping
$shoppingCart->info = array(
	'total' => $gBitCustomer->mCart->show_total(), // TAX ????
//	 'currency' => $currency,
//	 'currency_value'=> $currencies->currencies[$currency]['value']
);

$freeShipping = FALSE;

// check free shipping based on order $total
if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
	$pass = false; 
	switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
		case 'national':
			if ($shoppingCart->delivery['country_id'] == STORE_COUNTRY) {
				$pass = true; 
			}
			break;
		case 'international':
			if ($shoppingCart->delivery['country_id'] != STORE_COUNTRY) {
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
	$shoppingCart->info['shipping_method'] = CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS;
	$shoppingCart->info['shipping_cost'] = 0;
} elseif($freeShipping) {
	$shoppingCart->info['shipping_method'] = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
	$shoppingCart->info['shipping_cost'] = 0;
} else {
	require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceShipping.php');
	// weight and count needed for shipping !
	if( $gBitProduct->isValid() && !empty( $_REQUEST['cart_quantity'] ) ) {
		$weight = $gBitProduct->getWeight( $_REQUEST['cart_quantity'], $_REQUEST['id'] );
		$shoppingCart->subtotal = $gBitProduct->getPurchasePrice( $_REQUEST['cart_quantity'], $_REQUEST['id'] );
	} elseif( $gBitCustomer->mCart->count_contents() > 0 ) {
		$weight = $gBitCustomer->mCart->show_weight();
	} else {
		$weight = 0;
	}
	$shipping = new CommerceShipping();
	$gBitSmarty->assign( 'quotes', $shipping->quote( $shoppingCart ) );
	$shoppingCart->subtotal = $gBitCustomer->mCart->show_total();
}

$gBitSmarty->assign( 'freeShipping', $freeShipping );
// end of shipping cost
// end free shipping based on order total

if( $gBitThemes->isAjaxRequest() ) {
	$gBitSmarty->assign( 'liCss', 'col-xs-12' );
	$gBitSmarty->display( 'bitpackage:bitcommerce/shipping_estimator_inc.tpl' );
} else {
	$gBitThemes->loadAjax( 'jquery', array( 'ui/ui.core.js' ) );
	$gBitSystem->display( 'bitpackage:bitcommerce/popup_shipping_estimator.tpl' );
}

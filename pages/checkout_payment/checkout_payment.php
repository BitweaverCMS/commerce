<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

if( !$gBitCustomer->mCart->count_contents() ) {
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

// Maybe customer registered with an inline form
if( !$gBitUser->isRegistered() ) {
	if( !empty( $_REQUEST['email'] ) ) {
		$gBitUser->register( $_REQUEST ); 
		if( !$gBitUser->isRegistered() ) {
			$gBitSmarty->assign( 'reg', $gBitUser->mErrors );
		}
	} else {
		$gBitSystem->fatalPermission( 'p_bitcommerce_product_purchase' );
	}
}


// if no shipping method has been selected, redirect the customer to the shipping method selection page
if( !isset( $_SESSION['shipping'] ) ) {
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// Stock Check and more....
if( !$gBitCustomer->mCart->verifyCheckout() ) {
	$messageStack->add('header', 'Please update your order ...', 'error');
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

if( !empty( $_REQUEST['choose_address'] ) || !empty( $_REQUEST['save_address'] ) ) {
	if( $gBitUser->isRegistered() ) {
		if( !empty( $_REQUEST['save_address'] ) ) {
			// process a new address
			$process = true;
			if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
				$_SESSION['billto'] = $_REQUEST['address'];
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
			} else {
				$gBitSmarty->assign( 'addressErrors', $gBitCustomer->mErrors );
				$_REQUEST['change_address'] = TRUE;
			}
		} elseif( !empty( $_REQUEST['choose_address'] ) && !empty( $_REQUEST['address'] ) ) {
			if( empty( $_SESSION['billto'] ) || $_SESSION['billto'] != $_REQUEST['address'] ) {
				if( $gBitCustomer->isAddressOwner( $_REQUEST['address'] ) ) {
					$_SESSION['billto'] = $_REQUEST['address'];
					zen_redirect( zen_href_link( FILENAME_CHECKOUT_PAYMENT, '', 'SSL' ) );
				}
			}
		}
	}
}

require_once(BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php');
$order = new order;
$gBitSmarty->assign( 'order', $order );

// if the no billing address, try to get one by default
if( empty( $_SESSION['billto'] ) || empty( $order->billing ) || !$gBitCustomer->isValidAddress( $order->billing ) ) {
	if( $gBitCustomer->isValidAddress( $order->delivery ) ) {
		$order->billing = $order->delivery;
		$_SESSION['billto'] = $_SESSION['sendto'];
	} elseif( $defaultAddressId = $gBitCustomer->getDefaultAddress() ) {
		$order->billing = $gBitCustomer->getAddress( $defaultAddress );
		$_SESSION['billto'] =	$defaultAddressId;
	}
}

if( isset( $_REQUEST['change_address'] ) || !$gBitCustomer->isValidAddress( $order->billing ) ) {
	if( $addresses = $gBitCustomer->getAddresses() ) {
		$gBitSmarty->assign( 'addresses', $addresses );
	}
	$gBitSmarty->assign( 'changeAddress', TRUE );
} else {
	// load all enabled payment modules
	require(DIR_FS_CLASSES . 'payment.php');
	$payment_modules = new payment;
	echo $payment_modules->javascript_validation(); 
	$gBitSmarty->assign( 'paymentSelection', $payment_modules->selection() );

	// Load the selected shipping module(needed to calculate tax correctly)
	require( BITCOMMERCE_PKG_PATH.'classes/CommerceShipping.php');
	$shipping_modules = new CommerceShipping( $_SESSION['shipping'] );

	require_once(DIR_FS_MODULES . 'require_languages.php');

	if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
		$messageStack->add('checkout_payment', $error['error'], 'error');
	}

	$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
	$breadcrumb->add(NAVBAR_TITLE_2);
	$gBitSmarty->assign( 'order', $order );

	require(DIR_FS_CLASSES . 'order_total.php');
	$order_total_modules = new order_total;
	$order_total_modules->process();
	$gBitSmarty->assign( 'orderTotalModules', $order_total_modules );
	$gBitSmarty->assign( 'creditSelection', $order_total_modules->credit_selection() );
}

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_payment.tpl' );


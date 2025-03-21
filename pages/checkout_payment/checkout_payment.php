<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'page_checkout_parameters_inc.php' );

if( !$gBitUser->isRegistered() || !empty( $_REQUEST['choose_address'] ) || !empty( $_REQUEST['save_address'] ) ) {
	if( $gBitUser->isRegistered() ) {
		if( !empty( $_REQUEST['save_address'] ) ) {
			// process a new address
			$process = true;
			if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
				$_SESSION['billto'] = $_REQUEST['address'];
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT));
			} else {
				$gBitSmarty->assign( 'addressErrors', $gBitCustomer->mErrors );
				$_REQUEST['change_address'] = TRUE;
			}
		} elseif( !empty( $_REQUEST['choose_address'] ) && !empty( $_REQUEST['address'] ) ) {
			if( empty( $_SESSION['billto'] ) || $_SESSION['billto'] != $_REQUEST['address'] ) {
				if( $gBitCustomer->isAddressOwner( $_REQUEST['address'] ) ) {
					$_SESSION['billto'] = $_REQUEST['address'];
					zen_redirect( zen_href_link( FILENAME_CHECKOUT_PAYMENT ) );
				}
			}
		}
	}
	if( !empty( $_REQUEST['save_address'] ) ) {
		// an inline registration failed. Verify the fields and reassign so customer doesn't lose info
		$addressErrors = array();
		$gBitCustomer->verifyAddress( $_REQUEST, $addressErrors );
		$gBitSmarty->assign( 'address', $_REQUEST['address_store'] );
		$gBitSmarty->assign( 'addressErrors', $addressErrors );
	}
}

if ($gBitCustomer->mCart->count_contents() <= 0) {
	// if there is nothing in the customers cart, redirect them to the shopping cart page
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
} elseif (!$_SESSION['customer_id']) {
	// if the customer is not logged on, redirect them to the login page
	$_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
	zen_redirect(FILENAME_LOGIN);
} elseif( empty( $_SESSION['shipping_quote'] )  && ($gBitCustomer->mCart->get_content_type() != 'virtual') ) {
	// if no shipping method has been selected, redirect the customer to the shipping method selection page
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
} elseif( !$gBitCustomer->mCart->verifyCheckout() ) {
	// Stock Check and more....
	$messageStack->add('header', 'Please update your order ...', 'error');
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

require_once(BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');

// if the no billing address, try to get one by default
if( empty( $_SESSION['billto'] ) ) {
	if( !empty( $_SESSION['sendto'] ) ) {
		$_SESSION['billto'] = $_SESSION['sendto'];
	} elseif( $defaultAddressId = $gBitCustomer->getDefaultAddressId() ) {
		$_SESSION['billto'] =	$defaultAddressId;
	}
}

if( !empty( $_REQUEST['ot_error_encode'] ) ) {
	if( $errorHash = unserialize( $_REQUEST['ot_error_encode'] ) ) {
		$_REQUEST['ot_errors'] = $errorHash;
	}
}

$quoteOrder = CommerceOrder::orderFromCart( $gBitCustomer->mCart, $_SESSION );
$gBitSmarty->assign( 'quoteOrder', $quoteOrder );
if( isset( $_REQUEST['change_address'] ) ) {
	if( $addresses = $gBitCustomer->getAddresses() ) {
		$gBitSmarty->assign( 'addresses', $addresses );
	}
	$gBitSmarty->assign( 'changeAddress', TRUE );
} else {
	// load all enabled payment modules
	require( BITCOMMERCE_PKG_CLASS_PATH.'CommercePaymentManager.php' );
	$paymentManager = new CommercePaymentManager();
	echo $paymentManager->javascript_validation(); 
	$gBitSmarty->assign( 'paymentSelection', $paymentManager->selection() );

	require_once(DIR_FS_MODULES . 'require_languages.php');

	if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
		$messageStack->add('checkout_payment', $error['error'], 'error');
	}

	$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
	$breadcrumb->add(NAVBAR_TITLE_2);

	$quoteOrder->otProcess( $_REQUEST, $_SESSION );
}

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_payment.tpl' );


<?php
// +--------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org									|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+

global $gBitSmarty;

if( !empty( $_REQUEST['update_cart'] ) || !empty( $_REQUEST['checkout'] ) ) {
	// customer wants to update the product quantity in the shopping cart, empty quantity will remove it from the cart
	foreach( array_keys( $_REQUEST['cart_quantity'] ) as $productsKey ) {
		$gBitCustomer->mCart->updateQuantity( $productsKey, $_REQUEST['cart_quantity'][$productsKey] );
	}

	// check box multi-delete
	if( isset( $_REQUEST['cart_delete'] ) ) {
		foreach( $_REQUEST['cart_delete'] as $productsKey ) {
			$gBitCustomer->mCart->updateQuantity( $productsKey, 0 );
		}
	}

	if( !empty( $_REQUEST['checkout'] ) ) {
		zen_redirect(zen_href_link('checkout_proof'));
	}
} elseif( !empty( $_REQUEST['remove_product'] ) ) {
	$gBitCustomer->mCart->updateQuantity( $_REQUEST['remove_product'], 0 );
}
$gBitCustomer->mCart->calculate(TRUE);
$gBitSmarty->assign_by_ref( 'gBitCustomer', $gBitCustomer );
$gBitSmarty->display( 'bitpackage:bitcommerce/page_shopping_cart.tpl' );


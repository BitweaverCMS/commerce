<?php
	
require_once(BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');

$gCommerceSystem->setHeadingTitle( tra( 'Checkout Proof' ) );

$order = new order; 

$proofProducts = array();
foreach ( $order->contents as $itemKey => $item ){
	if( $itemObject = $order->getProductObject( $itemKey ) ) {
		if( $template = $itemObject->needsCheckoutReview( $item ) ){
			$proofProducts[] = array( 'object' => $itemObject, 'template' => $template, 'cart_item' => $item );
		}
	}
}

if( !empty( $proofProducts ) ) {
	$gBitSmarty->assignByRef( 'proofProducts', $proofProducts );
	//checkout_proof will loop through the array of includes and print them out, it has a next button at the bottom and a warnin
	global $gBitSystem;
	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_proof.tpl' );
} else { 
	// Cart does not need to be reviewed, all products are assumed to be OK
	bit_redirect( zen_get_page_url( 'checkout_shipping' ) );
}
?>

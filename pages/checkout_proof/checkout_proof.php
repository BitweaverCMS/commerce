<?php
	
require_once(DIR_FS_CLASSES . 'order.php');

global $gBitCustomer;

$order = new order; 

foreach ( $order->contents as $item ){
	if( empty($item['type_class']) ){
		$item['type_class'] = 'CommerceProduct'; //If not a derived type, must be base type. 
	}
	$loadedItem = new $item['type_class']($item['products_id']);
	$loadedItem->load();
	if( $template = $loadedItem->needsCheckoutReview( $item ) ){
		$pendingItems[] = $loadedItem;
		$pendingTemplates[] = $template;
	}
}

if( !empty( $pendingItems ) ){
	$gBitSmarty->assign_by_ref( 'pendingItems', $pendingItems );
	$gBitSmarty->assign_by_ref( 'pendingTemplates', $pendingTemplates);
	//checkout_proof will loop through the array of includes and print them out, it has a next button at the bottom and a warnin
	global $gBitSystem;
 	$gBitSystem->display( 'bitpackage:bitcommerce/checkout_proof.tpl' );
}else{ //User does not need to review any books, all have been ordered before and are assumed to be OK
	bit_redirect(BITCOMMERCE_PKG_SSL_URI.'?main_page=checkout_shipping');
}
?>

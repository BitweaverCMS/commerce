<?php

global $gBitSmarty;

require(BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php');
$order = new order( $_GET['order_id'] );

if( $order->hasViewPermission() ) {
	$order->loadHistory();
	$gBitSmarty->assign( 'order', $order );
	$gBitSmarty->display( 'bitpackage:bitcommerce/order_invoice.tpl' );
} else {
	bit_redirect( BITCOMMERCE_PKG_URL.'index.php?main_page='.FILENAME_ACCOUNT_HISTORY );
}


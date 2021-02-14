<?php

global $gBitSmarty;

require(BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');
if( !empty( $_REQUEST['order_id'] ) && ($order = new order( $_REQUEST['order_id'] ) ) && $order->hasViewPermission() ) {
	$order->loadHistory();
	$gBitSmarty->assign( 'showPricing', TRUE );
	$gBitSmarty->assign( 'order', $order );
	$gBitSmarty->display( 'bitpackage:bitcommerce/order_invoice.tpl' );
} else {
	bit_redirect( BITCOMMERCE_PKG_URL.'index.php?main_page='.FILENAME_ACCOUNT_HISTORY );
}

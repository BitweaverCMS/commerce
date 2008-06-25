<?php

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceOrderManager.php' );

$orderManager = new CommerceOrderManager();

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$history = $orderManager->getProductHistory( $_REQUEST );

$gBitSmarty->assign_by_ref( 'productHistory', $history );

$gBitSystem->display( 'bitpackage:bitcommerce/admin_product_history.tpl', 'Product History' , array( 'display_mode' => 'admin' ));

?>

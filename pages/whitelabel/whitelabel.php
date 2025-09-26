<?php

$gBitSystem->verifyPermission('p_bitcommerce_oem');

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$gQueryUser = $gBitUser; // new BitPermUser( $_REQUEST['user_id'] );

if( !empty( $_POST['save_whitelabel'] ) ) {
	$gQueryUser->storePreference( 'commerce_order_auto_process', !empty( $_REQUEST['commerce_order_auto_process'] ) ? 'y' : NULL );

	$returnAddress = trim( BitBase::getParameter( $_REQUEST, 'oem_return_address' ) );

	$gQueryUser->storePreference( 'oem_return_address', !empty( $returnAddress ) ? $returnAddress : NULL );
}

$gBitSmarty->assignByRef( 'gQueryUser', $gQueryUser );

$gCommerceSystem->setHeadingTitle( tra( 'Storefront Customization' ) );
$gBitSmarty->display( 'bitpackage:bitcommerce/page_whitelabel.tpl' );

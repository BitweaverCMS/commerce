<?php

$gBitSystem->verifyPermission('p_bitcommerce_retailer');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceCommission.php' );

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$commissionManager = new CommerceProductCommission();

if( !empty( $_REQUEST['save_commission_settings'] ) ) {
	$gBitUser->storePreference( 'commissions_payment_method', $_REQUEST['commissions_payment_method'] );
	if( !empty( $_REQUEST['commissions_'.$_REQUEST['commissions_payment_method'].'_address'] ) ) {
		$gBitUser->storePreference( 'commissions_'.$_REQUEST['commissions_payment_method'].'_address', $_REQUEST['commissions_'.$_REQUEST['commissions_payment_method'].'_address'] );
	}
}

$gBitSmarty->assign( 'commissionList', $commissionManager->getUserHistory( array( 'user_id' => $gBitCustomer->mCustomerId ) ) );
if( $addresses = $gBitCustomer->getAddresses() ) {
	foreach( $addresses AS $addrId=>$addr ) {
		$addressList[$addr['address_book_id']] = zen_address_format( $addr, 0, ' ', ' ' );
	}
	$gBitSmarty->assignByRef( 'addressList', $addressList );
	$gBitSmarty->assign( 'defaultAddressId', $gBitUser->getPreference( 'commissions_check_address', $gBitCustomer->getDefaultAddressId() ) );
}

$paymentOptions[''] = tra( 'Please Select Below...' );
$paymentOptions['storecredit'] = tra( 'Store Credit' );
$paymentOptions['paypal'] = tra( 'PayPal' );
$paymentOptions['worldpay'] = tra( 'WorldPay' );
$paymentOptions['check'] = tra( 'Check' );
$gBitSmarty->assign( 'paymentOptions', $paymentOptions );

$gCommerceSystem->setHeadingTitle( tra( 'Commissions' ) );
$gBitSmarty->display( 'bitpackage:bitcommerce/commissions.tpl' );

?>

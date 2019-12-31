<?php

$gBitSystem->verifyPermission('p_bitcommerce_retailer');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceCommission.php' );

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$commissionManager = new CommerceCommission();

if( !empty( $_REQUEST['save_commission_settings'] ) ) {
	$gBitUser->storePreference( 'commissions_payment_method', $_REQUEST['commissions_payment_method'] );
	if( !empty( $_REQUEST['commissions_'.$_REQUEST['commissions_payment_method'].'_address'] ) ) {
		$gBitUser->storePreference( 'commissions_'.$_REQUEST['commissions_payment_method'].'_address', $_REQUEST['commissions_'.$_REQUEST['commissions_payment_method'].'_address'] );
	}
}

$gBitSmarty->assign_by_ref( 'commissionList', $commissionManager->getHistory( array( 'user_id' => $gBitCustomer->mCustomerId ) ) );
if( $addresses = $gBitCustomer->getAddresses() ) {
	foreach( $addresses AS $addrId=>$addr ) {
		$addressList[$addr['address_book_id']] = zen_address_format( zen_get_address_format_id( $addr['country_id'] ), $addr, 0, ' ', ' ' );
	}
	$gBitSmarty->assign_by_ref( 'addressList', $addressList );
	$gBitSmarty->assign( 'defaultAddressId', $gBitUser->getPreference( 'commissions_check_address', $gBitCustomer->getDefaultAddress() ) );
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

<?php

$gBitSystem->verifyPermission('p_commerce_retailer');

if( !empty( $_REQUEST['save_commission_settings'] ) ) {
	$gBitUser->storePreference( 'commissions_payment_method', $_REQUEST['commissions_payment_method'] );
	if( !empty( $_REQUEST['commissions_'.$_REQUEST['commissions_payment_method'].'_address'] ) ) {
		$gBitUser->storePreference( 'commissions_'.$_REQUEST['commissions_payment_method'].'_address', $_REQUEST['commissions_'.$_REQUEST['commissions_payment_method'].'_address'] );
	}
}

$gBitSmarty->assign_by_ref( 'commissionList', $gBitCustomer->getCommissions() );
if( $addresses = $gBitCustomer->getAddresses( $gBitCustomer->mCustomerId ) ) {
	foreach( $addresses AS $addrId=>$addr ) {
		$addressList[$addr['address_book_id']] = zen_address_format( zen_get_address_format_id( $addr['country_id'] ), $addr, 0, ' ', ' ' );
	}
	$gBitSmarty->assign_by_ref( 'addressList', $addressList );
	$gBitSmarty->assign( 'defaultAddressId', $gBitUser->getPreference( 'commissions_check_address', $gBitCustomer->getDefaultAddress() ) );
}

$paymentOptions['storecredit'] = tra( 'Store Credit' );
$paymentOptions['paypal'] = 'PayPal';
$paymentOptions['worldpay'] = 'WorldPay';
$paymentOptions['check'] = tra( 'Check' );
$gBitSmarty->assign( 'paymentOptions', $paymentOptions );

define( 'HEADING_TITLE', tra( 'Commissions' ) );
$gBitSmarty->display( 'bitpackage:bitcommerce/commissions.tpl' );

?>

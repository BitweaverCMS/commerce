<?php

if (!$gBitUser->isRegistered() ) {
	$_SESSION['navigation']->set_snapshot();
	zen_redirect(FILENAME_LOGIN);
}

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceVoucher.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrderManager.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePaymentManager.php' );
$paymentManager = new CommercePaymentManager( BitBase::getParameter( $_REQUEST, 'payment_method') );

global $gBitUser;
$gBitUser->verifyRegistered();

$countryId = BitBase::getParameter( $_REQUEST, 'country_id', STORE_COUNTRY );
if( !$countryId && !$gBitUser->hasPermission( 'p_bitcommerce_admin' ) ) {
	if( $defaultAddress = $gBitCustomer->getAddress( $gBitCustomer->getDefaultAddressId() ) ) {
		if( isset( $defaultAddress['countries_id'] ) ) {
			$countryId = $defaultAddress['countries_id'];
		} else {
			$countryId = zen_get_country_id( $defaultAddress );
		}
	}
}

$payment = array();
if( $editPayment = BitBase::getParameter( $_REQUEST, 'edit_payment' ) ) {
	$payment = $gCommerceOrderManager->getPayment();
}

$paymentTypes = $gCommerceOrderManager->getPaymentTypes();
sort( $paymentTypes );
$gBitSmarty->assign( 'paymentTypes', array_combine( $paymentTypes, $paymentTypes ) );
$gBitSmarty->assignByRef( 'payment', $payment );
$order = new CommerceOrder();
$gBitSmarty->assignByRef( 'order', $order );
$orderStatuses = commerce_get_statuses( TRUE );
$orderStatuses[''] = 'No Change';
$gBitSmarty->assign( 'countryPullDown', zen_get_country_list('country_id', $countryId, 'required' ) );
$gBitSmarty->assign( 'orderStatuses', $orderStatuses );

$feedback = array( 'error' => [] );

if( $action = BitBase::getParameter( $_REQUEST, 'action' ) ) {
	switch( $action ) {
		case 'record_payment':
			if( $gBitThemes->isAjaxRequest() ) {
				$gBitSmarty->display( 'bitpackage:bitcommerce/order_payment_edit.tpl' );
				exit;
			}
			break;
		case 'save_payment':
			if( $paymentManager->payInvoice( $_REQUEST ) ) {
				bit_redirect( zen_get_page_url( 'invoices' ) );
			} else {
				$feedback['error'] = array_merge( $feedback['error'], $paymentManager->mErrors );
			}
			break;
	}
}

$gBitThemes->loadJavascript( CONFIG_PKG_PATH.'themes/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js');

$dueOrders = $paymentManager->getDueOrders();
$gBitSmarty->assignByRef( 'dueOrders', $dueOrders );

$paymentModules = $gCommerceSystem->scanModules( 'payment', TRUE );

$gBitSmarty->assign( 'feedback', $feedback );
$gBitSmarty->assignByRef( 'paymentModules', $paymentModules );

$gBitSmarty->assign( 'paymentSelections', $paymentManager->selection() );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_invoices.tpl' );


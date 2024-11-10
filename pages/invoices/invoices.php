<?php

if (!$gBitUser->isRegistered() ) {
	$_SESSION['navigation']->set_snapshot();
	zen_redirect(FILENAME_LOGIN);
}

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceVoucher.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePaymentManager.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrderManager.php');

global $gBitUser;
$gBitUser->verifyRegistered();

$payment = array();
if( is_numeric( $editPayment ) ) {
	$payment = $gCommerceOrderManager->getPayment();
}

$paymentTypes = $gCommerceOrderManager->getPaymentTypes();
sort( $paymentTypes );
$gBitSmarty->assign( 'paymentTypes', array_combine( $paymentTypes, $paymentTypes ) );
$gBitSmarty->assign_by_ref( 'payment', $payment );
$order = new CommerceOrder();
$gBitSmarty->assign_by_ref( 'order', $order );
$orderStatuses = commerce_get_statuses( TRUE );
$orderStatuses[''] = 'No Change';
$gBitSmarty->assign( 'countryPullDown', zen_get_country_list('country_id', $countryId, 'required' ) );
$gBitSmarty->assign( 'orderStatuses', $orderStatuses );

if( $action = BitBase::getParameter( $_REQUEST, 'action' ) ) {
	switch( $action ) {
		case 'record_payment':
			if( $gBitThemes->isAjaxRequest() ) {
				$gBitSmarty->display( 'bitpackage:bitcommerce/order_payment_edit.tpl' );
				exit;
			}
			break;
		case 'save_payment':
			$amountPaid = 0.00;
			$ordersPaid = 0;

			foreach( $_REQUEST['id'] as $optionId => $optionValueHash ) {
				foreach( $optionValueHash as $optionValueId => $optionValue ) {
					if( $dueOrders = $gCommerceOrderManager->getDueOrders( array( 'payment_number' => $optionValue ) ) ) {
						foreach( $dueOrders as $userId => $userOrders ) {
							foreach( $userOrders as $paymentNumber => $paymentOrders ) {
								$ordersCount = count( $paymentOrders );
								foreach( $paymentOrders as $paymentOrderHash ) {
									$order = new order( $paymentOrderHash['orders_id'] );
									if( $amountDue = $order->getField( 'amount_due' ) ) {
										$ordersPaid++;
										$amountPaid += $amountDue;
										$paymentHash = $_REQUEST;
										if( $ordersCount > 1 ) {
											$paymentHash['oID'] = $paymentOrderHash['orders_id'];
											$paymentHash['payment_amount'] = $amountDue;
											$paymentHash['comments'] = trim( "PAID $ordersPaid of $ordersCount, ". $currencies->format( $amountPaid, FALSE, '', '', FALSE ) ." of " . $currencies->format( $_REQUEST['payment_amount'], FALSE, '', '', FALSE ) . "\n\n" . $paymentHash['comments'] );
										}
										if( $gCommerceOrderManager->storeOrdersPayment( $paymentHash, $order ) ) {
										}
									}
								}
							}
						}
					}
				}
			}
			break;
	}
}

$gBitThemes->loadJavascript( CONFIG_PKG_PATH.'themes/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js');

$dueOrders = $gCommerceOrderManager->getDueOrders();
$gBitSmarty->assign_by_ref( 'dueOrders', $dueOrders );

$_REQUEST['order_ids'] = array( $_REQUEST['oID'] );

$paymentModules = $gCommerceSystem->scanModules( 'payment', TRUE );

$gBitSmarty->assign_by_ref( 'paymentModules', $paymentModules );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_invoices.tpl' );


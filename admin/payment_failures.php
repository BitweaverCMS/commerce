<?php
require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePaymentManager.php' );

$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );

$listHash = array(
	'days' => BitBase::getParameter( $_REQUEST, 'days', 7 ),
	'payment_status' => BitBase::getParameter( $_REQUEST, 'payment_status', '' ),
	'customers_id' => BitBase::getParameter( $_REQUEST, 'customers_id', '' ),
	'customers_email' => BitBase::getParameter( $_REQUEST, 'customers_email', '' ),
	'ip_address' => BitBase::getParameter( $_REQUEST, 'ip_address', '' ),
	'payment_module' => BitBase::getParameter( $_REQUEST, 'payment_module', '' ),
	'max_records' => 250,
);

$paymentManager = new CommercePaymentManager();
$failures = $paymentManager->getFailedPayments( $listHash );
$gBitSmarty->assignByRef( 'paymentFailures', $failures );
$gBitSmarty->assign( 'paymentFailureStatuses', array(
	'' => tra( 'All classes' ),
	'declined' => tra( 'Declined' ),
	'invalid' => tra( 'Invalid' ),
	'infra' => tra( 'Infra' ),
) );
$gBitSmarty->assign( 'paymentFailureDays', array(
	'1' => tra( '1 day' ),
	'7' => tra( '7 days' ),
	'30' => tra( '30 days' ),
	'90' => tra( '90 days' ),
) );

$gBitSystem->display( 'bitpackage:bitcommerce/admin_payment_failures.tpl', tra( 'Payment Failures' ), array( 'display_mode' => 'admin' ) );

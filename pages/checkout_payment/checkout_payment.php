<?php

echo $payment_modules->javascript_validation(); 

$order_total_modules->process();

if ($messageStack->size('checkout_payment') > 0) {
	echo $messageStack->output('checkout_payment');
}
$gBitSmarty->assign( 'orderTotalModules', $order_total_modules );
$gBitSmarty->assign( 'paymentSelection', $payment_modules->selection() );
$gBitSmarty->assign( 'creditSelection', $order_total_modules->credit_selection() );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/checkout_payment.tpl' );


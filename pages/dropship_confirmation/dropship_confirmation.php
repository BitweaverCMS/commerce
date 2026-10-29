<?php
// Drop ship confirmation: the read-only summary between the drop ship page and Create Orders.
// The Finalize Orders button posts the drop ship Process form here. The batch is priced at its combined
// quantity, each order is built (not created) the way Process will build it, and the Create Orders form
// posts the same fields back to the drop ship page, whose Process branch inserts the orders.

if (!$gBitUser->isRegistered() ) {
	$_SESSION['navigation']->set_snapshot();
	zen_redirect(FILENAME_LOGIN);
}

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceTemporaryCart.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceVoucher.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrderManager.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceShipping.php');
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePaymentManager.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'address_import_lib.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'dropship_lib.php' );

global $gBitUser, $gBitCustomer, $gBitThemes, $gCommerceShipping, $gCommerceSystem, $currencies;
$gBitUser->verifyRegistered();

if( !$gBitUser->hasPermission( 'p_bitcommerce_dropship' ) || !BitBase::getParameter( $_POST, 'dropship_finalize' ) ) {
	zen_redirect( zen_href_link( 'dropship', '', 'SSL' ) );
}

$gBitThemes->loadCss( BITCOMMERCE_PKG_PATH.'css/dropship.css', FALSE, 300, FALSE, FALSE, FALSE );

// The post came from the Process form, so keep the per-row "save to address book" ticks on the session rows
if( !empty( $_SESSION['dropship'] ) && is_array( $_SESSION['dropship'] ) ) {
	$saveTicks = BitBase::getParameter( $_POST, 'save_address', array() );
	$saveTicks = is_array( $saveTicks ) ? array_flip( array_map( 'strval', $saveTicks ) ) : array();
	foreach( $_SESSION['dropship'] as $tickIdx => &$tickRow ) {
		$tickRow['_save'] = isset( $saveTicks[(string)$tickIdx] );
	}
	unset( $tickRow );
}

$batch = bc_dropship_collect( $_POST );
if( !empty( $batch['errors'] ) || empty( $batch['orders'] ) ) {
	$reasons = array_merge( $batch['errors'], $batch['skipped'] );
	if( empty( $reasons ) ) {
		$reasons[] = tra( 'No address is ready to order.' );
	}
	$_SESSION['dropship_feedback'] = array( 'error' => $reasons );
	zen_redirect( zen_href_link( 'dropship', '', 'SSL' ) );
}

// Quantity discounts come from the whole batch, not each address
bc_dropship_apply_batch_pricing( $batch['quantities'] );

$sessionCopy = $_SESSION;
$confirmOrders = array();
$itemsTotal = 0.0;
$shippingTotal = 0.0;
$grandTotal = 0.0;
$readyCount = 0;
foreach( $batch['orders'] as $orderIdx => $batchOrder ) {
	$row = $batchOrder['row'];
	$dropSession = array_merge( $sessionCopy, $row );
	$dropSession['shipping_quote'] = $batchOrder['quote'];
	// ot_expedite reads the request the way checkout does
	$_REQUEST['main_page'] = 'checkout_process';
	$_REQUEST['ot_expedite'] = BitBase::getParameter( $row, 'ot_expedite' );

	$problems = array();
	$cart = bc_dropship_cart( $row );
	if( !$cart->count_contents() ) {
		$problems[] = tra( 'No products to ship.' );
		$problems = array_merge( $problems, array_values( $cart->mErrors ) );
	}
	$order = CommerceOrder::orderFromCart( $cart, $dropSession );
	if( !empty( $order->mErrors ) ) {
		$problems = array_merge( $problems, array_values( $order->mErrors ) );
	}
	if( $otErrors = $order->otCollectPosts( $row, $dropSession ) ) {
		$problems = array_merge( $problems, array_values( $otErrors ) );
	}
	$otOutput = empty( $problems ) ? $order->otProcess( $row, $dropSession ) : array();

	$shippingCost = (float)BitBase::getParameter( $order->info, 'shipping_cost', 0 );
	$total = (float)BitBase::getParameter( $order->info, 'total', 0 );
	$subtotal = !empty( $order->subtotal ) ? (float)$order->subtotal : (float)$cart->show_total();
	$confirmOrders[] = array(
		'idx' => $orderIdx,
		'label' => $batchOrder['label'],
		'delivery' => $order->getDelivery(),
		'telephone' => (string)BitBase::getParameter( $row, 'delivery_telephone', '' ),
		'comments' => (string)BitBase::getParameter( $row, 'comments', '' ),
		'cart' => $cart,
		'quote' => $batchOrder['quote'],
		'shipping_method' => (string)BitBase::getParameter( $order->info, 'shipping_method', '' ),
		'shipping_display' => $currencies->format( $shippingCost, TRUE, '', '', FALSE ),
		'subtotal_display' => $currencies->format( $subtotal, TRUE, '', '', FALSE ),
		'total_display' => $currencies->format( $total, TRUE, '', '', FALSE ),
		'ot' => $otOutput,
		'problems' => $problems,
		'save' => $batchOrder['save'],
	);
	if( empty( $problems ) ) {
		$readyCount++;
		$itemsTotal += $subtotal;
		$shippingTotal += $shippingCost;
		$grandTotal += $total;
	}
}

// What the batch pricing did, per cart line (product + options, since many products are priced by their options):
// copies across the batch, the tier price, and the single-copy price it is measured against (the same comparison
// each cart line's "n% discount" makes)
$batchLines = array();
foreach( $confirmOrders as $co ) {
	if( !empty( $co['problems'] ) || !is_array( $co['cart']->contents ) ) {
		continue;
	}
	foreach( $co['cart']->contents as $lineKey => $line ) {
		if( !isset( $batchLines[$lineKey] ) ) {
			if( !($lineHash = $co['cart']->getProductHash( $lineKey )) ) {
				continue;
			}
			$tierPrice = (float)BitBase::getParameter( $lineHash, 'price', 0 );
			$attributes = is_array( BitBase::getParameter( $line, 'attributes' ) ) ? $line['attributes'] : array();
			CommerceProduct::setDiscountQuantityFloor( array() );
			$alonePrice = (float)$co['cart']->getProductObject( $lineKey )->getPurchasePrice( 1, $attributes );
			bc_dropship_apply_batch_pricing( $batch['quantities'] );
			$options = array();
			foreach( $attributes as $attrHash ) {
				if( is_array( $attrHash ) && !empty( $attrHash['products_options_values_name'] ) ) {
					$options[] = $attrHash['products_options_values_name'];
				}
			}
			$batchLines[$lineKey] = array(
				'title' => (string)BitBase::getParameter( $lineHash, 'name', '' ),
				'options' => implode( ', ', $options ),
				'quantity' => 0,
				'price_display' => $currencies->format( $tierPrice, TRUE, '', '', FALSE ),
				'alone_display' => ( $alonePrice > $tierPrice + 0.005 ) ? $currencies->format( $alonePrice, TRUE, '', '', FALSE ) : '',
				'percent' => ( $alonePrice > 0 && $alonePrice > $tierPrice + 0.005 ) ? (int)round( 100 - $tierPrice / $alonePrice * 100 ) : 0,
			);
		}
		$batchLines[$lineKey]['quantity'] += (int)$line['products_quantity'];
	}
}
$batchLines = array_values( $batchLines );

$paymentManager = new CommercePaymentManager( BitBase::getParameter( $batch['payment'], 'payment_method' ) );
$paymentConfirmation = $paymentManager->confirmation( $batch['payment'] );

$gBitSmarty->assign( 'confirmOrders', $confirmOrders );
$gBitSmarty->assign( 'confirmReadyCount', $readyCount );
$gBitSmarty->assign( 'confirmBatchLines', $batchLines );
$gBitSmarty->assign( 'confirmPayment', $batch['payment'] );
$gBitSmarty->assign( 'confirmPaymentConfirmation', $paymentConfirmation );
$gBitSmarty->assign( 'confirmNotify', $batch['notify'] );
$gBitSmarty->assign( 'confirmPoNumber', (string)BitBase::getParameter( $batch['payment'], 'payment_po_number', '' ) );
$gBitSmarty->assign( 'confirmItemsDisplay', $currencies->format( $itemsTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'confirmShippingDisplay', $currencies->format( $shippingTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'confirmTotalDisplay', $currencies->format( $grandTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'confirmSkipped', $batch['skipped'] );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_dropship_confirmation.tpl' );

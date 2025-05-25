<?php

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
$paymentManager = new CommercePaymentManager( BitBase::getParameter( $_REQUEST, 'payment_method') );

global $gBitUser;
$gBitUser->verifyRegistered();

$feedback = array( 'success' => array(), 'error' => array() );

if( BitBase::getParameter( $_POST, 'dropship_upload' ) ) {
	if( !empty( $_FILES['drop_csv'] ) ) {
		if( $csvData = parseCSVToHash( $_FILES['drop_csv']['tmp_name'] ) ) {
			$_SESSION['dropship'] = $csvData;
		}
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_process' ) ) {
	$outputHash = array();

	$sessionCopy = $_SESSION;
	unset( $_SESSION['orders_id'] );

	foreach( $_REQUEST['dropship_order'] as $orderIdx ) {
		if( !empty( $_SESSION['dropship'] ) && $checkoutHash = BitBase::getParameter( $_SESSION['dropship'], $orderIdx ) ) {
			if( empty( $_REQUEST['notify'] ) ) {
				$checkoutHash['no_order_email'] = TRUE;
			}

			// Some payment parameters are expected in request, and some in session during browser checkout. Merge hash and session and them all in under the session
			$dropSession = array_merge( $sessionCopy, $checkoutHash );
			$dropSession['shipping_quote'] = BitBase::getParameter( $checkoutHash, 'shipping_method' ).'_'.BitBase::getParameter( $checkoutHash, 'shipping_method_code' );

			// These next two lines are a hack for ot_expedite, which needs to be cleaned up
			$_REQUEST['main_page'] = 'checkout_process';
			$_REQUEST['ot_expedite'] = $_SESSION['ot_expedite'] = BitBase::getParameter( $checkoutHash, 'ot_expedite' );

			if( $dropShipCart = bc_dropship_cart( $checkoutHash ) ) {
				$order = CommerceOrder::orderFromCart( $dropShipCart, $dropSession );
				$deliveryString = zen_address_format( $order->delivery, FALSE, ' ', ',' );
				if( $errors = $order->otCollectPosts( $checkoutHash, $dropSession ) ) {
					$outputStatusCode = HttpStatusCodes::HTTP_NOT_ACCEPTABLE;
					$feedback['error'][] = tra( 'Order Error' ).' '.tra( 'to' ).' '.$deliveryString;
					$feedback['error'][] = implode( "<br>", $errors );
bit_error_email( 'Dropship Order Failure '.$outputStatusCode, $deliveryString."\n\n".implode( "\n", array_merge( $errors, $checkoutHash )), $gBitUser->mInfo );
				} else {
					// load the selected payment module
					if( !$order->process( $checkoutHash, $dropSession ) ) {
						$outputHash = array_merge( $outputHash, $order->mErrors );
						$outputStatusCode = HttpStatusCodes::HTTP_NOT_ACCEPTABLE;
bit_error_email( 'Dropship Order Failure', $order->mErrors, $outputHash, $checkoutHash, $dropSession );
					} else {
						unset( $_SESSION['dropship'][$orderIdx] );
						$outputStatusCode = HttpStatusCodes::HTTP_OK;
						$feedback['success'][] = tra( 'Order Created:' ).' <a href="'.zen_href_link( 'account_history_info', 'order_id='.$order->mOrdersId ).'">'.$order->mOrdersId.'</a> '.tra( 'to' ).' '.$deliveryString;
					}
				}
			}
		}
	}
}

$gBitSmarty->assignByRef( 'feedback', $feedback );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_dropship.tpl' );

function bc_dropship_quote( $dropShipCart ) {
	global $gCommerceShipping;

	$quotes = $gCommerceShipping->quote($dropShipCart);

	return $quotes;
}

function bc_dropship_cart( $pOrderHash ) {

	$hashCart = new CommerceTemporaryCart();
	$hashCart->loadFromHash( $pOrderHash );

	return $hashCart;
}

function parseCSVToHash($file) {
	// Check if file exists and is readable
	if (!file_exists($file) || !is_readable($file)) {
		return ['error' => 'File could not be read'];
	}

	$result = [];
	$headers = [];
	
	try {
		// Open the CSV file
		if (($handle = fopen($file, 'r')) !== false) {
			// Get the header row
			$headers = fgetcsv($handle);
			
			if ($headers === false || empty($headers)) {
				fclose($handle);
				return ['error' => 'Empty file or no headers found'];
			}

			// Process remaining rows
			while (($data = fgetcsv($handle)) !== false) {
				// Skip empty rows
				if (empty(array_filter($data))) {
					continue;
				}

				$row = [];
				// Combine headers with row data
				foreach ($headers as $index => $header) {
					// Use empty string if no data for column
					$row[$header] = isset($data[$index]) ? $data[$index] : '';
				}
				$result[] = $row;
			}
			fclose($handle);
		} else {
			return ['error' => 'Could not open file'];
		}
	} catch (Exception $e) {
		return ['error' => 'Error processing file: ' . $e->getMessage()];
	}

	return $result;
}

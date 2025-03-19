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

if( BitBase::getParameter( $_POST, 'dropship_upload' ) ) {
	if( !empty( $_FILES['drop_csv'] ) ) {
		if( $csvData = parseCSVToHash( $_FILES['drop_csv']['tmp_name'] ) ) {
			$_SESSION['dropship'] = $csvData;
		}
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_process' ) ) {
	$outputHash = array();

//vvd( $_SESSION );
	foreach( $_REQUEST['dropship_order'] as $orderIdx ) {
		if( !empty( $_SESSION['dropship'] ) && $dropShipHash = BitBase::getParameter( $_SESSION['dropship'], $orderIdx ) ) {
			if( empty( $_REQUEST['notify'] ) ) {
				$dropShipHash['no_order_email'] = TRUE;
			}

			$dropSession = $dropShipHash;
			
			$dropSession['shipping_method'] = BitBase::getParameter( $dropShipHash, 'shipping_method' ).'_'.BitBase::getParameter( $dropShipHash, 'shipping_method_code' );
			if( $dropShipCart = bc_dropship_cart( $dropShipHash ) ) {

				$dropShipOrder = CommerceOrder::orderFromCart( $dropShipCart, $dropSession );
				// Some payment parameters are expected in request, and some in session. Send them all in
				if( $errors = $dropShipOrder->otCollectPosts( $dropShipHash, $dropSession ) ) {
					$outputStatusCode = HttpStatusCodes::HTTP_NOT_ACCEPTABLE;
					$outputHash['order'] = array( 'errors' => $errors );;
				} else {
					// load the selected payment module
					if( !$dropShipOrder->process( $dropSession, $dropShipHash ) ) {
						$outputHash = array_merge( $outputHash, $dropShipOrder->mErrors );
						$outputStatusCode = HttpStatusCodes::HTTP_NOT_ACCEPTABLE;
eb( $outputHash, $dropShipHash, $dropSession );
					} else {
						unset( $_SESSION['dropship'][$orderIdx] );
						$outputStatusCode = HttpStatusCodes::HTTP_OK;
					}
				}
			}
		}
	}
}

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

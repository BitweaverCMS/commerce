<?php
// +--------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org									|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+

global $gBitSmarty, $gBitCustomer, $gBitSystem;

define( 'HEADING_TITLE', 'Batch Order' );

if( !empty( $_POST ) && !empty( $_POST['action'] ) ) {
	if( !empty( $_POST['batch_index'] ) && ($batchOrder = $gBitCustomer->getBatchOrder()) && !empty( $batchOrder[$_POST['batch_index']] ) ) {
		if( $_POST['action'] == 'checkout' ) {
			$batchHash = &$batchOrder[$_POST['batch_index']];
			$addressKeys = array( 'firstname', 'lastname', 'company', 'street_address', 'suburb', 'city', 'state', 'postcode', 'country', 'telephone', 'countries_iso_code_3', 'countries_iso_code_2', 'countries_name', 'countries_id', 'country_id', 'zone_id' );
			$addressHash = array();
			foreach( $addressKeys as $key ) {
				if( !empty( $batchHash[$key] ) ) {
					$addressHash[$key] = $batchHash[$key];
				}
			}

			unset( $_SESSION['sendto'] );
			$_SESSION['sendtohash'] = $addressHash;

			$gBitCustomer->mCart->emptyCart();
			$gBitCustomer->mCart->addToCart( $batchHash['product_id'], $batchHash['quantity'], BitBase::getParameter( $batchHash, 'product_options' ) );
		
			if( !empty( $batchHash['discount_code'] ) ) {
				$voucher = new CommerceVoucher();
				if( $voucher->load( $batchHash['discount_code'] ) ) {
					$_SESSION['cc_id'] = $voucher->mCouponId;
				}
					
			}

			if( !empty( $batchHash['shipping_method'] ) ) {
				require( BITCOMMERCE_PKG_PATH.'classes/CommerceShipping.php');
				global $gCommerceShipping;
				unset( $_SESSION['shipping'] );
				list( $shipper, $method ) = explode( ' ', $batchHash['shipping_method'] );
				switch( strtolower( $shipper ) ) {
					case 'fedex':
						$shippingModule = 'fedexwebservices';
						$shippingMethod = strtoupper( str_replace( ' ', '', $batchHash['shipping_method'] ) );
						$shippingSession = array( 
							'id' => 'fedexwebservices_'.strtoupper( str_replace( ' ', '', $batchHash['shipping_method'] ) ),
							'code' => strtoupper( str_replace( ' ', '_', $batchHash['shipping_method'] ) ),
							'title' => ucwords( strtolower( $shipper ) ).' ('.ucwords( strtolower( $batchHash['shipping_method'] ) ).')',
						);
						break;
				}

				if( !empty( $shippingModule ) && !empty( $shippingMethod ) && ($quote = $gCommerceShipping->quote( $gBitCustomer->mCart, $shippingMethod, $shippingModule )) ) {
					if( isset( $quote['error'] ) ) {
						$batchHash['error'][] = $quote['error'];
					} elseif( !$gCommerceShipping->quoteToSession( $quote ) ) {
						$batchHash['error'][] = $quote[0]['methods'][0]['title'].' '.'Shipping method could not be calculated.';
eb( $quote );
					}
				}
				if( empty( $batchHash['error'] ) ) {
					$gBitCustomer->expungeBatchItem( $_POST['batch_index'] );
					zen_redirect(zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
				} else {
eb( $batchHash['error'] );
				}
			}
		} elseif( $_POST['action'] == 'remove' ) {
			$gBitCustomer->expungeBatchItem( $_POST['batch_index'] );
			zen_redirect(zen_href_link(FILENAME_BATCH_ORDER, '', 'SSL'));
		}
	} elseif( $_POST['action'] == 'clear' ) {
		$gBitCustomer->storeBatchOrder( array() );
	} elseif( $_POST['action'] == 'upload' ) {
		$verifyMime = $gBitSystem->verifyMimeType( $_FILES['batch_file']['tmp_name'] );
		if( $verifyMime == 'application/vnd.ms-excel' || $verifyMime == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ) {
			$phpOfficeAutload = UTIL_PKG_PATH.'includes/phpoffice/autoload.php';
			if( file_exists( $phpOfficeAutload ) ) {
				require_once( $phpOfficeAutload );
				$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $_FILES['batch_file']['tmp_name'] );
				$worksheet = $spreadsheet->getActiveSheet();
				$batchHash = $worksheet->toArray();
			}
		} elseif( $verifyMime == 'text/plain' ) {
			if( $csvFH = fopen( $_FILES['batch_file']['tmp_name'], 'r' ) ) {
				while( $csvRow = fgetcsv( $csvFH ) ) {
					$batchHash[] = $csvRow;
				}
			}
		}

		if( !empty( $batchHash ) ) {
			$gBitCustomer->storeBatchOrder( $batchHash );
		}
	}
}

if( $batchHash = $gBitCustomer->getBatchOrder() ) {
	$gBitSmarty->assign_by_ref( 'batchHash', $batchHash );
}
//eb( $_SESSION );
$gBitSmarty->display( 'bitpackage:bitcommerce/page_batch_order.tpl' );


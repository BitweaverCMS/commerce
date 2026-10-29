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

global $gBitUser, $gBitCustomer, $gBitThemes, $gCommerceShipping, $gCommerceSystem;
$gBitUser->verifyRegistered();

$gBitThemes->loadCss( BITCOMMERCE_PKG_PATH.'css/dropship.css', FALSE, 300, FALSE, FALSE, FALSE );

$feedback = array( 'success' => array(), 'error' => array(), 'warning' => array() );
$dropCreated = array();

// Every post from the Process form carries the per-row "save to address book" ticks; keep them on the session rows
if( array_key_exists( 'dropship_remove', $_POST ) && !empty( $_SESSION['dropship'] ) && is_array( $_SESSION['dropship'] ) ) {
	$saveTicks = BitBase::getParameter( $_POST, 'save_address', array() );
	$saveTicks = is_array( $saveTicks ) ? array_flip( array_map( 'strval', $saveTicks ) ) : array();
	foreach( $_SESSION['dropship'] as $tickIdx => &$tickRow ) {
		$tickRow['_save'] = isset( $saveTicks[(string)$tickIdx] );
	}
	unset( $tickRow );
}

if( BitBase::getParameter( $_POST, 'dropship_requote' ) ) {
	unset( $_SESSION['dropship_quotes'] );
	$feedback['success'][] = tra( 'Shipping rates refreshed.' );
} elseif( BitBase::getParameter( $_POST, 'dropship_clear' ) ) {
	unset( $_SESSION['dropship'], $_SESSION['dropship_file'], $_SESSION['dropship_quotes'] );
	$feedback['success'][] = tra( 'The list is empty. Upload a file to start again.' );
} elseif( ($removeIdx = BitBase::getParameter( $_POST, 'dropship_remove' )) !== NULL && $removeIdx !== '' && isset( $_SESSION['dropship'][$removeIdx] ) ) {
	$removed = $_SESSION['dropship'][$removeIdx];
	unset( $_SESSION['dropship'][$removeIdx] );
	if( empty( $_SESSION['dropship'] ) ) {
		unset( $_SESSION['dropship'], $_SESSION['dropship_file'] );
	}
	$feedback['success'][] = tra( 'Removed' ).' '.htmlspecialchars( BitBase::getParameter( $removed, 'delivery_name', '' ) ).'.';
} elseif( BitBase::getParameter( $_POST, 'dropship_addressbook' ) ) {
	// Entries picked from the shopper's own address book
	$picked = BitBase::getParameter( $_POST, 'address_book', array() );
	$bookRows = bc_dropship_address_book_rows( is_array( $picked ) ? $picked : array() );
	if( empty( $bookRows ) ) {
		$feedback['error'][] = tra( 'Pick at least one address from your address book.' );
	} else {
		$existing = is_array( BitBase::getParameter( $_SESSION, 'dropship' ) ) ? $_SESSION['dropship'] : array();
		$seen = array();
		foreach( $existing as $existingRow ) {
			$seen[bc_dropship_row_key( $existingRow )] = TRUE;
		}
		$added = 0;
		$skipped = 0;
		foreach( $bookRows as $row ) {
			$rowKey = bc_dropship_row_key( $row );
			if( isset( $seen[$rowKey] ) ) {
				$skipped++;
				continue;
			}
			$seen[$rowKey] = TRUE;
			$existing[] = $row;
			$added++;
		}
		$_SESSION['dropship'] = $existing;
		$feedback['success'][] = $added.' '.tra( 'addresses added from your address book.' ).( $skipped ? ' '.$skipped.' '.tra( 'already in the list were skipped.' ) : '' );
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_add' ) ) {
	// One address typed on the page; validated like a file row and appended (as a problem row if it fails)
	$add = BitBase::getParameter( $_POST, 'add', array() );
	$row = array( '_line' => 0, '_line_label' => tra( 'typed' ), '_file' => '', '_errors' => array(), 'cart' => 'active_cart' );
	foreach( bc_dropship_editable_fields() as $field ) {
		$row[$field] = ( is_array( $add ) && isset( $add[$field] ) && !is_array( $add[$field] ) ) ? trim( (string)$add[$field] ) : '';
	}
	if( $row['delivery_name'] === '' && $row['delivery_street_address1'] === '' ) {
		$feedback['error'][] = tra( 'Type at least a name and a street to add an address.' );
	} else {
		bc_dropship_validate_row( $row, bc_dropship_store_country_iso2() );
		$existing = is_array( BitBase::getParameter( $_SESSION, 'dropship' ) ) ? $_SESSION['dropship'] : array();
		$duplicate = FALSE;
		foreach( $existing as $existingRow ) {
			if( bc_dropship_row_key( $existingRow ) === bc_dropship_row_key( $row ) ) {
				$duplicate = TRUE;
			}
		}
		$rowName = htmlspecialchars( $row['delivery_name'] );
		if( $duplicate ) {
			$feedback['warning'][] = $rowName.' '.tra( 'is already in the list.' );
		} else {
			if( empty( $row['_errors'] ) && !empty( $add['save'] ) ) {
				$saveErrors = array();
				if( bc_dropship_save_to_address_book( $row, $saveErrors ) ) {
					$row['_saved'] = TRUE;
				} else {
					$feedback['warning'][] = $rowName.' '.tra( 'was not saved to your address book:' ).' '.implode( ' ', $saveErrors );
				}
			}
			$existing[] = $row;
			$_SESSION['dropship'] = $existing;
			if( empty( $row['_errors'] ) ) {
				$feedback['success'][] = tra( 'Added' ).' '.$rowName.( !empty( $row['_saved'] ) ? ' '.tra( 'and saved it to your address book' ) : '' ).'.';
			} else {
				$feedback['warning'][] = tra( 'Added' ).' '.$rowName.' '.tra( 'with a problem:' ).' '.implode( '. ', $row['_errors'] );
			}
		}
	}
} elseif( ($editIdx = BitBase::getParameter( $_POST, 'dropship_edit' )) !== NULL && $editIdx !== '' && isset( $_SESSION['dropship'][$editIdx] ) ) {
	// Inline edit of one row's delivery fields; the row is validated again so a problem row can be fixed in place
	$edit = BitBase::getParameter( $_POST, 'edit', array() );
	$row = $_SESSION['dropship'][$editIdx];
	foreach( bc_dropship_editable_fields() as $field ) {
		if( is_array( $edit ) && array_key_exists( $field, $edit ) && !is_array( $edit[$field] ) ) {
			$row[$field] = trim( (string)$edit[$field] );
		}
	}
	$row['_errors'] = array();
	bc_dropship_validate_row( $row, bc_dropship_store_country_iso2() );
	$_SESSION['dropship'][$editIdx] = $row;
	$rowName = htmlspecialchars( BitBase::getParameter( $row, 'delivery_name', '' ) );
	if( empty( $row['_errors'] ) ) {
		$feedback['success'][] = tra( 'Updated' ).' '.$rowName.'.';
	} else {
		$feedback['warning'][] = $rowName.' '.tra( 'still needs a fix:' ).' '.implode( '. ', $row['_errors'] );
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_upload' ) ) {
	// An upload adds to the list; Remove all clears it
	$uploadFile = BitBase::getParameter( $_FILES, 'drop_csv', array() );
	$uploadName = (string)BitBase::getParameter( $uploadFile, 'name', '' );
	$uploadTmp = (string)BitBase::getParameter( $uploadFile, 'tmp_name', '' );
	$uploadExt = strtolower( pathinfo( $uploadName, PATHINFO_EXTENSION ) );
	if( empty( $uploadTmp ) || !empty( $uploadFile['error'] ) ) {
		$feedback['error'][] = tra( 'Choose an address file (.csv) to upload.' );
	} elseif( in_array( $uploadExt, array( 'xlsx', 'xls', 'xlsm', 'numbers', 'ods' ) ) ) {
		$feedback['error'][] = tra( 'Spreadsheet workbooks cannot be read. In your spreadsheet choose File → Save As (or Export) and pick CSV, then upload that .csv file.' );
	} else {
		$parseErrors = array();
		if( in_array( $uploadExt, array( 'vcf', 'vcard' ) ) || bc_dropship_is_vcard( $uploadTmp ) ) {
			$csvRows = bc_dropship_parse_vcard( $uploadTmp, $parseErrors );
		} else {
			$csvRows = bc_dropship_parse_csv( $uploadTmp, $parseErrors );
		}
		if( !empty( $parseErrors['error'] ) ) {
			$feedback['error'] = array_merge( $feedback['error'], $parseErrors['error'] );
		}
		if( !empty( $parseErrors['warning'] ) ) {
			$feedback['warning'] = array_merge( $feedback['warning'], $parseErrors['warning'] );
		}
		if( $csvRows ) {
			$existing = is_array( BitBase::getParameter( $_SESSION, 'dropship' ) ) ? $_SESSION['dropship'] : array();
			$seen = array();
			foreach( $existing as $existingRow ) {
				$seen[bc_dropship_row_key( $existingRow )] = TRUE;
			}
			$added = 0;
			$skipped = 0;
			foreach( $csvRows as $newRow ) {
				$newRow['_file'] = $uploadName;
				$rowKey = bc_dropship_row_key( $newRow );
				if( isset( $seen[$rowKey] ) ) {
					$skipped++;
					continue;
				}
				$seen[$rowKey] = TRUE;
				$existing[] = $newRow;
				$added++;
			}
			$_SESSION['dropship'] = $existing;
			$fileNames = is_array( BitBase::getParameter( $_SESSION, 'dropship_file' ) ) ? $_SESSION['dropship_file'] : array();
			$fileNames[] = $uploadName;
			$_SESSION['dropship_file'] = $fileNames;
			$feedback['success'][] = $added.' '.tra( 'addresses added.' ).' '.( $skipped ? $skipped.' '.tra( 'already in the list were skipped.' ).' ' : '' ).tra( 'Check the list, pick shipping and payment, then create the orders.' );
		}
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_process' ) ) {
	$sessionCopy = $_SESSION;
	unset( $_SESSION['orders_id'] );

	$orderIndexes = BitBase::getParameter( $_REQUEST, 'dropship_order', array() );
	// Per-address menu (shipping_quote[rowIdx]) wins, then the row's own file columns, then the bulk choice
	$rowShippingQuotes = BitBase::getParameter( $_POST, 'shipping_quote', array() );
	$bulkShippingQuote = (string)BitBase::getParameter( $_POST, 'shipping_bulk', '' );
	// Payment for the whole batch comes from the form. A row that already carries a value keeps it.
	$batchPayment = array();
	foreach( $_POST as $postKey => $postValue ) {
		if( strpos( $postKey, 'payment_' ) === 0 && !is_array( $postValue ) && trim( (string)$postValue ) !== '' ) {
			$batchPayment[$postKey] = trim( (string)$postValue );
		}
	}
	$liveCartEmpty = !$gBitCustomer->mCart->count_contents();
	$savedToBook = 0;

	if( empty( $_SESSION['dropship'] ) ) {
		$feedback['error'][] = tra( 'There are no addresses loaded. Upload your address file again.' );
	} elseif( !is_array( $orderIndexes ) || !count( $orderIndexes ) ) {
		$feedback['error'][] = tra( 'No addresses are checked.' );
	} elseif( empty( $batchPayment['payment_method'] ) && !bc_dropship_rows_carry( $_SESSION['dropship'], $orderIndexes, 'payment_method' ) ) {
		$feedback['error'][] = tra( 'Choose a payment method for these orders.' );
	} else {
		foreach( $orderIndexes as $orderIdx ) {
			if( $checkoutHash = BitBase::getParameter( $_SESSION['dropship'], $orderIdx ) ) {
				$rowLabel = trim( BitBase::getParameter( $checkoutHash, 'delivery_name', '' ).', '.BitBase::getParameter( $checkoutHash, 'delivery_city', '' ), ', ' );
				if( !empty( $checkoutHash['_errors'] ) ) {
					$feedback['error'][] = tra( 'Skipped' ).' '.$rowLabel.': '.implode( '; ', $checkoutHash['_errors'] );
					continue;
				}
				$saveToBook = !empty( $checkoutHash['_save'] ) && empty( $checkoutHash['_saved'] ) && empty( $checkoutHash['_book'] );
				foreach( array_keys( $checkoutHash ) as $metaKey ) {
					if( strpos( $metaKey, '_' ) === 0 ) {
						unset( $checkoutHash[$metaKey] );
					}
				}

				if( bc_dropship_uses_active_cart( $checkoutHash ) && $liveCartEmpty ) {
					$feedback['error'][] = tra( 'Skipped' ).' '.$rowLabel.': '.tra( 'your cart is empty, so there is nothing to ship.' );
					continue;
				}

				if( empty( $_REQUEST['notify'] ) ) {
					$checkoutHash['no_order_email'] = TRUE;
				}

				foreach( $batchPayment as $paymentKey => $paymentValue ) {
					if( trim( (string)BitBase::getParameter( $checkoutHash, $paymentKey, '' ) ) === '' ) {
						$checkoutHash[$paymentKey] = $paymentValue;
					}
				}

				$rowQuote = is_array( $rowShippingQuotes ) ? (string)BitBase::getParameter( $rowShippingQuotes, $orderIdx, '' ) : '';
				if( strpos( $rowQuote, '_' ) === FALSE && trim( (string)BitBase::getParameter( $checkoutHash, 'shipping_method', '' ) ) !== '' ) {
					$rowQuote = BitBase::getParameter( $checkoutHash, 'shipping_method' ).'_'.BitBase::getParameter( $checkoutHash, 'shipping_method_code' );
				}
				if( strpos( $rowQuote, '_' ) === FALSE && strpos( $bulkShippingQuote, '|' ) !== FALSE ) {
					$rowQuote = bc_dropship_resolve_method( bc_dropship_cart( $checkoutHash ), $bulkShippingQuote );
				}
				if( strpos( $rowQuote, '_' ) === FALSE ) {
					$feedback['error'][] = tra( 'Skipped' ).' '.$rowLabel.': '.tra( 'no shipping method chosen.' );
					continue;
				}
				list( $checkoutHash['shipping_method'], $checkoutHash['shipping_method_code'] ) = explode( '_', $rowQuote, 2 );

				// Mark the order the way ot_expedite marks an expedited one, so staff can see it came from a drop-ship batch
				$checkoutHash['comments'] = trim( "DROP SHIP ORDER\n\n".BitBase::getParameter( $checkoutHash, 'comments', '' ) );

				// Some payment parameters are expected in request, and some in session during browser checkout. Merge hash and session and them all in under the session
				$dropSession = array_merge( $sessionCopy, $checkoutHash );
				$dropSession['shipping_quote'] = BitBase::getParameter( $checkoutHash, 'shipping_method' ).'_'.BitBase::getParameter( $checkoutHash, 'shipping_method_code' );

				// These next two lines are a hack for ot_expedite, which needs to be cleaned up
				$_REQUEST['main_page'] = 'checkout_process';
				$_REQUEST['ot_expedite'] = $_SESSION['ot_expedite'] = BitBase::getParameter( $checkoutHash, 'ot_expedite' );

				if( $dropShipCart = bc_dropship_cart( $checkoutHash ) ) {
					if( !$dropShipCart->count_contents() ) {
						$feedback['error'][] = tra( 'Skipped' ).' '.$rowLabel.': '.tra( 'no products to ship.' ).' '.implode( ' ', $dropShipCart->mErrors );
						continue;
					}
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
							$feedback['error'][] = tra( 'Order Error' ).' '.tra( 'to' ).' '.$deliveryString.': '.implode( ' ', $order->mErrors );
							$outputStatusCode = HttpStatusCodes::HTTP_NOT_ACCEPTABLE;
bit_error_email( 'Dropship Order Failure', $order->mErrors, $checkoutHash, $dropSession );
						} else {
							unset( $_SESSION['dropship'][$orderIdx] );
							$outputStatusCode = HttpStatusCodes::HTTP_OK;
							if( $saveToBook ) {
								$saveErrors = array();
								if( bc_dropship_save_to_address_book( $checkoutHash, $saveErrors ) ) {
									$savedToBook++;
								} else {
									$feedback['warning'][] = $rowLabel.': '.tra( 'not saved to your address book:' ).' '.implode( ' ', $saveErrors );
								}
							}
							$dropCreated[] = array(
								'orders_id' => $order->mOrdersId,
								'url' => zen_href_link( 'account_history_info', 'order_id='.$order->mOrdersId ),
								'name' => trim( BitBase::getParameter( $order->delivery, 'firstname', '' ).' '.BitBase::getParameter( $order->delivery, 'lastname', '' ) ),
								'place' => trim( BitBase::getParameter( $order->delivery, 'city', '' ).', '.BitBase::getParameter( $order->delivery, 'state', '' ), ', ' ),
								'shipping' => BitBase::getParameter( $order->info, 'shipping_method', '' ),
								'total' => (float)BitBase::getParameter( $order->info, 'total', 0 ),
								'po_number' => BitBase::getParameter( $checkoutHash, 'payment_po_number', '' ),
							);
						}
					}
				}
			}
		}
		if( $savedToBook ) {
			$feedback['success'][] = $savedToBook.' '.tra( 'addresses saved to your address book.' );
		}
		if( empty( $_SESSION['dropship'] ) ) {
			unset( $_SESSION['dropship'], $_SESSION['dropship_file'] );
		}
	}
}

$dropPreview = array();
$dropShippingMethods = array();
$dropShippingGroups = array();
$dropCartEmpty = FALSE;
$dropReadyCount = 0;
$dropActiveCartRows = 0;
if( !empty( $_SESSION['dropship'] ) ) {
	$dropCartEmpty = !$gBitCustomer->mCart->count_contents();
	foreach( $_SESSION['dropship'] as $rowIdx => $rowHash ) {
		$preview = bc_dropship_preview_row( $rowHash, $dropCartEmpty );
		foreach( $preview['quotes'] as $quoteHash ) {
			$methodKey = $quoteHash['key'];
			if( empty( $dropShippingMethods[$methodKey] ) ) {
				$dropShippingMethods[$methodKey] = array( 'id' => $quoteHash['id'], 'key' => $methodKey, 'title' => $quoteHash['title'], 'module' => $quoteHash['module'], 'method' => $quoteHash['method'], 'count' => 0, 'total' => 0.0 );
			}
			$dropShippingMethods[$methodKey]['count']++;
			$dropShippingMethods[$methodKey]['total'] += (float)$quoteHash['cost'];
		}
		if( empty( $preview['errors'] ) ) {
			$dropReadyCount++;
		}
		if( $preview['uses_active_cart'] ) {
			$dropActiveCartRows++;
		}
		$dropPreview[$rowIdx] = $preview;
	}
	// Default order: real carriers before pickup / free modules, then methods every address can use, then cheapest
	uasort( $dropShippingMethods, function( $a, $b ) {
		$aZero = (int)CommerceShipping::isZeroCostShippingAllowed( strstr( $a['id'], '_', TRUE ) );
		$bZero = (int)CommerceShipping::isZeroCostShippingAllowed( strstr( $b['id'], '_', TRUE ) );
		return ($aZero <=> $bZero) ?: ($b['count'] <=> $a['count']) ?: ($a['total'] <=> $b['total']);
	} );
	// $currencies is the PHP global; templates see it as $gCommerceCurrencies
	global $currencies;
	foreach( $dropShippingMethods as &$methodHash ) {
		$methodHash['total_display'] = $currencies->format( $methodHash['total'], TRUE, '', '', FALSE );
	}
	unset( $methodHash );
	// Bulk block: carriers in the order their best method sorts, rates within a carrier in that same order
	foreach( $dropShippingMethods as $methodHash ) {
		if( empty( $dropShippingGroups[$methodHash['module']] ) ) {
			$dropShippingGroups[$methodHash['module']] = array( 'module' => $methodHash['module'], 'methods' => array() );
		}
		$dropShippingGroups[$methodHash['module']]['methods'][] = $methodHash;
	}
	// Preselect each address menu: the file's carrier when it has one, else the first bulk method the address can use
	foreach( $dropPreview as &$preview ) {
		$preview['selected_quote'] = '';
		if( $preview['fixed_quote'] !== '' && isset( $preview['quotes'][$preview['fixed_quote']] ) ) {
			$preview['selected_quote'] = $preview['fixed_quote'];
		} else {
			foreach( array_keys( $dropShippingMethods ) as $methodKey ) {
				foreach( $preview['quotes'] as $quoteId => $quoteHash ) {
					if( $quoteHash['key'] === $methodKey ) {
						$preview['selected_quote'] = $quoteId;
						break 2;
					}
				}
			}
		}
	}
	unset( $preview );
}

global $currencies;
$dropCartTotal = $gBitCustomer->mCart->count_contents() ? (float)$gBitCustomer->mCart->show_total() : 0.0;
$dropShippingTotal = 0.0;
foreach( $dropPreview as $preview ) {
	if( empty( $preview['errors'] ) && $preview['selected_quote'] !== '' ) {
		$dropShippingTotal += (float)$preview['quotes'][$preview['selected_quote']]['cost'];
	}
}
$dropCreatedTotal = 0.0;
foreach( $dropCreated as $created ) {
	$dropCreatedTotal += $created['total'];
}
$dropPaymentDefaults = bc_dropship_payment_defaults();
$dropPaymentSelections = $paymentManager->userSelection( $dropPaymentDefaults );
$dropPaymentDefault = '';
foreach( $dropPaymentSelections as $selection ) {
	if( $selection['id'] == 'purchase_order' ) {
		$dropPaymentDefault = 'purchase_order';
	}
}
if( empty( $dropPaymentDefault ) && !empty( $dropPaymentSelections[0]['id'] ) ) {
	$dropPaymentDefault = $dropPaymentSelections[0]['id'];
}

$gBitSmarty->assignByRef( 'feedback', $feedback );
$gBitSmarty->assign( 'dropPreview', $dropPreview );
$gBitSmarty->assign( 'dropShippingMethods', $dropShippingMethods );
$gBitSmarty->assign( 'dropShippingGroups', $dropShippingGroups );
$gBitSmarty->assign( 'dropBulkDefault', $dropShippingMethods ? key( $dropShippingMethods ) : '' );
// "Ships from" is only worth a line when the rows do not all leave from the same place
$dropOrigins = array();
foreach( $dropPreview as $preview ) {
	foreach( $preview['quotes'] as $quoteHash ) {
		if( $quoteHash['origin'] !== '' ) {
			$dropOrigins[$quoteHash['origin']] = TRUE;
		}
	}
}
$gBitSmarty->assign( 'dropOriginsVary', count( $dropOrigins ) > 1 );
$gBitSmarty->assign( 'dropCartEmpty', $dropCartEmpty );
$gBitSmarty->assign( 'dropReadyCount', $dropReadyCount );
$gBitSmarty->assign( 'dropActiveCartRows', $dropActiveCartRows );
$gBitSmarty->assign( 'dropPaymentSelections', $dropPaymentSelections );
$gBitSmarty->assign( 'dropPaymentDefault', $dropPaymentDefault );
$gBitSmarty->assign( 'dropLiveCart', $gBitCustomer->mCart );
$dropAddressBook = $gBitCustomer->getAddresses();
$gBitSmarty->assign( 'dropAddressBook', is_array( $dropAddressBook ) ? $dropAddressBook : array() );
$dropFileNames = BitBase::getParameter( $_SESSION, 'dropship_file', array() );
$gBitSmarty->assign( 'dropFileName', is_array( $dropFileNames ) ? implode( ', ', array_unique( $dropFileNames ) ) : (string)$dropFileNames );
$gBitSmarty->assign( 'dropProblemCount', count( $dropPreview ) - $dropReadyCount );
$gBitSmarty->assign( 'dropCreated', $dropCreated );
$gBitSmarty->assign( 'dropCreatedTotalDisplay', $currencies->format( $dropCreatedTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropCartTotal', $dropCartTotal );
$gBitSmarty->assign( 'dropCartTotalDisplay', $currencies->format( $dropCartTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropItemsTotalDisplay', $currencies->format( $dropCartTotal * $dropReadyCount, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropShippingTotalDisplay', $currencies->format( $dropShippingTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropOrdersTotalDisplay', $currencies->format( $dropCartTotal * $dropReadyCount + $dropShippingTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropPoNumber', BitBase::getParameter( $dropPaymentDefaults, 'payment_po_number', '' ) );
$gBitSmarty->assign( 'dropCurrencySymbol', $currencies->getLeftSymbol( DEFAULT_CURRENCY ) );

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

function bc_dropship_uses_active_cart( $pRowHash ) {
	$cartString = trim( (string)BitBase::getParameter( $pRowHash, 'cart', '' ) );
	return ( $cartString === '' || $cartString === 'active_cart' );
}

// TRUE when every selected row carries a non-empty value for $pKey (a power-user file with its own columns)
function bc_dropship_rows_carry( $pRows, $pIndexes, $pKey ) {
	foreach( $pIndexes as $idx ) {
		$row = BitBase::getParameter( $pRows, $idx, array() );
		if( trim( (string)BitBase::getParameter( $row, $pKey, '' ) ) === '' ) {
			return FALSE;
		}
	}
	return TRUE;
}

// One row of the preview table: address, cart, quotes by method id, errors
function bc_dropship_preview_row( $pRowHash, $pLiveCartEmpty ) {
	global $gCommerceShipping, $currencies;

	$ret = array(
		'row' => $pRowHash,
		'line_label' => BitBase::getParameter( $pRowHash, '_line_label', tra( 'line' ).' '.BitBase::getParameter( $pRowHash, '_line', '' ) ),
		'errors' => BitBase::getParameter( $pRowHash, '_errors', array() ),
		'uses_active_cart' => bc_dropship_uses_active_cart( $pRowHash ),
		'fixed_quote' => '',
		'cart' => NULL,
		'delivery' => array(),
		'item_count' => 0,
		'quotes' => array(),
		'quote_errors' => array(),
	);
	$fileNames = BitBase::getParameter( $_SESSION, 'dropship_file', array() );
	if( is_array( $fileNames ) && count( $fileNames ) > 1 && !empty( $pRowHash['_file'] ) ) {
		$ret['line_label'] = $pRowHash['_file'].', '.$ret['line_label'];
	}
	$ret['save'] = !empty( $pRowHash['_save'] );
	$ret['saved'] = !empty( $pRowHash['_saved'] );
	$ret['from_book'] = !empty( $pRowHash['_book'] );
	foreach( array_keys( $pRowHash ) as $metaKey ) {
		if( strpos( $metaKey, '_' ) === 0 ) {
			unset( $pRowHash[$metaKey] );
		}
	}

	if( $ret['uses_active_cart'] && $pLiveCartEmpty ) {
		$ret['errors'][] = tra( 'Your cart is empty' );
	}

	$cart = bc_dropship_cart( $pRowHash );
	$ret['cart'] = $cart;
	$ret['delivery'] = $cart->delivery;
	$ret['item_count'] = $cart->count_contents();
	foreach( $cart->mErrors as $cartError ) {
		$ret['errors'][] = $cartError;
	}
	if( !$ret['uses_active_cart'] && !$ret['item_count'] && empty( $cart->mErrors ) ) {
		$ret['errors'][] = tra( 'No products in the cart column' );
	}

	if( empty( $ret['errors'] ) ) {
		$fixedModule = trim( (string)BitBase::getParameter( $pRowHash, 'shipping_method', '' ) );
		$fixedCode = trim( (string)BitBase::getParameter( $pRowHash, 'shipping_method_code', '' ) );
		if( $fixedModule !== '' ) {
			$ret['fixed_quote'] = $fixedModule.'_'.$fixedCode;
		}
		// Carrier calls are the slow part of this page. Quotes for the same cart and address are kept in the
		// session for bc_dropship_quote_ttl() seconds; Process re-quotes live when it builds each order.
		$cacheKey = bc_dropship_quote_key( $cart, $fixedModule, $fixedCode );
		$quotes = bc_dropship_quote_cached( $cacheKey );
		if( $quotes === NULL ) {
			$quotes = ( $fixedModule !== '' ) ? $gCommerceShipping->quote( $cart, $fixedCode, $fixedModule ) : $gCommerceShipping->quote( $cart );
			bc_dropship_quote_store( $cacheKey, $quotes );
		}
		if( is_array( $quotes ) ) {
			foreach( $quotes as $moduleQuote ) {
				if( !empty( $moduleQuote['error'] ) ) {
					$ret['quote_errors'][] = BitBase::getParameter( $moduleQuote, 'module', '' ).': '.$moduleQuote['error'];
				}
				if( !empty( $moduleQuote['methods'] ) && is_array( $moduleQuote['methods'] ) ) {
					foreach( $moduleQuote['methods'] as $method ) {
						$quoteId = $moduleQuote['id'].'_'.$method['id'];
						$originHash = BitBase::getParameter( $moduleQuote, 'origin', array() );
						$moduleTitle = (string)BitBase::getParameter( $moduleQuote, 'module', $moduleQuote['id'] );
						$methodTitle = (string)BitBase::getParameter( $method, 'title', $method['id'] );
						$ret['quotes'][$quoteId] = array(
							'id' => $quoteId,
							// Method ids are positional for some carriers (usps_1 is a different service abroad), so the
							// bulk block and the default selection match methods by carrier + name, never by id.
							'key' => $moduleQuote['id'].'|'.$methodTitle,
							'module' => $moduleTitle,
							'method' => $methodTitle,
							'origin' => is_array( $originHash ) ? (string)BitBase::getParameter( $originHash, 'countries_name', '' ) : '',
							'title' => ( $moduleTitle == $methodTitle ) ? $methodTitle : $moduleTitle.' '.$methodTitle,
							'cost' => BitBase::getParameter( $method, 'cost_add_tax', BitBase::getParameter( $method, 'cost', 0 ) ),
							// plain text: this goes into <option> labels and the rate column, where the formatter's HTML cannot render
							'display' => $currencies->format( BitBase::getParameter( $method, 'cost_add_tax', BitBase::getParameter( $method, 'cost', 0 ) ), TRUE, '', '', FALSE ),
							'delivery_date' => BitBase::getParameter( $method, 'delivery_date' ),
						);
					}
				}
			}
		}
		if( empty( $ret['quotes'] ) ) {
			$ret['errors'][] = tra( 'No shipping method could be quoted for this address' );
		}
	}
	$ret['quote_groups'] = array();
	foreach( $ret['quotes'] as $quoteHash ) {
		$ret['quote_groups'][$quoteHash['module']][] = $quoteHash;
	}

	return $ret;
}

// Prefills for the batch payment block: next purchase-order number for today, contact, organization
function bc_dropship_payment_defaults() {
	global $gBitUser, $gBitCustomer, $gBitDb;
	$ret = array();

	$prefix = date( 'Y-m-d' ).'#';
	$next = 1;
	if( $numbers = $gBitDb->getCol( "SELECT `payment_number` FROM ".TABLE_ORDERS_PAYMENTS." WHERE `customers_id`=? AND `payment_module`=? AND `payment_number` LIKE ?", array( $gBitUser->mUserId, 'purchase_order', $prefix.'%' ) ) ) {
		foreach( $numbers as $number ) {
			$counter = (int)substr( $number, strlen( $prefix ) );
			if( $counter >= $next ) {
				$next = $counter + 1;
			}
		}
	}
	$ret['payment_po_number'] = $prefix.$next;

	if( $defaultAddress = $gBitCustomer->getAddress( $gBitCustomer->getDefaultAddressId() ) ) {
		$ret['payment_po_contact'] = trim( BitBase::getParameter( $defaultAddress, 'entry_firstname', '' ).' '.BitBase::getParameter( $defaultAddress, 'entry_lastname', '' ) );
		$ret['payment_po_org'] = (string)BitBase::getParameter( $defaultAddress, 'entry_company', '' );
	}
	if( empty( $ret['payment_po_contact'] ) ) {
		$ret['payment_po_contact'] = trim( $gBitUser->getField( 'real_name', '' ) );
	}
	$ret['payment_owner'] = $ret['payment_po_contact'];

	return $ret;
}

// Header names a person types, mapped to the canonical column keys (see pub/drop_ship.csv)
function bc_dropship_header_aliases() {
	return array(
		'name' => 'delivery_name', 'full_name' => 'delivery_name', 'recipient' => 'delivery_name', 'recipient_name' => 'delivery_name',
		'ship_to' => 'delivery_name', 'ship_to_name' => 'delivery_name', 'contact' => 'delivery_name', 'contact_name' => 'delivery_name', 'attention' => 'delivery_name', 'attn' => 'delivery_name',
		'first_name' => '_first_name', 'firstname' => '_first_name', 'first' => '_first_name',
		'last_name' => '_last_name', 'lastname' => '_last_name', 'last' => '_last_name', 'surname' => '_last_name',
		'company' => 'delivery_company', 'organization' => 'delivery_company', 'organisation' => 'delivery_company', 'org' => 'delivery_company', 'business' => 'delivery_company', 'school' => 'delivery_company',
		'address' => 'delivery_street_address1', 'address1' => 'delivery_street_address1', 'address_1' => 'delivery_street_address1', 'address_line_1' => 'delivery_street_address1', 'address_line1' => 'delivery_street_address1',
		'street' => 'delivery_street_address1', 'street_address' => 'delivery_street_address1', 'street_1' => 'delivery_street_address1', 'street1' => 'delivery_street_address1', 'addr1' => 'delivery_street_address1', 'addr' => 'delivery_street_address1',
		'address2' => 'delivery_street_address2', 'address_2' => 'delivery_street_address2', 'address_line_2' => 'delivery_street_address2', 'address_line2' => 'delivery_street_address2',
		'street2' => 'delivery_street_address2', 'street_2' => 'delivery_street_address2', 'addr2' => 'delivery_street_address2', 'suite' => 'delivery_street_address2', 'apt' => 'delivery_street_address2', 'apartment' => 'delivery_street_address2', 'unit' => 'delivery_street_address2',
		'city' => 'delivery_city', 'town' => 'delivery_city', 'suburb' => 'delivery_city',
		'state' => 'delivery_state', 'province' => 'delivery_state', 'region' => 'delivery_state', 'state_province' => 'delivery_state', 'st' => 'delivery_state',
		'zip' => 'delivery_postcode', 'zipcode' => 'delivery_postcode', 'zip_code' => 'delivery_postcode', 'postal' => 'delivery_postcode', 'postal_code' => 'delivery_postcode', 'postcode' => 'delivery_postcode', 'post_code' => 'delivery_postcode',
		'country' => 'delivery_country_iso2', 'country_code' => 'delivery_country_iso2', 'country_iso2' => 'delivery_country_iso2', 'iso2' => 'delivery_country_iso2',
		'phone' => 'delivery_telephone', 'telephone' => 'delivery_telephone', 'phone_number' => 'delivery_telephone', 'tel' => 'delivery_telephone', 'mobile' => 'delivery_telephone', 'cell' => 'delivery_telephone',
		'comment' => 'comments', 'note' => 'comments', 'notes' => 'comments', 'instructions' => 'comments', 'gift_message' => 'comments', 'message' => 'comments',
	);
}

// One header cell → canonical key, or '' when the column is not used
function bc_dropship_normalize_header( $pHeader ) {
	$key = strtolower( trim( (string)$pHeader ) );
	$key = preg_replace( '/^\xEF\xBB\xBF/', '', $key );
	$key = trim( $key, " \t\n\r\0\x0B\"'" );
	$key = preg_replace( '/[^a-z0-9]+/', '_', $key );
	$key = trim( $key, '_' );
	if( $key === '' ) {
		return '';
	}
	$aliases = bc_dropship_header_aliases();
	if( isset( $aliases[$key] ) ) {
		return $aliases[$key];
	}
	// canonical columns pass through
	if( in_array( $key, array( 'cart', 'comments', 'dc_redeem_code', 'deadline_date' ) ) ) {
		return $key;
	}
	foreach( array( 'delivery_', 'billing_', 'payment_', 'shipping_', 'ot_' ) as $prefix ) {
		if( strpos( $key, $prefix ) === 0 ) {
			return $key;
		}
	}
	return '';
}

// Parse an uploaded address file into session rows. Row problems go into $row['_errors']; file problems into $pErrors['error'] / ['warning'].
function bc_dropship_parse_csv( $pFile, &$pErrors ) {
	$pErrors = array( 'error' => array(), 'warning' => array() );
	$ret = array();

	if( !file_exists( $pFile ) || !is_readable( $pFile ) ) {
		$pErrors['error'][] = tra( 'The file could not be read.' );
		return $ret;
	}
	if( !($handle = fopen( $pFile, 'r' )) ) {
		$pErrors['error'][] = tra( 'The file could not be opened.' );
		return $ret;
	}

	$rawHeaders = fgetcsv( $handle );
	if( $rawHeaders === FALSE || !count( array_filter( $rawHeaders, 'strlen' ) ) ) {
		fclose( $handle );
		$pErrors['error'][] = tra( 'The file is empty or has no header row. The first row must name the columns (Name, Street, City, State, Zip, Country, Phone).' );
		return $ret;
	}
	// A workbook saved with the wrong extension, or a binary file, has no readable header
	$headerBlob = implode( '', $rawHeaders );
	if( preg_match( '/[^\x09\x0A\x0D\x20-\x7E\x80-\xFF]/', $headerBlob ) || strpos( $headerBlob, 'PK' ) === 0 ) {
		fclose( $handle );
		$pErrors['error'][] = tra( 'That is not a CSV text file. In your spreadsheet choose File → Save As (or Export) and pick CSV, then upload that .csv file.' );
		return $ret;
	}

	$columns = array();
	$ignored = array();
	foreach( $rawHeaders as $index => $rawHeader ) {
		$key = bc_dropship_normalize_header( $rawHeader );
		if( $key === '' ) {
			if( trim( (string)$rawHeader ) !== '' ) {
				$ignored[] = trim( (string)$rawHeader );
			}
		} elseif( isset( $columns[$key] ) ) {
			$ignored[] = trim( (string)$rawHeader ).' ('.tra( 'duplicate' ).')';
		} else {
			$columns[$key] = $index;
		}
	}
	if( !empty( $ignored ) ) {
		$pErrors['warning'][] = tra( 'Ignored columns:' ).' '.implode( ', ', array_map( 'htmlspecialchars', $ignored ) );
	}
	$hasName = isset( $columns['delivery_name'] ) || isset( $columns['_first_name'] ) || isset( $columns['_last_name'] );
	if( !$hasName || !isset( $columns['delivery_street_address1'] ) ) {
		fclose( $handle );
		$pErrors['error'][] = tra( 'The header row needs at least a Name column and a Street (address) column. Download the address template below and paste your addresses into it.' );
		return $ret;
	}

	$storeCountryIso2 = bc_dropship_store_country_iso2();

	$lineNumber = 1;
	while( ($data = fgetcsv( $handle )) !== FALSE ) {
		$lineNumber++;
		if( !is_array( $data ) || !count( array_filter( $data, function( $v ) { return trim( (string)$v ) !== ''; } ) ) ) {
			continue;
		}
		$row = array( '_line' => $lineNumber, '_errors' => array() );
		foreach( $columns as $key => $index ) {
			$row[$key] = isset( $data[$index] ) ? trim( (string)$data[$index] ) : '';
		}
		if( trim( (string)BitBase::getParameter( $row, 'delivery_name', '' ) ) === '' ) {
			$row['delivery_name'] = trim( BitBase::getParameter( $row, '_first_name', '' ).' '.BitBase::getParameter( $row, '_last_name', '' ) );
		}
		unset( $row['_first_name'], $row['_last_name'] );

		$row['_line_label'] = tra( 'line' ).' '.$lineNumber;
		bc_dropship_validate_row( $row, $storeCountryIso2 );

		$ret[] = $row;
	}
	fclose( $handle );

	if( empty( $ret ) ) {
		$pErrors['error'][] = tra( 'No address rows were found under the header row.' );
	}

	return $ret;
}

function bc_dropship_store_country_iso2() {
	if( $storeCountry = zen_get_countries( STORE_COUNTRY ) ) {
		return (string)BitBase::getParameter( $storeCountry, 'countries_iso_code_2', '' );
	}
	return '';
}

// Country spellings people type that the lookup table does not have
function bc_dropship_country_alias( $pCountry ) {
	$key = strtoupper( trim( preg_replace( '/[^A-Za-z ]/', '', $pCountry ) ) );
	$aliases = array(
		'USA' => 'US', 'UNITED STATES' => 'US', 'UNITED STATES OF AMERICA' => 'US', 'U S A' => 'US', 'US' => 'US', 'AMERICA' => 'US',
		'UK' => 'GB', 'GREAT BRITAIN' => 'GB', 'ENGLAND' => 'GB', 'SCOTLAND' => 'GB', 'WALES' => 'GB', 'UNITED KINGDOM' => 'GB',
		'CAN' => 'CA', 'CANADA' => 'CA',
	);
	return isset( $aliases[$key] ) ? $aliases[$key] : trim( $pCountry );
}

// Normalizes and checks one address row in place: cart default, name split, country, state, postcode. Problems go in $row['_errors'].
function bc_dropship_validate_row( &$row, $pStoreCountryIso2 ) {
	if( !isset( $row['_errors'] ) || !is_array( $row['_errors'] ) ) {
		$row['_errors'] = array();
	}
	if( bc_dropship_uses_active_cart( $row ) ) {
		$row['cart'] = 'active_cart';
	}
	$row['delivery_name'] = trim( (string)BitBase::getParameter( $row, 'delivery_name', '' ) );
	if( $row['delivery_name'] === '' ) {
		$row['_errors'][] = tra( 'Missing name' );
	} elseif( strpos( $row['delivery_name'], ' ' ) === FALSE ) {
		$row['_errors'][] = tra( 'Name needs a first and last name' );
	}
	if( trim( (string)BitBase::getParameter( $row, 'delivery_street_address1', '' ) ) === '' ) {
		$row['_errors'][] = tra( 'Missing street address' );
	}
	if( trim( (string)BitBase::getParameter( $row, 'delivery_city', '' ) ) === '' ) {
		$row['_errors'][] = tra( 'Missing city' );
	}

	$countryValue = trim( (string)BitBase::getParameter( $row, 'delivery_country_iso2', '' ) );
	if( $countryValue === '' ) {
		$countryValue = $pStoreCountryIso2;
	}
	$countryValue = bc_dropship_country_alias( $countryValue );
	$countryHash = $countryValue !== '' ? zen_get_countries( $countryValue ) : array();
	if( empty( $countryHash['countries_id'] ) ) {
		$row['_errors'][] = tra( 'Unknown country' ).' "'.htmlspecialchars( $countryValue ).'"';
	} else {
		$row['delivery_country_iso2'] = $countryHash['countries_iso_code_2'];
		$stateValue = trim( (string)BitBase::getParameter( $row, 'delivery_state', '' ) );
		$countryHasZones = bc_dropship_country_has_zones( $countryHash['countries_id'] );
		if( $stateValue !== '' ) {
			if( $zone = zen_get_zone_by_name( $countryHash['countries_id'], $stateValue ) ) {
				$row['delivery_state'] = $zone['zone_code'];
			} elseif( $countryHasZones ) {
				$row['_errors'][] = tra( 'Unknown state' ).' "'.htmlspecialchars( $stateValue ).'"';
			}
		} elseif( $countryHasZones ) {
			$row['_errors'][] = tra( 'Missing state' );
		}
		if( trim( (string)BitBase::getParameter( $row, 'delivery_postcode', '' ) ) === '' && $countryHash['address_format_id'] != 4 ) {
			$row['_errors'][] = tra( 'Missing postal code' );
		}
	}
}

// TRUE when the file starts with a vCard, whatever its extension
function bc_dropship_is_vcard( $pFile ) {
	if( !is_readable( $pFile ) || !($head = file_get_contents( $pFile, FALSE, NULL, 0, 64 )) ) {
		return FALSE;
	}
	$head = preg_replace( '/^\xEF\xBB\xBF/', '', $head );
	return (bool)preg_match( '/^\s*BEGIN:VCARD/i', $head );
}

function bc_dropship_vcard_unescape( $pValue ) {
	return strtr( (string)$pValue, array( '\\n' => "\n", '\\N' => "\n", '\\,' => ',', '\;' => ';', '\\\\' => '\\' ) );
}

// Split a compound vCard value (N, ADR) on unescaped semicolons and unescape each part
function bc_dropship_vcard_parts( $pValue, $pCount ) {
	$parts = preg_split( '/(?<!\\\\);/', (string)$pValue );
	$parts = array_map( 'bc_dropship_vcard_unescape', $parts );
	return array_pad( $parts, $pCount, '' );
}

// Pick one of several ADR / TEL entries: preferred first, then the wanted type, then the first one
function bc_dropship_vcard_pick( $pEntries, $pWantType ) {
	if( empty( $pEntries ) ) {
		return NULL;
	}
	foreach( $pEntries as $entry ) {
		if( in_array( 'PREF', $entry['types'] ) || isset( $entry['params']['PREF'] ) ) {
			return $entry;
		}
	}
	foreach( $pEntries as $entry ) {
		if( in_array( $pWantType, $entry['types'] ) ) {
			return $entry;
		}
	}
	return $pEntries[0];
}

// Parse a vCard file (2.1, 3.0, 4.0; one or many cards) into the same rows the CSV parser makes
function bc_dropship_parse_vcard( $pFile, &$pErrors ) {
	$pErrors = array( 'error' => array(), 'warning' => array() );
	$ret = array();

	if( !is_readable( $pFile ) || ($text = file_get_contents( $pFile )) === FALSE ) {
		$pErrors['error'][] = tra( 'The file could not be read.' );
		return $ret;
	}
	$text = preg_replace( '/^\xEF\xBB\xBF/', '', $text );
	$text = str_replace( array( "\r\n", "\r" ), "\n", $text );
	// folded lines continue with a space or tab
	$text = preg_replace( "/\n[ \t]/", '', $text );
	// Walk lines rather than regex the whole file: embedded photos make a card hundreds of KB,
	// and a lazy match across that exceeds the PCRE backtrack limit and silently fails.
	$cards = array();
	$current = NULL;
	foreach( explode( "\n", $text ) as $line ) {
		$marker = strtoupper( trim( $line ) );
		if( $marker === 'BEGIN:VCARD' ) {
			$current = array();
		} elseif( $marker === 'END:VCARD' ) {
			if( is_array( $current ) ) {
				$cards[] = $current;
			}
			$current = NULL;
		} elseif( is_array( $current ) && $line !== '' ) {
			$current[] = $line;
		}
	}
	unset( $text );
	if( empty( $cards ) ) {
		$pErrors['error'][] = tra( 'No contacts were found in the file. Export the contacts again as a vCard (.vcf) file, or save your list as CSV.' );
		return $ret;
	}

	$storeCountryIso2 = bc_dropship_store_country_iso2();
	$noAddress = array();
	$wantedProps = array( 'FN', 'N', 'ORG', 'ADR', 'TEL', 'NOTE' );

	foreach( $cards as $cardIndex => $card ) {
		$cardNumber = $cardIndex + 1;
		$props = array();
		foreach( $card as $line ) {
			if( strpos( $line, ':' ) === FALSE ) {
				continue;
			}
			list( $head, $value ) = explode( ':', $line, 2 );
			$headParts = explode( ';', $head );
			$name = strtoupper( trim( array_shift( $headParts ) ) );
			// Apple groups properties as item1.ADR
			if( ($dot = strrpos( $name, '.' )) !== FALSE ) {
				$name = substr( $name, $dot + 1 );
			}
			if( !in_array( $name, $wantedProps ) ) {
				continue;
			}
			$params = array();
			$types = array();
			foreach( $headParts as $param ) {
				if( strpos( $param, '=' ) !== FALSE ) {
					list( $paramKey, $paramValue ) = explode( '=', $param, 2 );
					$paramKey = strtoupper( trim( $paramKey ) );
					$paramValues = array_map( 'strtoupper', array_map( 'trim', explode( ',', str_replace( '"', '', $paramValue ) ) ) );
					$params[$paramKey] = $paramValues;
					if( $paramKey == 'TYPE' ) {
						$types = array_merge( $types, $paramValues );
					}
				} else {
					// vCard 2.1 bare type words: ADR;HOME;PREF
					$types[] = strtoupper( trim( $param ) );
				}
			}
			if( !empty( $params['ENCODING'] ) && in_array( 'QUOTED-PRINTABLE', $params['ENCODING'] ) ) {
				$value = quoted_printable_decode( $value );
			}
			// vCard 2.1 exports may declare a legacy charset; everything downstream is UTF-8
			if( !empty( $params['CHARSET'][0] ) && !in_array( $params['CHARSET'][0], array( 'UTF-8', 'UTF8' ) ) && function_exists( 'iconv' ) ) {
				if( ($converted = @iconv( $params['CHARSET'][0], 'UTF-8//IGNORE', $value )) !== FALSE ) {
					$value = $converted;
				}
			}
			$props[$name][] = array( 'params' => $params, 'types' => $types, 'value' => trim( $value ) );
		}

		$fullName = !empty( $props['FN'][0]['value'] ) ? bc_dropship_vcard_unescape( $props['FN'][0]['value'] ) : '';
		if( !empty( $props['N'][0]['value'] ) ) {
			list( $lastName, $firstName ) = bc_dropship_vcard_parts( $props['N'][0]['value'], 5 );
			$structuredName = trim( $firstName.' '.$lastName );
			if( $fullName === '' || strpos( $fullName, ' ' ) === FALSE ) {
				$fullName = $structuredName !== '' ? $structuredName : $fullName;
			}
		}
		$company = !empty( $props['ORG'][0]['value'] ) ? trim( bc_dropship_vcard_parts( $props['ORG'][0]['value'], 1 )[0] ) : '';
		if( $fullName === '' && $company !== '' ) {
			$fullName = $company;
		}

		$row = array(
			'_line' => $cardNumber,
			'_line_label' => tra( 'contact' ).' '.$cardNumber,
			'_errors' => array(),
			'cart' => 'active_cart',
			'delivery_name' => $fullName,
			'delivery_company' => $company,
			'delivery_street_address1' => '',
			'delivery_street_address2' => '',
			'delivery_city' => '',
			'delivery_state' => '',
			'delivery_postcode' => '',
			'delivery_country_iso2' => '',
			'delivery_telephone' => '',
			'comments' => '',
		);

		if( $adr = bc_dropship_vcard_pick( BitBase::getParameter( $props, 'ADR', array() ), 'HOME' ) ) {
			list( $poBox, $extended, $street, $city, $region, $postal, $country ) = bc_dropship_vcard_parts( $adr['value'], 7 );
			$streetLines = array_values( array_filter( array_map( 'trim', explode( "\n", $street ) ), 'strlen' ) );
			$row['delivery_street_address1'] = isset( $streetLines[0] ) ? $streetLines[0] : trim( $poBox );
			$extra = array_slice( $streetLines, 1 );
			if( trim( $extended ) !== '' ) {
				array_unshift( $extra, trim( $extended ) );
			}
			if( isset( $streetLines[0] ) && trim( $poBox ) !== '' ) {
				$extra[] = trim( $poBox );
			}
			$row['delivery_street_address2'] = implode( ', ', $extra );
			$row['delivery_city'] = trim( $city );
			$row['delivery_state'] = trim( $region );
			$row['delivery_postcode'] = trim( $postal );
			$row['delivery_country_iso2'] = trim( $country );
		} else {
			$noAddress[] = $fullName !== '' ? $fullName : tra( 'contact' ).' '.$cardNumber;
			continue;
		}
		if( $tel = bc_dropship_vcard_pick( BitBase::getParameter( $props, 'TEL', array() ), 'CELL' ) ) {
			$row['delivery_telephone'] = preg_replace( '/^tel:/i', '', bc_dropship_vcard_unescape( $tel['value'] ) );
		}
		if( !empty( $props['NOTE'][0]['value'] ) ) {
			$row['comments'] = bc_dropship_vcard_unescape( $props['NOTE'][0]['value'] );
		}

		bc_dropship_validate_row( $row, $storeCountryIso2 );
		$ret[] = $row;
	}

	if( !empty( $noAddress ) ) {
		$pErrors['warning'][] = count( $noAddress ).' '.tra( 'contacts have no postal address and were left out:' ).' '.htmlspecialchars( implode( ', ', $noAddress ) );
	}
	if( empty( $ret ) ) {
		$pErrors['error'][] = tra( 'None of the contacts in the file has a postal address.' );
	}

	return $ret;
}

// Two rows are the same person at the same place: name, street, and postcode, case-insensitive
function bc_dropship_row_key( $pRow ) {
	$key = preg_replace( '/\s+/u', ' ', trim( BitBase::getParameter( $pRow, 'delivery_name', '' ).'|'.BitBase::getParameter( $pRow, 'delivery_street_address1', '' ).'|'.BitBase::getParameter( $pRow, 'delivery_postcode', '' ) ) );
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( $key, 'UTF-8' ) : strtolower( $key );
}

// How long the page keeps a shipping quote before asking the carrier again.
// Hardcoded at 15 minutes for now; make it a commerce configuration value if staff ever need to tune it.
function bc_dropship_quote_ttl() {
	return 900;
}

// Turn a carrier|method-name key into this address's module_method id by quoting that carrier (no JS fallback path)
function bc_dropship_resolve_method( $pCart, $pMethodKey ) {
	global $gCommerceShipping;
	list( $module, $methodTitle ) = explode( '|', $pMethodKey, 2 );
	$quotes = $gCommerceShipping->quote( $pCart, '', $module );
	if( is_array( $quotes ) ) {
		foreach( $quotes as $moduleQuote ) {
			foreach( BitBase::getParameter( $moduleQuote, 'methods', array() ) as $method ) {
				if( (string)BitBase::getParameter( $method, 'title', '' ) === $methodTitle ) {
					return $moduleQuote['id'].'_'.$method['id'];
				}
			}
		}
	}
	return '';
}

// Cache key for a row's quotes: what ships (ids, quantities, options) and where, plus any carrier the file fixed
function bc_dropship_quote_key( $pCart, $pFixedModule, $pFixedCode ) {
	$items = array();
	if( is_array( $pCart->contents ) ) {
		foreach( $pCart->contents as $key => $item ) {
			$items[$key] = array( BitBase::getParameter( $item, 'products_id' ), BitBase::getParameter( $item, 'products_quantity' ), BitBase::getParameter( $item, 'attributes_values' ) );
		}
		ksort( $items );
	}
	$where = array();
	foreach( array( 'street_address', 'suburb', 'city', 'state', 'zone_id', 'postcode', 'countries_iso_code_2' ) as $field ) {
		$where[$field] = BitBase::getParameter( $pCart->delivery, $field );
	}
	return md5( serialize( array( $items, $where, $pFixedModule, $pFixedCode ) ) );
}

function bc_dropship_quote_cached( $pKey ) {
	$cache = ( !empty( $_SESSION['dropship_quotes'] ) && is_array( $_SESSION['dropship_quotes'] ) ) ? $_SESSION['dropship_quotes'] : array();
	$entry = BitBase::getParameter( $cache, $pKey );
	if( is_array( $entry ) && !empty( $entry['t'] ) && ( time() - $entry['t'] ) < bc_dropship_quote_ttl() && array_key_exists( 'q', $entry ) ) {
		return $entry['q'];
	}
	return NULL;
}

function bc_dropship_quote_store( $pKey, $pQuotes ) {
	if( empty( $_SESSION['dropship_quotes'] ) || !is_array( $_SESSION['dropship_quotes'] ) ) {
		$_SESSION['dropship_quotes'] = array();
	}
	// drop stale entries so the session does not grow across uploads
	foreach( $_SESSION['dropship_quotes'] as $key => $entry ) {
		if( empty( $entry['t'] ) || ( time() - $entry['t'] ) >= bc_dropship_quote_ttl() ) {
			unset( $_SESSION['dropship_quotes'][$key] );
		}
	}
	$_SESSION['dropship_quotes'][$pKey] = array( 't' => time(), 'q' => $pQuotes );
}

// Address-book entries (by address_book_id) as validated drop-ship rows
function bc_dropship_address_book_rows( $pAddressBookIds ) {
	global $gBitCustomer, $gBitDb;
	$ret = array();
	$wanted = array_flip( array_map( 'intval', $pAddressBookIds ) );
	if( empty( $wanted ) || !($entries = $gBitCustomer->getAddresses()) ) {
		return $ret;
	}
	$storeCountryIso2 = bc_dropship_store_country_iso2();
	foreach( $entries as $entry ) {
		if( !isset( $wanted[(int)$entry['address_book_id']] ) ) {
			continue;
		}
		$state = trim( (string)BitBase::getParameter( $entry, 'state', '' ) );
		if( $state === '' && BitBase::verifyId( BitBase::getParameter( $entry, 'zone_id' ) ) ) {
			$state = (string)$gBitDb->getOne( "SELECT `zone_code` FROM ".TABLE_ZONES." WHERE `zone_id`=?", array( (int)$entry['zone_id'] ) );
		}
		$row = array(
			'_line' => (int)$entry['address_book_id'],
			'_line_label' => tra( 'address book' ),
			'_book' => TRUE,
			'_file' => '',
			'_errors' => array(),
			'cart' => 'active_cart',
			'delivery_name' => trim( BitBase::getParameter( $entry, 'firstname', '' ).' '.BitBase::getParameter( $entry, 'lastname', '' ) ),
			'delivery_company' => (string)BitBase::getParameter( $entry, 'company', '' ),
			'delivery_street_address1' => (string)BitBase::getParameter( $entry, 'street_address', '' ),
			'delivery_street_address2' => (string)BitBase::getParameter( $entry, 'suburb', '' ),
			'delivery_city' => (string)BitBase::getParameter( $entry, 'city', '' ),
			'delivery_state' => $state,
			'delivery_postcode' => (string)BitBase::getParameter( $entry, 'postcode', '' ),
			'delivery_country_iso2' => (string)BitBase::getParameter( $entry, 'countries_iso_code_2', '' ),
			'delivery_telephone' => (string)BitBase::getParameter( $entry, 'telephone', '' ),
			'comments' => '',
		);
		bc_dropship_validate_row( $row, $storeCountryIso2 );
		$ret[] = $row;
	}
	return $ret;
}

// Store one drop-ship row in the shopper's address book through CommerceCustomer::storeAddress().
// An entry with the same name, street, and postcode already there counts as saved.
function bc_dropship_save_to_address_book( $pRow, &$pErrors ) {
	global $gBitCustomer;
	$pErrors = array();
	if( $entries = $gBitCustomer->getAddresses() ) {
		$rowKey = bc_dropship_row_key( $pRow );
		foreach( $entries as $entry ) {
			$entryRow = array( 'delivery_name' => trim( $entry['firstname'].' '.$entry['lastname'] ), 'delivery_street_address1' => $entry['street_address'], 'delivery_postcode' => $entry['postcode'] );
			if( bc_dropship_row_key( $entryRow ) === $rowKey ) {
				return TRUE;
			}
		}
	}
	$name = trim( (string)BitBase::getParameter( $pRow, 'delivery_name', '' ) );
	$firstSpace = strpos( $name, ' ' );
	$countryHash = zen_get_countries( (string)BitBase::getParameter( $pRow, 'delivery_country_iso2', '' ) );
	$address = array(
		'firstname' => $firstSpace === FALSE ? $name : substr( $name, 0, $firstSpace ),
		'lastname' => $firstSpace === FALSE ? '' : trim( substr( $name, $firstSpace + 1 ) ),
		'company' => (string)BitBase::getParameter( $pRow, 'delivery_company', '' ),
		'street_address' => (string)BitBase::getParameter( $pRow, 'delivery_street_address1', '' ),
		'suburb' => (string)BitBase::getParameter( $pRow, 'delivery_street_address2', '' ),
		'city' => (string)BitBase::getParameter( $pRow, 'delivery_city', '' ),
		'state' => (string)BitBase::getParameter( $pRow, 'delivery_state', '' ),
		'postcode' => (string)BitBase::getParameter( $pRow, 'delivery_postcode', '' ),
		'telephone' => (string)BitBase::getParameter( $pRow, 'delivery_telephone', '' ),
		'country_id' => (int)BitBase::getParameter( $countryHash, 'countries_id', 0 ),
	);
	$gBitCustomer->mErrors = array();
	if( $gBitCustomer->storeAddress( $address ) ) {
		return TRUE;
	}
	$pErrors = array_values( $gBitCustomer->mErrors );
	$gBitCustomer->mErrors = array();
	return FALSE;
}

// Delivery fields a shopper may change on the page, in display order
function bc_dropship_editable_fields() {
	return array( 'delivery_name', 'delivery_company', 'delivery_street_address1', 'delivery_street_address2', 'delivery_city', 'delivery_state', 'delivery_postcode', 'delivery_country_iso2', 'delivery_telephone', 'comments' );
}

function bc_dropship_country_has_zones( $pCountryId ) {
	global $gBitDb;
	static $cache = array();
	if( !isset( $cache[$pCountryId] ) ) {
		$cache[$pCountryId] = (bool)$gBitDb->getOne( "SELECT COUNT(*) FROM ".TABLE_ZONES." WHERE `zone_country_id`=?", array( (int)$pCountryId ) );
	}
	return $cache[$pCountryId];
}

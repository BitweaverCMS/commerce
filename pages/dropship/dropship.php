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
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'address_import_lib.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'dropship_lib.php' );
$paymentManager = new CommercePaymentManager( BitBase::getParameter( $_REQUEST, 'payment_method') );

global $gBitUser, $gBitCustomer, $gBitThemes, $gCommerceShipping, $gCommerceSystem;
$gBitUser->verifyRegistered();

$gBitThemes->loadCss( BITCOMMERCE_PKG_PATH.'css/dropship.css', FALSE, 300, FALSE, FALSE, FALSE );

$feedback = array( 'success' => array(), 'error' => array(), 'warning' => array() );
if( !empty( $_SESSION['dropship_feedback'] ) && is_array( $_SESSION['dropship_feedback'] ) ) {
	// the confirmation page sends the shopper back here with its reasons
	foreach( $_SESSION['dropship_feedback'] as $kind => $lines ) {
		if( isset( $feedback[$kind] ) && is_array( $lines ) ) {
			$feedback[$kind] = array_merge( $feedback[$kind], $lines );
		}
	}
	unset( $_SESSION['dropship_feedback'] );
}
$dropCreated = array();
$dropMapping = NULL;

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
	bc_address_import_clear();
	$feedback['success'][] = tra( 'The list is empty. Upload a file to start again.' );
} elseif( ($removeIdx = BitBase::getParameter( $_POST, 'dropship_remove' )) !== NULL && $removeIdx !== '' && isset( $_SESSION['dropship'][$removeIdx] ) ) {
	$removed = $_SESSION['dropship'][$removeIdx];
	unset( $_SESSION['dropship'][$removeIdx] );
	if( empty( $_SESSION['dropship'] ) ) {
		unset( $_SESSION['dropship'], $_SESSION['dropship_file'] );
		bc_address_import_clear();
	}
	$feedback['success'][] = tra( 'Removed' ).' '.htmlspecialchars( BitBase::getParameter( $removed, 'delivery_name', '' ) ).'.';
} elseif( BitBase::getParameter( $_POST, 'dropship_addressbook' ) ) {
	// Entries picked from the shopper's own address book
	$picked = BitBase::getParameter( $_POST, 'address_book', array() );
	$bookRows = bc_address_book_rows( is_array( $picked ) ? $picked : array() );
	if( empty( $bookRows ) ) {
		$feedback['error'][] = tra( 'Pick at least one address from your address book.' );
	} else {
		$counts = bc_dropship_append_rows( $bookRows, '', FALSE );
		$feedback['success'][] = $counts['added'].' '.tra( 'addresses added from your address book.' ).( $counts['skipped'] ? ' '.$counts['skipped'].' '.tra( 'already in the list were skipped.' ) : '' );
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_add' ) ) {
	// One address typed on the page; validated like a file row and appended (as a problem row if it fails)
	$add = BitBase::getParameter( $_POST, 'add', array() );
	$row = array( '_line' => 0, '_line_label' => tra( 'typed' ), '_file' => '', '_errors' => array() );
	foreach( bc_address_fields() as $field ) {
		$row[$field] = ( is_array( $add ) && isset( $add[$field] ) && !is_array( $add[$field] ) ) ? trim( (string)$add[$field] ) : '';
	}
	if( $row['delivery_name'] === '' && $row['delivery_street_address1'] === '' ) {
		$feedback['error'][] = tra( 'Type at least a name and a street to add an address.' );
	} else {
		bc_address_validate_row( $row, bc_address_store_country_iso2() );
		$existing = is_array( BitBase::getParameter( $_SESSION, 'dropship' ) ) ? $_SESSION['dropship'] : array();
		$duplicate = FALSE;
		foreach( $existing as $existingRow ) {
			if( bc_address_row_key( $existingRow ) === bc_address_row_key( $row ) ) {
				$duplicate = TRUE;
			}
		}
		$rowName = htmlspecialchars( $row['delivery_name'] );
		if( $duplicate ) {
			$feedback['warning'][] = $rowName.' '.tra( 'is already in the list.' );
		} else {
			if( empty( $row['_errors'] ) && !empty( $add['save'] ) ) {
				$saveErrors = array();
				if( bc_address_save_to_book( $row, $saveErrors ) ) {
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
	foreach( bc_address_fields() as $field ) {
		if( is_array( $edit ) && array_key_exists( $field, $edit ) && !is_array( $edit[$field] ) ) {
			$row[$field] = trim( (string)$edit[$field] );
		}
	}
	$row['_errors'] = array();
	bc_address_validate_row( $row, bc_address_store_country_iso2() );
	$_SESSION['dropship'][$editIdx] = $row;
	$rowName = htmlspecialchars( BitBase::getParameter( $row, 'delivery_name', '' ) );
	if( empty( $row['_errors'] ) ) {
		$bookNote = '';
		// A row from the address book can write the change back to its entry (the id comes from the session row)
		if( !empty( $row['_book_id'] ) && is_array( $edit ) && !empty( $edit['update_book'] ) ) {
			$saveErrors = array();
			$duplicate = FALSE;
			if( bc_address_save_to_book( $row, $saveErrors, $duplicate, (int)$row['_book_id'] ) ) {
				$bookNote = ' '.tra( 'and in your address book' );
			} else {
				$feedback['warning'][] = $rowName.' '.tra( 'was not updated in your address book:' ).' '.implode( ' ', $saveErrors );
			}
		}
		$feedback['success'][] = tra( 'Updated' ).' '.$rowName.$bookNote.'.';
	} else {
		$feedback['warning'][] = $rowName.' '.tra( 'still needs a fix:' ).' '.implode( '. ', $row['_errors'] );
	}
} elseif( ($qtyIdx = BitBase::getParameter( $_POST, 'dropship_qty' )) !== NULL && $qtyIdx !== '' && isset( $_SESSION['dropship'][$qtyIdx] ) ) {
	// Custom quantities for one address: a map of live-cart line key → quantity kept on the row as cart_quantities.
	// Quantities equal to the live cart mean "send as is", so the key is dropped and the row follows the cart again.
	$posted = BitBase::getParameter( $_POST, 'qty', array() );
	$posted = ( is_array( $posted ) && isset( $posted[$qtyIdx] ) && is_array( $posted[$qtyIdx] ) ) ? $posted[$qtyIdx] : array();
	$quantities = bc_dropship_custom_quantities( $posted );
	$rowName = htmlspecialchars( BitBase::getParameter( $_SESSION['dropship'][$qtyIdx], 'delivery_name', '' ) );
	if( $quantities === NULL ) {
		unset( $_SESSION['dropship'][$qtyIdx]['cart_quantities'] );
		$feedback['success'][] = $rowName.' '.tra( 'gets the cart as is.' );
	} elseif( !array_sum( $quantities ) ) {
		$feedback['error'][] = $rowName.': '.tra( 'every quantity is zero, so nothing would ship. Remove the address instead.' );
	} else {
		$_SESSION['dropship'][$qtyIdx]['cart_quantities'] = $quantities;
		$feedback['success'][] = tra( 'Quantities set for' ).' '.$rowName.'.';
	}
} elseif( ($qtyIdx = BitBase::getParameter( $_POST, 'dropship_qty_reset' )) !== NULL && $qtyIdx !== '' && isset( $_SESSION['dropship'][$qtyIdx] ) ) {
	unset( $_SESSION['dropship'][$qtyIdx]['cart_quantities'] );
	$feedback['success'][] = htmlspecialchars( BitBase::getParameter( $_SESSION['dropship'][$qtyIdx], 'delivery_name', '' ) ).' '.tra( 'gets the cart as is.' );
} elseif( BitBase::getParameter( $_POST, 'dropship_upload' ) ) {
	// An upload adds to the list; Remove all clears it. A vCard goes straight in; a sheet goes through the column mapping when its headings are not recognized.
	$uploadFile = BitBase::getParameter( $_FILES, 'drop_csv', array() );
	$uploadName = (string)BitBase::getParameter( $uploadFile, 'name', '' );
	$uploadTmp = (string)BitBase::getParameter( $uploadFile, 'tmp_name', '' );
	$uploadExt = strtolower( pathinfo( $uploadName, PATHINFO_EXTENSION ) );
	$saveToBook = !empty( $_POST['drop_save_book'] );
	if( empty( $uploadTmp ) || !empty( $uploadFile['error'] ) ) {
		$feedback['error'][] = tra( 'Choose an address file (.csv, .xlsx, or .vcf) to upload.' );
	} elseif( in_array( $uploadExt, array( 'vcf', 'vcard' ) ) || bc_address_is_vcard( $uploadTmp ) ) {
		$parseErrors = array();
		$fileRows = bc_address_parse_vcard( $uploadTmp, $parseErrors );
		$feedback['error'] = array_merge( $feedback['error'], $parseErrors['error'] );
		$feedback['warning'] = array_merge( $feedback['warning'], $parseErrors['warning'] );
		if( $fileRows ) {
			$counts = bc_dropship_append_rows( $fileRows, $uploadName, $saveToBook );
			$feedback['success'][] = bc_dropship_append_message( $counts );
		}
	} else {
		$readErrors = array();
		if( $table = bc_address_read_table( $uploadTmp, $uploadName, $readErrors ) ) {
			$import = bc_address_import_store( $table, 'dropship', array( 'save' => $saveToBook ) );
			$feedback['warning'] = array_merge( $feedback['warning'], $readErrors['warning'] );
			if( bc_address_mapping_complete( $import['mapping'] ) && empty( $_POST['drop_check_columns'] ) ) {
				$dropMapping = bc_dropship_apply_import( $import, $feedback );
			} else {
				$dropMapping = bc_address_mapping_view( $table, $import['sheet'], $import['mapping'] );
				if( !bc_address_mapping_complete( $import['mapping'] ) ) {
					$feedback['warning'][] = tra( 'The column headings in' ).' '.htmlspecialchars( $uploadName ).' '.tra( 'were not recognized. Match each field to a column below.' );
				}
			}
		} else {
			$feedback['error'] = array_merge( $feedback['error'], $readErrors['error'] );
			$feedback['warning'] = array_merge( $feedback['warning'], $readErrors['warning'] );
		}
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_map' ) ) {
	// The mapping form: a sheet change re-guesses, Use these columns re-reads the stored upload with the posted mapping
	if( !($import = bc_address_import_get( 'dropship' )) ) {
		$feedback['error'][] = tra( 'The uploaded file is no longer available. Upload it again.' );
	} elseif( BitBase::getParameter( $_POST, 'dropship_map' ) === 'cancel' ) {
		bc_address_import_clear();
	} else {
		$sheetIndex = (int)BitBase::getParameter( $_POST, 'map_sheet', $import['sheet'] );
		if( !isset( $import['table']['sheets'][$sheetIndex] ) ) {
			$sheetIndex = $import['sheet'];
		}
		if( $sheetIndex !== (int)$import['sheet'] || BitBase::getParameter( $_POST, 'dropship_map' ) === 'sheet' ) {
			$mapping = bc_address_guess_mapping( $import['table']['sheets'][$sheetIndex]['headers'] );
			bc_address_import_update( array( 'sheet' => $sheetIndex, 'mapping' => $mapping ) );
			$dropMapping = bc_address_mapping_view( $import['table'], $sheetIndex, $mapping );
		} else {
			$mapping = bc_address_mapping_from_post( BitBase::getParameter( $_POST, 'map', array() ), $import['table']['sheets'][$sheetIndex]['headers'], $import['mapping'] );
			bc_address_import_update( array( 'sheet' => $sheetIndex, 'mapping' => $mapping ) );
			$import = bc_address_import_get( 'dropship' );
			if( !bc_address_mapping_complete( $mapping ) ) {
				$feedback['error'][] = tra( 'Pick the column that holds the name (or first and last name) and the one that holds the street.' );
				$dropMapping = bc_address_mapping_view( $import['table'], $sheetIndex, $mapping );
			} else {
				$dropMapping = bc_dropship_apply_import( $import, $feedback );
			}
		}
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_map_show' ) ) {
	// "Check columns" after an upload that matched on its own
	if( $import = bc_address_import_get( 'dropship' ) ) {
		$dropMapping = bc_address_mapping_view( $import['table'], $import['sheet'], $import['mapping'] );
	} else {
		$feedback['error'][] = tra( 'The uploaded file is no longer available. Upload it again.' );
	}
} elseif( BitBase::getParameter( $_POST, 'dropship_process' ) ) {
	// Create orders: the confirmation page posts the same fields the Process form carried, so one collection serves both
	$sessionCopy = $_SESSION;
	unset( $_SESSION['orders_id'] );
	$batch = bc_dropship_collect( $_POST );
	$feedback['error'] = array_merge( $feedback['error'], $batch['errors'], $batch['skipped'] );
	$savedToBook = 0;

	if( empty( $batch['errors'] ) && !empty( $batch['orders'] ) ) {
		// Quantity discounts come from the whole batch, not each address
		bc_dropship_apply_batch_pricing( $batch['quantities'] );
		$batchLabel = bc_dropship_batch_label( $batch['quantities'], count( $batch['orders'] ) );
		foreach( $batch['orders'] as $orderIdx => $batchOrder ) {
			$checkoutHash = $batchOrder['row'];
			$rowLabel = $batchOrder['label'];
			if( !$batch['notify'] ) {
				$checkoutHash['no_order_email'] = TRUE;
			}

			// Mark the order the way ot_expedite marks an expedited one, so staff can see it came from a drop-ship batch and why its lines are priced at the batch tier
			$checkoutHash['comments'] = trim( $batchLabel."\n\n".BitBase::getParameter( $checkoutHash, 'comments', '' ) );

			// Some payment parameters are expected in request, and some in session during browser checkout. Merge hash and session and them all in under the session
			$dropSession = array_merge( $sessionCopy, $checkoutHash );
			$dropSession['shipping_quote'] = $batchOrder['quote'];

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
						if( $batchOrder['save'] ) {
							$saveErrors = array();
							if( bc_address_save_to_book( $checkoutHash, $saveErrors ) ) {
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
		if( $savedToBook ) {
			$feedback['success'][] = $savedToBook.' '.tra( 'addresses saved to your address book.' );
		}
		if( empty( $_SESSION['dropship'] ) ) {
			unset( $_SESSION['dropship'], $_SESSION['dropship_file'] );
			bc_address_import_clear();
		}
	}
}

$dropPreview = array();
$dropShippingMethods = array();
$dropShippingGroups = array();
$dropCartEmpty = FALSE;
$dropReadyCount = 0;
$dropActiveCartRows = 0;
$dropBatchQuantities = array();
if( !empty( $_SESSION['dropship'] ) ) {
	$dropCartEmpty = !$gBitCustomer->mCart->count_contents();
	// Quantity discounts count every address that can become an order, so the table and the rail show the batch tier
	// the confirmation page will price at. Unchecking a row in the browser changes the batch only on the next post.
	$batchRows = array();
	foreach( $_SESSION['dropship'] as $rowHash ) {
		if( empty( $rowHash['_errors'] ) && !( bc_dropship_uses_active_cart( $rowHash ) && $dropCartEmpty ) ) {
			$batchRows[] = $rowHash;
		}
	}
	$dropBatchQuantities = bc_dropship_batch_quantities( $batchRows );
	bc_dropship_apply_batch_pricing( $dropBatchQuantities );
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
$dropItemsTotal = 0.0;
$dropCustomRows = 0;
foreach( $dropPreview as $preview ) {
	if( empty( $preview['errors'] ) && $preview['selected_quote'] !== '' ) {
		$dropShippingTotal += (float)$preview['quotes'][$preview['selected_quote']]['cost'];
	}
	if( empty( $preview['errors'] ) ) {
		$dropItemsTotal += $preview['cart_total'];
	}
	if( $preview['custom'] ) {
		$dropCustomRows++;
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
$gBitSmarty->assign( 'dropItemsTotalDisplay', $currencies->format( $dropItemsTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropShippingTotalDisplay', $currencies->format( $dropShippingTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropOrdersTotalDisplay', $currencies->format( $dropItemsTotal + $dropShippingTotal, TRUE, '', '', FALSE ) );
$gBitSmarty->assign( 'dropCustomRows', $dropCustomRows );
$gBitSmarty->assign( 'dropBatchCopies', array_sum( $dropBatchQuantities ) );
$gBitSmarty->assign( 'dropPoNumber', BitBase::getParameter( $dropPaymentDefaults, 'payment_po_number', '' ) );
$gBitSmarty->assign( 'dropCurrencySymbol', $currencies->getLeftSymbol( DEFAULT_CURRENCY ) );
// Column mapping step (a sheet whose headings were not recognized, or Check columns), and whether a mapped upload can still be checked
$gBitSmarty->assign( 'dropMapping', $dropMapping );
$dropImport = bc_address_import_get( 'dropship' );
$gBitSmarty->assign( 'dropImportFile', ( $dropImport && empty( $dropMapping ) ) ? $dropImport['table']['name'] : '' );
// Country select and the state lists for the typed row and inline edit
$gBitSmarty->assign( 'dropCountries', bc_address_country_options() );
$gBitSmarty->assign( 'dropStoreCountry', bc_address_store_country_iso2() );
$gBitSmarty->assign( 'dropZonesJson', json_encode( bc_address_zones_by_country() ) );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_dropship.tpl' );

// Add parsed rows to the session list, skipping any already there. With $pSaveToBook, valid rows go to the address book at once.
function bc_dropship_append_rows( $pRows, $pFileName, $pSaveToBook ) {
	$counts = array( 'added' => 0, 'skipped' => 0, 'saved' => 0, 'book_duplicate' => 0, 'not_saved' => array(), 'problems' => 0 );
	$existing = is_array( BitBase::getParameter( $_SESSION, 'dropship' ) ) ? $_SESSION['dropship'] : array();
	$seen = array();
	foreach( $existing as $existingRow ) {
		$seen[bc_address_row_key( $existingRow )] = TRUE;
	}
	foreach( $pRows as $row ) {
		if( $pFileName !== '' ) {
			$row['_file'] = $pFileName;
		}
		$rowKey = bc_address_row_key( $row );
		if( isset( $seen[$rowKey] ) ) {
			$counts['skipped']++;
			continue;
		}
		$seen[$rowKey] = TRUE;
		if( !empty( $row['_errors'] ) ) {
			$counts['problems']++;
		}
		if( $pSaveToBook && empty( $row['_book'] ) ) {
			if( empty( $row['_errors'] ) ) {
				$saveErrors = array();
				$duplicate = FALSE;
				if( bc_address_save_to_book( $row, $saveErrors, $duplicate ) ) {
					$row['_saved'] = TRUE;
					$duplicate ? $counts['book_duplicate']++ : $counts['saved']++;
				} else {
					$counts['not_saved'][] = htmlspecialchars( BitBase::getParameter( $row, 'delivery_name', '' ) ).': '.implode( ' ', $saveErrors );
				}
			} else {
				// saved at Process once the row is fixed
				$row['_save'] = TRUE;
			}
		}
		$existing[] = $row;
		$counts['added']++;
	}
	$_SESSION['dropship'] = $existing;
	if( $pFileName !== '' ) {
		$fileNames = is_array( BitBase::getParameter( $_SESSION, 'dropship_file' ) ) ? $_SESSION['dropship_file'] : array();
		$fileNames[] = $pFileName;
		$_SESSION['dropship_file'] = array_values( array_unique( $fileNames ) );
	}
	return $counts;
}

function bc_dropship_append_message( $pCounts ) {
	$message = $pCounts['added'].' '.tra( 'addresses added.' );
	if( $pCounts['skipped'] ) {
		$message .= ' '.$pCounts['skipped'].' '.tra( 'already in the list were skipped.' );
	}
	if( $pCounts['saved'] || $pCounts['book_duplicate'] ) {
		$message .= ' '.$pCounts['saved'].' '.tra( 'saved to your address book' ).( $pCounts['book_duplicate'] ? ' ('.$pCounts['book_duplicate'].' '.tra( 'already there' ).')' : '' ).'.';
	}
	if( !empty( $pCounts['not_saved'] ) ) {
		$message .= ' '.tra( 'Not saved to your address book:' ).' '.implode( '; ', $pCounts['not_saved'] );
	}
	return $message.' '.tra( 'Check the list, pick shipping and payment, then create the orders.' );
}

// Rows from the stored upload with its current mapping replace that file's rows in the list. Returns NULL (no mapping panel to show).
function bc_dropship_apply_import( $pImport, &$pFeedback ) {
	$table = $pImport['table'];
	$sheet = $table['sheets'][$pImport['sheet']];
	$rowErrors = array();
	$fileRows = bc_address_rows_from_table( $sheet, $pImport['mapping'], $table['name'], count( $table['sheets'] ), $rowErrors );
	$pFeedback['error'] = array_merge( $pFeedback['error'], $rowErrors['error'] );
	$pFeedback['warning'] = array_merge( $pFeedback['warning'], $rowErrors['warning'] );
	if( empty( $fileRows ) ) {
		return bc_address_mapping_view( $table, $pImport['sheet'], $pImport['mapping'] );
	}
	if( $ignored = bc_address_mapping_ignored( $pImport['mapping'], $sheet['headers'] ) ) {
		$pFeedback['warning'][] = tra( 'Ignored columns:' ).' '.implode( ', ', array_map( 'htmlspecialchars', $ignored ) );
	}
	// a second pass over the same file (new mapping) replaces its earlier rows rather than skipping them as duplicates
	if( !empty( $pImport['applied'] ) && !empty( $_SESSION['dropship'] ) && is_array( $_SESSION['dropship'] ) ) {
		foreach( $_SESSION['dropship'] as $idx => $row ) {
			if( (string)BitBase::getParameter( $row, '_file', '' ) === (string)$table['name'] ) {
				unset( $_SESSION['dropship'][$idx] );
			}
		}
	}
	$counts = bc_dropship_append_rows( $fileRows, $table['name'], !empty( $pImport['save'] ) );
	bc_address_import_update( array( 'applied' => TRUE ) );
	$pFeedback['success'][] = bc_dropship_append_message( $counts );
	return NULL;
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
	$ret['custom'] = $ret['uses_active_cart'] && !empty( $pRowHash['cart_quantities'] ) && is_array( $pRowHash['cart_quantities'] );
	$ret['quantities'] = $ret['custom'] ? $pRowHash['cart_quantities'] : array();
	$ret['cart_total'] = 0.0;
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
	if( $ret['custom'] && !$ret['item_count'] && !$pLiveCartEmpty ) {
		$ret['errors'][] = tra( 'Every quantity is zero' );
	}
	if( empty( $ret['errors'] ) ) {
		$ret['cart_total'] = (float)$cart->show_total();
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

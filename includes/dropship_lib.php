<?php
/**
 * Drop ship (ship to multiple addresses): the cart, quote, payment, and batch helpers shared by the drop ship page
 * (pages/dropship) and its confirmation page (pages/dropship_confirmation). Row hashes are the delivery_* keys of
 * includes/address_import_lib.php plus the order columns (cart, shipping_*, payment_*, comments); meta keys start
 * with '_' and are stripped before a row becomes an order.
 *
 * @package bitcommerce
 */

function bc_dropship_quote( $dropShipCart ) {
	global $gCommerceShipping;

	$quotes = $gCommerceShipping->quote($dropShipCart);

	return $quotes;
}

function bc_dropship_cart( $pOrderHash ) {

	$hashCart = new CommerceTemporaryCart();
	$hashCart->loadFromHash( $pOrderHash );

	// Custom quantities for this address. Set the copied contents directly: updateQuantity() and cleanup()
	// write to the customer's basket table by line key, which would change the live cart.
	$quantities = BitBase::getParameter( $pOrderHash, 'cart_quantities', array() );
	if( is_array( $quantities ) && !empty( $quantities ) && is_array( $hashCart->contents ) ) {
		foreach( array_keys( $hashCart->contents ) as $lineKey ) {
			if( array_key_exists( $lineKey, $quantities ) ) {
				$qty = (int)$quantities[$lineKey];
				if( $qty > 0 ) {
					$hashCart->contents[$lineKey]['products_quantity'] = $qty;
				} else {
					unset( $hashCart->contents[$lineKey] );
				}
			}
		}
		$hashCart->calculate( TRUE );
	}

	return $hashCart;
}

// Posted qty[row][lineKey] values → line key → whole number, or NULL when every line matches the live cart (send as is)
function bc_dropship_custom_quantities( $pPosted ) {
	global $gBitCustomer;
	$live = is_array( $gBitCustomer->mCart->contents ) ? $gBitCustomer->mCart->contents : array();
	$quantities = array();
	$custom = FALSE;
	foreach( $live as $lineKey => $line ) {
		$liveQty = (int)BitBase::getParameter( $line, 'products_quantity', 0 );
		$qty = ( is_array( $pPosted ) && isset( $pPosted[$lineKey] ) && !is_array( $pPosted[$lineKey] ) && is_numeric( $pPosted[$lineKey] ) ) ? max( 0, (int)$pPosted[$lineKey] ) : $liveQty;
		if( $qty > MAX_CART_QUANTITY ) {
			$qty = MAX_CART_QUANTITY;
		}
		$quantities[$lineKey] = $qty;
		if( $qty !== $liveQty ) {
			$custom = TRUE;
		}
	}
	return $custom ? $quantities : NULL;
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

// payment_* fields posted for the whole batch (blank ones left out)
function bc_dropship_batch_payment( $pPost ) {
	$ret = array();
	foreach( $pPost as $postKey => $postValue ) {
		if( strpos( $postKey, 'payment_' ) === 0 && !is_array( $postValue ) && trim( (string)$postValue ) !== '' ) {
			$ret[$postKey] = trim( (string)$postValue );
		}
	}
	return $ret;
}

// The module_method id for one row: its posted menu, else the row's own shipping columns, else the bulk carrier|title key quoted for this row
function bc_dropship_row_shipping( $pRow, $pIdx, $pRowQuotes, $pBulkKey ) {
	$rowQuote = is_array( $pRowQuotes ) ? (string)BitBase::getParameter( $pRowQuotes, $pIdx, '' ) : '';
	if( strpos( $rowQuote, '_' ) === FALSE && trim( (string)BitBase::getParameter( $pRow, 'shipping_method', '' ) ) !== '' ) {
		$rowQuote = BitBase::getParameter( $pRow, 'shipping_method' ).'_'.BitBase::getParameter( $pRow, 'shipping_method_code' );
	}
	if( strpos( $rowQuote, '_' ) === FALSE && strpos( (string)$pBulkKey, '|' ) !== FALSE ) {
		$rowQuote = bc_dropship_resolve_method( bc_dropship_cart( $pRow ), $pBulkKey );
	}
	return ( strpos( $rowQuote, '_' ) === FALSE ) ? '' : $rowQuote;
}

// Quantity of each product across every order in the batch: products_id => total, from each row's cart
function bc_dropship_batch_quantities( $pRows ) {
	$ret = array();
	foreach( $pRows as $row ) {
		$cart = bc_dropship_cart( $row );
		if( is_array( $cart->contents ) ) {
			foreach( $cart->contents as $lineKey => $line ) {
				$productsId = (int)zen_get_prid( $lineKey );
				$ret[$productsId] = BitBase::getParameter( $ret, $productsId, 0 ) + (int)BitBase::getParameter( $line, 'products_quantity', 0 );
			}
		}
	}
	return $ret;
}

// Price every cart and order line in this request at the batch quantity's discount tier
function bc_dropship_apply_batch_pricing( $pQuantities ) {
	CommerceProduct::setDiscountQuantityFloor( $pQuantities );
}

// "DROP SHIP ORDER (12 copies across 10 orders)" for the order comment
function bc_dropship_batch_label( $pQuantities, $pOrderCount ) {
	return 'DROP SHIP ORDER ('.array_sum( $pQuantities ).' '.tra( 'copies across' ).' '.(int)$pOrderCount.' '.tra( 'orders' ).')';
}

/**
 * Turn the Process form post into the batch that will become orders. Shared by the confirmation page and Process
 * so both see the same rows, shipping, payment, and batch quantities.
 *
 * @return array errors (the whole batch is refused), skipped (per-row lines), orders (idx => row without meta keys,
 *   label, save-to-book flag, module_method quote), payment (payment_* fields), notify, quantities (products_id => total)
 */
function bc_dropship_collect( $pPost ) {
	global $gBitCustomer;
	$ret = array( 'errors' => array(), 'skipped' => array(), 'orders' => array(), 'payment' => bc_dropship_batch_payment( $pPost ), 'notify' => !empty( $pPost['notify'] ), 'quantities' => array() );
	$orderIndexes = BitBase::getParameter( $pPost, 'dropship_order', array() );
	$rowShippingQuotes = BitBase::getParameter( $pPost, 'shipping_quote', array() );
	$bulkShippingQuote = (string)BitBase::getParameter( $pPost, 'shipping_bulk', '' );
	$liveCartEmpty = !$gBitCustomer->mCart->count_contents();

	if( empty( $_SESSION['dropship'] ) ) {
		$ret['errors'][] = tra( 'There are no addresses loaded. Upload your address file again.' );
	} elseif( !is_array( $orderIndexes ) || !count( $orderIndexes ) ) {
		$ret['errors'][] = tra( 'No addresses are checked.' );
	} elseif( empty( $ret['payment']['payment_method'] ) && !bc_dropship_rows_carry( $_SESSION['dropship'], $orderIndexes, 'payment_method' ) ) {
		$ret['errors'][] = tra( 'Choose a payment method for these orders.' );
	} else {
		foreach( $orderIndexes as $orderIdx ) {
			if( !($checkoutHash = BitBase::getParameter( $_SESSION['dropship'], $orderIdx )) ) {
				continue;
			}
			$rowLabel = trim( BitBase::getParameter( $checkoutHash, 'delivery_name', '' ).', '.BitBase::getParameter( $checkoutHash, 'delivery_city', '' ), ', ' );
			if( !empty( $checkoutHash['_errors'] ) ) {
				$ret['skipped'][] = tra( 'Skipped' ).' '.$rowLabel.': '.implode( '; ', $checkoutHash['_errors'] );
				continue;
			}
			$saveToBook = !empty( $checkoutHash['_save'] ) && empty( $checkoutHash['_saved'] ) && empty( $checkoutHash['_book'] );
			foreach( array_keys( $checkoutHash ) as $metaKey ) {
				if( strpos( $metaKey, '_' ) === 0 ) {
					unset( $checkoutHash[$metaKey] );
				}
			}
			if( bc_dropship_uses_active_cart( $checkoutHash ) && $liveCartEmpty ) {
				$ret['skipped'][] = tra( 'Skipped' ).' '.$rowLabel.': '.tra( 'your cart is empty, so there is nothing to ship.' );
				continue;
			}
			foreach( $ret['payment'] as $paymentKey => $paymentValue ) {
				if( trim( (string)BitBase::getParameter( $checkoutHash, $paymentKey, '' ) ) === '' ) {
					$checkoutHash[$paymentKey] = $paymentValue;
				}
			}
			$rowQuote = bc_dropship_row_shipping( $checkoutHash, $orderIdx, $rowShippingQuotes, $bulkShippingQuote );
			if( $rowQuote === '' ) {
				$ret['skipped'][] = tra( 'Skipped' ).' '.$rowLabel.': '.tra( 'no shipping method chosen.' );
				continue;
			}
			list( $checkoutHash['shipping_method'], $checkoutHash['shipping_method_code'] ) = explode( '_', $rowQuote, 2 );
			$ret['orders'][$orderIdx] = array( 'row' => $checkoutHash, 'label' => $rowLabel, 'save' => $saveToBook, 'quote' => $rowQuote );
		}
	}
	$ret['quantities'] = bc_dropship_batch_quantities( array_column( $ret['orders'], 'row' ) );
	return $ret;
}

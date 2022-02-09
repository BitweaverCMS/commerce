<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require(DIR_FS_CLASSES . 'http_client.php');

$gCommerceSystem->setHeadingTitle( tra( 'Checkout Shipping' ) );

if( !$gBitUser->isRegistered() || !empty( $_REQUEST['choose_address'] ) || !empty( $_REQUEST['save_address'] ) ) {
	if( $gBitUser->isRegistered() ) {
		if( !empty( $_REQUEST['save_address'] ) ) {
			// process a new address
			$process = true;
			if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
				$_SESSION['sendto'] = $_REQUEST['address'];
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING));
			} else {
				$gBitSmarty->assign( 'addressErrors', $gBitCustomer->mErrors );
				$_REQUEST['change_address'] = TRUE;
			}
		} elseif( !empty( $_REQUEST['choose_address'] ) && !empty( $_REQUEST['address'] ) ) {
			if( empty( $_SESSION['sendto'] ) || $_SESSION['sendto'] != $_REQUEST['address'] ) {
				if( $gBitCustomer->isAddressOwner( $_REQUEST['address'] ) ) {
					$_SESSION['sendto'] = $_REQUEST['address'];
					zen_redirect( zen_href_link( FILENAME_CHECKOUT_SHIPPING ) );
				}
			}
		}
	}
	if( !empty( $_REQUEST['save_address'] ) ) {
		// an inline registration failed. Verify the fields and reassign so customer doesn't lose info
		$addressErrors = array();
		$gBitCustomer->verifyAddress( $_REQUEST, $addressErrors );
		$gBitSmarty->assign( 'address', $_REQUEST['address_store'] );
		$gBitSmarty->assign( 'addressErrors', $addressErrors );
	}
}

if( !$gBitCustomer->mCart->count_contents() ) {
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

// if the order contains only virtual products, forward the customer to the delivery page as a shipping address is not needed
if( $gBitCustomer->mCart->get_content_type() == 'virtual') {
	$_SESSION['shipping'] = false;
	$_SESSION['sendto'] = false;
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT));
}

// Validate Cart for checkout
$_SESSION['valid_to_checkout'] = true;
if( !$gBitCustomer->mCart->verifyCheckout() ) {
	$messageStack->add('header', 'Please update your order ...', 'error');
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

require_once(BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php');

// if the no delivery address, try to get one by default
if( empty( $_SESSION['sendto'] ) ) {
	if( $defaultAddressId = $gBitCustomer->getDefaultAddressId() ) {
		$_SESSION['sendto'] =	$defaultAddressId;
	}
}

if( isset( $_REQUEST['change_address'] ) ) {
	$gBitSmarty->assign( 'addresses', $gBitCustomer->getAddresses() );
	$gBitSmarty->assign( 'changeAddress', TRUE );
} else {

	// load all enabled shipping modules
	require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceShipping.php');
	global $gCommerceShipping;

	if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
		$pass = false;

		switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
			case 'national':
				if( $order->delivery['country_id'] == STORE_COUNTRY ) {
					$pass = true;
				}
				break;
			case 'international':
				if( $order->delivery['country_id'] != STORE_COUNTRY ) {
					$pass = true;
				}
				break;
			case 'both':
				$pass = true;
				break;
		}

		$free_shipping = false;

		if ( ($pass == true) && ($gBitCustomer->mCart->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
			$free_shipping = true;
		}
	} elseif( $gBitCustomer->mCart->free_shipping_items() == $gBitCustomer->mCart->quantity ) {
		$free_shipping = TRUE;
	} else {
		$free_shipping = false;
	}

	if( isset($_POST['action']) && ($_POST['action'] == 'process') ) {
		if (zen_not_null($_POST['comments'])) {
			$_SESSION['comments'] = zen_db_prepare_input($_POST['comments']);
		}

		if ( ($gCommerceShipping->isShippingAvailable() > 0) || ($free_shipping == true) ) {
			if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
				$_SESSION['shipping'] = $_POST['shipping'];

				if ($_SESSION['shipping'] == 'free_free') {
					$quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
					$quote[0]['methods'][0]['cost'] = '0';
				} elseif( !empty( $_SESSION['shipping'] ) ) {
					list($module, $method) = explode('_', $_SESSION['shipping'], 2);
					$quote = $gCommerceShipping->quote( $gBitCustomer->mCart, $method, $module);
				}

				if( isset( $quote['error'] ) ) {
					$_SESSION['shipping'] = '';
				} elseif( $gCommerceShipping->quoteToSession( $quote ) ) {
					zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT));
				} else {
					$gBitSmarty->assign( 'errors', array( 'Shipping method could not be calculated.', $quote[0]['methods'][0]['title'] ) );
				}
			} elseif( empty( $free_shipping ) ) {
				$gBitSmarty->assign( 'errors', 'Please select a shipping method' );
			}
		} else {
			// not virtual product, but no shipping cost.
			$_SESSION['shipping'] = (!$free_shipping ? 'free_free' : false);
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT));
		}
	}
	if( $gBitUser->isRegistered() && $gCommerceShipping->isShippingAvailable() && !empty( $_SESSION['sendto'] ) && empty( $_REQUEST['change_address'] ) ) {
		// get all available shipping quotes
		$quotes = array();

		if( empty( $free_shipping ) ) {		
			$quotes = $gCommerceShipping->quote( $gBitCustomer->mCart );
		}

		// if no shipping method has been selected, automatically select the cheapest method.
		// if the modules status was changed when none were available, to save on implementing
		// a javascript force-selection method, also automatically select the cheapest shipping
		// method if more than one module is now enabled
		if ( empty( $_SESSION['shipping'] ) || ( $_SESSION['shipping'] && ($_SESSION['shipping'] == false) && ($gCommerceShipping->isShippingAvailable() > 1) ) ) {
			$cheapest = false;
			if( !empty( $quotes ) ) {
				foreach( $quotes as $quote ) {
					if( !empty( $quote['methods'] ) ) {
						for( $i=0; $i< count( $quote['methods'] ); $i++ ) {
							if( empty( $cheapest ) || ($quote['methods'][$i]['cost'] < $cheapest['cost']) ) {
								$cheapest = array( 'id' => $quote['id'] . '_' . $quote['methods'][$i]['id'],
													'title' => $quote['module'] . ' (' . $quote['methods'][$i]['title'] . ')',
													'cost' => $quote['methods'][$i]['cost'],
													'module' => $quote['id']
												 );
							}
						}
					}
				}
			}
			if( $cheapest ) {
				$_SESSION['shipping'] = $cheapest;
			}
		}

		$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING));
		$breadcrumb->add(NAVBAR_TITLE_2);

		$gBitSmarty->assign( 'shippingModules', TRUE );
		$gBitSmarty->assign( 'quotes', $quotes );
		$gBitSmarty->register_object('currencies', $currencies, array(), true, array('formatAddTax'));
		$gBitSmarty->assign( 'freeShipping', $free_shipping );
		$gBitSmarty->assign( 'sessionShippingId', BitBase::getParameter( $_SESSION, 'shipping' ) );
	}
}

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_shipping.tpl' );


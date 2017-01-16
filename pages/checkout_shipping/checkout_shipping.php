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

define( 'HEADING_TITLE', tra( 'Checkout Shipping' ) );

if( !$gBitUser->isRegistered() || !empty( $_REQUEST['choose_address'] ) || !empty( $_REQUEST['save_address'] ) ) {
	if( $gBitUser->isRegistered() ) {
		if( !empty( $_REQUEST['save_address'] ) ) {
			// process a new address
			$process = true;
			if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
				$_SESSION['sendto'] = $_REQUEST['address'];
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
			} else {
				$gBitSmarty->assign( 'addressErrors', $gBitCustomer->mErrors );
				$_REQUEST['change_address'] = TRUE;
			}
		} elseif( !empty( $_REQUEST['choose_address'] ) && !empty( $_REQUEST['address'] ) ) {
			if( empty( $_SESSION['sendto'] ) || $_SESSION['sendto'] != $_REQUEST['address'] ) {
				if( $gBitCustomer->isAddressOwner( $_REQUEST['address'] ) ) {
					$_SESSION['sendto'] = $_REQUEST['address'];
					zen_redirect( zen_href_link( FILENAME_CHECKOUT_SHIPPING, '', 'SSL' ) );
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
	zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

// Validate Cart for checkout
$_SESSION['valid_to_checkout'] = true;
if( !$gBitCustomer->mCart->verifyCheckout() ) {
	$messageStack->add('header', 'Please update your order ...', 'error');
	zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

require_once(BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php');
$order = new order();
$gBitSmarty->assign( 'order', $order );

// if the no delivery address, try to get one by default
if( empty( $_SESSION['sendto'] ) || !$gBitCustomer->isValidAddress( $order->delivery ) ) {
	if( $defaultAddressId = $gBitCustomer->getDefaultAddress() ) {
		$order->delivery = $gBitCustomer->getAddress( $defaultAddressId );
		$_SESSION['sendto'] =	$defaultAddressId;
	}
}

if( isset( $_REQUEST['change_address'] ) || !$gBitCustomer->isValidAddress( $order->delivery ) ) {
	if( $addresses = $gBitCustomer->getAddresses() ) {
		$gBitSmarty->assign( 'addresses', $addresses );
	}
	$gBitSmarty->assign( 'changeAddress', TRUE );
} else {

	// load all enabled shipping modules
	require( BITCOMMERCE_PKG_PATH.'classes/CommerceShipping.php');
	$shipping = new CommerceShipping();

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

		if ( (zen_count_shipping_modules() > 0) || ($free_shipping == true) ) {
			if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
				$_SESSION['shipping'] = $_POST['shipping'];

				list($module, $method) = explode('_', $_SESSION['shipping']);
				if ( is_object($$module) || ($_SESSION['shipping'] == 'free_free') ) {
					if ($_SESSION['shipping'] == 'free_free') {
						$quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
						$quote[0]['methods'][0]['cost'] = '0';
					} else {
						$quote = $shipping->quote( $gBitCustomer->mCart->show_weight(), $method, $module);
					}
					if (isset($quote['error'])) {
						$_SESSION['shipping'] = '';
					} else {
						if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
							$_SESSION['shipping'] = array(
								'id' => $_SESSION['shipping'],
								'title' => (($free_shipping == true) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
								'cost' => $quote[0]['methods'][0]['cost'],
								'code' => !empty( $quote[0]['methods'][0]['code'] ) ? $quote[0]['methods'][0]['code'] : NULL,
								);
							zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
						}
					}
				} else {
					$_SESSION['shipping'] = false;
				}
			} elseif( empty( $free_shipping ) ) {
				$gBitSmarty->assign( 'errors', "Please select a shipping method" );
			}
		} else {
			// not virtual product, but no shipping cost.
			$_SESSION['shipping'] = (!$free_shipping ? 'free_free' : false);
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
		}
	}
	if( $gBitUser->isRegistered() && zen_count_shipping_modules() && !empty( $_SESSION['sendto'] ) && empty( $_REQUEST['change_address'] ) ) {
		// get all available shipping quotes
		$quotes = array();

		if( empty( $free_shipping ) ) {		
			$quotes = $shipping->quote( $gBitCustomer->mCart->show_weight() );
		}

		// if no shipping method has been selected, automatically select the cheapest method.
		// if the modules status was changed when none were available, to save on implementing
		// a javascript force-selection method, also automatically select the cheapest shipping
		// method if more than one module is now enabled
		if ( empty( $_SESSION['shipping'] ) || ( $_SESSION['shipping'] && ($_SESSION['shipping'] == false) && (zen_count_shipping_modules() > 1) ) ) {
			$_SESSION['shipping'] = $shipping->cheapest( $gBitCustomer->mCart->show_weight() );
		}

		$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
		$breadcrumb->add(NAVBAR_TITLE_2);

		$gBitSmarty->assign( 'shippingModules', TRUE );
		$gBitSmarty->assign( 'quotes', $quotes );
		$gBitSmarty->register_object('currencies', $currencies, array(), true, array('formatAddTax'));
		$gBitSmarty->assign( 'freeShipping', $free_shipping );
		$gBitSmarty->assign( 'sessionShippingId', $_SESSION['shipping'] );
	}
}

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_checkout_shipping.tpl' );
?>

<?php
// :vim:tabstop=4:
// +--------------------------------------------------------------------+
// | Copyright (c) 2005-2010 bitcommerce.org							|
// | http://www.bitcommerce.org											|
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
// | Portions Copyright (c) 2003 The zen-cart developers				|
// | Portions Copyright (c) 2003 osCommerce								|	
// +--------------------------------------------------------------------+
//

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrderBase.php' );

class CommerceTemporaryCart extends CommerceShoppingCart {

	public function loadFromHash( &$pOrderHash ) {

		$this->setupCartFromHash( $pOrderHash );

		// Load up the cart items. This will overwrite / append existing cart database entries for this user in ::addToCart()
		if( !empty( $pOrderHash['cart'] ) ) {
			if( $pOrderHash['cart'] == 'active_cart' ) {
				global $gBitCustomer;
				unset( $pOrderHash['cart'] );
				$this->contents = $gBitCustomer->mCart->contents;
			} else {
				$lineItemStrings = explode( ';', $pOrderHash['cart'] );
				foreach( $lineItemStrings as $lineItemString ) {
					if( preg_match( '/(\d+)@(\d+):(\d+)/', $lineItemString ) ) {
						list( $productId, $productString ) = explode( '@', $lineItemString );
						list( $quantity, $optionString ) = explode( ':', $productString );
						if( BitBase::verifyId( $productId ) && $apiProduct = CommerceProduct::getCommerceObject( $productId ) ) {
							if( $apiProduct->isValid() ) {
								$apiProduct->verifyViewPermission();
							}
							$povidHash = array();
							if( $optionString ) {
								if( $povidList = explode( ',', $optionString ) ) {
									foreach( $povidList as $povid ) {
										if( $optionHash = $this->mDb->GetRow( "SELECT `products_options_id`, `products_options_values_id`, `products_options_values_name` FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_options_values_id` = ?", array( $povid ) ) ) {
											$povidHash[$optionHash['products_options_id'].'=>'.$optionHash['products_options_values_id']] = $optionHash;
										}
									}
								}
							}
							$this->addToCart( $apiProduct->mProductsId, $quantity, $povidHash, FALSE );
						} else {
							$this->mErrors['product'] = 'Product ID does not exist: '.$productId;
						}
					}
				}
			}
		}
		return empty( $this->mErrors );
	}

	protected function setupCartFromHash( &$pOrderHash ) {
		global $currencies, $gBitUser, $gBitCustomer;

		if( $defaultAddress = $gBitCustomer->getAddress( $gBitCustomer->getDefaultAddressId() ) ) {
			$this->customer = array('firstname' => $defaultAddress['entry_firstname'],
									'lastname' => $defaultAddress['entry_lastname'],
									'customers_id' => $defaultAddress['customers_id'],
									'user_id' => $defaultAddress['customers_id'],
									'company' => $defaultAddress['entry_company'],
									'street_address' => $defaultAddress['entry_street_address'],
									'suburb' => $defaultAddress['entry_suburb'],
									'city' => $defaultAddress['entry_city'],
									'postcode' => $defaultAddress['entry_postcode'],
									'state' => (!empty( $defaultAddress['entry_state'] ) ? $defaultAddress['entry_state'] : NULL),
									'zone_id' => $defaultAddress['entry_zone_id'],
									'countries_name' => $defaultAddress['countries_name'],
									'countries_id' => $defaultAddress['countries_id'],
									'countries_iso_code_2' => $defaultAddress['countries_iso_code_2'],
									'countries_iso_code_3' => $defaultAddress['countries_iso_code_3'],
									'address_format_id' => $defaultAddress['address_format_id'],
									// Needed for orderFromCart
									'address_format_id' => $defaultAddress['address_format_id'],
									'telephone' => $defaultAddress['entry_telephone'],
									'email_address' => $gBitUser->getField( 'email' )
									 );
			$this->billing = $defaultAddress;
		} else {
			$this->customer = array('firstname' => $gBitCustomer->getField( 'customers_firstname' ),
									'lastname' => $gBitCustomer->getField( 'customers_lastname' ),
									'user_id' => $gBitCustomer->getField( 'customers_id' ),
									'email_address' => $gBitCustomer->getField('customers_email_address' ),
									'customers_id' => $gBitCustomer->mCustomerId
									);
		}

		foreach( array( 'billing', 'delivery' ) as $addressType ) {
			$addressHash = array();
			$countryValue = $stateValue = NULL;
			foreach( array(
				'city' => 'city',
				'country_iso2' => 'countries_iso_code_2',
				'name' => 'name',
				'company' => 'company',
				'postcode' => 'postcode',
				'state' => 'state',
				'street_address1' => 'street_address',
				'street_address2' => 'suburb',
				'telephone' => 'telephone',
			) as $hashPostfix => $addressKey ) {
				$hashKey = $addressType.'_'.$hashPostfix;
				$hashValue = BitBase::getParameter( $pOrderHash, $hashKey, NULL );
				if( !empty( $hashValue ) ) {
					switch( $hashPostfix ) {
						case 'country_iso2':
							$countryValue = $hashValue;
							break;
						case 'state':
							$stateValue = $hashValue;
							break;
						case 'name':
							$firstSpace = strpos( $hashValue, ' ' );
							$addressHash['firstname'] = substr( $hashValue, 0, $firstSpace );
							$addressHash['lastname'] = substr( $hashValue, $firstSpace + 1 );
							$addressHash[$addressKey] = $hashValue;
							break;
						default:
							$addressHash[$addressKey] = $hashValue;
							break;
					}	
				}
			}

			if( $countryValue ) {
				if( $countryHash = zen_get_countries( $countryValue) ) {
					$addressHash['countries_id'] = $countryHash['countries_id'];
					$addressHash['country_id'] = $countryHash['countries_id'];
					$addressHash['countries_name'] = $countryHash['countries_name'];
					$addressHash['countries_iso_code_2'] = $countryHash['countries_iso_code_2'];
					$addressHash['countries_iso_code_3'] = $countryHash['countries_iso_code_3'];
					$addressHash['address_format_id'] = $countryHash['address_format_id'];
					if( $stateValue ) {
						if( $stateZone = zen_get_zone_by_name( $addressHash['countries_id'], $stateValue ) ) {
							$addressHash = array_merge( $addressHash, $stateZone );
							$addressHash['state'] = $stateZone['zone_name'];
						}
					}
				}
			} else {
//eb( $addressHash, $pOrderHash );
			}
			$this->$addressType = $addressHash;
		}
	}

	function load() {
	}
	
	function addToCart($pProductsId, $pQty = '1', $attributes = array(), $notify = true) {
		global $gBitUser, $gCommerceSystem;
		$cartItemKey = $this->getUniqueCartItemKey( $pProductsId, $attributes );

		if ($notify == true) {
			$_SESSION['new_products_id_in_cart'] = $cartItemKey;
		}

		$addQty = $this->get_quantity( $cartItemKey, $attributes ) + $pQty;
		// overflow protection
		if( $addQty > MAX_CART_QUANTITY ) {
			$addQty = MAX_CART_QUANTITY;
		}

		$this->StartTrans();
		if ($this->in_cart($cartItemKey)) {
			$this->updateQuantity( $cartItemKey, $addQty );
		} elseif( $productsId = $this->mDb->GetOne( "SELECT `products_id` FROM " . TABLE_PRODUCTS . " WHERE `products_id`=?", array( (int)zen_get_prid( $cartItemKey ) ) ) ) {
			$selectColumn = $gBitUser->isRegistered() ? 'customers_id' : 'cookie' ;
			$selectValue = $gBitUser->isRegistered() ? $gBitUser->mUserId : session_id();

			if( $gCommerceSystem->getConfig( 'QUANTITY_DECIMALS' ) ) {
				// This is some fractional product crap - hope it still works...
				switch (true) {
					case (strstr($addQty, '.')):
						// remove all trailing zeros after zero
						$addQty = preg_replace('/[0]+$/','',$addQty);
						break;
				}
			}

			$attributesValues = array();
			foreach( $attributes as $attrKey => $attrHash ) {
				$attributesValues[$attrKey] = $attrHash['products_options_values_id'];
			}

			$this->contents[$cartItemKey] = array( 
				'customers_basket_id' => NULL,
				'customers_id' => $gBitUser->mUserId,
				'products_quantity' => $pQty,
				'final_price' => NULL,
				'products_key' => $cartItemKey,
				'products_id' => $productsId,
				'date_added' => date( 'c' ),
				'cookie' => NULL,
				'attributes' => $attributes,
				'attributes_values' => $attributesValues
			);
		}
		$this->cleanup();

		$this->CompleteTrans();
	}
}


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

define( 'MAX_CART_QUANTITY', 9999999 );

class CommerceShoppingCart extends CommerceOrderBase {
	public $cartID, $content_type;

	// Abstract methods implementation
	public function getDelivery() {
		global $gBitCustomer;
		$ret = array();

		if( !empty( $this->delivery ) ) {
			$ret = $this->delivery;
		} elseif( $ret = BitBase::getParameter( $_SESSION, 'sendtohash' ) ) {
			
		} elseif( ($selAddress = $gBitCustomer->getAddress( BitBase::getParameter( $_SESSION, 'sendto', $gBitCustomer->getDefaultAddressId() ) ))
			   || ($selAddress = $gBitCustomer->getAddress( $gBitCustomer->getDefaultAddressId() )) ) {
			foreach( $selAddress as $key => $value ) {
				$ret[str_replace( 'entry_', '', $key)] = $value;
			}
			if( !empty( $_SESSION['cart_zone_id'] ) ) {
				$ret['zone_id'] = (int)$selAddress['entry_zone_id'];
			}
			$_SESSION['country_id'] = NULL;

			// cache for subsequent calls
			$this->delivery = $ret;
		}

		return $ret;
	}

	public function getBilling() {
		global $gBitCustomer;
		$ret = array();

		if( !empty( $this->billing ) ) {
			$ret = $this->billing;
		} elseif( $ret = BitBase::getParameter( $_SESSION, 'billtohash' ) ) {
			
		} elseif( $selAddress = $gBitCustomer->getAddress( BitBase::getParameter( $_SESSION, 'billto', $gBitCustomer->getDefaultAddressId() ) ) ) {
			foreach( $selAddress as $key => $value ) {
				$ret[str_replace( 'entry_', '', $key)] = $value;
			}
			if( !empty( $_SESSION['cart_zone_id'] ) ) {
				$ret['zone_id'] = (int)$selAddress['entry_zone_id'];
			}
			$_SESSION['country_id'] = NULL;

			// cache for subsequent calls
			$this->billing = $ret;
		}

		return $ret;
	}


	function load() {
		global $gBitUser;

		$this->contents = array();

		$whereSql = '';

		$bindVars[] = session_id();
		if( $gBitUser->isRegistered() ) {
			$whereSql = " OR `customers_id` = ?";
			$bindVars[] = $gBitUser->mUserId;
		}

		$query = "SELECT `customers_basket_id` AS `hash_key`, cb.* FROM " . TABLE_CUSTOMERS_BASKET . " cb WHERE `cookie`=? $whereSql ORDER BY cb.`customers_basket_id` DESC";
		if( $products = $this->mDb->getAssoc( $query, $bindVars ) ) {
			foreach( $products as $basketId=>$basketProduct ) {
				$this->contents[$basketProduct['products_key']] = $basketProduct;

				$query = "SELECT cba.`products_options_id`||'='||cba.`products_options_values_id` AS `hash_key`, cba.`products_options_id`, cba.`products_options_values_id`, cba.`products_options_value_text`, cpa.`products_options_values_name`
						  FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " cba
							INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " cpa ON ( cba.products_options_values_id=cpa.products_options_values_id )
							LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " cpo ON( cba.products_options_id=cpo.products_options_id )
						  WHERE cba.`customers_basket_id` = ?
						  ORDER BY cpo.`products_options_sort_order`, cpa.`products_options_sort_order`";
				if( $this->contents[$basketProduct['products_key']]['attributes'] =  $this->mDb->getAssoc( $query, array( $basketId ) ) ) {
					foreach( $this->contents[$basketProduct['products_key']]['attributes'] as $productsOptionsKey=>$attribute ) {
						$this->contents[$basketProduct['products_key']]['attributes_values'][$productsOptionsKey] = $attribute['products_options_values_id'];
					}
				}
			}
		}

		$this->cleanup();
	}

	function reset() {
		global $gBitUser;

		$this->contents = array();
		$this->quantity = NULL;
		$this->total = NULL;
		$this->weight = NULL;
		$this->content_type = false;

		// shipping adjustment
		$this->free_shipping_item = 0;
		$this->free_shipping_price = 0;
		$this->free_shipping_weight = 0;

		$selectColumn = $gBitUser->isRegistered() ? 'customers_id' : 'cookie' ;
		$selectValue = $gBitUser->isRegistered() ? $gBitUser->mUserId : session_id();
		$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE `customers_basket_id` IN (SELECT `customers_basket_id` FROM " . TABLE_CUSTOMERS_BASKET . " WHERE $selectColumn = ?)";
		$this->mDb->query($sql, array( $selectValue ) );
		$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " where `$selectColumn` = ?";
		$this->mDb->query($sql, array( $selectValue ) );

		unset($this->cartID);
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
		} elseif( $exists = $this->mDb->GetOne( "SELECT `products_id` FROM " . TABLE_PRODUCTS . " WHERE `products_id`=?", array( (int)zen_get_prid( $cartItemKey ) ) ) ) {
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
	
			// insert into database
			$sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET . " (`$selectColumn`, `products_key`, `products_id`, `products_quantity`, `date_added`) values ( ?, ?, ?, ?, ? )";
			$this->mDb->query( $sql, array( $selectValue, $cartItemKey, zen_get_prid( $cartItemKey ), $addQty, date('Ymd') ) );
			$basketId = $this->mDb->GetOne( "SELECT MAX(`customers_basket_id`) FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `products_key`=? AND `$selectColumn`=?", array( $cartItemKey, $selectValue ) ); 

			if (is_array($attributes)) {
				reset($attributes);
				foreach( $attributes as $option=>$value ) {
						if (is_array($value) ) {
							reset($value);
							foreach( $value AS $optValId =>$optVal ) {
								$sql = "INSERT INTO  " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (`customers_basket_id`, `products_options_id`, `products_options_key`, `products_options_values_id`, `products_options_value_text`) VALUES ( ?, ?, ?, ?, ? )";
								$this->mDb->query($sql, array( $basketId, (int)$option, $option.'='.$optValId, (int)$optValId, ($optVal!=$optValId ? $optVal : NULL) ) );
							}
						} else {
							// update db insert to include attribute value_text. This is needed for text attributes.
							$sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (`customers_basket_id`, `products_options_id`, `products_options_key`, `products_options_values_id`, `products_options_value_text`) VALUES (?, ?, ?, ?, ?)";
							$bindVars = array( $basketId, (int)$option, $option, (int)$value, (!is_numeric( $value ) ? $value : NULL) );
							$this->mDb->query( $sql, $bindVars );
						}
				}
			}
		}

		$this->CompleteTrans();
		$this->cleanup();

		$this->load();

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
		$this->cartID = $this->generate_cart_id();
	}

	function emptyCart() {
		foreach( array_keys( $this->contents ) as $productsKey ) {
			$this->updateQuantity( $productsKey, 0 );
		}
	}

	////
	// Return a unique product key based on attributes, used for ensuring uniqueness for shopping cart items
	public static function getUniqueCartItemKey( $prid, $params ) {
		if( (is_array($params)) && (!strstr($prid, ':')) ) {
			$uprid = $prid;
			foreach( $params as $option=>$value ) {
				if (is_array($value)) {
					foreach( $value as $opt=>$val ) {
						$uprid .= '{' . $option . '}' . trim($opt).':'.trim($val);
					}
				} else {
					$uprid .= '{' . $option . '}' . trim($value);
				}
			}
			return $prid . ':' . md5($uprid);
		} else {
			return $prid;
		}
	}


	function verifyQuantity( $pProductsKey, $pQty ) {

		$pQty = (int)$pQty;

		if( $product = $this->getProductObject( $pProductsKey ) ) {
			// verify qty to add
			$add_max = $product->getField( 'products_quantity_order_max' );
			$cart_qty = $gBitCustomer->mCart->in_cart_mixed($_REQUEST['products_id']);
			$new_qty = zen_get_buy_now_qty($_REQUEST['products_id']);
			if (($add_max == 1 and $cart_qty == 1)) {
				// do not add
				$new_qty = 0;
			} else {
				// adjust quantity if needed
				if (($new_qty + $cart_qty > $add_max) and $add_max != 0) {
					$new_qty = $add_max - $cart_qty;
				}
			}
			if( !empty( $adjust_max ) && $adjust_max == 'true' ) {
				$messageStack->add_session('header', ERROR_MAXIMUM_QTY . ' - ' . zen_get_products_name($prodId), 'caution');
			}

			if( is_object( $product ) && $pQty > $product->getField( 'products_quantity_order_max' ) ) { 
				// we are trying to add quantity greater than max purchable quantity
				$pQty = $product->getField( 'products_quantity_order_max' );
			}	
		} else {
			// product couldn't load, delete from card
			$pQty = 0;
		}
		return $pQty;
	}

	function verifyCheckout() {
		global $gCommerceSystem;
		foreach( $this->contents AS $cartItemKey => $productsHash ) {
			$product = $this->getProductObject( $cartItemKey );
			$check_quantity = $productsHash['products_quantity'];
			$check_quantity_min = $product->getField( 'products_quantity_order_min' );
			
			// Check quantity min
			if ($new_check_quantity = $this->in_cart_mixed( $cartItemKey ) ) {
				$check_quantity = $new_check_quantity;
			}

			$fix_once = 0;
			if ($check_quantity < $check_quantity_min) {
				$fix_once ++;
				$this->mErrors['checkout'][$cartItemKey] = tra( 'Product: ' ) . $product->getTitle() . tra( ' ... Quantity Units errors - ' ) . tra( 'You ordered a total of: ' ) . $check_quantity	. ' <span class="alertBlack">' . $product->getQuantityMinUnitsDisplay() . '</span> ';
			}

			// Check Quantity Units if not already an error on Quantity Minimum
			if ($fix_once == 0) {
				$check_units = $product->getField( 'products_quantity_order_units' );
				if ( fmod($check_quantity,$check_units) != 0 ) {
					$this->mErrors['checkout'][$cartItemKey] = tra( 'Product: ' ) . $product->getTitle() . tra( ' ... Quantity Units errors - ' ) . tra( 'You ordered a total of: ' ) . $check_quantity	. ' <span class="alertBlack">' . $product->getQuantityMinUnitsDisplay() . '</span> ';
				}
			}

			// Check if the required stock is available. If insufficent stock is available return an out of stock message
			if ( $gCommerceSystem->getConfig( 'STOCK_CHECK' ) && !$gCommerceSystem->getConfig( 'STOCK_ALLOW_CHECKOUT' ) ) {
				if( !$product->getField( 'products_quantity' ) && !$product->getField( 'products_virtual' ) ) {
					if( $gCommerceSystem->getConfig( 'STOCK_ALLOW_CHECKOUT' ) ) {
						$this->mErrors['checkout'][$cartItemKey] = tra( 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock.<br />Items not in stock will be placed on backorder.' );
					} else {
						$this->mErrors['checkout'][$cartItemKey] = tra( 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock or there are not enough in stock to fill your order.<br />Please change the quantity of products marked with (' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '). Thank you' );
					}
				}
			}




		}
		return( empty( $this->mErrors['checkout'] ) );
	}

	function updateQuantity( $pProductsKey, $pQty ) {
		global $gBitUser;

		// overflow protection
		if( $pQty > MAX_CART_QUANTITY ) {
			$pQty = MAX_CART_QUANTITY;
		}

		$selectColumn = $gBitUser->isRegistered() ? 'customers_id' : 'cookie' ;
		$selectValue = $gBitUser->isRegistered() ? $gBitUser->mUserId : session_id();
		if( $basketId = $this->mDb->getOne( "SELECT `customers_basket_id` FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `$selectColumn` = ? AND `products_key` = ?", array( $selectValue, $pProductsKey ) ) ) {
			$pQty = abs( $pQty );
			if( !empty( $pQty ) ) {
				// TODO products *can* take decimal values, and that needs to be handled here
				$this->contents[$pProductsKey]['products_quantity'] = $pQty;
				$sql = "UPDATE " . TABLE_CUSTOMERS_BASKET . " SET `products_quantity` = ?  WHERE `customers_basket_id` = ?";
				$this->mDb->query($sql, array( $pQty, $basketId ) );
			} else {
				// because of foreign key constraints, need to delete attributes first, then the product
				$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where `customers_basket_id`=?";
				$this->mDb->query($sql, array( $basketId ) );
				$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " where `customers_basket_id`=?";
				$this->mDb->query($sql, array( $basketId ) );
				unset( $this->contents[$pProductsKey] );
			}
		}
		$this->calculate( TRUE );
	}

	function cleanup() {
		reset($this->contents);
		foreach( array_keys( $this->contents ) as $key ) {
			if( empty( $this->contents[$key]['products_quantity'] ) || $this->contents[$key]['products_quantity'] <= 0 || !$this->getProductObject( $key ) ) {
				$this->updateQuantity( $key, 0 );
			}
		}
	}

	function count_contents() {	// get total number of items in cart
		$total_items = 0;
		if (is_array($this->contents)) {
			reset($this->contents);
			foreach( array_keys( $this->contents ) AS $k ) {
				$total_items += $this->contents[$k]['products_quantity'];
			}
		}

		return $total_items;
	}

	function get_quantity($pProductsId) {
		$ret = 0;
		$keys = array_keys( $this->contents );
		foreach( $keys AS $k ) {
			if( !strpos( $pProductsId, ':' ) ) {
				$productId = (strpos( $k, ':' ) ? substr( $k, 0, strpos( $k, ':' ) ) : $k);
			} else {
				$productId = $k;
			}
			if( $productId == $pProductsId ) {
				$ret += $this->contents[$k]['products_quantity'];
			}
		}
		return( $ret );
	}

	function in_cart( $pProductsId ) {
		return( $this->get_quantity( $pProductsId ) > 0 );
	}


	function get_product_id_list() {
		$product_id_list = '';
		if (is_array($this->contents)) {
			reset($this->contents);
			foreach( array_keys( $this->contents ) AS $cartItemKey ) {
				$product_id_list .= ', ' . zen_db_input($cartItemKey);
			}
		}

		return substr($product_id_list, 2);
	}

	// calculates totals
	function calculate( $pForceRecalculate=FALSE ) {
		if( is_null( $this->total ) || $pForceRecalculate ) {
			$this->subtotal = 0;
			$this->total = 0;
			$this->weight = 0;
			$this->quantity = 0;

			// shipping adjustment
			$this->free_shipping_item = 0;
			$this->free_shipping_price = 0;
			$this->free_shipping_weight = 0;

			if( !is_array($this->contents) ) {
				 return 0;
			}

			reset($this->contents);
			foreach( array_keys( $this->contents ) as $cartItemKey ) {
				$qty = $this->contents[$cartItemKey]['products_quantity'];
				// $cartItemKey will be unique joined string of products_id:hash for cart, eg: 17054:be19531ba04f4dc3fd33bca49a16dca8 
				$prid = zen_get_prid( $cartItemKey );

				// products price
				$product = $this->getProductObject( $prid );
				// sometimes 0 hash things can get stuck in cart.
				if( $product && $product->isValid() ) {
					// make sure this file is included. weird state had it missing
					require_once(DIR_FS_FUNCTIONS . 'functions_taxes.php');
					$productAttributes = !empty( $this->contents[$cartItemKey]['attributes'] ) ? $this->contents[$cartItemKey]['attributes'] : array();
					$products_tax = zen_get_tax_rate($product->getField('products_tax_class_id'));
					$purchasePrice = $product->getPurchasePrice( $qty, $productAttributes );
					$onetimeCharges = $product->getOneTimeCharges( $qty, $productAttributes );

					$productWeight = $product->getWeight( $qty, $productAttributes );
					$productsTotal = zen_add_tax( (($purchasePrice * $qty) + $onetimeCharges), $products_tax);

					// shipping adjustments
					if (($product->getField('product_is_always_free_ship') == 1) || $product->isVirtual( $this->contents[$cartItemKey] ) || (preg_match('/^GIFT/', addslashes($product->getField('products_model'))))) {
						$this->free_shipping_item += $qty;
						$this->free_shipping_price += $productsTotal;
						$this->free_shipping_weight += $productWeight;
					}

					$this->total += $productsTotal;
					$this->subtotal += $productsTotal;
					$this->weight += $productWeight;
					$this->quantity += $qty;
				}
			}
		}
	}

	public function getProductHash( $pProductsKey ) {
		 global $gBitProduct, $currencies;

		$productHash = array();

		$product = $this->getProductObject( $pProductsKey );
		if( $product && $product->isValid() ) {
			$prid = $product->mProductsId;

			$productHash = $product->mInfo;
			$productHash['attributes'] = $this->contents[$pProductsKey]['attributes'];
			$productHash['attributes_values'] = $this->getParameter( $this->contents[$pProductsKey], 'attributes_values' );
			// this is the stock quantity coming out of mInfo
			unset( $productHash['products_quantity'] );
			$productHash['id'] = $pProductsKey;
			$productHash['name'] = $product->getField('products_name');
			$productHash['purchase_group_id'] = $product->getField('purchase_group_id');
			$productHash['model'] = $product->getField('products_model');
			$productHash['display_url'] = $product->getDisplayUrl();
			$productHash['image'] = $product->getField('products_image');
			$productHash['image_url'] = $product->getImageUrl();
			$productHash['products_quantity'] = (!empty( $this->contents[$pProductsKey]['products_quantity'] ) ? $this->contents[$pProductsKey]['products_quantity'] : NULL);
			$productHash['commission'] = $product->getCommissionUserCharges();
			$productHash['weight'] = $product->getWeight( $productHash['products_quantity'], $productHash['attributes'] );
			$productHash['price'] = $product->getPurchasePrice( $productHash['products_quantity'], $productHash['attributes'] );
			$productHash['tax_rate'] = zen_get_tax_rate( $product->getField( 'products_tax_class_id' ) );
			$productHash['final_price'] = $productHash['price'];
			$productHash['final_price_display'] = $currencies->display_price( $productHash['final_price'] , $productHash['tax_rate'], $productHash['products_quantity'] );
			$productHash['onetime_charges'] = $product->getOneTimeCharges( $productHash['products_quantity'], $productHash['attributes'] );
			$productHash['onetime_charges_display'] = $currencies->display_price($productHash['onetime_charges'], $productHash['tax_rate'], 1);
			$productHash['tax_class_id'] = $product->getField('products_tax_class_id');
			$productHash['tax'] = $product->getField('tax_rate');
			$productHash['tax_description'] = $product->getField('tax_description');
		}

		return $productHash;
	}


	function show_total() {
		$this->calculate();

		return $this->total;
	}

	function show_weight( $pUnit=NULL ) {
		$this->calculate();
		$ret = $this->weight;
		if( strtolower( $pUnit ) == 'kg' ) {
			$ret *= .45359;
		}

		return $ret;
	}

	function generate_cart_id($length = 5) {
		return zen_create_random_value($length, 'digits');
	}

	function get_content_type($gv_only = 'false') {

		$this->content_type = false;
		$gift_voucher = 0;

		if ( $this->count_contents() > 0 ) {
			reset($this->contents);
			foreach( array_keys( $this->contents ) as $cartItemKey ) {
				if( $product = $this->getProductObject( $cartItemKey ) ) {
					if( preg_match( '/^GIFT/', addslashes( $product->getField( 'products_model' ) ) ) ) {
						$gift_voucher += $product->getPurchasePrice( $this->contents[$cartItemKey]['products_quantity'], $this->contents[$cartItemKey]['attributes'] );
					}
					if (isset($this->contents[$cartItemKey]['attributes'])) {
						reset($this->contents[$cartItemKey]['attributes']);
						foreach( $this->contents[$cartItemKey]['attributes'] as $value ) {
							$virtual_check_query = "SELECT COUNT(*) as `total`
													FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
														INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON(pa.`products_options_values_id` = pad.`products_options_values_id`)
													WHERE pa.`products_options_values_id` = ?";

							$virtualCount = $this->mDb->getOne( $virtual_check_query, array( (int)$value ) );

							if ($virtualCount > 0) {
								switch ($this->content_type) {
									case 'physical':
										$this->content_type = 'mixed';
											if ($gv_only == 'true') {
												return $gift_voucher;
											} else {
												return $this->content_type;
											}
										break;
									default:
										$this->content_type = 'virtual';
										break;
								}
							} else {
								switch ($this->content_type) {
									case 'virtual':
										if ($product->getField( 'products_virtual' ) == '1') {
											$this->content_type = 'virtual';
										} else {
											$this->content_type = 'mixed';
											if ($gv_only == 'true') {
												return $gift_voucher;
											} else {
												return $this->content_type;
											}
										}
										break;
									case 'physical':
										if ($product->getField( 'products_virtual' ) == '1') {
											$this->content_type = 'mixed';
											if ($gv_only == 'true') {
												return $gift_voucher;
											} else {
												return $this->content_type;
											}
										} else {
											$this->content_type = 'physical';
										}
										break;
									default:
										if ($product->getField( 'products_virtual' ) == '1') {
											$this->content_type = 'virtual';
										} else {
											$this->content_type = 'physical';
										}
								}
							}
						}
					} else {
						switch ($this->content_type) {
							case 'virtual':
								if ($product->getField( 'products_virtual' ) == '1') {
									$this->content_type = 'virtual';
								} else {
									$this->content_type = 'mixed';
									if ($gv_only == 'true') {
										return $gift_voucher;
									} else {
										return $this->content_type;
									}
								}
								break;
							case 'physical':
								if ($product->getField( 'products_virtual' ) == '1') {
									$this->content_type = 'mixed';
									if ($gv_only == 'true') {
										return $gift_voucher;
									} else {
										return $this->content_type;
									}
								 } else {
									$this->content_type = 'physical';
								 }
								break;
							default:
								if( $product->getField( 'products_virtual' ) == '1') {
									$this->content_type = 'virtual';
								 } else {
									$this->content_type = 'physical';
								 }
						}
					}
				}
			}
		} else {
			$this->content_type = 'physical';
		}

		if ($gv_only == 'true') {
			return $gift_voucher;
		} else {
			return $this->content_type;
		}
	}

	function __sleep() {
		unset( $this->mProductObjects );
		return array( 'contents', 'total', 'weight', 'content_type' );
	}

	function unserialize($broken) {
		for(reset($broken);$kv=each($broken);) {
			$key=$kv['key'];
			if (gettype($this->$key)!="user function")
			$this->$key=$kv['value'];
		}
	}

	// check mixed min/units
	function in_cart_mixed($pProductsKey) {
		// if nothing is in cart return 0
		if (!is_array($this->contents)) return 0;

		if( is_array( $pProductsKey ) ) {
			$pProductsKey = current( $pProductsKey );
		}
		// check if mixed is on
		$productQtyMixed = $this->mDb->GetOne("select `products_quantity_mixed` from " . TABLE_PRODUCTS .  " where `products_id` ='" .	zen_get_prid( $pProductsKey ) . "'");

		// if mixed attributes is off return qty for current attribute selection
		if( $productQtyMixed == '0' ) {
			return $this->get_quantity($pProductsKey);
		}

		// compute total quantity regardless of attributes
		$in_cart_mixed_qty = 0;
		$chk_products_id= zen_get_prid($pProductsKey);

		// reset($this->contents); // breaks cart
		$check_contents = $this->contents;
		foreach( array_keys( $check_contents ) AS $pProductsKey ) {
			$test_id = zen_get_prid($pProductsKey);
			if ($test_id == $chk_products_id) {
				$in_cart_mixed_qty += $check_contents[$pProductsKey]['products_quantity'];
			}
		}
		return $in_cart_mixed_qty;
	}

	// check mixed discount_quantity
	function in_cart_mixed_discount_quantity( $pProductsId ) {
		// if nothing is in cart return 0
		$ret = 0;

		if( is_array( $this->contents ) ) {
			// check if mixed is on
			$chk_products_id= zen_get_prid( $pProductsId );
			if( $hasMixedQuantity = $this->mDb->getOne("select `products_mixed_discount_qty` from " . TABLE_PRODUCTS . " where `products_id` =?", array( zen_get_prid( $chk_products_id ) ) ) ) {
				// compute total quantity regardless of attributes
				// reset($this->contents); // breaks cart
				$check_contents = $this->contents;
				foreach( array_keys( $check_contents ) as $products_key ) {
					$test_id = zen_get_prid($products_key);
					if ($test_id == $chk_products_id) {
						$ret += $check_contents[$products_key]['products_quantity'];
					}
				}
			} else {
				$ret = $this->get_quantity( $pProductsId );
			}
		}
				
		return $ret;
	}

	// $check_what is the fieldname example: 'products_is_free'
	// $check_value is the value being tested for - default is 1
	// Syntax: $gBitCustomer->mCart->in_cart_check('product_is_free','1');
	function in_cart_check($check_what, $check_value='1') {
		// if nothing is in cart return 0
		if (!is_array($this->contents)) return 0;

		// compute total quantity for field
		$in_cart_check_qty=0;

		reset($this->contents);
		foreach( array_keys( $this->contents ) AS $cartItemKey ) {
			$testing_id = zen_get_prid($cartItemKey);
			// check if field it true
			$product_check = $this->mDb->getOne("select " . $check_what . " as `check_it` from " . TABLE_PRODUCTS .  " where `products_id` = ?" , array( $testing_id ) );
			if( $product_check == $check_value ) {
				$in_cart_check_qty += $this->contents[$cartItemKey]['products_quantity'];
			}
		}
		return $in_cart_check_qty;
	}

	// gift voucher only
	function gv_only() {
		$gift_voucher = $this->get_content_type(true);
		return $gift_voucher;
	}

}
?>

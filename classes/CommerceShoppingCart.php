<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce											
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers								
// |																		
// | http://www.zen-cart.com/index.php										
// |																		
// | Portions Copyright (c) 2003 osCommerce									
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			
// | that is bundled with this package in the file LICENSE, and is			
// | available through the world-wide-web at the following url:				
// | http://www.zen-cart.com/license/2_0.txt.								
// | If you did not receive a copy of the zen-cart license and are unable 
// | to obtain it through the world-wide-web, please send a note to			
// | license@zen-cart.com so we can mail you a copy immediately.			
// +----------------------------------------------------------------------+
// $Id: CommerceShoppingCart.php,v 1.6 2009/08/20 19:02:49 spiderr Exp $
//

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceOrderBase.php' );

class CommerceShoppingCart extends CommerceOrderBase {
	var $cartID, $content_type;

	function CommerceShoppingCart() {
		parent::CommerceOrderBase();
		$this->reset();
	}

	function load() {
		global $gBitUser;

		$this->contents = array();

		$bindVars[] = session_id();
		if( $gBitUser->isRegistered() ) {
			$whereSql = " OR `customers_id` = ?";
			$bindVars[] = $gBitUser->mUserId;
		}

		$query = "SELECT `customers_basket_id` AS `hash_key`, cb.* FROM " . TABLE_CUSTOMERS_BASKET . " cb WHERE `cookie`=? $whereSql";
		if( $products = $this->mDb->getAssoc( $query, $bindVars ) ) {
			foreach( $products as $basketId=>$basketProduct ) {
				$this->contents[$basketProduct['products_key']] = $basketProduct;
				$this->contents[$basketProduct['products_key']]['quantity'] = $basketProduct['customers_basket_quantity'];

				$query = "SELECT `products_options_key` AS `hash_key`, cba.`products_options_id`, cba.`products_options_values_id`, `products_options_value_text`
						  FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " cba
							INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " cpo ON( cba.products_options_id=cpo.products_options_id )
							INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " cpa ON ( cba.products_options_values_id=cpa.products_options_values_id )
						  WHERE cba.`customers_basket_id` = ?
						  ORDER BY cpo.`products_options_sort_order`, cpa.`products_options_sort_order`";
				if( $attributes = $this->mDb->getAssoc( $query, array( $basketId ) ) ) {

				foreach( $attributes as $productsOptionsKey=>$attribute )
					$this->contents[$basketProduct['products_key']]['attributes'][$productsOptionsKey] = $attribute['products_options_values_id'];
					//CLR 020606 if text attribute, then set additional information
					if ($attribute['products_options_values_id'] == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
						$this->contents[$basketProduct['products_key']]['attributes_values'][$productsOptionsKey] = $attribute['products_options_value_text'];
					}
				}
			}
		}

		$this->cleanup();
	}

	function reset($reset_database = false) {
		global $gBitUser;

		$this->contents = array();
		$this->total = NULL;
		$this->weight = NULL;
		$this->content_type = false;

		// shipping adjustment
		$this->free_shipping_item = 0;
		$this->free_shipping_price = 0;
		$this->free_shipping_weight = 0;

		if( $gBitUser->isRegistered() && ($reset_database == true)) {
bt(); die;
			$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " where `customers_id` = ?";
			$this->mDb->query($sql, array( $gBitUser->mUserId ) );

			$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where `customers_id` = ?";
			$this->mDb->query($sql, array( $gBitUser->mUserId ) );
		}

		unset($this->cartID);
	}

	function addToCart($pProductsKey, $pQty = '1', $attributes = '', $notify = true) {
		global $gBitUser, $gCommerceSystem;
		$productsKey = zen_get_uprid($pProductsKey, $attributes);
		if ($notify == true) {
			$_SESSION['new_products_id_in_cart'] = $productsKey;
		}

		$this->mDb->StartTrans();

		if ($this->in_cart($productsKey)) {
			$this->updateQuantity( $productsKey, $pQty );
		} else {
			$selectColumn = $gBitUser->isRegistered() ? 'customers_id' : 'cookie' ;
			$selectValue = $gBitUser->isRegistered() ? $gBitUser->mUserId : session_id();

			if( $gCommerceSystem->getConfig( 'QUANTITY_DECIMALS' ) ) {
				// This is some fractional product crap - hope it still works...
				switch (true) {
					case (strstr($pQty, '.')):
						// remove all trailing zeros after zero
						$pQty = preg_replace('/[0]+$/','',$pQty);
						break;
				}
			}

			// insert into database
			$sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET . " (`$selectColumn`, `products_key`, `products_id`, `customers_basket_quantity`, `date_added`) values ( ?, ?, ?, ?, ? )";
			$this->mDb->query( $sql, array( $selectValue, $productsKey, zen_get_prid( $productsKey ), $pQty, date('Ymd') ) );
			$basketId = $this->mDb->GetOne( "SELECT MAX(`customers_basket_id`) FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `products_key`=? AND `$selectColumn`=?", array( $productsKey, $selectValue ) ); 

			if (is_array($attributes)) {
				reset($attributes);
				foreach( $attributes as $option=>$value ) {
					// check if input was from text box.	If so, store additional attribute information
					// check if text input is blank, if so do not add to attribute lists
					$attr_value = NULL;
					$blank_value = FALSE;
					if (strstr($option, TEXT_PREFIX)) {
						if (trim($value) == NULL) {
							$blank_value = TRUE;
						} else {
							$option = substr($option, strlen(TEXT_PREFIX));
							$attr_value = stripslashes($value);
							$value = PRODUCTS_OPTIONS_VALUES_TEXT_ID;
						}
					}

					if (!$blank_value) {
						if (is_array($value) ) {
							reset($value);
							while (list($opt, $val) = each($value)) {
								$sql = "INSERT INTO  " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . "
										(`customers_basket_id`, `products_key`, `products_id`, `products_options_id`, `products_options_key`, `products_options_values_id`)
										VALUES ( ?, ?, ?, ?, ? )";
								$this->mDb->query($sql, array( $basketId, $productsKey, zen_get_prid( $productsKey ), (int)$option, (int)$option.'_chk'.$val, $val ) );
							}
						} else {
							// update db insert to include attribute value_text. This is needed for text attributes.
							$sql = "INSERT INTO " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (`customers_basket_id`, `products_options_id`, `products_options_key`, `products_options_values_id`, `products_options_value_text`) VALUES (?, ?, ?, ?, ?)";
							$this->mDb->query( $sql, array( $basketId, (int)$option, $option, $value, $attr_value ) );
						}
					}
				}
			}
		}
		$this->cleanup();
		$this->mDb->CompleteTrans();

		$this->load();

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
		$this->cartID = $this->generate_cart_id();
	}

	function verifyQuantity( $pProductsKey, $pQty ) {

		$pQty = (int)$pQty;

		// verify qty to add
		$add_max = zen_get_products_quantity_order_max($_REQUEST['products_id']);
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

		if( $product = $this->getProductObject( $pProductsKey ) ) {
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
		foreach( $this->contents AS $productsKey => $productsHash ) {
			$product = $this->getProductObject( $productsKey );
			$check_quantity = $productsHash['quantity'];
			$check_quantity_min = $product->getField( 'products_quantity_order_min' );
			// Check quantity min
			if ($new_check_quantity = $this->in_cart_mixed($prid) ) {
				$check_quantity = $new_check_quantity;
			}

			$fix_once = 0;
			if ($check_quantity < $check_quantity_min) {
				$fix_once ++;
				$this->mErrors['checkout'][$productsKey] = tra( 'Product: ' ) . $product->getTitle() . tra( ' ... Quantity Units errors - ' ) . tra( 'You ordered a total of: ' ) . $check_quantity	. ' <span class="alertBlack">' . zen_get_products_quantity_min_units_display((int)$prid, false, true) . '</span> ';
			}

			// Check Quantity Units if not already an error on Quantity Minimum
			if ($fix_once == 0) {
				$check_units = $product->getField( 'products_quantity_order_units' );
				if ( fmod($check_quantity,$check_units) != 0 ) {
					$this->mErrors['checkout'][$productsKey] = tra( 'Product: ' ) . $product->getTitle() . tra( ' ... Quantity Units errors - ' ) . tra( 'You ordered a total of: ' ) . $check_quantity	. ' <span class="alertBlack">' . zen_get_products_quantity_min_units_display((int)$prid, false, true) . '</span> ';
				}
			}

			// Check if the required stock is available. If insufficent stock is available return an out of stock message
			if ( $gCommerceSystem->getConfig( 'STOCK_CHECK' ) && !$gCommerceSystem->getConfig( 'STOCK_ALLOW_CHECKOUT' ) ) {
				foreach( $this->contents AS $productsKey => $productsHash ) {
					$product = $this->getProductObject( $productsKey );
					if( !$product->getField( 'products_quantity' ) && !$product->getField( 'products_virtual' ) ) {
						if( $gCommerceSystem->getConfig( 'STOCK_ALLOW_CHECKOUT' ) ) {
							$this->mErrors['checkout'][$productsKey] = tra( 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock.<br />Items not in stock will be placed on backorder.' );
						} else {
							$this->mErrors['checkout'][$productsKey] = tra( 'Products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' are out of stock or there are not enough in stock to fill your order.<br />Please change the quantity of products marked with (' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '). Thank you' );
						}
					}
				}
			}




		}
		return( empty( $this->mErrors['checkout'] ) );
	}

	function updateQuantity( $pProductsKey, $pQty ) {
		global $gBitUser;

		$selectColumn = $gBitUser->isRegistered() ? 'customers_id' : 'cookie' ;
		$selectValue = $gBitUser->isRegistered() ? $gBitUser->mUserId : session_id();
		if( $basketId = $this->mDb->getOne( "SELECT `customers_basket_id` FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `$selectColumn` = ? AND `products_key` = ?", array( $selectValue, $pProductsKey ) ) ) {
			if( !empty( $pQty ) ) {
				$this->contents[$pProductsKey]['quantity'] = $pQty;
				$sql = "UPDATE " . TABLE_CUSTOMERS_BASKET . " SET `customers_basket_quantity` = ?  WHERE `customers_basket_id` = ?";
				$this->mDb->query($sql, array( (int)$pQty, $basketId ) );
			} else {
bt(); die;
				// because of foreign key constraints, need to delete attributes first, then the product
				$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where `customers_basket_id=?";
				$this->mDb->query($sql, array( $basketId ) );
				$sql = "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " where `customers_basket_id`=?";
				$this->mDb->query($sql, array( $basketId ) );
				unset( $this->contents[$key] );
			}
		}
	}

	function cleanup() {
		reset($this->contents);
		foreach( array_keys( $this->contents ) as $key ) {
			if( empty( $this->contents[$key]['quantity'] ) || $this->contents[$key]['quantity'] <= 0 || !$this->getProductObject( $key ) ) {
				$this->updateQuantity( $key, 0 );
			}
		}
	}

	function count_contents() {	// get total number of items in cart
		$total_items = 0;
		if (is_array($this->contents)) {
			reset($this->contents);
			while (list($productsKey, ) = each($this->contents)) {
				$total_items += $this->get_quantity($productsKey);
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
				$ret += $this->contents[$k]['quantity'];
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
			while (list($productsKey, ) = each($this->contents)) {
				$product_id_list .= ', ' . zen_db_input($productsKey);
			}
		}

		return substr($product_id_list, 2);
	}
/* use base class method
	// calculates totals
	function calculate( $pForceRecalculate=FALSE ) {
		if( is_null( $this->total ) || $pForceRecalculate ) {
			$this->total = 0;
			$this->weight = 0;

			// shipping adjustment
			$this->free_shipping_item = 0;
			$this->free_shipping_price = 0;
			$this->free_shipping_weight = 0;

			if (!is_array($this->contents)) return 0;

			reset($this->contents);
			foreach( array_keys( $this->contents ) as $productsKey ) {
				$qty = $this->contents[$productsKey]['quantity'];

				// products price
				$product = $this->getProductObject( $productsKey );
				// sometimes 0 hash things can get stuck in cart.
				if( $product && $product->isValid() ) {
					$products_tax = zen_get_tax_rate($product->getField('products_tax_class_id'));
					$products_price = $product->getPurchasePrice( $qty, $this->contents[$productsKey]['attributes'] );

					// shipping adjustments
					if (($product->getField('product_is_always_free_ship') == 1) or ($product->getField('products_virtual') == 1) or (ereg('^GIFT', addslashes($product->getField('products_model'))))) {
						$this->free_shipping_item += $qty;
						$this->free_shipping_price += zen_add_tax($products_price, $products_tax) * $qty;
						$this->free_shipping_weight += $product->getWeight( $qty, $this->contents[$productsKey]['attributes'] );
					}

					$this->total += zen_add_tax($products_price, $products_tax) * $qty;
					$this->weight += $product->getWeight( $qty, $this->contents[$productsKey]['attributes'] );
				}
			}
		}
	}
*/

	// can take a productsKey or a straight productsId
	function getProductObject( $pProductsMixed ) {
		$productsId = zen_get_prid( $pProductsMixed );
		if( BitBase::verifyId( $productsId ) ) {
			if( !isset( $this->mProductObjects[$productsId] ) ) {
				$this->mProductObjects[$productsId] = bc_get_commerce_product( zen_get_prid( $productsId ) );
				if( $this->mProductObjects[$productsId]->load() ) {
					$ret = &$this->mProductObjects[$productsId];
				}
			}
		}
		return $this->mProductObjects[$productsId];
	}

	function getProductHash( $pProductsKey = false ) {
		 global $gBitProduct, $currencies;

		$product = $this->getProductObject( $pProductsKey );
		if( $product && $product->isValid() ) {
			$prid = $product->mProductsId;

			$productHash =$product->mInfo;
			$productHash['id'] = $pProductsKey;
			$productHash['name'] = $product->getField('products_name');
			$productHash['purchase_group_id'] = $product->getField('purchase_group_id');
			$productHash['model'] = $product->getField('products_model');
			$productHash['image'] = $product->getField('products_image');
			$productHash['image_url'] = $product->getField('products_image_url');
			$productHash['customers_basket_quantity'] = $this->contents[$pProductsKey]['customers_basket_quantity'];
			$productHash['commission'] = $product->getCommissionUserCharges();
			$productHash['weight'] = $product->getWeight( $productHash['customers_basket_quantity'], $this->contents[$pProductsKey]['attributes'] );
			$productHash['price'] = $product->getPurchasePrice( $productHash['customers_basket_quantity'], $this->contents[$pProductsKey]['attributes'] );
			$productHash['final_price'] = $productHash['price'];
			$productHash['final_price_display'] = $currencies->display_price( $productHash['final_price'] , zen_get_tax_rate($productHash['tax_class_id']), $productHash['customers_basket_quantity'] );
			$productHash['onetime_charges'] = $product->getOneTimeCharges( $productHash['customers_basket_quantity'], $this->contents[$pProductsKey]['attributes'] );
			$productHash['onetime_charges_display'] = $currencies->display_price($productHash['onetime_charges'], zen_get_tax_rate($productHash['tax_class_id']), 1);
			$productHash['tax_class_id'] = $product->getField('products_tax_class_id');
			$productHash['tax'] = $product->getField('tax_rate');
			$productHash['tax_description'] = $product->getField('tax_description');
			$productHash['attributes'] = (isset( $productsHash['attributes'] ) ? $productsHash['attributes'] : '');
			$productHash['attributes_values'] = (isset( $productsHash['attributes_values'] ) ? $productsHash['attributes_values'] : '');
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

//			if ( (DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0) ) {
		if ( $this->count_contents() > 0 ) {
			reset($this->contents);
			while (list($productsKey, ) = each($this->contents)) {
				if( $free_ship_check && ereg( '^GIFT', addslashes($free_ship_check->fields['products_model'] ) ) ) {
					if( $product = $this->getProductObject( $productsKey ) ) {
						$gift_voucher += $product->getPurchasePrice( $this->contents[$productsKey]['quantity'], $this->contents[$productsKey]['attributes'] );
					}
				}
				if (isset($this->contents[$productsKey]['attributes'])) {
					reset($this->contents[$productsKey]['attributes']);
					while (list(, $value) = each($this->contents[$productsKey]['attributes'])) {
						$virtual_check_query = "SELECT COUNT(*) as `total`
												FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
													INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON(pa.`products_attributes_id` = pad.`products_attributes_id`)
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
									if ($free_ship_check->fields['products_virtual'] == '1') {
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
									if ($free_ship_check->fields['products_virtual'] == '1') {
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
									if ($free_ship_check->fields['products_virtual'] == '1') {
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
							if ($free_ship_check->fields['products_virtual'] == '1') {
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
							if ($free_ship_check->fields['products_virtual'] == '1') {
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
							if( $free_ship_check && $free_ship_check->fields['products_virtual'] == '1') {
								$this->content_type = 'virtual';
							 } else {
								$this->content_type = 'physical';
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
		return array( 'contents', 'total', 'weight', 'content_type', 'free_shipping_item', 'free_shipping_weight', 'free_shipping_price' );
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
		while (list($pProductsKey, ) = each($check_contents)) {
			$test_id = zen_get_prid($pProductsKey);
			if ($test_id == $chk_products_id) {
				$in_cart_mixed_qty += $check_contents[$pProductsKey]['quantity'];
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
						$ret += $check_contents[$products_key]['quantity'];
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
		while (list($productsKey, ) = each($this->contents)) {
			$testing_id = zen_get_prid($productsKey);
			// check if field it true
			$product_check = $this->mDb->getOne("select " . $check_what . " as `check_it` from " . TABLE_PRODUCTS .  " where `products_id` = ?" , array( $testing_id ) );
			if( $product_check == $check_value ) {
				$in_cart_check_qty += $this->contents[$productsKey]['quantity'];
			}
		}
		return $in_cart_check_qty;
	}

	// gift voucher only
	function gv_only() {
		$gift_voucher = $this->get_content_type(true);
		return $gift_voucher;
	}

	// shipping adjustment
	function free_shipping_items() {
		$this->calculate();

		return $this->free_shipping_item;
	}

	function free_shipping_prices() {
		$this->calculate();

		return $this->free_shipping_price;
	}

	function free_shipping_weight() {
		$this->calculate();

		return $this->free_shipping_weight;
	}

}
?>

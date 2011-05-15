<?php
//
// +------------------------------------------------------------------------+
// |zen-cart Open Source E-commerce											|
// +------------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers								|
// |																		|
// | http://www.zen-cart.com/index.php										|
// |																		|
// | Portions Copyright (c) 2003 osCommerce									|
// +------------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			|
// | that is bundled with this package in the file LICENSE, and is			|
// | available through the world-wide-web at the following url:				|
// | http://www.zen-cart.com/license/2_0.txt.								|
// | If you did not receive a copy of the zen-cart license and are unable 	|
// | to obtain it through the world-wide-web, please send a note to			|
// | license@zen-cart.com so we can mail you a copy immediately.			|
// +------------------------------------------------------------------------+
// $Id$
//

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceOrderBase.php' );

class order extends CommerceOrderBase {
	var $mOrdersId;
	var $info, $totals, $customer, $delivery, $content_type, $email_low_stock, $products_ordered_attributes,
			$products_ordered, $products_ordered_email;

	function order($order_id = '') {
		parent::CommerceOrderBase();
		$this->mOrdersId = $order_id;
		$this->initOrder();

		if (zen_not_null($order_id)) {
			$this->load($order_id);
		} else {
			$this->cart();
		}
	}

	function initOrder() {
		$this->info = array();
		$this->totals = array();
		$this->subtotal = 0;
		$this->contents = array();
		$this->customer = array();
		$this->delivery = array();
	}

	function getField( $pField ) {
		$ret = (isset( $this->info[$pField] ) ? $this->info[$pField] : NULL );
		return $ret;
	}

	function getModuleTotal( $pClass, $pKey ) {
		$ret = '';
		for( $i = 0; $i < count( $this->totals ); $i++ ) {
			if( $this->totals[$i]['class'] == $pClass && !empty( $this->totals[$i][$pKey] ) ) {
				$ret = $this->totals[$i][$pKey];
			}
		}
		return $ret;
	}

	function getList( $pListHash ) {
		global $gBitDb, $gBitSystem;
		$bindVars = array();
		$ret = array();
		$selectSql = ''; $joinSql = ''; $whereSql = '';

		$comparison = (!empty( $pListHash['orders_status_comparison'] ) && strlen( $pListHash['orders_status_comparison'] ) <= 2) ? $pListHash['orders_status_comparison'] : '=';

		if( !empty( $pListHash['user_id'] ) ) {
			$whereSql .= ' AND `customers_id`=? ';
			$bindVars[] = $pListHash['user_id'];
		}

		if( isset( $pListHash['orders_status_id'] ) ) {
			$whereSql .= ' AND `orders_status`'.$comparison.'? ';
			$bindVars[] = $pListHash['orders_status_id'];
		}

		if( empty( $pListHash['sort_mode'] ) ) {
			$pListHash['sort_mode'] = 'co.orders_id_desc';
		}
		if( empty( $pListHash['max_records'] ) ) {
			$pListHash['max_records'] = -1;
		}

		if( !empty( $pListHash['search'] ) ) {
			if( !empty( $pListHash['search_scope'] ) && $pListHash['search_scope'] == 'history' ) {
				$joinSql .= " INNER JOIN " . TABLE_ORDERS_STATUS_HISTORY . " osh ON(osh.`orders_id`=co.`orders_id`) ";
				$whereSql .= " AND LOWER(osh.`text`) LIKE ? ";
				$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
			} else {
				$whereSql .= " AND ( ";
				$whereSql .= " LOWER(`delivery_name`) like ? OR ";
				$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
				$whereSql .= " LOWER(`billing_name`) like ? OR ";
				$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
				$whereSql .= " LOWER(uu.`email`) like ? OR ";
				$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
				if( is_numeric( $pListHash['search'] ) ) {
					$whereSql .= " `order_total` = ? OR ";
					$bindVars[] = $pListHash['search'];
				}
				if( is_numeric( $pListHash['search'] ) ) {
					if( strpos( $pListHash['search'], '.' ) === FALSE ) {
						$whereSql .= " co.`orders_id` = ? OR ";
						$bindVars[] = $pListHash['search'];
					}
					$whereSql .= " co.`order_total` = ? OR ";
					$bindVars[] = $pListHash['search'];
				}
				$whereSql .= " LOWER(uu.`real_name`) like ? ";
				$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
				$whereSql .= " ) ";
			}
		}

		if( !empty( $pListHash['products_options_values_id'] ) ) {
			$joinSql .= " INNER JOIN " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa ON(opa.`orders_id`=co.`orders_id`) ";
			$whereSql .= ' AND opa.`products_options_values_id` = ?';
			$bindVars[] = $pListHash['products_options_values_id'];
		}

		if( !empty( $pListHash['interests_id'] ) ) {
			$joinSql .= " INNER JOIN " . TABLE_CUSTOMERS_INTERESTS_MAP . " cim ON(cim.`customers_id`=co.`customers_id`)
						INNER JOIN " . TABLE_CUSTOMERS_INTERESTS . " ci ON(ci.`interests_id`=cim.`interests_id`) ";
			$whereSql .= ' AND cim.`interests_id` = ?';
			$bindVars[] = $pListHash['interests_id'];
		}

		if( !empty( $pListHash['period'] ) && !empty( $pListHash['timeframe'] ) ) {
			$whereSql .= ' AND '.$gBitDb->mDb->SQLDate( $pListHash['period'], '`date_purchased`' ).' = ?';
			$bindVars[] = $pListHash['timeframe'];
		}

		if( $gBitSystem->isPackageActive( 'stats' ) ) {
			$selectSql .= " , sru.`referer_url` ";
			$joinSql .= " LEFT JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`user_id`=uu.`user_id`) 
						  LEFT JOIN `".BIT_DB_PREFIX."stats_referer_urls` sru ON (sru.`referer_url_id`=srum.`referer_url_id`) ";
		}

		$query = "SELECT co.`orders_id` AS `hash_key`, ot.`text` AS `order_total`, co.*, uu.*, os.*, ".$gBitDb->mDb->SQLDate( 'Y-m-d H:i', 'co.`date_purchased`' )." AS `purchase_time` $selectSql
					FROM " . TABLE_ORDERS . " co
						INNER JOIN " . TABLE_ORDERS_STATUS . " os ON(co.`orders_status`=os.`orders_status_id`)
						INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(co.`customers_id`=uu.`user_id`)
					$joinSql
						LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot on (co.`orders_id` = ot.`orders_id`)
					WHERE `class` = 'ot_total' $whereSql
					ORDER BY ".$gBitDb->convertSortmode( $pListHash['sort_mode'] );
		if( $rs = $gBitDb->query( $query, $bindVars, $pListHash['max_records'] ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[$row['orders_id']] = $row;
				if( !empty( $pListHash['recent_comment'] ) ) {
					$ret[$row['orders_id']]['comments'] = $gBitDb->getOne( "SELECT `comments` FROM " . TABLE_ORDERS_STATUS_HISTORY . " osh WHERE osh.`orders_id`=? AND `comments` IS NOT NULL ORDER BY `orders_status_history_id` DESC", array( $row['orders_id'] ) );
				}
				if( !empty( $pListHash['orders_products'] ) ) {
					$sql = "SELECT cop.`orders_products_id` AS `hash_key`, cop.*, cp.*
							FROM " . TABLE_ORDERS_PRODUCTS . " cop 
								INNER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`)
							WHERE cop.`orders_id`=?";
					$ret[$row['orders_id']]['products'] = $gBitDb->getAssoc( $sql, array( $row['orders_id'] ) );
				}
			}
		}

		return( $ret );
	}

	function load($order_id) {
		global $gBitDb, $gBitSystem;

		$selectSql = '';
		$joinSql = '';

		$order_id = zen_db_prepare_input($order_id);

		if( $gBitSystem->isPackageActive( 'stats' ) ) {
			$selectSql .= " , sru.`referer_url` ";
			$joinSql .= " LEFT JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`user_id`=uu.`user_id`) 
						  LEFT JOIN `".BIT_DB_PREFIX."stats_referer_urls` sru ON (sru.`referer_url_id`=srum.`referer_url_id`) ";
		}

		$order_query = "SELECT co.*, uu.*, cpccl.* $selectSql
						FROM " . TABLE_ORDERS . " co 
							INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(uu.`user_id`=co.`customers_id`) 
							$joinSql
							LEFT JOIN `com_pubs_credit_card_log` cpccl ON(cpccl.`orders_id`=co.`orders_id`) 
						WHERE co.`orders_id` = ?";
		$order = $gBitDb->query( $order_query, array( $order_id ) );

		$totals_query = "select `title`, `text`, `class`, `orders_value` from " . TABLE_ORDERS_TOTAL . " where `orders_id` = '" . (int)$order_id . "' order by `sort_order`";
		$totals = $gBitDb->Execute($totals_query);

		while (!$totals->EOF) {
			$this->totals[] = array('title' => $totals->fields['title'],
									'text' => $totals->fields['text'],
									'class' => $totals->fields['class'],
									'orders_value' => $totals->fields['orders_value']);
			$totals->MoveNext();
		}

		$order_total_query = "select `text`, `orders_value` from " . TABLE_ORDERS_TOTAL . " where `orders_id` = '" . (int)$order_id . "' and class = 'ot_total'";
		$order_total = $gBitDb->Execute($order_total_query);

		$shipping_method_query = "select 'title', `orders_value` AS `shipping_total` FROM " . TABLE_ORDERS_TOTAL . " WHERE `orders_id` =	? AND class = 'ot_shipping'";
		$shippingInfo = $gBitDb->getRow($shipping_method_query, array( (int)$order_id ) );

		$order_status_query = "select `orders_status_name` from " . TABLE_ORDERS_STATUS . " where `orders_status_id` = ? AND `language_id` = ?";
		$order_status = $gBitDb->query( $order_status_query, array( $order->fields['orders_status'], $_SESSION['languages_id'] ) );

		$this->info = array('currency' => $order->fields['currency'],
							'currency_value' => $order->fields['currency_value'],
							'payment_method' => $order->fields['payment_method'],
							'payment_module_code' => $order->fields['payment_module_code'],
							'shipping_method' => $order->fields['shipping_method'],
							'shipping_method_code' => $order->fields['shipping_method_code'],
							'shipping_module_code' => $order->fields['shipping_module_code'],
							'shipping_total' => $shippingInfo['shipping_total'],
							'coupon_code' => $order->fields['coupon_code'],
							'cc_type' => $order->fields['cc_type'],
							'cc_owner' => $order->fields['cc_owner'],
							'cc_number' => $order->fields['cc_number'],
							'cc_expires' => $order->fields['cc_expires'],
							'cc_ref_id' => $order->fields['ref_id'],
							'date_purchased' => $order->fields['date_purchased'],
							'orders_status_id' => $order->fields['orders_status'],
							'orders_status' => $order_status->fields['orders_status_name'],
							'last_modified' => $order->fields['last_modified'],
							'total' => $order->fields['order_total'],
							'tax' => $order->fields['order_tax'],
							'ip_address' => $order->fields['ip_address']
							);

		$this->customer = array('id' => $order->fields['customers_id'],
								'user_id' => $order->fields['user_id'],
								'name' => $order->fields['customers_name'],
								'real_name' => $order->fields['real_name'],
								'login' => $order->fields['login'],
								'company' => $order->fields['customers_company'],
								'street_address' => $order->fields['customers_street_address'],
								'suburb' => $order->fields['customers_suburb'],
								'city' => $order->fields['customers_city'],
								'postcode' => $order->fields['customers_postcode'],
								'state' => $order->fields['customers_state'],
								'country' => $order->fields['customers_country'],
								'format_id' => $order->fields['customers_address_format_id'],
								'telephone' => $order->fields['customers_telephone'],
								'email_address' => $order->fields['email']); // 'email' comes from users_users, which is always most current

		if( !empty( $order->fields['referer_url'] ) ) {
			$this->customer['referer_url'] = $order->fields['referer_url'];
		}

		$this->delivery = array('name' => $order->fields['delivery_name'],
								'company' => $order->fields['delivery_company'],
								'street_address' => $order->fields['delivery_street_address'],
								'suburb' => $order->fields['delivery_suburb'],
								'city' => $order->fields['delivery_city'],
								'postcode' => $order->fields['delivery_postcode'],
								'state' => $order->fields['delivery_state'],
								'country' => zen_get_countries( $order->fields['delivery_country'], TRUE ),
								'zone_id' => zen_get_zone_id( $order->fields['delivery_country'], $order->fields['delivery_state'] ),
								'telephone' => $order->fields['delivery_telephone'],
								'format_id' => $order->fields['delivery_address_format_id']);

		if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
			$this->delivery = false;
		}

		$this->billing = array('name' => $order->fields['billing_name'],
													 'company' => $order->fields['billing_company'],
													 'street_address' => $order->fields['billing_street_address'],
													 'suburb' => $order->fields['billing_suburb'],
													 'city' => $order->fields['billing_city'],
													 'postcode' => $order->fields['billing_postcode'],
													 'state' => $order->fields['billing_state'],
													 'country' => $order->fields['billing_country'],
													 'telephone' => $order->fields['billing_telephone'],
													 'format_id' => $order->fields['billing_address_format_id']);

		$orders_products_query = "SELECT op.*, pt.*, p.content_id, p.related_content_id, lc.user_id
															FROM " . TABLE_ORDERS_PRODUCTS . " op
								LEFT OUTER JOIN	" . TABLE_PRODUCTS . " p ON ( op.`products_id`=p.`products_id` )
								LEFT OUTER JOIN	" . TABLE_PRODUCT_TYPES . " pt ON ( p.`products_type`=pt.`type_id` )
								LEFT OUTER JOIN	`" . BIT_DB_PREFIX . "liberty_content` lc ON ( lc.`content_id`=p.`content_id` )
															WHERE `orders_id` = ?
							ORDER BY op.`orders_products_id`";
		$orders_products = $this->mDb->query( $orders_products_query, array( $order_id ) );

		while (!$orders_products->EOF) {
			// convert quantity to proper decimals - account history
			if (QUANTITY_DECIMALS != 0) {
				$fix_qty = $orders_products->fields['products_quantity'];
				switch (true) {
				case (!strstr($fix_qty, '.')):
					$new_qty = $fix_qty;
					break;
				default:
					$new_qty = preg_replace('/[0]+$/', '', $orders_products->fields['products_quantity']);
					break;
				}
			} else {
				$new_qty = $orders_products->fields['products_quantity'];
			}

			$new_qty = round($new_qty, QUANTITY_DECIMALS);

			if ($new_qty == (int)$new_qty) {
				$new_qty = (int)$new_qty;
			}

			$productsKey = $orders_products->fields['orders_products_id'];
			$this->contents[$productsKey] = $orders_products->fields;
			$this->contents[$productsKey]['products_quantity'] = $new_qty;
			$this->contents[$productsKey]['id'] = $orders_products->fields['products_id'];
			$this->contents[$productsKey]['name'] = $orders_products->fields['products_name'];
			$this->contents[$productsKey]['model'] = $orders_products->fields['products_model'];
			$this->contents[$productsKey]['tax'] = (!empty( $orders_products->fields['tax_rate'] ) ? $orders_products->fields['tax_rate'] : NULL);
			$this->contents[$productsKey]['price'] = $orders_products->fields['products_price'];

			$attributes_query = "SELECT opa.*, `orders_products_attributes_id` AS `products_attributes_id`
								 FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa
								 WHERE `orders_id` = ? AND `orders_products_id` = ?
								 ORDER BY `orders_products_id`";
			$attributes = $this->mDb->query( $attributes_query, array( $order_id, $orders_products->fields['orders_products_id'] ) );
			if ($attributes->RecordCount()) {
				while (!$attributes->EOF) {
/*
					$this->contents[$productsKey]['attributes'] = $attributes->fields;
					// old school legacy naming conventions
					$this->contents[$productsKey]['attributes'][]['options_id'] = $attributes->fields['products_options_id'];
					$this->contents[$productsKey]['attributes'][]['options_values_id'] = $attributes->fields['products_options_values_id'];
					$this->contents[$productsKey]['attributes'][]['option'] = $attributes->fields['products_options'];
					$this->contents[$productsKey]['attributes'][]['value'] = $attributes->fields['products_options_values'];
					$this->contents[$productsKey]['attributes'][]['prefix'] = $attributes->fields['price_prefix'];
					$this->contents[$productsKey]['attributes'][]['final_price'] = $this->getOrderAttributePrice( $attributes->fields, $this->contents[$productsKey] );
					$this->contents[$productsKey]['attributes'][]['price'] = $attributes->fields['options_values_price'];
					$this->contents[$productsKey]['attributes'][]['orders_products_attributes_id'] = $attributes->fields['orders_products_attributes_id'];
*/
					$this->contents[$productsKey]['attributes'][] = array( 'options_id' => $attributes->fields['products_options_id'],
																			'options_values_id' => $attributes->fields['products_options_values_id'],
																			'option' => $attributes->fields['products_options'],
																			'value' => $attributes->fields['products_options_values'],
																			'prefix' => $attributes->fields['price_prefix'],
																			'final_price' => $this->getOrderAttributePrice( $attributes->fields, $this->contents[$productsKey] ),
																			'price' => $attributes->fields['options_values_price'],
																			'orders_products_attributes_id' => $attributes->fields['orders_products_attributes_id'] );

					$attributes->MoveNext();
				}
			}

			$this->info['tax_groups']["{$this->contents[$productsKey]['tax']}"] = '1';

			$orders_products->MoveNext();
		 }
	}

	// calculates totals
	function calculate( $pForceRecalculate=FALSE ) {
		global $gBitDb;
		if( is_null( $this->total ) || $pForceRecalculate ) {
			$this->subtotal = 0;
			$this->total = 0;
			$this->weight = 0;

			// shipping adjustment
			$this->free_shipping_item = 0;
			$this->free_shipping_price = 0;
			$this->free_shipping_weight = 0;

			if( !is_array($this->contents) ) {
				 return 0;
			}

			reset($this->contents);
			foreach( array_keys( $this->contents ) as $productsKey ) {
				$qty = $this->contents[$productsKey]['products_quantity'];
				// $productsKey will be orders_products_id for an order
				$prid = $this->contents[$productsKey]['products_id']; 

				// products price
				$product = $this->getProductObject( $prid );
				// sometimes 0 hash things can get stuck in cart.
				if( $product && $product->isValid() ) {
					$productAttributes = array();
					if( !empty( $this->contents[$productsKey]['attributes'] ) ) {
						foreach( $this->contents[$productsKey]['attributes'] as $attribute ) {
							$productAttributes[$attribute['options_id']] = $attribute['options_values_id'];
						}
					}

					// shipping adjustments
					if (($product->getField('product_is_always_free_ship') == 1) or ($product->getField('products_virtual') == 1) or (preg_match('/^GIFT/', addslashes($product->getField('products_model'))))) {
						$this->free_shipping_item += $qty;
						$this->free_shipping_price += zen_add_tax($products_price, $products_tax) * $qty;
						$this->free_shipping_weight += $product->getWeight( $qty, $productAttributes );
					}

					$this->total += $this->contents[$productsKey]['price'] * $qty;
					$this->subtotal += $this->total;
					$this->weight += $product->getWeight( $qty, $productAttributes );
				}
			}
		}
	}

	function expunge( $pRestock=FALSE ) {
		global $gBitProduct, $gBitDb;
		$ret = NULL;

		if( BitBase::verifyId( $this->mOrdersId ) ) {
			$gBitDb->StartTrans();
			if ($pRestock == 'on') {
				if( $products = $gBitDb->getAssoc("SELECT `products_id`, `products_quantity` FROM " . TABLE_ORDERS_PRODUCTS . " WHERE `orders_id` = ?", array( $this->mOrdersId ) ) ) {
					foreach( $products AS $productsId=>$productsQuantity	) {
						$gBitDb->Execute("update " . TABLE_PRODUCTS . " set `products_quantity` = `products_quantity` + ?, `products_ordered` = `products_ordered` - ? WHERE `products_id` = ?", array( $productsQuantity, $productsQuantity, $productsId ) );
					}
				}
			}

			$gBitProduct->invokeServices( 'commerce_expunge_order_function', $this );

			$gBitDb->query("DELETE FROM " . TABLE_COUPON_REDEEM_TRACK . " WHERE `order_id` = ?", array( $this->mOrdersId ) );
			$gBitDb->query("DELETE FROM " . TABLE_COUPON_GV_QUEUE . " WHERE `order_id` = ?", array( $this->mOrdersId ) );
			$gBitDb->query("DELETE FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$gBitDb->query("DELETE FROM " . TABLE_ORDERS_PRODUCTS . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$gBitDb->query("DELETE FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$gBitDb->query("DELETE FROM " . TABLE_ORDERS_TOTAL . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$gBitDb->query("DELETE FROM " . TABLE_ORDERS . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );

			$gBitDb->CompleteTrans();
			$ret = TRUE;
		}

		return $ret;
	}

	function getOrderAttributePrice( $pAttributeHash, $pProductHash ) {
		$ret = 0;
		// normal attributes price
		if( $pAttributeHash["price_prefix"] == '-' ) {
			$ret -= $pAttributeHash["options_values_price"];
		} else {
			$ret += $pAttributeHash["options_values_price"];
		}
		// qty discounts
		$ret += zen_get_attributes_qty_prices_onetime( $pAttributeHash["attributes_qty_prices"], $pProductHash['products_quantity'] );

		// price factor
		$display_normal_price = $pProductHash['price'];

		$ret += zen_get_attributes_price_factor( $pProductHash['price'], $pProductHash['price'], $pAttributeHash["attributes_price_factor"], $pAttributeHash["attributes_pf_offset"] );

		// per word and letter charges
		if (zen_get_attributes_type($pAttributeHash['products_attributes_id']) == PRODUCTS_OPTIONS_TYPE_TEXT) {
		// calc per word or per letter
		}

		return $ret;
	}

	function loadHistory() {
		$this->mHistory = array();
		if( $this->isValid() ) {
			$sql = "SELECT *
					FROM	 " . TABLE_ORDERS_STATUS . " os
						INNER JOIN " . TABLE_ORDERS_STATUS_HISTORY . " osh ON( osh.`orders_status_id` = os.`orders_status_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=osh.`user_id` )
					WHERE osh.`orders_id` = ? AND os.`language_id` = ?
					ORDER BY osh.`date_added`";

			if( $rs = $this->mDb->query($sql, array( $this->mOrdersId, $_SESSION['languages_id'] ) ) ) {
				while( !$rs->EOF ) {
					array_push( $this->mHistory, $rs->fields );
					$rs->MoveNext();
				}
			}
		}
		return( count( $this->mHistory ) );
	}

	function cart() {
		global $gBitDb, $currencies, $gBitUser, $gBitCustomer;
		$this->content_type = $gBitCustomer->mCart->get_content_type();

		if( $gBitUser->isRegistered() ) {
			$customer_address_query = "select c.`customers_firstname`, c.`customers_lastname`, c.`customers_telephone`, c.`customers_email_address`, ab.`entry_company`, ab.`entry_street_address`, ab.`entry_suburb`, ab.`entry_postcode`, ab.`entry_city`, ab.`entry_zone_id`, z.`zone_name`, co.`countries_id`, co.`countries_name`, co.`countries_iso_code_2`, co.`countries_iso_code_3`, co.`address_format_id`, ab.`entry_state`, ab.`address_book_id`
									 from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab
										 left join " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
										 left join " . TABLE_COUNTRIES . " co on (ab.`entry_country_id` = co.`countries_id`)
									 where c.`customers_id` = ? AND ab.`customers_id` = ?  and c.`customers_default_address_id` = ab.`address_book_id`";
			$defaultAddress = $gBitDb->getRow( $customer_address_query, array( $gBitUser->mUserId, $gBitUser->mUserId ) );

			// default to primary address in case we have ended up here without anything previously selected
			$sendToAddressId = !empty( $_SESSION['sendto'] ) ? (int)$_SESSION['sendto'] : (!empty( $defaultAddress['address_book_id'] ) ? $defaultAddress['address_book_id'] : NULL);
			if( $sendToAddressId ) {
				$query = "SELECT ab.*, z.`zone_name`, ab.`entry_country_id`, c.`countries_id`, c.`countries_name`, c.`countries_iso_code_2`, c.`countries_iso_code_3`, c.`address_format_id`, ab.`entry_state`
						 FROM " . TABLE_ADDRESS_BOOK . " ab
							 LEFT JOIN " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
							 LEFT JOIN " . TABLE_COUNTRIES . " c on (ab.`entry_country_id` = c.`countries_id`)
						 WHERE ab.`customers_id`=? AND ab.`address_book_id`=?";
				$shippingAddress = $gBitDb->getRow( $query, array( $gBitUser->mUserId, $sendToAddressId ) );
				if( !$shippingAddress ) {
					$shippingAddress = $defaultAddress;
				}
			}

			// default to primary address in case we have ended up here without anything previously selected
			$billToAddressId = !empty( $_SESSION['billto'] ) ? (int)$_SESSION['billto'] : (!empty( $defaultAddress['address_book_id'] ) ? $defaultAddress['address_book_id'] : NULL);
			if( $billToAddressId ) {
				$query = "SELECT ab.*, z.`zone_name`, ab.`entry_country_id`, c.`countries_id`, c.`countries_name`, c.`countries_iso_code_2`, c.`countries_iso_code_3`, c.`address_format_id`, ab.`entry_state`
							FROM " . TABLE_ADDRESS_BOOK . " ab
							LEFT JOIN " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
							LEFT JOIN " . TABLE_COUNTRIES . " c on (ab.`entry_country_id` = c.`countries_id`)
							WHERE ab.`customers_id` = ?	and ab.`address_book_id` = ?";
				$billingAddress = $gBitDb->getRow( $query, array( $gBitUser->mUserId, $billToAddressId ) );
			}

			switch( STORE_PRODUCT_TAX_BASIS ) {
				case 'Shipping':
					$taxAddressId = ($this->content_type == 'virtual' ? $billToAddressId : $sendToAddressId);
					break;
				case 'Billing':
					$taxAddressId = $billToAddressId;
					break;
				case 'Store':
					if ($billingAddress['entry_zone_id'] == STORE_ZONE) {
						$taxAddressId = (int)$billToAddressId;
					} else {
						$taxAddressId = (int)($this->content_type == 'virtual' ? $billToAddressId : $sendToAddressId);
					}
					break;
			 }

			//STORE_PRODUCT_TAX_BASIS
			if( !empty( $taxAddressId ) ) {
				$tax_address_query = "SELECT ab.entry_country_id, ab.entry_zone_id, ab.`entry_state`
									  FROM " . TABLE_ADDRESS_BOOK . " ab
										LEFT JOIN " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
									  WHERE ab.`customers_id` = ?  and ab.`address_book_id` = ?";
				$tax_address = $gBitDb->getAssoc( $tax_address_query, array( $gBitUser->mUserId, $taxAddressId) );
			}

			if( !empty( $taxAddress['entry_country_id'] ) && empty( $taxAddress['entry_zone_id'] ) ) {
				if( $gBitCustomer->getZoneCount( $taxAddress['entry_country_id'] ) && ($zoneId = $gBitCustomer->getZoneId( $taxAddress['entry_state'], $taxAddress['entry_country_id'] )) ) {
					$taxAddress['entry_zone_id'] = $zoneId;
				}
				// maybe we have some newly updated zones and outdated address_book entries
			} else {
				$taxAddress = $defaultAddress;
			}

		}

		$class = &$_SESSION['payment'];

		$coupon_code = NULL;
		if( !empty( $_SESSION['cc_id'] ) ) {
			$coupon_code_query = "SELECT `coupon_code` FROM " . TABLE_COUPONS . " WHERE `coupon_id` = ?";
			$coupon_code = $gBitDb->GetOne($coupon_code_query, array( (int)$_SESSION['cc_id'] ) );
		}

		$this->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
							'currency' => !empty( $_SESSION['currency'] ) ? $_SESSION['currency'] : NULL,
							'currency_value' => !empty( $_SESSION['currency'] ) ? $currencies->currencies[$_SESSION['currency']]['currency_value'] : NULL,
							'payment_method' => !empty( $GLOBALS[$class] ) ? $GLOBALS[$class]->title : '',
							'payment_module_code' => !empty( $GLOBALS[$class] ) ? $GLOBALS[$class]->code : '',
							'coupon_code' => $coupon_code,
							'shipping_method' => !empty( $_SESSION['shipping']['title'] ) ? $_SESSION['shipping']['title'] : '',
							'shipping_method_code' => !empty( $_SESSION['shipping']['code'] ) ? $_SESSION['shipping']['code'] : '',
							'shipping_module_code' => !empty( $_SESSION['shipping']['id'] ) ? $_SESSION['shipping']['id'] : '',
							'shipping_cost' => !empty( $_SESSION['shipping']['cost'] ) ? $_SESSION['shipping']['cost'] : '',
							'subtotal' => 0,
							'tax' => 0,
							'total' => 0,
							'tax_groups' => array(),
							'comments' => (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''),
							'ip_address' => $_SERVER['REMOTE_ADDR']
							);

		if ($this->info['total'] == 0) {
			if (DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID == 0) {
				$this->info['order_status'] = DEFAULT_ORDERS_STATUS_ID;
			} else {
				$this->info['order_status'] = DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID;
			}
		}
		if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
			if ( isset($GLOBALS[$class]->order_status) && is_numeric($GLOBALS[$class]->order_status) && ($GLOBALS[$class]->order_status > 0) ) {
				$this->info['order_status'] = $GLOBALS[$class]->order_status;
			}
		}


		if( !empty( $defaultAddress ) ) {
			$this->customer = array('firstname' => $defaultAddress['customers_firstname'],
									'lastname' => $defaultAddress['customers_lastname'],
									'company' => $defaultAddress['entry_company'],
									'street_address' => $defaultAddress['entry_street_address'],
									'suburb' => $defaultAddress['entry_suburb'],
									'city' => $defaultAddress['entry_city'],
									'postcode' => $defaultAddress['entry_postcode'],
									'state' => ((zen_not_null($defaultAddress['entry_state'])) ? $defaultAddress['entry_state'] : $defaultAddress['zone_name']),
									'zone_id' => $defaultAddress['entry_zone_id'],
									'country' => array(
										'countries_name' => $defaultAddress['countries_name'], 
										'countries_id' => $defaultAddress['countries_id'], 
										'countries_iso_code_2' => $defaultAddress['countries_iso_code_2'], 
										'countries_iso_code_3' => $defaultAddress['countries_iso_code_3'],
									),
									'format_id' => $defaultAddress['address_format_id'],
									'telephone' => $defaultAddress['customers_telephone'],
									'email_address' => $defaultAddress['customers_email_address']);
		}

		if( !empty( $shippingAddress ) ) {
			$this->delivery = array('firstname' => $shippingAddress['entry_firstname'],
									'lastname' => $shippingAddress['entry_lastname'],
									'company' => $shippingAddress['entry_company'],
									'street_address' => $shippingAddress['entry_street_address'],
									'suburb' => $shippingAddress['entry_suburb'],
									'city' => $shippingAddress['entry_city'],
									'postcode' => $shippingAddress['entry_postcode'],
									'state' => ((zen_not_null($shippingAddress['entry_state'])) ? $shippingAddress['entry_state'] : $shippingAddress['zone_name']),
									'zone_id' => $shippingAddress['entry_zone_id'],
									'country' => array(
										'countries_id' => $shippingAddress['countries_id'],
										'countries_name' => $shippingAddress['countries_name'],
										'countries_iso_code_2' => $shippingAddress['countries_iso_code_2'],
										'countries_iso_code_3' => $shippingAddress['countries_iso_code_3']),
									'country_id' => $shippingAddress['entry_country_id'],
									'telephone' => $shippingAddress['entry_telephone'],
									'format_id' => $shippingAddress['address_format_id']);
		}

		if( !empty( $billingAddress ) ) {
			$this->billing = array('firstname' => $billingAddress['entry_firstname'],
									'lastname' => $billingAddress['entry_lastname'],
									'company' => $billingAddress['entry_company'],
									'street_address' => $billingAddress['entry_street_address'],
									'suburb' => $billingAddress['entry_suburb'],
									'city' => $billingAddress['entry_city'],
									'postcode' => $billingAddress['entry_postcode'],
									'state' => ((zen_not_null($billingAddress['entry_state'])) ? $billingAddress['entry_state'] : $billingAddress['zone_name']),
									'zone_id' => $billingAddress['entry_zone_id'],
									'country' => array(
										'countries_id' => $billingAddress['countries_id'],
										'countries_name' => $billingAddress['countries_name'],
										'countries_iso_code_2' => $billingAddress['countries_iso_code_2'],
										'countries_iso_code_3' => $billingAddress['countries_iso_code_3']),
									'country_id' => $billingAddress['entry_country_id'],
									'telephone' => $billingAddress['entry_telephone'],
									'format_id' => $billingAddress['address_format_id']);
		}

		foreach( array_keys( $gBitCustomer->mCart->contents ) as $productsKey ) {
			$this->contents[$productsKey] = $gBitCustomer->mCart->getProductHash( $productsKey );
			if( !empty( $taxAddress ) ) {
				$this->contents[$productsKey]['tax'] = zen_get_tax_rate( $this->contents[$productsKey]['tax_class_id'], $taxAddress['countries_id'], $taxAddress['entry_zone_id'] );
				$this->contents[$productsKey]['tax_description'] = zen_get_tax_description( $this->contents[$productsKey]['tax_class_id'], $taxAddress['countries_id'], $taxAddress['entry_zone_id'] );
			}

			if ( !empty( $this->contents[$productsKey]['attributes'] ) ) {
				$attributes  = $this->contents[$productsKey]['attributes'];
				$this->contents[$productsKey]['attributes'] = array();
				$subindex = 0;
				foreach( $attributes as $option=>$value ) {
					$optionValues = zen_get_option_value( zen_get_options_id( $option ), (int)$value );
					// Determine if attribute is a text attribute and change products array if it is.
					if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID){
						$attr_value = $this->contents[$productsKey]['attributes_values'][$option];
					} else {
						$attr_value = $optionValues['products_options_values_name'];
					}

					$this->contents[$productsKey]['attributes'][$subindex] = array('option' => $optionValues['products_options_name'],
																			 'value' => $attr_value,
																			 'option_id' => $option,
																			 'value_id' => $value,
																			 'prefix' => $optionValues['price_prefix'],
																			 'price' => $optionValues['options_values_price']);

					$subindex++;
				}
			}

			$shown_price = (zen_add_tax($this->contents[$productsKey]['final_price'], $this->contents[$productsKey]['tax']) * $this->contents[$productsKey]['products_quantity'])
							+ zen_add_tax($this->contents[$productsKey]['onetime_charges'], $this->contents[$productsKey]['tax']);
			$this->subtotal += $shown_price;

			$products_tax = $this->contents[$productsKey]['tax'];
			$products_tax_description = $this->contents[$productsKey]['tax_description'];
			if (DISPLAY_PRICE_WITH_TAX == 'true') {
				$this->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
				if (isset($this->info['tax_groups']["$products_tax_description"])) {
					$this->info['tax_groups']["$products_tax_description"] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
				} else {
					$this->info['tax_groups']["$products_tax_description"] = $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
				}
			} else {
				$this->info['tax'] += ($products_tax / 100) * $shown_price;
				if (isset($this->info['tax_groups']["$products_tax_description"])) {
					$this->info['tax_groups']["$products_tax_description"] += ($products_tax / 100) * $shown_price;
				} else {
					$this->info['tax_groups']["$products_tax_description"] = ($products_tax / 100) * $shown_price;
				}
			}
			$this->info['tax'] = zen_round($this->info['tax'],2);
		}

		if (DISPLAY_PRICE_WITH_TAX == 'true') {
			$this->info['total'] = $this->subtotal + $this->info['shipping_cost'];
		} else {
			$this->info['total'] = $this->subtotal + $this->info['tax'] + $this->info['shipping_cost'];
		}
	}

	function create($zf_ot_modules, $zf_mode = 2) {
		global $gBitDb;

//		$gBitDb->StartTrans();
		if ($_SESSION['shipping'] == 'free_free') {
			$this->info['shipping_module_code'] = $_SESSION['shipping'];
		}

		$sql_data_array = array('customers_id' => $_SESSION['customer_id'],
							'customers_name' => $this->customer['firstname'] . ' ' . $this->customer['lastname'],
							'customers_company' => $this->customer['company'],
							'customers_street_address' => $this->customer['street_address'],
							'customers_suburb' => $this->customer['suburb'],
							'customers_city' => $this->customer['city'],
							'customers_postcode' => $this->customer['postcode'],
							'customers_state' => $this->customer['state'],
							'customers_country' => $this->customer['country']['countries_name'],
							'customers_telephone' => $this->customer['telephone'],
							'customers_email_address' => $this->customer['email_address'],
							'customers_address_format_id' => $this->customer['format_id'],
							'delivery_name' => $this->delivery['firstname'] . ' ' . $this->delivery['lastname'],
							'delivery_company' => $this->delivery['company'],
							'delivery_street_address' => $this->delivery['street_address'],
							'delivery_suburb' => $this->delivery['suburb'],
							'delivery_city' => $this->delivery['city'],
							'delivery_postcode' => $this->delivery['postcode'],
							'delivery_state' => $this->delivery['state'],
							'delivery_country' => $this->delivery['country']['countries_name'],
							'delivery_telephone' => $this->delivery['telephone'],
							'delivery_address_format_id' => $this->delivery['format_id'],
							'billing_name' => $this->billing['firstname'] . ' ' . $this->billing['lastname'],
							'billing_company' => $this->billing['company'],
							'billing_street_address' => $this->billing['street_address'],
							'billing_suburb' => $this->billing['suburb'],
							'billing_city' => $this->billing['city'],
							'billing_postcode' => $this->billing['postcode'],
							'billing_state' => $this->billing['state'],
							'billing_country' => $this->billing['country']['countries_name'],
							'billing_telephone' => $this->billing['telephone'],
							'billing_address_format_id' => $this->billing['format_id'],
							'payment_method' => (($this->info['payment_module_code'] == '' and $this->info['payment_method'] == '') ? PAYMENT_METHOD_GV : $this->info['payment_method']),
							'payment_module_code' => (($this->info['payment_module_code'] == '' and $this->info['payment_method'] == '') ? PAYMENT_MODULE_GV : $this->info['payment_module_code']),
							'shipping_method' => $this->info['shipping_method'],
							'shipping_method_code' => $this->info['shipping_method_code'],
							'shipping_module_code' => (strpos($this->info['shipping_module_code'], '_') > 0 ? substr($this->info['shipping_module_code'], 0, strpos($this->info['shipping_module_code'], '_')) : $this->info['shipping_module_code']),
							'coupon_code' => $this->info['coupon_code'],
							'cc_type' => $this->info['cc_type'],
							'cc_owner' => $this->info['cc_owner'],
							'cc_number' => $this->info['cc_number'],
							'cc_expires' => $this->info['cc_expires'],
							'date_purchased' => $this->mDb->NOW(),
							'orders_status' => $this->info['order_status'],
							'order_total' => $this->info['total'],
							'order_tax' => $this->info['tax'],
							'currency' => $this->info['currency'],
							'currency_value' => $this->info['currency_value'],
							'ip_address' => $_SERVER['REMOTE_ADDR']
							);


		$gBitDb->associateInsert(TABLE_ORDERS, $sql_data_array);

		$this->mOrdersId = zen_db_insert_id( TABLE_ORDERS, 'orders_id' );

		for ($i=0, $n=sizeof($zf_ot_modules); $i<$n; $i++) {
			$sql_data_array = array('orders_id' => $this->mOrdersId,
									'title' => $zf_ot_modules[$i]['title'],
									'text' => $zf_ot_modules[$i]['text'],
									'orders_value' => (is_numeric( $zf_ot_modules[$i]['value'] ) ? $zf_ot_modules[$i]['value'] : 0),
									'class' => $zf_ot_modules[$i]['code'],
									'sort_order' => $zf_ot_modules[$i]['sort_order']);
			$gBitDb->associateInsert(TABLE_ORDERS_TOTAL, $sql_data_array);
		}

		$customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
		$sql_data_array = array('orders_id' => $this->mOrdersId,
							'orders_status_id' => $this->info['order_status'],
							'user_id' => $_SESSION['customer_id'],
							'date_added' => $this->mDb->NOW(),
							'customer_notified' => $customer_notification,
							'comments' => $this->info['comments']);
		$gBitDb->associateInsert(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

//		$gBitDb->CompleteTrans();

		return( $this->mOrdersId );
	}


	function	create_add_products($zf_insert_id, $zf_mode = false) {
		global $gBitDb, $gBitUser, $currencies, $order_total_modules, $order_totals;

			$this->mDb->StartTrans();
	// initialized for the email confirmation

		$this->products_ordered = '';
		$this->products_ordered_html = '';

	// lowstock email report
		$this->email_low_stock='';

		foreach( array_keys( $this->contents ) as $productsKey ) {
			// Stock Update - Joao Correia
			if (STOCK_LIMITED == 'true') {
				if (DOWNLOAD_ENABLED == 'true') {
					$stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
										FROM " . TABLE_PRODUCTS . " p
											LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(p.`products_id`=pom.`products_id`)
											LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON (pa.`products_options_values_id`=pom.`products_options_values_id`)
											LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON(pa.`products_attributes_id`=pad.`products_attributes_id`)
										WHERE p.`products_id` = ?";
					$bindVars = array( zen_get_prid($this->contents[$productsKey]['id']) );

					// Will work with only one option for downloadable products
					// otherwise, we have to build the query dynamically with a loop
					$products_attributes = $this->contents[$productsKey]['attributes'];
					if( is_array( $products_attributes ) ) {
						$stock_query_raw .= " AND pa.`products_options_id` = ? AND pa.`products_options_values_id` = ?";
						$bindVars[] = zen_get_options_id( $products_attributes[0]['option_id'] );
						$bindVars[] = $products_attributes[0]['value_id'];
					}
					$stock_values = $gBitDb->query($stock_query_raw, $bindVars);
				} else {
					$stock_values = $gBitDb->Execute("select `products_quantity` from " . TABLE_PRODUCTS . " where `products_id` = '" . zen_get_prid($this->contents[$productsKey]['id']) . "'");
				}

				if ($stock_values->RecordCount() > 0) {
					// do not decrement quantities if products_attributes_filename exists
					if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values->fields['products_attributes_filename'])) {
						$stock_left = $stock_values->fields['products_quantity'] - $this->contents[$productsKey]['products_quantity'];
						$this->contents[$productsKey]['stock_reduce'] = $this->contents[$productsKey]['products_quantity'];
					} else {
						$stock_left = $stock_values->fields['products_quantity'];
					}

		//						$this->contents[$productsKey]['stock_value'] = $stock_values->fields['products_quantity'];

					$gBitDb->Execute("update " . TABLE_PRODUCTS . " set `products_quantity` = '" . $stock_left . "' where `products_id` = '" . zen_get_prid($this->contents[$productsKey]['id']) . "'");
		//				if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
					if ($stock_left < 1) {
						// only set status to off when not displaying sold out
						if (SHOW_PRODUCTS_SOLD_OUT == '0') {
							$gBitDb->Execute("update " . TABLE_PRODUCTS . " set `products_status` = '0' where `products_id` = '" . zen_get_prid($this->contents[$productsKey]['id']) . "'");
						}
					}

		// for low stock email
					if ( $stock_left <= STOCK_REORDER_LEVEL ) {
				// WebMakers.com Added: add to low stock email
					$this->email_low_stock .=	'ID# ' . zen_get_prid($this->contents[$productsKey]['id']) . "\t\t" . $this->contents[$productsKey]['model'] . "\t\t" . $this->contents[$productsKey]['name'] . "\t\t" . ' Qty Left: ' . $stock_left . "\n";
					}
				}
			}

			// Update products_ordered (for bestsellers list)
			$gBitDb->Execute("update " . TABLE_PRODUCTS . " set `products_ordered` = `products_ordered` + " . sprintf('%f', $this->contents[$productsKey]['products_quantity']) . " where `products_id` = '" . zen_get_prid($this->contents[$productsKey]['id']) . "'");

			$sql_data_array = array('orders_id' => $zf_insert_id,
								'products_id' => zen_get_prid($this->contents[$productsKey]['id']),
								'products_model' => $this->contents[$productsKey]['model'],
								'products_name' => $this->contents[$productsKey]['name'],
								'products_price' => $this->contents[$productsKey]['price'],
								'products_commission' => $this->contents[$productsKey]['commission'],
								'final_price' => $this->contents[$productsKey]['final_price'],
								'onetime_charges' => $this->contents[$productsKey]['onetime_charges'],
								'products_tax' => $this->contents[$productsKey]['tax'],
								'products_quantity' => $this->contents[$productsKey]['products_quantity'],
								'products_priced_by_attribute' => $this->contents[$productsKey]['products_priced_by_attribute'],
								'product_is_free' => $this->contents[$productsKey]['product_is_free'],
								'products_discount_type' => $this->contents[$productsKey]['products_discount_type'],
								'products_discount_type_from' => $this->contents[$productsKey]['products_discount_type_from']);
			$gBitDb->associateInsert(TABLE_ORDERS_PRODUCTS, $sql_data_array);
			$this->contents[$productsKey]['orders_products_id'] = zen_db_insert_id( TABLE_ORDERS_PRODUCTS, 'orders_products_id' );

			$order_total_modules->update_credit_account($productsKey);//ICW ADDED FOR CREDIT CLASS SYSTEM

			if( !empty( $this->contents[$productsKey]['purchase_group_id'] ) ) {
				$gBitUser->addUserToGroup( $gBitUser->mUserId, $this->contents[$productsKey]['purchase_group_id'] );
			}


	//------insert customer choosen option to order--------
			$attributes_exist = '0';
			$this->products_ordered_attributes = '';
			if( !empty($this->contents[$productsKey]['attributes']) ) {
				$attributes_exist = '1';
				foreach( array_keys( $this->contents[$productsKey]['attributes'] ) as $j ) {

					$optionValues = zen_get_option_value( (int)$this->contents[$productsKey]['attributes'][$j]['option_id'], (int)$this->contents[$productsKey]['attributes'][$j]['value_id'] );
					if( !empty( $optionValues['purchase_group_id'] ) ) {
						$gBitUser->addUserToGroup( $gBitUser->mUserId, $optionValues['purchase_group_id'] );
					}

					if( !empty( $optionValues['products_options_id'] ) ) {
						//clr 030714 update insert query.	changing to use values form $order->contents for products_options_values.
						$sql_data_array = array('orders_id' => $zf_insert_id,
												'orders_products_id' => $this->contents[$productsKey]['orders_products_id'],
												'products_options' => $optionValues['products_options_name'],
												'products_options_values' => $this->contents[$productsKey]['attributes'][$j]['value'],
												'options_values_price' => $optionValues['options_values_price'],
												'price_prefix' => $optionValues['price_prefix'],
												'product_attribute_is_free' => $optionValues['product_attribute_is_free'],
												'products_attributes_wt' => $optionValues['products_attributes_wt'],
												'products_attributes_wt_pfix' => $optionValues['products_attributes_wt_pfix'],
												'attributes_discounted' => (int)$optionValues['attributes_discounted'],
												'attributes_price_base_inc' => (int)$optionValues['attributes_price_base_inc'],
												'attributes_price_onetime' => $optionValues['attributes_price_onetime'],
												'attributes_price_factor' => $optionValues['attributes_price_factor'],
												'attributes_pf_offset' => $optionValues['attributes_pf_offset'],
												'attributes_pf_onetime' => $optionValues['attributes_pf_onetime'],
												'attributes_pf_onetime_offset' => $optionValues['attributes_pf_onetime_offset'],
												'attributes_qty_prices' => $optionValues['attributes_qty_prices'],
												'attributes_qty_prices_onetime' => $optionValues['attributes_qty_prices_onetime'],
												'attributes_price_words' => $optionValues['attributes_price_words'],
												'attributes_price_words_free' => $optionValues['attributes_price_words_free'],
												'attributes_price_letters' => $optionValues['attributes_price_letters'],
												'attributes_price_letters_free' => $optionValues['attributes_price_letters_free'],
												'products_options_id' => $optionValues['products_options_id'],
												'products_options_values_id' => $optionValues['products_options_values_id'],
												);
	
	
						$gBitDb->associateInsert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
					}

					if ((DOWNLOAD_ENABLED == 'true') && isset($optionValues['products_attributes_filename']) && zen_not_null($optionValues['products_attributes_filename'])) {
						$sql_data_array = array('orders_id' => $zf_insert_id,
												'orders_products_id' => $this->contents[$productsKey]['orders_products_id'],
												'orders_products_filename' => $optionValues['products_attributes_filename'],
												'download_maxdays' => $optionValues['products_attributes_maxdays'],
												'download_count' => $optionValues['products_attributes_maxcount']);

						$gBitDb->associateInsert(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
					}
			//clr 030714 changing to use values from $orders->contents and adding call to zen_decode_specialchars()
			//				$this->products_ordered_attributes .= "\n\t" . $optionValues['products_options_name'] . ' ' . $optionValues['products_options_values_name'];
					$this->products_ordered_attributes .= "\n\t" . $optionValues['products_options_name'] . ' ' . zen_decode_specialchars($this->contents[$productsKey]['attributes'][$j]['value']);
				}
			}
			//------insert customer choosen option eof ----
			$this->total_weight += ($this->contents[$productsKey]['products_quantity'] * $this->contents[$productsKey]['weight']);
//			$this->total_tax += zen_calculate_tax($total_products_price, $products_tax) * $this->contents[$productsKey]['products_quantity'];
//			$this->total_cost += $total_products_price;

			// include onetime charges
			$this->products_ordered .=	$this->contents[$productsKey]['products_quantity'] . ' x ' . $this->contents[$productsKey]['name'] . ($this->contents[$productsKey]['model'] != '' ? ' (' . $this->contents[$productsKey]['model'] . ') ' : '') . ' = ' .
									$currencies->display_price($this->contents[$productsKey]['final_price'], $this->contents[$productsKey]['tax'], $this->contents[$productsKey]['products_quantity']) .
									($this->contents[$productsKey]['onetime_charges'] !=0 ? "\n" . TEXT_ONETIME_CHARGES_EMAIL . $currencies->display_price($this->contents[$productsKey]['onetime_charges'], $this->contents[$productsKey]['tax'], 1) : '') .
									$this->products_ordered_attributes . "\n";
			$this->products_ordered_html .=
			'<tr>' .
			'<td class="product-details" align="right" valign="top" width="30">' . $this->contents[$productsKey]['products_quantity'] . '&nbsp;x</td>' .
			'<td class="product-details" valign="top">' . $this->contents[$productsKey]['name'] . ($this->contents[$productsKey]['model'] != '' ? ' (' . $this->contents[$productsKey]['model'] . ') ' : '') .
			'<span style="white-space:nowrap;"><small><em> '. $this->products_ordered_attributes .'</em></small></span></td>' .
			'<td class="product-details-num" valign="top" align="right">' .
				$currencies->display_price($this->contents[$productsKey]['final_price'], $this->contents[$productsKey]['tax'], $this->contents[$productsKey]['products_quantity']) .
				($this->contents[$productsKey]['onetime_charges'] !=0 ?
						'</td></tr><tr><td class="product-details">' . TEXT_ONETIME_CHARGES_EMAIL . '</td>' .
						'<td>' . $currencies->display_price($this->contents[$productsKey]['onetime_charges'], $this->contents[$productsKey]['tax'], 1) : '') .
			'</td></tr>';
		}

		$order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM
		$this->mDb->CompleteTrans();
	}


		function send_order_email($zf_insert_id, $zf_mode) {
			global $currencies, $order_totals;

//			print_r($this);
//			die();
			if ($this->email_low_stock != '' and SEND_LOWSTOCK_EMAIL=='1') {
	// send an email
					$email_low_stock = SEND_EXTRA_LOW_STOCK_EMAIL_TITLE . "\n\n" . $this->email_low_stock;
					zen_mail('', SEND_EXTRA_LOW_STOCK_EMAILS_TO, EMAIL_TEXT_SUBJECT_LOWSTOCK, $email_low_stock, STORE_OWNER, EMAIL_FROM, array('EMAIL_MESSAGE_HTML' => nl2br($email_low_stock)),'low_stock');
			}

// lets start with the email confirmation
// make an array to store the html version
				$html_msg=array();

//intro area
				$email_order = EMAIL_TEXT_HEADER . EMAIL_TEXT_FROM . STORE_NAME . "\n\n" .
											 $this->customer['firstname'] . ' ' . $this->customer['lastname'] . "\n\n" .
											 EMAIL_THANKS_FOR_SHOPPING . "\n" . EMAIL_DETAILS_FOLLOW . "\n" .
											 EMAIL_SEPARATOR . "\n" .
											 EMAIL_TEXT_ORDER_NUMBER . ' ' . $zf_insert_id . "\n" .
											 EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n" .
											 EMAIL_TEXT_INVOICE_URL . ' ' . zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $zf_insert_id, 'SSL', false) . "\n\n";
				$html_msg['EMAIL_TEXT_HEADER']		 = EMAIL_TEXT_HEADER;
				$html_msg['EMAIL_TEXT_FROM']			 = EMAIL_TEXT_FROM;
				$html_msg['INTRO_STORE_NAME']			= STORE_NAME;
				$html_msg['EMAIL_THANKS_FOR_SHOPPING'] = EMAIL_THANKS_FOR_SHOPPING;
				$html_msg['EMAIL_DETAILS_FOLLOW']	= EMAIL_DETAILS_FOLLOW;
				$html_msg['INTRO_ORDER_NUM_TITLE'] = EMAIL_TEXT_ORDER_NUMBER;
				$html_msg['INTRO_ORDER_NUMBER']		= $zf_insert_id;
				$html_msg['INTRO_DATE_TITLE']			= EMAIL_TEXT_DATE_ORDERED;
				$html_msg['INTRO_DATE_ORDERED']		= strftime(DATE_FORMAT_LONG);
				$html_msg['INTRO_URL_TEXT']				= EMAIL_TEXT_INVOICE_URL_CLICK;
				$html_msg['INTRO_URL_VALUE']			 = zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $zf_insert_id, 'SSL', false);

//comments area
				if ($this->info['comments']) {
					$email_order .= zen_db_output($this->info['comments']) . "\n\n";
					$html_msg['ORDER_COMMENTS'] = zen_db_output($this->info['comments']);
				} else {
					$html_msg['ORDER_COMMENTS'] = '';
				}

//products area
				$email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
												EMAIL_SEPARATOR . "\n" .
												$this->products_ordered .
												EMAIL_SEPARATOR . "\n";
				$html_msg['PRODUCTS_TITLE'] = EMAIL_TEXT_PRODUCTS;
				$html_msg['PRODUCTS_DETAIL']='<table class="product-details" border="0" width="100%" cellspacing="0" cellpadding="2">' . $this->products_ordered_html . '</table>';

//order totals area
				$html_ot = '<td class="order-totals-text" align="right" width="100%">' . '&nbsp;' . '</td><td class="order-totals-num" align="right" nowrap="nowrap">' . '---------' .'</td></tr><tr>';
				for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
					$email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
				$html_ot .= '<td class="order-totals-text" align="right" width="100%">' . $order_totals[$i]['title'] . '</td><td class="order-totals-num" align="right" nowrap="nowrap">' .($order_totals[$i]['text']) .'</td></tr><tr>';
				}
				$html_msg['ORDER_TOTALS'] = '<table border="0" width="100%" cellspacing="0" cellpadding="2">' . $html_ot . '</table>';

//addresses area: Delivery
				$html_msg['HEADING_ADDRESS_INFORMATION']= HEADING_ADDRESS_INFORMATION;
		$html_msg['ADDRESS_DELIVERY_TITLE']		 = EMAIL_TEXT_DELIVERY_ADDRESS;
		$html_msg['ADDRESS_DELIVERY_DETAIL']		= ($this->content_type != 'virtual') ? zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, '', "<br />") : 'n/a';
		$html_msg['SHIPPING_METHOD_TITLE']			= HEADING_SHIPPING_METHOD;
		$html_msg['SHIPPING_METHOD_DETAIL']		 = (zen_not_null($this->info['shipping_method'])) ? $this->info['shipping_method'] : 'n/a';

				if ($this->content_type != 'virtual') {
					$email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
													EMAIL_SEPARATOR . "\n" .
				 zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") . "\n";
				}

//addresses area: Billing
				$email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
												EMAIL_SEPARATOR . "\n" .
											 zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") . "\n\n";
		$html_msg['ADDRESS_BILLING_TITLE']	 = EMAIL_TEXT_BILLING_ADDRESS;
		$html_msg['ADDRESS_BILLING_DETAIL']	= zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, '', "<br />");

		if (is_object($GLOBALS[$_SESSION['payment']])) {
					$email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
													EMAIL_SEPARATOR . "\n";
					$payment_class = $_SESSION['payment'];
					$email_order .= $GLOBALS[$payment_class]->title . "\n\n";
					if( !empty( $GLOBALS[$payment_class]->email_footer ) ) {
						$email_order .= $GLOBALS[$payment_class]->email_footer . "\n\n";
					}
				}
		$html_msg['PAYMENT_METHOD_TITLE']	= (is_object($GLOBALS[$_SESSION['payment']]) ? EMAIL_TEXT_PAYMENT_METHOD : '') ;
		$html_msg['PAYMENT_METHOD_DETAIL'] = (is_object($GLOBALS[$_SESSION['payment']]) ? $GLOBALS[$payment_class]->title : '' );
		$html_msg['PAYMENT_METHOD_FOOTER'] = (is_object($GLOBALS[$_SESSION['payment']]) && !empty( $GLOBALS[$payment_class]->email_footer ) ? $GLOBALS[$payment_class]->email_footer : '');

// include disclaimer
				$email_order .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";
// include copyright
				$email_order .= "\n-----\n" . EMAIL_FOOTER_COPYRIGHT . "\n\n";

				while (strstr($email_order, '&nbsp;')) $email_order = str_replace('&nbsp;', ' ', $email_order);

				$html_msg['EMAIL_FIRST_NAME'] = $this->customer['firstname'];
				$html_msg['EMAIL_LAST_NAME'] = $this->customer['lastname'];
//	$html_msg['EMAIL_TEXT_HEADER'] = EMAIL_TEXT_HEADER;
				$html_msg['EXTRA_INFO'] = '';
				zen_mail($this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $zf_insert_id, $email_order, STORE_NAME, EMAIL_FROM, $html_msg, 'checkout');

// send additional emails
			 if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
		 $extra_info=email_collect_extra_info('','', $this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], $this->customer['telephone']);
				 $html_msg['EXTRA_INFO'] = $extra_info['HTML'];
				 zen_mail('', SEND_EXTRA_ORDER_EMAILS_TO, SEND_EXTRA_NEW_ORDERS_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $zf_insert_id,
				 $email_order . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $html_msg, 'checkout_extra');
			}
		}

	function getFormattedAddress( $pAddressHash, $pBreak='<br>' ) {
		$ret = '';
		if( $this->isValid() ) {
			$ret = zen_address_format( $this->customer['format_id'], $this->$pAddressHash, 1, '', $pBreak );
		}
		return $ret;
	}

	function getStatus() {
		$ret = NULL;
		if( !empty( $this->info['orders_status_id'] ) ) {
			$ret = $this->info['orders_status_id'];
		}
		return $ret;
	}

	function expungeStatus( $pOrdersStatusHistoryId ) {
		$this->mDb->query( "DELETE FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE `orders_status_history_id`=?", array( $pOrdersStatusHistoryId ) );
	}

	function updateStatus( $pParamHash ) {
		global $gBitUser;

		$order_updated = false;

		// default to order status if not specified
		$status = !empty( $pParamHash['status'] ) ? zen_db_prepare_input( $pParamHash['status'] ) : $this->getStatus();
		$comments = !empty( $pParamHash['comments'] ) ? zen_db_prepare_input( $pParamHash['comments'] ) : NULL;

		$statusChanged = ($this->getStatus() != $status);

		if ( $statusChanged || !empty( $comments ) ) {
			$this->mDb->StartTrans();
			$this->mDb->query( "update " . TABLE_ORDERS . "
								set `orders_status` = ?, `last_modified` = ".$this->mDb->NOW()."
								where `orders_id` = ?", array( $status, $this->mOrdersId ) );

			$this->info['orders_status_id'] = $status;
			$this->info['orders_status'] = zen_get_order_status_name( $status );

			$customer_notified = '0';
			if( isset( $pParamHash['notify'] ) && ( $pParamHash['notify'] == 'on' ) ) {
				$notify_comments = '';
				if( !empty( $comments ) ) {
					$notify_comments = $comments . "\n\n";
				}

				//send emails
				$message = STORE_NAME . "\n------------------------------------------------------\n" .
					tra( 'Order Number' ) . ': ' . $this->mOrdersId . "\n\n" .
					tra( 'Detailed Invoice' ) . ': ' . $this->getDisplayLink() . "\n\n" .
					tra( 'Date Ordered' ) . ': ' . zen_date_long($this->info['date_purchased']) . "\n\n" .
					strip_tags($notify_comments) ;
				if( $statusChanged ) {
					$message .= tra( 'Your order has been updated to the following status' ) . ': ' . $this->info['orders_status'] . "\n\n";
				}
				$message .= tra( 'Please reply to this email if you have any questions.' );

				$html_msg['EMAIL_CUSTOMERS_NAME']		= $this->customer['name'];
				$html_msg['EMAIL_TEXT_ORDER_NUMBER'] = tra( 'Order Number' ) . ': ' . $this->mOrdersId;
				$html_msg['EMAIL_TEXT_INVOICE_URL']	= $this->getDisplayLink();
				$html_msg['EMAIL_TEXT_DATE_ORDERED'] = tra( 'Date Ordered' ) . ': ' . zen_date_long( $this->info['date_purchased'] );
				$html_msg['EMAIL_TEXT_STATUS_COMMENTS'] = $notify_comments;
				if( $statusChanged ) {
					$html_msg['EMAIL_TEXT_STATUS_UPDATED'] = tra( 'Your order has been updated to the following status' ) . ': ';
					$html_msg['EMAIL_TEXT_NEW_STATUS'] = $this->info['orders_status'];
				}
				$html_msg['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = tra( 'Please reply to this email if you have any questions.' );

				zen_mail( $this->customer['name'], $this->customer['email_address'], STORE_NAME . ' ' . tra( 'Order Update' ) . ' #' . $this->mOrdersId, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status');

				$customer_notified = '1';
				//send extra emails
				if (SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO != '') {
					zen_mail('', SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . ' #' . $this->mOrdersId, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'order_status_extra');
				}
			}

			$this->mDb->query( "insert into " . TABLE_ORDERS_STATUS_HISTORY . "
						(`orders_id`, `orders_status_id`, `date_added`, `customer_notified`, `comments`, `user_id`)
						values ( ?, ?, ?, ?, ?, ? )", array( $this->mOrdersId, $status, $this->mDb->NOW(), $customer_notified, $comments, $gBitUser->mUserId ) );

			$this->mDb->CompleteTrans();
			$order_updated = true;
		}
		return $order_updated;
	}

	function updateOrder( $pParamHash ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			$this->mDb->associateUpdate( TABLE_ORDERS, $pParamHash, array( 'orders_id' => $this->mOrdersId ) );
			$this->mDb->CompleteTrans();
			$this->load( $this->mOrdersId );
			$ret = TRUE;
		}
		return $ret;
	}

	function changeShipping( $pQuote, $pParamHash ) {
		global $currencies;
		$this->mDb->StartTrans();
		$newTotal = 0;
		foreach( array_keys( $this->totals ) as $k ) {
			if( $this->totals[$k]['class'] == 'ot_shipping' ) {
				$initialShipping = $this->totals[$k];
				$this->totals[$k]['title'] = $pQuote['methods']['module'].' '.$pQuote['methods'][0]['title'];
				$this->totals[$k]['text'] = $pQuote['methods'][0]['format_add_tax'];
				$this->totals[$k]['orders_value'] = $pQuote['methods'][0]['cost_add_tax'];
				$finalShipping = $this->totals[$k];
			}
			if( $this->totals[$k]['class'] == 'ot_total' ) {
				$initialTotal = $this->totals[$k]['orders_value'];
				$totalKey = $k;
			} else {
				$newTotal += $this->totals[$k]['orders_value'];
			}
		}
		if( $initialShipping ) {
			$this->totals[$totalKey]['orders_value'] = $newTotal;
			$this->totals[$totalKey]['text'] = $currencies->format( $newTotal );

			$formatString = tra( "The shipping method was changed from '%s',%s to '%s',%s for a cost change of %s. Previous order total was %s" );
			$message = sprintf( $formatString, $initialShipping['title'], $currencies->format( $initialShipping['orders_value'] ), $finalShipping['title'], $currencies->format( $finalShipping['orders_value'] ), $currencies->format( round( ($newTotal - $initialTotal), 2 ) ), $currencies->format( round( ($initialTotal), 2 ) ) );
			$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `shipping_method_code`=? WHERE `orders_id`=?", array( $pQuote['methods'][0]['code'], $this->mOrdersId ) );
			if( !empty( $_REQUEST['update_totals'] ) ) {
				$this->mDb->query( "UPDATE " . TABLE_ORDERS_TOTAL . " SET `title`=?, `orders_value`=?, `text`=? WHERE `orders_id`=? AND `class`=?", array( $finalShipping['title'], $finalShipping['orders_value'], $finalShipping['text'], $this->mOrdersId, 'ot_shipping' ) );
				$this->mDb->query( "UPDATE " . TABLE_ORDERS_TOTAL . " SET `title`=?, `orders_value`=?, `text`=? WHERE `orders_id`=? AND `class`=?", array( $this->totals[$totalKey]['title'], $this->totals[$totalKey]['orders_value'], $this->totals[$totalKey]['text'], $this->mOrdersId, 'ot_total' ) );
				$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `order_total`=? WHERE `orders_id`=?", array( $newTotal, $this->mOrdersId ) );
			} else {
				$message .= "\n!!! ".tra( 'The order totals were NOT updated.' );
			}

			if( !empty( $pParamHash['comment'] ) ) {
				$message .= "\n--\n".trim( $pParamHash['comment'] );
			}
			$this->updateStatus( array( 'notify' => FALSE , "comments" => $message ) );
		}
		$this->mDb->CompleteTrans();
	}

	function combineOrders( $pParamHash ) {
		global $currencies;
		$ret = FALSE;

		$sql = "SELECT * FROM " . TABLE_ORDERS . " WHERE `orders_id`=?";
		$sourceHash = $this->mDb->getRow( $sql, array( $pParamHash['source_orders_id'] ) );
		$destHash = $this->mDb->getRow( $sql, array( $pParamHash['dest_orders_id'] ) );

		if( empty( $sourceHash ) ) {
			$this->mErrors['combine'] = "Order $pParamHash[source_orders_id] not found";
		} elseif( empty( $destHash ) ) {
			$this->mErrors['combine'] = "Order $pParamHash[dest_orders_id] not found";
		} elseif( $sourceHash['orders_status'] != DEFAULT_ORDERS_STATUS_ID ) {
			$this->mErrors['combine'] = "Order $pParamHash[source_orders_id] does not have status " . zen_get_order_status_name( DEFAULT_ORDERS_STATUS_ID );
		} elseif( $destHash['orders_status'] != DEFAULT_ORDERS_STATUS_ID ) {
			$this->mErrors['combine'] = "Order $pParamHash[dest_orders_id] does not have status " . zen_get_order_status_name( DEFAULT_ORDERS_STATUS_ID );
		} elseif( ($sourceHash['delivery_street_address'] == $destHash['delivery_street_address']) &&
			($sourceHash['delivery_suburb'] == $destHash['delivery_suburb']) &&
			($sourceHash['delivery_city'] == $destHash['delivery_city']) &&
			($sourceHash['delivery_postcode'] == $destHash['delivery_postcode']) &&
			($sourceHash['delivery_state'] == $destHash['delivery_state']) &&
			($sourceHash['delivery_country'] == $destHash['delivery_country']) ) {

			$this->mDb->StartTrans();
			// update new order with combined total and combined tax
			$this->mDb->query( "UPDATE ". TABLE_ORDERS . " SET `order_total`=?, `order_tax`=? WHERE `orders_id`=?", array( ($sourceHash['order_total'] + $destHash['order_total']), ($sourceHash['order_tax'] + $destHash['order_tax']), $pParamHash['dest_orders_id'] ) );

			// Move products and attributes over to new order
			$this->mDb->query( "UPDATE ". TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " SET `orders_id`=? WHERE `orders_id`=?", array( $pParamHash['dest_orders_id'], $pParamHash['source_orders_id'] ) );
			$this->mDb->query( "UPDATE ". TABLE_ORDERS_PRODUCTS . " SET `orders_id`=? WHERE `orders_id`=?", array( $pParamHash['dest_orders_id'], $pParamHash['source_orders_id'] ) );

			if( $rs = $this->mDb->query( "SELECT cot.`orders_total_id`, cot.* FROM " . TABLE_ORDERS_TOTAL . " cot WHERE `orders_id`=?", array( $pParamHash['source_orders_id'] ) ) ) {
				while( $sourceTotal = $rs->fetchRow() ) {
					$destTotal = $this->mDb->getRow( "SELECT `class`, cot.* FROM " . TABLE_ORDERS_TOTAL . " cot WHERE `orders_id`=? AND `class`=?", array( $pParamHash['dest_orders_id'], $sourceTotal['class'] ) );
					if( empty( $destHash['currency'] ) ) {
						$destHash['currency'] = NULL;
						$destHash['currency_value'] = NULL;
					}
					$total = $sourceTotal['orders_value'] + $destTotal['orders_value'];
					$text = $currencies->format( $total, true, $destHash['currency'], $destHash['currency_value'] );
					$this->mDb->query( "UPDATE ". TABLE_ORDERS_TOTAL . " SET `orders_value`=?, `text`=? WHERE `orders_id`=? AND `class`=?", array( $total, $text, $pParamHash['dest_orders_id'], $sourceTotal['class'] ) );
							
				}
			}

			// Move statuses over to 
			$this->mDb->query( "UPDATE ". TABLE_ORDERS_STATUS_HISTORY . " SET `orders_id`=? WHERE `orders_id`=?", array( $pParamHash['dest_orders_id'], $pParamHash['source_orders_id'] ) );
			$this->updateStatus( array( 'notify' => !empty( $pParamHash['combine_notify'] ) , "comments" => "Order $pParamHash[source_orders_id] was combined with this order" ) );
			$delOrder	= new order( $pParamHash['source_orders_id'] );
			$delOrder->expunge();
			$this->mDb->CompleteTrans();
		} else {
			$this->mErrors['combine'] = "Address mismatch. To combine orders, they must have the same delivery address.";
		}

		// no yet implemented
		return empty( $this->mErrors['combine'] );
	}

	function isValid() {
		return( !empty( $this->mOrdersId ) && is_numeric( $this->mOrdersId ) );
	}

	function getDisplayLink() {
		$ret = '';
		if( $this->isValid() ) {
			$ret = '<a href="' .$this->getDisplayUrl() .'">'.str_replace( ':','',tra( 'Detailed Invoice:' ) ).( $this->isvalid() ? ' #'.$this->mOrdersId : '' ).'</a>';
		}
		return $ret;
	}

	function getDisplayUrl( $page = '', $parameters = '', $connection = 'NONSSL') {
		global $gCommerceSystem;
		if( $gCommerceSystem->getConfig( 'ENABLE_SSL_CATALOG' ) == 'true') {
			$link = BITCOMMERCE_PKG_SSL_URI;
		} else {
			$link = BITCOMMERCE_PKG_URI;
		}

		if( $this->isValid() ) {
			$link .= 'index.php?main_page=account_history_info&'.$this->mOrdersId;
		} else {
			$link .= 'index.php?main_page=account_history_info';
		}

		while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

		return $link;
	}



}
?>

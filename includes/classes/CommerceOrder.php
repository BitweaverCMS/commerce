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

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrderBase.php' );

// maintained for backwards compatibility in the code. CommerceOrder should be invoked
class order extends CommerceOrder {

	function __construct( $pOrdersId=NULL ) {
		parent::__construct();

		if( self::verifyId( $pOrdersId ) ) {
			$this->mOrdersId = $pOrdersId;
			$this->load();
		}
	}
}

class CommerceOrder extends CommerceOrderBase {
	public $mOrdersId;
	public $info, $totals, $customer, $content_type, $email_low_stock, $products_ordered_attributes, $products_ordered_email, $mPayments = array();

	function __construct() {
		parent::__construct();
		$this->init();
	}

	protected function init() {
		$this->info = array();
		$this->totals = array();
		$this->subtotal = 0;
		$this->contents = array();
		$this->customer = array();
	}

	// Abstract methods implementation
	public function getDelivery() {
		return $this->delivery;
	}

	public function getBilling() {
		return $this->billing;
	}

	public function getProductHash( $pProductsKey ) {
		return BitBase::getParameter( $this->contents, $pProductsKey );
	}

	// Called at various times. This function calulates the total value of the order that the
	// credit will be appled aginst. This varies depending on whether the credit class applies
	// to shipping & tax
	function getField( $pFieldName, $pDefault = NULL ) {
		$ret = (isset( $this->info[$pFieldName] ) && (!empty( $this->info[$pFieldName] ) || is_numeric( $this->info[$pFieldName] )) ? $this->info[$pFieldName] : $pDefault );
		return $ret;
	}
	public function displayOrderProductData( $opid ) {
		if( $this->isValid() && $this->contents[$opid] ) {
			$ordersProduct = $this->contents[$opid];
			if( $productObject = $this->getProductObject( $ordersProduct['products_id'] ) ) {
				$ordersProductFile = BITCOMMERCE_PKG_PATH.'pages/'.$ordersProduct['type_handler'].'_info/orders_product_inc.php';
				$productObject->displayOrderData( $ordersProduct );
			}
		}
	}

	public static function getList( $pListHash ) {
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
			$whereSql .= ' AND co.`orders_status_id`'.$comparison.'? ';
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
				$whereSql .= " AND LOWER(osh.`comments`) LIKE ? ";
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

		if( !empty( $pListHash['products_type'] ) ) {
			$joinSql .= " INNER JOIN " . TABLE_ORDERS_PRODUCTS . " cop ON(cop.`orders_id`=co.`orders_id`) INNER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`) ";
			$whereSql .= ' AND cp.`products_type` = ?';
			$bindVars[] = $pListHash['products_type'];
		}

		if( !empty( $pListHash['interests_id'] ) ) {
			$joinSql .= " INNER JOIN " . TABLE_CUSTOMERS_INTERESTS_MAP . " cim ON(cim.`customers_id`=co.`customers_id`)
						INNER JOIN " . TABLE_CUSTOMERS_INTERESTS . " ci ON(ci.`interests_id`=cim.`interests_id`) ";
			$whereSql .= ' AND cim.`interests_id` = ?';
			$bindVars[] = $pListHash['interests_id'];
		}

		if( !empty( $pListHash['period'] ) && !empty( $pListHash['timeframe'] ) ) {
			$whereSql .= ' AND '.$gBitDb->SQLDate( $pListHash['period'], '`date_purchased`' ).' = ?';
			$bindVars[] = $pListHash['timeframe'];
		}

		if( $gBitSystem->isPackageActive( 'stats' ) ) {
			$selectSql .= " , sru.`referer_url` ";
			$joinSql .= " LEFT JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`user_id`=uu.`user_id`) 
							LEFT JOIN `".BIT_DB_PREFIX."stats_referer_urls` sru ON (sru.`referer_url_id`=srum.`referer_url_id`) ";
		}

		if( !empty( $whereSql ) ) {
			$whereSql =  preg_replace('/^ AND /',' WHERE ', $whereSql);
		}

		$query = "SELECT co.`orders_id` AS `hash_key`, ot.`text` AS `order_total`, co.*, uu.*, os.*, ".$gBitDb->SQLDate( 'Y-m-d H:i', 'co.`date_purchased`' )." AS `purchase_time` $selectSql
					FROM " . TABLE_ORDERS . " co
						INNER JOIN " . TABLE_ORDERS_STATUS . " os ON(co.`orders_status_id`=os.`orders_status_id`)
						INNER JOIN `" . BIT_DB_PREFIX . "users_users` uu ON(co.`customers_id`=uu.`user_id`)
					$joinSql
						LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot on (co.`orders_id` = ot.`orders_id` AND `class` = 'ot_total')
					$whereSql
					ORDER BY ".$gBitDb->convertSortmode( $pListHash['sort_mode'] );
		if( $rs = $gBitDb->query( $query, $bindVars, $pListHash['max_records'] ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[$row['orders_id']] = $row;
				if( !empty( $pListHash['recent_comment'] ) ) {
					if( $lastComment = $gBitDb->getRow( "SELECT *, ".$gBitDb->SQLDate( 'Y-m-d H:i', '`date_added`' )." as comments_time FROM " . TABLE_ORDERS_STATUS_HISTORY . " osh WHERE osh.`orders_id`=? AND `comments` IS NOT NULL ORDER BY `orders_status_history_id` DESC", array( $row['orders_id'] ) ) ) {
						$ret[$row['orders_id']]['comments_time'] = $lastComment['comments_time'];
						$ret[$row['orders_id']]['comments'] = $lastComment['comments'];
					}
				}
				if( !empty( $pListHash['orders_products'] ) ) {
					$sql = "SELECT cop.`orders_products_id` AS `hash_key`, cp.*, cop.*
							FROM " . TABLE_ORDERS_PRODUCTS . " cop
								INNER JOIN " . TABLE_PRODUCTS . " cp ON(cp.`products_id`=cop.`products_id`)
							WHERE cop.`orders_id`=?";
					$ret[$row['orders_id']]['products'] = $gBitDb->getAssoc( $sql, array( $row['orders_id'] ) );

					$sql = "SELECT copa.`orders_products_attributes_id` AS `hash_key`, copa.*
							FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " copa
							WHERE copa.`orders_id`=?";
					$orderAttributes = $gBitDb->getAssoc( $sql, array( $row['orders_id'] ) );
					foreach( array_keys( $orderAttributes ) as $ordersProductsAttId ) {
						$ret[$row['orders_id']]['products'][$orderAttributes[$ordersProductsAttId]['orders_products_id']]['attributes'][$orderAttributes[$ordersProductsAttId]['products_options_values_id']] = $orderAttributes[$ordersProductsAttId]['products_options_values_name'];
						
					}
				}
			}
		}

		return( $ret );
	}

	/**
	 * Create an export hash from the data
	 *
	 * @access public
	 * @return export data
	 */
	function exportHash() {
		$ret = array();
		if( $this->isValid() ) {
			$ret = array_merge( array( 'orders_id' => $this->mOrdersId, 'info' => $this->info), array( 'delivery' => $this->delivery ), array( 'billing' => $this->billing ) );
		}
		return $ret;
	}

	protected function load() {
		global $gBitSystem;
		$ret = FALSE;

		if( $this->isValid() ) {
			$selectSql = '';
			$joinSql = '';

			if( $gBitSystem->isPackageActive( 'stats' ) ) {
				$selectSql .= " , sru.`referer_url` ";
				$joinSql .= " LEFT JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`user_id`=uu.`user_id`) 
								LEFT JOIN `".BIT_DB_PREFIX."stats_referer_urls` sru ON (sru.`referer_url_id`=srum.`referer_url_id`) ";
			}

			$order_query = "SELECT co.*, uu.* $selectSql
							FROM " . TABLE_ORDERS . " co
								INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(uu.`user_id`=co.`customers_id`)
								$joinSql
							WHERE co.`orders_id` = ?";
			$order = $this->mDb->query( $order_query, array( $this->mOrdersId ) );
			if( $order->RecordCount() ) {
				$totals_query = "SELECT `title`, `text`, `class`, `orders_value` FROM " . TABLE_ORDERS_TOTAL . " where `orders_id`=? ORDER BY `sort_order`";
				$totals = $this->mDb->query($totals_query, array( $this->mOrdersId ) );

				while (!$totals->EOF) {
					$this->totals[] = array('title' => $totals->fields['title'],
											'text' => $totals->fields['text'],
											'class' => $totals->fields['class'],
											'orders_value' => $totals->fields['orders_value']);
					$totals->MoveNext();
				}

				$this->mPayments = $this->mDb->getAssoc( "SELECT `orders_payments_id`, * FROM " . TABLE_ORDERS_PAYMENTS . " copay LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=copay.`user_id` ) WHERE `orders_id`=? ORDER BY payment_date", array(  $this->mOrdersId ) ); 

				$this->info = array('currency' => $order->fields['currency'],
									'currency_value' => $order->fields['currency_value'],
									'payment_method' => $order->fields['payment_method'],
									'payment_module_code' => $order->fields['payment_module_code'],
									'shipping_method' => $order->fields['shipping_method'],
									'shipping_method_code' => $order->fields['shipping_method_code'],
									'shipping_module_code' => $order->fields['shipping_module_code'],
									'shipping_tracking_number' => $order->fields['shipping_tracking_number'],
									'deadline_date' => $order->fields['deadline_date'],
									'estimated_ship_date' => $order->fields['estimated_ship_date'],
									'estimated_arrival_date' => $order->fields['estimated_arrival_date'],
									'coupon_code' => $order->fields['coupon_code'],
									'payment_type' => $order->fields['payment_type'],
									'payment_owner' => $order->fields['payment_owner'],
									'payment_number' => $order->fields['payment_number'],
									'payment_expires' => $order->fields['payment_expires'],
									'date_purchased' => $order->fields['date_purchased'],
									'orders_status_id' => $order->fields['orders_status_id'],
									'orders_status_name' => zen_get_order_status_name( $order->fields['orders_status_id'] ),
									'last_modified' => $order->fields['last_modified'],
									'total' => $order->fields['order_total'],
									'amount_due' => $order->fields['amount_due'],
									'tax' => $order->fields['order_tax'],
									'ip_address' => $order->fields['ip_address']
									);

				$this->info['shipping_cost'] =	$this->mDb->getOne( "SELECT `orders_value` AS `shipping_cost` FROM " . TABLE_ORDERS_TOTAL . " WHERE `orders_id` = ? AND class = 'ot_shipping'", array( $this->mOrdersId ) );

				$this->customer = array('customers_id' => $order->fields['customers_id'],
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
										'countries_name' => $order->fields['customers_country'],
										'format_id' => $order->fields['customers_address_format_id'],
										'telephone' => $order->fields['customers_telephone'],
										'email_address' => $order->fields['email']); // 'email' comes from users_users, which is always most current

				if( !empty( $order->fields['referer_url'] ) ) {
					$this->customer['referer_url'] = $order->fields['referer_url'];
				}

				foreach( array( 'delivery', 'billing' ) as $addressType ) {
					$this->$addressType = array_merge(
										array ( 'name' => $order->fields[$addressType.'_name'],
												'company' => $order->fields[$addressType.'_company'],
												'street_address' => $order->fields[$addressType.'_street_address'],
												'suburb' => $order->fields[$addressType.'_suburb'],
												'city' => $order->fields[$addressType.'_city'],
												'postcode' => $order->fields[$addressType.'_postcode'],
												'state' => $order->fields[$addressType.'_state'],
												'telephone' => $order->fields[$addressType.'_telephone'],
												'format_id' => $order->fields[$addressType.'_address_format_id'] ),
									  );
					if( $order->fields[$addressType.'_country'] ) {
						$this->$addressType = array_merge( $this->$addressType, zen_get_countries( $order->fields[$addressType.'_country'] ) );
					}

					if( $order->fields[$addressType.'_state'] ) {
						$this->$addressType = array_merge( $this->$addressType,  zen_get_zone_by_name( $this->$addressType['countries_id'], $order->fields[$addressType.'_state'] ) );
					}

					if( strpos( $this->$addressType['name'], ' ' ) ) {
						list( $this->$addressType['first_name'], $this->$addressType['last_name'] ) = explode( ' ', $this->$addressType['name'],  2 );
					}
				}

				// nullify street address if empty
				if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
					$this->delivery = false;
				}

				$orders_products_query = 	"SELECT op.*, pt.*, p.content_id, p.related_content_id, lc.user_id
											FROM " . TABLE_ORDERS_PRODUCTS . " op
												LEFT OUTER JOIN	" . TABLE_PRODUCTS . " p ON ( op.`products_id`=p.`products_id` )
												LEFT OUTER JOIN	" . TABLE_PRODUCT_TYPES . " pt ON ( p.`products_type`=pt.`type_id` )
												LEFT OUTER JOIN	`" . BIT_DB_PREFIX . "liberty_content` lc ON ( lc.`content_id`=p.`content_id` )
											WHERE `orders_id` = ?
											ORDER BY op.`orders_products_id`";
				$orders_products = $this->mDb->query( $orders_products_query, array( $this->mOrdersId ) );

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
					if( $attributes = $this->mDb->getArray( $attributes_query, array( $this->mOrdersId, $orders_products->fields['orders_products_id'] ) ) ) {
						foreach( $attributes as $attribute ) {
							$this->contents[$productsKey]['attributes'][] = array( 'products_options_id' => $attribute['products_options_id'],
																					'products_options_values_id' => $attribute['products_options_values_id'],
																					'products_options_name' => $attribute['products_options_name'],
																					'products_options_values_name' => $attribute['products_options_values_name'],
																					'price_prefix' => $attribute['price_prefix'],
																					'final_price' => $this->getOrderAttributePrice( $attribute, $this->contents[$productsKey] ),
																					'price' => $attribute['options_values_price'],
																					'orders_products_attributes_id' => $attribute['orders_products_attributes_id'] );

						}
					}

					$this->info['tax_groups']["{$this->contents[$productsKey]['tax']}"] = '1';

					$orders_products->MoveNext();
				}
				$ret = TRUE;
			}
		}
		return $ret;
	}

	function hasViewPermission() {
		global $gBitUser;
		$ret = FALSE;
		if( $this->isValid() && self::verifyIdParameter( $this->customer, 'user_id' ) ) {
			$ret = ($gBitUser->mUserId == $this->customer['user_id']) || $gBitUser->hasPermission( 'p_bitcommerce_admin' );
		}
		return $ret;
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
							$productAttributes[$attribute['products_options_id']] = $attribute['products_options_values_id'];
						}
					}

					$productsTotal = $this->contents[$productsKey]['price'] * $qty;
					$productWeight = $product->getWeight( $qty, $productAttributes );

					// shipping adjustments
					if (($product->getField('product_is_always_free_ship') == 1) || $product->isVirtual( $this->contents[$productsKey] ) || (preg_match('/^GIFT/', addslashes($product->getField('products_model'))))) {
						$this->free_shipping_item += $qty;
						$this->free_shipping_price += $this->contents[$productsKey]['price'] * $qty;
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

	function expunge( $pRestock=FALSE ) {
		global $gBitProduct;
		$ret = NULL;

		if( BitBase::verifyId( $this->mOrdersId ) ) {
			$this->StartTrans();
			if ($pRestock == 'on') {
				if( $products = $this->mDb->getAssoc("SELECT `products_id`, `products_quantity` FROM " . TABLE_ORDERS_PRODUCTS . " WHERE `orders_id` = ?", array( $this->mOrdersId ) ) ) {
					foreach( $products AS $productsId=>$productsQuantity	) {
						$this->mDb->query("update " . TABLE_PRODUCTS . " set `products_quantity` = `products_quantity` + ?, `products_ordered` = `products_ordered` - ? WHERE `products_id` = ?", array( $productsQuantity, $productsQuantity, $productsId ) );
					}
				}
			}

			$gBitProduct->invokeServices( 'commerce_expunge_order_function', $this );

			$this->mDb->query("DELETE FROM " . TABLE_COUPON_REDEEM_TRACK . " WHERE `order_id` = ?", array( $this->mOrdersId ) );
			$this->mDb->query("DELETE FROM " . TABLE_COUPON_GV_QUEUE . " WHERE `order_id` = ?", array( $this->mOrdersId ) );
			$this->mDb->query("DELETE FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$this->mDb->query("DELETE FROM " . TABLE_ORDERS_PRODUCTS . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$this->mDb->query("DELETE FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$this->mDb->query("DELETE FROM " . TABLE_ORDERS_TOTAL . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );
			$this->mDb->query("DELETE FROM " . TABLE_ORDERS . " WHERE `orders_id` = ?", array( $this->mOrdersId ) );

			$this->CompleteTrans();
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
					FROM " . TABLE_ORDERS_STATUS_HISTORY . " osh 
						INNER JOIN " . TABLE_ORDERS_STATUS . " os ON( osh.`orders_status_id` = os.`orders_status_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=osh.`user_id` )
					WHERE osh.`orders_id` = ?
					ORDER BY osh.`date_added`";

			if( $rs = $this->mDb->query($sql, array( $this->mOrdersId ) ) ) {
				while( !$rs->EOF ) {
					array_push( $this->mHistory, $rs->fields );
					$rs->MoveNext();
				}
			}
		}
		return( count( $this->mHistory ) );
	}

	public static function orderFromCart( $pCart, &$pSessionParams ) {
		$ret = new order();
		$ret->loadFromCart( $pCart, $pSessionParams );
		return $ret;
	}

	private function loadFromCart( $pCart, &$pSessionParams ) {
		global $currencies, $gBitUser, $gBitCustomer, $gCommerceSystem;
		$this->content_type = $pCart->get_content_type();

		$shippingAddress = $pCart->getDelivery();
		$billingAddress = $pCart->getBilling();

		$taxAddress = zen_get_tax_locations();

		$coupon_code = NULL;
		if( !empty( $pSessionParams['cc_id'] ) ) {
			$coupon_code_query = "SELECT `coupon_code` FROM " . TABLE_COUPONS . " WHERE `coupon_id` = ?";
			$coupon_code = $this->mDb->GetOne($coupon_code_query, array( (int)$pSessionParams['cc_id'] ) );
		} elseif( !empty( $pSessionParams['dc_redeem_code'] ) ) {
			$coupon_code = $pSessionParams['dc_redeem_code'];
		}

		if( !empty( $pSessionParams['shipping_quote'] ) ) {
			// load all enabled shipping modules
			require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceShipping.php');
			global $gCommerceShipping;
			list($module, $method) = explode('_', $pSessionParams['shipping_quote'], 2);
			$quote = $gCommerceShipping->quote( $pCart, $method, $module);

			if( isset( $quote['error'] ) || !($quoteHash = $gCommerceShipping->quoteToHash( $quote )) ) {
				$pSessionParams['shipping'] = '';
				$this->mErrors['shipping'] = 'Could not quote shipping method: '.$pSessionParams['shipping_quote'].' ['.BitBase::getParameter( $quote, 'error', 'Unknown quote error' ).']';
			} else {
				$pSessionParams['shipping'] = $quoteHash;
			}
		}

		$this->info = array('orders_status_id' => DEFAULT_ORDERS_STATUS_ID, // may be adjusted below
							'currency' => !empty( $pSessionParams['currency'] ) ? $pSessionParams['currency'] : NULL,
							'currency_value' => !empty( $pSessionParams['currency'] ) ? $currencies->currencies[$pSessionParams['currency']]['currency_value'] : NULL,
							'payment_method' => '',
							'payment_module_code' => '',
							'coupon_code' => $coupon_code,
							'shipping_method' => !empty( $pSessionParams['shipping']['title'] ) ? $pSessionParams['shipping']['title'] : '',
							'shipping_method_code' => !empty( $pSessionParams['shipping']['code'] ) ? $pSessionParams['shipping']['code'] : '',
							'shipping_module_code' => !empty( $pSessionParams['shipping']['id'] ) ? $pSessionParams['shipping']['id'] : '',
							'shipping_cost' => !empty( $pSessionParams['shipping']['cost'] ) ? $pSessionParams['shipping']['cost'] : 0,
							'estimated_ship_date' => !empty( $pSessionParams['shipping']['ship_date'] ) ? $pSessionParams['shipping']['ship_date'] : NULL,
							'estimated_arrival_date' => !empty( $quote[0]['methods'][0]['delivery_date'] ) ? $quote[0]['methods'][0]['delivery_date'] : NULL,
							'deadline_date' => $this->getParameter( $pSessionParams, 'deadline_date', NULL ),
							'subtotal' => 0,
							'tax' => 0,
							'total' => 0,
							'tax_groups' => array(),
							'comments' => $this->getParameter( $pSessionParams, 'comments' ),
							'ip_address' => $_SERVER['REMOTE_ADDR']
							);

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
									'format_id' => $defaultAddress['address_format_id'],
									'telephone' => $defaultAddress['entry_telephone'],
									'email_address' => $gBitUser->getField( 'email' )
									 );
		} else {
			$this->customer = array('firstname' => $gBitCustomer->getField( 'customers_firstname' ),
									'lastname' => $gBitCustomer->getField( 'customers_lastname' ),
									'user_id' => $gBitCustomer->getField( 'customers_id' ),
									'email_address' => $gBitCustomer->getField('customers_email_address' ),
									'customers_id' => $gBitCustomer->mCustomerId
									);
		}

		if( !empty( $shippingAddress ) ) {
			$this->delivery = array('firstname' => $shippingAddress['firstname'],
									'lastname' => $shippingAddress['lastname'],
									'company' => $this->getParameter( $shippingAddress, 'company' ),
									'street_address' => $shippingAddress['street_address'],
									'suburb' => $this->getParameter( $shippingAddress, 'suburb' ),
									'city' => $shippingAddress['city'],
									'postcode' => $shippingAddress['postcode'],
									'state' => (!empty( $shippingAddress['state'] ) ? $shippingAddress['state'] : NULL),
									'zone_id' => BitBase::getParameter( $shippingAddress, 'zone_id', NULL),
									'countries_id' => $shippingAddress['countries_id'],
									'countries_name' => $shippingAddress['countries_name'],
									'countries_iso_code_2' => $shippingAddress['countries_iso_code_2'],
									'countries_iso_code_3' => $shippingAddress['countries_iso_code_3'],
									'country_id' => $shippingAddress['country_id'],
									'telephone' => $this->getParameter( $shippingAddress, 'telephone' ),
									'format_id' => $this->getParameter( $shippingAddress, 'address_format_id' ));
		}

		if( !empty( $billingAddress ) ) {
			$this->billing = array('firstname' => $billingAddress['firstname'],
									'lastname' => $billingAddress['lastname'],
									'company' => $this->getParameter( $billingAddress, 'company', NULL ),
									'street_address' => $billingAddress['street_address'],
									'suburb' => $this->getParameter( $billingAddress, 'suburb', NULL ),
									'city' => $billingAddress['city'],
									'postcode' => $billingAddress['postcode'],
									'state' => (!empty( $billingAddress['state'] ) ? $billingAddress['state'] : ''),
									'zone_id' => $billingAddress['zone_id'],
									'countries_id' => $billingAddress['countries_id'],
									'countries_name' => $billingAddress['countries_name'],
									'countries_iso_code_2' => $billingAddress['countries_iso_code_2'],
									'countries_iso_code_3' => $billingAddress['countries_iso_code_3'],
									'country_id' => $billingAddress['country_id'],
									'telephone' => $this->getParameter( $billingAddress, 'telephone' ),
									'format_id' => $this->getParameter( $billingAddress, 'address_format_id' ));
		}

		foreach( array_keys( $pCart->contents ) as $cartItemKey ) {
			if( $productHash = $pCart->getProductHash( $cartItemKey ) ) {
				$this->contents[$cartItemKey] = $productHash;
				if( !empty( $taxAddress ) ) {
					$this->contents[$cartItemKey]['tax'] = zen_get_tax_rate( $this->contents[$cartItemKey]['tax_class_id'], $taxAddress['country_id'], $taxAddress['zone_id'] );
					$this->contents[$cartItemKey]['tax_description'] = zen_get_tax_description( $this->contents[$cartItemKey]['tax_class_id'], $taxAddress['country_id'], $taxAddress['zone_id'] );
				}

				if ( !empty( $this->contents[$cartItemKey]['attributes'] ) ) {
					$attributes	= $this->contents[$cartItemKey]['attributes'];
					$this->contents[$cartItemKey]['attributes'] = array();
					$subindex = 0;
					foreach( $attributes as $optionKey=>$valueHash ) {
						list( $optionId, $keyValueId ) = explode( '=', $optionKey );
						$this->contents[$cartItemKey]['attributes'][$subindex] = zen_get_option_value( $optionId, (int)$valueHash['products_options_values_id'] );
						// Determine if attribute is a text attribute and change products array if it is.
	if( empty( $this->contents[$cartItemKey]['attributes'][$subindex]['products_options_values_name'] ) ) { eb( 'EMTPY', $attributes,  $this->contents[$cartItemKey]['attributes']  ); }
						$attrValue = $this->contents[$cartItemKey]['attributes'][$subindex]['products_options_values_name']; 
						if( !empty( $valueHash['products_options_values_text'] ) ) {
							$this->contents[$cartItemKey]['attributes'][$subindex]['products_options_values_text'] = $valueHash['products_options_values_text'];
						}
						$this->contents[$cartItemKey]['attributes'][$subindex]['value'] = $attrValue;
						$subindex++;
					}
				}

				$shown_price = (zen_add_tax($this->contents[$cartItemKey]['final_price'], $this->contents[$cartItemKey]['tax']) * $this->contents[$cartItemKey]['products_quantity'])
								+ zen_add_tax($this->contents[$cartItemKey]['onetime_charges'], $this->contents[$cartItemKey]['tax']);
				$this->subtotal += $shown_price;

				$products_tax = $this->contents[$cartItemKey]['tax'];
				$products_tax_description = $this->contents[$cartItemKey]['tax_description'];
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
		}

		$this->info['total'] = (double)$this->subtotal + $this->getField( 'shipping_cost', 0.0 );

		if (DISPLAY_PRICE_WITH_TAX != 'true') {
			$this->info['total'] += $this->info['tax'];
		}

		if ($this->info['total'] == 0 && ($zeroBalanceStatusId = $gCommerceSystem->getConfig( 'DEFAULT_ZERO_BALANCE_ORDERS_STATUS_ID' )) ) {
			$this->info['orders_status_id'] = $zeroBalanceStatusId ;
		}

		return count( $this->mErrors ) == 0;
	}

	public function getNextOrderId() {
		$ret = NULL;

		do {
			$ret = $this->mDb->GenID( 'com_orders_orders_id_seq' );
		} while( $this->mDb->getOne( "SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id=?", array( $ret ) ) );

		return $ret;
	}

	function getPaymentModule() {
		if( $this->isValid() ) {
			return $this->loadPaymentModule( $this->info['payment_module_code'] );
		}
	}

	function loadPaymentModule( $pModule ) {
		return CommerceSystem::loadModule( 'payment', $pModule );
	}

	function process( $pRequestParams, &$pSessionParams ) {
		$ret = FALSE;
		// load selected payment module
		require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePaymentManager.php' );
		$paymentManager = new CommercePaymentManager( BitBase::getParameter( $pSessionParams, 'payment_method') );
		// Order Totals may effect processing, e.g. group pricing, coupons, expedite, etc.
		$this->otProcess( $pRequestParams, $pSessionParams );
		// Mush together request and Session data and send it for payment processing
		$paymentParams = array_merge( $pRequestParams, $pSessionParams );
		if( !$this->hasPaymentDue( $paymentParams ) || (!empty( $paymentParams['payment_method'] ) && $paymentManager->processPayment( $this, $paymentParams, $pSessionParams )) ) {
			// Set Payment status
			$this->info['payment_method'] = $paymentParams['payment_method'];
			$this->info['payment_module_code'] = BitBase::getParameter( $paymentParams, 'payment_module_code' );
			$this->info['orders_status_id'] = BitBase::getParameter( $paymentParams, 'processed_orders_status_id' );

			$newOrderId = $this->create( $paymentParams, $pSessionParams );
			$paymentParams['result']['orders_id'] = $this->mOrdersId;
			if( $this->hasPaymentDue( $paymentParams ) ) {
				$paymentManager->storeOrdersPayment( $paymentParams['result'], $this);
			}

			$paymentManager->after_order_create( $newOrderId, $this );

			if( empty( $pRequestParams['no_order_email'] ) ) {
				$this->sendOrderEmail();
			}
/*
				global $currencies;
				$paymentModule = $this->getPaymentModule();
				$adjustmentText = tra( 'Order Payment' ).' - '.date('Y-m-d H:i');
				$formatCharge = $currencies->format( $pSessionParams['charge_amount'], FALSE, BitBase::getParameter( $pSessionParams, 'charge_currency' ) );
//				$statusMsg = trim( $statusMsg."\n\n".tra( 'A payment has been made to this order for the following amount:' )."\n".$formatCharge );
//				$statusMsg .= "\n\n".tra( 'Transaction ID:' )." ".$paymentModule->getTransactionReference();
				$adjustmentText .= ' - '.tra( 'Transaction ID:' )." ".$paymentModule->getTransactionReference();

				$otPayment['orders_id'] = $newOrderId;
				$otPayment['class'] = 'ot_payment';
				$otPayment['title'] = tra( 'Order Payment' );
				$otPayment['text'] = $adjustmentText;
				$otPayment['sort_order'] = $paymentModule->getSortOrder() + 1;
				$otPayment['orders_value'] = $pSessionParams['charge_amount'];
				$this->mDb->associateInsert( TABLE_ORDERS_TOTAL, $otPayment );
*/
			$ret = TRUE;
		} else {
			$this->mErrors = array_merge( $this->mErrors, $paymentManager->mErrors );
		}

		return $ret;
	}

	function getDeductionTotal() {
		$ret = 0;

		if( !empty( $this->info['deductions'] ) ) {
			foreach( array_keys( $this->info['deductions'] ) as $otClass ) {
				foreach( $this->info['deductions'][$otClass] as $key=>$amount ) {
					$ret += $amount;
				}
			}
		}

		return $ret;
	}

	protected function create( $pPaymentParams, $pSessionParams ) {
		global $gBitCustomer;

		$this->StartTrans();

		if( !empty( $pSessionParams['shipping_quote'] ) && $pSessionParams['shipping_quote'] == 'free_free') {
			$this->info['shipping_module_code'] = $pSessionParams['shipping_quote'];
		}

		$this->mOrdersId =  (!empty( $pPaymentParams['orders_id'] ) ? $pPaymentParams['orders_id'] : $this->getNextOrderId());

		$sql_data_array = array('orders_id' => $this->mOrdersId,
							'customers_id' => $gBitCustomer->mCustomerId,
/* TOODO 2016-DEC-15 - spiderr - data is unpopulated
							'customers_company' => $this->customer['company'],
							'customers_street_address' => $this->customer['street_address'],
							'customers_suburb' => $this->customer['suburb'],
							'customers_city' => $this->customer['city'],
							'customers_postcode' => $this->customer['postcode'],
							'customers_state' => $this->customer['state'],
							'customers_country' => $this->customer['countries_name'],
							'customers_telephone' => $this->customer['telephone'],
							'customers_address_format_id' => $this->customer['format_id'],
*/
							'customers_name' => trim( $this->customer['firstname'] . ' ' . $this->customer['lastname'] ),
							'customers_email_address' => $this->customer['email_address'],
							'delivery_name' => $this->delivery['firstname'] . ' ' . $this->delivery['lastname'],
							'delivery_company' => $this->delivery['company'],
							'delivery_street_address' => $this->delivery['street_address'],
							'delivery_suburb' => $this->delivery['suburb'],
							'delivery_city' => $this->delivery['city'],
							'delivery_postcode' => $this->delivery['postcode'],
							'delivery_state' => $this->delivery['state'],
							'delivery_country' => $this->delivery['countries_name'],
							'delivery_telephone' => $this->delivery['telephone'],
							'delivery_address_format_id' => $this->delivery['format_id'],
							'billing_name' => $this->billing['firstname'] . ' ' . $this->billing['lastname'],
							'billing_company' => $this->billing['company'],
							'billing_street_address' => $this->billing['street_address'],
							'billing_suburb' => $this->billing['suburb'],
							'billing_city' => $this->billing['city'],
							'billing_postcode' => $this->billing['postcode'],
							'billing_state' => $this->billing['state'],
							'billing_country' => $this->billing['countries_name'],
							'billing_telephone' => $this->billing['telephone'],
							'billing_address_format_id' => $this->billing['format_id'],
							'shipping_method' => $this->info['shipping_method'],
							'shipping_method_code' => $this->info['shipping_method_code'],
							'shipping_module_code' => (strpos($this->info['shipping_module_code'], '_') > 0 ? substr($this->info['shipping_module_code'], 0, strpos($this->info['shipping_module_code'], '_')) : $this->info['shipping_module_code']),
							'estimated_arrival_date' => $this->info['estimated_arrival_date'],
							'estimated_ship_date' => $this->info['estimated_ship_date'],
							'coupon_code' => $this->info['coupon_code'],
							'date_purchased' => $this->mDb->NOW(),
							'orders_status_id' => $this->getField( 'orders_status_id', DEFAULT_ORDERS_STATUS_ID ),
							'order_total' => $this->info['total'],
							'order_tax' => $this->info['tax'],
							'amount_due' => BitBase::getParameter( $this->info, 'amount_due', NULL ),
							'currency' => $this->info['currency'],
							'currency_value' => $this->info['currency_value'],
							'payment_method' => $this->info['payment_method'],
							'payment_module_code' => $this->info['payment_module_code'],
							'ip_address' => $_SERVER['REMOTE_ADDR']
							);

		if( $deadlineDate = BitBase::getParameter( $pPaymentParams, 'deadline_date', BitBase::getParameter( $pSessionParams, 'deadline_date', NULL ) ) ) {
			$sql_data_array['deadline_date'] = $deadlineDate;
		}

		if( !empty( $sql_data_array['deadline_date'] ) ) {	
			list($yyyy,$mm,$dd) = explode( '-', $sql_data_array['deadline_date'] );
			if( !checkdate( $mm,$dd,$yyyy ) ) {
				$sql_data_array['deadline_date'] = NULL;
			} else {
				$date = strtotime( $sql_data_array['deadline_date'] );
				// clear deadline if not between now and +2 years
				if( ($date < time()) || ($date > (time() + 63072000)) ) {
					$sql_data_array['deadline_date'] = NULL;
				} else {
					// Ensure database safe format
					$sql_data_array['deadline_date'] = date( 'Y-m-d', $date );
				}
			}
		}
/*
		if( $paymentModule = $this->loadPaymentModule( BitBase::getParameter( $pPaymentParams, 'payment' ) ) ) {
			$sql_data_array['payment_number'] = $paymentModule->getPaymentNumber( $pPaymentParams, TRUE );
			$sql_data_array['payment_expires'] = $paymentModule->getPaymentExpires( $pPaymentParams );
			$sql_data_array['payment_type'] = $paymentModule->getPaymentType( $pPaymentParams );
			$sql_data_array['payment_owner'] = $paymentModule->getPaymentOwner( $pPaymentParams );
			$sql_data_array['payment_method'] = $paymentModule->title;
			$sql_data_array['payment_module_code'] = $paymentModule->code;
		}
*/
		$this->mDb->associateInsert(TABLE_ORDERS, $sql_data_array);

		$this->CompleteTrans();

		$this->StartTrans();
		$this->otApplyCredit( $pSessionParams );
		$this->CompleteTrans();

		$this->StartTrans();

		foreach( array_keys( $this->mOtProcessModules ) as $key ) {
			if( $this->mOtProcessModules[$key]['code'] == 'ot_total' ) {
				if( $this->mOtProcessModules[$key]['value'] != $this->info['total'] ) {
					// discounting or credits affected final order_total
					$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `order_total`=? WHERE `orders_id`=?", array( $this->mOtProcessModules[$key]['value'], $this->mOrdersId ) );
				}
			}
			$sqlParams = array( 'orders_id' => $this->mOrdersId,
								'title' => $this->mOtProcessModules[$key]['title'],
								'text' => $this->mOtProcessModules[$key]['text'],
								'orders_value' => (is_numeric( $this->mOtProcessModules[$key]['value'] ) ? $this->mOtProcessModules[$key]['value'] : 0),
								'class' => $this->mOtProcessModules[$key]['code'],
								'sort_order' => $this->mOtProcessModules[$key]['sort_order'] );
			$this->mDb->associateInsert(TABLE_ORDERS_TOTAL, $sqlParams );
		}

		$customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
		$sqlParams = array( 'orders_id' => $this->mOrdersId,
							'orders_status_id' => $this->info['orders_status_id'],
							'user_id' => $gBitCustomer->mCustomerId,
							'date_added' => $this->mDb->NOW(),
							'customer_notified' => $customer_notification,
							'comments' => $this->info['comments'] );
		$this->mDb->associateInsert(TABLE_ORDERS_STATUS_HISTORY, $sqlParams );

		$this->createAddProducts( $this->mOrdersId );

		foreach( array_keys( $this->contents ) as $cartItemKey ) {
			if( $addProduct = CommerceProduct::getCommerceObject( array( 'products_id' => $this->contents[$cartItemKey]['products_id'] ) ) ) {
				$addProduct->productPurchased( $this, $this->contents[$cartItemKey] );
			}
		}
		$this->CompleteTrans();

		return( $this->mOrdersId );
	}

	public function adjustOrder( &$pPaymentParams, &$pSessionParams ) {

		$ret = TRUE;
		$adjustmentText = tra( 'Order Payment Adjustment' ).' - '.date('Y-m-d H:i');
		$statusMsg = BitBase::getParameter( $pPaymentParams, 'comments' );

		global $currencies, $messageStack;
		if( !empty( $pPaymentParams['charge_amount'] ) ) {
			$formatCharge = $currencies->format( $pPaymentParams['charge_amount'], FALSE, BitBase::getParameter( $pPaymentParams, 'charge_currency' ) );
			$statusMsg = trim( $statusMsg."\n\n".tra( 'A payment adjustment has been made to this order for the following amount:' )."\n".$formatCharge );

			if( !empty( $pPaymentParams['additional_charge'] ) ) { 
				if( $paymentModule = $this->getPaymentModule() ) {
//					$pPaymentParams['payment_ref_id'] = $this->info['payment_ref_id'];
					// Mush together request and Session data and send it for payment processing
					$paymentParams = array_merge( $pPaymentParams, $pSessionParams );
					if( $paymentModule->processPayment( $this, $paymentParams, $pSessionParams ) ) {
						$statusMsg .= "\n\n".tra( 'Transaction ID:' )." ".$paymentModule->getTransactionReference();
						$adjustmentText .= ' - '.tra( 'Transaction ID:' )." ".$paymentModule->getTransactionReference();
						$pPaymentParams['comments'] = (!empty( $pPaymentParams['comments'] ) ? $pPaymentParams['comments']."\n\n" : '').$statusMsg;
						
					} else {
						$statusMsg = tra( 'Additional charge could not be made:' ).' '.$formatCharge.'<br/>'.implode( '<br/>', $paymentModule->mErrors );
						$ret = FALSE;
						$messageStack->add_session( $statusMsg, 'error');
						$this->updateStatus( array( 'comments' => $statusMsg ) );
					}
				} else {
					$statusMsg = tra( 'Payment Module could not be loaded.' ).' ('.$this->info['payment_module_code'].')';
					$ret = FALSE;
					$messageStack->add_session( $statusMsg, 'error');
				}
			}
		}

		if( $statusMsg || (BitBase::getParameter( $pPaymentParams, 'status' ) != $this->getField( 'orders_status_id' )) ) {
			$pPaymentParams['comments'] = $statusMsg;
			$this->updateStatus( $pPaymentParams );
		}

		if( $ret ) {
			if( $adjustTotal = (BitBase::getParameter( $pPaymentParams, 'adjust_total' ) == 'y') ) {
				// discounting or credits affected final order_total
				$newTotal = (float)BitBase::getParameter( $pPaymentParams, 'charge_amount', 0 ) + (float)$this->getField( 'total' );
				$maxSortOrder = $this->mDb->getOne( "SELECT MAX(sort_order) + 1 FROM " . TABLE_ORDERS_TOTAL . " WHERE `orders_id`=? ", array( $this->mOrdersId ) );
				$this->mDb->query( "UPDATE " . TABLE_ORDERS_TOTAL . " SET `class`=?, title=? WHERE `orders_id`=? AND class='ot_total'", array( 'ot_subtotal', 'Previous Total', $this->mOrdersId ) );
				$sqlParams = array( 'orders_id' => $this->mOrdersId,
									'title' => $adjustmentText, //$this->mOtClasses[$key]->title,
									'text' => $adjustmentText,
									'orders_value' => BitBase::getParameter( $pPaymentParams, 'charge_amount', 0),
									'class' => 'ot_subtotal',
									'sort_order' => $maxSortOrder++,
								  );
				$this->mDb->associateInsert(TABLE_ORDERS_TOTAL, $sqlParams );
				$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `order_total`=? WHERE `orders_id`=?", array( $newTotal, $this->mOrdersId ) );
				$sqlParams = array( 'orders_id' => $this->mOrdersId,
									'title' => 'Total',
									'text' => $currencies->format( $newTotal, FALSE, BitBase::getParameter( $pPaymentParams, 'charge_currency' ) ),
									'orders_value' => $newTotal,
									'class' => 'ot_total',
									'sort_order' => $maxSortOrder++
								  );
				$this->mDb->associateInsert(TABLE_ORDERS_TOTAL, $sqlParams );
			}
		}
//eb( $ret, $statusMsg, $pPaymentParams, $this->info, $newTotal );
		return $ret;
	}

	private function createAddProducts($pOrdersId) {
		global $gBitUser, $currencies;

		$this->StartTrans();
		// initialized for the email confirmation

		$this->products_ordered_html = '';

		// lowstock email report
		$this->email_low_stock = '';
		$this->total_weight = 0;

		foreach( array_keys( $this->contents ) as $cartItemKey ) {
			$stockQty = $this->mDb->getOne( "SELECT `products_quantity` FROM " . TABLE_PRODUCTS . " WHERE `products_id` = ?", array( zen_get_prid($this->contents[$cartItemKey]['id']) ) );

			if ( $stockQty ) {
//				$this->contents[$cartItemKey]['stock_value'] = $stockValues['products_quantity'];
				$this->mDb->query("update " . TABLE_PRODUCTS . " set `products_quantity` = ? where `products_id` = ?", array( $stockQty, zen_get_prid($this->contents[$cartItemKey]['id']) ) );
				if ($stockQty < 1) { // && (STOCK_ALLOW_CHECKOUT == 'false') ) 
					// only set status to off when not displaying sold out
					if (SHOW_PRODUCTS_SOLD_OUT == '0') {
						$this->mDb->query("update " . TABLE_PRODUCTS . " set `products_status` = '0' where `products_id` = ?", array( zen_get_prid($this->contents[$cartItemKey]['id']) ) );
					}
				}

				// for low stock email
				if ( $stockQty <= STOCK_REORDER_LEVEL ) {
					// WebMakers.com Added: add to low stock email
					$this->email_low_stock .=	'ID# ' . zen_get_prid($this->contents[$cartItemKey]['id']) . "\t\t" . $this->contents[$cartItemKey]['model'] . "\t\t" . $this->contents[$cartItemKey]['name'] . "\t\t" . ' Qty Left: ' . $stock_left . "\n";
				}
			}

			// Update products_ordered (for bestsellers list)
			$this->mDb->query( "UPDATE " . TABLE_PRODUCTS . " SET `products_ordered` = `products_ordered` + ? WHERE `products_id` = ?", array( sprintf('%f', $this->contents[$cartItemKey]['products_quantity'] ), zen_get_prid( $this->contents[$cartItemKey]['id'] ) ) );

			$sql_data_array = array('orders_id' => $pOrdersId,
								'products_id' => zen_get_prid($this->contents[$cartItemKey]['id']),
								'products_model' => $this->contents[$cartItemKey]['model'],
								'products_name' => $this->contents[$cartItemKey]['name'],
								'products_price' => $this->contents[$cartItemKey]['price'],
								'products_cogs' => $this->contents[$cartItemKey]['products_cogs'],
								'products_wholesale' => $this->contents[$cartItemKey]['products_wholesale'],
								'products_commission' => $this->contents[$cartItemKey]['commission'],
								'final_price' => $this->contents[$cartItemKey]['final_price'],
								'onetime_charges' => $this->contents[$cartItemKey]['onetime_charges'],
								'products_tax' => $this->contents[$cartItemKey]['tax'],
								'products_quantity' => $this->contents[$cartItemKey]['products_quantity'],
								'products_priced_by_attribute' => $this->contents[$cartItemKey]['products_priced_by_attribute'],
								'product_is_free' => $this->contents[$cartItemKey]['product_is_free'],
								'products_discount_type' => $this->contents[$cartItemKey]['products_discount_type'],
								'products_discount_type_from' => $this->contents[$cartItemKey]['products_discount_type_from']);

			$this->mDb->associateInsert(TABLE_ORDERS_PRODUCTS, $sql_data_array);
			$this->contents[$cartItemKey]['orders_products_id'] = zen_db_insert_id( TABLE_ORDERS_PRODUCTS, 'orders_products_id' );

			$this->otUpdateCreditAccount( $cartItemKey );

			if( !empty( $this->contents[$cartItemKey]['purchase_group_id'] ) ) {
				$gBitUser->addUserToGroup( $gBitUser->mUserId, $this->contents[$cartItemKey]['purchase_group_id'] );
			}

			//------insert customer choosen option to order--------
			$attributes_exist = '0';
			$this->products_ordered_attributes = '';

			if( !empty($this->contents[$cartItemKey]['attributes']) ) {
				$attributes_exist = '1';
				foreach( $this->contents[$cartItemKey]['attributes'] as $j=>$attrHash ) {
					if( !empty( $attrHash['purchase_group_id'] ) ) {
						$gBitUser->addUserToGroup( $gBitUser->mUserId, $attrHash['purchase_group_id'] );
					}

					if( !empty( $attrHash['products_options_id'] ) ) {
						//clr 030714 update insert query.	changing to use values form $order->contents for products_options_values_name.
						$bindVars = array(  'orders_id' => $pOrdersId,
											'orders_products_id' => $this->contents[$cartItemKey]['orders_products_id'],
											'products_options_name' => $attrHash['products_options_name'],
											'products_options_values_name' => $this->contents[$cartItemKey]['attributes'][$j]['value'],
											'options_values_price' => $attrHash['options_values_price'],
											'options_values_cogs' => $attrHash['options_values_cogs'],
											'options_values_wholesale' => $attrHash['options_values_wholesale'],
											'price_prefix' => $attrHash['price_prefix'],
											'product_attribute_is_free' => $attrHash['product_attribute_is_free'],
											'products_attributes_wt' => $attrHash['products_attributes_wt'],
											'attributes_discounted' => (int)$attrHash['attributes_discounted'],
											'attributes_price_base_inc' => (int)$attrHash['attributes_price_base_inc'],
											'attributes_price_onetime' => $attrHash['attributes_price_onetime'],
											'attributes_price_factor' => $attrHash['attributes_price_factor'],
											'attributes_pf_offset' => $attrHash['attributes_pf_offset'],
											'attributes_pf_onetime' => $attrHash['attributes_pf_onetime'],
											'attributes_pf_onetime_offset' => $attrHash['attributes_pf_onetime_offset'],
											'attributes_qty_prices' => $attrHash['attributes_qty_prices'],
											'attributes_qty_prices_onetime' => $attrHash['attributes_qty_prices_onetime'],
											'attributes_price_words' => $attrHash['attributes_price_words'],
											'attributes_price_words_free' => $attrHash['attributes_price_words_free'],
											'attributes_price_letters' => $attrHash['attributes_price_letters'],
											'attributes_price_letters_free' => $attrHash['attributes_price_letters_free'],
											'products_options_id' => $attrHash['products_options_id'],
											'products_options_values_id' => $attrHash['products_options_values_id'],
										);
						$this->mDb->associateInsert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $bindVars);
					}
					$this->products_ordered_attributes .= "\n\t" . $attrHash['products_options_name'] . ' ' . zen_decode_specialchars($this->contents[$cartItemKey]['attributes'][$j]['value']);
				}
			}

			//------insert customer choosen option eof ----
			$this->total_weight += ($this->contents[$cartItemKey]['products_quantity'] * $this->contents[$cartItemKey]['weight']);
//			$this->total_tax += zen_calculate_tax($total_products_price, $products_tax) * $this->contents[$cartItemKey]['products_quantity'];
//			$this->total_cost += $total_products_price;

			$this->products_ordered_html .=
			'<tr>' .
			'<td class="product-details alignright" valign="top" width="30">' . $this->contents[$cartItemKey]['products_quantity'] . '&nbsp;x</td>' .
			'<td class="product-details" valign="top">' . $this->contents[$cartItemKey]['name'] . ($this->contents[$cartItemKey]['model'] != '' ? ' (' . $this->contents[$cartItemKey]['model'] . ') ' : '') .
			'<span style="white-space:nowrap;"><small><em> '. $this->products_ordered_attributes .'</em></small></span></td>' .
			'<td class="product-details-num alignright" valign="top">' .
				$currencies->display_price($this->contents[$cartItemKey]['final_price'], $this->contents[$cartItemKey]['tax'], $this->contents[$cartItemKey]['products_quantity']) .
				($this->contents[$cartItemKey]['onetime_charges'] !=0 ?
						'</td></tr><tr><td class="product-details">' . TEXT_ONETIME_CHARGES_EMAIL . '</td>' .
						'<td>' . $currencies->display_price($this->contents[$cartItemKey]['onetime_charges'], $this->contents[$cartItemKey]['tax'], 1) : '') .
			'</td></tr>';
		}

		$this->CompleteTrans();
	}

	function addProductToOrder( $pProductsId, $pQuantity = 1, $pOrderAttributes=array() ) {
		if( $this->isValid() && $newProduct = CommerceProduct::getCommerceObject( array( 'products_id' => $pProductsId ) ) ) {	
			$paramHash['orders_id'] = $this->mOrdersId;
			$paramHash['products_id'] = $newProduct->mProductsId;
			$paramHash['products_model'] = $newProduct->getProductsModel();
			$paramHash['products_name'] = $newProduct->getTitle();
			$paramHash['products_price'] = $newProduct->getPurchasePrice( $pQuantity, $pOrderAttributes );
			$paramHash['products_cogs'] = $newProduct->getCostPrice( $pQuantity, $pOrderAttributes );
			$paramHash['products_wholesale'] = $newProduct->getWholesalePrice( $pQuantity, $pOrderAttributes );
			$paramHash['products_commission'] = $newProduct->getCommissionUserCharges();
			$paramHash['final_price'] = $newProduct->getPurchasePrice();
			$paramHash['onetime_charges'] = $newProduct->getOneTimeCharges( $pQuantity, $pOrderAttributes );
//			$paramHash['products_tax'] = $newProduct->getField( 'tax' );
			$paramHash['products_quantity'] = $pQuantity;
			$paramHash['products_priced_by_attribute'] = $newProduct->getField( 'products_priced_by_attribute' );
			$paramHash['product_is_free'] = $newProduct->getField( 'product_is_free' );
			$paramHash['products_discount_type'] = $newProduct->getField( 'products_discount_type' );
			$paramHash['products_discount_type_from'] = $newProduct->getField( 'products_discount_type_from' );
			$this->mDb->associateInsert( TABLE_ORDERS_PRODUCTS, $paramHash );
		} else {
			$this->mErrors['new_product'] = 'Product not found: '.$pProductsId;
		}
		return (count( $this->mErrors ) === 0);
	}

	function sendOrderEmail( $pEmailRecipient=NULL, $pFormat=NULL ) {
		global $currencies, $gBitCustomer;

		$language_page_directory = DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/' ;
		require_once( BITCOMMERCE_PKG_PATH . $language_page_directory . 'checkout_process.php' );
//		require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'languages/en.php' );
		require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'functions/functions_customers.php' );

		if( empty( $pEmailRecipient ) ) {
			$pEmailRecipient = $this->customer['email_address'];
		}

		if ($this->email_low_stock != '' and SEND_LOWSTOCK_EMAIL=='1') {
			// send an email
			$email_low_stock = SEND_EXTRA_LOW_STOCK_EMAIL_TITLE . "\n\n" . $this->email_low_stock;
			zen_mail('', SEND_EXTRA_LOW_STOCK_EMAILS_TO, EMAIL_TEXT_SUBJECT_LOWSTOCK, $email_low_stock, STORE_OWNER, EMAIL_FROM, array('EMAIL_MESSAGE_HTML' => nl2br($email_low_stock)),'low_stock','',$pFormat);
		}

		// lets start with the email confirmation
		// make an array to store the html version
		$emailVars=array();
		$emailVars['order'] = $this;

		//intro area
		if( !empty( $this->customer['firstname'] ) ) {
			$customerName = $this->customer['firstname'].' '.$this->customer['lastname'];
		} else {
			$customerName = BitUser::getDisplayNameFromHash( $this->customer, FALSE );
		}
		$emailVars['INTRO_URL_VALUE']			 = zen_get_page_uri( FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->mOrdersId);
		$emailVars['EMAIL_TEXT_HEADER']		 = EMAIL_TEXT_HEADER;
		$emailVars['EMAIL_TEXT_FROM']			 = EMAIL_TEXT_FROM;
		$emailVars['INTRO_STORE_NAME']			= STORE_NAME;
		$emailVars['EMAIL_THANKS_FOR_SHOPPING'] = EMAIL_THANKS_FOR_SHOPPING;
		$emailVars['EMAIL_DETAILS_FOLLOW']	= EMAIL_DETAILS_FOLLOW;
		$emailVars['INTRO_ORDER_NUM_TITLE'] = EMAIL_TEXT_ORDER_NUMBER;
		$emailVars['INTRO_ORDER_NUMBER']		= $this->mOrdersId;
		$emailVars['INTRO_DATE_TITLE']			= EMAIL_TEXT_DATE_ORDERED;
		$emailVars['INTRO_DATE_ORDERED']		= strftime(DATE_FORMAT_LONG);
		$emailVars['INTRO_URL_TEXT']				= EMAIL_TEXT_INVOICE_URL_CLICK;

		$email_order = $emailVars['EMAIL_TEXT_HEADER'] . ' ' . $emailVars['EMAIL_TEXT_FROM'] . ' ' . STORE_NAME . "\n\n" .
						$customerName . "\n\n" .
						$emailVars['EMAIL_THANKS_FOR_SHOPPING'] . "\n\n" . $emailVars['EMAIL_DETAILS_FOLLOW'] . "\n" .
						EMAIL_SEPARATOR . "\n" .
						$emailVars['INTRO_ORDER_NUM_TITLE'] . ' ' . $this->mOrdersId . "\n" .
						$emailVars['INTRO_DATE_TITLE'] . ' ' . strftime(DATE_FORMAT_LONG) . "\n" .
						$emailVars['INTRO_URL_TEXT'] . ' ' .  zen_get_page_uri( FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->mOrdersId ) . "\n\n";

		//comments area
		if( !empty( $this->info['comments'] ) ) {
			$email_order .= zen_db_output($this->info['comments']) . "\n\n";
			$emailVars['ORDER_COMMENTS'] = zen_db_output($this->info['comments']);
		} else {
			$emailVars['ORDER_COMMENTS'] = '';
		}

		//products area
		$email_order .= EMAIL_TEXT_PRODUCTS . "\n" . EMAIL_SEPARATOR . "\n";

		foreach( array_keys( $this->contents ) as $cartItemKey ) {
			$email_order .=	$this->contents[$cartItemKey]['products_quantity'] . ' x ' . $this->contents[$cartItemKey]['name'] . ($this->contents[$cartItemKey]['model'] != '' ? ' (' . $this->contents[$cartItemKey]['model'] . ') ' : '') . ' = ' .
							$currencies->display_price( $this->contents[$cartItemKey]['final_price'], $this->contents[$cartItemKey]['tax'], $this->contents[$cartItemKey]['products_quantity'], $this->getField( 'currency' ), $this->getField( 'currency_value' ) ) .
			($this->contents[$cartItemKey]['onetime_charges'] !=0 ? "\n" . TEXT_ONETIME_CHARGES_EMAIL . $currencies->display_price($this->contents[$cartItemKey]['onetime_charges'], $this->contents[$cartItemKey]['tax'], 1) : '');
			foreach( array_keys( $this->contents[$cartItemKey]['attributes'] ) as $j ) {
				$email_order .= "\n    + " . zen_decode_specialchars($this->contents[$cartItemKey]['attributes'][$j]['products_options_name']) . ' ' . zen_decode_specialchars($this->contents[$cartItemKey]['attributes'][$j]['products_options_values_name']);
			}
			$email_order .= "\n\n";
		}

		$email_order .= EMAIL_SEPARATOR . "\n";

		$emailVars['PRODUCTS_TITLE'] = EMAIL_TEXT_PRODUCTS;
		if( !empty( $this->products_ordered_html ) ) {
			$emailVars['PRODUCTS_DETAIL']='<table class="product-details" border="0" width="100%" cellspacing="0" cellpadding="2">' . $this->products_ordered_html . '</table>';
		}

//order totals area
		$html_ot = '<td class="order-totals-text alignright" width="100%">' . '&nbsp;' . '</td><td class="order-totals-num alignright" nowrap="nowrap">' . '---------' .'</td></tr><tr>';
		foreach( array_keys( $this->mOtProcessModules ) as $key ) {
			$email_order .= strip_tags($this->mOtProcessModules[$key]['title']) . ' ' . strip_tags( $this->mOtProcessModules[$key]['text'] ) . "\n";
			$html_ot .= '<td class="order-totals-text" align="right" width="100%">' . $this->mOtProcessModules[$key]['title'] . '</td><td class="order-totals-num" align="right" nowrap="nowrap">' . $this->mOtProcessModules[$key]['text'] .'</td></tr><tr>';
		}
		$emailVars['ORDER_TOTALS'] = '<table border="0" width="100%" cellspacing="0" cellpadding="2">' . $html_ot . '</table>';

//addresses area: Delivery
		$emailVars['HEADING_ADDRESS_INFORMATION']= tra( 'Address Information' );
		$emailVars['ADDRESS_DELIVERY_TITLE']		 = EMAIL_TEXT_DELIVERY_ADDRESS;
		$emailVars['ADDRESS_DELIVERY_DETAIL']		= ($this->content_type != 'virtual') ? zen_address_format( $this->delivery, true, '', "<br />") : 'n/a';
		$emailVars['SHIPPING_METHOD_TITLE']			= HEADING_SHIPPING_METHOD;
		$emailVars['SHIPPING_METHOD_DETAIL']		 = (!empty( $this->info['shipping_method'] ) ? $this->info['shipping_method'] : 'n/a');

		if ($this->content_type != 'virtual') {
			$email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .	EMAIL_SEPARATOR . "\n" . zen_address_format( $this->delivery, FALSE, '', "\n" ) . "\n\n";
		}

		//addresses area: Billing
		$email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .	EMAIL_SEPARATOR . "\n" . zen_address_format( $this->billing, FALSE, '', "\n" ) . "\n\n";
		$emailVars['ADDRESS_BILLING_TITLE']	 = EMAIL_TEXT_BILLING_ADDRESS;
		$emailVars['ADDRESS_BILLING_DETAIL']	= zen_address_format( $this->billing, true, '', "<br />");

		$emailVars['PAYMENT_METHOD_TITLE'] = $emailVars['PAYMENT_METHOD_DETAIL'] = $emailVars['PAYMENT_METHOD_FOOTER'] = '';
		if( $paymentModule = $this->getPaymentModule() ) {
			$email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .	EMAIL_SEPARATOR . "\n";
			$email_order .= $paymentModule->title . "\n\n";
			if( !empty( $paymentModule->email_footer ) ) {
				$email_order .= $paymentModule->email_footer . "\n\n";
			}
			$emailVars['PAYMENT_METHOD_TITLE'] = EMAIL_TEXT_PAYMENT_METHOD;
			$emailVars['PAYMENT_METHOD_DETAIL'] = $paymentModule->title;
			$emailVars['PAYMENT_METHOD_FOOTER'] = (!empty( $paymentModule->email_footer ) ? $paymentModule->email_footer : '');
		}

		// include disclaimer
		$email_order .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";
		// include copyright
		$email_order .= "\n-----\n" . EMAIL_FOOTER_COPYRIGHT . "\n\n";

		while (strstr($email_order, '&nbsp;')) {
			$email_order = str_replace('&nbsp;', ' ', $email_order);
		}

		$emailVars['EMAIL_FIRST_NAME'] = $this->getFirstName();
//		$emailVars['EMAIL_LAST_NAME'] = $this->customer['lastname'];
//	$emailVars['EMAIL_TEXT_HEADER'] = EMAIL_TEXT_HEADER;
		$emailVars['EXTRA_INFO'] = '';
		zen_mail($customerName, $pEmailRecipient, EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $this->mOrdersId, $email_order, STORE_NAME, EMAIL_FROM, $emailVars, 'checkout','',$pFormat);

		// send additional emails
		if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
			$extra_info=email_collect_extra_info('','', $customerName, $this->customer['email_address'], BitBase::getParameter( $this->customer, 'telephone' ) );
			$emailVars['EXTRA_INFO'] = $extra_info['HTML'];
			zen_mail('', SEND_EXTRA_ORDER_EMAILS_TO, tra( '[NEW ORDER]' ) . ' ' . EMAIL_TEXT_SUBJECT . EMAIL_ORDER_NUMBER_SUBJECT . $this->mOrdersId, $email_order . $extra_info['TEXT'], STORE_NAME, EMAIL_FROM, $emailVars, 'checkout_extra','',$pFormat);
		}
	}

	public function hasDifferentBillingAddress() {

		foreach( array_keys( $this->delivery ) as $addressKey ) {
			if( $this->delivery[$addressKey] != $this->billing[$addressKey] ) {
				return TRUE;
			}
		}

		return FALSE;
	}

	function getFormattedAddress( $pAddressHash, $pBreak='<br>' ) {
		$ret = '';
		if( $this->isValid() ) {
			$isHtml = strpos( '<', $pBreak ) !== FALSE;
			$ret = zen_address_format( $this->$pAddressHash, $isHtml, '', $pBreak );
		}
		return $ret;
	}

	function getDownloads() {
		$ret = array();
		if( $this->isValid() ) {
			$query = "SELECT ".$this->mDb->SQLDate('Y-m-d', 'o.date_purchased')." as `date_purchased_day`, opd.`download_maxdays`, op.`products_name`, opd.`orders_products_download_id`, opd.`orders_products_filename`, opd.`download_count`, opd.`download_maxdays`
						FROM " . TABLE_ORDERS . " o
						INNER JOIN " . TABLE_ORDERS_PRODUCTS . " op ON (o.`orders_id`=op.`orders_id`)
						INNER JOIN " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd ON (op.`orders_products_id`=opd.`orders_products_id`)
						WHERE o.`customers_id` = ? AND (o.`orders_status_id` >= ? AND o.`orders_status_id` <= ?) AND o.`orders_id` = ?	AND opd.`orders_products_filename` != ''
						ORDER BY op.`orders_products_id`";
			$ret = $this->mDb->getAll( $query, array( $this->getField('customers_id'), DOWNLOADS_CONTROLLER_ORDERS_STATUS, DOWNLOADS_CONTROLLER_ORDERS_STATUS_END, $this->mOrdersId ) );

// copies from includes/modules/downloads.php before git rm
/*
list($dt_year, $dt_month, $dt_day) = explode('-', $downloads->fields['date_purchased_day']);
$download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads->fields['download_maxdays'], $dt_year);
$download_expiry = date('Y-m-d H:i:s', $download_timestamp);

	echo '						<td align="center">' . zen_date_short($download_expiry) . '</td>' . "\n" .
		 '						<td align="center">' . $downloads->fields['download_count'] . '</td>' . "\n" .

// If there is a download in the order and they cannot get it, tell customer about download rules
$downloads_check_query = $this->mDb->query("select o.`orders_id`, opd.orders_products_download_id
																 from " .	TABLE_ORDERS . " o, " .	TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
									 							 where o.`orders_id` = opd.`orders_id` and o.`orders_id` = ? and opd.orders_products_filename != '' ", array( $last_order ) );
*/

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

		$orderUpdated = FALSE;

		// a semaphore can be passed in to prevent two different people from updating the same order behind each other's back
		$statusCleared = TRUE;
		if( !empty( $pParamHash['last_status_id'] ) ) {
			$lastStatusId = $this->mDb->getOne( "SELECT `orders_status_history_id` FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE `orders_id` = ? ORDER BY `date_added` DESC", array( $this->mOrdersId ) );
			if( !($statusCleared = ($lastStatusId == $pParamHash['last_status_id'])) ) {
				$this->mErrors['status'] = 'The status of this order has changed since it was opened. Please verify what has changed.';
			}
		}

		// default to order status if not specified
		$status = !empty( $pParamHash['status'] ) ? zen_db_prepare_input( $pParamHash['status'] ) : $this->getStatus();
		$comments = !empty( $pParamHash['comments'] ) ? zen_db_prepare_input( trim( $pParamHash['comments'] ) ) : NULL;

		$statusChanged = ($this->getStatus() != $status);

		if( $statusCleared ) {
			if( $statusChanged || !empty( $comments ) ) {
				$this->StartTrans();
				$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `orders_status_id` = ?, `last_modified` = ".$this->mDb->NOW()." WHERE `orders_id` = ?", array( $status, $this->mOrdersId ) );

				$this->info['orders_status_id'] = $status;
				$this->info['orders_status_name'] = zen_get_order_status_name( $status );
				$customer_notified = '0';
				if( isset( $pParamHash['notify'] ) && ( $pParamHash['notify'] == 'on' ) ) {
					$notify_comments = '';
					if( !empty( $comments ) ) {
						$notify_comments = $comments . "\n\n";
					}

					//send emails
					$textMessage = STORE_NAME . "\n------------------------------------------------------\n" .
						tra( 'Order Number' ) . ': ' . $this->mOrdersId . "\n" .
						tra( 'Date Ordered' ) . ': ' . zen_date_long($this->info['date_purchased']) . "\n" .
						$this->getDisplayUrl() . "\n\n" .
						strip_tags($notify_comments) ;
					
					if( $statusChanged ) {
						$textMessage .= tra( 'Your order has been updated to the following status' ) . ': ' . $this->info['orders_status_name'] . "\n\n";
					}
					$textMessage .= tra( 'Please reply to this email if you have any questions.' );

					$emailVars['EMAIL_CUSTOMERS_NAME']		= $this->customer['name'];
					$emailVars['EMAIL_TEXT_ORDER_NUMBER'] = tra( 'Order Number' ) . ': ' . $this->mOrdersId;
					$emailVars['EMAIL_TEXT_INVOICE_URL']	= $this->getDisplayLink();
					$emailVars['EMAIL_TEXT_DATE_ORDERED'] = tra( 'Date Ordered' ) . ': ' . zen_date_long( $this->info['date_purchased'] );
					$emailVars['EMAIL_TEXT_STATUS_COMMENTS'] = nl2br( $notify_comments );
					if( $statusChanged ) {
						$emailVars['EMAIL_TEXT_STATUS_UPDATED'] = tra( 'Your order has been updated to the following status' ) . ': ';
						$emailVars['EMAIL_TEXT_NEW_STATUS'] = $this->info['orders_status_name'];
					}
					$emailVars['EMAIL_TEXT_STATUS_PLEASE_REPLY'] = tra( 'Please reply to this email if you have any questions.' );

					$emailVars['order'] = $this;
					zen_mail( $this->customer['name'], $this->customer['email_address'], STORE_NAME . ' ' . tra( 'Order Update' ) . ' #' . $this->mOrdersId, $textMessage, STORE_NAME, EMAIL_FROM, $emailVars, 'order_status');

					$customer_notified = '1';
					//send extra emails
					if (SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO != '') {
						zen_mail('', SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . EMAIL_TEXT_SUBJECT . ' #' . $this->mOrdersId, $textMessage, STORE_NAME, EMAIL_FROM, $emailVars, 'order_status_extra');
					}
				}

				$this->mDb->query( "INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . " (`orders_id`, `orders_status_id`, `date_added`, `customer_notified`, `comments`, `user_id`)
									VALUES ( ?, ?, ?, ?, ?, ? )", array( $this->mOrdersId, $status, $this->mDb->NOW(), $customer_notified, $comments, $gBitUser->mUserId ) );

				$this->CompleteTrans();
				$orderUpdated = true;
			} else {
				$this->mErrors['status'] = 'Nothing to change.';
			}
		}
		return $orderUpdated;
	}

	function updateOrder( $pParamHash ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->StartTrans();
			$this->mDb->associateUpdate( TABLE_ORDERS, $pParamHash, array( 'orders_id' => $this->mOrdersId ) );
			$this->CompleteTrans();
			$this->load( $this->mOrdersId );
			$ret = TRUE;
		}
		return $ret;
	}

	function changeShipping( $pQuote, $pParamHash ) {
		global $currencies;
		$this->StartTrans();
		$newTotal = 0;

		// assume a single method
		if( $newMethod = current( $pQuote['methods'] ) ) {
			foreach( array_keys( $this->totals ) as $k ) {
				if( $this->totals[$k]['class'] == 'ot_shipping' ) {
					$initialShipping = $this->totals[$k];
					$this->totals[$k]['title'] = $pQuote['module'].' '.$newMethod['title'];
					$this->totals[$k]['text'] = BitBase::getParameter( $newMethod, 'format_add_tax' );
					$this->totals[$k]['orders_value'] = BitBase::getParameter( $newMethod, 'cost_add_tax', 0 );
					$finalShipping = $this->totals[$k];
				}
				if( $this->totals[$k]['class'] == 'ot_total' ) {
					$initialTotal = $this->totals[$k]['orders_value'];
					$totalKey = $k;
				} else {
					$newTotal += $this->totals[$k]['orders_value'];
				}
			}

			$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `shipping_module_code`=?, `shipping_method_code`=? WHERE `orders_id`=?", array( $pQuote['id'], $newMethod['code'], $this->mOrdersId ) );
			if( empty( $initialShipping ) ) {
				$finalShipping['orders_id'] = $this->mOrdersId;
				$finalShipping['class'] = 'ot_shipping';
				$finalShipping['title'] = $pQuote['module'].' '.$newMethod['title'];
				$finalShipping['text'] = $newMethod['format_add_tax'];
				$finalShipping['sort_order'] = 200;
				$finalShipping['orders_value'] = $newMethod['cost_add_tax'];
				$newTotal += $finalShipping['orders_value'];
				$this->mDb->associateInsert( TABLE_ORDERS_TOTAL, $finalShipping );
				$formatString = tra( "The shipping method was added '%s', %s for a cost change of %s. Previous order total was %s" );
				$message = sprintf( $formatString, $finalShipping['title'], $currencies->format( $finalShipping['orders_value'] ), $currencies->format( round( ($newTotal - $initialTotal), 2 ) ), $currencies->format( round( ($initialTotal), 2 ) ) );
			} else {
				$formatString = tra( "The shipping method was changed from '%s',%s to '%s',%s for a cost change of %s. Previous order total was %s" );
				$message = sprintf( $formatString, $initialShipping['title'], $currencies->format( $initialShipping['orders_value'] ), $finalShipping['title'], $currencies->format( $finalShipping['orders_value'] ), $currencies->format( round( ($newTotal - $initialTotal), 2 ) ), $currencies->format( round( ($initialTotal), 2 ) ) );
			}
			$this->totals[$totalKey]['orders_value'] = $newTotal;
			$this->totals[$totalKey]['text'] = $currencies->format( $newTotal );
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
			$this->CompleteTrans();
		}
	}

	function combineOrders( $pParamHash ) {
		global $currencies, $gCommerceSystem;
		$ret = FALSE;

		$sql = "SELECT * FROM " . TABLE_ORDERS . " WHERE `orders_id`=?";
		$sourceHash = $this->mDb->getRow( $sql, array( $pParamHash['source_orders_id'] ) );
		$destHash = $this->mDb->getRow( $sql, array( $pParamHash['dest_orders_id'] ) );

		$combineOrdersStatus = $gCommerceSystem->getConfig( 'COMBINE_ORDERS_STATUS_ID', DEFAULT_ORDERS_STATUS_ID );

		if( empty( $sourceHash ) ) {
			$this->mErrors['combine'] = "Order $pParamHash[source_orders_id] not found";
		} elseif( empty( $destHash ) ) {
			$this->mErrors['combine'] = "Order $pParamHash[dest_orders_id] not found";
		} elseif( $sourceHash['orders_status_id'] != $combineOrdersStatus ) {
			$this->mErrors['combine'] = "Order $pParamHash[source_orders_id] does not have status " . zen_get_order_status_name( $combineOrdersStatus );
		} elseif( $destHash['orders_status_id'] != $combineOrdersStatus ) {
			$this->mErrors['combine'] = "Order $pParamHash[dest_orders_id] does not have status " . zen_get_order_status_name( $combineOrdersStatus );
		} elseif( $pParamHash['dest_orders_id'] == $pParamHash['source_orders_id'] ) {
			$this->mErrors['combine'] = "Order $pParamHash[dest_orders_id] cannot be combined into itself";
		} elseif( ($sourceHash['delivery_street_address'] == $destHash['delivery_street_address']) &&
			($sourceHash['delivery_suburb'] == $destHash['delivery_suburb']) &&
			($sourceHash['delivery_city'] == $destHash['delivery_city']) &&
			($sourceHash['delivery_postcode'] == $destHash['delivery_postcode']) &&
			($sourceHash['delivery_state'] == $destHash['delivery_state']) &&
			($sourceHash['delivery_country'] == $destHash['delivery_country']) ) {

			$this->StartTrans();
			// update new order with combined total and combined tax
			$this->mDb->query( "UPDATE ". TABLE_ORDERS . " SET `order_total`=?, `order_tax`=? WHERE `orders_id`=?", array( ($sourceHash['order_total'] + $destHash['order_total']), ($sourceHash['order_tax'] + $destHash['order_tax']), $pParamHash['dest_orders_id'] ) );

			// Move products and attributes over to new order
			$this->mDb->query( "UPDATE ". TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " SET `orders_id`=? WHERE `orders_id`=?", array( $pParamHash['dest_orders_id'], $pParamHash['source_orders_id'] ) );
			$this->mDb->query( "UPDATE ". TABLE_ORDERS_PRODUCTS . " SET `orders_id`=? WHERE `orders_id`=?", array( $pParamHash['dest_orders_id'], $pParamHash['source_orders_id'] ) );
			$this->mDb->query( "UPDATE ". TABLE_REVIEWS . " SET `orders_id`=? WHERE `orders_id`=?", array( $pParamHash['dest_orders_id'], $pParamHash['source_orders_id'] ) );

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
			$this->CompleteTrans();
		} else {
			$this->mErrors['combine'] = "Address mismatch. To combine orders, they must have the same delivery address.";
		}

		// no yet implemented
		return empty( $this->mErrors['combine'] );
	}

	function isValid() {
		return $this->verifyId( $this->mOrdersId );
	}

	function getFirstName() {
		$ret = '';
		if( $this->isValid() ) {
			if( !empty( $this->customer['real_name'] ) ) {
				$names = explode(' ', $this->customer['real_name'], 2);
				$ret = $names[0];
			} elseif( !empty( $this->customer['real_name'] ) ) {
				$ret = explode(' ', $this->customer['real_name'], 1);
			} elseif( !empty( $this->billing['firstname'] ) ) {
				$ret = $this->billing['firstname'];
			} elseif( !empty( $this->delivery['firstname'] ) ) {
				$ret = $this->delivery['firstname'];
			} else {
				$ret = $this->customer['email_address'];
			}
		}
		return $ret;
	}

	function getDisplayLink() {
		$ret = '';
		if( $this->isValid() ) {
			$ret = '<a href="' .$this->getDisplayUrl() .'">'.str_replace( ':','',tra( 'Detailed Invoice:' ) ).( $this->isvalid() ? ' #'.$this->mOrdersId : '' ).'</a>';
		}
		return $ret;
	}

	function getDisplayUrl( $page = '', $parameters = '', $connection = 'NONSSL') {
		return zen_get_page_uri( 'account_history_info', 'order_id='.$this->mOrdersId );
	}

	static function getObjectByOrdersProduct( $pOrdersProductId, $pVerify=TRUE ) {
		global $gBitDb, $gBitUser;
		$ret = NULL;
		if( self::verifyId( $pOrdersProductId ) ) {
			$orderHash = $gBitDb->getRow( "SELECT co.`orders_id`, co.`customers_id` FROM " . TABLE_ORDERS . " co INNER JOIN " . TABLE_ORDERS_PRODUCTS . "cop ON (cop.`orders_id`=co.`orders_id`) WHERE `orders_products_id`=?", array( $pOrdersProductId ) );
			if( $orderHash['customers_id'] == $gBitUser->mUserId || $gBitUser->hasPermission( 'p_bitcommerce_admin' ) ) {
				$ret = new order( $orderHash['orders_id'] );
			}
		}
		return $ret;
	}


}


<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: order.php,v 1.56 2007/08/20 19:26:40 spiderr Exp $
//

class order extends BitBase {
  	var $mOrdersId;
    var $info, $totals, $products, $customer, $delivery, $content_type, $email_low_stock, $products_ordered_attributes,
        $products_ordered, $products_ordered_email;

    function order($order_id = '') {
    	BitBase::BitBase();
		$this->mOrdersId = $order_id;
		$this->info = array();
		$this->totals = array();
		$this->products = array();
		$this->customer = array();
		$this->delivery = array();

		if (zen_not_null($order_id)) {
			$this->load($order_id);
		} else {
			$this->cart();
		}
    }

	function getField( $pField ) {
		$ret = (isset( $this->info[$pField] ) ? $this->info[$pField] : NULL );
		return $ret;
	}

	function getTotal( $pClass, $pKey ) {
		$ret = '';
		for( $i = 0; $i < count( $this->totals ); $i++ ) {
			if( $this->totals[$i]['class'] == $pClass && !empty( $this->totals[$i][$pKey] ) ) {
				$ret = $this->totals[$i][$pKey];
			}
		}
		return $ret;
	}

	function getList( $pListHash ) {
		global $gBitDb;
		$bindVars = array();
		$ret = array();
		$selectSql = ''; $joinSql = ''; $whereSql = '';

		$comparison = (!empty( $pListHash['orders_status_comparison'] ) && strlen( $pListHash['orders_status_comparison'] ) <= 2) ? $pListHash['orders_status_comparison'] : '=';

		if( !empty( $pListHash['user_id'] ) ) {
			$whereSql .= ' AND `customers_id`=? ';
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['orders_status_id'] ) ) {
			$whereSql .= ' AND `orders_status`'.$comparison.'? ';
			$bindVars[] = $pListHash['orders_status_id'];
		}

		if( empty( $pListHash['sort_mode'] ) ) {
			$pListHash['sort_mode'] = 'o.orders_id_desc';
		}
		if( empty( $pListHash['max_records'] ) ) {
			$pListHash['max_records'] = -1;
		}

		if( !empty( $pListHash['search'] ) ) {
			$whereSql .= " AND ( ";
			$whereSql .= " LOWER(`delivery_name`) like ? OR ";
			$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
			$whereSql .= " LOWER(`billing_name`) like ? OR ";
			$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
			$whereSql .= " LOWER(uu.`email`) like ? OR ";
			$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
			$whereSql .= " LOWER(uu.`real_name`) like ? ";
			$bindVars[] = '%'.strtolower( $pListHash['search'] ).'%';
			$whereSql .= " ) ";
		}

		$query = "SELECT o.`orders_id` AS `hash_key`, ot.`text` AS `order_total`, o.*, uu.*, os.*, ".$gBitDb->mDb->SQLDate( 'Y-m-d H:i', 'o.`date_purchased`' )." AS `purchase_time` $selectSql
				  FROM " . TABLE_ORDERS . " o
				  	INNER JOIN " . TABLE_ORDERS_STATUS . " os ON(o.`orders_status`=os.`orders_status_id`)
				  	INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(o.`customers_id`=uu.`user_id`)
				  	LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot on (o.`orders_id` = ot.`orders_id`)
					$joinSql
				  WHERE `class` = 'ot_total' $whereSql
				  ORDER BY ".$gBitDb->convertSortmode( $pListHash['sort_mode'] );
		if( $rs = $gBitDb->query( $query, $bindVars, $pListHash['max_records'] ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[$row['orders_id']] = $row;
				if( !empty( $pListHash['recent_comment'] ) ) {
					$ret[$row['orders_id']]['comments'] = $gBitDb->getOne( "SELECT `comments` FROM " . TABLE_ORDERS_STATUS_HISTORY . " osh WHERE osh.`orders_id`=? AND `comments` IS NOT NULL ORDER BY `orders_status_history_id` DESC", array( $row['orders_id'] ) );
				}
			}
		}

		return( $ret );
	}

    function load($order_id) {
      global $gBitDb;

      $order_id = zen_db_prepare_input($order_id);

      $order_query = "SELECT *
                      FROM " . TABLE_ORDERS . " co INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(uu.`user_id`=co.`customers_id`)
                      WHERE `orders_id` = ?";

      $order = $gBitDb->query( $order_query, array( $order_id ) );

      $totals_query = "select `title`, `text`, `class`, `orders_value`
                       from " . TABLE_ORDERS_TOTAL . "
                       where `orders_id` = '" . (int)$order_id . "'
                       order by `sort_order`";

      $totals = $gBitDb->Execute($totals_query);

      while (!$totals->EOF) {
        $this->totals[] = array('title' => $totals->fields['title'],
                                'text' => $totals->fields['text'],
                                'class' => $totals->fields['class'],
                                'orders_value' => $totals->fields['orders_value']);
        $totals->MoveNext();
      }

      $order_total_query = "select `text`, `orders_value`
         from " . TABLE_ORDERS_TOTAL . "
         where `orders_id` = '" . (int)$order_id . "'
         and class = 'ot_total'";


      $order_total = $gBitDb->Execute($order_total_query);


      $shipping_method_query = "select 'title', `orders_value` AS `shipping_total`
          FROM " . TABLE_ORDERS_TOTAL . "
          WHERE `orders_id` =  ? AND class = 'ot_shipping'";
      $shippingInfo = $gBitDb->getRow($shipping_method_query, array( (int)$order_id ) );

      $order_status_query = "select `orders_status_name`
                             from " . TABLE_ORDERS_STATUS . "
                             where `orders_status_id` = ? AND `language_id` = ?";

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
                              'email_address' => $order->fields['customers_email_address']);

      $this->delivery = array('name' => $order->fields['delivery_name'],
                              'company' => $order->fields['delivery_company'],
                              'street_address' => $order->fields['delivery_street_address'],
                              'suburb' => $order->fields['delivery_suburb'],
                              'city' => $order->fields['delivery_city'],
                              'postcode' => $order->fields['delivery_postcode'],
                              'state' => $order->fields['delivery_state'],
                              'country' => $order->fields['delivery_country'],
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

      $index = 0;
      $orders_products_query = "SELECT op.*, pt.*, p.content_id, p.related_content_id, lc.user_id
                                FROM " . TABLE_ORDERS_PRODUCTS . " op
									LEFT OUTER JOIN  " . TABLE_PRODUCTS . " p ON ( op.`products_id`=p.`products_id` )
									LEFT OUTER JOIN  " . TABLE_PRODUCT_TYPES . " pt ON ( p.`products_type`=pt.`type_id` )
									LEFT OUTER JOIN  `" . BIT_DB_PREFIX . "liberty_content` lc ON ( lc.`content_id`=p.`content_id` )
                                WHERE `orders_id` = ?";
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

        $this->products[$index] = $orders_products->fields;
		$this->products[$index]['quantity'] = $new_qty;
		$this->products[$index]['id'] = $orders_products->fields['products_id'];
		$this->products[$index]['name'] = $orders_products->fields['products_name'];
		$this->products[$index]['model'] = $orders_products->fields['products_model'];
		$this->products[$index]['tax'] = (!empty( $orders_products->fields['tax_rate'] ) ? $orders_products->fields['tax_rate'] : NULL);
		$this->products[$index]['price'] = $orders_products->fields['products_price'];

        $subindex = 0;
        $attributes_query = "SELECT *, `orders_products_attributes_id` AS products_attributes_id
                             FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                             WHERE `orders_id` = ? AND orders_products_id = ?";
        $attributes = $this->mDb->query( $attributes_query, array( $order_id, $orders_products->fields['orders_products_id'] ) );
        if ($attributes->RecordCount()) {
			while (!$attributes->EOF) {
				$this->products[$index]['attributes'][$subindex] = array( 'options_id' => $attributes->fields['products_options_id'],
																		'options_values_id' => $attributes->fields['products_options_values_id'],
																		'option' => $attributes->fields['products_options'],
																		'value' => $attributes->fields['products_options_values'],
																		'prefix' => $attributes->fields['price_prefix'],
																		'final_price' => $this->getOrderAttributePrice( $attributes->fields, $this->products[$index] ),
																		'price' => $attributes->fields['options_values_price'],
																		'orders_products_attributes_id' => $attributes->fields['orders_products_attributes_id'] );

				$subindex++;
				$attributes->MoveNext();
			}
        }

        $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

        $index++;
        $orders_products->MoveNext();
       }
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
		$display_special_price = zen_get_products_special_price( $pProductHash["products_id"] );

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
					FROM   " . TABLE_ORDERS_STATUS . " os
						INNER JOIN " . TABLE_ORDERS_STATUS_HISTORY . " osh ON( osh.`orders_status_id` = os.`orders_status_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=osh.`user_id` )
					WHERE osh.`orders_id` = ? AND os.`language_id` = ?
					ORDER BY osh.`date_added`";

			if( $rs = $this->mDb->query($sql, array( $this->mOrdersId, $_SESSION['languages_id'] ) ) ) {
				while( !$rs->EOF ) {
					$rs->fields['comments'] = str_replace( "\n",'<br/>', $rs->fields['comments'] );
					array_push( $this->mHistory, $rs->fields );
					$rs->MoveNext();
				}
			}
		}
		return( count( $this->mHistory ) );
	}

    function cart() {
      global $gBitDb, $currencies, $gBitUser, $gBitCustomer;
      $this->content_type = $_SESSION['cart']->get_content_type();

		if( $gBitUser->isRegistered() ) {
			  $customer_address_query = "select c.`customers_firstname`, c.`customers_lastname`, c.`customers_telephone`,
												c.`customers_email_address`, ab.`entry_company`, ab.`entry_street_address`,
												ab.`entry_suburb`, ab.`entry_postcode`, ab.`entry_city`, ab.`entry_zone_id`,
												z.`zone_name`, co.`countries_id`, co.`countries_name`,
												co.`countries_iso_code_2`, co.`countries_iso_code_3`,
												co.`address_format_id`, ab.`entry_state`
										 from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab
										 left join " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
										 left join " . TABLE_COUNTRIES . " co on (ab.`entry_country_id` = co.`countries_id`)
										 where c.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
										 and ab.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
										 and c.`customers_default_address_id` = ab.`address_book_id`";

			  $customer_address = $gBitDb->Execute($customer_address_query);

			  $shipping_address_query = "select ab.*, z.`zone_name`, ab.`entry_country_id`,
												c.`countries_id`, c.`countries_name`, c.`countries_iso_code_2`,
												c.`countries_iso_code_3`, c.`address_format_id`, ab.`entry_state`
										 from " . TABLE_ADDRESS_BOOK . " ab
										 left join " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
										 left join " . TABLE_COUNTRIES . " c on (ab.`entry_country_id` = c.`countries_id`)
										 where ab.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
										 and ab.`address_book_id` = '" . (int)$_SESSION['sendto'] . "'";

			  $shipping_address = $gBitDb->Execute($shipping_address_query);

			  $billing_address_query = "select ab.*, z.`zone_name`, ab.`entry_country_id`,
											   c.`countries_id`, c.`countries_name`, c.`countries_iso_code_2`,
											   c.`countries_iso_code_3`, c.`address_format_id`, ab.`entry_state`
										from " . TABLE_ADDRESS_BOOK . " ab
										left join " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
										left join " . TABLE_COUNTRIES . " c on (ab.`entry_country_id` = c.`countries_id`)
										where ab.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
										and ab.`address_book_id` = '" . (int)$_SESSION['billto'] . "'";

			  $billing_address = $gBitDb->Execute($billing_address_query);
		}
//STORE_PRODUCT_TAX_BASIS

      switch (STORE_PRODUCT_TAX_BASIS) {
        case 'Shipping':

          $tax_address_query = "select ab.`entry_country_id`, ab.`entry_zone_id`, ab.`entry_state`
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.`address_book_id` = '" . (int)($this->content_type == 'virtual' ? $_SESSION['billto'] : $_SESSION['sendto']) . "'";
          $tax_address = $gBitDb->Execute($tax_address_query);
        break;
        case 'Billing':

          $tax_address_query = "select ab.`entry_country_id`, ab.`entry_zone_id`, ab.`entry_state`
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
                                where ab.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.`address_book_id` = '" . (int)$_SESSION['billto'] . "'";
          $tax_address = $gBitDb->Execute($tax_address_query);
        break;
        case 'Store':
          if ($billing_address->fields['entry_zone_id'] == STORE_ZONE) {

            $tax_address_query = "select ab.`entry_country_id`, ab.`entry_zone_id`, ab.`entry_state`
                                  from " . TABLE_ADDRESS_BOOK . " ab
                                  left join " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
                                  where ab.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
                                  and ab.`address_book_id` = '" . (int)$_SESSION['billto'] . "'";
          } else {
            $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id, ab.`entry_state`
                                  from " . TABLE_ADDRESS_BOOK . " ab
                                  left join " . TABLE_ZONES . " z on (ab.`entry_zone_id` = z.`zone_id`)
                                  where ab.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
                                  and ab.`address_book_id` = '" . (int)($this->content_type == 'virtual' ? $_SESSION['billto'] : $_SESSION['sendto']) . "'";
          }
          $tax_address = $gBitDb->Execute($tax_address_query);
     }

	if( !empty( $tax_address->fields['entry_country_id'] ) && empty( $tax_address->fields['entry_zone_id'] ) ) {
		if( $gBitCustomer->getZoneCount( $tax_address->fields['entry_country_id'] ) && ($zoneId = $gBitCustomer->getZoneId( $tax_address->fields['entry_state'], $tax_address->fields['entry_country_id'] )) ) {
			$tax_address->fields['entry_zone_id'] = $zoneId;
		}
		// maybe we have some newly updated zones and outdated address_book entries
	}



      $class =& $_SESSION['payment'];

		$coupon_code = NULL;
      if( !empty( $_SESSION['cc_id'] ) ) {
        $coupon_code_query = "SELECT `coupon_code`
                              FROM " . TABLE_COUPONS . "
                              WHERE `coupon_id` = ?";

        $coupon_code = $gBitDb->GetOne($coupon_code_query, array( (int)$_SESSION['cc_id'] ) );
      }

      $this->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
                          'currency' => !empty( $_SESSION['currency'] ) ? $_SESSION['currency'] : NULL,
                          'currency_value' => !empty( $_SESSION['currency'] ) ? $currencies->currencies[$_SESSION['currency']]['value'] : NULL,
                          'payment_method' => !empty( $GLOBALS[$class] ) ? $GLOBALS[$class]->title : '',
                          'payment_module_code' => !empty( $GLOBALS[$class] ) ? $GLOBALS[$class]->code : '',
                          'coupon_code' => $coupon_code,
//                          'cc_type' => (isset($GLOBALS['cc_type']) ? $GLOBALS['cc_type'] : ''),
//                          'cc_owner' => (isset($GLOBALS['cc_owner']) ? $GLOBALS['cc_owner'] : ''),
//                          'cc_number' => (isset($GLOBALS['cc_number']) ? $GLOBALS['cc_number'] : ''),
//                          'cc_expires' => (isset($GLOBALS['cc_expires']) ? $GLOBALS['cc_expires'] : ''),
//                          'cc_cvv' => (isset($GLOBALS['cc_cvv']) ? $GLOBALS['cc_cvv'] : ''),
                          'shipping_method' => $_SESSION['shipping']['title'],
                          'shipping_method_code' => $_SESSION['shipping']['code'],
                          'shipping_module_code' => $_SESSION['shipping']['id'],
                          'shipping_cost' => $_SESSION['shipping']['cost'],
                          'subtotal' => 0,
                          'tax' => 0,
                          'total' => 0,
                          'tax_groups' => array(),
                          'comments' => (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''),
                          'ip_address' => $_SERVER['REMOTE_ADDR']
                          );

//print_r($GLOBALS[$class]);
//echo $class;
//print_r($GLOBALS);
//echo $_SESSION['payment'];
/*
// this is set above to the module filename it should be set to the module title like Checks/Money Order rather than moneyorder
      if (isset($$_SESSION['payment']) && is_object($$_SESSION['payment'])) {
        $this->info['payment_method'] = $$_SESSION['payment']->title;
      }
*/
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

      $this->customer = array('firstname' => $customer_address->fields['customers_firstname'],
                              'lastname' => $customer_address->fields['customers_lastname'],
                              'company' => $customer_address->fields['entry_company'],
                              'street_address' => $customer_address->fields['entry_street_address'],
                              'suburb' => $customer_address->fields['entry_suburb'],
                              'city' => $customer_address->fields['entry_city'],
                              'postcode' => $customer_address->fields['entry_postcode'],
                              'state' => ((zen_not_null($customer_address->fields['entry_state'])) ? $customer_address->fields['entry_state'] : $customer_address->fields['zone_name']),
                              'zone_id' => $customer_address->fields['entry_zone_id'],
                              'country' => array('id' => $customer_address->fields['countries_id'], 'title' => $customer_address->fields['countries_name'], 'iso_code_2' => $customer_address->fields['countries_iso_code_2'], 'iso_code_3' => $customer_address->fields['countries_iso_code_3']),
                              'format_id' => $customer_address->fields['address_format_id'],
                              'telephone' => $customer_address->fields['customers_telephone'],
                              'email_address' => $customer_address->fields['customers_email_address']);

      $this->delivery = array('firstname' => $shipping_address->fields['entry_firstname'],
                              'lastname' => $shipping_address->fields['entry_lastname'],
                              'company' => $shipping_address->fields['entry_company'],
                              'street_address' => $shipping_address->fields['entry_street_address'],
                              'suburb' => $shipping_address->fields['entry_suburb'],
                              'city' => $shipping_address->fields['entry_city'],
                              'postcode' => $shipping_address->fields['entry_postcode'],
                              'state' => ((zen_not_null($shipping_address->fields['entry_state'])) ? $shipping_address->fields['entry_state'] : $shipping_address->fields['zone_name']),
                              'zone_id' => $shipping_address->fields['entry_zone_id'],
                              'country' => array('id' => $shipping_address->fields['countries_id'], 'title' => $shipping_address->fields['countries_name'], 'iso_code_2' => $shipping_address->fields['countries_iso_code_2'], 'iso_code_3' => $shipping_address->fields['countries_iso_code_3']),
                              'country_id' => $shipping_address->fields['entry_country_id'],
                              'telephone' => $shipping_address->fields['entry_telephone'],
                              'format_id' => $shipping_address->fields['address_format_id']);

      $this->billing = array('firstname' => $billing_address->fields['entry_firstname'],
                             'lastname' => $billing_address->fields['entry_lastname'],
                             'company' => $billing_address->fields['entry_company'],
                             'street_address' => $billing_address->fields['entry_street_address'],
                             'suburb' => $billing_address->fields['entry_suburb'],
                             'city' => $billing_address->fields['entry_city'],
                             'postcode' => $billing_address->fields['entry_postcode'],
                             'state' => ((zen_not_null($billing_address->fields['entry_state'])) ? $billing_address->fields['entry_state'] : $billing_address->fields['zone_name']),
                             'zone_id' => $billing_address->fields['entry_zone_id'],
                             'country' => array('id' => $billing_address->fields['countries_id'], 'title' => $billing_address->fields['countries_name'], 'iso_code_2' => $billing_address->fields['countries_iso_code_2'], 'iso_code_3' => $billing_address->fields['countries_iso_code_3']),
                             'country_id' => $billing_address->fields['entry_country_id'],
                             'telephone' => $billing_address->fields['entry_telephone'],
                             'format_id' => $billing_address->fields['address_format_id']);

      $index = 0;
      $products = $_SESSION['cart']->get_products();
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $this->products[$index] = $products[$i];
        $this->products[$index]['final_price'] = $products[$i]['price'] + $_SESSION['cart']->attributes_price($products[$i]['id']);
        $this->products[$index]['onetime_charges'] = $_SESSION['cart']->attributes_price_onetime_charges($products[$i]['id'], $products[$i]['quantity']);
        $this->products[$index]['tax'] = zen_get_tax_rate( $products[$i]['tax_class_id'], $tax_address->fields['entry_country_id'], $tax_address->fields['entry_zone_id'] );
        $this->products[$index]['tax_description'] = zen_get_tax_description( $products[$i]['tax_class_id'], $tax_address->fields['entry_country_id'], $tax_address->fields['entry_zone_id'] );

		if ($products[$i]['attributes']) {
			$subindex = 0;
			reset($products[$i]['attributes']);
			$this->products[$index]['attributes'] = array();
			while (list($option, $value) = each($products[$i]['attributes'])) {
/*
	//clr 030714 Determine if attribute is a text attribute and change products array if it is.
            if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID){
              $attr_value = $products[$i]['attributes_values'][$option];
            } else {
              $attr_value = $attributes->fields['products_options_values_name'];
            }
*/

            $attributes_query = "SELECT popt.`products_options_name`, pa.`products_options_values_name`, pa.`options_values_price`, pa.`price_prefix`
                                 FROM " . TABLE_PRODUCTS_OPTIONS . " popt
                                    INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON(pa.`products_options_id` = popt.`products_options_id`)
									INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id`)
                                 WHERE pom.`products_id` = ?
									AND pa.`products_options_id` = ?
									AND pa.`products_options_values_id` = ?
									AND popt.`language_id` = ? ";

            $attributes = $gBitDb->query( $attributes_query, array( zen_get_prid( $products[$i]['id'] ), zen_get_options_id( $option ), (int)$value, (int)$_SESSION['languages_id'] ) );

	//clr 030714 Determine if attribute is a text attribute and change products array if it is.
            if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID){
              $attr_value = $products[$i]['attributes_values'][$option];
            } else {
              $attr_value = $attributes->fields['products_options_values_name'];
            }

            $this->products[$index]['attributes'][$subindex] = array('option' => $attributes->fields['products_options_name'],
                                                                     'value' => $attr_value,
                                                                     'option_id' => $option,
                                                                     'value_id' => $value,
                                                                     'prefix' => $attributes->fields['price_prefix'],
                                                                     'price' => $attributes->fields['options_values_price']);

            $subindex++;
          }
        }

// add onetime charges here
//$_SESSION['cart']->attributes_price_onetime_charges($products[$i]['id'], $products[$i]['quantity'])

        $shown_price = (zen_add_tax($this->products[$index]['final_price'], $this->products[$index]['tax']) * $this->products[$index]['quantity'])
                      + zen_add_tax($this->products[$index]['onetime_charges'], $this->products[$index]['tax']);
        $this->info['subtotal'] += $shown_price;

        $products_tax = $this->products[$index]['tax'];
        $products_tax_description = $this->products[$index]['tax_description'];
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
        $index++;
      }

      if (DISPLAY_PRICE_WITH_TAX == 'true') {
        $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
      } else {
        $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
      }
    }

    function create($zf_ot_modules, $zf_mode = 2) {
		global $gBitDb;

		$gBitDb->StartTrans();
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
							'customers_country' => $this->customer['country']['title'],
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
							'delivery_country' => $this->delivery['country']['title'],
							'delivery_telephone' => $this->delivery['telephone'],
							'delivery_address_format_id' => $this->delivery['format_id'],
							'billing_name' => $this->billing['firstname'] . ' ' . $this->billing['lastname'],
							'billing_company' => $this->billing['company'],
							'billing_street_address' => $this->billing['street_address'],
							'billing_suburb' => $this->billing['suburb'],
							'billing_city' => $this->billing['city'],
							'billing_postcode' => $this->billing['postcode'],
							'billing_state' => $this->billing['state'],
							'billing_country' => $this->billing['country']['title'],
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
							'date_added' => $this->mDb->NOW(),
							'customer_notified' => $customer_notification,
							'comments' => $this->info['comments']);
		$gBitDb->associateInsert(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

		$gBitDb->CompleteTrans();

		return( $this->mOrdersId );
    }


	function  create_add_products($zf_insert_id, $zf_mode = false) {
		global $gBitDb, $currencies, $order_total_modules, $order_totals;

			$this->mDb->StartTrans();
	// initialized for the email confirmation

		$this->products_ordered = '';
		$this->products_ordered_html = '';
		$this->subtotal = 0;
		$this->total_tax = 0;

	// lowstock email report
		$this->email_low_stock='';

		for ($i=0, $n=count( $this->products ); $i<$n; $i++) {
			// Stock Update - Joao Correia
			if (STOCK_LIMITED == 'true') {
				if (DOWNLOAD_ENABLED == 'true') {
					$stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
										FROM " . TABLE_PRODUCTS . " p
											LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(p.`products_id`=pom.`products_id`)
											LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON (pa.`products_options_values_id`=pom.`products_options_values_id`)
											LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad ON(pa.`products_attributes_id`=pad.`products_attributes_id`)
										WHERE p.`products_id` = ?";
					$bindVars = array( zen_get_prid($this->products[$i]['id']) );

					// Will work with only one option for downloadable products
					// otherwise, we have to build the query dynamically with a loop
					$products_attributes = $this->products[$i]['attributes'];
					if( is_array( $products_attributes ) ) {
						$stock_query_raw .= " AND pa.`products_options_id` = ? AND pa.`products_options_values_id` = ?";
						$bindVars[] = zen_get_options_id( $products_attributes[0]['option_id'] );
						$bindVars[] = $products_attributes[0]['value_id'];
					}
					$stock_values = $gBitDb->query($stock_query_raw, $bindVars);
				} else {
					$stock_values = $gBitDb->Execute("select `products_quantity` from " . TABLE_PRODUCTS . " where `products_id` = '" . zen_get_prid($this->products[$i]['id']) . "'");
				}

				if ($stock_values->RecordCount() > 0) {
					// do not decrement quantities if products_attributes_filename exists
					if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values->fields['products_attributes_filename'])) {
						$stock_left = $stock_values->fields['products_quantity'] - $this->products[$i]['quantity'];
						$this->products[$i]['stock_reduce'] = $this->products[$i]['quantity'];
					} else {
						$stock_left = $stock_values->fields['products_quantity'];
					}

		//            $this->products[$i]['stock_value'] = $stock_values->fields['products_quantity'];

					$gBitDb->Execute("update " . TABLE_PRODUCTS . " set `products_quantity` = '" . $stock_left . "' where `products_id` = '" . zen_get_prid($this->products[$i]['id']) . "'");
		//        if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
					if ($stock_left < 1) {
						// only set status to off when not displaying sold out
						if (SHOW_PRODUCTS_SOLD_OUT == '0') {
							$gBitDb->Execute("update " . TABLE_PRODUCTS . " set `products_status` = '0' where `products_id` = '" . zen_get_prid($this->products[$i]['id']) . "'");
						}
					}

		// for low stock email
					if ( $stock_left <= STOCK_REORDER_LEVEL ) {
				// WebMakers.com Added: add to low stock email
					$this->email_low_stock .=  'ID# ' . zen_get_prid($this->products[$i]['id']) . "\t\t" . $this->products[$i]['model'] . "\t\t" . $this->products[$i]['name'] . "\t\t" . ' Qty Left: ' . $stock_left . "\n";
					}
				}
			}

			// Update products_ordered (for bestsellers list)
			$gBitDb->Execute("update " . TABLE_PRODUCTS . " set `products_ordered` = `products_ordered` + " . sprintf('%f', $this->products[$i]['quantity']) . " where `products_id` = '" . zen_get_prid($this->products[$i]['id']) . "'");

			$sql_data_array = array('orders_id' => $zf_insert_id,
								'products_id' => zen_get_prid($this->products[$i]['id']),
								'products_model' => $this->products[$i]['model'],
								'products_name' => $this->products[$i]['name'],
								'products_price' => $this->products[$i]['price'],
								'products_commission' => $this->products[$i]['commission'],
								'final_price' => $this->products[$i]['final_price'],
								'onetime_charges' => $this->products[$i]['onetime_charges'],
								'products_tax' => $this->products[$i]['tax'],
								'products_quantity' => $this->products[$i]['quantity'],
								'products_priced_by_attribute' => $this->products[$i]['products_priced_by_attribute'],
								'product_is_free' => $this->products[$i]['product_is_free'],
								'products_discount_type' => $this->products[$i]['products_discount_type'],
								'products_discount_type_from' => $this->products[$i]['products_discount_type_from']);
			$gBitDb->associateInsert(TABLE_ORDERS_PRODUCTS, $sql_data_array);
			$this->products[$i]['orders_products_id'] = zen_db_insert_id( TABLE_ORDERS_PRODUCTS, 'orders_products_id' );

			$order_total_modules->update_credit_account($i);//ICW ADDED FOR CREDIT CLASS SYSTEM

			if( !empty( $this->products[$i]['purchase_group_id'] ) ) {
				global $gBitUser;
				$gBitUser->addUserToGroup( $gBitUser->mUserId, $this->products[$i]['purchase_group_id'] );
			}


	//------insert customer choosen option to order--------
			$attributes_exist = '0';
			$this->products_ordered_attributes = '';
			if( !empty($this->products[$i]['attributes']) ) {
				$attributes_exist = '1';
				for ($j=0, $n2=count( $this->products[$i]['attributes'] ); $j<$n2; $j++) {
					if (DOWNLOAD_ENABLED == 'true') {
						$attributes_query = "SELECT popt.`products_options_name`, pa.`products_options_values_name`, pom.*, pa.*, pad.`products_attributes_maxdays`, pad.`products_attributes_maxcount`, pad.`products_attributes_filename`
										FROM " . TABLE_PRODUCTS_OPTIONS . " popt 
											INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON (pa.`products_options_id` = popt.`products_options_id`)
											INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`products_options_values_id`=pom.`products_options_values_id`)
											LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on(pa.`products_attributes_id` = pad.`products_attributes_id`)
										WHERE pom.`products_id` = ? AND pa.`products_options_id` = ? AND pa.`products_options_values_id` = ? AND popt.`language_id` = ?";

						$attributes_values = $gBitDb->query($attributes_query, array( zen_get_prid( $this->products[$i]['id'] ), (int)$this->products[$i]['attributes'][$j]['option_id'], (int)$this->products[$i]['attributes'][$j]['value_id'], $_SESSION['languages_id'] ) );
					} else {
						$attributes_values = $gBitDb->query("select popt.`products_options_name`, pa.*
										FROM " . TABLE_PRODUCTS_OPTIONS . " popt 
											INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON(pa.`products_options_id` = popt.`products_options_id`)
											INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`products_options_values_id`=pom.`products_options_values_id`)
										WHERE pom.`products_id`=? and pa.`products_options_id`=? AND pa.`products_options_values_id`=? AND popt.`language_id`=?", array( zen_get_prid( $this->products[$i]['id'] ), $this->products[$i]['attributes'][$j]['option_id'], $this->products[$i]['attributes'][$j]['value_id'], $_SESSION['languages_id'] ) );
					}

					if( !empty( $attributes_values->fields['products_options_id'] ) ) {
						//clr 030714 update insert query.  changing to use values form $order->products for products_options_values.
						$sql_data_array = array('orders_id' => $zf_insert_id,
												'orders_products_id' => $this->products[$i]['orders_products_id'],
												'products_options' => $attributes_values->fields['products_options_name'],
	
				//                                'products_options_values' => $attributes_values->fields['products_options_values_name'],
												'products_options_values' => $this->products[$i]['attributes'][$j]['value'],
												'options_values_price' => $attributes_values->fields['options_values_price'],
												'price_prefix' => $attributes_values->fields['price_prefix'],
												'product_attribute_is_free' => $attributes_values->fields['product_attribute_is_free'],
												'products_attributes_wt' => $attributes_values->fields['products_attributes_wt'],
												'products_attributes_wt_pfix' => $attributes_values->fields['products_attributes_wt_pfix'],
												'attributes_discounted' => (int)$attributes_values->fields['attributes_discounted'],
												'attributes_price_base_inc' => (int)$attributes_values->fields['attributes_price_base_inc'],
												'attributes_price_onetime' => $attributes_values->fields['attributes_price_onetime'],
												'attributes_price_factor' => $attributes_values->fields['attributes_price_factor'],
												'attributes_pf_offset' => $attributes_values->fields['attributes_pf_offset'],
												'attributes_pf_onetime' => $attributes_values->fields['attributes_pf_onetime'],
												'attributes_pf_onetime_offset' => $attributes_values->fields['attributes_pf_onetime_offset'],
												'attributes_qty_prices' => $attributes_values->fields['attributes_qty_prices'],
												'attributes_qty_prices_onetime' => $attributes_values->fields['attributes_qty_prices_onetime'],
												'attributes_price_words' => $attributes_values->fields['attributes_price_words'],
												'attributes_price_words_free' => $attributes_values->fields['attributes_price_words_free'],
												'attributes_price_letters' => $attributes_values->fields['attributes_price_letters'],
												'attributes_price_letters_free' => $attributes_values->fields['attributes_price_letters_free'],
												'products_options_id' => $attributes_values->fields['products_options_id'],
												'products_options_values_id' => $attributes_values->fields['products_options_values_id'],
												);
	
	
						$gBitDb->associateInsert(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
					}

					if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values->fields['products_attributes_filename']) && zen_not_null($attributes_values->fields['products_attributes_filename'])) {
						$sql_data_array = array('orders_id' => $zf_insert_id,
												'orders_products_id' => $this->products[$i]['orders_products_id'],
												'orders_products_filename' => $attributes_values->fields['products_attributes_filename'],
												'download_maxdays' => $attributes_values->fields['products_attributes_maxdays'],
												'download_count' => $attributes_values->fields['products_attributes_maxcount']);

						$gBitDb->associateInsert(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
					}
			//clr 030714 changing to use values from $orders->products and adding call to zen_decode_specialchars()
			//        $this->products_ordered_attributes .= "\n\t" . $attributes_values->fields['products_options_name'] . ' ' . $attributes_values->fields['products_options_values_name'];
					$this->products_ordered_attributes .= "\n\t" . $attributes_values->fields['products_options_name'] . ' ' . zen_decode_specialchars($this->products[$i]['attributes'][$j]['value']);
				}
			}
			//------insert customer choosen option eof ----
			$this->total_weight += ($this->products[$i]['quantity'] * $this->products[$i]['weight']);
			$this->total_tax += zen_calculate_tax($total_products_price, $products_tax) * $this->products[$i]['quantity'];
			$this->total_cost += $total_products_price;

			// include onetime charges
			$this->products_ordered .=  $this->products[$i]['quantity'] . ' x ' . $this->products[$i]['name'] . ($this->products[$i]['model'] != '' ? ' (' . $this->products[$i]['model'] . ') ' : '') . ' = ' .
									$currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['quantity']) .
									($this->products[$i]['onetime_charges'] !=0 ? "\n" . TEXT_ONETIME_CHARGES_EMAIL . $currencies->display_price($this->products[$i]['onetime_charges'], $this->products[$i]['tax'], 1) : '') .
									$this->products_ordered_attributes . "\n";
			$this->products_ordered_html .=
			'<tr>' .
			'<td class="product-details" align="right" valign="top" width="30">' . $this->products[$i]['quantity'] . '&nbsp;x</td>' .
			'<td class="product-details" valign="top">' . $this->products[$i]['name'] . ($this->products[$i]['model'] != '' ? ' (' . $this->products[$i]['model'] . ') ' : '') .
			'<nobr><small><em> '. $this->products_ordered_attributes .'</em></small></nobr></td>' .
			'<td class="product-details-num" valign="top" align="right">' .
				$currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['quantity']) .
				($this->products[$i]['onetime_charges'] !=0 ?
						'</td></tr><tr><td class="product-details">' . TEXT_ONETIME_CHARGES_EMAIL . '</td>' .
						'<td>' . $currencies->display_price($this->products[$i]['onetime_charges'], $this->products[$i]['tax'], 1) : '') .
			'</td></tr>';
		}

		$order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM
		$this->mDb->CompleteTrans();
    }


    function send_order_email($zf_insert_id, $zf_mode) {
      global $currencies, $order_totals;

//      print_r($this);
//      die();
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
        $html_msg['EMAIL_TEXT_HEADER']     = EMAIL_TEXT_HEADER;
        $html_msg['EMAIL_TEXT_FROM']       = EMAIL_TEXT_FROM;
        $html_msg['INTRO_STORE_NAME']      = STORE_NAME;
        $html_msg['EMAIL_THANKS_FOR_SHOPPING'] = EMAIL_THANKS_FOR_SHOPPING;
        $html_msg['EMAIL_DETAILS_FOLLOW']  = EMAIL_DETAILS_FOLLOW;
        $html_msg['INTRO_ORDER_NUM_TITLE'] = EMAIL_TEXT_ORDER_NUMBER;
        $html_msg['INTRO_ORDER_NUMBER']    = $zf_insert_id;
        $html_msg['INTRO_DATE_TITLE']      = EMAIL_TEXT_DATE_ORDERED;
        $html_msg['INTRO_DATE_ORDERED']    = strftime(DATE_FORMAT_LONG);
        $html_msg['INTRO_URL_TEXT']        = EMAIL_TEXT_INVOICE_URL_CLICK;
        $html_msg['INTRO_URL_VALUE']       = zen_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $zf_insert_id, 'SSL', false);

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
        $html_ot .= '<td class="order-totals-text" align="right" width="100%">' . '&nbsp;' . '</td><td class="order-totals-num" align="right" nowrap="nowrap">' . '---------' .'</td></tr><tr>';
        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
          $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      	$html_ot .= '<td class="order-totals-text" align="right" width="100%">' . $order_totals[$i]['title'] . '</td><td class="order-totals-num" align="right" nowrap="nowrap">' .($order_totals[$i]['text']) .'</td></tr><tr>';
        }
        $html_msg['ORDER_TOTALS'] = '<table border="0" width="100%" cellspacing="0" cellpadding="2">' . $html_ot . '</table>';

//addresses area: Delivery
        $html_msg['HEADING_ADDRESS_INFORMATION']= HEADING_ADDRESS_INFORMATION;
	  $html_msg['ADDRESS_DELIVERY_TITLE']     = EMAIL_TEXT_DELIVERY_ADDRESS;
	  $html_msg['ADDRESS_DELIVERY_DETAIL']    = ($this->content_type != 'virtual') ? zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, '', "<br />") : 'n/a';
	  $html_msg['SHIPPING_METHOD_TITLE']      = HEADING_SHIPPING_METHOD;
	  $html_msg['SHIPPING_METHOD_DETAIL']     = (zen_not_null($this->info['shipping_method'])) ? $this->info['shipping_method'] : 'n/a';

        if ($this->content_type != 'virtual') {
          $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                          EMAIL_SEPARATOR . "\n" .
         zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") . "\n";
        }

//addresses area: Billing
        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                       zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") . "\n\n";
	  $html_msg['ADDRESS_BILLING_TITLE']   = EMAIL_TEXT_BILLING_ADDRESS;
	  $html_msg['ADDRESS_BILLING_DETAIL']  = zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, '', "<br />");

    if (is_object($GLOBALS[$_SESSION['payment']])) {
          $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                          EMAIL_SEPARATOR . "\n";
          $payment_class = $_SESSION['payment'];
          $email_order .= $GLOBALS[$payment_class]->title . "\n\n";
          if ($GLOBALS[$payment_class]->email_footer) {
            $email_order .= $GLOBALS[$payment_class]->email_footer . "\n\n";
          }
        }
	  $html_msg['PAYMENT_METHOD_TITLE']  = (is_object($GLOBALS[$_SESSION['payment']]) ? EMAIL_TEXT_PAYMENT_METHOD : '') ;
	  $html_msg['PAYMENT_METHOD_DETAIL'] = (is_object($GLOBALS[$_SESSION['payment']]) ? $GLOBALS[$payment_class]->title : '' );
	  $html_msg['PAYMENT_METHOD_FOOTER'] = (is_object($GLOBALS[$_SESSION['payment']]) ? $GLOBALS[$payment_class]->email_footer : '');

// include disclaimer
        $email_order .= "\n-----\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS) . "\n\n";
// include copyright
        $email_order .= "\n-----\n" . EMAIL_FOOTER_COPYRIGHT . "\n\n";

        while (strstr($email_order, '&nbsp;')) $email_order = str_replace('&nbsp;', ' ', $email_order);

        $html_msg['EMAIL_FIRST_NAME'] = $this->customer['firstname'];
        $html_msg['EMAIL_LAST_NAME'] = $this->customer['lastname'];
//  $html_msg['EMAIL_TEXT_HEADER'] = EMAIL_TEXT_HEADER;
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

				$html_msg['EMAIL_CUSTOMERS_NAME']    = $this->customer['name'];
				$html_msg['EMAIL_TEXT_ORDER_NUMBER'] = tra( 'Order Number' ) . ': ' . $this->mOrdersId;
				$html_msg['EMAIL_TEXT_INVOICE_URL']  = $this->getDisplayLink();
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
			$this->mDb->query( "UPDATE ". TABLE_ORDERS . " SET `order_total`=?, `order_tax`=? WHERE `orders_id`=?", array( ($sourceHash['order_total'] + $destHash['order_total']), ($sourceHash['order_tax'] + $destHash['order_tax']), $pParamHash['dest_orders_id'] ) );

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
			zen_remove_order( $pParamHash['source_orders_id'], FALSE );
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
		if (ENABLE_SSL_CATALOG == 'true') {
			$link = HTTPS_CATALOG_SERVER . DIR_WS_HTTPS_CATALOG;
		} else {
			$link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
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

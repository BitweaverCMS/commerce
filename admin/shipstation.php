<?php

/*
 * ShipStation Shipping Module for Zen Cart, Version 1.9 for Zen Cart 1.5.8+
 *
 * Copyright (c) 2011 Auctane LLC
 *
 * Find out more about ShipStation at www.shipstation.com
 *
 * Released under the GNU General Public License v2
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; under version 2 of the License
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA 02110-1301, USA.
 */

global $gLightWeightScan, $gBitProduct;
$gLightWeightScan = TRUE;
require_once( '../../kernel/includes/setup_inc.php' );
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php' );

global $gBitDb;

$gBitUser->login( $_GET['SS-UserName'], $_GET['SS-Password'], FALSE, FALSE );

define('DATE_TIME_FORMAT', '%m/%d/%Y' . ' %H:%M:%S');
//set the content type to xml
//header('Content-Type: text/xml');

/**
 * function to add noode to the xml
 * 
 * @para $FieldName string
 * @para $Value string
 * @retrurn xml
 */
function AddFieldToXML($FieldName, $Value) {
	$FindStr = "&";
	$NewStr = "&amp;";
	$Result = str_replace($FindStr, $NewStr, $Value);
	echo "\t\t<$FieldName>$Result</$FieldName>\n";
}

/**
 * function to convert the start and end date with zend date format
 * 
 * @para $date date
 * @para $reverse boolean
 * @retrurn date
 */
function zen_date_raw2($date, $reverse = false) {
	if ($reverse) {
		return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
	} else {
		return substr($date, 6, 4) . '-' . substr($date, 0, 2) . '-' . substr($date, 3, 2) . ' ' . substr($date, 11, 2) . '.' . substr($date, 14, 2) . '.' . '00';
	}
}

/**
 * function to convert the ordr end date with date time format
 * 
 * @para $raw_datetime date
 * @retrurn date
 */
function zen_datetime_short2($raw_datetime) {
	return date( 'm/d/Y H:i:s', strtotime( $raw_datetime ) );
}

// SSZen.php?action=export&start_date=06/02/2009%2005:30&end_date=11/05/2009%2022:53
switch( $_GET['action'] ) {
	case 'export':
		if( BitBase::verifyIdParameter( $_REQUEST, 'orders_status_id', ) ) {
		//get order from database
			if( $orderIds = $gBitDb->getCol( "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_status_id = ?", array( $_REQUEST['orders_status_id'] ) ) ) {
				//begin outputing XML
		//		echo "<?xml version=\"1.0\" encoding=\"utf-16\" ? >\n";
				echo "<Orders>\n";
				//process orders
				foreach( $orderIds as $orderId ) {
					$order = new order( $orderId );
					$order->loadHistory();	

					$cust_id = $order->getField( 'customers_id' );

					$ship_order_id = $orderId;
					$ship_query = "SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '$ship_order_id' and class = 'ot_shipping'";
					$ship_result = $gBitDb->Execute($ship_query);

					$last_modified = '';
					$shipping_comments = '';

					echo "\t<Order>\n";

					//order details
					AddFieldToXML("OrderNumber", $orderId);
					AddFieldToXML("OrderDate", zen_datetime_short2($order->info['date_purchased']));
					AddFieldToXML("OrderStatusCode", $order->getField( 'order_status_id' ) );
					AddFieldToXML("OrderStatusName", $order->getField( 'order_status' ) );
					AddFieldToXML("LastModified", $last_modified);
					AddFieldToXML("LastModifiedOrderTable", $order->info['last_modified']);
					if( count( $order->mPayments ) ) {
						$payment = current( $order->mPayments );
		//				AddFieldToXML("PaymentMethod", '<![CDATA[' . $firstPayment['payment_method'] . ']]>');
		//				AddFieldToXML("PaymentMethodCode", $firstPayment['payment_module_code']);
						AddFieldToXML("Currency", $payment['payment_currency']);
						AddFieldToXML("CurrencyValue", $payment['exchange_rate']);
					} 
					AddFieldToXML("ShippingMethod", '<![CDATA[' . $order->info['shipping_method'] . ']]>');
					//AddFieldToXML("ShippingMethodCode", $order->info['shipping_module_code']);
					AddFieldToXML("ShippingMethodCode", '<![CDATA[' . $order->info['shipping_method'] . ']]>');
					AddFieldToXML("CouponCode", $order->getField( 'coupon_code' ) );
					AddFieldToXML("OrderTotal", $order->getField( 'total' ) );
					AddFieldToXML("TaxAmount", $order->getField( 'tax' ) );
					AddFieldToXML("ShippingAmount", $ship_result->fields['orders_value']);
					if( !empty( $order->mHistory['0']['comments'] ) ) {
						AddFieldToXML("CommentsFromBuyer", '<![CDATA[' . $order->mHistory['0']['comments'] . ']]>');
					}
					//order details
					//customer details
					echo "\t<Customer>\n";

					AddFieldToXML("CustomerNumber", $order->getField( 'customers_id' ) );

					//billing details
					echo "\t<BillTo>\n";

					$billing_state = $order->billing['state'];

					AddFieldToXML("Name", '<![CDATA[' . $order->billing['name'] . ']]>');
					AddFieldToXML("Company", '<![CDATA[' . $order->billing['company'] . ']]>');
					AddFieldToXML("Address1", '<![CDATA[' . $order->billing['street_address'] . ']]>');
					AddFieldToXML("Address2", '<![CDATA[' . $order->billing['suburb'] . ']]>');
					AddFieldToXML("City", '<![CDATA[' . $order->billing['city'] . ']]>');
					AddFieldToXML("State", '<![CDATA[' . $order->billing['state'] . ']]>');
					AddFieldToXML("StateCode", $order->billing['zone_code']);
					AddFieldToXML("PostalCode", $order->billing['postcode']);
					AddFieldToXML("Country", $order->billing['countries_name']);
					AddFieldToXML("CountryCode", $order->billing['countries_iso_code_2']);
					AddFieldToXML("Phone", $order->billing['telephone']);
					AddFieldToXML("Email", $order->customer['email_address']);
					//	AddFieldToXML("CountryCode", $country_result->fields['countries_iso_code_2']);

					echo "\t</BillTo>\n";
					//billing details
					//shipping details

					echo "\t<ShipTo>\n";

					$shipping_state = $order->delivery['state'];

					AddFieldToXML("Name", '<![CDATA[' . $order->delivery['name'] . ']]>');
					AddFieldToXML("Company", '<![CDATA[' . $order->delivery['company'] . ']]>');
					AddFieldToXML("Address1", '<![CDATA[' . $order->delivery['street_address'] . ']]>');
					AddFieldToXML("Address2", '<![CDATA[' . $order->delivery['suburb'] . ']]>');
					AddFieldToXML("City", '<![CDATA[' . $order->delivery['city'] . ']]>');
					AddFieldToXML("State", '<![CDATA[' . $shipping_state . ']]>');
					if( $order->delivery['zone_code'] ) { 
					   AddFieldToXML("StateCode", $order->delivery['zone_code'] );
					}
					AddFieldToXML("PostalCode", $order->delivery['postcode']);
					AddFieldToXML("Country", $order->delivery['countries_name']);
					AddFieldToXML("CountryCode", $order->delivery['countries_iso_code_2']);

					echo "\t</ShipTo>\n";
					//shipping details

					echo "\t</Customer>\n";
					//customer details
					echo "\t<Items>\n";

					//process Order Items
					//get order items from datbase
					foreach( $order->contents as $orderItemId => $orderItem ) {

						$product = bc_get_commerce_product( $orderItem['products_id'] );

						echo "\t<Item>\n";
						AddFieldToXML("ProductID", $orderItem['products_id']);
						AddFieldToXML("SKU", '<![CDATA[' . $orderItem['products_id'] . ']]>');
						AddFieldToXML("Name", '<![CDATA[' . $orderItem['products_name'] . ']]>');

						AddFieldToXML("ImageUrl", BIT_BASE_URI.$product->getThumbnailUrlFromOrder( $orderItem['attributes'] ) );
						AddFieldToXML("Weight", $product->getWeight( $orderItem['products_quantity'], $orderItem['attributes'] ) );
						AddFieldToXML("UnitPrice", round($orderItem['final_price'], 2));
						AddFieldToXML("TaxAmount", round($orderItem['products_tax'], 2));
						AddFieldToXML("Quantity", $orderItem['products_quantity']);

						if( !empty( $orderItem['attributes'] ) ) {
						   echo "\t<Options>\n";

						   foreach( $orderItem['attributes'] as $attr ) {
							   echo "\t<Option><Name><![CDATA[" . $attr['products_options'] . "]]></Name><Value><![CDATA[" . $attr['products_options_values'] . "]]></Value></Option>\n";
						   }

						   echo "\t</Options>\n";
						}
						echo "\t</Item>\n";
					}
					//process Order Items
					echo "\t</Items>\n";
					echo "\t</Order>\n";
				}

				//process Orders
				//finish outputing XML
				echo "</Orders>";
			} else {

				echo "<?xml version=\"1.0\" encoding=\"utf-16\"?>\n";
				echo "<Orders />\n";
			}
		} else {	
			echo "The Store URL in your ShipStation connection must specify numeric order_status_id for ship ready orders to be imported into ShipStation, e.g. ".$_SERVER['SCRIPT_URI'].'?order_status_id=40';
		}
		break;
	case 'verifystatus':
		echo 'true';
		break;
	case 'update':

		//?action=update&order_number=ABC123&status=4&comment=commment
		userAuthentication($gBitDb);
		if ($_GET['order_number']) {

			$status = strtolower($_GET['status']);
			$customer_notified = '0';
			$comments = $_GET['comment'];


			$record_query = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = '" . $_GET['order_number'] . "'";
			$record_result = $gBitDb->Execute($record_query);

			if ($record_result->fields['orders_id']) {

				$orders_status = $gBitDb->Execute("select orders_status_id, orders_status_name
										 from " . TABLE_ORDERS_STATUS . "
										 where language_id = '" . (int) $_SESSION['languages_id'] . "' and LOWER(orders_status_name) = '" . $status . "'");

				if ($orders_status->fields['orders_status_id']) {

					$status = $orders_status->fields['orders_status_id'];

					zen_update_orders_history((int)$_GET['order_number'], $comments, (!empty($_GET['SS-UserName']) ? zen_db_input($_GET['SS-UserName']) : 'ShipStation'), $status, $customer_notified, false);
					echo 'Status updated successfully';
				} else {
					echo 'No order status in database';
				}
			} else {
				echo 'Order does not exist in database';
			}
		} else {
			echo 'No order number';
		}
		break;

	default:
		echo 'No action parameter. Please contact software provider.';
		break;
}

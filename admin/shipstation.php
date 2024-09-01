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
?>
<?php

require('includes/application_top.php');
define('DATE_TIME_FORMAT', '%m/%d/%Y' . ' %H:%M:%S');
//set the content type to xml
header('Content-Type: text/xml');

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
 * function to authenticate username and password sent from the shipstation
 * 
 * @para $db object
 * @retrurn boolean
 */
function userAuthentication($db = null) {
    $un = $_GET['SS-UserName'];
    $pw = $_GET['SS-Password'];
    if (!isset($un) || strlen($un) == 0) {
        header('WWW-Authenticate: Basic realm="ShipStation"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Unauthorized';
        exit;
    } else {
        $admin_query = "SELECT admin_id, admin_pass FROM " . TABLE_ADMIN . " WHERE admin_name = '" . $un . "'";
        $admin_result = $db->Execute($admin_query);
        //validate the password with the zencart encrypted password
        if (!zen_validate_password($pw, $admin_result->fields['admin_pass'])) {
            header('WWW-Authenticate: Basic realm="ShipStation"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Unauthorized';
            exit;
        }
    }
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
    global $zcDate;

    if (($raw_datetime == '0001-01-01 00:00:00') || ($raw_datetime == ''))
        return false;

    $year = (int) substr($raw_datetime, 0, 4);
    $month = (int) substr($raw_datetime, 5, 2);
    $day = (int) substr($raw_datetime, 8, 2);
    $hour = (int) substr($raw_datetime, 11, 2);
    $minute = (int) substr($raw_datetime, 14, 2);
    $second = (int) substr($raw_datetime, 17, 2);

    return $zcDate->output(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
}

// SSZen.php?action=export&start_date=06/02/2009%2005:30&end_date=11/05/2009%2022:53
if ($_GET['action'] == 'export') {
    //call the user authentication before start of order data replication
    userAuthentication($db);
    $sd = zen_date_raw2((!isset($_GET['start_date']) ? date("m-d-Y", (time())) : $_GET['start_date']));
    $ed = zen_date_raw2((!isset($_GET['end_date']) ? date("m-d-Y", (time())) : $_GET['end_date']));

    //get order from database
    $orders_query = "SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id >'0' and IFNULL(last_modified, date_purchased) BETWEEN '" . $sd . "' AND DATE_ADD('" . $ed . "', INTERVAL 1 MINUTE)";
    $orders_result = $db->Execute($orders_query);

    if (!$orders_result->EOF) {
        //begin outputing XML
        echo "<?xml version=\"1.0\" encoding=\"utf-16\"?>\n";
        echo "<Orders>\n";
        //process orders
        while (!$orders_result->EOF) {
            //get order items from datbase
            $orderitems_query = "SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = '" . $orders_result->fields['orders_id'] . "'";
            $orderitems_result = $db->Execute($orderitems_query);

            $cust_id = $orders_result->fields['customers_id'];

            //billing country code
            $billing_country_id = $orders_result->fields['billing_country'];
            $billing_query = "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_name = '$billing_country_id'";
            $billing_result = $db->Execute($billing_query);
            //billing country code
            //shipping country code
            $shipping_country_id = $orders_result->fields['delivery_country'];
            $shipping_query = "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_name = '$shipping_country_id'";
            $shipping_result = $db->Execute($shipping_query);
            //billing zone code
            $billing_zone_id = addslashes($orders_result->fields['billing_state']);
            $zone_billing_query = "SELECT * FROM " . TABLE_ZONES . " WHERE zone_name = '$billing_zone_id'";
            $zone_billing_result = $db->Execute($zone_billing_query);
            //billing zone code
            //shipping zone code
            $shipping_zone_id = addslashes($orders_result->fields['delivery_state']);
            $zone_shipping_query = "SELECT * FROM " . TABLE_ZONES . " WHERE zone_name = '$shipping_zone_id'";
            $zone_shipping_result = $db->Execute($zone_shipping_query);
            //shipping zone code

            $ship_order_id = $orders_result->fields['orders_id'];
            $ship_query = "SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = '$ship_order_id' and class = 'ot_shipping'";
            $ship_result = $db->Execute($ship_query);

            $orders_status = $db->Execute("select orders_status_id, orders_status_name
	                                 from " . TABLE_ORDERS_STATUS . "
	                                 where language_id = '" . (int) $_SESSION['languages_id'] . "' and orders_status_id = '" . $orders_result->fields['orders_status'] . "'");

            if ($orders_status->fields['orders_status_name']) {
                $order_status = $orders_status->fields['orders_status_name'];
                $order_status_id = $orders_status->fields['orders_status_id'];
            } else {
                $order_status = '';
                $order_status_id = '';
            }

            if ($order_status_id) {
                $orders_history = $db->Execute("select date_added
	                                    from " . TABLE_ORDERS_STATUS_HISTORY . "
	                                    where orders_id = '" . zen_db_input($orders_result->fields['orders_id']) . "' and orders_status_id = '" . $order_status_id . "'
	                                    order by date_added LIMIT 1");

                $last_modified = zen_datetime_short2($orders_history->fields['date_added']);

                $orders_history_comments = $db->Execute("select comments
	                                      from " . TABLE_ORDERS_STATUS_HISTORY . "
	                                      where orders_id = '" . zen_db_input($orders_result->fields['orders_id']) . "' and (comments is not null or comments != '')
	                                    order by date_added LIMIT 1");

                $shipping_comments = $orders_history_comments->fields['comments'];
            } else {
                $last_modified = '';
                $shipping_comments = '';
            }

            echo "\t<Order>\n";

            //order details
            AddFieldToXML("OrderNumber", $orders_result->fields['orders_id']);
            AddFieldToXML("OrderDate", zen_datetime_short2($orders_result->fields['date_purchased']));
            AddFieldToXML("OrderStatusCode", $order_status_id);
            AddFieldToXML("OrderStatusName", $order_status);
            AddFieldToXML("LastModified", $last_modified);
            AddFieldToXML("LastModifiedOrderTable", $orders_result->fields['last_modified']);
            AddFieldToXML("date_purchased", $orders_result->fields['date_purchased']);
            AddFieldToXML("PaymentMethod", '<![CDATA[' . $orders_result->fields['payment_method'] . ']]>');
            AddFieldToXML("PaymentMethodCode", $orders_result->fields['payment_module_code']);
            AddFieldToXML("ShippingMethod", '<![CDATA[' . $orders_result->fields['shipping_method'] . ']]>');
            //AddFieldToXML("ShippingMethodCode", $orders_result->fields['shipping_module_code']);
            AddFieldToXML("ShippingMethodCode", '<![CDATA[' . $orders_result->fields['shipping_method'] . ']]>');
            AddFieldToXML("CouponCode", $orders_result->fields['coupon_code']);
            AddFieldToXML("Currency", $orders_result->fields['currency']);
            AddFieldToXML("CurrencyValue", $orders_result->fields['currency_value']);
            AddFieldToXML("OrderTotal", $orders_result->fields['order_total']);
            AddFieldToXML("TaxAmount", $orders_result->fields['order_tax']);
            AddFieldToXML("ShippingAmount", $ship_result->fields['value']);
            AddFieldToXML("CommentsFromBuyer", '<![CDATA[' . $shipping_comments . ']]>');
            //order details
            //customer details
            echo "\t<Customer>\n";

            AddFieldToXML("CustomerNumber", $orders_result->fields['customers_id']);

            //billing details
            echo "\t<BillTo>\n";

            $billing_state = $orders_result->fields['billing_state'];

            AddFieldToXML("Name", '<![CDATA[' . $orders_result->fields['billing_name'] . ']]>');
            AddFieldToXML("Company", '<![CDATA[' . $orders_result->fields['billing_company'] . ']]>');
            AddFieldToXML("Address1", '<![CDATA[' . $orders_result->fields['billing_street_address'] . ']]>');
            AddFieldToXML("Address2", '<![CDATA[' . $orders_result->fields['billing_suburb'] . ']]>');
            AddFieldToXML("City", '<![CDATA[' . $orders_result->fields['billing_city'] . ']]>');
            AddFieldToXML("State", '<![CDATA[' . $billing_state . ']]>');
            if (!$zone_billing_result->EOF) { 
               AddFieldToXML("StateCode", $zone_billing_result->fields['zone_code']);
            }
            AddFieldToXML("PostalCode", $orders_result->fields['billing_postcode']);
            AddFieldToXML("Country", $orders_result->fields['billing_country']);
            AddFieldToXML("CountryCode", $billing_result->fields['countries_iso_code_2']);
            AddFieldToXML("Phone", $orders_result->fields['customers_telephone']);
            AddFieldToXML("Email", $orders_result->fields['customers_email_address']);
            //	AddFieldToXML("CountryCode", $country_result->fields['countries_iso_code_2']);

            echo "\t</BillTo>\n";
            //billing details
            //shipping details

            echo "\t<ShipTo>\n";

            $shipping_state = $orders_result->fields['delivery_state'];

            AddFieldToXML("Name", '<![CDATA[' . $orders_result->fields['delivery_name'] . ']]>');
            AddFieldToXML("Company", '<![CDATA[' . $orders_result->fields['delivery_company'] . ']]>');
            AddFieldToXML("Address1", '<![CDATA[' . $orders_result->fields['delivery_street_address'] . ']]>');
            AddFieldToXML("Address2", '<![CDATA[' . $orders_result->fields['delivery_suburb'] . ']]>');
            AddFieldToXML("City", '<![CDATA[' . $orders_result->fields['delivery_city'] . ']]>');
            AddFieldToXML("State", '<![CDATA[' . $shipping_state . ']]>');
            if (!$zone_shipping_result->EOF) { 
               AddFieldToXML("StateCode", $zone_shipping_result->fields['zone_code']);
            }
            AddFieldToXML("PostalCode", $orders_result->fields['delivery_postcode']);
            AddFieldToXML("Country", $orders_result->fields['delivery_country']);
            AddFieldToXML("CountryCode", $shipping_result->fields['countries_iso_code_2']);

            echo "\t</ShipTo>\n";
            //shipping details

            echo "\t</Customer>\n";
            //customer details
            echo "\t<Items>\n";
            //process Order Items
            while (!$orderitems_result->EOF) {

                $image_query = "SELECT products_image, products_weight FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . $orderitems_result->fields['products_id'] . "'";
                $image_result = $db->Execute($image_query);

                echo "\t<Item>\n";
                AddFieldToXML("ProductID", $orderitems_result->fields['products_id']);
                AddFieldToXML("SKU", '<![CDATA[' . $orderitems_result->fields['products_model'] . ']]>');
                AddFieldToXML("Name", '<![CDATA[' . $orderitems_result->fields['products_name'] . ']]>');
                AddFieldToXML("ImageUrl", HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . $image_result->fields['products_image']);
                AddFieldToXML("Weight", $image_result->fields['products_weight']);
                AddFieldToXML("UnitPrice", round($orderitems_result->fields['final_price'], 2));
                AddFieldToXML("TaxAmount", round($orderitems_result->fields['products_tax'], 2));
                AddFieldToXML("Quantity", $orderitems_result->fields['products_quantity']);

                $orderitems_attributes_query = "SELECT orders_products_attributes_id, products_options, products_options_values  FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE orders_id = '" . $orders_result->fields['orders_id'] . "' and orders_products_id = '" . $orderitems_result->fields['orders_products_id'] . "'";
                $orderitems_attributes_result = $db->Execute($orderitems_attributes_query);

                if (!$orderitems_attributes_result->EOF) {
                   echo "\t<Options>\n";

                   while (!$orderitems_attributes_result->EOF) {
   
                       echo "\t<Option><Name><![CDATA[" . $orderitems_attributes_result->fields['products_options'] . "]]></Name><Value><![CDATA[" . $orderitems_attributes_result->fields['products_options_values'] . "]]></Value></Option>\n";
   
                       $orderitems_attributes_result->MoveNext();
                   }

                   echo "\t</Options>\n";
                }

                echo "\t</Item>\n";

                $orderitems_result->MoveNext();
            }
            //process Order Items
            echo "\t</Items>\n";
            echo "\t</Order>\n";

            $orders_result->MoveNext();
        }

        //process Orders
        //finish outputing XML
        echo "</Orders>";
    } else {

        echo "<?xml version=\"1.0\" encoding=\"utf-16\"?>\n";
        echo "<Orders />\n";
    }
} elseif ($_GET['action'] == 'verifystatus') {
    echo 'true';
} elseif ($_GET['action'] == 'update') {

    //?action=update&order_number=ABC123&status=4&comment=commment
    userAuthentication($db);
    if ($_GET['order_number']) {

        $status = strtolower($_GET['status']);
        $customer_notified = '0';
        $comments = $_GET['comment'];


        $record_query = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = '" . $_GET['order_number'] . "'";
        $record_result = $db->Execute($record_query);

        if ($record_result->fields['orders_id']) {

            $orders_status = $db->Execute("select orders_status_id, orders_status_name
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
} else {
    echo 'No action parameter. Please contact software provider.';
}
?>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>


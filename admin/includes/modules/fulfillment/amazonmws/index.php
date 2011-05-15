<?php
// +--------------------------------------------------------------------+
// | bitcommerce														|
// +--------------------------------------------------------------------+
// | Copyright (c) 2011 bitcommerce.org									|
// |																	|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+


chdir( '../../../../' );
require_once( 'includes/application_top.php' );
require_once( 'amazonmws_setup_inc.php' );
	

if( !empty( $_REQUEST['mws_process_order'] ) ) {
	if( $ordersId = amazon_process_order( $_REQUEST['amazon_order_id'] ) ) {
		bit_redirect( BITCOMMERCE_PKG_URL.'admin/orders.php?oID='.$ordersId );
	}
} else {
}

/************************************************************************
 * Setup request parameters and uncomment invoke to try out 
 * sample for List Orders Action
 ***********************************************************************/
$request = new MarketplaceWebServiceOrders_Model_ListOrdersRequest();
$request->setSellerId(MERCHANT_ID);

// List all orders udpated after a certain date
$request->setCreatedAfter(new DateTime('2011-01-01 12:00:00', new DateTimeZone('UTC')));

// Set the marketplaces queried in this ListOrdersRequest
$marketplaceIdList = new MarketplaceWebServiceOrders_Model_MarketplaceIdList();
$marketplaceIdList->setId(array(MARKETPLACE_ID));
$request->setMarketplaceId($marketplaceIdList);

// Set the order statuses for this ListOrdersRequest (optional)
// $orderStatuses = new MarketplaceWebServiceOrders_Model_OrderStatusList();
// $orderStatuses->setStatus(array('Shipped'));
// $request->setOrderStatus($orderStatuses);

// Set the Fulfillment Channel for this ListOrdersRequest (optional)
//$fulfillmentChannels = new MarketplaceWebServiceOrders_Model_FulfillmentChannelList();
//$fulfillmentChannels->setChannel(array('MFN'));
//$request->setFulfillmentChannel($fulfillmentChannels);

try {
	$response = $gAmazonMWS->listOrders($request);
	$listOrdersResult = $response->getListOrdersResult();
	if( $listOrdersResult->isSetOrders() ) { 
		$orders = $listOrdersResult->getOrders();
		$memberList = $orders->getOrder();
		$gBitSmarty->assign_by_ref( 'orderList', $memberList );
	}
 } catch (MarketplaceWebServiceOrders_Exception $ex) {
	 echo("Caught Exception: " . $ex->getMessage() . "\n");
	 echo("Response Status Code: " . $ex->getStatusCode() . "\n");
	 echo("Error Code: " . $ex->getErrorCode() . "\n");
	 echo("Error Type: " . $ex->getErrorType() . "\n");
	 echo("Request ID: " . $ex->getRequestId() . "\n");
	 echo("XML: " . $ex->getXML() . "\n");
}


$mid = $awsModulePath.'amazonmws_list_orders.tpl';

$gBitSystem->display( $mid, 'Amazon Marketplace Orders', array( 'display_mode' => 'admin' ));




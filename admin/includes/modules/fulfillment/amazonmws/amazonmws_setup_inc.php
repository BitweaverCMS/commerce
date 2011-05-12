<?php
 
global $gCommerceSystem;
 
/************************************************************************
* REQUIRED
* 
* Access Key ID and Secret Acess Key ID, obtained from:
* http://aws.amazon.com
***********************************************************************/
define('AWS_ACCESS_KEY_ID', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_AWS_ACCESS_KEY_ID') ); // '<Your Access Key ID>');
define('AWS_SECRET_ACCESS_KEY', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_SECRET_KEY' ) );  

/************************************************************************
* REQUIRED
* 
* All MWS requests must contain a User-Agent header. The application
* name and version defined below are used in creating this value.
***********************************************************************/
define('APPLICATION_NAME', 'bitcommerce');
define('APPLICATION_VERSION', '3.0.0');

/************************************************************************
* REQUIRED
* 
* All MWS requests must contain the seller's merchant ID and
* marketplace ID.
***********************************************************************/
define ('MERCHANT_ID', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_MERCHANT_ID') );
define ('MARKETPLACE_ID', $gCommerceSystem->getConfig('MODULE_FULFILLMENT_AMAZONMWS_MARKETPLACE_ID') );

/************************************************************************ 
* OPTIONAL ON SOME INSTALLATIONS
*
* Set include path to root of library, relative to Samples directory.
* Only needed when running library from local directory.
* If library is installed in PHP include path, this is not needed
***********************************************************************/   
$awsModulePath = dirname( __FILE__ ).'/';
set_include_path(get_include_path() . PATH_SEPARATOR . $awsModulePath.'src');	

/************************************************************************ 
* OPTIONAL ON SOME INSTALLATIONS  
* 
* Autoload function is reponsible for loading classes of the library on demand
* 
* NOTE: Only one __autoload function is allowed by PHP per each PHP installation,
* and this function may need to be replaced with individual require_once statements
* in case where other framework that define an __autoload already loaded.
* 
* However, since this library follow common naming convention for PHP classes it
* may be possible to simply re-use an autoload mechanism defined by other frameworks
* (provided library is installed in the PHP include path), and so classes may just 
* be loaded even when this function is removed
***********************************************************************/   
 function __autoload($className){
	$filePath = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	$includePaths = explode(PATH_SEPARATOR, get_include_path());
	foreach($includePaths as $includePath){
		if(file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)){
			require_once $filePath;
			return;
		}
	}
}

function amazon_mws_get_order_items( $pAmazonOrderId ) {
	global $gAmazonMWS;
	$ret = NULL;

	$request = new MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
	$request->setSellerId(MERCHANT_ID);
	$request->setAmazonOrderId( $pAmazonOrderId );

	try {
		$response = $gAmazonMWS->listOrderItems($request);
		if ($response->isSetListOrderItemsResult()) { 
			$listOrderItemsResult = $response->getListOrderItemsResult();
			if ($listOrderItemsResult->isSetOrderItems()) { 
			}
		}

		$ret = $listOrderItemsResult->getOrderItems();
	 } catch (MarketplaceWebServiceOrders_Exception $ex) {
		 echo("Caught Exception: " . $ex->getMessage() . "\n");
		 echo("Response Status Code: " . $ex->getStatusCode() . "\n");
		 echo("Error Code: " . $ex->getErrorCode() . "\n");
		 echo("Error Type: " . $ex->getErrorType() . "\n");
		 echo("Request ID: " . $ex->getRequestId() . "\n");
		 echo("XML: " . $ex->getXML() . "\n");
	}

	return $ret;
}

function amazon_process_order( $pAmazonOrderId ) {
	global $gAmazonMWS, $gBitUser, $gCommerceSystem;
	vd( $pAmazonOrderId );
	$request = new MarketplaceWebServiceOrders_Model_GetOrderRequest();
	$request->setSellerId(MERCHANT_ID);
	// @TODO: set request. Action can be passed as MarketplaceWebServiceOrders_Model_GetOrderRequest
	// object or array of parameters

	// Set the list of AmazonOrderIds
	$orderIds = new MarketplaceWebServiceOrders_Model_OrderIdList();
	$orderIds->setId( array( $pAmazonOrderId ) );
	$request->setAmazonOrderId($orderIds);

	$holdUser = $gBitUser;
	$gBitUser = new BitPermUser( $holdUser->lookupHomepage( $gCommerceSystem->getConfig( 'MODULE_FULFILLMENT_AMAZONMWS_LOCAL_USERNAME', 'amazonmws' ) ) );
	$gBitUser->load();

	try {
		$response = $gAmazonMWS->getOrder($request);
	  
		if ($response->isSetGetOrderResult()) { 
			$getOrderResult = $response->getGetOrderResult();
			if ($getOrderResult->isSetOrders()) { 
				$azOrderList = $getOrderResult->getOrders();

				require_once(DIR_FS_CLASSES . 'order.php');
				$newOrder = new order;
				$newOrder->contents = array();
				$newOrder->customer = array();
				$newOrder->delivery = array();

				$newOrder->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
									'currency' => !empty( $_SESSION['currency'] ) ? $_SESSION['currency'] : NULL,
									'currency_value' => !empty( $_SESSION['currency'] ) ? $currencies->currencies[$_SESSION['currency']]['currency_value'] : NULL,
									'payment_method' => !empty( $GLOBALS[$class] ) ? $GLOBALS[$class]->title : '',
									'payment_module_code' => !empty( $GLOBALS[$class] ) ? $GLOBALS[$class]->code : '',
									'coupon_code' => $coupon_code,
									'subtotal' => 0,
									'tax' => 0,
									'total' => 0,
									'tax_groups' => array(),
									'comments' => (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''),
									'ip_address' => $_SERVER['REMOTE_ADDR']
									);
				if( $azOrders = $azOrderList->getOrder() ) {
					$azOrder = current( $azOrders );

					// Setup delivery address
					if( $shippingAddress = $azOrder->getShippingAddress() ) {
						$newOrder->delivery = array('firstname' => substr( $shippingAddress->getName(), 0, strpos( $shippingAddress->getName(), ' ' ) ),
												'lastname' => substr( $shippingAddress->getName(), strpos( $shippingAddress->getName(), ' ' ) + 1 ),
											//	'company' => $defaultAddress['entry_company'],
												'street_address' => $shippingAddress->getAddressLine1(),
												'suburb' => trim( $shippingAddress->getAddressLine2().' '.$shippingAddress->getAddressLine3() ),
												'city' => $shippingAddress->getCity(),
												'postcode' => $shippingAddress->getPostalCode(),
												'state' => $shippingAddress->getStateOrRegion(),
											//	'zone_id' => $defaultAddress['entry_zone_id'],
												'country' => array(
											//		'countries_name' => $defaultAddress['countries_name'], 
											//		'countries_id' => $defaultAddress['countries_id'], 
													'countries_iso_code_2' => $shippingAddress->getCountryCode(), 
											//		'countries_iso_code_3' => $defaultAddress['countries_iso_code_3'],
												),
											//	'format_id' => $defaultAddress['address_format_id'],
												'telephone' => $shippingAddress->getPhone(),
											//	'email_address' => $defaultAddress['customers_email_address']
											);
						$newOrder->customer = $newOrder->delivery;
					}

					// Setup shipping
					$shipping = array( 'cost' => 0 );
					switch( $azOrder->getShipServiceLevel() ) {
						case 'Std US Dom':
							$shipping['id'] = 'usps_MEDIA';
							$shipping['title'] = 'United States Postal Service (USPS Media Mail (1 - 2 Weeks))';
							$shipping['code'] = 'USPSREG';
							break;
					}

					$azOrderItems = amazon_mws_get_order_items( $azOrder->getAmazonOrderId() );
					$azOrderItem = $azOrderItems->getOrderItem();
					foreach( $azOrderItem as $azi ) {
/*
						{if $azi->getQuantityOrdered()}{$azi->getQuantityOrdered()} x {/if}
						<a href="{$gBitProduct->getDisplayUrl($azi->getSellerSKU())}">{$azi->getSellerSKU()} {$azi->getTitle()|escape}</a>
						{assign var=lineTotal value=0}
						<div class="floatright">
							{if $azi->getItemPrice()}{assign var=itemPrice value=$azi->getItemPrice()}{$itemPrice->getAmount()}{assign var=lineTotal value=$lineTotal+$itemPrice->getAmount()}{/if} 
							{if $lineTotal} = {$lineTotal} {$itemPrice->getCurrencyCode()}{/if}
						</div>
*/
						if( $azi->isSetShippingPrice() ) {
							$shippingPrice = $azi->getShippingPrice();
							$shipping['cost'] += $shippingPrice->getAmount();
						}
					}
				
					$newOrder->info['shipping_method'] = $shipping['title'];
					$newOrder->info['shipping_method_code'] = $shipping['code'];
					$newOrder->info['shipping_module_code'] = $shipping['id'];
					$newOrder->info['shipping_cost'] = $shipping['cost'];
			vd( $newOrder );	
					require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceShipping.php');
					$shipping_modules = new CommerceShipping($_SESSION['shipping']);
/*

	require(DIR_FS_CLASSES . 'order_total.php');
	$order_total_modules = new order_total;
	$order_totals = $order_total_modules->pre_confirmation_check();
	$order_totals = $order_total_modules->process();

$gBitDb->mDb->StartTrans();
// load the before_process function from the payment modules
	$payment_modules->before_process();

	$insert_id = $order->create($order_totals, 2);
	$order->create_add_products($insert_id);
*/

				}
			}
		}
	} catch (MarketplaceWebServiceOrders_Exception $ex) {
		echo("Caught Exception: " . $ex->getMessage() . "\n");
		echo("Response Status Code: " . $ex->getStatusCode() . "\n");
		echo("Error Code: " . $ex->getErrorCode() . "\n");
		echo("Error Type: " . $ex->getErrorType() . "\n");
		echo("Request ID: " . $ex->getRequestId() . "\n");
		echo("XML: " . $ex->getXML() . "\n");
	}

	$gBitUser = $holdUser;
}


// United States:
$serviceUrl = "https://mws.amazonservices.com/Orders/2011-01-01";
// United Kingdom
//$serviceUrl = "https://mws.amazonservices.co.uk/Orders/2011-01-01";
// Germany
//$serviceUrl = "https://mws.amazonservices.de/Orders/2011-01-01";
// France
//$serviceUrl = "https://mws.amazonservices.fr/Orders/2011-01-01";
// Japan
//$serviceUrl = "https://mws.amazonservices.jp/Orders/2011-01-01";
// China
//$serviceUrl = "https://mws.amazonservices.com.cn/Orders/2011-01-01";
// Canada
//$serviceUrl = "https://mws.amazonservices.ca/Orders/2011-01-01";


// Instantiate Implementation of MarketplaceWebServiceOrders
$config = array (
	'ServiceURL' => $serviceUrl,
	'ProxyHost' => null,
	'ProxyPort' => -1,
	'MaxErrorRetry' => 3,
);

$gAmazonMWS = new MarketplaceWebServiceOrders_Client( AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, APPLICATION_NAME, APPLICATION_VERSION, $config);

 
/************************************************************************
 * Uncomment to try out Mock Service that simulates MarketplaceWebServiceOrders
 * responses without calling MarketplaceWebServiceOrders service.
 *
 * Responses are loaded from local XML files. You can tweak XML files to
 * experiment with various outputs during development
 *
 * XML files available under MarketplaceWebServiceOrders/Mock tree
 *
 ***********************************************************************/
 // $gAmazonMWS = new MarketplaceWebServiceOrders_Mock();




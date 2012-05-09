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

 
global $gCommerceSystem, $gBitSystem;
 
if( !defined( 'MODULE_PAYMENT_AMAZONMWS_STATUS' ) || MODULE_PAYMENT_AMAZONMWS_STATUS != 'True' ) {
	$gBitSystem->fatalError( 'AmazonMWS module is not active' );
}

/************************************************************************
* REQUIRED
* 
* Access Key ID and Secret Acess Key ID, obtained from:
* http://aws.amazon.com
***********************************************************************/
define('AWS_ACCESS_KEY_ID', $gCommerceSystem->getConfig('MODULE_PAYMENT_AMAZONMWS_AWS_ACCESS_KEY_ID') ); // '<Your Access Key ID>');
define('AWS_SECRET_ACCESS_KEY', $gCommerceSystem->getConfig('MODULE_PAYMENT_AMAZONMWS_SECRET_KEY' ) );  

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
define ('MERCHANT_ID', $gCommerceSystem->getConfig('MODULE_PAYMENT_AMAZONMWS_MERCHANT_ID') );
define ('MARKETPLACE_ID', $gCommerceSystem->getConfig('MODULE_PAYMENT_AMAZONMWS_MARKETPLACE_ID') );

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

function amazon_order_is_processed( $pAmazonOrderId ) {
	global $gBitDb;
	return $gBitDb->getOne( "SELECT `orders_id` FROM " . TABLE_ORDERS_TOTAL . " WHERE class='pay_amazonmws' AND `title` LIKE ?", array( "%$pAmazonOrderId" ) );
}

// returns new orders_id
function amazon_process_order( $pAmazonOrderId ) {
	global $gAmazonMWS, $gBitUser, $gCommerceSystem, $gBitCustomer, $currencies, $order;

	$ret = NULL;	
	$request = new MarketplaceWebServiceOrders_Model_GetOrderRequest();
	$request->setSellerId(MERCHANT_ID);
	// @TODO: set request. Action can be passed as MarketplaceWebServiceOrders_Model_GetOrderRequest
	// object or array of parameters

	// Set the list of AmazonOrderIds
	$orderIds = new MarketplaceWebServiceOrders_Model_OrderIdList();
	$orderIds->setId( array( $pAmazonOrderId ) );
	$request->setAmazonOrderId($orderIds);
	$holdUser = $gBitUser;
	$azUser = new BitPermUser( $holdUser->lookupHomepage( $gCommerceSystem->getConfig( 'MODULE_PAYMENT_AMAZONMWS_LOCAL_USERNAME', 'amazonmws' ) ) );
	$azUser->load();
	$gBitUser = $azUser;
	$gBitCustomer = new CommerceCustomer( $gBitUser->mUserId );
	$gBitCustomer->syncBitUser( $gBitUser->mInfo );
	$_SESSION['customer_id'] = $gBitUser->mUserId;

	try {
		$response = $gAmazonMWS->getOrder($request);
	  
		if ($response->isSetGetOrderResult()) { 
			$getOrderResult = $response->getGetOrderResult();
			if ($getOrderResult->isSetOrders()) { 
				$oldCwd = getcwd();
				chdir( BITCOMMERCE_PKG_PATH );

				$azOrderList = $getOrderResult->getOrders();

				if( $azOrders = $azOrderList->getOrder() ) {
					require_once(BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php');
					$order = new order;
					$order->initOrder();

					$order->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
										'subtotal' => 0,
										'tax' => 0,
										'total' => 0,
										'tax_groups' => array(),
										'comments' => (isset($_SESSION['comments']) ? $_SESSION['comments'] : ''),
										'ip_address' => $_SERVER['REMOTE_ADDR']
										);
					$azOrder = current( $azOrders );

					// Setup delivery address
					if( $orderTotal = $azOrder->getOrderTotal() ) {
						$order->info['total'] = $orderTotal->getAmount();
						$order->info['currency'] = $orderTotal->getCurrencyCode();
						$order->info['currency_value'] = $currencies->currencies[$order->info['currency']]['currency_value'];
					}

					if( $shippingAddress = $azOrder->getShippingAddress() ) {
						$country = zen_get_countries( zen_get_country_id( $shippingAddress->getCountryCode() ), TRUE );
						$zoneName = zen_get_zone_name_by_code( $country['countries_id'], $shippingAddress->getStateOrRegion() );
						$order->delivery = array('firstname' => substr( $shippingAddress->getName(), 0, strpos( $shippingAddress->getName(), ' ' ) ),
												'lastname' => substr( $shippingAddress->getName(), strpos( $shippingAddress->getName(), ' ' ) + 1 ),
												'company' => NULL,
												'street_address' => $shippingAddress->getAddressLine1(),
												'suburb' => trim( $shippingAddress->getAddressLine2().' '.$shippingAddress->getAddressLine3() ),
												'city' => $shippingAddress->getCity(),
												'postcode' => $shippingAddress->getPostalCode(),
												'state' => $zoneName,
												'country' => $country,
												'format_id' => $country['address_format_id'],
												'telephone' => $shippingAddress->getPhone(),
												'email_address' => NULL
											);
						$order->customer = $order->delivery;
						$order->billing = $order->delivery;
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
						$testSku = $azi->getSellerSKU();
						list( $productsId, $attrString ) = explode( ':', $testSku, 2 );
						$productsKey = $productsId.':ASIN-'.$azi->getASIN();
						$order->contents[$productsKey] = $gBitCustomer->mCart->getProductHash( $productsKey );
						$order->contents[$productsKey]['products_quantity'] = $azi->getQuantityOrdered();
						$order->contents[$productsKey]['products_name'] = $azi->getTitle();
						if( $itemPrice = $azi->getItemPrice() ) {
//							{$itemTax->getCurrencyCode()}
							$order->contents[$productsKey]['price'] = $itemPrice->getAmount();
							$order->contents[$productsKey]['final_price'] = $itemPrice->getAmount();
						}
						if( $itemTax = $azi->getItemTax() ) {
//							{$itemTax->getCurrencyCode()}
							$order->contents[$productsKey]['tax'] = $itemTax->getAmount();
						}
						if( $shippingPrice = $azi->getShippingPrice() ) {
//							{$itemTax->getCurrencyCode()}
							$order->info['shipping_cost'] = $shippingPrice->getAmount();
						}

						if( empty( $attrString ) ) {
							$attrString = $gCommerceSystem->getConfig( 'MODULE_PAYMENT_AMAZONMWS_DEFAULT_ATTRIBUTES' ); 
						}
						// stock up the attributes	
						if( $attrString && $attrs = explode( ',', $attrString ) ) {
							foreach( $attrs as $optionValueId ) {
								$optionId = $order->mDb->getOne( "SELECT cpa.`products_options_id` FROM " . TABLE_PRODUCTS_ATTRIBUTES . " cpa WHERE cpa.`products_options_values_id`=?", array( $optionValueId ) );
								$order->contents[$productsKey]['attributes'][$optionId.'_'.$optionValueId] = $optionValueId;
							}
						}

						if ( !empty( $order->contents[$productsKey]['attributes'] ) ) {
							$attributes  = $order->contents[$productsKey]['attributes'];
							$order->contents[$productsKey]['attributes'] = array();
							$subindex = 0;
							foreach( $attributes as $option=>$value ) {
								$optionValues = zen_get_option_value( zen_get_options_id( $option ), (int)$value );
								// Determine if attribute is a text attribute and change products array if it is.
								if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID){
									$attr_value = $order->contents[$productsKey]['attributes_values'][$option];
								} else {
									$attr_value = $optionValues['products_options_values_name'];
								}

								$order->contents[$productsKey]['attributes'][$subindex] = array('option' => $optionValues['products_options_name'],
																						 'value' => $attr_value,
																						 'option_id' => $option,
																						 'value_id' => $value,
																						 'prefix' => $optionValues['price_prefix'],
																						 'price' => $optionValues['options_values_price']);

								$subindex++;
							}
						}

						$shown_price = (zen_add_tax($order->contents[$productsKey]['final_price'], $order->contents[$productsKey]['tax']) * $order->contents[$productsKey]['products_quantity'])
										+ zen_add_tax($order->contents[$productsKey]['onetime_charges'], $order->contents[$productsKey]['tax']);
						$order->subtotal += $shown_price;

						$products_tax = $order->contents[$productsKey]['tax'];
						$products_tax_description = $order->contents[$productsKey]['tax_description'];
						if (DISPLAY_PRICE_WITH_TAX == 'true') {
							$order->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
							if (isset($order->info['tax_groups']["$products_tax_description"])) {
								$order->info['tax_groups']["$products_tax_description"] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
							} else {
								$order->info['tax_groups']["$products_tax_description"] = $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
							}
						} else {
							$order->info['tax'] += ($products_tax / 100) * $shown_price;
							if (isset($order->info['tax_groups']["$products_tax_description"])) {
								$order->info['tax_groups']["$products_tax_description"] += ($products_tax / 100) * $shown_price;
							} else {
								$order->info['tax_groups']["$products_tax_description"] = ($products_tax / 100) * $shown_price;
							}
						}
						$order->info['tax'] = zen_round($order->info['tax'],2);

						if( $azi->isSetShippingPrice() ) {
							$shippingPrice = $azi->getShippingPrice();
							$shipping['cost'] += $shippingPrice->getAmount();
						}
					}
			
					foreach ( array( 'cc_type', 'cc_owner', 'cc_number', 'cc_expires', 'coupon_code' ) as $key ) {
						$order->info[$key] = NULL;
					}
					$order->info['shipping_method'] = $shipping['title'];
					$order->info['shipping_method_code'] = $shipping['code'];
					$order->info['shipping_module_code'] = $shipping['id'];
					$order->info['payment_module_code'] = 'amazonmws';
					$order->info['payment_method'] = 'Amazon Order';

					$_SESSION['sendto'] = NULL;
					$_SESSION['shipping'] = $shipping;
					unset( $_SESSION['cot_gv'] );
					require_once( DIR_FS_CLASSES . 'order_total.php' );
					global $order_total_modules;
					$order_total_modules = new order_total;
					$order_totals = $order_total_modules->pre_confirmation_check();
					require_once( DIR_WS_MODULES.'payment/amazonmws.php' );
					$amazon = new amazonmws( $azOrder->getAmazonOrderId() );
					$amazonOutput = $amazon->process();
					$order_totals = $order_total_modules->process();
					array_splice( $order_totals, count( $order_totals ) - 1, 0, array( $amazonOutput ) );
					if( $ordersId = $order->create( $order_totals, 2 ) ) {
						$order->create_add_products( $ordersId );
						$ret = $ordersId;
						$order->updateStatus( array( 'status' => MODULE_PAYMENT_AMAZONMWS_INITIAL_ORDER_STATUS_ID ) );
					}
				}
				chdir( $oldCwd );
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
	$gBitCustomer = new CommerceCustomer( $gBitUser->mUserId );
	$_SESSION['customer_id'] = $gBitUser->mUserId;

	return $ret;
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




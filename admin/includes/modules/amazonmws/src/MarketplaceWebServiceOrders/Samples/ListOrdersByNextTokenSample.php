<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     MarketplaceWebServiceOrders
 *  @copyright   Copyright 2008-2009 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2011-01-01
 */
/******************************************************************************* 
 *  Marketplace Web Service Orders PHP5 Library
 *  Generated: Fri Jan 21 18:53:17 UTC 2011
 * 
 */

/**
 * List Orders By Next Token  Sample
 */

include_once ('.config.inc.php'); 

/************************************************************************
 * Instantiate Implementation of MarketplaceWebServiceOrders
 * 
 * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
 * are defined in the .config.inc.php located in the same 
 * directory as this sample
 ***********************************************************************/
// United States:
//$serviceUrl = "https://mws.amazonservices.com/Orders/2011-01-01";
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

 $config = array (
   'ServiceURL' => $serviceUrl,
   'ProxyHost' => null,
   'ProxyPort' => -1,
   'MaxErrorRetry' => 3,
 );

 $service = new MarketplaceWebServiceOrders_Client(
        AWS_ACCESS_KEY_ID,
        AWS_SECRET_ACCESS_KEY,
        APPLICATION_NAME,
        APPLICATION_VERSION,
        $config);

 
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
 // $service = new MarketplaceWebServiceOrders_Mock();

/************************************************************************
 * Setup request parameters and uncomment invoke to try out 
 * sample for List Orders By Next Token Action
 ***********************************************************************/
 $request = new MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest();
 $request->setSellerId(MERCHANT_ID);
 $request->setNextToken('<NEXT TOKEN>');
 // @TODO: set request. Action can be passed as MarketplaceWebServiceOrders_Model_ListOrdersByNextTokenRequest
 // object or array of parameters
 invokeListOrdersByNextToken($service, $request);

                        
/**
  * List Orders By Next Token Action Sample
  * If ListOrders returns a nextToken, thus indicating that there are more orders
  * than returned that matched the given filter criteria, ListOrdersByNextToken
  * can be used to retrieve those other orders using that nextToken.
  *   
  * @param MarketplaceWebServiceOrders_Interface $service instance of MarketplaceWebServiceOrders_Interface
  * @param mixed $request MarketplaceWebServiceOrders_Model_ListOrdersByNextToken or array of parameters
  */
  function invokeListOrdersByNextToken(MarketplaceWebServiceOrders_Interface $service, $request) 
  {
      try {
              $response = $service->listOrdersByNextToken($request);
              
                echo ("<pre>Service Response\n");
                echo ("=============================================================================\n");

                echo("        ListOrdersByNextTokenResponse\n");
                if ($response->isSetListOrdersByNextTokenResult()) { 
                    echo("            ListOrdersByNextTokenResult\n");
                    $listOrdersByNextTokenResult = $response->getListOrdersByNextTokenResult();
                    if ($listOrdersByNextTokenResult->isSetNextToken()) 
                    {
                        echo("                NextToken\n");
                        echo("                    " . $listOrdersByNextTokenResult->getNextToken() . "\n");
                    }
                    if ($listOrdersByNextTokenResult->isSetCreatedBefore()) 
                    {
                        echo("                CreatedBefore\n");
                        echo("                    " . $listOrdersByNextTokenResult->getCreatedBefore() . "\n");
                    }
                    if ($listOrdersByNextTokenResult->isSetLastUpdatedBefore()) 
                    {
                        echo("                LastUpdatedBefore\n");
                        echo("                    " . $listOrdersByNextTokenResult->getLastUpdatedBefore() . "\n");
                    }
                    if ($listOrdersByNextTokenResult->isSetOrders()) { 
                        echo("                Orders\n");
                        $orders = $listOrdersByNextTokenResult->getOrders();
                        $memberList = $orders->getOrder();
                        foreach ($memberList as $member) {
                            echo("                    member\n");
                            if ($member->isSetAmazonOrderId()) 
                            {
                                echo("                        AmazonOrderId\n");
                                echo("                            " . $member->getAmazonOrderId() . "\n");
                            }
                            if ($member->isSetSellerOrderId()) 
                            {
                                echo("                        SellerOrderId\n");
                                echo("                            " . $member->getSellerOrderId() . "\n");
                            }
                            if ($member->isSetPurchaseDate()) 
                            {
                                echo("                        PurchaseDate\n");
                                echo("                            " . $member->getPurchaseDate() . "\n");
                            }
                            if ($member->isSetLastUpdateDate()) 
                            {
                                echo("                        LastUpdateDate\n");
                                echo("                            " . $member->getLastUpdateDate() . "\n");
                            }
                            if ($member->isSetOrderStatus()) 
                            {
                                echo("                        OrderStatus\n");
                                echo("                            " . $member->getOrderStatus() . "\n");
                            }
                            if ($member->isSetFulfillmentChannel()) 
                            {
                                echo("                        FulfillmentChannel\n");
                                echo("                            " . $member->getFulfillmentChannel() . "\n");
                            }
                            if ($member->isSetSalesChannel()) 
                            {
                                echo("                        SalesChannel\n");
                                echo("                            " . $member->getSalesChannel() . "\n");
                            }
                            if ($member->isSetOrderChannel()) 
                            {
                                echo("                        OrderChannel\n");
                                echo("                            " . $member->getOrderChannel() . "\n");
                            }
                            if ($member->isSetShipServiceLevel()) 
                            {
                                echo("                        ShipServiceLevel\n");
                                echo("                            " . $member->getShipServiceLevel() . "\n");
                            }
                            if ($member->isSetShippingAddress()) { 
                                echo("                        ShippingAddress\n");
                                $shippingAddress = $member->getShippingAddress();
                                if ($shippingAddress->isSetName()) 
                                {
                                    echo("                            Name\n");
                                    echo("                                " . $shippingAddress->getName() . "\n");
                                }
                                if ($shippingAddress->isSetAddressLine1()) 
                                {
                                    echo("                            AddressLine1\n");
                                    echo("                                " . $shippingAddress->getAddressLine1() . "\n");
                                }
                                if ($shippingAddress->isSetAddressLine2()) 
                                {
                                    echo("                            AddressLine2\n");
                                    echo("                                " . $shippingAddress->getAddressLine2() . "\n");
                                }
                                if ($shippingAddress->isSetAddressLine3()) 
                                {
                                    echo("                            AddressLine3\n");
                                    echo("                                " . $shippingAddress->getAddressLine3() . "\n");
                                }
                                if ($shippingAddress->isSetCity()) 
                                {
                                    echo("                            City\n");
                                    echo("                                " . $shippingAddress->getCity() . "\n");
                                }
                                if ($shippingAddress->isSetCounty()) 
                                {
                                    echo("                            County\n");
                                    echo("                                " . $shippingAddress->getCounty() . "\n");
                                }
                                if ($shippingAddress->isSetDistrict()) 
                                {
                                    echo("                            District\n");
                                    echo("                                " . $shippingAddress->getDistrict() . "\n");
                                }
                                if ($shippingAddress->isSetStateOrRegion()) 
                                {
                                    echo("                            StateOrRegion\n");
                                    echo("                                " . $shippingAddress->getStateOrRegion() . "\n");
                                }
                                if ($shippingAddress->isSetPostalCode()) 
                                {
                                    echo("                            PostalCode\n");
                                    echo("                                " . $shippingAddress->getPostalCode() . "\n");
                                }
                                if ($shippingAddress->isSetCountryCode()) 
                                {
                                    echo("                            CountryCode\n");
                                    echo("                                " . $shippingAddress->getCountryCode() . "\n");
                                }
                                if ($shippingAddress->isSetPhone()) 
                                {
                                    echo("                            Phone\n");
                                    echo("                                " . $shippingAddress->getPhone() . "\n");
                                }
                            } 
                            if ($member->isSetOrderTotal()) { 
                                echo("                        OrderTotal\n");
                                $orderTotal = $member->getOrderTotal();
                                if ($orderTotal->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $orderTotal->getCurrencyCode() . "\n");
                                }
                                if ($orderTotal->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $orderTotal->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetNumberOfItemsShipped()) 
                            {
                                echo("                        NumberOfItemsShipped\n");
                                echo("                            " . $member->getNumberOfItemsShipped() . "\n");
                            }
                            if ($member->isSetNumberOfItemsUnshipped()) 
                            {
                                echo("                        NumberOfItemsUnshipped\n");
                                echo("                            " . $member->getNumberOfItemsUnshipped() . "\n");
                            }
                        }
                    } 
                } 
                if ($response->isSetResponseMetadata()) { 
                    echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        echo("                RequestId\n");
                        echo("                    " . $responseMetadata->getRequestId() . "\n");
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
 }
                        

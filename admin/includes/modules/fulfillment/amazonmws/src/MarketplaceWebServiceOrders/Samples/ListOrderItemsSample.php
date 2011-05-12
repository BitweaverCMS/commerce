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
 * List Order Items  Sample
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
 * sample for List Order Items Action
 ***********************************************************************/
 $request = new MarketplaceWebServiceOrders_Model_ListOrderItemsRequest();
 $request->setSellerId(MERCHANT_ID);
 $request->setAmazonOrderId("<AMAZON ORDER ID>");
 // @TODO: set request. Action can be passed as MarketplaceWebServiceOrders_Model_ListOrderItemsRequest
 // object or array of parameters
 invokeListOrderItems($service, $request);

                                    
/**
  * List Order Items Action Sample
  * This operation can be used to list the items of the order indicated by the
  * given order id (only a single Amazon order id is allowed).
  *   
  * @param MarketplaceWebServiceOrders_Interface $service instance of MarketplaceWebServiceOrders_Interface
  * @param mixed $request MarketplaceWebServiceOrders_Model_ListOrderItems or array of parameters
  */
  function invokeListOrderItems(MarketplaceWebServiceOrders_Interface $service, $request) 
  {
      try {
              $response = $service->listOrderItems($request);
              
                echo ("<pre>Service Response\n");
                echo ("=============================================================================\n");

                echo("        ListOrderItemsResponse\n");
                if ($response->isSetListOrderItemsResult()) { 
                    echo("            ListOrderItemsResult\n");
                    $listOrderItemsResult = $response->getListOrderItemsResult();
                    if ($listOrderItemsResult->isSetNextToken()) 
                    {
                        echo("                NextToken\n");
                        echo("                    " . $listOrderItemsResult->getNextToken() . "\n");
                    }
                    if ($listOrderItemsResult->isSetAmazonOrderId()) 
                    {
                        echo("                AmazonOrderId\n");
                        echo("                    " . $listOrderItemsResult->getAmazonOrderId() . "\n");
                    }
                    if ($listOrderItemsResult->isSetOrderItems()) { 
                        echo("                OrderItems\n");
                        $orderItems = $listOrderItemsResult->getOrderItems();
                        $memberList = $orderItems->getOrderItem();
                        foreach ($memberList as $member) {
                            echo("                    member\n");
                            if ($member->isSetASIN()) 
                            {
                                echo("                        ASIN\n");
                                echo("                            " . $member->getASIN() . "\n");
                            }
                            if ($member->isSetSellerSKU()) 
                            {
                                echo("                        SellerSKU\n");
                                echo("                            " . $member->getSellerSKU() . "\n");
                            }
                            if ($member->isSetTitle()) 
                            {
                                echo("                        Title\n");
                                echo("                            " . $member->getTitle() . "\n");
                            }
                            if ($member->isSetQuantityOrdered()) 
                            {
                                echo("                        QuantityOrdered\n");
                                echo("                            " . $member->getQuantityOrdered() . "\n");
                            }
                            if ($member->isSetQuantityShipped()) 
                            {
                                echo("                        QuantityShipped\n");
                                echo("                            " . $member->getQuantityShipped() . "\n");
                            }
                            if ($member->isSetGiftMessageText()) 
                            {
                                echo("                        GiftMessageText\n");
                                echo("                            " . $member->getGiftMessageText() . "\n");
                            }
                            if ($member->isSetItemPrice()) { 
                                echo("                        ItemPrice\n");
                                $itemPrice = $member->getItemPrice();
                                if ($itemPrice->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $itemPrice->getCurrencyCode() . "\n");
                                }
                                if ($itemPrice->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $itemPrice->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetShippingPrice()) { 
                                echo("                        ShippingPrice\n");
                                $shippingPrice = $member->getShippingPrice();
                                if ($shippingPrice->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $shippingPrice->getCurrencyCode() . "\n");
                                }
                                if ($shippingPrice->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $shippingPrice->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetGiftWrapPrice()) { 
                                echo("                        GiftWrapPrice\n");
                                $giftWrapPrice = $member->getGiftWrapPrice();
                                if ($giftWrapPrice->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $giftWrapPrice->getCurrencyCode() . "\n");
                                }
                                if ($giftWrapPrice->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $giftWrapPrice->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetItemTax()) { 
                                echo("                        ItemTax\n");
                                $itemTax = $member->getItemTax();
                                if ($itemTax->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $itemTax->getCurrencyCode() . "\n");
                                }
                                if ($itemTax->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $itemTax->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetShippingTax()) { 
                                echo("                        ShippingTax\n");
                                $shippingTax = $member->getShippingTax();
                                if ($shippingTax->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $shippingTax->getCurrencyCode() . "\n");
                                }
                                if ($shippingTax->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $shippingTax->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetGiftWrapTax()) { 
                                echo("                        GiftWrapTax\n");
                                $giftWrapTax = $member->getGiftWrapTax();
                                if ($giftWrapTax->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $giftWrapTax->getCurrencyCode() . "\n");
                                }
                                if ($giftWrapTax->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $giftWrapTax->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetShippingDiscount()) { 
                                echo("                        ShippingDiscount\n");
                                $shippingDiscount = $member->getShippingDiscount();
                                if ($shippingDiscount->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $shippingDiscount->getCurrencyCode() . "\n");
                                }
                                if ($shippingDiscount->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $shippingDiscount->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetPromotionDiscount()) { 
                                echo("                        PromotionDiscount\n");
                                $promotionDiscount = $member->getPromotionDiscount();
                                if ($promotionDiscount->isSetCurrencyCode()) 
                                {
                                    echo("                            CurrencyCode\n");
                                    echo("                                " . $promotionDiscount->getCurrencyCode() . "\n");
                                }
                                if ($promotionDiscount->isSetAmount()) 
                                {
                                    echo("                            Amount\n");
                                    echo("                                " . $promotionDiscount->getAmount() . "\n");
                                }
                            } 
                            if ($member->isSetPromotionIds()) { 
                                echo("                        PromotionIds\n");
                                $promotionIds = $member->getPromotionIds();
                                $member1List  =  $promotionIds->getPromotionId();
                                foreach ($member1List as $member1) { 
                                    echo("                            member\n");
                                    echo("                                " . $member1);
                                }	
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
            

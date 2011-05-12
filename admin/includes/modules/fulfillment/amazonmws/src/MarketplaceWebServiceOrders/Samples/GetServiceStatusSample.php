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
 * Get Service Status  Sample
 */

include_once ('.config.inc.php'); 

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

/************************************************************************
 * Instantiate Implementation of MarketplaceWebServiceOrders
 * 
 * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants 
 * are defined in the .config.inc.php located in the same 
 * directory as this sample
 ***********************************************************************/
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
 * sample for Get Service Status Action
 ***********************************************************************/
 $request = new MarketplaceWebServiceOrders_Model_GetServiceStatusRequest();
 $request->setSellerId(MERCHANT_ID);

 // @TODO: set request. Action can be passed as MarketplaceWebServiceOrders_Model_GetServiceStatusRequest
 // object or array of parameters
 invokeGetServiceStatus($service, $request);

                                            
/**
  * Get Service Status Action Sample
  * Returns the service status of a particular MWS API section. The operation
  * takes no input.
  * All API sections within the API are required to implement this operation.
  *   
  * @param MarketplaceWebServiceOrders_Interface $service instance of MarketplaceWebServiceOrders_Interface
  * @param mixed $request MarketplaceWebServiceOrders_Model_GetServiceStatus or array of parameters
  */
  function invokeGetServiceStatus(MarketplaceWebServiceOrders_Interface $service, $request) 
  {
      try {
              $response = $service->getServiceStatus($request);
              
                echo ("<pre>Service Response\n");
                echo ("=============================================================================\n");

                echo("        GetServiceStatusResponse\n");
                if ($response->isSetGetServiceStatusResult()) { 
                    echo("            GetServiceStatusResult\n");
                    $getServiceStatusResult = $response->getGetServiceStatusResult();
                    if ($getServiceStatusResult->isSetStatus()) 
                    {
                        echo("                Status\n");
                        echo("                    " . $getServiceStatusResult->getStatus() . "\n");
                    }
                    if ($getServiceStatusResult->isSetTimestamp()) 
                    {
                        echo("                Timestamp\n");
                        echo("                    " . $getServiceStatusResult->getTimestamp() . "\n");
                    }
                    if ($getServiceStatusResult->isSetMessageId()) 
                    {
                        echo("                MessageId\n");
                        echo("                    " . $getServiceStatusResult->getMessageId() . "\n");
                    }
                    if ($getServiceStatusResult->isSetMessages()) { 
                        echo("                Messages\n");
                        $messages = $getServiceStatusResult->getMessages();
                        $memberList = $messages->getMessages();
                        foreach ($memberList as $member) {
                            echo("                    member\n");
                            if ($member->isSetLocale()) 
                            {
                                echo("                        Locale\n");
                                echo("                            " . $member->getLocale() . "\n");
                            }
                            if ($member->isSetText()) 
                            {
                                echo("                        Text\n");
                                echo("                            " . $member->getText() . "\n");
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
    

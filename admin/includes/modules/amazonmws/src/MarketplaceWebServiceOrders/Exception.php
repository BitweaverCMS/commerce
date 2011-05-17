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
 *
 *  Marketplace Web Service Orders PHP5 Library
 *  Generated: Fri Jan 21 18:53:17 UTC 2011
 * 
 */


/**
 * Marketplace Web Service Orders  Exception provides details of errors 
 * returned by Marketplace Web Service Orders  service
 *
 */
class MarketplaceWebServiceOrders_Exception extends Exception

{
    /** @var string */
    private $_message = null;
    /** @var int */
    private $_statusCode = -1;
    /** @var string */
    private $_errorCode = null;
    /** @var string */
    private $_errorType = null;
    /** @var string */
    private $_requestId = null;
    /** @var string */
    private $_xml = null;
   

    /**
     * Constructs MarketplaceWebServiceOrders_Exception
     * @param array $errorInfo details of exception.
     * Keys are:
     * <ul>
     * <li>Message - (string) text message for an exception</li>
     * <li>StatusCode - (int) HTTP status code at the time of exception</li>
     * <li>ErrorCode - (string) specific error code returned by the service</li>
     * <li>ErrorType - (string) Possible types:  Sender, Receiver or Unknown</li>
     * <li>RequestId - (string) request id returned by the service</li>
     * <li>XML - (string) compete xml response at the time of exception</li>
     * <li>Exception - (Exception) inner exception if any</li>
     * </ul>
     *         
     */
    public function __construct(array $errorInfo = array())
    {
        $this->_message = $errorInfo["Message"];
        parent::__construct($this->_message);
        if (array_key_exists("Exception", $errorInfo)) {
            $exception = $errorInfo["Exception"];
            if ($exception instanceof MarketplaceWebServiceOrders_Exception) {
                $this->_statusCode = $exception->getStatusCode();
                $this->_errorCode = $exception->getErrorCode();
                $this->_errorType = $exception->getErrorType();
                $this->_requestId = $exception->getRequestId();
                $this->_xml= $exception->getXML();
            } 
        } else {
            $this->_statusCode = $errorInfo["StatusCode"];
            $this->_errorCode = $errorInfo["ErrorCode"];
            $this->_errorType = $errorInfo["ErrorType"];
            $this->_requestId = $errorInfo["RequestId"];
            $this->_xml= $errorInfo["XML"];
        }
    }

    /**
     * Gets error type returned by the service if available.
     *
     * @return string Error Code returned by the service
     */
    public function getErrorCode(){
        return $this->_errorCode;
    }
   
    /**
     * Gets error type returned by the service.
     *
     * @return string Error Type returned by the service.
     * Possible types:  Sender, Receiver or Unknown
     */
    public function getErrorType(){
        return $this->_errorType;
    }
    
    
    /**
     * Gets error message
     *
     * @return string Error message
     */
    public function getErrorMessage() {
        return $this->_message;
    }
    
    /**
     * Gets status code returned by the service if available. If status
     * code is set to -1, it means that status code was unavailable at the
     * time exception was thrown
     *
     * @return int status code returned by the service
     */
    public function getStatusCode() {
        return $this->_statusCode;
    }
    
    /**
     * Gets XML returned by the service if available.
     *
     * @return string XML returned by the service
     */
    public function getXML() {
        return $this->_xml;
    }
    
    /**
     * Gets Request ID returned by the service if available.
     *
     * @return string Request ID returned by the service
     */
    public function getRequestId() {
        return $this->_requestId;
    }
}

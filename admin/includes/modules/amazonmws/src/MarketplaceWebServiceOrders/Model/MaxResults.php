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
 *  @see MarketplaceWebServiceOrders_Model
 */
require_once ('MarketplaceWebServiceOrders/Model.php');  

    
        private   valueField;

/**
 * MarketplaceWebServiceOrders_Model_MaxResults
 * 
 * Properties:
 * <ul>
 * 
 *
 * </ul>
 */ 
class MarketplaceWebServiceOrders_Model_MaxResults extends MarketplaceWebServiceOrders_Model
{


    /**
     * Construct new MarketplaceWebServiceOrders_Model_MaxResults
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        );
        parent::__construct($data);
    }

    
/**
 * Gets the value of the Value property.
 * 
 * @return  Value
 */
public function getValue() 
{
    return $this->_value;
}

/**
 * Sets the value of the Value property.
 * 
 * @param  Value
 * @return void
 */
public function setValue($value) 
{
    $this->_Value = $value;
    return;
}


/**
 * Checks if Value property is set
 * 
 * @return bool true if Value property is set
 */
public function isSetValue()
{
    return !is_null($this->_value);

}


}
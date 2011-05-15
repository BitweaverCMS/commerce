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

class amazonmws extends BitBase { 
   var $code;
   var $title;
   var $description;
   var $enabled; 
   var $mPartsList;

// class constructor
   function amazonmws() {
		$this->code = 'amazonmws';
   		parent::__construct();
		if ( !empty( $_GET['main_page'] ) ) {
			$this->title = ''; // Payment Module title in Catalog
		} else {
			$this->title = tra( 'AmazonMWS' ); // Payment Module title in Admin
		}
		$this->description = tra( 'AmazonMWS Fulfillment<br/><a href="'.PRODUCTS_PKG_URL.'admin/accounting/index.php?fulfillment_code=amazonmws">Accounting</a>' );
		$this->sort_order = 5;
		$this->enabled = ((defined( 'MODULE_FULFILLMENT_AMAZONMWS_STATUS' ) && MODULE_FULFILLMENT_AMAZONMWS_STATUS == 'True') ? true : false);
		$this->mPartsList = array(
			'123' =>	'Acme 123 Widget',
		);
	}

	function check() {
		global $gBitDb;
		if( !isset( $this->_check ) ) {
			$this->_check = 'True' == $gBitDb->getOne("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = ?", array( strtoupper( 'MODULE_FULFILLMENT_'.$this->code.'_STATUS' ) ) );
		}
		return $this->_check;
	}

	function getManufactureTime( $pOrderProduct=NULL, $pQuantity=NULL ) {
		$ret = MODULE_FULFILLMENT_AMAZONMWS_MANF_TIME;
		return $ret;
	}

	function getShippingMethodCode( $pShippingMethodCode ) {
		$ret = $pShippingMethodCode;
		switch( $pShippingMethodCode ) {
			default:
				$ret = $pShippingMethodCode;
				break;
		}
		return $ret;
	}

	function getPartNumber( &$pOrderProduct, $pOrderProductHash ) {
		switch( $pOrderProductHash['1'] ) {
			case '42':
				$partNumber = 123;
				break;
		}
		$ret = isset( $this->mPartsList[$partNumber] ) ? $partNumber : NULL;

		return $ret;
	}


	function getCostPrice( $pPartNumber, $pPages, $pQuantity ) {
		$lotSize = 1;
		switch( $pPartNumber ) {
			case '123':
				$basePrice = 5.05;
				break;
			default:
				$basePrice = 10.50;
				break;
		}

		return( $pQuantity * $basePrice );
	}

   function install() {
     global $gBitDb, $gBitUser;
     $gBitDb->StartTrans();
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable AmazonMWS Module', 'MODULE_FULFILLMENT_AMAZONMWS_STATUS', 'True', 'Do you want enable AmazonMWS fulfillment?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('AmazonMWS Activation Mode', 'MODULE_FULFILLMENT_AMAZONMWS_MODE', 'Test', 'What mode is your account in?', '6', '2', 'zen_cfg_select_option(array(\'Production\', \'Test\'), ', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Local Username', 'MODULE_FULFILLMENT_AMAZONMWS_LOCAL_USERNAME','amazonmws', 'This is the username on this site under which all orders will be processed.', '6', '4', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Merchant ID', 'MODULE_FULFILLMENT_AMAZONMWS_MERCHANT_ID','', '', '6', '4', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Marketplace ID', 'MODULE_FULFILLMENT_AMAZONMWS_MARKETPLACE_ID','', '', '6', '4', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('AWS Access Key ID', 'MODULE_FULFILLMENT_AMAZONMWS_AWS_ACCESS_KEY_ID','', '', '6', '4', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Secret Key', 'MODULE_FULFILLMENT_AMAZONMWS_SECRET_KEY','', '', '6', '4', now())");
    $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Initial Order Status', 'MODULE_FULFILLMENT_AMAZONMWS_INITIAL_ORDER_STATUS_ID', '20', 'Orders with this status will be processed for fulfillment<br />(\'Transferred\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
//	$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Final Order Status', 'MODULE_FULFILLMENT_AMAZONMWS_FINAL_ORDER_STATUS_ID', '40', 'Set the status of orders that have completed fulfillment to this value<br />(\'Fulfilling\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
//	$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Manufacturing Days', 'MODULE_FULFILLMENT_AMAZONMWS_MANF_TIME','3', 'Number of days it takes to manufacture books', '6', '8', now())");

	if( !$gBitUser->lookupHomepage( 'amazonmws' ) ) {
		$newUser = new BitPermUser();
		$userHash['login'] = 'amazonmws';
		$userHash['email'] = str_replace( '@', '+amazonmws@', STORE_OWNER_EMAIL_ADDRESS );
		$userHash['real_name'] = 'Amazon Marketplace';
		$userHash['hash'] = $gBitUser->getField( 'hash' );
		$newUser->importUser( $userHash );
	}
    $gBitDb->CompleteTrans();
}

   function remove() {
     global $gBitDb;
     $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` LIKE  'MODULE_FULFILLMENT_AMAZONMWS%'");
   }

   function keys() {
     return array(
		'MODULE_FULFILLMENT_AMAZONMWS_STATUS',
		'MODULE_FULFILLMENT_AMAZONMWS_MODE',
		'MODULE_FULFILLMENT_AMAZONMWS_LOCAL_USERNAME',
		'MODULE_FULFILLMENT_AMAZONMWS_MERCHANT_ID',
		'MODULE_FULFILLMENT_AMAZONMWS_MARKETPLACE_ID',
		'MODULE_FULFILLMENT_AMAZONMWS_AWS_ACCESS_KEY_ID',		
		'MODULE_FULFILLMENT_AMAZONMWS_SECRET_KEY',		
		'MODULE_FULFILLMENT_AMAZONMWS_INITIAL_ORDER_STATUS_ID',
		'MODULE_FULFILLMENT_AMAZONMWS_FINAL_ORDER_STATUS_ID',
		'MODULE_FULFILLMENT_AMAZONMWS_MANF_TIME',
     );
   }

 }
?>

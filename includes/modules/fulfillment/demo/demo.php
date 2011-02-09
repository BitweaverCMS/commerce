<?php
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004-2007 viovio.com, Proprietary CODE, ALL RIGHTS RESERVED |
// +---------------------------------------------------------------------------+
// $Id: demo.php,v 1.7 2010/04/02 15:45:49 cfowler Exp $
//

// Note this is temporary

class demo extends BitBase { 
   var $code;
   var $title;
   var $description;
   var $enabled; 
   var $mPartsList;

// class constructor
   function demo() {
		$this->code = 'demo';
   		parent::__construct();
		if ( !empty( $_GET['main_page'] ) ) {
			$this->title = ''; // Payment Module title in Catalog
		} else {
			$this->title = tra( 'Demo' ); // Payment Module title in Admin
		}
		$this->description = tra( 'Demo Fulfillment<br/><a href="'.PRODUCTS_PKG_URL.'admin/accounting/index.php?fulfillment_code=demo">Accounting</a>' );
		$this->sort_order = 5;
		$this->enabled = ((defined( 'MODULE_FULFILLMENT_DEMO_STATUS' ) && MODULE_FULFILLMENT_DEMO_STATUS == 'True') ? true : false);
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
		$ret = MODULE_FULFILLMENT_DEMO_MANF_TIME;
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
     global $gBitDb;
     $gBitDb->StartTrans();
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable Demo Module', 'MODULE_FULFILLMENT_DEMO_STATUS', 'True', 'Do you want enable Demo fulfillment?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Demo Activation Mode', 'MODULE_FULFILLMENT_DEMO_MODE', 'Test', 'What mode is your account in?', '6', '2', 'zen_cfg_select_option(array(\'Production\', \'Test\'), ', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Username', 'MODULE_FULFILLMENT_DEMO_USERNAME','', 'Username', '6', '4', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Password', 'MODULE_FULFILLMENT_DEMO_PASSWORD','', 'Password', '6', '4', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('FTP URI', 'MODULE_FULFILLMENT_DEMO_FTP_URI','', 'FTP URI where order will be transfered.', '6', '4', now())");
    $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Initial Order Status', 'MODULE_FULFILLMENT_DEMO_INITIAL_ORDER_STATUS_ID', '30', 'Orders with this status will be processed for fulfillment<br />(\'Transferred\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Final Order Status', 'MODULE_FULFILLMENT_DEMO_FINAL_ORDER_STATUS_ID', '40', 'Set the status of orders that have completed fulfillment to this value<br />(\'Fulfilling\' recommended)', '6', '5', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
     $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Manufacturing Days', 'MODULE_FULFILLMENT_DEMO_MANF_TIME','3', 'Number of days it takes to manufacture books', '6', '8', now())");
    $gBitDb->CompleteTrans();
}

   function remove() {
     global $gBitDb;
     $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` LIKE  'MODULE_FULFILLMENT_DEMO%'");
   }

   function keys() {
     return array(
		'MODULE_FULFILLMENT_DEMO_STATUS',
		'MODULE_FULFILLMENT_DEMO_MODE',
		'MODULE_FULFILLMENT_DEMO_USERNAME',		
		'MODULE_FULFILLMENT_DEMO_PASSWORD',		
		'MODULE_FULFILLMENT_DEMO_FTP_URI',		
		'MODULE_FULFILLMENT_DEMO_INITIAL_ORDER_STATUS_ID',
		'MODULE_FULFILLMENT_DEMO_FINAL_ORDER_STATUS_ID',
		'MODULE_FULFILLMENT_DEMO_MANF_TIME',
     );
   }

 }
?>

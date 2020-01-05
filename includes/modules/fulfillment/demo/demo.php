<?php
// +---------------------------------------------------------------------------+
// | Copyright (c) 2004-2007 viovio.com, Proprietary CODE, ALL RIGHTS RESERVED |
// +---------------------------------------------------------------------------+
//

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginFulfillmentBase.php' );

class demo extends CommercePluginFulfillmentBase { 
	 var $code;
	 var $title;
	 var $description;
	 var $enabled; 
	 var $mPartsList;

// class constructor
	 function __construct() {
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

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_FTP_URI' => array(
				'configuration_title' => 'FTP URI',
				'configuration_description' => 'URI to FTP site where orders will be transferred. (not functional)',
				'configuration_group_id' => '6',
				'sort_order' => '2',
			),
		) );
	}
 }

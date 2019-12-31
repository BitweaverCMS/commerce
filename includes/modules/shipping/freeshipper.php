<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2019 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class freeshipper extends CommercePluginShippingBase {

	function __construct() {
		parent::__construct();
		$this->title = tra( 'FREE SHIPPING!' );
		$this->description = tra( 'FREE SHIPPING' );
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			$quotes['methods'][] = array(
										'id' => $this->code,
										'title' => tra( 'Free Shipping Only' ),
										'cost' => MODULE_SHIPPING_FREESHIPPER_COST + MODULE_SHIPPING_FREESHIPPER_HANDLING
										);
		}
		return $quotes;
	}

	function install() {
		if( !$this->isInstalled() ) {
			parent::install();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Free Shipping Cost', 'MODULE_SHIPPING_FREESHIPPER_COST', '0.00', 'What is the Shipping cost?', '6', '6', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Handling Fee', 'MODULE_SHIPPING_FREESHIPPER_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");
		}
	}

	function keys() {
		return array_merge( parent::keys(), array(
			'MODULE_SHIPPING_FREESHIPPER_COST', 
			'MODULE_SHIPPING_FREESHIPPER_HANDLING', 
		) );
	}
}

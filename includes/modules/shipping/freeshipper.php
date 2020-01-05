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

	protected function config() {
		$i = 3;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_COST' => array(
				'configuration_title' => 'Shipping Cost',
				'configuration_description' => 'The shipping cost for all orders using this shipping method.',
				'configuration_value' => '0',
				'sort_order' => $i++,
			),
		) );
	}
}

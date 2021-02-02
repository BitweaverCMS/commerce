<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class flat extends CommercePluginShippingBase {

	function __construct() {
		parent::__construct();
		$this->title = tra( 'Flat Rate' );
		$this->description = tra( 'Fixed price shipping for orders of any size.' );
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			$quotes['methods'][] = array(
										'id' => $this->code,
										'title' => tra( 'Best Way' ),
										'cost' => MODULE_SHIPPING_FLAT_COST
										);
		}
		return $quotes;
	}

	protected function config() {
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_COST' => array(
				'configuration_title' => 'Shipping Cost',
				'configuration_description' => 'The shipping cost for all orders using this shipping method.',
				'configuration_value' => '5',
			),
		) );
	}
}

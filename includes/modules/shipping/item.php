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

class item extends CommercePluginShippingBase {

	function __construct() {
		parent::__construct();
		$this->title = tra('Per Item');
		$this->description = tra( 'Per Item' );
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			$quotes['methods'][] = array(
										'id' => $this->code,
										'title' => tra( 'Best Way' ),
										'cost' => (MODULE_SHIPPING_ITEM_COST * $pShipHash['shipping_num_boxes']) + MODULE_SHIPPING_ITEM_HANDLING
										);
		}

		return $quotes;
	}

	protected function config() {
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_COST' => array(
				'configuration_title' => 'Shipping Cost',
				'configuration_description' => 'The shipping cost will be multiplied by the number of items in an order that uses this shipping method.',
				'configuration_value' => '2.5',
			),
		) );
	}
}

<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2019 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class table extends CommercePluginShippingBase {

	function __construct() {
		parent::__construct();
		$this->title = tra( 'Table Rate' );
		$this->description = tra( 'Table Rate' );
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {

			if (MODULE_SHIPPING_TABLE_MODE == 'price') {
				$shipMetric = $pShipHash['shipment_value'];
			} else {
				$shipMetric = $pShipHash['shipping_weight_box'];
			}

			$shipping = 0;

			$table_cost = preg_split("#[:,]#" , MODULE_SHIPPING_TABLE_COST);
			$size = sizeof($table_cost);
			for( $k = 0; $k < $pShipHash['shipping_num_boxes']; $k++ ) {
				for( $i=0, $n=$size; $i < $n; $i+=2 ) {
					if( $shipMetric <= $table_cost[$i] ) {
						$shipping += $table_cost[$i+1];
						break;
					}
				}
			}

			$quotes['methods'][] = array(
										'id' => $this->code,
										'title' => MODULE_SHIPPING_TABLE_TEXT_WAY,
										'cost' => $shipping + MODULE_SHIPPING_TABLE_HANDLING 
										);
		}

		return $quotes;
	}

	protected function config() {
		$i = 3;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_COST' => array(
				'configuration_title' => 'Shipping Table',
				'configuration_value' => '25:8.50,50:5.50,10000:0.00',
				'configuration_description' => 'The shipping cost is based on the total cost or weight of items. Example: 25:8.50,50:5.50,etc.. Up to 25 charge 8.50, from there to 50 charge 5.50, etc',
				'sort_order' => $i++,
				'set_function' => 'zen_cfg_textarea(',
			),
			$this->getModuleKeyTrunk().'_MODE' => array(
				'configuration_title' => 'Table Method',
				'configuration_value' => 'weight',
				'configuration_description' => 'The shipping cost is based on the order total or the total weight of the items ordered.',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('weight', 'price'), ",
			),
		) );
	}
}

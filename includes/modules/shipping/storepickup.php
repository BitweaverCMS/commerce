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

class storepickup extends CommercePluginShippingBase {
	var $code, $title, $description, $icon, $enabled;

	function __construct() {
		parent::__construct();
		$this->title = tra( 'Store Pickup' );
		$this->description = tra( 'Customer In Store Pick-up' );
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			$quotes['methods'][] = array(
										'id' => $this->code,
										'code' => $this->code,
										'title' => tra( 'Walk In' ),
										'cost' => MODULE_SHIPPING_STOREPICKUP_COST
										);
		}
		return $quotes;
	}

	protected function config() {
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_COST' => array(
				'configuration_title' => 'Shipping Cost',
				'configuration_description' => 'The shipping cost for all orders using this shipping method.',
				'configuration_value' => '0',
			),
		) );
	}
}
?>

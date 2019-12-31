<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

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

	function install() {
		if( !$this->isInstalled() ) {
			parent::install();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Shipping Cost', 'MODULE_SHIPPING_FLAT_COST', '5.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now())");
		}
	}

	function keys() {
		return array_merge( parent::keys(), array(
			'MODULE_SHIPPING_FLAT_COST',
		) );
	}
}

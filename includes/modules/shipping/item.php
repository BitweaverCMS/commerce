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

	function install() {
		if( !$this->isInstalled() ) {
			parent::install();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Shipping Cost', 'MODULE_SHIPPING_ITEM_COST', '2.50', 'The shipping cost will be multiplied by the number of items in an order that uses this shipping method.', '6', '0', now())");
		}
	}

	function keys() {
		return array_merge( parent::keys(), array(
			'MODULE_SHIPPING_ITEM_COST',
		) );
	}
}

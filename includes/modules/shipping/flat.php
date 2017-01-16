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
	protected $tax_class, $tax_basis;

	// class constructor
	function __construct() {

		parent::__construct();

		$this->title = tra( 'Flat Rate' );
		$this->description = tra( 'Fixed price shipping for orders of any size.' );
		$this->icon = '';
		if( $this->isEnabled() ) {
			$this->sort_order = MODULE_SHIPPING_FLAT_SORT_ORDER;
			$this->tax_class = MODULE_SHIPPING_FLAT_TAX_CLASS;
			$this->tax_basis = MODULE_SHIPPING_FLAT_TAX_BASIS;
		}
	}

// class methods
	function quote( $pShipHash = array() ) {
		global $order;

		$this->quotes = array();

		$quoteOrder = TRUE;
		if ( ((int)MODULE_SHIPPING_FLAT_ZONE > 0) ) {
			$quoteOrder = false;
			$check = $this->mDB->query("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` =? and `zone_country_id` = ? order by `zone_id`", array( MODULE_SHIPPING_FLAT_ZONE, $order->delivery['country']['countries_id'] ) );
			while (!$check->EOF) {
				if ($check->fields['zone_id'] < 1) {
					$quoteOrder = true;
					break;
				} elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
					$quoteOrder = true;
					break;
				}
				$check->MoveNext();
			}
		}

		if( $quoteOrder ) {
			$this->quotes = array(	'id' => $this->code,
									'module' => $this->title,
									'methods' => array(array('id' => $this->code,
									'title' => tra( 'Best Way' ),
									'cost' => MODULE_SHIPPING_FLAT_COST)));
			if ($this->tax_class > 0) {
				$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
			}

			if (zen_not_null($this->icon)) {
				$this->quotes['icon'] = $this->icon;
			}
		}

		return $this->quotes;
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable Flat Shipping', 'MODULE_SHIPPING_FLAT_STATUS', 'True', 'Do you want to offer flat rate shipping?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Shipping Cost', 'MODULE_SHIPPING_FLAT_COST', '5.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_SHIPPING_FLAT_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Tax Basis', 'MODULE_SHIPPING_FLAT_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(''Shipping'', ''Billing'', ''Store''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Shipping Zone', 'MODULE_SHIPPING_FLAT_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_SHIPPING_FLAT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
	}

	function keys() {
		return array('MODULE_SHIPPING_FLAT_STATUS', 'MODULE_SHIPPING_FLAT_COST', 'MODULE_SHIPPING_FLAT_TAX_CLASS', 'MODULE_SHIPPING_FLAT_TAX_BASIS', 'MODULE_SHIPPING_FLAT_ZONE', 'MODULE_SHIPPING_FLAT_SORT_ORDER');
	}
}
?>

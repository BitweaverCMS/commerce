<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginOrderTotalBase.php' );

class ot_tax extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->code = 'ot_tax';

		$this->title = MODULE_ORDER_TOTAL_TAX_TITLE;
		$this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
		$this->sort_order = MODULE_ORDER_TOTAL_TAX_SORT_ORDER;
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_TAX_STATUS';
	}

	function process() {
		parent::process();
		global $currencies;

		reset($this->mOrder->info['tax_groups']);
		while (list($key, $value) = each($this->mOrder->info['tax_groups'])) {
			if ($value > 0 or STORE_TAX_DISPLAY_STATUS == 1) {
				$this->mProcessingOutput = array( 'code' => $this->code,
													'sort_order' => $this->getSortOrder(),
													'title' => $key,
													'text' => $currencies->format($value, true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
													'value' => $value);
			}
		}
	}

	function keys() {
		return array('MODULE_ORDER_TOTAL_TAX_STATUS', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER');
	}

	function install() {
	global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '300', 'Sort order of display.', '6', '2', now())");
	}

}

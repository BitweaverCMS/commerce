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

class ot_subtotal extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->code = 'ot_subtotal';

		$this->title = MODULE_ORDER_TOTAL_SUBTOTAL_TITLE;
		$this->description = MODULE_ORDER_TOTAL_SUBTOTAL_DESCRIPTION;
		$this->sort_order = MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER;
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS';
	}

	function process() {
		parent::process();
		global $currencies;

		$this->mProcessingOutput = array( 'code' => $this->code,
											'sort_order' => $this->getSortOrder(),
											'title' => $this->title,
											'text' => $currencies->format($this->mOrder->subtotal, true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
											'value' => $this->mOrder->subtotal);
	}

	function keys() {
		return array('MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER');
	}

	function install() {
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '100', 'Sort order of display.', '6', '2', now())");
	}

}

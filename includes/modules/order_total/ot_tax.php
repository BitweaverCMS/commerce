<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginOrderTotalBase.php' );

class ot_tax extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->title = MODULE_ORDER_TOTAL_TAX_TITLE;
		$this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
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

	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$ret = parent::config();
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '300';
		return $ret;
	}

}

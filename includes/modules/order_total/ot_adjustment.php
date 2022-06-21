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

class ot_adjustment extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->code = 'ot_adjustment';

		$this->title = tra( 'Order Adjustment' );
		$this->description = tra( 'Order Adjustment' );
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_ADJUSTMENT_STATUS';
	}

	function process() {
		parent::process();
		global $currencies;

		$this->mProcessingOutput = array( 'code' => $this->code,
											'sort_order' => $this->getSortOrder(),
											'title' => $this->title,
											'text' => $currencies->format($this->mOrder->adjustment, true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
											'value' => $this->mOrder->adjustment);
	}

	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$ret = parent::config();
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '1000';
		return $ret;
	}

}

<?php
// +----------------------------------------------------------------------+
// | bitcommerce														  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org								   |
// |																	  |
// | http://www.bitcommerce.org										   |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginBase.php' );

abstract class CommercePluginOrderTotalBase extends CommercePluginBase {
	protected $output, $mOrder, $mProcessingOutput;

	public function __construct( &$pOrder = NULL ) {
		parent::__construct();
		$this->mProcessingOutput = array();
		if( $pOrder ) {
			$this->setOrder( $pOrder );
		}
	}

	public function setOrder( &$pOrder ) {
		$this->mOrder = &$pOrder;
	}

	protected function getConfigKey() {
		return strtoupper( str_replace( 'ot_', '', $this->code ) );
	}

	protected function getTitle( $pDefault = '' ) {
		if( !empty( $pDefault ) ) {
			$ret = $pDefault;
		} elseif( $ret = $this->getModuleConfigValue( '_TITLE' ) ) {
		} else {
			$ret = ucwords( str_replace( '_', ' ', str_replace( 'ot_', ' ', get_class( $this ) ) ) );
		}
		return tra( $ret );
	}

	protected function getModuleType() {
		return 'order_total';
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		$this->mProcessingOutput = array();
	}

	public function credit_selection( $pOrder, &$pSessionParams ) {}
	public function update_credit_account( $i ) {}
	public function collect_posts( $pRequestParams, &$pSessionParams ) {}
	public function apply_credit( &$pSessionParams ) {}
	public function getOrderDeduction( $pOrder, &$pSessionParams ) {}

	public function getOutput() {
		return $this->mProcessingOutput;
	}

	function setOrderDeduction( $pDeduction, $pCode = 'discount' ) {
		if( !empty( $this->mOrder ) ) {
			$this->mOrder->info['deductions'][$this->code][$pCode] = $pDeduction;	
		}
	}
}



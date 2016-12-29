<?php
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginBase.php' );

abstract class CommercePluginOrderTotalBase extends CommercePluginBase {
	protected $title, $output, $mOrder, $mProcessingOutput;

	public function __construct( $pOrder ) {
		parent::__construct();
		$this->mOrder = &$pOrder;
		$this->mProcessingOutput = array();
	}

    function process() {
		$this->mProcessingOutput = array();
	}
    function credit_selection() {}
    function update_credit_account( $i ) {}
    function collect_posts( $pRequestParams ) {}
    function apply_credit() {}
	function pre_confirmation_check() {}

	function getOutput() {
		return $this->mProcessingOutput;
	}
}



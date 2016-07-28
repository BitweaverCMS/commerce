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

abstract class CommercePluginBase extends BitBase {

	abstract function keys();
	abstract function install();
	// Check if module is installed (Administration Tool)

	public $mStatusKey;

	public function __construct() {
		parent::__construct();
	}

	function remove() {
		global $gBitDb;
		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
	}

	function isEnabled() {
		global $gCommerceSystem;
		return $gCommerceSystem->isConfigActive( $this->mStatusKey );
	}

	function check() {
		global $gCommerceSystem;
		if( !isset( $this->_check ) ) {
			$this->_check = $gCommerceSystem->isConfigActive( $this->mStatusKey );
		}
		return $this->_check;
	}

}


<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2019 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingRateTableBase.php' );

class fixed extends CommercePluginShippingRateTableBase {

	function __construct() {
		parent::__construct();
		$this->description = tra( 'Fixed Rate' );
	}

	public function quote( $pShipHash ) {
		return parent::quote( $pShipHash );
	}

}

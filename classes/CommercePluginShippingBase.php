<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2013 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginBase.php' );

abstract class CommercePluginShippingBase extends CommercePluginBase {

	protected $quotes = array();

	abstract public function quote( $pShipHash = array() );

	protected function getStatusKey() {
		return 'MODULE_SHIPPING_'.strtoupper( $this->code ).'_STATUS';
	}

	public function __construct() {
		parent::__construct();
		$this->quotes = array();
	}

}

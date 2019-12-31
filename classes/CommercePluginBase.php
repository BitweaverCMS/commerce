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

	public $code;
	public $title;
	public $description;
	public $enabled; 
	public $icon; 
	protected $isEnabled;
	protected $isInstalled;
	protected $sort_order;
	protected $mConfigKey;

	abstract public function keys();
	abstract public function install();
	abstract protected function getStatusKey();
	// Check if module is installed (Administration Tool)

	public function __construct() {
		parent::__construct();
		$this->code = get_called_class();
		$this->mConfigKey = $this->getConfigKey();
		$this->enabled = $this->isEnabled(); // legacy support for old plugins
		$this->check = $this->isInstalled(); // legacy support for old plugins
	}

	public function remove() {
		$this->mDb->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` IN ('" . implode("', '", $this->keys()) . "')");
	}

	protected function getConfigKey() {
		return strtoupper( $this->code );
	}

	public function isEnabled() {
		global $gCommerceSystem;
		if( !isset( $this->isEnabled ) ) {
			$this->isEnabled = $gCommerceSystem->isConfigActive( $this->getStatusKey() );
		}
		return $this->isEnabled;
	}

	public function check() {
		return $this->isInstalled();
	}

	public function getSortOrder() {
		return $this->sort_order;
	}

	public function isInstalled() {
		global $gCommerceSystem;
		if( !isset( $this->isInstalled ) ) {
			$this->isInstalled= $gCommerceSystem->isConfigLoaded( $this->getStatusKey() );
		}
		$this->check = $this->isInstalled; // legacy variable
		return $this->isInstalled;
	}

	function getConfig( $pConfigName, $pDefault=NULL ) {
		global $gCommerceSystem;
		return $gCommerceSystem->getConfig( $pConfigName, $pDefault );
	}

	function isConfigActive( $pConfigName ) {
		global $gCommerceSystem;
		return $gCommerceSystem->isConfigActive( $pConfigName );
	}

	public function storeConfig ( $pConfigKey, $pConfigValue ) {
		global $gCommerceSystem;
		return $gCommerceSystem->storeConfig( $pConfigKey, $pConfigValue );
	}
}


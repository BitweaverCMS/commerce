<?php

require_once( KERNEL_PKG_PATH.'BitSingleton.php' );

class CommerceSystem extends BitSingleton {
	var $mConfig = array();
	var $mProductTypeLayout = array();

	function __construct() {
		parent::__construct();
		$this->loadConfig();
	}

    public function __wakeup() {
		$this->loadConstants();
	}

	private function loadConstants() {
		foreach( $this->mConfig AS $key=>$value ) {
			define($key, $value );
		}

		foreach( $this->mProductTypeLayout AS $key=>$value ) {
			define($key, $value );
		}
    }

	public function storeConfig ( $pConfigName, $pConfigValue ) {
          if( is_array( $pConfigValue ) ){
			// see usage in UPS and USPS
            $pConfigValue = implode( ", ", $pConfigValue );
            $pConfigValue = str_replace ( ", --none--", "", $pConfigValue );
          }
			if( !empty( $this->mConfig[$pConfigKey] ) ) {
	        	$this->mDb->query( "UPDATE " . TABLE_CONFIGURATION . " SET `configuration_value` = ? WHERE `configuration_key` = ?", array( $pConfigValue, $pConfigKey ) );
				$this->mConfig[$pConfigKey] = $pConfigValue;
			} else {
				// TODO Need more robust insert here.
//	        	$this->mDb->query( "INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_value`, `configuration_key` ) VALUES ( ?, ? )", array( $pConfigValue, $pConfigKey ) );
			}


		
	}

	function loadConfig() {
		$this->mConfig = $this->mDb->getAssoc( 'SELECT `configuration_key` AS `cfgkey`, `configuration_value` AS `cfgvalue` FROM ' . TABLE_CONFIGURATION ); 
		$this->mProductTypeLayout = $this->mDb->getAssoc( 'select `configuration_key` as `cfgkey`, `configuration_value` as `cfgvalue` from ' . TABLE_PRODUCT_TYPE_LAYOUT );
		$this->loadConstants();
	}

	function getConfig( $pConfigName, $pDefault=NULL ) {
		global $gBitSystem;
		$ret = $pDefault;
		if( defined( strtoupper( $pConfigName ) ) ) {
			$ret = constant( strtoupper( $pConfigName ) );
		} elseif( $pDefault === NULL && strpos( 'MAX_DISPLAY', $pConfigName ) !== FALSE ) {
			$ret = $gBitSystem->getConfig( 'max_records', 20 );
		} else {
			$ret = $gBitSystem->getConfig( strtolower( $pConfigName ), $pDefault );
		}
		return $ret;
	}

	static function isConfigActive( $pConfigName ) {
		return ((defined( $pConfigName ) && strtolower( constant( $pConfigName ) ) == 'true') ? true : false);
	}

	function setHeadingTitle( $pTitle ) {
		if( !defined( 'HEADING_TITLE' ) ) {
			define( 'HEADING_TITLE', $pTitle );
		}
	}
}


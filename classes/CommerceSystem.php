<?php

require_once( KERNEL_PKG_PATH.'BitBase.php' );

class CommerceSystem extends BitBase {
	var $mConfig;
	var $mProductTypeLayout;

	function CommerceSystem() {
		BitBase::BitBase();
		$this->loadConfig();
	}

	function loadConfig() {
		if( $this->mConfig = $this->mDb->getAssoc( 'SELECT `configuration_key` AS `cfgkey`, `configuration_value` AS `cfgvalue` FROM ' . TABLE_CONFIGURATION ) ) {
			foreach( $this->mConfig AS $key=>$value ) {
				define($key, $value );
			}
		}

		if( $this->mProductTypeLayout = $this->mDb->getAssoc( 'select `configuration_key` as `cfgkey`, `configuration_value` as `cfgvalue` from ' . TABLE_PRODUCT_TYPE_LAYOUT ) ) {
			foreach( $this->mProductTypeLayout AS $key=>$value ) {
				define($key, $value );
			}
		}
	}

	function getConfig( $pConfigName, $pDefault=NULL ) {
		global $gBitSystem;
		$ret = $pDefault;
		if( defined( $pConfigName ) ) {
			$ret = constant( $pConfigName );
		} elseif( $pDefault === NULL && strpos( 'MAX_DISPLAY', $pConfigName ) !== FALSE ) {
			$ret = $gBitSystem->getConfig( 'max_records', 20 );
		}
		return $ret;
	}
}

?>

<?php

require_once( KERNEL_PKG_PATH.'BitSingleton.php' );

class CommerceSystem extends BitSingleton {
	public $mConfig = array();
	public $mProductTypeLayout = array();
	public $mTemplateDir = '';

	function __construct() {
		parent::__construct();
		$this->loadConfig();
	}

		public function __wakeup() {
		parent::__wakeup();
		$this->loadConstants();
	}

	public function __sleep() {
		return array_merge( parent::__sleep(), array( 'mConfig', 'mProductTypeLayout' ) );
	}

	private function loadConstants() {
		foreach( $this->mConfig AS $key=>$value ) {
			define($key, $value );
		}

		foreach( $this->mProductTypeLayout AS $key=>$value ) {
			define($key, $value );
		}

		// Set theme related directories
		$this->mTemplateDir = $this->mDb->getOne( "SELECT `template_dir` FROM " . TABLE_TEMPLATE_SELECT .	" WHERE `template_language` = ?", array( $this->getParameter( $_SESSION, 'languages_id', 0 )), NULL, NULL, BIT_QUERY_CACHE_TIME );
		//if (template_switcher_available=="YES") $this->mTemplateDir = templateswitch_custom($current_domain);
		define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES . $this->mTemplateDir . '/');
		define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE . 'images/');
		define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');

	}

	public function storeConfigId ( $pConfigId, $pConfigValue ) {
		$configKey = $this->mDb->getOne( 'SELECT `configuration_key` FROM ' . TABLE_CONFIGURATION . ' WHERE `configuration_id`=? ', array( $pConfigId ) );
		$this->storeConfig( $configKey, $pConfigValue );
	}

	public function storeConfig ( $pConfigKey, $pConfigValue ) {
		if( is_array( $pConfigValue ) ){
			// see usage in UPS and USPS
			$pConfigValue = implode( ", ", $pConfigValue );
			$pConfigValue = str_replace ( ", --none--", "", $pConfigValue );
		}

		if( $pConfigValue !== NULL ) {
			if( isset( $this->mConfig[$pConfigKey] ) ) {
				$this->mDb->query( "UPDATE " . TABLE_CONFIGURATION . " SET `configuration_value` = ?, `last_modified`='NOW' WHERE `configuration_key` = ?", array( $pConfigValue, $pConfigKey ) );
			} else {
				$defaultTitle = ucwords( strtolower( str_replace( '_', ' ', preg_replace( '/MODULE_[A-Z]*_/', '', $pConfigKey ) ) ) );
				$this->mDb->query( "INSERT INTO " . TABLE_CONFIGURATION . " ( `configuration_value`, `configuration_key`, `configuration_title` ) VALUES ( ?, ?, ? )", array( $pConfigValue, $pConfigKey, $defaultTitle ) );
			}
		} else {
			$this->mDb->query( "DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = ?", array( $pConfigKey ) );
		}
		$this->mConfig[$pConfigKey] = $pConfigValue;
		$this->clearFromCache();
	}

	function loadConfig() {
		$this->mConfig = $this->mDb->getAssoc( 'SELECT `configuration_key` AS `cfgkey`, `configuration_value` AS `cfgvalue` FROM ' . TABLE_CONFIGURATION, NULL, NULL, NULL, BIT_QUERY_CACHE_TIME  ); 
		$this->mProductTypeLayout = $this->mDb->getAssoc( 'select `configuration_key` as `cfgkey`, `configuration_value` as `cfgvalue` from ' . TABLE_PRODUCT_TYPE_LAYOUT, NULL, NULL, NULL, BIT_QUERY_CACHE_TIME  );
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

	function isConfigLoaded( $pConfigName ) {
		return isset( $this->mConfig[$pConfigName] );
	}

	static function isConfigDefined( $pConfigName ) {
		return defined( $pConfigName );
	}

	static function isConfigActive( $pConfigName ) {
		return ((defined( $pConfigName ) && strtolower( constant( $pConfigName ) ) == 'true') ? true : false);
	}

	function setHeadingTitle( $pTitle ) {
		if( !defined( 'HEADING_TITLE' ) ) {
			define( 'HEADING_TITLE', $pTitle );
		}
	}

	// {{{ =================== Template ====================
	function get_template_part($page_directory, $template_part, $file_extension = '.php') {
		$directory_array = array();
		if( is_dir( $page_directory ) && $dir = dir($page_directory)) {
			while ($file = $dir->read()) {
				if (!is_dir($page_directory . $file)) {
					if (substr($file, strrpos($file, '.')) == $file_extension && preg_match($template_part, $file)) {
						$directory_array[] = $file;
					}
				}
			}

			sort($directory_array);
			$dir->close();
		}
		return $directory_array;
	}

	function get_template_dir($template_code, $current_template, $current_page, $template_dir, $debug=false) {
		if ($this->template_file_exists($current_template . $current_page, $template_code)) {
			return $current_template . $current_page . '/';
		} elseif ($this->template_file_exists(DIR_WS_TEMPLATES . 'template_default/' . $current_page, str_replace('/', '', $template_code), $debug)) {
			return DIR_WS_TEMPLATES . 'template_default/' . $current_page;
		} elseif ($this->template_file_exists($current_template . $template_dir, str_replace('/', '', $template_code), $debug)) {
			return $current_template . $template_dir;
		} else {
			return DIR_WS_TEMPLATES . 'template_default/' . $template_dir;
//        return $current_template . $template_dir;
		}

	}

	function template_file_exists($file_dir, $file_pattern, $debug=false) {
		$file_found = false;
		if( is_dir( $file_dir ) && $mydir = dir( $file_dir ) ) {
			while ($file = $mydir->read()) {
				if ( strstr($file, $file_pattern) ) {
					$file_found = true;
					break;
				}
			}
		}
		return $file_found;
	}
	// }}}
}


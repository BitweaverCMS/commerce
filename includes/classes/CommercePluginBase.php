<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 * Copyright (c) 2020 bitweaver.org, All Rights Reserved
 * This source file is subject to the 2.0 GNU GENERAL PUBLIC LICENSE. 
 *
 * Base class for all plugin types.
 *
 */

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceBase.php' );

abstract class CommercePluginBase extends CommerceBase {

	public $code;
	public $title;
	protected $adminTitle = '';
	public $description;
	public $enabled; 
	public $icon; 

	// Cached computed vars
	protected $isEnabled;
	protected $isInstalled;
	protected $sort_order;
	protected $mConfigKey;
	protected $mModuleKey;

	abstract protected function getModuleType();
	// Check if module is installed (Administration Tool)

	public function __construct() {
		parent::__construct();
		$this->code = get_called_class();
		$this->mConfigKey = $this->getConfigKey();
		$this->mModuleKey = $this->getModuleKey();
		$this->enabled = $this->isEnabled(); // legacy support for old plugins
		$this->check = $this->isInstalled(); // legacy support for old plugins
		$this->title = $this->getTitle();
	}

	public function isUserEnabled( $pUserId=NULL ) {
		if( $ret = $this->isEnabled() ) {
			if( $poPerm = $this->getCommerceConfig( $this->getPermissionKey() ) ) {
				global $gBitUser;
				if( BitBase::verifyId( $pUserId ) && $checkUser = BitUser::getUserObject( $uid ) ) {
				} else {
					$checkUser = &$gBitUser;
				}
				$ret = $checkUser->hasPermission( $poPerm );
			}
		}
		return $ret;
	}

	public function getAdminTitle() {
		return (!empty( $this->adminTitle ) ? $this->adminTitle : (!empty( $this->title) ? $this->title: $this->code));
	}

	public function getCustomerTitle() {
		return $title;
	}

	protected function getModuleKey() {
		return strtoupper( $this->getModuleType() );
	}

	protected function getConfigKey() {
		return strtoupper( $this->code );
	}

	protected function getModuleKeyTrunk() {
		return 'MODULE_'.$this->mModuleKey.'_'.$this->mConfigKey;
	}

	protected function getTitle( $pDefault = '' ) {
		if( !empty( $pDefault ) ) {
			$ret = $pDefault;
		} elseif( $ret = $this->getModuleConfigValue( '_TITLE' ) ) {
		} else {
	 		$ret = ucwords( str_replace( '_', ' ', get_class( $this ) ) );
		}
		return tra( $ret );
	}

	protected function getTitleKey() {
		return $this->getModuleKeyTrunk().'_TITLE';
	}

	protected function getStatusKey() {
		return $this->getModuleKeyTrunk().'_STATUS';
	}

	protected function getPermissionKey() {
		return $this->getModuleKeyTrunk().'_PERMISSION';
	}

	protected function getSortOrderKey() {
		return $this->getModuleKeyTrunk().'_SORT_ORDER';
	}

	public function keys() {
		return array_keys( $this->config() );
	}

	public function install() {
		if( !$this->isInstalled() ) {
			$this->mDb->StartTrans();
			$this->remove(); // clean out any existing module keys
			if( $configTables = $this->configTables() ) {
				$dict = NewDataDictionary( $this->mDb->mDb );
				foreach( $configTables as $tableName=>$tableDict ) {
					if( $sqlArray = $dict->CreateTableSQL( $tableName, $tableDict, 'REPLACE' ) ) {
						foreach( $sqlArray as $sql ) {
							$this->mDb->query( $sql );
						}
					} else {
						eb( "Cannot create Table: ". $tableName, $tables );
					}
				}
			}
			foreach( $this->config() as $configKey => $configHash ) {
				$this->storeModuleConfigHash( $configKey, $configHash );
			}
			// Backward compatibility with older modules
			$this->mDb->CompleteTrans();
		}
	}

	public function remove() {
		if( $this->isInstalled() ) {
			$this->mDb->StartTrans();	
			
			if( $configTables = $this->configTables() ) {
				foreach( $configTables as $tableName=>$tableDict ) {
					$this->mDb->Execute( "DROP TABLE ".$tableName );
				}
			}

			$this->mDb->CompleteTrans();
		}
		$this->mDb->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` LIKE '".$this->getModuleKeyTrunk()."\_%'");
	}

	protected function storeModuleConfigHash( $pModuleConfigKey, $pModuleConfigHash ) {
		// set some defaults if not set
		foreach( array( 'configuration_key' => $pModuleConfigKey, 'sort_order' => 0, 'date_added' => 'now()', 'configuration_group_id' => '6' ) as $key => $defaultValue ) {
			if( empty( $pModuleConfigHash[$key] ) ) {
				$pModuleConfigHash[$key] = $defaultValue;
			}
		}
		$this->mDb->AssociateInsert( TABLE_CONFIGURATION, $pModuleConfigHash );
	}

	protected function getModuleConfigValue( $pConfigKeyBranch, $pDefaultValue=NULL ) {
		return $this->getCommerceConfig( $this->getModuleKeyTrunk().$pConfigKeyBranch, $pDefaultValue );
	}

	protected function isModuleConfigActive( $pConfigKeyBranch ) {
		$ret = FALSE;
		if( $configValue = $this->getModuleConfigValue( $pConfigKeyBranch ) ) {
			$ret = ($configValue == 'true');
		}
		return $ret;
	}

	protected function storeModuleConfigValue( $pConfigKeyBranch, $pConfigValue ) {
		$this->storeCommerceConfig( $this->getModuleKeyTrunk().$pConfigKeyBranch, $pConfigValue );
	}

	public function getDefaultConfig() {
		return $this->config();
	}

	public function getActiveConfig() {
		return $this->mDb->getAssoc( 'SELECT `configuration_key`, * FROM ' . TABLE_CONFIGURATION . ' WHERE `configuration_key` LIKE ?', array( $this->getModuleKeyTrunk().'\_%' ) );
	}

	// Default methods that should be overridden in derived classes
	protected function getSessionVars() {
		return array();
	}

	public function clearSessionDetails() {
		foreach( $this->getSessionVars() as $var ) {
			// $_SESSION[$var] = $this->$var; WTF, makes no sense
			if( isset( $_SESSION[$var] ) ) {
				unset( $_SESSION[$var] );
			}
		}	
	}

	public function isEnabled() {
		if( !isset( $this->isEnabled ) ) {
			$this->isEnabled = $this->isCommerceConfigActive( $this->getStatusKey() );
		}
		return $this->isEnabled;
	}

	public function check() {
		return $this->isInstalled();
	}

	public function getSortOrder() {
		if( empty( $this->sort_order ) ) {
			if( $this->isInstalled() ) {
				$this->sort_order = $this->getCommerceConfig( $this->getSortOrderKey() );
			}
		}
		return $this->sort_order;
	}

	public function isInstalled() {
		if( !isset( $this->isInstalled ) ) {
			$this->isInstalled= $this->isCommerceConfigLoaded( $this->getStatusKey() );
		}
		$this->check = $this->isInstalled; // legacy variable
		return $this->isInstalled;
	}

	public function fixConfig() {
		if( $this->isInstalled() ) {
			$this->mDb->StartTrans();
			$activeKeys = array_keys( $this->getActiveConfig() );
			$defaultConfig = $this->getDefaultConfig();
			$defaultKeys = array_keys( $defaultConfig );

			if( $missingConfigKeys = array_flip( array_diff( $defaultKeys, $activeKeys ) ) ) {
				foreach( $defaultConfig as $configKey => $configHash ) {
					if( isset( $missingConfigKeys[$configKey] ) ) {
						$this->storeModuleConfigHash( $configKey, $configHash );
					}
				}
			}
			if( $unusedConfigKeys = array_flip( array_diff( $activeKeys, $defaultKeys ) ) ) {
				foreach( $unusedConfigKeys as $configKey=>$configValue ) {
					$this->storeCommerceConfig( $configKey, NULL );
				}
			}
			$this->mDb->CompleteTrans();
		}
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 1;
		return array( 
			$this->getTitleKey() => array(
				'configuration_title' => 'Title',
				'configuration_description' => 'Title of module display to customer.',
				'sort_order' => $i++,
			),
			$this->getStatusKey() => array(
				'configuration_title' => 'Enable '.$this->title,
				'configuration_value' => 'True',
				'sort_order' => $i++,
				'configuration_description' => 'Do you want '.$this->getAdminTitle().' '.$this->getModuleType().' active?',
				'set_function' => "zen_cfg_select_option(array('True', 'False'), ",
			),
			$this->getSortOrderKey() => array(
				'configuration_title' => 'Sort Order',
				'configuration_description' => 'Sort order of display.',
				'sort_order' => $i++,
			),
			$this->getPermissionKey() => array(
				'configuration_title' => ucwords( $this->code ).' Permission',
				'configuration_description' => '<strong>Ex: p_bitcommerce_special_customer</strong> This permission is required to show and accept '.$this->code.' '.$this->getModuleType().'. Leave empty to give access to all customers. To limit use, make sure you have a <a href="/users/admin/edit_group.php">Group configured</a> with that permission, and desired users are also in that group.',
				'configuration_value' => '',
				'sort_order' => $i++,
			)
		);
	}

	/**
    * Any custom tables needed for this module. Should be overridden
    */
	protected function configTables() {
		return array();
	}

}


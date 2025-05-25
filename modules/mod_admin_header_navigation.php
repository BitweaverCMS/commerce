<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce										  |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers							  |
// |																	  |
// | http://www.zen-cart.com/index.php									  |
// |																	  |
// | Portions Copyright (c) 2003 osCommerce								  |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,		  |
// | that is bundled with this package in the file LICENSE, and is		  |
// | available through the world-wide-web at the following url:			  |
// | http://www.zen-cart.com/license/2_0.txt.							  |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to		  |
// | license@zen-cart.com so we can mail you a copy immediately.		  |
// +----------------------------------------------------------------------+
// $Id$
//
global $gBitDb, $gBitProduct, $currencies;

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );

if( empty( $moduleTitle ) ) {
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( tra( 'Admin Navigation' ) );
}

// ### CONFIGRUATION MENU
global $gBitDb;
$heading = array();
$contents = array();
if( $configHash = $gBitDb->getAssoc( "SELECT `configuration_group_id` as `cg_id`, `configuration_group_title` as `cg_title` from " . TABLE_CONFIGURATION_GROUP . " where `visible` = '1' order by `sort_order`" ) ) {
	$_template->tpl_vars['configMenu'] = new Smarty_variable( $configHash );
}

// #### MODULES Menu

$dir = opendir( DIR_FS_MODULES );
global $gBitUser;
$moduleMenu = array();
while( $file = readdir( $dir ) ) {
	if( is_dir( DIR_FS_MODULES.$file ) && $file[0] != '.' && (($file != 'payment' && $file != 'fulfillment' && $file != 'shipping') || $gBitUser->hasPermission( 'p_bitcommerce_root' )) ) {
		$subdir = opendir( DIR_FS_MODULES.$file );
		while( $subfile = readdir( $subdir ) ) {
			$moduleName = basename( $subfile, '.php' );
			if( $subfile[0] != '.' ) {
				$moduleMenu[$file][] = array( 'module_name' => $moduleName, 'menu_title' => tra( ucwords( str_replace( '_', ' ', $moduleName ) ) ) );
			}
		}
	}
}
$_template->tpl_vars['moduleMenu'] = new Smarty_variable( $moduleMenu );

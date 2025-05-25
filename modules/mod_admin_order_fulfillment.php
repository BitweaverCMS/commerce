<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 * Copyright (c) 2025 bitweaver.org, All Rights Reserved
 * This source file is subject to the 2.0 GNU GENERAL PUBLIC LICENSE. 
 *
 * 
 *
 */

global $gBitDb, $gBitProduct, $currencies;

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );

if( empty( $moduleTitle ) ) {
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( tra( 'Order Download' ) );
}



global $gBitUser;
// only super admin's can monkey with 
if( $gBitUser->hasPermission( 'p_admin' ) ) {
	// scan fulfillment modules
	$fulfillmentFiles = array();
	$fulfillDir = DIR_FS_MODULES . 'fulfillment/';
	if( is_readable( $fulfillDir ) && $fulfillHandle = opendir( $fulfillDir ) ) {
		while( $ffFile = readdir( $fulfillHandle ) ) {
			if( is_file( $fulfillDir.$ffFile.'/admin_order_inc.php' ) ) {
				$fulfillmentFiles[] = $fulfillDir.$ffFile.'/admin_order_inc.php';
			}
		}
	}
}

if( !empty( $fulfillmentFiles ) ) {
?>
<ul class="nav nav-tabs" data-tab="tab" id="mbRzQ6D3nJ">
<?php
	foreach( $fulfillmentFiles as $fulfillmentFile )  {
		foreach( $fulfillmentFiles as $fulfillmentFile )  {
			include $fulfillmentFile;
		}
	}
?>
</ul>
<?php
}

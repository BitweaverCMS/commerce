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



<?php
//
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id$
//
global $gBitDb, $gCommerceSystem, $gBitProduct, $currencies, $gBitUser, $gBitCustomer;

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );
if( !empty( $gBitCustomer->mCart ) && is_object( $gBitCustomer->mCart ) && $gBitCustomer->mCart->count_contents() > 0 ) {
	$_template->tpl_vars['sessionCart'] = new Smarty_variable( $gBitCustomer->mCart );
}

?>

<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//
global $gBitDb, $gBitProduct;
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );

$record_company_query = "select record_company_id, record_company_name
		from " . TABLE_RECORD_COMPANY . "
		order by `record_company_name`";

$record_company = $gBitDb->Execute($record_company_query);

if ($record_company->RecordCount()>0) {
	$number_of_rows = $record_company->RecordCount()+1;
}

// Display a list
$record_company_array = array();
if ( !isset($_GET['record_company_id']) or
		$_GET['record_company_id'] == '' ) {
	$record_company_array[] = tra( 'Please Select' );
} else {
	$record_company_array[] = tra( '- Reset -' );
}

while (!$record_company->EOF) {
	$record_company_name = ((strlen($record_company->fields['record_company_name']) > MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) ? substr($record_company->fields['record_company_name'], 0, MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) . '..' : $record_company->fields['record_company_name']);
	$record_company_array[$record_company->fields['record_company_id']] = $record_company_name;
	$record_company->MoveNext();
}

$gBitSmarty->assign( 'record_company', $record_company_array );

if( empty( $moduleTitle ) ) {
	$gBitSmarty->assign( 'moduleTitle', tra( 'Record Companies' ) );
}
?>

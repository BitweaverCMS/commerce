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
require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );

// test if manufacturers sidebox should show
$show_manufacturers= true;

// for large lists of manufacturers uncomment this section
/*
if (($_GET['main_page']==FILENAME_DEFAULT and ($_GET['cPath'] == '' or $_GET['cPath'] == 0)) or  ($_SERVER['HTTPS'] == 'on')) {
$show_manufacturers= false;
} else {
$show_manufacturers= true;
}
*/

if ($show_manufacturers) {

	// only check products if requested - this may slow down the processing of the manufacturers sidebox
	if (PRODUCTS_MANUFACTURERS_STATUS == '1') {
		$manufacturer_sidebox_query = "select distinct m.`manufacturers_id`, m.`manufacturers_name`
							from " . TABLE_MANUFACTURERS . " m
							left join " . TABLE_PRODUCTS . " p on m.`manufacturers_id` = p.`manufacturers_id`
							where m.`manufacturers_id` = p.`manufacturers_id` and p.`products_status`= '1'
							order by `manufacturers_name`";
	} else {
		$manufacturer_sidebox_query = "select m.`manufacturers_id`, m.`manufacturers_name`
							from " . TABLE_MANUFACTURERS . " m
							order by `manufacturers_name`";
	}

	if( $manufacturers = $gBitDb->getAssoc( $manufacturer_sidebox_query ) ) {
		$number_of_rows = $manufacturers->RecordCount()+1;

		// Display a list
		$manufacturer_sidebox_array = array( '' => 'Please Select') ;

		while( $manufacturer_sidebox = $manufacturers->fetchRow() ) {
			$manufacturer_sidebox_name = ((strlen($manufacturer_sidebox['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturer_sidebox['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $manufacturer_sidebox['manufacturers_name']);
			$manufacturer_sidebox_array[$manufacturer_sidebox['manufacturers_id']] = $manufacturer_sidebox_name;
		}
		//$_template->tpl_vars['manufacturersPulldown'] = new Smarty_variable( zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, (isset($_GET['manufacturers_id']) ) );
		$_template->tpl_vars['manufacturers'] = new Smarty_variable( $manufacturer_sidebox_array );
		if( empty( $moduleTitle ) ) {
			$_template->tpl_vars['moduleTitle'] = new Smarty_variable(  'Manufacturers' );
		}
	}
} 
?>

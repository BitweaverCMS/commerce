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
// $Id: mod_record_companies.php,v 1.1 2005/07/30 15:08:15 spiderr Exp $
//
	global $db, $gBitProduct;

  $record_company_query = "select record_company_id, record_company_name
                          from " . TABLE_RECORD_COMPANY . "
                          order by record_company_name";

  $record_company = $db->Execute($record_company_query);

  if ($record_company->RecordCount()>0) {
    $number_of_rows = $record_company->RecordCount()+1;

// Display a list
    $record_company_array = array();
    if ($_GET['record_company_id'] == '' ) {
      $record_company_array[] = array('id' => '', 'text' => PULL_DOWN_ALL);
    } else {
      $record_company_array[] = array('id' => '', 'text' => PULL_DOWN_RECORD_COMPANIES);
    }

    while (!$record_company->EOF) {
      $record_company_name = ((strlen($record_company->fields['record_company_name']) > MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) ? substr($record_company->fields['record_company_name'], 0, MAX_DISPLAY_RECORD_COMPANY_NAME_LEN) . '..' : $record_company->fields['record_company_name']);
      $record_company_array[] = array('id' => $record_company->fields['record_company_id'],
                                       'text' => $record_company_name);

      $record_company->MoveNext();
    }
  //	require($template->get_template_dir('tpl_record_company_select.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_record_company_select.php');

    $title =  BOX_HEADING_RECORD_COMPANY;
    $left_corner = false;
    $right_corner = false;
    $right_arrow = false;
    $title_link = false;
//	require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle', tra( 'Record Companies' ) );
	}
?>

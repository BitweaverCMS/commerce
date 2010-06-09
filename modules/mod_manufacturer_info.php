<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
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

  if (isset($_GET['products_id'])) {
    $manufacturer_info_sidebox_query = "select m.`manufacturers_id`, m.`manufacturers_name`, m.`manufacturers_image`,
                                  mi.`manufacturers_url`
                           from " . TABLE_MANUFACTURERS . " m
                           left join " . TABLE_MANUFACTURERS_INFO . " mi
                           on (m.`manufacturers_id` = mi.`manufacturers_id`
                           and mi.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'), " . TABLE_PRODUCTS . " p
                           where p.`products_id` = '" . (int)$_GET['products_id'] . "'
                           and p.`manufacturers_id` = m.`manufacturers_id`";

    $manufacturer_info_sidebox = $gBitDb->Execute($manufacturer_info_sidebox_query);

	if ($manufacturer_info_sidebox->RecordCount() > 0) {
		$sideboxManufacturerInfo = $manufacturer_info_sidebox->fields;
		if (zen_not_null($manufacturer->fields['manufacturers_image'])) {
			$sideboxManufacturerInfo['manufacturers_image_url'] .= DIR_WS_IMAGES . $manufacturer->fields['manufacturers_image'];
		}
		$gBitSmarty->assign( 'sideboxManufacturerInfo', $sideboxManufacturerInfo );

	//	require($template->get_template_dir('tpl_manufacturer_info.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_manufacturer_info.php');
		$title =  BOX_HEADING_MANUFACTURER_INFO;
		$left_corner = false;
		$right_corner = false;
		$right_arrow = false;
		$title_link = false;
	//	require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
		}
	}
?>

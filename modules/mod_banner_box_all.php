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
// $Id: mod_banner_box_all.php,v 1.1 2005/07/30 15:08:15 spiderr Exp $
//
	global $db, $gBitProduct;

// INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Banner Display Group - Side Box banner_box_all', 'SHOW_BANNERS_GROUP_SET_ALL', 'BannersAll', 'The Banner Display Group may only be from one (1) Banner Group for the Banner All sidebox<br /><br />Default Group is BannersAll<br /><br />What Banner Group do you want to use in the Side Box - banner_box_all?<br />Leave blank for none', '19', '72', '', '', now());
// ALTER TABLE `banners` ADD `banners_sort_order` INT( 11 ) DEFAULT '0' NOT NULL;

	$banner_box[] = TEXT_BANNER_BOX_ALL;

	$banner_box_group= SHOW_BANNERS_GROUP_SET_ALL;

	//	require($template->get_template_dir('tpl_banner_box_all.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_banner_box_all.php');
	$new_banner_search = zen_build_banners_group(SHOW_BANNERS_GROUP_SET_ALL);

	// secure pages
	$my_page_ssl = $_SERVER['HTTPS'];
	switch (true) {
		case ($my_page_ssl=='on'):
			$my_banner_filter=" and banners_on_ssl= " . "'1' ";
			break;
		case ($my_page_ssl=='off' ):
			$my_banner_filter='';
			break;
	}

	$sql = "select banners_id from " . TABLE_BANNERS . " where status = '1' " . $new_banner_search . $my_banner_filter . " order by banners_sort_order";
	$banners_all = $db->Execute($sql);

	$sideboxBannersAll = array();
	// if no active banner in the specified banner group then the box will not show
	// uses banners in the defined group $banner_box_group
	while( !$banners_all->EOF ) {
		$banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET_ALL);
		array_push( $sideboxBannersAll, zen_display_banner('static', $banners_all->fields['banners_id']) );
	}

	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle', tra( 'Sponsors' ) );
	}
?>

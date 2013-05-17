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
global $gBitDb, $gCommerceSystem, $gBitProduct, $banner;

require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'banner.php');
require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'html_output.php');

$banner_box_group= SHOW_BANNERS_GROUP_SET7;

if( $bannerRs = zen_banner_exists('dynamic', $banner_box_group) ) {
	$_template->tpl_vars['bannerContent'] = new Smarty_variable( zen_display_banner('static', $bannerRs ) );
}
if( empty( $moduleTitle ) ) {
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable(  'Sponsors' );
}

?>

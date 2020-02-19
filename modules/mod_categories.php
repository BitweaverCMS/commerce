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
global $gBitDb, $gCommerceSystem, $gBitProduct;

require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
$main_category_tree = new category_tree;
$row = 0;
$box_categories_array = array();

// don't build a tree when no categories
if ( $gBitDb->getOne("select `categories_id` from " . TABLE_CATEGORIES . " where `categories_status` = 1") ) {
	$_template->tpl_vars['box_categories_array'] = new Smarty_variable( $main_category_tree->zen_category_tree() );
}
if( empty( $moduleTitle ) ) {
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( 'Categories' );
}

//	require($gCommerceSystem->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
?>

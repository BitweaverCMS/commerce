<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce										|
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers							|
// |																	|
// | http://www.zen-cart.com/index.php									|
// |																	|
// | Portions Copyright (c) 2003 osCommerce								|
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,		|
// | that is bundled with this package in the file LICENSE, and is		|
// | available through the world-wide-web at the following url:			|
// | http://www.zen-cart.com/license/2_0.txt.							|
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to		|
// | license@zen-cart.com so we can mail you a copy immediately.		|
// +----------------------------------------------------------------------+
//	$Id$
//
define('MAX_DISPLAY_RESTRICT_ENTRIES', 5);
require('includes/application_top.php');
$restrict_array = array();
$restrict_array[] = array('id'=>'Deny', 'text'=>'Deny');
$restrict_array[] = array('id'=>'Allow', 'text'=>'Allow');

if( !empty( $_REQUEST['cid'] ) ) {
	$gCoupon = new CommerceVoucher( $_REQUEST['cid'] );
	$gCoupon->load( NULL, FALSE );
} else {
	$gCoupon = new CommerceVoucher();
}

if( !empty( $_REQUEST['action'] ) && $gCoupon->isValid() ) { 
	switch( $_REQUEST['action'] ) {
		case 'switch_status':
			$status = $gBitDb->getOne( "SELECT coupon_restrict FROM " . TABLE_COUPON_RESTRICT . " WHERE restrict_id = ?", array( $_GET['info'] ) );
			$new_status = ($status == 'N') ? 'Y' : 'N'; 
			$gBitDb->query( "UPDATE " . TABLE_COUPON_RESTRICT . " SET coupon_restrict = ? WHERE restrict_id = ?", array( $new_status, $_GET['info'] ) );
	bit_redirect( $_SERVER['SCRIPT_NAME'].'?cid='.$gCoupon->mCouponId );
			break;
		case 'Add':
			$gCoupon->storeRestriction( $_REQUEST );
	bit_redirect( $_SERVER['SCRIPT_NAME'].'?cid='.$gCoupon->mCouponId );
			break;
		case 'remove':
			if( !empty( $_GET['info'] ) ) {
				$gBitDb->query("delete from " . TABLE_COUPON_RESTRICT . " where restrict_id = ?", array( $_GET['info'] ) );
			}
	bit_redirect( $_SERVER['SCRIPT_NAME'].'?cid='.$gCoupon->mCouponId );
			break;
	}
}

if(isset($_POST['cPath_prod'])) {
	$current_category_id = $_POST['cPath_prod'];
} else {
	$_POST['cPath_prod'] = NULL;
}

$productsList = $gBitDb->getAssoc("select p.`products_id`, pd.`products_name` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.`products_id` = pd.`products_id` and pd.`language_id` = ? and p.`products_id` = p2c.`products_id` and p2c.`categories_id` = ? order by pd.`products_name`", array( $_SESSION['languages_id'], $_POST['cPath_prod'] ) );

$gBitSmarty->assign_by_ref( 'productsList', $productsList );
$gBitSmarty->assign_by_ref( 'gCoupon', $gCoupon );

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceProductManager.php' );
$productManager = new CommerceProductManager();
$gBitSmarty->assign_by_ref( 'optionsList', $productManager->getOptionsList() );

$categoryTree = zen_get_category_tree();
$gBitSmarty->assign( 'categorySelect', zen_draw_pull_down_menu('category_id', $categoryTree, $current_category_id) );
$gBitSmarty->assign( 'productCategorySelect',  zen_draw_pull_down_menu('cPath_prod', $categoryTree, $current_category_id, 'onChange="this.form.submit();"') );
$gBitSmarty->assign( 'productTypes', $productManager->getProductTypes() );
$gBitSmarty->assign_by_ref( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:bitcommerce/admin_coupon_restrict.tpl', HEADING_TITLE, array( 'display_mode' => 'admin' ));



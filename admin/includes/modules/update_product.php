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
//  $Id: update_product.php,v 1.10 2008/07/13 16:42:03 lsces Exp $
//
	if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
		$action = 'new_product';
	} else {
		$_REQUEST['category_id'] = $current_category_id;
		$newProduct = new CommerceProduct();
		// update with a full path so the image is copied to the proper place - if not already loaded
		if( !empty( $_REQUEST['products_image'] ) && !is_numeric( $_REQUEST['products_image'] ) ) {
			$_REQUEST['products_image'] = STORAGE_PKG_PATH.BITCOMMERCE_PKG_NAME.'/images/'.$_REQUEST['products_image'];
		}
		if( !empty( $_REQUEST['products_image_att'] ) && is_numeric( $_REQUEST['products_image_att'] ) ) {
			$_REQUEST['products_image'] = $_REQUEST['products_image_att'];
		}

		$newProduct->store( $_REQUEST );

		zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $newProduct->mProductsId . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
	}
?>

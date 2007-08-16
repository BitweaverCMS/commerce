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
//  $Id: new_product_preview.php,v 1.4 2007/08/16 09:19:11 lsces Exp $

// copy image only if modified
	if( empty( $_REQUEST['read'] ) ) {
		$products_image = new upload('products_image');
		$products_image->set_destination(DIR_FS_CATALOG_IMAGES);
		if ($products_image->parse() && $products_image->save( isset($_POST['overwrite']) ? $_POST['overwrite'] : true ) ) {
			$products_image_name = $products_image->filename;
		} else {
			$products_image_name = (isset($_REQUEST['products_previous_image']) ? $_REQUEST['products_previous_image'] : '');
		}
	}
?>

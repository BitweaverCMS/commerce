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
//  $Id: update_product.php,v 1.3 2005/07/08 05:56:41 spiderr Exp $
//
	if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
		$action = 'new_product';
	} else {
		$_REQUEST['category_id'] = $current_category_id;
		$newProduct = new CommerceProduct();
		$newProduct->store( $_REQUEST );
		zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $newProduct->mProductId . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
	}
?>

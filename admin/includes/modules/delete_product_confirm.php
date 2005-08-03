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
//  $Id: delete_product_confirm.php,v 1.2 2005/08/03 15:35:12 spiderr Exp $
//
//
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
        }
        if (isset($_POST['products_id']) && isset($_POST['product_categories']) && is_array($_POST['product_categories'])) {
          $product_id = zen_db_prepare_input($_POST['products_id']);
          $product_categories = $_POST['product_categories'];

          for ($i=0, $n=sizeof($product_categories); $i<$n; $i++) {
            $db->Execute("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                          where products_id = '" . (int)$product_id . "'
                          and categories_id = '" . (int)$product_categories[$i] . "'");
          }

          $product_categories = $db->Execute("select count(*) as total
                                              from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                              where products_id = '" . (int)$product_id . "'");

          if ($product_categories->fields['total'] == '0') {
            zen_remove_product($product_id);
          }
        }

        zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath));
?>
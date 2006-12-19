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
//  $Id: move_product_confirm.php,v 1.5 2006/12/19 00:11:30 spiderr Exp $
//

        $products_id = zen_db_prepare_input($_POST['products_id']);
        $new_parent_id = zen_db_prepare_input($_POST['move_to_category_id']);

        $duplicate_check = $gBitDb->Execute("select count(*) as `total`
                                        from " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                        where `products_id` = '" . (int)$products_id . "'
                                        and `categories_id` = '" . (int)$new_parent_id . "'");

        if ($duplicate_check->fields['total'] < 1) {
          $gBitDb->Execute("update " . TABLE_PRODUCTS_TO_CATEGORIES . "
                        set `categories_id` = '" . (int)$new_parent_id . "'
                        where `products_id` = '" . (int)$products_id . "'
                        and `categories_id` = '" . (int)$current_category_id . "'");

          // reset master_categories_id if moved from original master category
          $check_master = $gBitDb->Execute("select `products_id`, `master_categories_id` from " . TABLE_PRODUCTS . " where `products_id` ='" .  (int)$products_id . "'");
          if ($check_master->fields['master_categories_id'] == (int)$current_category_id) {
            $gBitDb->Execute("update " . TABLE_PRODUCTS . "
                          set `master_categories_id`='" . (int)$new_parent_id . "'
                          where `products_id` = '" . (int)$products_id . "'");
          }

          // reset products_price_sorter for searches etc.
          zen_update_products_price_sorter((int)$products_id);
        } else {
          $messageStack->add_session(ERROR_CANNOT_MOVE_PRODUCT_TO_CATEGORY_SELF, 'error');
        }

        zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
?>
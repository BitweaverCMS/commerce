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
// $Id: header_php.php,v 1.2 2005/07/08 06:13:01 spiderr Exp $
//

// the following cPath references come from application_top.php
  $category_depth = 'top';
  if (isset($cPath) && zen_not_null($cPath)) {
    $categories_products_query = "select count(*) as total
                                  from   " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                  where   categories_id = '" . (int)$current_category_id . "'";

    $categories_products = $db->Execute($categories_products_query);

    if ($categories_products->fields['total'] > 0) {
      $category_depth = 'products'; // display products
    } else {
      $category_parent_query = "select count(*) as total
                                from   " . TABLE_CATEGORIES . "
                                where  parent_id = '" . (int)$current_category_id . "'";

      $category_parent = $db->Execute($category_parent_query);

      if ($category_parent->fields['total'] > 0) {
        $category_depth = 'nested'; // navigate through the categories
      } else {
        $category_depth = 'products'; // category has no products, but display the 'no products' message
      }
    }
  }
// include template specific file name defines
  $define_main_page = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitLanguage->getLanguage() . '/html_includes/', FILENAME_DEFINE_MAIN_PAGE, 'false');
  require(DIR_WS_MODULES . 'require_languages.php');
?>

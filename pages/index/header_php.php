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
// $Id: header_php.php,v 1.6 2005/11/26 15:03:02 spiderr Exp $
//

// the following cPath references come from application_top.php
  $category_depth = 'top';
  if (isset($cPath) && zen_not_null($cPath)) {
    if ( $gComCategory->countProductsInCategory( $current_category_id ) ) {
      $category_depth = 'products'; // display products
    } else {
      if ( $gComCategory->countParentCategories( $current_category_id ) ) {
        $category_depth = 'nested'; // navigate through the categories
      } else {
        $category_depth = 'products'; // category has no products, but display the 'no products' message
      }
    }
  }
// include template specific file name defines
  $define_main_page = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/html_includes/', FILENAME_DEFINE_MAIN_PAGE, 'false');
  require(DIR_FS_MODULES . 'require_languages.php');
?>

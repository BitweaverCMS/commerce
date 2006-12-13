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
// $Id: upcoming_products.php,v 1.9 2006/12/13 19:33:44 lsces Exp $
//
	global $db;

  if ( (!isset($new_products_category_id)) || ($new_products_category_id == '0') ) {
    $expected_query = "select p.`products_id`, pd.`products_name`, `products_date_available` as `date_expected`
                       from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                       where `products_date_available` >= 'NOW'
                       and p.`products_id` = pd.`products_id`
                       and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                       order by `products_date_available` " . EXPECTED_PRODUCTS_SORT;
 //                      order by `" . EXPECTED_PRODUCTS_FIELD . "` " . EXPECTED_PRODUCTS_SORT;
 //                       where to_days(`products_date_available`) >= to_days(now())
  } else {
    $expected_query = "select p.`products_id`, pd.`products_name`, `products_date_available` as `date_expected`
                       from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
                              TABLE_CATEGORIES . " c
                       where p.`products_id` = p2c.`products_id`
                       and p2c.`categories_id` = c.`categories_id`
                       and c.`parent_id` = '" . (int)$new_products_category_id . "'
                       and `products_date_available` >= 'NOW'
                       and p.`products_id` = pd.`products_id`
                       and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                      order by `products_date_available` " . EXPECTED_PRODUCTS_SORT;
//                       order by `" . EXPECTED_PRODUCTS_FIELD . "` " . EXPECTED_PRODUCTS_SORT;
  }

  $expected = $db->query($expected_query, NULL, MAX_DISPLAY_UPCOMING_PRODUCTS);

  if ($expected->RecordCount() > 0) {
    require( DIR_FS_MODULES . 'tpl_modules_upcoming_products.php');
  }
?>

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
// $Id: upcoming_products.php,v 1.1 2005/07/05 05:59:09 bitweaver Exp $
//

  if ( (!isset($new_products_category_id)) || ($new_products_category_id == '0') ) {
    $expected_query = "select p.products_id, pd.products_name, products_date_available as date_expected
                       from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                       where to_days(products_date_available) >= to_days(now())
                       and p.products_id = pd.products_id
                       and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                       order by " . EXPECTED_PRODUCTS_FIELD . " " . EXPECTED_PRODUCTS_SORT . "
                       limit " . MAX_DISPLAY_UPCOMING_PRODUCTS;
  } else {
    $expected_query = "select p.products_id, pd.products_name, products_date_available as date_expected
                       from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
                              TABLE_CATEGORIES . " c
                       where p.products_id = p2c.products_id
                       and p2c.categories_id = c.categories_id
                       and c.parent_id = '" . (int)$new_products_category_id . "'
                       and to_days(products_date_available) >= to_days(now())
                       and p.products_id = pd.products_id
                       and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                       order by " . EXPECTED_PRODUCTS_FIELD . " " . EXPECTED_PRODUCTS_SORT . "
                       limit " . MAX_DISPLAY_UPCOMING_PRODUCTS;
  }

  $expected = $db->Execute($expected_query);

  if ($expected->RecordCount() > 0) {
    require($template->get_template_dir('tpl_modules_upcoming_products.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_upcoming_products.php');
  }
?>
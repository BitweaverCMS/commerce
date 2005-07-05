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
// $Id: categories_tabs.php,v 1.1 2005/07/05 05:59:09 bitweaver Exp $
//

  $order_by = " order by c.sort_order, cd.categories_name ";

  $categories_tab_query = "select c.categories_id, cd.categories_name from " .
                          TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                          where c.categories_id=cd.categories_id and c.parent_id= '0' and cd.language_id='" . $_SESSION['languages_id'] . "' and c.categories_status='1'" .
                          $order_by;
  $categories_tab = $db->Execute($categories_tab_query);


  while (!$categories_tab->EOF) {

    // currently selected category
    if ((int)$cPath == $categories_tab->fields['categories_id']) {
      $new_style = 'category-top';
      $categories_tab_current = '<span class="category-subs-selected">' . $categories_tab->fields['categories_name'] . '</span>';
    } else {
      $new_style = 'category-top';
      $categories_tab_current = $categories_tab->fields['categories_name'];
    }

    // create link to top level category
    $link = '<a class="' . $new_style . '" href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . (int)$categories_tab->fields['categories_id']) . '">' . $categories_tab_current . '</a> ';
    echo $link;
    $categories_tab->MoveNext();
  }
  
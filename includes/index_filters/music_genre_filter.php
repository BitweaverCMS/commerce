<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
// $Id: music_genre_filter.php,v 1.3 2005/08/24 12:17:46 lsces Exp $
//
// show the products of a specified music_genre
    if (isset($_GET['music_genre_id']))
    {
      if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id']))
      {
// We are asked to show only a specific category
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, if(s.status = '1', s.specials_new_products_price, NULL) AS specials_new_products_price, IF(s.status = '1', s.specials_new_products_price, p.`products_price`) as final_price, p.`products_sort_order` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MUSIC_GENRE . " m left join " . TABLE_SPECIALS . " s on pme.`products_id` = s.`products_id` where  m.music_genre_id = '" . (int)$_GET['music_genre_id'] . "' and p.`products_id` = pme.`products_id` and p.`products_status = '1' and pme.music_genre_id = '" . (int)$_GET['music_genre_id'] . "' and pme.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$_GET['filter_id'] . "'";
      } else {
// We show them all
        $listing_sql = "select " . $select_column_list . " pme.`products_id`, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, IF(s.status = '1', s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status = '1', s.specials_new_products_price, p.`products_price`) as final_price, p.`products_sort_order` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme, " . TABLE_MUSIC_GENRE . " m left join " . TABLE_SPECIALS . " s on pme.`products_id` = s.`products_id` where  m.music_genre_id = '" . (int)$_GET['music_genre_id'] . "' and p.`products_id` = pme.`products_id` and p.`products_status = '1' and  pd.`products_id` = pme.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and pme.music_genre_id = '" . (int)$_GET['music_genre_id'] . "'";

      }
    } else {
// show the products in a given categorie
      if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id']))
      {
// We are asked to show only specific catgeory
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.music_genre_id, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, IF(s.status = '1', s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status = '1', s.specials_new_products_price, p.`products_price`) as final_price, p.`products_sort_order` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MUSIC_GENRE . " m, " . TABLE_PRODUCTS_MUSIC_EXTRA . " pme, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` where p.`products_status = '1' and pme.music_genre_id = m.music_genre_id and m.music_genre_id = '" . (int)$_GET['filter_id'] . "' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$current_category_id . "'";
      } else {
// We show them all
       if ($current_categories_id) {
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.music_genre_id, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, IF(s.status = '1', s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status ='1', s.specials_new_products_price, p.`products_price`) as final_price, p.`products_sort_order` from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_MUSIC_GENRE . " m, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme on pme.music_genre_id = m.music_genre_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` where p.`products_status = '1' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$current_category_id . "'";
	} else {
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.music_genre_id, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, IF(s.status = '1', s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status ='1', s.specials_new_products_price, p.`products_price`) as final_price, p.`products_sort_order` from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_MUSIC_GENRE . " m, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme on pme.music_genre_id = m.music_genre_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` where p.`products_status = '1' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'";
	}
      }
    }
// set the default sort order setting from the Admin when not defined by customer
    if (!isset($_GET['sort']) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
      $_GET['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
    }
    $listing_sql = str_replace('m.manufacturers_name', 'm.music_genre_name as manufacturers_name', $listing_sql);

    if ( (!isset($_GET['sort'])) || (!ereg('[1-8][ad]', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) )
    {
      for ($i=0, $n=sizeof($column_list); $i<$n; $i++)
      {
        if ($column_list[$i] == 'PRODUCT_LIST_NAME')
        {
          $_GET['sort'] = $i+1 . 'a';
          $listing_sql .= " order by p.`products_sort_order`, pd.`products_name`";
          break;
        }
      }
// if set to nothing use products_sort_order and PRODUCTS_LIST_NAME is off
      if (PRODUCT_LISTING_DEFAULT_SORT_ORDER == '') {
        $_GET['sort'] = '20a';
      }
    } else {
      $sort_col = substr($_GET['sort'], 0 , 1);
      $sort_order = substr($_GET['sort'], 1);
      $listing_sql .= ' order by ';
      switch ($column_list[$sort_col-1])
      {
        case 'PRODUCT_LIST_MODEL':
          $listing_sql .= "p.`products_model` " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
          break;
        case 'PRODUCT_LIST_NAME':
          $listing_sql .= "pd.`products_name` " . ($sort_order == 'd' ? 'desc' : '');
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $listing_sql .= "m.music_genre_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $listing_sql .= "p.`products_quantity` " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
          break;
        case 'PRODUCT_LIST_IMAGE':
          $listing_sql .= "pd.`products_name`";
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $listing_sql .= "p.`products_weight` " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
          break;
        case 'PRODUCT_LIST_PRICE':
//          $listing_sql .= "final_price " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
          $listing_sql .= "p.`products_price_sorter` " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
          break;
      }
    }
// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0)
    {
      if (isset($_GET['music_genre_id']))
      {
        $filterlist_sql = "select distinct c.`categories_id` as id, cd.`categories_name` as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme  where p.`products_status = '1' and pme.`products_id` = p2c.`products_id` and p2c.`categories_id` = c.`categories_id` and p2c.`categories_id` = cd.`categories_id` and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and pme.music_genre_id = '" . (int)$_GET['music_genre_id'] . "' order by cd.`categories_name`";
      } else {
        $filterlist_sql= "select distinct m.music_genre_id as id, m.music_genre_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCT_MUSIC_EXTRA . " pme, " . TABLE_MUSIC_GENRE . " m where p.`products_status = '1' and pme.music_genre_id = m.music_genre_id and p.`products_id` = p2c.`products_id` and p2c.`categories_id` = '" . (int)$current_category_id . "' order by m.music_genre_name";
      }
      $getoption_set =  false;
      $filterlist = $db->Execute($filterlist_sql);
      if ($filterlist->RecordCount() > 1)
      {
          $do_filter_list = true;
        if (isset($_GET['music_genre_id']))
        {
//die('here');
          $getoption_set =  true;
          $get_option_variable = 'music_genre_id';
          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
        } else {
          $options = array(array('id' => '', 'text' => TEXT_ALL_MUSIC_GENRE));
        }
        while (!$filterlist->EOF) {
          $options[] = array('id' => $filterlist->fields['id'], 'text' => $filterlist->fields['name']);
          $filterlist->MoveNext();
        }
      }
    }

// Get the right image for the top-right
    $image = DIR_WS_TEMPLATE_IMAGES . 'table_background_list.gif';
    if ($current_category_id) {

      $sql = "select categories_image from " . TABLE_CATEGORIES . "
              where  categories_id = '" . (int)$current_category_id . "'";

      $image_name = $db->Execute($sql);
      $image = $image_name->fields['categories_image'];
    }
?>
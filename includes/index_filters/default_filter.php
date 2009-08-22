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
// $Id: default_filter.php,v 1.13 2009/08/22 21:29:04 spiderr Exp $
//
// show the products of a specified manufacturer
	$bindVars = array(); $selectSql = ''; $joinSql = ''; $whereSql = '';
// 	$gBitProduct->getServicesSql( 'content_load_function', $selectSql, $joinSql, $whereSql, $bindVars );
	$gBitProduct->getGatekeeperSql( $selectSql, $joinSql, $whereSql );
    if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '' ) {
      if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
// We are asked to show only a specific category
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, if(s.`status` = '1', s.`specials_new_products_price`, NULL) AS `specials_new_products_price`, IF(s.`status` = '1', s.`specials_new_products_price`, p.`products_price`) as `final_price`, p.`products_sort_order` $selectSql from " . TABLE_PRODUCTS . " p INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc  ON (p.`content_id`=lc.`content_id`) $joinSql, " . TABLE_PRODUCTS_DESCRIPTION . " pd , " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` where p.`products_status` = '1' and p.`manufacturers_id` = m.`manufacturers_id` and m.`manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$_GET['filter_id'] . "'";
      } else {
// We show them all
$listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, IF(s.`status` = '1', s.`specials_new_products_price`, NULL) as `specials_new_products_price`, IF(s.`status` = '1', s.`specials_new_products_price`, p.`products_price`) as `final_price`, p.`products_sort_order` $selectSql from " . TABLE_PRODUCTS . " p INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (p.`content_id`=lc.`content_id`) INNER JOIN " . TABLE_MANUFACTURERS ." m ON(p.`manufacturers_id` = m.`manufacturers_id`) left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` $joinSql, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.`products_status` = '1' and pd.`products_id` = p.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and m.`manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "'";
      }
    } else {
// show the products in a given categorie
      if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
// We are asked to show only specific catgeory
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, IF(s.`status` = '1', s.`specials_new_products_price`, NULL) as specials_new_products_price, IF(s.`status` = '1', s.`specials_new_products_price`, p.`products_price`) as `final_price`, p.`products_sort_order` $selectSql from " . TABLE_PRODUCTS . " p INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (p.`content_id`=lc.`content_id`) $joinSql, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` where p.`products_status` = '1' and p.`manufacturers_id` = m.`manufacturers_id` and m.`manufacturers_id` = '" . (int)$_GET['filter_id'] . "' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$current_category_id . "'";
      } else {
// We show them all
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_tax_class_id`, pd.`products_description`, s.`specials_new_products_price`, p.`products_sort_order` $selectSql
			 from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p
				 INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (p.`content_id`=lc.`content_id`) $joinSql
				 LEFT JOIN " . TABLE_MANUFACTURERS . " m on p.`manufacturers_id` = m.`manufacturers_id`
				 , " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p2c.`products_id` = s.`products_id`
			 where p.`products_status` = '1' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'";
      }
		if( !empty( $current_category_id ) && is_numeric($current_category_id ) ) {
			$listing_sql .= "and p2c.`categories_id` = '".(int)$current_category_id."'";
		}
    }
	if( !empty( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {
		$listing_sql .= " AND lc.user_id = '".(int)$_GET['user_id']."'";
	}
	$listing_sql .= $whereSql;


// set the default sort order setting from the Admin when not defined by customer
    if (!isset($_GET['sort']) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
      $_GET['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
    }

    if ( (!isset($_GET['sort'])) || (!ereg('[1-8][ad]', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) )
    {
      for ($i=0, $n=sizeof($column_list); $i<$n; $i++)
      {
        if ($column_list[$i] == 'PRODUCT_LIST_NAME')
        {
          $_GET['sort'] = $i+1 . 'a';
          $listing_sql .= " order by p.`products_sort_order`, pd.`products_name`";
          break;
        } else {
// sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER ia left blank
// for reverse, descending order use:
//       $listing_sql .= " order by p.`products_sort_order` desc, pd.`products_name`";
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
          $listing_sql .= "m.`manufacturers_name` " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
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
          $listing_sql .= "p.`lowest_purchase_price` " . ($sort_order == 'd' ? 'desc' : '') . ", pd.`products_name`";
          break;
      }
    }
// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0)
    {
      if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '')
      {
        $filterlist_sql = "select distinct c.`categories_id` as `id`, cd.`categories_name` as `name` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.`products_status` = '1' and p.`products_id` = p2c.`products_id` and p2c.`categories_id` = c.`categories_id` and p2c.`categories_id` = cd.`categories_id` and cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p.`manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "' order by cd.`categories_name`";
      } else {
        $filterlist_sql= "select distinct m.`manufacturers_id` as `id`, m.`manufacturers_name` as `name` from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.`products_status` = '1' and p.`manufacturers_id` = m.`manufacturers_id` and p.`products_id` = p2c.`products_id` and p2c.`categories_id` = '" . (int)$current_category_id . "' order by m.`manufacturers_name`";
      }
      $filterlist = $gBitDb->Execute($filterlist_sql);
      if ($filterlist->RecordCount() > 1)
      {
          $do_filter_list = true;
        if (isset($_GET['manufacturers_id']))
        {
          $getoption_set =  true;
          $get_option_variable = 'manufacturers_id';
          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
        } else {
          $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
        }
        while (!$filterlist->EOF) {
          $options[] = array('id' => $filterlist->fields['id'], 'text' => $filterlist->fields['name']);
          $filterlist->MoveNext();
        }
      }
    }

// Get the right image for the top-right
    $image = DIR_WS_TEMPLATE_IMAGES . 'table_background_list.gif';
    if (isset($_GET['manufacturers_id']))
    {
      $sql = "select `manufacturers_image`
                from   " . TABLE_MANUFACTURERS . "
                where      `manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "'";

      $image_name = $gBitDb->Execute($sql);
      $image = $image_name->fields['manufacturers_image'];

    } elseif ($current_category_id) {

      $sql = "select `categories_image` from " . TABLE_CATEGORIES . "
              where  `categories_id` = '" . (int)$current_category_id . "'";

      $image_name = $gBitDb->Execute($sql);
      $image = $image_name->fields['categories_image'];
    }
?>

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
// $Id: blk_advanced_search_result.php,v 1.4 2005/08/24 12:16:41 lsces Exp $
//
// create column list
  $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                       'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                       'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                       'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                       'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                       'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                       'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE);

/*                       ,
                       'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);
*/

  asort($define_list);

  $column_list = array();
  reset($define_list);
  while (list($column, $value) = each($define_list)) {
    if ($value) $column_list[] = $column;
  }

  $select_column_list = '';

  for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
    if (($column_list[$col] == 'PRODUCT_LIST_NAME') || ($column_list[$col] == 'PRODUCT_LIST_PRICE')) {
      continue;
    }

    if (zen_not_null($select_column_list)) {
      $select_column_list .= ', ';
    }

    switch ($column_list[$col]) {
      case 'PRODUCT_LIST_MODEL':
        $select_column_list .= 'p.`products_model`';
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $select_column_list .= 'm.manufacturers_name';
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $select_column_list .= 'p.`products_quantity`';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $select_column_list .= 'p.`products_image`';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $select_column_list .= 'p.`products_weight`';
        break;
    }
  }

  if (zen_not_null($select_column_list)) {
    $select_column_list .= ', ';
  }

//  $select_str = "select distinct " . $select_column_list . " m.`manufacturers_id`, p.`products_id`, pd.`products_name`, p.`products_price`, p.`products_tax_class_id`, IF(s.status = '1', s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status = '1', s.specials_new_products_price, p.`products_price`) as final_price ";
  $select_str = "select " . $select_column_list . " m.`manufacturers_id`, p.`products_id`, pd.`products_name`, p.`products_price`, p.`products_tax_class_id`, p.`products_price_sorter` ";

  if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_GET['pfrom']) && zen_not_null($_GET['pfrom'])) || (isset($_GET['pto']) && zen_not_null($_GET['pto'])))) {
    $select_str .= ", SUM(tr.tax_rate) as tax_rate ";
  }

//  $from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id`, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";
  $from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) ";

  if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_GET['pfrom']) && zen_not_null($_GET['pfrom'])) || (isset($_GET['pto']) && zen_not_null($_GET['pto'])))) {
    if (!$_SESSION['customer_country_id']) {
      $_SESSION['customer_country_id'] = STORE_COUNTRY;
      $_SESSIOn['customer_zone_id'] = STORE_ZONE;
    }
    $from_str .= " left join " . TABLE_TAX_RATES . " tr on p.`products_tax_class_id` = tr.tax_class_id left join " . TABLE_ZONES_TO_GEO_ZONES . " gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = '0' or gz.zone_country_id = '" . $_SESSION['customer_country_id'] . "') and (gz.zone_id is null or gz.zone_id = '0' or gz.zone_id = '" . $_SESSION['customer_zone_id'] . "')";
  }

  $from_str .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON( p.`products_id` = p2c.`products_id` ) LEFT JOIN  " . TABLE_CATEGORIES . " c ON( p2c.`categories_id`=c.`categories_id` ) left join " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd on mtpd.`products_id`= p.`products_id` and mtpd.`language_id` = '" . $_SESSION['languages_id'] . "', " . TABLE_PRODUCTS_DESCRIPTION . " pd ";

  $where_str = " where p.`products_status = '1' and p.`products_id` = pd.`products_id` and pd.`language_id` = '" . $_SESSION['languages_id'] . "' ";

  if (isset($_GET['categories_id']) && zen_not_null($_GET['categories_id'])) {
    if ($_GET['inc_subcat'] == '1') {
      $subcategories_array = array();
      zen_get_subcategories($subcategories_array, $_GET['categories_id']);
      $where_str .= " and p2c.`products_id` = pd.`products_id` and (p2c.`categories_id` = '" . (int)$_GET['categories_id'] . "'";
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $where_str .= " or p2c.`categories_id` = '" . $subcategories_array[$i] . "'";
      }
      $where_str .= ")";
    } else {
      $where_str .= " and pd.`language_id` = '" . $_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$_GET['categories_id'] . "'";
    }
  }

  if (isset($_GET['manufacturers_id']) && zen_not_null($_GET['manufacturers_id'])) {
    $where_str .= " and m.`manufacturers_id` = '" . $_GET['manufacturers_id'] . "'";
  }

  if (isset($_GET['keyword']) && zen_not_null($_GET['keyword'])) {
    if (zen_parse_search_string(stripslashes($_GET['keyword']), $search_keywords)) {
      $where_str .= " and (";
      for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
        switch ($search_keywords[$i]) {
          case '(':
          case ')':
          case 'and':
          case 'or':
            $where_str .= " " . $search_keywords[$i] . " ";
            break;
          default:
            $where_str .= "(lower( pd.`products_name` ) like '%" . addslashes( strtolower( $search_keywords[$i]) ) . "%' or p.`products_model` like '%" . addslashes($search_keywords[$i]) . "%' or m.manufacturers_name like '%" . addslashes($search_keywords[$i]) . "%'";
// search meta tags
            $where_str .= " or (mtpd.metatags_keywords like '%" . addslashes($search_keywords[$i]) . "%' and mtpd.metatags_keywords !='')";
            $where_str .= " or (mtpd.metatags_description like '%" . addslashes($search_keywords[$i]) . "%' and mtpd.metatags_description !='')";
            if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) $where_str .= " or pd.`products_description` like '%" . addslashes($search_keywords[$i]) . "%'";
              $where_str .= ')';
            break;
        }
      }
      $where_str .= " )";
    }
  }

  if (isset($_GET['dfrom']) && zen_not_null($_GET['dfrom']) && ($_GET['dfrom'] != DOB_FORMAT_STRING)) {
    $where_str .= " and p.`products_date_added` >= '" . zen_date_raw($dfrom) . "'";
  }

  if (isset($_GET['dto']) && zen_not_null($_GET['dto']) && ($_GET['dto'] != DOB_FORMAT_STRING)) {
    $where_str .= " and p.`products_date_added` <= '" . zen_date_raw($dto) . "'";
  }

  $rate = $currencies->get_value($_SESSION['currency']);
  if ($rate) {
    $pfrom = $_GET['pfrom'] / $rate;
    $pto = $_GET['pto'] / $rate;
  }

  if (DISPLAY_PRICE_WITH_TAX == 'true') {
//    if ($pfrom) $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) >= " . $pfrom . ")";
//    if ($pto)   $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) <= " . $pto . ")";
    if ($pfrom) $where_str .= " and (p.`products_price_sorter` * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) >= " . $pfrom . ")";
    if ($pto)   $where_str .= " and (p.`products_price_sorter` * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) <= " . $pto . ")";
  } else {
//    if ($pfrom) $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) >= " . $pfrom . ")";
//    if ($pto)   $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) <= " . $pto . ")";
    if ($pfrom) $where_str .= " and (p.`products_price_sorter` >= " . $pfrom . ")";
    if ($pto)   $where_str .= " and (p.`products_price_sorter` <= " . $pto . ")";
  }

  if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_GET['pfrom']) && zen_not_null($_GET['pfrom'])) || (isset($_GET['pto']) && zen_not_null($_GET['pto'])))) {
    $where_str .= " group by p.`products_id`, tr.tax_priority";
  }

// set the default sort order setting from the Admin when not defined by customer
    if (!isset($_GET['sort']) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
      $_GET['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
    }
//die('I SEE ' . $_GET['sort'] . ' - ' . PRODUCT_LISTING_DEFAULT_SORT_ORDER);
  if ((!isset($_GET['sort'])) || (!ereg('[1-8][ad]', $_GET['sort'])) || (substr($_GET['sort'], 0 , 1) > sizeof($column_list))) {
    for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
      if ($column_list[$col] == 'PRODUCT_LIST_NAME') {
        $_GET['sort'] = $col+1 . 'a';
        $order_str = ' order by pd.`products_name`';
        break;
        } else {
// sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER ia left blank
// for reverse, descending order use:
//       $listing_sql .= " order by p.`products_sort_order` desc, pd.`products_name`";
          $order_str .= " order by p.`products_sort_order`, pd.`products_name`";
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
    $order_str = ' order by ';
    switch ($column_list[$sort_col-1]) {
      case 'PRODUCT_LIST_MODEL':
        $order_str .= "p.`products_model` " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        break;
      case 'PRODUCT_LIST_NAME':
        $order_str .= "pd.`products_name` " . ($sort_order == 'd' ? "desc" : "");
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $order_str .= "m.manufacturers_name " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $order_str .= "p.`products_quantity` " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        break;
      case 'PRODUCT_LIST_IMAGE':
        $order_str .= "pd.`products_name`";
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $order_str .= "p.`products_weight` " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        break;
      case 'PRODUCT_LIST_PRICE':
//        $order_str .= "final_price " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        $order_str .= "p.`products_price_sorter` " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        break;
    }
  }
  $listing_sql = $select_str . $from_str . $where_str . $order_str;
  require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING));
?>
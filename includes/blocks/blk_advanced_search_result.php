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
// $Id$
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
        $select_column_list .= 'm.`manufacturers_name`';
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
  $select_str = "select " . $select_column_list . " m.`manufacturers_id`, p.`products_id`, pd.`products_name`, p.`products_price`, p.`products_tax_class_id`, p.`lowest_purchase_price`, p.`products_priced_by_attribute`, pt.* ";

  if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_REQUEST['pfrom']) && zen_not_null($_REQUEST['pfrom'])) || (isset($_REQUEST['pto']) && zen_not_null($_REQUEST['pto'])))) {
    $select_str .= ", SUM(tr.tax_rate) as tax_rate ";
  }

//  $from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id`, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";
  $from_str = "from " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id`) left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) ";

  if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_REQUEST['pfrom']) && zen_not_null($_REQUEST['pfrom'])) || (isset($_REQUEST['pto']) && zen_not_null($_REQUEST['pto'])))) {
    if (!$_SESSION['customer_country_id']) {
      $_SESSION['customer_country_id'] = STORE_COUNTRY;
      $_SESSIOn['customer_zone_id'] = STORE_ZONE;
    }
    $from_str .= " left join " . TABLE_TAX_RATES . " tr on p.`products_tax_class_id` = tr.tax_class_id left join " . TABLE_ZONES_TO_GEO_ZONES . " gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = '0' or gz.zone_country_id = '" . $_SESSION['customer_country_id'] . "') and (gz.zone_id is null or gz.zone_id = '0' or gz.zone_id = '" . $_SESSION['customer_zone_id'] . "')";
  }

  $from_str .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON( p.`products_id` = p2c.`products_id` ) LEFT JOIN  " . TABLE_CATEGORIES . " c ON( p2c.`categories_id`=c.`categories_id` ) left join " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd on mtpd.`products_id`= p.`products_id` and mtpd.`language_id` = '" . $_SESSION['languages_id'] . "', " . TABLE_PRODUCTS_DESCRIPTION . " pd ";

  $where_str = " where p.`products_status` = '1' and p.`products_id` = pd.`products_id` and pd.`language_id` = '" . $_SESSION['languages_id'] . "' ";

  if (isset($_REQUEST['categories_id']) && zen_not_null($_REQUEST['categories_id'])) {
    if ($_REQUEST['inc_subcat'] == '1') {
      $subcategories_array = array();
      zen_get_subcategories($subcategories_array, $_REQUEST['categories_id']);
      $where_str .= " and p2c.`products_id` = pd.`products_id` and (p2c.`categories_id` = '" . (int)$_REQUEST['categories_id'] . "'";
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $where_str .= " or p2c.`categories_id` = '" . $subcategories_array[$i] . "'";
      }
      $where_str .= ")";
    } else {
      $where_str .= " and pd.`language_id` = '" . $_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$_REQUEST['categories_id'] . "'";
    }
  }

  if (isset($_REQUEST['manufacturers_id']) && zen_not_null($_REQUEST['manufacturers_id'])) {
    $where_str .= " and m.`manufacturers_id` = '" . $_REQUEST['manufacturers_id'] . "'";
  }

  if (isset($_REQUEST['keyword']) && zen_not_null($_REQUEST['keyword'])) {
    if (zen_parse_search_string(stripslashes($_REQUEST['keyword']), $search_keywords)) {
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
            $where_str .= "(lower( pd.`products_name` ) like '%" . addslashes( strtolower( $search_keywords[$i]) ) . "%' or p.`products_model` like '%" . addslashes($search_keywords[$i]) . "%' or m.`manufacturers_name` like '%" . addslashes($search_keywords[$i]) . "%'";
// search meta tags
            $where_str .= " or (mtpd.`metatags_keywords` like '%" . addslashes($search_keywords[$i]) . "%' and mtpd.`metatags_keywords` !='')";
            $where_str .= " or (mtpd.`metatags_description` like '%" . addslashes($search_keywords[$i]) . "%' and mtpd.`metatags_description` !='')";
            if (isset($_REQUEST['search_in_description']) && ($_REQUEST['search_in_description'] == '1')) $where_str .= " or pd.`products_description` like '%" . addslashes($search_keywords[$i]) . "%'";
              $where_str .= ')';
            break;
        }
      }
      $where_str .= " )";
    }
  }

  if (isset($_REQUEST['dfrom']) && zen_not_null($_REQUEST['dfrom']) && ($_REQUEST['dfrom'] != DOB_FORMAT_STRING)) {
    $where_str .= " and p.`products_date_added` >= '" . zen_date_raw($dfrom) . "'";
  }

  if (isset($_REQUEST['dto']) && zen_not_null($_REQUEST['dto']) && ($_REQUEST['dto'] != DOB_FORMAT_STRING)) {
    $where_str .= " and p.`products_date_added` <= '" . zen_date_raw($dto) . "'";
  }

  if( !empty( $_SESSION['currency'] ) && $rate = $currencies->get_value($_SESSION['currency']) ) {
    $pfrom = $_REQUEST['pfrom'] / $rate;
    $pto = $_REQUEST['pto'] / $rate;
  }

  if (DISPLAY_PRICE_WITH_TAX == 'true') {
//    if ($pfrom) $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) >= " . $pfrom . ")";
//    if ($pto)   $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) <= " . $pto . ")";
    if ($pfrom) $where_str .= " and (p.`lowest_purchase_price` * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) >= " . $pfrom . ")";
    if ($pto)   $where_str .= " and (p.`lowest_purchase_price` * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100)) <= " . $pto . ")";
  } else {
//    if ($pfrom) $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) >= " . $pfrom . ")";
//    if ($pto)   $where_str .= " and (IF(s.status = '1', s.specials_new_products_price, p.`products_price`) <= " . $pto . ")";
    if ($pfrom) $where_str .= " and (p.`lowest_purchase_price` >= " . $pfrom . ")";
    if ($pto)   $where_str .= " and (p.`lowest_purchase_price` <= " . $pto . ")";
  }

  if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset($_REQUEST['pfrom']) && zen_not_null($_REQUEST['pfrom'])) || (isset($_REQUEST['pto']) && zen_not_null($_REQUEST['pto'])))) {
    $where_str .= " group by p.`products_id`, tr.tax_priority";
  }

// set the default sort order setting from the Admin when not defined by customer
    if (!isset($_REQUEST['sort']) and PRODUCT_LISTING_DEFAULT_SORT_ORDER != '') {
      $_REQUEST['sort'] = PRODUCT_LISTING_DEFAULT_SORT_ORDER;
    }
//die('I SEE ' . $_REQUEST['sort'] . ' - ' . PRODUCT_LISTING_DEFAULT_SORT_ORDER);
  if ((!isset($_REQUEST['sort'])) || (!ereg('[1-8][ad]', $_REQUEST['sort'])) || (substr($_REQUEST['sort'], 0 , 1) > sizeof($column_list))) {
    for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
      if ($column_list[$col] == 'PRODUCT_LIST_NAME') {
        $_REQUEST['sort'] = $col+1 . 'a';
        $order_str = ' order by pd.`products_name`';
        break;
        } else {
// sort by products_sort_order when PRODUCT_LISTING_DEFAULT_SORT_ORDER ia left blank
// for reverse, descending order use:
//       $listing_sql .= " order by p.`products_sort_order` desc, pd.`products_name`";
          $order_str = " order by p.`products_sort_order`, pd.`products_name`";
          break;
        }
    }
// if set to nothing use products_sort_order and PRODUCTS_LIST_NAME is off
      if (PRODUCT_LISTING_DEFAULT_SORT_ORDER == '') {
        $_REQUEST['sort'] = '20a';
      }
  } else {
    $sort_col = substr($_REQUEST['sort'], 0 , 1);
    $sort_order = substr($_REQUEST['sort'], 1);
    $order_str = ' order by ';
    switch ($column_list[$sort_col-1]) {
      case 'PRODUCT_LIST_MODEL':
        $order_str .= "p.`products_model` " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        break;
      case 'PRODUCT_LIST_NAME':
        $order_str .= "pd.`products_name` " . ($sort_order == 'd' ? "desc" : "");
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $order_str .= "m.`manufacturers_name` " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
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
        $order_str .= "p.`lowest_purchase_price` " . ($sort_order == 'd' ? "desc" : "") . ", pd.`products_name`";
        break;
    }
  }
  $listing_sql = $select_str . $from_str . $where_str . $order_str;

  $show_submit = zen_run_normal();
  $listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_PRODUCTS_LISTING, 'p.`products_id`', 'page');
  $how_many = 0;
  if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0 and $show_submit == 'true' and $listing_split->number_of_rows > 0) {
    // bof: multiple products
    echo zen_draw_form('multiple_products_cart_quantity', zen_href_link($gBitProduct->getInfoPage(), zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product'), 'post', 'enctype="multipart/form-data"');
  }
  $zc_col_count_description = 0;
  for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
    switch ($column_list[$col]) {
      case 'PRODUCT_LIST_MODEL':
        $lc_text = TABLE_HEADING_MODEL;
        $lc_align = '';
        $zc_col_count_description++;
        break;
      case 'PRODUCT_LIST_NAME':
        $lc_text = TABLE_HEADING_PRODUCTS;
        $lc_align = '';
        $zc_col_count_description++;
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $lc_text = TABLE_HEADING_MANUFACTURER;
        $lc_align = '';
        $zc_col_count_description++;
        break;
      case 'PRODUCT_LIST_PRICE':
        $lc_text = TABLE_HEADING_PRICE;
        $lc_align = 'right' . (PRODUCTS_LIST_PRICE_WIDTH > 0 ? '" width="' . PRODUCTS_LIST_PRICE_WIDTH : '');
        $zc_col_count_description++;
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $lc_text = TABLE_HEADING_QUANTITY;
        $lc_align = 'right';
        $zc_col_count_description++;
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $lc_text = TABLE_HEADING_WEIGHT;
        $lc_align = 'right';
        $zc_col_count_description++;
        break;
      case 'PRODUCT_LIST_IMAGE':
        $lc_text = TABLE_HEADING_IMAGE;
        $lc_align = 'center';
        $zc_col_count_description++;
        break;
    }

    if ( ($column_list[$col] != 'PRODUCT_LIST_IMAGE') && isset( $_GET['sort'] ) ) {
      $lc_text = zen_create_sort_heading($_GET['sort'], $col+1, $lc_text);
    }

    $list_box_contents[0][$col] = array('align' => $lc_align,
                                    'params' => 'class="productListing-heading" nowrap="nowrap"',
                                    'text' => '&nbsp;' . $lc_text . '&nbsp;');
  }

  if ($listing_split->number_of_rows > 0) {
	$extra_row = 0;
    $rows = 0;
	$offset = MAX_DISPLAY_PRODUCTS_LISTING * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
    $listing = $gBitDb->query( $listing_split->sql_query, NULL, MAX_DISPLAY_PRODUCTS_LISTING, $offset );
    while (!$listing->EOF) {
      $rows++;

      if ((($rows-$extra_row)/2) == floor(($rows-$extra_row)/2)) {
        $list_box_contents[$rows] = array('params' => 'class="even"');
      } else {
        $list_box_contents[$rows] = array('params' => 'class="odd"');
      }
      $cur_row = sizeof($list_box_contents) - 1;
      for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
        $lc_align = '';

        switch ($column_list[$col]) {
          case 'PRODUCT_LIST_MODEL':
            $lc_align = '';
            $lc_text = $listing->fields['products_model'];
            break;
          case 'PRODUCT_LIST_NAME':
            $lc_align = '';
            if (isset($_GET['manufacturers_id'])) {
              $lc_text = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $listing->fields['products_id']) . '">' . $listing->fields['products_name'] . '</a>';
            } else {
              $lc_text = '<a href="' . CommerceProduct::getDisplayUrlFromId( $listing->fields['products_id'] ) . '">' . $listing->fields['products_name'] . '</a>';
            }
			// add description
			if (PRODUCT_LIST_DESCRIPTION > 0) {
				$lc_text .= '<div>' . zen_trunc_string(zen_clean_html(zen_get_products_description($listing->fields['products_id'], $_SESSION['languages_id'])), PRODUCT_LIST_DESCRIPTION) . '</div>';
			}

            break;
          case 'PRODUCT_LIST_MANUFACTURER':
            $lc_align = '';
            $lc_text = '<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $listing->fields['manufacturers_id']) . '">' . $listing->fields['manufacturers_name'] . '</a>';
            break;
          case 'PRODUCT_LIST_PRICE':
            $lc_price = CommerceProduct::getDisplayPriceFromHash($listing->fields['products_id']) . '<br />';
            $lc_align = 'right';
            $lc_text = $lc_price;

// more info in place of buy now
            $lc_button = '';
            if( $listing->fields['products_priced_by_attribute'] || PRODUCT_LIST_PRICE_BUY_NOW == '0' || zen_has_product_attributes( $listing->fields['products_id'] ) ) {
              $lc_button = '<a href="' . CommerceProduct::getDisplayUrlFromId( $listing->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>';
            } else {
              if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0) {
                $how_many++;
                $lc_button = TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART . "<input type=\"text\" name=\"products_id[" . $listing->fields['products_id'] . "]\" value=0 size=\"4\">";
              } else {
                $lc_button = '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT) . '</a>&nbsp;';
              }
            }
            $the_button = $lc_button;
            $products_link = '<a href="' . CommerceProduct::getDisplayUrlFromId( $listing->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>';
            $lc_text .= '<br />' . zen_get_buy_now_button($listing->fields['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($listing->fields['products_id']);

            break;
          case 'PRODUCT_LIST_QUANTITY':
            $lc_align = 'right';
            $lc_text = '&nbsp;' . $listing->fields['products_quantity'] . '&nbsp;';
            break;
          case 'PRODUCT_LIST_WEIGHT':
            $lc_align = 'right';
            $lc_text = '&nbsp;' . $listing->fields['products_weight'] . '&nbsp;';
            break;
          case 'PRODUCT_LIST_IMAGE':
            $lc_align = 'center';
            if (isset($_GET['manufacturers_id'])) {
              $lc_text = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $listing->fields['products_id']) . '">' . zen_image(  CommerceProduct::getImageUrlFromHash( $listing->fields['products_id'], 'avatar' ), $listing->fields['products_name'] ) . '</a>';
            } else {
				$typeClass = BitBase::getParameter( $listing->fields, 'type_class', 'CommerceProduct' );
				if( !empty( $listing->fields['type_class_file'] ) && file_exists( BIT_ROOT_PATH.$listing->fields['type_class_file'] ) ) {
					require_once( BIT_ROOT_PATH.$listing->fields['type_class_file'] );
				}
				if( $thumbnail = $typeClass::getImageUrlFromHash( $listing->fields['products_id'], 'avatar' ) ) {
	              $lc_text = '<a href="' . CommerceProduct::getDisplayUrlFromId( $listing->fields['products_id'] ) . '">' . zen_image( $thumbnail, $listing->fields['products_name'] ) . '</a>';
				}
            }
            break;
        }

        $list_box_contents[$rows][$col] = array('align' => $lc_align, 'params' => 'class="data"', 'text'  => $lc_text);
      }
      $listing->MoveNext();
    }
    $error_categories = false;
  } else {
    $list_box_contents = array();

    $list_box_contents[0] = array('params' => 'class="odd"');
    $list_box_contents[0][] = array('params' => 'class="data"', 'text' => TEXT_NO_PRODUCTS);

    $error_categories = true;
  }

  if (($how_many > 0 and $show_submit == 'true' and $listing_split->number_of_rows > 0) and (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 1 or  PRODUCT_LISTING_MULTIPLE_ADD_TO_CART == 3) ) {
    $show_top_submit_button = 'true';
  }
  if (($how_many > 0 and $show_submit == 'true' and $listing_split->number_of_rows > 0) and (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART >= 2) ) {
    $show_bottom_submit_button = 'true';
  }
?>
<?php  require( DIR_FS_MODULES . 'tpl_modules_product_listing.php' ); ?>

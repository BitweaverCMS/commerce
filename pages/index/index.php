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
//die($category_depth);
//die($_REQUEST['music_genre_id']);

// release manufactures_id when nothing is there so a blank filter is not setup.
// this will result in the home page, if used
if( !empty( $_REQUEST['manufacturers_id'] ) && ($_REQUEST['manufacturers_id'] <= 0) ) {
	unset($_REQUEST['manufacturers_id']);
	unset($manufacturers_id);
}

if ($category_depth == 'nested') {
	$sql = "select cd.`categories_name`, c.categories_image
			from   " . TABLE_CATEGORIES . " c, " .
					 TABLE_CATEGORIES_DESCRIPTION . " cd
			where      c.`categories_id` = '" . (int)$current_category_id . "'
			and        cd.`categories_id` = '" . (int)$current_category_id . "'
			and        cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
			and        c.`categories_status`= '1'";

	$category = $gBitDb->Execute($sql);

	if (isset($cPath) && strpos($cPath, '_')) {
// check to see if there are deeper categories within the current category
	$category_links = array_reverse($cPath_array);
	for($i=0, $n=sizeof($category_links); $i<$n; $i++)
	{
		$subcatCount = $gBitDb->getOn( "select count(*) as `total` from   " . TABLE_CATEGORIES . " c INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON ( c.`categories_id` = cd.`categories_id` )
				where      c.`parent_id` =? AND cd.`language_id` = ? AND c.`categories_status`= '1'", array( (int)$category_links[$i], (int)$_SESSION['languages_id']) );

		if( $subcatCount ) {
		$categories_query = "select c.`categories_id`, cd.`categories_name`, c.categories_image, c.`parent_id`
				FROM   " . TABLE_CATEGORIES . " c INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.`categories_id` = cd.`categories_id`)
				WHERE c.`parent_id` = '" . (int)$category_links[$i] . "' AND cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
				AND        c.`categories_status`= '1'
				ORDER BY   `sort_order`, cd.`categories_name`";

		break; // we've found the deepest category the customer is in
		}
	}
	} else {
	$categories_query = "SELECT c.`categories_id`, cd.`categories_name`, c.categories_image, c.`parent_id`
						 FROM   " . TABLE_CATEGORIES . " c INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON( c.`categories_id` = cd.`categories_id` )
						 WHERE      c.`parent_id` = '" . (int)$current_category_id . "'
						 AND        cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
						 AND        c.`categories_status`= '1'
						 ORDER BY   `sort_order`, cd.`categories_name`";
	}
	$categories = $gBitDb->Execute($categories_query);
	$number_of_categories = $categories->RecordCount();
	$new_products_category_id = $current_category_id;

/////////////////////////////////////////////////////////////////////////////////////////////////////
	$tpl_page_body = 'index_categories.php';
/////////////////////////////////////////////////////////////////////////////////////////////////////

//  } elseif ($category_depth == 'products' || isset($_REQUEST['manufacturers_id']) || isset($_REQUEST['music_genre_id'])) {
} elseif ($category_depth == 'products' || !empty( $_GET['user_id'] ) || zen_check_url_get_terms() ) {
	if (SHOW_PRODUCT_INFO_ALL_PRODUCTS == '1') {
		// set a category filter
		$new_products_category_id = $cPath;
	} else {
		// do not set the category
	}
	// create column list
	$define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
						 'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
						 'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
						 'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
						 'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
						 'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
						 'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE);
	asort($define_list);
	$column_list = array();
	reset($define_list);
	while (list($key, $value) = each($define_list)) {
		if ($value > 0) {
			$column_list[] = $key;
		}
	}

	$select_column_list = '';

	for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
		switch ($column_list[$i]) {
			case 'PRODUCT_LIST_MODEL':
			$select_column_list .= 'p.`products_model`, ';
			break;
			case 'PRODUCT_LIST_NAME':
			$select_column_list .= 'pd.`products_name`, ';
			break;
			case 'PRODUCT_LIST_MANUFACTURER':
			$select_column_list .= 'm.`manufacturers_name`, ';
			break;
			case 'PRODUCT_LIST_QUANTITY':
			$select_column_list .= 'p.`products_quantity`, ';
			break;
			case 'PRODUCT_LIST_IMAGE':
			$select_column_list .= 'p.`products_image`, ';
			break;
			case 'PRODUCT_LIST_WEIGHT':
			$select_column_list .= 'p.`products_weight`, ';
			break;
		}
	}
	// add the product filters for other product types here


// show the products of a specified manufacturer
	$bindVars = array(); $selectSql = ''; $joinSql = ''; $whereSql = '';
// 	$gBitProduct->getServicesSql( 'content_load_function', $selectSql, $joinSql, $whereSql, $bindVars );
	$gBitProduct->getGatekeeperSql( $selectSql, $joinSql, $whereSql );
    if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '' ) {
      if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
// We are asked to show only a specific category
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_priced_by_attribute`, p.`products_tax_class_id`, pd.`products_description`, if(s.`status` = '1', s.`specials_new_products_price`, NULL) AS `specials_new_products_price`, IF(s.`status` = '1', s.`specials_new_products_price`, p.`products_price`) as `final_price`, p.`products_sort_order` $selectSql from " . TABLE_PRODUCTS . " p INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc  ON (p.`content_id`=lc.`content_id`) $joinSql, " . TABLE_PRODUCTS_DESCRIPTION . " pd , " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` where p.`products_status` = '1' and p.`manufacturers_id` = m.`manufacturers_id` and m.`manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$_GET['filter_id'] . "'";
      } else {
// We show them all
$listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_priced_by_attribute`, p.`products_tax_class_id`, pd.`products_description`, IF(s.`status` = '1', s.`specials_new_products_price`, NULL) as `specials_new_products_price`, IF(s.`status` = '1', s.`specials_new_products_price`, p.`products_price`) as `final_price`, p.`products_sort_order` $selectSql from " . TABLE_PRODUCTS . " p INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (p.`content_id`=lc.`content_id`) INNER JOIN " . TABLE_MANUFACTURERS ." m ON(p.`manufacturers_id` = m.`manufacturers_id`) left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` $joinSql, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.`products_status` = '1' and pd.`products_id` = p.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and m.`manufacturers_id` = '" . (int)$_GET['manufacturers_id'] . "'";
      }
    } else {
// show the products in a given categorie
      if (isset($_GET['filter_id']) && zen_not_null($_GET['filter_id'])) {
// We are asked to show only specific catgeory
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_priced_by_attribute`, p.`products_tax_class_id`, pd.`products_description`, IF(s.`status` = '1', s.`specials_new_products_price`, NULL) as specials_new_products_price, IF(s.`status` = '1', s.`specials_new_products_price`, p.`products_price`) as `final_price`, p.`products_sort_order` $selectSql from " . TABLE_PRODUCTS . " p INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (p.`content_id`=lc.`content_id`) $joinSql, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS . " s on p.`products_id` = s.`products_id` where p.`products_status` = '1' and p.`manufacturers_id` = m.`manufacturers_id` and m.`manufacturers_id` = '" . (int)$_GET['filter_id'] . "' and p.`products_id` = p2c.`products_id` and pd.`products_id` = p2c.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and p2c.`categories_id` = '" . (int)$current_category_id . "'";
      } else {
// We show them all
        $listing_sql = "select " . $select_column_list . " p.`products_id`, p.`manufacturers_id`, p.`products_price`, p.`products_priced_by_attribute`, p.`products_tax_class_id`, pd.`products_description`, s.`specials_new_products_price`, p.`products_sort_order` $selectSql
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

    if ( (!isset($_GET['sort'])) || (!preg_match('/[1-8][ad]/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) )
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





	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	?>
	<table border="0" width="100%" cellspacing="2" cellpadding="2">
		<tr>
		<td class="pageHeading"><h1><?php echo $breadcrumb->last(); ?></h1></td>
	<?php
		if( !empty( $do_filter_list ) ) {
		$form = zen_draw_form('filter', zen_href_link(FILENAME_DEFAULT), 'get') . TEXT_SHOW;
	?>
		<td align="right" valign="bottom" class="main"><?php echo $form ?>
	<?php

		if (!$getoption_set) {
		echo zen_draw_hidden_field('cPath', $cPath);
		} else {
		echo zen_draw_hidden_field($get_option_variable, $_GET[$get_option_variable]);
		}
		if (isset($_GET['typefilter'])) echo zen_draw_hidden_field('typefilter', $_GET['typefilter']);
		if ($_GET['manufacturers_id']) {
		echo zen_draw_hidden_field('manufacturers_id', $_GET['manufacturers_id']);
		}
		echo zen_draw_hidden_field('sort', $_GET['sort']);
		echo zen_draw_hidden_field('main_page', FILENAME_DEFAULT);
		echo zen_draw_pull_down_menu('filter_id', $options, (isset($_GET['filter_id']) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"');
	?>
		</form></td>
	<?php
		}
	?>
		</tr>
	<?php 

	$listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_PRODUCTS_LISTING, 'p.`products_id`', 'page');
	if ($listing_split->number_of_rows > 0) {
	?>
		<tr>
		<td colspan="2" class="main">
	<?php
		$extra_row = 0;
		$rows = 0;
		$offset = MAX_DISPLAY_PRODUCTS_LISTING * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
		$listing = $gBitDb->query( $listing_split->sql_query, NULL, MAX_DISPLAY_PRODUCTS_LISTING, $offset );
		while (!$listing->EOF) {
			$listProducts[$listing->fields['products_id']] = $listing->fields;
			$listing->MoveNext();
		}

		$gBitSmarty->assign_by_ref( 'listProducts', $listProducts );
		$gBitSmarty->display( 'bitpackage:bitcommerce/list_products_inc.tpl' );
	?>
		</td>
		</tr>
	<?php
		$error_categories = false;
	} else {
		$error_categories = true;
	}
	?>
	</table>

	<?php
	//// bof: categories error
	if ($error_categories==true) {
		// verify lost category and reset category
		$check_category = $gBitDb->Execute("select `categories_id` from " . TABLE_CATEGORIES . " where `categories_id` ='" . $current_category_id . "'");
		if ($check_category->RecordCount() == 0) {
			$new_products_category_id = '0';
			$cPath= '';
		}
		$show_display_category = $gBitDb->Execute(SQL_SHOW_PRODUCT_INFO_MISSING);

		while (!$show_display_category->EOF) {
			if( $show_display_category->fields['configuration_value'] ) {
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_FEATURED_PRODUCTS') {
					include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_FEATURED_PRODUCTS_MODULE));
				}
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_SPECIALS_PRODUCTS') {
					include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_SPECIALS_INDEX));
				}
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_NEW_PRODUCTS') {
					require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_NEW_PRODUCTS));
				}
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_UPCOMING') {
					include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS));
				}
			}
			$show_display_category->MoveNext();
		} // !EOF

	} //// eof: categories error 

	//// bof: categories
	$show_display_category = $gBitDb->Execute(SQL_SHOW_PRODUCT_INFO_LISTING_BELOW);
	if ($error_categories == false and $show_display_category->RecordCount() > 0) {
		$show_display_category = $gBitDb->Execute(SQL_SHOW_PRODUCT_INFO_LISTING_BELOW);
		while( !$show_display_category->EOF ) {
			if( $show_display_category->fields['configuration_value'] ) {
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_FEATURED_PRODUCTS') {
					include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_FEATURED_PRODUCTS_MODULE));
				}
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_SPECIALS_PRODUCTS') {
					include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_SPECIALS_INDEX));
				}
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_NEW_PRODUCTS') {
					require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_NEW_PRODUCTS));
				}
				if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_UPCOMING') {
					include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS));
				}
			}
			$show_display_category->MoveNext();
		} // !EOF
	} //// eof: categories
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
} else {
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$gBitSmarty->assign( 'mainDisplayBlocks', $gBitDb->getAll(SQL_SHOW_PRODUCT_INFO_MAIN) );
}


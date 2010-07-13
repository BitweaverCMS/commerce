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

    if ( ($column_list[$col] != 'PRODUCT_LIST_IMAGE') ) {
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
              $lc_text = '<a href="' . CommerceProduct::getDisplayUrl( $listing->fields['products_id'] ) . '">' . $listing->fields['products_name'] . '</a>';
            }
			// add description
			if (PRODUCT_LIST_DESCRIPTION > 0) {
				$lc_text .= '<div>' . zen_trunc_string(zen_clean_html(zen_get_products_description($listing->fields['products_id'], $_SESSION['languages_id'])), PRODUCT_LIST_DESCRIPTION) . '</div>';
			}

            break;
          case 'PRODUCT_LIST_MANUFACTURER':
            $lc_align = '';
            $lc_text = '&nbsp;<a href="' . zen_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $listing->fields['manufacturers_id']) . '">' . $listing->fields['manufacturers_name'] . '</a>&nbsp;';
            break;
          case 'PRODUCT_LIST_PRICE':
            $lc_price = CommerceProduct::getDisplayPrice($listing->fields['products_id']) . '<br />';
            $lc_align = 'right';
            $lc_text = '&nbsp;' . $lc_price . '&nbsp;';

// more info in place of buy now
            $lc_button = '';
            if( $listing->fields['products_priced_by_attribute'] || PRODUCT_LIST_PRICE_BUY_NOW == '0' || zen_has_product_attributes( $listing->fields['products_id'] ) ) {
              $lc_button = '<a href="' . CommerceProduct::getDisplayUrl( $listing->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>';
            } else {
              if (PRODUCT_LISTING_MULTIPLE_ADD_TO_CART != 0) {
                $how_many++;
                $lc_button = TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART . "<input type=\"text\" name=\"products_id[" . $listing->fields['products_id'] . "]\" value=0 size=\"4\">";
              } else {
                $lc_button = '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT) . '</a>&nbsp;';
              }
            }
            $the_button = $lc_button;
            $products_link = '<a href="' . CommerceProduct::getDisplayUrl( $listing->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>';
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
              $lc_text = '<a href="' . zen_href_link(zen_get_info_page($listing->fields['products_id']), 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $listing->fields['products_id']) . '">' . zen_image(  CommerceProduct::getImageUrl( $listing->fields['products_id'], 'avatar' ), $listing->fields['products_name'] ) . '</a>';
            } else {
              $lc_text = '&nbsp;<a href="' . CommerceProduct::getDisplayUrl( $listing->fields['products_id'] ) . '">' . zen_image( CommerceProduct::getImageUrl( $listing->fields['products_id'], 'avatar' ), $listing->fields['products_name'] ) . '</a>&nbsp;';
            }
            break;
        }

        $list_box_contents[$rows][$col] = array('align' => $lc_align,
                                               'params' => 'class="data"',
                                               'text'  => $lc_text);
      }
      $listing->MoveNext();
    }
    $error_categories = false;
  } else {
    $list_box_contents = array();

    $list_box_contents[0] = array('params' => 'class="odd"');
    $list_box_contents[0][] = array('params' => 'class="data"',
                                   'text' => TEXT_NO_PRODUCTS);

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

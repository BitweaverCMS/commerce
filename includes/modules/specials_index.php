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
// $Id: specials_index.php,v 1.5 2005/08/03 13:04:40 spiderr Exp $
//

  $title = sprintf(TABLE_HEADING_SPECIALS_INDEX, strftime('%B'));



	$listHash['specials'] = TRUE;
	if ( !empty( $new_products_category_id ) ) {
  		$listHash['categories_id'] = $new_products_category_id;
	}

/*
    $specials_index_query = "select p.products_id, p.products_image, pd.products_name
                           from " . TABLE_PRODUCTS . " p
                           left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id
                           left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id
                           where p.products_id = s.products_id and p.products_id = pd.products_id and p.products_status = '1' and s.status = '1' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
  } else {
    $specials_index_query = "select p.products_id, p.products_image, pd.products_name
                           from " . TABLE_PRODUCTS . " p
                           left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id
                           left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id, " .
                              TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " .
                              TABLE_CATEGORIES . " c
                           where p.products_id = p2c.products_id
                           and p2c.categories_id = c.categories_id
                           and c.parent_id = '" . (int)$new_products_category_id . "'
                           and p.products_id = s.products_id and p.products_id = pd.products_id and p.products_status = '1' and s.status = '1' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

  }
  $specials_index = $db->query($specials_index_query.' ORDER BY '.$db->convert_sortmode( 'random' ), NULL, MAX_DISPLAY_SPECIAL_PRODUCTS_INDEX);
*/


  $row = 0;
  $col = 0;
  $list_box_contents = '';

// show only when 1 or more
  if ( $specialProducts = $gBitProduct->getList( $listHash ) ) {
  	$num_products_count = count( $specialProducts );
    if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS) {
      $col_width = 100/$num_products_count;
    } else {
      $col_width = 100/SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS;
    }

	foreach( array_keys( $specialProducts ) AS $productsId ) {
		$products_price = zen_get_products_display_price( $productsId );
		$specialProducts['products_name'] = zen_get_products_name($specialProducts[$productsId]['products_id']);
		$list_box_contents[$row][$col] = array('align' => 'center',
												'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
												'text' => '<a href="' . zen_href_link(zen_get_info_page($productsId), 'products_id=' . $productsId) . '">' . zen_image( CommerceProduct::getImageUrl( $productsId, 'avatar' ), $specialProducts['products_name']) . '</a><br /><a href="' . zen_href_link(zen_get_info_page($productsId), 'products_id=' . $productsId) . '">' . $specialProducts['products_name'] . '</a><br />' . $products_price);

		$col ++;
		if ($col > (SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS - 1)) {
			$col = 0;
			$row ++;
		}
    }

    if( !empty( $specialProducts ) ) {
      $title = sprintf(TABLE_HEADING_SPECIALS_INDEX, strftime('%B'));
      require($template->get_template_dir('tpl_modules_specials_default.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_specials_default.php');
    }
  }
?>

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
// $Id: new_products.php,v 1.10 2005/08/12 07:03:22 spiderr Exp $
//
  $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B'));

// display limits
  switch (true) {
    case (SHOW_NEW_PRODUCTS_LIMIT == '0'):
      $display_limit = '';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '1'):
      $display_limit = " and date_format(p.products_date_added, '%Y%m') >= date_format(now(), '%Y%m')";
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '30'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 30';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '60'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 60';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '90'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 90';
      break;
    case (SHOW_NEW_PRODUCTS_LIMIT == '120'):
      $display_limit = ' and TO_DAYS(NOW()) - TO_DAYS(p.products_date_added) <= 120';
      break;
  }
  // nuke MYSQL specific stuff for now - spiderr
  $display_limit = '';

	$listHash['sort_mode'] = 'random';
	if ( !empty( $new_products_category_id ) ) {
		$listHash['category_id'] = $new_products_category_id;
	}
  $new_products = $gBitProduct->getList( $listHash );
  $row = 0;
  $col = 0;
  $list_box_contents = '';

  $num_products_count = count( $new_products );

// show only when 1 or more
  if ($num_products_count > 0) {
    if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS) {
      $col_width = 100/$num_products_count;
    } else {
      $col_width = 100/SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS;
    }
// $gBitProduct->debug();
    foreach( $new_products as $product ) {
      $products_price = zen_get_products_display_price($product['products_id']);

      $list_box_contents[$row][$col] = array('align' => 'center',
                                             'params' => 'class="smallText" width="' . $col_width . '%" valign="top"',
                                             'text' => '<a href="' . zen_href_link(zen_get_info_page($product['products_id']), 'products_id=' . $product['products_id']) . '">' . zen_image( CommerceProduct::getImageUrl( $product['products_id'], 'avatar' ), $product['products_name'] ) . '</a><br /><a href="' . zen_href_link( $product['info_page'], 'products_id=' . $product['products_id']) . '">' . $product['products_name'] . '</a><br />' . $products_price);

      $col ++;
      if ($col > (SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS - 1)) {
        $col = 0;
        $row ++;
      }
    }

    if ( $num_products_count ) {
      if (isset($new_products_category_id)) {
        $category_title = zen_get_categories_name((int)$new_products_category_id);
        $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')) . ($category_title != '' ? ' - ' . $category_title : '' );
      } else {
        $title = sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B'));
      }
      require( DIR_FS_MODULES . 'tpl_modules_whats_new.php' );
    }
// $gBitProduct->debug( 0 );
  }
?>

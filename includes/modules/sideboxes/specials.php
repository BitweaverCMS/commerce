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
// $Id: specials.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//

// test if box should display
  $show_specials= false;

  if (isset($_GET['products_id'])) {
    $show_specials= false;
  } else {
    $show_specials= true;
  }

  if ($show_specials == true) {
    $random_specials_sidebox_product_query = "select p.products_id, pd.products_name, p.products_price,
                                    p.products_tax_class_id, p.products_image,
                                    s.specials_new_products_price
                             from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, "
                                    . TABLE_SPECIALS . " s
                             where p.products_status = '1'
                             and p.products_id = s.products_id
                             and pd.products_id = s.products_id
                             and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             and s.status = '1'
                             limit " . MAX_RANDOM_SELECT_SPECIALS;

    $random_specials_sidebox_product = zen_random_select($random_specials_sidebox_product_query);

    if ($random_specials_sidebox_product->RecordCount() > 0)  {
      $specials_box_price = zen_get_products_display_price($random_specials_sidebox_product->fields['products_id']);

      require($template->get_template_dir('tpl_specials.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_specials.php');
      $title =  BOX_HEADING_SPECIALS;
      $left_corner = false;
      $right_corner = false;
      $right_arrow = false;
      $title_link = FILENAME_SPECIALS;
      require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
    }
  }
?>
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
//  $Id: catalog_dhtml.php,v 1.8 2007/01/06 07:07:23 spiderr Exp $
//
  $za_contents = array();
  $za_heading = array('text' => BOX_HEADING_CATALOG, 'link' => zen_href_link_admin(FILENAME_ALT_NAV, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_CATEGORIES_PRODUCTS, 'link' => zen_href_link_admin(FILENAME_CATEGORIES, '', 'NONSSL'));

  $za_contents[] = array('text' => BOX_CATALOG_PRODUCT_TYPES, 'link' => zen_href_link_admin(FILENAME_PRODUCT_TYPES, '', 'NONSSL'));
  $za_contents[] = array('text' => tra( 'Product Options' ), 'link' => zen_href_link_admin( 'products_options.php', '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_PRODUCTS_PRICE_MANAGER, 'link' => zen_href_link_admin(FILENAME_PRODUCTS_PRICE_MANAGER, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_CATEGORIES_ATTRIBUTES_DOWNLOADS_MANAGER, 'link' => zen_href_link_admin(FILENAME_DOWNLOADS_MANAGER, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_MANUFACTURERS, 'link' => zen_href_link_admin(FILENAME_MANUFACTURERS, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_SUPPLIERS, 'link' => zen_href_link_admin(FILENAME_SUPPLIERS, '', 'NONSSL'));  
  $za_contents[] = array('text' => BOX_CATALOG_REVIEWS, 'link' => zen_href_link_admin(FILENAME_REVIEWS, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_SPECIALS, 'link' => zen_href_link_admin(FILENAME_SPECIALS, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_FEATURED, 'link' => zen_href_link_admin(FILENAME_FEATURED, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_SALEMAKER, 'link' => zen_href_link_admin(FILENAME_SALEMAKER, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CATALOG_PRODUCTS_EXPECTED, 'link' => zen_href_link_admin(FILENAME_PRODUCTS_EXPECTED, '', 'NONSSL'));

if ($za_dir = @dir(DIR_WS_BOXES . 'extra_boxes')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/extras_dhtml.php$/', $zv_file)) {
      require(DIR_WS_BOXES . 'extra_boxes/' . $zv_file);
    }
  }
}

if ($za_dir = @dir(DIR_WS_BOXES . 'extra_boxes')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/catalog_dhtml.php$/', $zv_file)) {
      require(DIR_WS_BOXES . 'extra_boxes/' . $zv_file);
    }
  }
}

?>
<!-- catalog //-->
<?php
echo zen_draw_admin_box($za_heading, $za_contents);
?>
<!-- catalog_eof //-->

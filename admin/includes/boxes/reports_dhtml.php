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
//  $Id: reports_dhtml.php,v 1.2 2005/08/03 15:35:09 spiderr Exp $
//

  $za_contents = array();
  $za_heading = array();
  $za_heading = array('text' => BOX_HEADING_REPORTS, 'link' => zen_href_link_admin(FILENAME_ALT_NAV, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_REPORTS_PRODUCTS_VIEWED, 'link' => zen_href_link_admin(FILENAME_STATS_PRODUCTS_VIEWED, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_REPORTS_PRODUCTS_PURCHASED, 'link' => zen_href_link_admin(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_REPORTS_ORDERS_TOTAL, 'link' => zen_href_link_admin(FILENAME_STATS_CUSTOMERS, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_REPORTS_PRODUCTS_LOWSTOCK, 'link' => zen_href_link_admin(FILENAME_STATS_PRODUCTS_LOWSTOCK, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_REPORTS_CUSTOMERS_REFERRALS, 'link' => zen_href_link_admin(FILENAME_STATS_CUSTOMERS_REFERRALS, '', 'NONSSL'));
if ($za_dir = @dir(DIR_WS_BOXES . 'extra_boxes')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/reports_dhtml.php$/', $zv_file)) {
      require(DIR_WS_BOXES . 'extra_boxes/' . $zv_file);
    }
  }
}
?>
<!-- reports //-->
<?php
echo zen_draw_admin_box($za_heading, $za_contents);
?>
<!-- reports_eof //-->

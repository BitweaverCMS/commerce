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
//  $Id$
//
  $za_contents = array();
  $za_heading = array();
  $za_heading = array('text' => BOX_HEADING_CUSTOMERS, 'link' => zen_href_link_admin(FILENAME_ALT_NAV, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CUSTOMERS_CUSTOMERS, 'link' => zen_href_link_admin(FILENAME_CUSTOMERS, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CUSTOMERS_ORDERS, 'link' => zen_href_link_admin(FILENAME_ORDERS, '', 'NONSSL'));
if( defined( 'MODULE_PAYMENT_AMAZONMWS_STATUS' ) || MODULE_PAYMENT_AMAZONMWS_STATUS == 'True' ) {
  $za_contents[] = array('text' => tra('Amazon Orders'), 'link' => zen_href_link_admin( 'includes/modules/amazonmws/index.php', '', 'NONSSL'));
}
  $za_contents[] = array('text' => tra('Interests'), 'link' => zen_href_link_admin('interests.php', '', 'NONSSL'));
  $za_contents[] = array('text' => tra('Commissions'), 'link' => zen_href_link_admin('commissions.php', '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_LOCALIZATION_ORDERS_STATUS, 'link' => zen_href_link_admin(FILENAME_ORDERS_STATUS, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CUSTOMERS_GROUP_PRICING, 'link' => zen_href_link_admin(FILENAME_GROUP_PRICING, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_CUSTOMERS_PAYPAL, 'link' => zen_href_link_admin(FILENAME_PAYPAL, '', 'NONSSL'));
// don't Coupons unless installed
if (MODULE_ORDER_TOTAL_COUPON_STATUS=='true') {
  $za_contents[] = array('text' => BOX_COUPON_ADMIN, 'link' => zen_href_link_admin(FILENAME_COUPON_ADMIN, '', 'NONSSL'));
 } // coupons installed

// don't Gift Vouchers unless installed
if (MODULE_ORDER_TOTAL_GV_STATUS=='true') {
  $za_contents[] = array('text' => BOX_GV_ADMIN_QUEUE, 'link' => zen_href_link_admin(FILENAME_GV_QUEUE, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_GV_ADMIN_MAIL, 'link' => zen_href_link_admin(FILENAME_GV_MAIL, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_GV_ADMIN_SENT, 'link' => zen_href_link_admin(FILENAME_GV_SENT, '', 'NONSSL'));
} // gift vouchers installed

// if both are off display msg
if (!defined('MODULE_ORDER_TOTAL_COUPON_STATUS') and !defined('MODULE_ORDER_TOTAL_GV_STATUS')) {
  $za_contents[] = array('text' => NOT_INSTALLED_TEXT, 'link' => '');
} // coupons and gift vouchers not installed
if ($za_dir = @dir(DIR_WS_BOXES . 'extra_boxes')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/customers_dhtml.php$/', $zv_file)) {
      require(DIR_WS_BOXES . 'extra_boxes/' . $zv_file);
    }
  }
}
?>
<!-- customers //-->
<?php
echo zen_draw_admin_box($za_heading, $za_contents);
?>
<!-- customers_eof //-->

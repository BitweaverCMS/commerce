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
// $Id: checkout_process.php,v 1.1 2005/07/05 05:59:09 bitweaver Exp $
//

  require(DIR_WS_MODULES . 'require_languages.php');

// if the customer is not logged on, redirect them to the time out page
  if (!$_SESSION['customer_id']) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
  }

// confirm where link came from
  if (!strstr($_SERVER['HTTP_REFERER'], FILENAME_CHECKOUT_CONFIRMATION)) {
//    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT,'','SSL'));
  }

// load selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($_SESSION['payment']);
// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($_SESSION['shipping']);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;
  $order_totals = $order_total_modules->pre_confirmation_check();
  $order_totals = $order_total_modules->process();

  if (!isset($_SESSION['payment']) && !$credit_covers) {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
  }

// load the before_process function from the payment modules
  $payment_modules->before_process();

  $insert_id = $order->create($order_totals, 2);

  $payment_modules->after_order_create($insert_id);

  $order->create_add_products($insert_id);

  $order->send_order_email($insert_id, 2);

?>
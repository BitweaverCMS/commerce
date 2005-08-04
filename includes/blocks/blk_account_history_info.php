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
// $Id: blk_account_history_info.php,v 1.2 2005/08/04 07:29:20 spiderr Exp $
//
  $statuses_query = "select os.orders_status_name, osh.date_added, osh.comments
                     from   " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh
                     where      osh.orders_id = '" . (int)$_GET['order_id'] . "'
                     and        osh.orders_status_id = os.orders_status_id
                     and        os.language_id = '" . (int)$_SESSION['languages_id'] . "'
                     order by   osh.date_added";

  $statuses = $db->Execute($statuses_query);

  while (!$statuses->EOF) {

    require( DIR_FS_BLOCKS . 'tpl_block_account_history_info.php');

    $statuses->MoveNext();
  }
?>
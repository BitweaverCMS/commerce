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
// $Id: blk_account_notifications.php,v 1.5 2005/08/24 15:06:38 lsces Exp $
//
  $counter = 0;
  $products_query = "select pd.`products_id`, pd.`products_name`
                     from   " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                            " . TABLE_PRODUCTS_NOTIFICATIONS . " pn
                     where  pn.`customers_id` = '" . (int)$_SESSION['customer_id'] . "'
                     and    pn.`products_id` = pd.`products_id`
                     and    pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
                     order by pd.`products_name`";

  $products = $db->Execute($products_query);
  while (!$products->EOF) {

    require( DIR_FS_BLOCKS . 'tpl_block_account_notifications.php');

    $counter++;
    $products->MoveNext();
  }
?>
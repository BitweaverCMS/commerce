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
// $Id: whos_online.php,v 1.2 2005/07/08 06:12:28 spiderr Exp $
//
/**
 * @package ZenCart_Functions
*/

  function zen_update_whos_online() {
    global $db;

    if ($_SESSION['customer_id']) {
      $wo_customer_id = $_SESSION['customer_id'];

      $customer_query = "select customers_firstname, customers_lastname
                         from " . TABLE_CUSTOMERS . "
                         where customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $customer = $db->Execute($customer_query);

      $wo_full_name = $customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname'];
    } else {
      $wo_customer_id = '';
      $wo_full_name = 'Guest';
    }

    $wo_session_id = zen_session_id();
    $wo_ip_address = $_SERVER['REMOTE_ADDR'];
    $wo_last_page_url = $_SERVER['REQUEST_URI'];
    $wo_user_agent = zen_db_prepare_input($_SERVER['HTTP_USER_AGENT']);

    $current_time = time();
    $xx_mins_ago = ($current_time - 900);

// remove entries that have expired
    $sql = "delete from " . TABLE_WHOS_ONLINE . "
            where time_last_click < '" . $xx_mins_ago . "'";

    $db->Execute($sql);

    $stored_customer_query = "select count(*) as count
                              from " . TABLE_WHOS_ONLINE . "
                              where session_id = '" . zen_db_input($wo_session_id) . "'";

    $stored_customer = $db->Execute($stored_customer_query);

    if ($stored_customer->fields['count'] > 0) {
      $sql = "update " . TABLE_WHOS_ONLINE . "
              set customer_id = '" . (int)$wo_customer_id . "',
                  full_name = '" . zen_db_input($wo_full_name) . "',
                  ip_address = '" . zen_db_input($wo_ip_address) . "',
                  time_last_click = '" . zen_db_input($current_time) . "',
                  last_page_url = '" . zen_db_input(substr($wo_last_page_url, 0, 255)) . "',
                  host_address = '" . zen_db_input($_SESSION['customers_host_address']) . "',
                  user_agent = '" . zen_db_input(substr($wo_user_agent, 0, 255)) . "'
              where session_id = '" . zen_db_input($wo_session_id) . "'";

      $db->Execute($sql);

    } else {
      $sql = "insert into " . TABLE_WHOS_ONLINE . "
                              (customer_id, full_name, session_id, ip_address, time_entry,
                               time_last_click, last_page_url, host_address, user_agent)
              values ('" . (int)$wo_customer_id . "', '" . zen_db_input($wo_full_name) . "', '"
                         . zen_db_input($wo_session_id) . "', '" . zen_db_input($wo_ip_address)
                         . "', '" . zen_db_input($current_time) . "', '" . zen_db_input($current_time)
                         . "', '" . zen_db_input($wo_last_page_url)
                         . "', '" . zen_db_input($_SESSION['customers_host_address'])
                         . "', '" . zen_db_input($wo_user_agent)
                         . "')";

       $db->Execute($sql);
    }
  }
?>

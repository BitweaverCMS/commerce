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
// $Id: blk_address_book.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//
  $addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
                             entry_company as company, entry_street_address as street_address,
                             entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                             entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id
                      from   " . TABLE_ADDRESS_BOOK . "
                      where  customers_id = '" . (int)$_SESSION['customer_id'] . "'
                      order by firstname, lastname";

  $addresses = $db->Execute($addresses_query);

  while (!$addresses->EOF) {
    $format_id = zen_get_address_format_id($addresses->fields['country_id']);
    require($template->get_template_dir('tpl_block_address_book.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_block_address_book.php');
    $addresses->MoveNext();
  }
 

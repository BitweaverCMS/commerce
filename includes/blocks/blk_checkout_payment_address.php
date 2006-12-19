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
// $Id: blk_checkout_payment_address.php,v 1.4 2006/12/19 00:11:32 spiderr Exp $
//
      $radio_buttons = 0;

      $addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
                                 entry_company as company, entry_street_address as street_address,
                                 entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                                 entry_state as state, entry_zone_id as zone_id,
                                 entry_country_id as country_id
                          from " . TABLE_ADDRESS_BOOK . "
                          where `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";

      $addresses = $gBitDb->Execute($addresses_query);

      while (!$addresses->EOF) {
        $format_id = zen_get_address_format_id($addresses->fields['country_id']);
        require( DIR_FS_BLOCKS . 'tpl_block_checkout_payment_address.php');
        $radio_buttons++;
        $addresses->MoveNext();
      }
?>
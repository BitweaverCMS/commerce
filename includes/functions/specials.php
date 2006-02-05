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
// $Id: specials.php,v 1.5 2006/02/05 22:51:33 lsces Exp $
//
/**
 * @package ZenCart_Functions
*/

////
// Sets the status of a special product
  function zen_set_specials_status($specials_id, $status) {
    global $db;
    $sql = "update " . TABLE_SPECIALS . "
            set status = '" . $status . "', date_status_change = '".$db->NOW()."'
            where specials_id = '" . (int)$specials_id . "'";

    return $db->Execute($sql);
   }

////
// Auto expire products on special
  function zen_expire_specials() {
    global $db;

    $specials_query = "select `specials_id`, `products_id`
                       from " . TABLE_SPECIALS . "
                       where `status` = '1'
                       and (('NOW' >= `expires_date` and `expires_date` != '0001-01-01')
                       or ('NOW' < `specials_date_available` and `specials_date_available` != '0001-01-01'))";

    if( $rs = $db->Execute($specials_query) ) {
      while( $specials = $rs->FetchRow() ) {
        zen_set_specials_status($specials['specials_id'], '0');
        zen_update_products_price_sorter($specials['products_id']);
      }
    }
  }

////
// Auto start products on special
  function zen_start_specials() {
    global $db;

// turn on special if active
    $specials_query = "select `specials_id`, `products_id`
                       from " . TABLE_SPECIALS . "
                       where `status` = '0'
                       and (((`specials_date_available` <= 'NOW' and `specials_date_available` != '0001-01-01') and (`expires_date` >= 'NOW'))
                       or ((`specials_date_available` <= 'NOW' and `specials_date_available` != '0001-01-01') and (`expires_date` = '0001-01-01'))
                       or (`specials_date_available` = '0001-01-01' and `expires_date` >= 'NOW'))
                       ";

    if( $rs = $db->Execute($specials_query) ) {
      while( $specials = $rs->FetchRow() ) {
        zen_set_specials_status($specials['specials_id'], '1');
        zen_update_products_price_sorter($specials['products_id']);
      }
    }

// turn off special if not active yet
    $specials_query = "select `specials_id`, `products_id`
                       from " . TABLE_SPECIALS . "
                       where `status` = '1'
                       and ('NOW' < `specials_date_available` and `specials_date_available` != '0001-01-01')
                       ";

    if( $rs = $db->Execute($specials_query) ) {
      while( $specials = $rs->FetchRow() ) {
        zen_set_specials_status($specials['specials_id'], '0');
        zen_update_products_price_sorter($specials['products_id']);
      }
    }
  }
?>

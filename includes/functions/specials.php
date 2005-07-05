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
// $Id: specials.php,v 1.1 2005/07/05 05:59:00 bitweaver Exp $
//
/**
 * @package ZenCart_Functions
*/

////
// Sets the status of a special product
  function zen_set_specials_status($specials_id, $status) {
    global $db;
    $sql = "update " . TABLE_SPECIALS . "
            set status = '" . $status . "', date_status_change = now()
            where specials_id = '" . (int)$specials_id . "'";

    return $db->Execute($sql);
   }

////
// Auto expire products on special
  function zen_expire_specials() {
    global $db;

    $specials_query = "select specials_id, products_id
                       from " . TABLE_SPECIALS . "
                       where status = '1'
                       and ((now() >= expires_date and expires_date != '0001-01-01')
                       or (now() < specials_date_available and specials_date_available != '0001-01-01'))";

    $specials = $db->Execute($specials_query);

    if ($specials->RecordCount() > 0) {
      while (!$specials->EOF) {
        zen_set_specials_status($specials->fields['specials_id'], '0');
        zen_update_products_price_sorter($specials->fields['products_id']);
        $specials->MoveNext();
      }
    }
  }

////
// Auto start products on special
  function zen_start_specials() {
    global $db;

// turn on special if active
    $specials_query = "select specials_id, products_id
                       from " . TABLE_SPECIALS . "
                       where status = '0'
                       and (((specials_date_available <= now() and specials_date_available != '0001-01-01') and (expires_date >= now()))
                       or ((specials_date_available <= now() and specials_date_available != '0001-01-01') and (expires_date = '0001-01-01'))
                       or (specials_date_available = '0001-01-01' and expires_date >= now()))
                       ";

    $specials = $db->Execute($specials_query);

    if ($specials->RecordCount() > 0) {
      while (!$specials->EOF) {
        zen_set_specials_status($specials->fields['specials_id'], '1');
        zen_update_products_price_sorter($specials->fields['products_id']);
        $specials->MoveNext();
      }
    }

// turn off special if not active yet
    $specials_query = "select specials_id, products_id
                       from " . TABLE_SPECIALS . "
                       where status = '1'
                       and (now() < specials_date_available and specials_date_available != '0001-01-01')
                       ";

    $specials = $db->Execute($specials_query);

    if ($specials->RecordCount() > 0) {
      while (!$specials->EOF) {
        zen_set_specials_status($specials->fields['specials_id'], '0');
        zen_update_products_price_sorter($specials->fields['products_id']);
        $specials->MoveNext();
      }
    }
  }
?>
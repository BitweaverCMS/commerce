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
// $Id: salemaker.php,v 1.1 2005/07/05 05:59:00 bitweaver Exp $
//
/**
 * @package ZenCart_Functions
*/

////
// Sets the status of a salemaker sale
  function zen_set_salemaker_status($sale_id, $status) {
    global $db;
    $sql = "update " . TABLE_SALEMAKER_SALES . "
            set sale_status = '" . $status . "', sale_date_status_change = now()
            where sale_id = '" . (int)$sale_id . "'";

    return $db->Execute($sql);
   }

////
// Auto expire salemaker sales
  function zen_expire_salemaker() {
    global $db;

    $salemaker_query = "select sale_id
                       from " . TABLE_SALEMAKER_SALES . "
                       where sale_status = '1'
                       and ((now() >= sale_date_end and sale_date_end != '0001-01-01')
                       or (now() < sale_date_start and sale_date_start != '0001-01-01'))";

    $salemaker = $db->Execute($salemaker_query);

    if ($salemaker->RecordCount() > 0) {
      while (!$salemaker->EOF) {
        zen_set_salemaker_status($salemaker->fields['sale_id'], '0');
        zen_update_salemaker_product_prices($salemaker->fields['sale_id']);
        $salemaker->MoveNext();
      }
    }
  }

////
// Auto start salemaker sales
  function zen_start_salemaker() {
    global $db;

    $salemaker_query = "select sale_id
                       from " . TABLE_SALEMAKER_SALES . "
                       where sale_status = '0'
                       and (((sale_date_start <= now() and sale_date_start != '0001-01-01') and (sale_date_end >= now()))
                       or ((sale_date_start <= now() and sale_date_start != '0001-01-01') and (sale_date_end = '0001-01-01'))
                       or (sale_date_start = '0001-01-01' and sale_date_end >= now()))
                       ";

    $salemaker = $db->Execute($salemaker_query);

    if ($salemaker->RecordCount() > 0) {
      while (!$salemaker->EOF) {
        zen_set_salemaker_status($salemaker->fields['sale_id'], '1');
        zen_update_salemaker_product_prices($salemaker->fields['sale_id']);
        $salemaker->MoveNext();
      }
    }

// turn off salemaker sales if not active yet
    $salemaker_query = "select sale_id
                       from " . TABLE_SALEMAKER_SALES . "
                       where sale_status = '1'
                       and (now() < sale_date_start and sale_date_start != '0001-01-01')
                       ";

    $salemaker = $db->Execute($salemaker_query);

    if ($salemaker->RecordCount() > 0) {
      while (!$salemaker->EOF) {
        zen_set_salemaker_status($salemaker->fields['sale_id'], '0');
        zen_update_salemaker_product_prices($salemaker->fields['sale_id']);
        $salemaker->MoveNext();
      }
    }
  }
?>
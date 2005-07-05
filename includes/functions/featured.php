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
// $Id: featured.php,v 1.1 2005/07/05 05:59:00 bitweaver Exp $
//
/**
 * @package ZenCart_Functions
*/

////
// Sets the status of a featured product
  function zen_set_featured_status($featured_id, $status) {
    global $db;
    $sql = "update " . TABLE_FEATURED . "
            set status = '" . $status . "', date_status_change = now()
            where featured_id = '" . (int)$featured_id . "'";

    return $db->Execute($sql);
   }

////
// Auto expire products on featured
  function zen_expire_featured() {
    global $db;
    $featured_query = "select featured_id
                       from " . TABLE_FEATURED . "
                       where status = '1'
                       and ((now() >= expires_date and expires_date != '0001-01-01')
                       or (now() < featured_date_available and featured_date_available != '0001-01-01'))";

    $featured = $db->Execute($featured_query);

    if ($featured->RecordCount() > 0) {
      while (!$featured->EOF) {
        zen_set_featured_status($featured->fields['featured_id'], '0');
        $featured->MoveNext();
      }
    }
  }

////
// Auto start products on featured
  function zen_start_featured() {
    global $db;

    $featured_query = "select featured_id
                       from " . TABLE_FEATURED . "
                       where status = '0'
                       and (((featured_date_available <= now() and featured_date_available != '0001-01-01') and (expires_date >= now()))
                       or ((featured_date_available <= now() and featured_date_available != '0001-01-01') and (expires_date = '0001-01-01'))
                       or (featured_date_available = '0001-01-01' and expires_date >= now()))
                       ";

    $featured = $db->Execute($featured_query);

    if ($featured->RecordCount() > 0) {
      while (!$featured->EOF) {
        zen_set_featured_status($featured->fields['featured_id'], '1');
        $featured->MoveNext();
      }
    }

// turn off featured if not active yet
    $featured_query = "select featured_id
                       from " . TABLE_FEATURED . "
                       where status = '1'
                       and (now() < featured_date_available and featured_date_available != '0001-01-01')
                       ";

    $featured = $db->Execute($featured_query);

    if ($featured->RecordCount() > 0) {
      while (!$featured->EOF) {
        zen_set_featured_status($featured->fields['featured_id'], '0');
        $featured->MoveNext();
      }
    }

  }
?>
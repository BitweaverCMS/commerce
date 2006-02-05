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
// $Id: featured.php,v 1.3 2006/02/05 21:36:07 spiderr Exp $
//
/**
 * @package ZenCart_Functions
*/

////
// Sets the status of a featured product
  function zen_set_featured_status($featured_id, $status) {
    global $db;
    $sql = "update " . TABLE_FEATURED . "
            set `status` = '" . $status . "', `date_status_change` = 'NOW'
            where `featured_id` = '" . (int)$featured_id . "'";

    return $db->Execute($sql);
   }

////
// Auto expire products on featured
  function zen_expire_featured() {
    global $db;
    $featured_query = "select `featured_id`
                       from " . TABLE_FEATURED . "
                       where `status` = '1'
                       and (('NOW' >= `expires_date` and `expires_date` != '0001-01-01')
                       or ('NOW' < `featured_date_available` and `featured_date_available` != '0001-01-01'))";

    if( $rs = $db->Execute($featured_query) ) {
      while( $featured = $rs->getRow() ) {
        zen_set_featured_status($featured['featured_id'], '0');
      }
    }
  }

////
// Auto start products on featured
  function zen_start_featured() {
    global $db;

    $featured_query = "select `featured_id`
                       from " . TABLE_FEATURED . "
                       where `status` = '0'
                       and (((`featured_date_available` <= 'NOW' and `featured_date_available` != '0001-01-01') and (`expires_date` >= 'NOW'))
                       or ((`featured_date_available` <= 'NOW' and `featured_date_available` != '0001-01-01') and (`expires_date` = '0001-01-01'))
                       or (`featured_date_available` = '0001-01-01' and `expires_date` >= 'NOW'))
                       ";

    if( $rs = $db->Execute($featured_query) ) {
      while( $featured = $rs->getRow() ) {
        zen_set_featured_status($featured['featured_id'], '1');
      }
    }

// turn off featured if not active yet
    $featured_query = "select `featured_id`
                       from " . TABLE_FEATURED . "
                       where `status` = '1'
                       and ('NOW' < `featured_date_available` and `featured_date_available` != '0001-01-01')
                       ";

    if( $rs = $db->Execute($featured_query) ) {
      while( $featured = $rs->getRow() ) {
        zen_set_featured_status($featured['featured_id'], '0');
      }
    }

  }
?>

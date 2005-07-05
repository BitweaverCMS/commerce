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
// $Id: counter.php,v 1.1 2005/07/05 05:58:59 bitweaver Exp $
//

  $counter_query = "select startdate, counter from " . TABLE_COUNTER;
  $counter = $db->Execute($counter_query);
  if ($counter->RecordCount() <= 0) {
    $date_now = date('Ymd');
    $sql = "insert into " . TABLE_COUNTER . " (startdate, counter) values ('" . $date_now . "', '1')";
    $db->Execute($sql);
    $counter_startdate = $date_now;
    $counter_now = 1;
  } else {
    $counter_startdate = $counter->fields['startdate'];
    $counter_now = ($counter->fields['counter'] + 1);
    $sql = "update " . TABLE_COUNTER . " set counter = '" . $counter_now . "'";
    $db->Execute($sql);
  }

  $counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
?>
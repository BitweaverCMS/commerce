<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
// $Id: whos_online.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//

// test if box should display
  $show_whos_online= false;

  $show_whos_online= true;

// Set expiration time, default is 1200 secs (20 mins)
  $xx_mins_ago = (time() - 1200);

  $db->Execute("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

  $whos_online_query = $db->Execute("select customer_id from " . TABLE_WHOS_ONLINE);
  while (!$whos_online_query->EOF) {
    if (!$whos_online_query->fields['customer_id'] == 0) $n_members++;
    if ($whos_online_query->fields['customer_id'] == 0) $n_guests++;

    $user_total = sprintf($whos_online_query->RecordCount());

    $whos_online_query->MoveNext();
  }

  if ($user_total == 1) {
    $there_is_are = BOX_WHOS_ONLINE_THEREIS . '&nbsp;';
  } else {
    $there_is_are = BOX_WHOS_ONLINE_THEREARE . '&nbsp;';
  }

  if ($n_guests == 1) {
    $word_guest = '&nbsp;' . BOX_WHOS_ONLINE_GUEST;
  } else {
    $word_guest = '&nbsp;' . BOX_WHOS_ONLINE_GUESTS;
  }

  if ($n_members == 1) {
    $word_member = '&nbsp;' . BOX_WHOS_ONLINE_MEMBER;
  } else {
    $word_member = '&nbsp;' . BOX_WHOS_ONLINE_MEMBERS;
  }

  if (($n_guests >= 1) && ($n_members >= 1)) $word_and = '&nbsp;' . BOX_WHOS_ONLINE_AND . '&nbsp;<br />';

  $textstring = $there_is_are;
  if ($n_guests >= 1) $textstring .= $n_guests . $word_guest;

  $textstring .= $word_and;
  if ($n_members >= 1) $textstring .= $n_members . $word_member;

  $textstring .= '&nbsp;' . BOX_WHOS_ONLINE_ONLINE;


  $whos_online[] = $textstring;

// only show if either the tutorials are active or additional links are active
  if (sizeof($whos_online) > 0) {
    require($template->get_template_dir('tpl_whos_online.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_whos_online.php');

    $title =  BOX_HEADING_WHOS_ONLINE;
    $left_corner = false;
    $right_corner = false;
    $right_arrow = false;
    $title_link = false;
    require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
  }
?>
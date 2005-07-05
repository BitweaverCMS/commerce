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
// $Id: tpl_banner_box_all.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

// select banners_group to be used
  $new_banner_search = zen_build_banners_group(SHOW_BANNERS_GROUP_SET_ALL);

  // secure pages
  $my_page_ssl = $_SERVER['HTTPS'];
  switch (true) {
    case ($my_page_ssl=='on'):
      $my_banner_filter=" and banners_on_ssl= " . "'1' ";
      break;
    case ($my_page_ssl=='off' ):
      $my_banner_filter='';
      break;
  }

  $sql = "select banners_id from " . TABLE_BANNERS . " where status = '1' " . $new_banner_search . $my_banner_filter . " order by banners_sort_order";
  $banners_all = $db->Execute($sql);

  $content = '<div align="center">';

// if no active banner in the specified banner group then the box will not show
  $banner_cnt = 0;
  while (!$banners_all->EOF) {
    $banner_cnt++;
    $banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET_ALL);
    $content .= zen_display_banner('static', $banners_all->fields['banners_id']);
// add spacing between banners
    if ($banner_cnt < $banners_all->RecordCount()) {
      $content .= '<br /><br />';
    }
    $banners_all->MoveNext();
  }

  $content .= '</div>';
?>
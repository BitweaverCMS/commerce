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
// $Id: banner.php,v 1.12 2007/01/06 09:46:13 squareing Exp $
//
/**
 * @package ZenCart_Functions
*/

////
// Sets the status of a banner
  function zen_set_banner_status($banners_id, $status) {
    global $gBitDb;
    if ($status == '1') {
      $sql = "UPDATE " . TABLE_BANNERS . "
              SET `status` = '1', `date_status_change` = ".$gBitDb->qtNOW().", `date_scheduled` = ''
              WHERE `banners_id` = '" . (int)$banners_id . "'";

      return $gBitDb->Execute($sql);

    } elseif ($status == '0') {
      $sql = "UPDATE " . TABLE_BANNERS . "
              SET `status` = '0', `date_status_change` = ".$gBitDb->qtNOW()."
              WHERE `banners_id` = '" . (int)$banners_id . "'";

      return $gBitDb->Execute($sql);

    } else {
      return -1;
    }
  }

////
// Auto activate banners
  function zen_activate_banners() {
    global $gBitDb;
    $banners_query = "SELECT `banners_id`, `date_scheduled`
                      FROM " . TABLE_BANNERS . "
                      WHERE `date_scheduled` IS NOT NULL";

    $banners = $gBitDb->query($banners_query);

    if( $banners = $gBitDb->query($banners_query) ) {
      while( $row = $banners->fetchRow() ) {
        if (date('Y-m-d H:i:s') >= $row['date_scheduled'] ) {
          zen_set_banner_status( $row['banners_id'], '1' );
        }
      }
    }
  }

////
// Auto expire banners
  function zen_expire_banners() {
    global $gBitDb;
    $banners_query = "select b.`banners_id`, b.`expires_date`, b.`expires_impressions`,
                             sum(bh.`banners_shown`) as `banners_shown`
                      from " . TABLE_BANNERS . " b, " . TABLE_BANNERS_HISTORY . " bh
                      where b.`status` = '1'
                      and b.`banners_id` = bh.`banners_id`
                      group by b.`banners_id`, b.`expires_date`, b.`expires_impressions`";

    $banners = $gBitDb->Execute($banners_query);

    if( $banners = $gBitDb->Execute($banners_query) ) {
      while ( $row = $banners->fetchRow() ) {
        if (zen_not_null($row['expires_date'])) {
          if (date('Y-m-d H:i:s') >= $row['expires_date']) {
            zen_set_banner_status($row['banners_id'], '0');
          }
        } elseif (zen_not_null($row['expires_impressions'])) {
          if ( ($row['expires_impressions'] > 0) && ($row['banners_shown'] >= $row['expires_impressions']) ) {
            zen_set_banner_status($row['banners_id'], '0');
          }
        }
      }
    }
  }

////
// Display a banner from the specified group or banner id ($identifier)
  function zen_display_banner($action, $identifier) {
    global $gBitDb;

	if( !empty( $_SERVER['HTTPS'] ) && ($_SERVER['HTTPS'] =='on' ) ) {
        $my_banner_filter=" and banners_on_ssl= " . "'1' ";
	} else {
        $my_banner_filter='';
    }

    if ($action == 'dynamic') {
      $new_banner_search = zen_build_banners_group($identifier);

      $banners_query = "SELECT count(*) as `count`
                        FROM " . TABLE_BANNERS . "
                           WHERE `status` = '1' " .
                           $new_banner_search . $my_banner_filter;

      if( $bannerCount = $gBitDb->getOne($banners_query) ) {
        $banner = $gBitDb->getRow("SELECT `banners_id`, `banners_title`, `banners_image`, `banners_html_text`, `banners_open_new_windows`
                               FROM " . TABLE_BANNERS . "
                               WHERE `status` = '1' " .
                               $new_banner_search . $my_banner_filter . " order by ".$gBitDb->convertSortmode( 'random' ));

      } else {
        return '<strong>ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> No banners with group \'' . $identifier . '\' found!</strong>';
      }
    } elseif ($action == 'static') {
      if (is_object($identifier)) {
        $banner = $identifier->fields;
      } else {
        $banner_query = "select `banners_id`, `banners_title`, `banners_image`, `banners_html_text`, `banners_open_new_windows`
                         from " . TABLE_BANNERS . "
                         where `status` = '1'
                         and `banners_id` = '" . (int)$identifier . "'" . $my_banner_filter;

        if( $banner = $gBitDb->getRow($banner_query) ) {
          //return '<strong>ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> Banner with ID \'' . $identifier . '\' not found, or status inactive</strong>';
        }
      }
    } else {
      return '<strong>ZEN ERROR! (zen_display_banner(' . $action . ', ' . $identifier . ') -> Unknown $action parameter value - it must be either \'dynamic\' or \'static\'</strong>';
    }

    if (zen_not_null($banner['banners_html_text'])) {
      $banner_string = $banner['banners_html_text'];
    } else {
      if ($banner['banners_open_new_windows'] == '1') {
        $banner_string = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=banner&amp;goto=' . $banner['banners_id']) . '">' . zen_image( CommerceProduct::getImageUrl($banner['banners_image']), $banner['banners_title']) . '</a>';
      } else {
        $banner_string = '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=banner&amp;goto=' . $banner['banners_id']) . '">' . zen_image( CommerceProduct::getImageUrl($banner['banners_image']), $banner['banners_title']) . '</a>';
      }
    }
    zen_update_banner_display_count($banner['banners_id']);

    return $banner_string;
  }

////
// Check to see if a banner exists
  function zen_banner_exists($action, $identifier) {
    global $gBitDb;

	if( !empty( $_SERVER['HTTPS'] ) && ($_SERVER['HTTPS'] =='on' ) ) {
        $my_banner_filter=" and `banners_on_ssl`= " . "'1' ";
	} else {
        $my_banner_filter='';
    }

    if ($action == 'dynamic') {
      $new_banner_search = zen_build_banners_group($identifier);
      return $gBitDb->Execute("SELECT `banners_id`, `banners_title`, `banners_image`, `banners_html_text`, `banners_open_new_windows`
                           FROM " . TABLE_BANNERS . "
                               WHERE `status` = '1' " .
                               $new_banner_search . $my_banner_filter . " order by ".$gBitDb->convertSortmode( 'random' ));
    } elseif ($action == 'static') {
      $banner_query = "select `banners_id`, `banners_title`, `banners_image`, `banners_html_text`, `banners_open_new_windows`
                       from " . TABLE_BANNERS . "
                       where `status` = '1'
                       and `banners_id` = '" . (int)$identifier . "'" . $my_banner_filter;

      return $banner = $gBitDb->Execute($banner_query);
    } else {
      return false;
    }
  }

////
// Update the banner display statistics
  function zen_update_banner_display_count($banner_id) {
    global $gBitDb;
return;
    $banner_check = $gBitDb->Execute(sprintf(SQL_BANNER_CHECK_QUERY, (int)$banner_id));

    if ($banner_check->fields['count'] > 0) {

      $gBitDb->Execute(sprintf(SQL_BANNER_CHECK_UPDATE, (int)$banner_id));

    } else {
      $sql = "insert into " . TABLE_BANNERS_HISTORY . "
                     (`banners_id`, `banners_shown`, `banners_history_date`)
              values ('" . (int)$banner_id . "', 1, $gBitDb->qtNOW())";

      $gBitDb->Execute($sql);
    }
  }

////
// Update the banner click statistics
  function zen_update_banner_click_count($banner_id) {
    global $gBitDb;
    $gBitDb->Execute(sprintf(SQL_BANNER_UPDATE_CLICK_COUNT, (int)$banner_id));
  }

////
// build banner groups
  function zen_build_banners_group($selected_banners) {
    $selected_banners = explode(':', $selected_banners);
    $size = sizeof($selected_banners);
    if ($size == 1) {
      $new_banner_search = " `banners_group` = '" . $selected_banners[0] . "'";
    } else {
      for ($i=0, $n=$size; $i<$n; $i+=1) {
        $new_banner_search .= " `banners_group` = '" . $selected_banners[$i] . "'";
        if ($i+1 < $n) {
          $new_banner_search .= ' or ';
        }
      }
    }
    if ($new_banner_search != '') {
      $new_banner_search = ' and ' . $new_banner_search;
    }
    return $new_banner_search;
  }
?>

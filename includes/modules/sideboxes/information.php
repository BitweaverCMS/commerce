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
// $Id: information.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//

  unset($information);
  $information[] = '<a href="' . zen_href_link(FILENAME_SHIPPING) . '">' . BOX_INFORMATION_SHIPPING . '</a>';
  $information[] = '<a href="' . zen_href_link(FILENAME_PRIVACY) . '">' . BOX_INFORMATION_PRIVACY . '</a>';
  $information[] = '<a href="' . zen_href_link(FILENAME_CONDITIONS) . '">' . BOX_INFORMATION_CONDITIONS . '</a>';
  $information[] = '<a href="' . zen_href_link(FILENAME_CONTACT_US) . '">' . BOX_INFORMATION_CONTACT . '</a>';
  if ( ($sniffer->phpBB['db_installed_config']) && ($sniffer->phpBB['files_installed'])  && (PHPBB_LINKS_ENABLED=='true')) {
    $information[] = '<a href="' . zen_href_link($sniffer->phpBB['phpbb_url'] . FILENAME_BB_INDEX, '', 'NONSSL', '', '', true, false) . '" target="_blank">' . BOX_BBINDEX . '</a>';
// or: $sniffer->phpBB['phpbb_url'] . FILENAME_BB_INDEX
// or: str_replace(str_replace(DIR_WS_CATALOG, '', DIR_FS_CATALOG), '', DIR_WS_PHPBB)
  }

  // only show GV FAQ when installed
  if (MODULE_ORDER_TOTAL_GV_STATUS=='true') {
    $information[] = '<a href="' . zen_href_link(FILENAME_GV_FAQ) . '">' . BOX_INFORMATION_GV . '</a>';
  }

  if (SHOW_NEWSLETTER_UNSUBSCRIBE_LINK=='true') {
    $information[] = '<a href="' . zen_href_link(FILENAME_UNSUBSCRIBE) . '">' . BOX_INFORMATION_UNSUBSCRIBE . '</a>';
  }

  require($template->get_template_dir('tpl_information.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_information.php');

  $title =  BOX_HEADING_INFORMATION;
  $left_corner = false;
  $right_corner = false;
  $right_arrow = false;
  $title_link = false;

  require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
?>

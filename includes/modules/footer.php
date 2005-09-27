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
// $Id: footer.php,v 1.2 2005/09/27 22:33:53 spiderr Exp $
//

  require(DIR_FS_INCLUDES . 'counter.php');
  $time_start = explode(' ', PAGE_PARSE_START_TIME);
  $time_end = explode(' ', microtime());
  $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

  if (STORE_PAGE_PARSE_TIME == 'true') {
    error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . $_SESSION['REQUEST_URI'] . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
  }
  require($template->get_template_dir($footer_template, DIR_WS_TEMPLATE, $current_page_base,'common'). '/'. $footer_template);
?>

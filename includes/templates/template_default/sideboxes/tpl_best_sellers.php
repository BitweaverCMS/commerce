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
// $Id: tpl_best_sellers.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//
  $id = bestsellers;
  $content = "";
  $content = '<ol>';

  for ($i=1; $i<=sizeof($bestsellers_list); $i++) {

    $content .= '<li><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $bestsellers_list[$i]['id']) . '">' . $bestsellers_list[$i]['name'] . '</a></li>';


  }
  $content .= '</ol>';
?>
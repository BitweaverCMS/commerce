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
// $Id: tpl_categories.php,v 1.1 2005/07/05 05:59:27 bitweaver Exp $
//
  $id = categories;
  $content = "";
  
  $content .= '<ul>';
  
  for ($i=0;$i<sizeof($box_categories_array);$i++) {
     $content .= '<li><a ';

     if ($box_categories_array[$i]['current']) {
       $content .= 'id="active" ';
     } 
     $content .= 'href="' . zen_href_link(FILENAME_DEFAULT, $box_categories_array[$i]['path']) . '">';
	 
       $content .= $box_categories_array[$i]['name'];


     if ($box_categories_array[$i]['has_sub_cat']) {
       $content .= '-&gt;';
     }
   
     if (SHOW_COUNTS == 'true') {
       $content .= ' (' . $box_categories_array[$i]['count'] . ')';   
     }
     $content .= '</a>';

     $content .= '</li>';
  }
  $content .= '<li><a href="' . zen_href_link(FILENAME_SPECIALS) . '">' . BOX_HEADING_SPECIALS . '</a></li>';
  $content .= '<li><a href="' . zen_href_link(FILENAME_PRODUCTS_NEW) . '">' . BOX_HEADING_WHATS_NEW . '</a></li>';

  $content .= '</ul>';
?>
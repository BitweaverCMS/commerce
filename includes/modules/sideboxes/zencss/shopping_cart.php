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
// $Id: shopping_cart.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//

if (($_SESSION['cart']->count_contents() > 0)  && ($_GET['main_page'] != FILENAME_SHOPPING_CART)) { ?>

<?php  require($template->get_template_dir('tpl_shopping_cart.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_shopping_cart.php');
  $title =  BOX_HEADING_SHOPPING_CART;
  $left_corner = false;
  $right_corner = false;
  $right_arrow = false;
  $title_link = false;
  $title_link = FILENAME_SHOPPING_CART; ?>

<script language="javascript"><!--
function couponpopupWindow(url) {
 window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<?php require($template->get_template_dir('tpl_box_default.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_box_default.php'); ?>
 
<?php } ?>
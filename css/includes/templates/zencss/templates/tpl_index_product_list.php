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
// $Id: tpl_index_product_list.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<h1><?php echo $breadcrumb->last();  ?></h1>
<?php
  if ($do_filter_list) {
  $form = zen_draw_form('filter', zen_href_link(FILENAME_DEFAULT), 'get') . TEXT_SHOW;
?>
<?php echo $form ?> 
<?php
  if (!$manufacturers_set) {
    echo zen_draw_hidden_field('cPath', $cPath);
  } else {
    echo zen_draw_hidden_field('manufacturers_id', $_GET['manufacturers_id']);
  }
  if ($_GET['manufacturers_id']) {
    echo zen_draw_hidden_field('manufacturers_id', $_GET['manufacturers_id']);
  }
  echo zen_draw_hidden_field('sort', $_GET['sort']);
  echo zen_draw_hidden_field('main_page', FILENAME_DEFAULT);
  echo zen_draw_pull_down_menu('filter_id', $options, (isset($_GET['filter_id']) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"');
?></form>
<?php
  }
?>
<?php include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING); ?>
<?php
if ($error_categories==true) {
?>
<?php if (SHOW_PRODUCT_INFO_MISSING_NEW_PRODUCTS=='1') { ?>
<?php include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS); ?>
<?php } ?>
<?php if (SHOW_PRODUCT_INFO_MISSING_UPCOMING=='1') { ?>
<?php include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS); ?>
<?php } ?>
<?php } ?>

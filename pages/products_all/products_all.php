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
// $Id$
//
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
  </tr>
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
<?php
// display order dropdown
  $disp_order_default = PRODUCT_ALL_LIST_SORT_DEFAULT;
  include(DIR_WS_MODULES . 'listing_display_order.php');
?>
<?php
  $products_all_array = array();

  $products_all_query_raw = "select p.`products_id`, pd.`products_name`, p.`products_image`, p.`products_price`, p.`products_tax_class_id`, p.`products_date_added`, m.`manufacturers_name`, p.`products_model`, p.`products_quantity`, p.`products_weight` from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on (p.`manufacturers_id` = m.`manufacturers_id`), " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.`products_status` = '1' and p.`products_id` = pd.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'" . $order_by;

  $products_all_split = new splitPageResults($products_all_query_raw, MAX_DISPLAY_PRODUCTS_ALL);
?>
<?php
  $show_submit = zen_run_normal();

  if (PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART > 0 and $show_submit == 'true' and $products_all_split->number_of_rows > 0) {
    // bof: multiple products

// check how many rows
    $check_products_all = $gBitDb->Execute($products_all_split->sql_query);
    $how_many = 0;
    while (!$check_products_all->EOF) {
      if (zen_has_product_attributes($check_products_all->fields['products_id'])) {
      } else {
        $how_many++;
      }
      $check_products_all->MoveNext();
    }

    if ( (($how_many > 0 and $show_submit == 'true' and $products_all_split->number_of_rows > 0) and (PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART == 1 or  PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART == 3)) ) {
      $show_top_submit_button = 'true';
    } else {
      $show_top_submit_button = 'false';
    }
    if ( (($how_many > 0 and $show_submit == 'true' and $products_all_split->number_of_rows > 0) and (PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART >= 2)) ) {
      $show_bottom_submit_button = 'true';
    } else {
      $show_bottom_submit_button = 'false';
    }

    if ($show_top_submit_button == 'true' or $show_bottom_submit_button == 'true') {
      echo zen_draw_form('multiple_products_cart_quantity', zen_href_link(zen_get_info_page($_GET['products_id']), zen_get_all_get_params(array('action')) . 'action=multiple_products_add_product'), 'post', 'enctype="multipart/form-data"');
    }
  }
?>
<?php
  if ($show_top_submit_button == 'true') {
// only show when there is something to submit
?>
  <tr>
    <td align="right" colspan="2"><input type="submit" align="absmiddle" value="<?php echo SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART; ?>" id="submit1" name="submit1" Class="SubmitBtn"></td>
  </tr>
<?php
  } // PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART > 0
?>
<?php
  if (($products_all_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
  <tr>
    <td class="pageresults"><?php echo $products_all_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_ALL); ?></td>
    <td align="right" class="pageresults"><?php echo TEXT_RESULT_PAGE . ' ' . $products_all_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main" colspan="2"><?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCTS_ALL_LISTING)); ?></td>
  </tr>
<?php
  if (($products_all_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
  <tr>
    <td class="pageresults"><?php echo $products_all_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS_ALL); ?></td>
    <td align="right" class="pageresults"><?php echo TEXT_RESULT_PAGE . ' ' . $products_all_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></td>
  </tr>
<?php
  }
?>
<?php
  if ($show_bottom_submit_button == 'true') {
// only show when there is something to submit
?>
  <tr>
    <td align="right" colspan="2"><input type="submit" align="absmiddle" value="<?php echo SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART; ?>" id="submit1" name="submit1" Class="SubmitBtn"></td>
  </tr>
<?php
  } // PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART > 0
?>
<?php
// only end form if form is created
    if ($show_top_submit_button = 'true' or $show_bottom_submit_button = 'true') {
?>
</form>
<?php } // end if form is made ?>
</table>
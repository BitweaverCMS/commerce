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
// $Id: tpl_index_product_list.php,v 1.3 2005/09/27 22:33:56 spiderr Exp $
//
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading"><h1><?php echo $breadcrumb->last(); ?></h1></td>
<?php
  if( !empty( $do_filter_list ) ) {
  $form = zen_draw_form('filter', zen_href_link(FILENAME_DEFAULT), 'get') . TEXT_SHOW;
?>
    <td align="right" valign="bottom" class="main"><?php echo $form ?>
<?php

  if (!$getoption_set) {
    echo zen_draw_hidden_field('cPath', $cPath);
  } else {
    echo zen_draw_hidden_field($get_option_variable, $_GET[$get_option_variable]);
  }
  if (isset($_GET['typefilter'])) echo zen_draw_hidden_field('typefilter', $_GET['typefilter']);
  if ($_GET['manufacturers_id']) {
    echo zen_draw_hidden_field('manufacturers_id', $_GET['manufacturers_id']);
  }
  echo zen_draw_hidden_field('sort', $_GET['sort']);
  echo zen_draw_hidden_field('main_page', FILENAME_DEFAULT);
  echo zen_draw_pull_down_menu('filter_id', $options, (isset($_GET['filter_id']) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"');
?>
    </form></td>
<?php
  }
?>
  </tr>

<?php
// categories_description
    if ($current_categories_description != '') {
?>
  <tr>
    <td colspan="4">
      <table border="0" width="100%" cellspacing="2" cellpadding="2" class="categoriesdescription">
        <tr class="categoriesdescription">
          <td class="categoriesdescription"><?php echo $current_categories_description;  ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php } // categories_description ?>

  <tr>
    <td colspan="2" class="main"><?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING)); ?></td>
  </tr>
</table>

<?php
//// bof: categories error
if ($error_categories==true) {
  // verify lost category and reset category
  $check_category = $db->Execute("select categories_id from " . TABLE_CATEGORIES . " where categories_id='" . $current_category_id . "'");
  if ($check_category->RecordCount() == 0) {
    $new_products_category_id = '0';
    $cPath= '';
  }
?>

<?php
$show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_MISSING);

while (!$show_display_category->EOF) {
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_FEATURED_PRODUCTS') {
    include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_FEATURED_PRODUCTS_MODULE));
  }
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_SPECIALS_PRODUCTS') {
    include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_SPECIALS_INDEX));
  }
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_NEW_PRODUCTS') {
    require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_NEW_PRODUCTS));
  }
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_UPCOMING') {
    include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS));
  }
?>
<?php
  $show_display_category->MoveNext();
} // !EOF
?>
<?php } //// eof: categories error ?>

<?php
//// bof: categories
$show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_LISTING_BELOW);
if ($error_categories == false and $show_display_category->RecordCount() > 0) {
?>

<?php
  $show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_LISTING_BELOW);
  while (!$show_display_category->EOF) {
?>

<?php
    if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_FEATURED_PRODUCTS') {
      include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_FEATURED_PRODUCTS_MODULE));
    }
?>

<?php
    if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_SPECIALS_PRODUCTS') {
      include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_SPECIALS_INDEX));
    }
?>

<?php
    if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_NEW_PRODUCTS') {
      require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_NEW_PRODUCTS));
    }
?>

<?php
    if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_LISTING_BELOW_UPCOMING') {
      include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS));
    }
?>
<?php
  $show_display_category->MoveNext();
  } // !EOF
?>

<?php
} //// eof: categories
?>



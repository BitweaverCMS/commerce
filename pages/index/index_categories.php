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
// $Id: index_categories.php,v 1.1 2005/10/06 19:38:28 spiderr Exp $
//
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
<?php
if ($show_welcome == 'true') {
?>
  <tr>
    <td class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="greetUser"><?php echo zen_customer_greeting(); ?></td>
  </tr>
<?php if (TEXT_MAIN) { ?>
  <tr>
    <td class="main"><?php echo TEXT_MAIN; ?></td>
  </tr>
<?php } ?>
<?php if (TEXT_INFORMATION) { ?>
  <tr>
    <td class="plainBox"><?php echo TEXT_INFORMATION; ?></td>
  </tr>
<?php } ?>
<?php if (DEFINE_MAIN_PAGE_STATUS == '1') { ?>
  <tr>
    <td class="plainBox"><?php require($define_main_page); ?><br /></td>
  </tr>
<?php } ?>

<?php } else { ?>
  <tr>
  </tr>
  <tr>
    <td class="pageHeading"><h1><?php echo $breadcrumb->last(); ?></h1></td>
  </tr>
<?php } ?>
</table><br />
<table border="0" width="100%" cellspacing="2" cellpadding="2">
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
<?php
  require(DIR_FS_MODULES . 'pages/index/category_row.php');
?>
</tr>
</table>

<?php
$show_display_category = $db->Execute(SQL_SHOW_PRODUCT_INFO_CATEGORY);

while (!$show_display_category->EOF) {
  // //  echo 'I found ' . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS);

?>

<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_FEATURED_PRODUCTS') { ?>
<?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_FEATURED_PRODUCTS_MODULE)); ?><?php } ?>

<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_SPECIALS_PRODUCTS') { ?>
<?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_SPECIALS_INDEX)); ?><?php } ?>
<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_NEW_PRODUCTS') { ?>
<?php require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_NEW_PRODUCTS)); ?><?php } ?>
<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_UPCOMING') { ?>
<?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS)); ?><?php } ?>
<?php
  $show_display_category->MoveNext();
} // !EOF
?>

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
// $Id: tpl_product_info_noproduct.php,v 1.2 2005/07/14 04:55:16 spiderr Exp $
//
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
  </tr>
  <tr>
    <td class="main">&nbsp;</td>
  </tr>
  <tr>
    <td class="plainBox"><?php echo TEXT_PRODUCT_NOT_FOUND; ?></td>
  </tr>
  <tr>
    <td align="right"><?php zen_back_link() . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></td>
  </tr>
</table>

<?php
//// bof: missing
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
    require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_NEW_PRODUCTS));
  }
?>

<?php
  if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_MISSING_UPCOMING') {
    include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS));
  }
?>
<?php
  $show_display_category->MoveNext();
} //// eof: missing
?>

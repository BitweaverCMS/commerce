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
// $Id: tpl_products_next_previous.php,v 1.3 2005/07/26 12:31:55 spiderr Exp $
//
  /*

  WebMakers.com Added: Previous/Next through categories products
  Thanks to Nirvana, Yoja and Joachim de Boer
  Modifications: Linda McGrath osCommerce@WebMakers.com


  Can now work with categories at any depth

  */
?>
<?php
  global $products_ids, $cPath, $module_show_categories, $previous, $next_item, $next_item_image, $next_item_button, $previous_image, $previous_button, $counter, $position;
// only display when more than 1
  if ($products_ids->RecordCount() > 1) {
?>
<table border="0" align="left" width="100%">
<?php
    if ($cPath == '') {
      $cPath= zen_get_product_path((int)$_GET['products_id']);
    }

    if ($module_show_categories != '0') {
      $cPath_new = zen_get_path(zen_get_products_category_id((int)$_GET['products_id']));
      if ((zen_get_categories_image(zen_get_products_category_id((int)$_GET['products_id']))) !='') {
        switch(true) {
          case ($module_show_categories=='1'):
          $align='left';
          break;
          case ($module_show_categories=='2'):
          $align='center';
          break;
          case ($module_show_categories=='3'):
          $align='right';
          break;
        }
?>
<?php
        if ($module_next_previous !='3') {
?>
  <tr>
    <td colspan="3" align="<?php echo $align; ?>">
      <?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT, $cPath_new, 'NONSSL') . '">' . zen_image( DIR_WS_CATALOG_IMAGES . zen_get_categories_image(zen_get_products_category_id((int)$_GET['products_id'])),zen_get_categories_name(zen_get_products_category_id((int)$_GET['products_id']), $_SESSION['languages_id']),HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, ' align="middle"') . '</a>'; ?>
      <?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT, $cPath_new, 'NONSSL') . '">' . zen_get_categories_name(zen_get_products_category_id((int)$_GET['products_id']), $_SESSION['languages_id']) . '</a>'; ?>
    </td>
  </tr>
<?php
        } // don't show when top and bottom
?>
<?php
      }
    }
?>
  <tr>
    <td align="center" class="smallText" colspan="3"><?=tra( 'Product' )?> <?php echo ($position+1 . "/" . $counter); ?></td>
  </tr>
  <tr>
    <td align="center" valign="bottom" class="main"><a href="<?php echo zen_href_link(zen_get_info_page($previous), "cPath=$cPath&products_id=$previous"); ?>"><?php echo $previous_image . $previous_button; ?></a> </td>
    <td align="center" class="main"><a href="<?php echo zen_href_link(FILENAME_DEFAULT, "cPath=$cPath"); ?>"> <?php echo zen_image_button(BUTTON_IMAGE_RETURN_TO_PROD_LIST, BUTTON_RETURN_TO_PROD_LIST_ALT); ?></a> </td>
    <td align="center" valign="bottom" class="main"><a href="<?php echo zen_href_link(zen_get_info_page($next_item), "cPath=$cPath&products_id=$next_item"); ?>"><?php echo $next_item_image . $next_item_button; ?></a></td>
  </tr>
</table>
<?php
  }
?>

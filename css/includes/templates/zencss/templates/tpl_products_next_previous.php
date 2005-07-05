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
// $Id: tpl_products_next_previous.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
  /*
  WebMakers.com Added: Previous/Next through categories products
  Thanks to Nirvana, Yoja and Joachim de Boer
  Modifications: Linda McGrath osCommerce@WebMakers.com

  Can now work with categories at any depth
  */
?>
<?php
// only display when more than 1
  if ($products_ids->RecordCount() > 1) {
?>
<?php 
    if (PRODUCT_INFO_CATEGORIES !='0') {
      $cPath_new = zen_get_path(zen_get_products_category_id((int)$_GET['products_id']));
      if ((zen_get_categories_image(zen_get_products_category_id((int)$_GET['products_id']))) !='') {
        switch(true) {
          case (PRODUCT_INFO_CATEGORIES=='1'):
          $align='left';
          break;
          case (PRODUCT_INFO_CATEGORIES=='2'):
          $align='center';
          break;
          case (PRODUCT_INFO_CATEGORIES=='3'):
          $align='right';
          break;
 } ?>
 <?php
        if (PRODUCT_INFO_PREVIOUS_NEXT !='3') {
?>
      <p style="text-align:<?php echo $align; ?>">
            <?php echo '<h2>' . '<a href="' . zen_href_link(FILENAME_DEFAULT, $cPath_new, 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . zen_get_categories_image(zen_get_products_category_id((int)$_GET['products_id'])),zen_get_categories_name(zen_get_products_category_id((int)$_GET['products_id']), $_SESSION['languages_id']),HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, ' align="middle"') . '</a>'; ?>
            <?php echo zen_get_categories_name(zen_get_products_category_id((int)$_GET['products_id']), $_SESSION['languages_id']) . '</h2>'; ?>
      </p>
<?php
        } // don't show when top and bottom
?>
<?php
      }
    }
?>

<p class="center">
	<span class="right"><a href="<?php echo zen_href_link(FILENAME_PRODUCT_INFO, "products_id=$previous&cPath=$cPath"); ?>"><?php echo zen_image_button('button_prev.gif', IMAGE_BUTTON_PREVIOUS); ?></a>
	<?php echo (PREV_NEXT_PRODUCT); ?><?php echo ' '. ($position+1 . " of " . $counter); ?>
	<a href="<?php echo ' ' . zen_href_link(FILENAME_PRODUCT_INFO, "products_id=$next_item&cPath=$cPath"); ?>"> <?php echo zen_image_button('button_next.gif', IMAGE_BUTTON_NEXT); ?></a></span>
 	( <?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT, $cPath_new, 'NONSSL') . '">' . 'View all in ' . zen_get_categories_name(zen_get_products_category_id((int)$_GET['products_id']), $_SESSION['languages_id']) . '</a>'; ?> )

<a href="<?php echo zen_href_link(FILENAME_DEFAULT, "cPath=$current_category_id"); ?>"> <?php echo zen_image_button('button_return_to_product_list.gif', IMAGE_BUTTON_RETURN_TO_PRODUCT_LIST); ?></a>

</p>
<?php
  }
?>
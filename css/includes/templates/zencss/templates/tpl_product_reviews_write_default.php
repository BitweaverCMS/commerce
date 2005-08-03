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
// $Id: tpl_product_reviews_write_default.php,v 1.2 2005/08/03 13:26:00 spiderr Exp $
//
?>
<?php echo zen_draw_form('product_reviews_write', zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, 'action=process&products_id=' . $_GET['products_id']), 'post', 'onsubmit="return checkForm();"'); ?> 
<h1><?php echo $products_name; ?></h1>
<h1><?php echo $products_price; ?></h1>
<?php
  if ($messageStack->size('review_text') > 0) {
?>
<?php echo $messageStack->output('review_text'); ?> 
<?php
  }
?>
<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, zen_get_all_get_params()) . '">' . TEXT_PRODUCT_INFO . '</a>'; ?> 
<?php
  if (zen_not_null($product_info->fields['products_image'])) {
?>
<script language="javascript" type="text/javascript"><!--
document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . zen_href_link(FILENAME_POPUP_IMAGE, 'products_id=' . $product_info->fields['products_id']) . '\\\')">' . zen_image(DIR_WS_IMAGES . $product_info->fields['products_image'], addslashes($product_info->fields['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>');
//--></script>
<noscript>
<?php echo '<a href="' . zen_href_link(DIR_WS_IMAGES . $product_info->fields['products_image']) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . $product_info->fields['products_image'], $product_info->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?> 
</noscript>
<?php
  }

/* Turn off price on a Write Page
// more info in place of buy now
 if (zen_has_product_attributes($product_info->fields['products_id'])) {
   $link = '<p>' . '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product_info->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>' . '</p>';
  } else {
    $link= '<p>' . '<a href="' . zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('action')) . 'action=buy_now') . '">' . zen_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART) . '</a>' . '</p>';
  }
  echo $link;
*/
?>
<?php echo SUB_TITLE_FROM, zen_output_string_protected($customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']); ?> 
<?php echo SUB_TITLE_REVIEW; ?>
<?php echo zen_draw_textarea_field('review_text', 'soft', 60, 15); ?> 
<?php echo TEXT_NO_HTML; ?>
<?php echo SUB_TITLE_RATING . '<br />' . TEXT_BAD . ' ' . zen_draw_radio_field('rating', '1') . ' ' . zen_draw_radio_field('rating', '2') . ' ' . zen_draw_radio_field('rating', '3') . ' ' . zen_draw_radio_field('rating', '4') . ' ' . zen_draw_radio_field('rating', '5') . ' ' . TEXT_GOOD; ?> 
<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id', 'action'))) . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?>
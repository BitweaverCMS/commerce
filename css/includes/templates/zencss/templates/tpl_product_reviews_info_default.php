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
// $Id: tpl_product_reviews_info_default.php,v 1.2 2005/08/03 13:26:00 spiderr Exp $
//
?>
<h1><?php echo $products_name; ?></h1><h2><?php echo $products_price; ?></h2>
<?php
  if (zen_not_null($review_info->fields['products_image'])) {
?>
<script language="javascript" type="text/javascript"><!--
document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . zen_href_link(FILENAME_POPUP_IMAGE, 'products_id=' . $review_info->fields['products_id']) . '\\\')">' . zen_image(DIR_WS_IMAGES . $review_info->fields['products_image'], addslashes($review_info->fields['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>');
//--></script>
<noscript>
<?php echo '<a href="' . zen_href_link(DIR_WS_IMAGES . $review_info->fields['products_image']) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . $review_info->fields['products_image'], $review_info->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?> 
</noscript>
<?php
  }

// more info in place of buy now
 if (zen_has_product_attributes($review_info->fields['products_id'] )) {
//   $link = '<p>' . '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $review_info->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>' . '</p>';
    $link = '';
  } else {
    $link= '<p><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now') . '">' . zen_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART) . '</a></p>';
  }

  echo $link;
?>
<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, zen_get_all_get_params()) . '">' . TEXT_PRODUCT_INFO . '</a>'; ?> 
<span class="greetUser"><?php echo sprintf(TEXT_REVIEW_BY, zen_output_string_protected($review_info->fields['customers_name'])); ?></span> 
<p><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, zen_date_short($review_info->fields['date_added'])); ?></p> 
<p><?php echo zen_break_string(nl2br(zen_output_string_protected($review_info->fields['reviews_text'])), 60, '-<br />') . '</p><p>' . sprintf(TEXT_REVIEW_RATING, zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $review_info->fields['reviews_rating'] . '.gif', sprintf(TEXT_OF_5_STARS, $review_info->fields['reviews_rating'])), sprintf(TEXT_OF_5_STARS, $review_info->fields['reviews_rating'])) . '</p>'; ?> 
<div class="row">
	<span class="left"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, zen_get_all_get_params(array('reviews_id'))) . '">' . zen_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW) . '</a>'; ?></span> 
	<span class="right"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id'))) . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></span> 
</div>
<br class="clear" />
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
// $Id: tpl_product_reviews_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<h1><?php echo $products_name; ?></h1>
<?php echo $products_price; ?>
<?php
  if (zen_not_null($products_image)) {
?>
<script language="javascript" type="text/javascript"><!--
document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . zen_href_link(FILENAME_POPUP_IMAGE, 'pID=' . (int)$_GET['products_id']) . '\\\')">' . zen_image(DIR_WS_IMAGES . $products_image, addslashes($products_name), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>');
//--></script>
<noscript>
<?php echo '<a href="' . zen_href_link(DIR_WS_IMAGES . $products_image) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . $products_image, $products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>
</noscript>
<?php
  }

// more info in place of buy now
 if (zen_has_product_attributes((int)$_GET['products_id'])) {
//   $link = '<p>' . '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $review->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>' . '</p>';
    $link='';
  } else {
    $link= '<p>' . '<a href="' . zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('action')) . 'action=buy_now') . '">' . zen_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART) . '</a>' . '</p>';
  }

  echo $link;
?>

<?php
  $reviews_query_raw = "select r.reviews_id, left(rd.reviews_text, 100) as reviews_text,
                               r.reviews_rating, r.date_added, r.customers_name
                        from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                        where r.products_id = '" . (int)$_GET['products_id'] . "'
                        and r.reviews_id = rd.reviews_id
                        and rd.languages_id = '" . (int)$_SESSION['languages_id'] . "'
                        order by r.reviews_id desc";

  $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);

  if ($reviews_split->number_of_rows > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>
<div class="row" id="pageresultsbottom">
<span class="left"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></span>
<span class="right"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'main_page'))); ?></span>
</div>
<br class="clear" />

<?php
    }

    $reviews = $db->Execute($reviews_split->sql_query);

    while (!$reviews->EOF) {
?>

<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, zen_get_all_get_params()) . '">' . TEXT_PRODUCT_INFO . '</a>'; ?>&nbsp;|&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . (int)$_GET['products_id'] . '&reviews_id=' . $reviews->fields['reviews_id']) . '">' . TEXT_READ_REVIEW . '</a>'; ?>
<span class="greetUser"><?php echo sprintf(TEXT_REVIEW_BY, zen_output_string_protected($reviews->fields['customers_name'])); ?></span>
<?php echo sprintf(TEXT_REVIEW_DATE_ADDED, zen_date_short($reviews->fields['date_added'])); ?>
<?php echo zen_break_string(zen_output_string_protected($reviews->fields['reviews_text']), 60, '-<br />') . ((strlen($reviews->fields['reviews_text']) >= 100) ? '..' : '') . '<br /><br /><i>' . sprintf(TEXT_REVIEW_RATING, zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $reviews->fields['reviews_rating'] . '.gif', sprintf(TEXT_OF_5_STARS, $reviews->fields['reviews_rating'])), sprintf(TEXT_OF_5_STARS, $reviews->fields['reviews_rating'])) . '</i>'; ?>

<?php
      $reviews->MoveNext();
    }
?>
<?php
  } else {
?>
<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, zen_get_all_get_params()) . '">' . TEXT_PRODUCT_INFO . '</a>'; ?>
                

      <?php echo TEXT_NO_REVIEWS; ?>

<?php
  }

  if (($reviews_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>

<br class="clear" />
<div class="row" id="pageresultsbottom">
<span class="left"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></span>
<span class="right"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'main_page'))); ?></span>
</div>
<br class="clear" />
<?php
  }
?>
<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, zen_get_all_get_params()) . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?>
<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, zen_get_all_get_params()) . '">' . zen_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW) . '</a>'; ?>


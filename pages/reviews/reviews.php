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
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
  </tr>
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo $breadcrumb->last();  ?></h1></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
// if review must be approved or disabled do not show review
  $review_status = " and r.`status` = '1'";

  $reviews_query_raw = "select r.`reviews_id`, rd.`reviews_text`, r.`reviews_rating`, r.`date_added`, p.`products_id`, pd.`products_name`, p.`products_image`, r.customers_name from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.`products_status` = '1' and p.`products_id` = r.`products_id` and r.`reviews_id` = rd.`reviews_id` and p.`products_id` = pd.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and rd.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'" . $review_status . " order by r.`reviews_id` DESC";
  $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);

  if ($reviews_split->number_of_rows > 0) {
    if ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3')) {
?>
  <tr>
    <td colspan="2"><table width="100%"><tr>
      <td class="pageresults"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
      <td align="right" class="pageresults"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'main_page'))); ?></td>
    </tr></table></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
    }

	$offset = MAX_DISPLAY_NEW_REVIEWS * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
    $reviews = $gBitDb->query($reviews_split->sql_query, NULL, MAX_DISPLAY_NEW_REVIEWS, $offset );
    while (!$reviews->EOF) {
?>
  <tr>
    <td  colspan="2" align="left" class="smallText"><?php echo '<a href="' . zen_href_link(zen_get_info_page($reviews->fields['products_id']), 'products_id=' . $reviews->fields['products_id']) . '">' . TEXT_PRODUCT_INFO . '</a>'; ?>&nbsp;|&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews->fields['products_id'] . '&reviews_id=' . $reviews->fields['reviews_id']) . '">' . TEXT_READ_REVIEW . '</a>'; ?></td>
  </tr>
  <tr>
    <td colspan="2" class="pageHeading"><?php echo $reviews->fields['products_name']; ?></td>
  </tr>
  <tr>
    <td class="smallText"><?php echo sprintf(TEXT_REVIEW_BY, zen_output_string_protected($reviews->fields['customers_name'])); ?></td>
    <td class="smallText" align="left"><?php echo sprintf(TEXT_REVIEW_DATE_ADDED, zen_date_long($reviews->fields['date_added'])); ?></td>
  </tr>
  <tr>
    <td width="<?php echo SMALL_IMAGE_WIDTH + 10; ?>" align="left" valign="top" class="main"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $reviews->fields['products_id'] . '&reviews_id=' . $reviews->fields['reviews_id']) . '">' . zen_image( CommerceProduct::getImageUrl( $reviews->fields['products_id'], 'avatar' ), $reviews->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>'; ?></td>
    <td valign="top" class="main"><?php echo zen_break_string(nl2br(zen_output_string_protected(stripslashes( substr( $reviews->fields['reviews_text'], 0, 100 ) ))), 60, '-<br />') . ((strlen($reviews->fields['reviews_text']) >= 100) ? '..' : '') . '<br /><br /><i>' . sprintf(TEXT_REVIEW_RATING, zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $reviews->fields['reviews_rating'] . '.png', sprintf(TEXT_OF_5_STARS, $reviews->fields['reviews_rating'])), sprintf(TEXT_OF_5_STARS, $reviews->fields['reviews_rating'])) . '</i>'; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
      $reviews->MoveNext();
    }
?>
<?php
  } else {
?>
  <tr>
    <td class="plainBox" colspan="2"><?php echo TEXT_NO_REVIEWS; ?></td>
  </tr>
<?php
  }

  if (($reviews_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
  <tr>
    <td colspan="2"><table width="100%"><tr>
      <td class="pageresults"><?php echo $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
      <td align="right" class="pageresults"><?php echo TEXT_RESULT_PAGE . ' ' . $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'main_page'))); ?></td>
    </tr></table></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
  </tr>
</table>

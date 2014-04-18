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
  if (isset($_GET['reviews_id']) && zen_not_null($_GET['reviews_id']) && isset($_GET['products_id']) && zen_not_null($_GET['products_id'])) {

// count reviews for additional link
// if review must be approved or disabled do not show review
    $review_status = " and r.`status` = '1'";

	$reviews_count_query = "select count(*) from " . TABLE_REVIEWS . " r, "
						. TABLE_REVIEWS_DESCRIPTION . " rd
						where r.`products_id` = '" . (int)$_GET['products_id'] . "'
						and r.`reviews_id` = rd.`reviews_id`
						and rd.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'" .
						$review_status;

	$reviews_counter = $gBitDb->getOne($reviews_count_query);

// if review must be approved or disabled do not show review
	$review_status = " and r.`status` = '1'";

	$review_info_check_query = "select count(*) as `total`
                           from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                           where r.`reviews_id` = '" . (int)$_GET['reviews_id'] . "'
                           and r.`products_id` = '" . (int)$_GET['products_id'] . "'
                           and r.`reviews_id` = rd.`reviews_id`
                           and rd.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'" .
                           $review_status;

    $review_info_check = $gBitDb->Execute($review_info_check_query);

    if ($review_info_check->fields['total'] < 1) {
      zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id'))));
    }
  } else {
    zen_redirect(zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id'))));
  }

  $sql = "update " . TABLE_REVIEWS . "
          set reviews_read = reviews_read+1
          where reviews_id = '" . (int)$_GET['reviews_id'] . "'";

  $gBitDb->Execute($sql);

  $review_info_query = "select rd.reviews_text, r.reviews_rating, r.`reviews_id`, r.customers_name,
                          r.`date_added`, r.reviews_read, p.`products_id`, p.`products_price`,
                          p.`products_tax_class_id`, p.`products_image`, p.`products_model`, pd.`products_name`
                   from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                          . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                   where r.`reviews_id` = '" . (int)$_GET['reviews_id'] . "'
                   and r.`reviews_id` = rd.`reviews_id`
                   and rd.`languages_id` = '" . (int)$_SESSION['languages_id'] . "'
                   and r.`products_id` = p.`products_id`
                   and p.`products_status` = '1'
                   and p.`products_id` = pd.`products_id`
                   and pd.`language_id` = '". (int)$_SESSION['languages_id'] . "'" .
                   $review_status;

  $review_info = $gBitDb->Execute($review_info_query);

  $products_price = CommerceProduct::getDisplayPriceFromHash($review_info->fields['products_id']);

  $products_name = $review_info->fields['products_name'];

  if ($review_info->fields['products_model'] != '') {
    $products_model = '<br /><span class="smallText">[' . $review_info->fields['products_model'] . ']</span>';
  } else {
    $products_model = '';
  }

// set image
//  $products_image = $review_info->fields['products_image'];
  if ($review_info->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  } else {
    $products_image = $review_info->fields['products_image'];
  }

  require_once(DIR_FS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);
?>

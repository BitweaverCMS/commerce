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
// $Id: tpl_reviews_random.php,v 1.2 2005/07/30 03:01:59 spiderr Exp $
//
  $id = reviewsrandom;
  $content = "";
  $content .= '<div align="center"><a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $random_review_sidebox_product->fields['products_id'] . '&reviews_id=' . $random_review_sidebox_product->fields['reviews_id']) . '">' . zen_image( CommerceProduct::getImageUrl(  $random_review_sidebox_product->fields['products_id'], 'avatar' ), $random_review_sidebox_product->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div><a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $random_review_sidebox_product->fields['products_id'] . '&reviews_id=' . $random_review_sidebox_product->fields['reviews_id']) . '">' . substr( $review_box_text, 0, 75 ) . ' ...</a><p>' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $random_review_sidebox_product->fields['reviews_rating'] . '.png' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $random_review_sidebox_product->fields['reviews_rating'])) . '</p>';
?>
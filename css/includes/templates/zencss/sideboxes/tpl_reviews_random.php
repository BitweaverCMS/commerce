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
// $Id: tpl_reviews_random.php,v 1.1 2005/07/05 05:59:27 bitweaver Exp $
//
  $id = reviewsrandom;
  $content = "";
  $content .= '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $random_product->fields['products_id'] . '&reviews_id=' . $random_product->fields['reviews_id']) . '">' . zen_image(DIR_WS_IMAGES . $random_product->fields['products_image'], $random_product->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $random_product->fields['products_id'] . '&reviews_id=' . $random_product->fields['reviews_id']) . '">' . $review_box_text . ' ...</a><p>' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $random_product->fields['reviews_rating'] . '.gif' , sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $random_product->fields['reviews_rating'])) . '</p>';
?>
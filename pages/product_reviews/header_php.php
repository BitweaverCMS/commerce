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
// $Id: header_php.php,v 1.1 2005/07/05 05:59:11 bitweaver Exp $
//
  $review_query_raw = "select p.products_id, p.products_price,
                          p.products_tax_class_id, p.products_image, p.products_model, pd.products_name
                   from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                   where p.products_id = '" . (int)$_GET['products_id'] . "'
                   and p.products_status = '1'
                   and p.products_id = pd.products_id
                   and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

  $review = $db->Execute($review_query_raw);

  $products_price = zen_get_products_display_price($review->fields['products_id']);

  if (zen_not_null($review->fields['products_model'])) {
    $products_name = $review->fields['products_name'] . '<br /><span class="smallText">[' . $review->fields['products_model'] . ']</span>';
  } else {
    $products_name = $review->fields['products_name'];
  }

// set image
//  $products_image = $review->fields['products_image'];
  if ($review->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  } else {
    $products_image = $review->fields['products_image'];
  }

  require(DIR_WS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);
?>

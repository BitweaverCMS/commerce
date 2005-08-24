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
// $Id: header_php.php,v 1.5 2005/08/24 15:31:17 spiderr Exp $
//
  $_SESSION['navigation']->remove_current_page();
/*
  $products_values = $db->Execute("select pd.`products_name`, p.`products_image`
                                  from " . TABLE_PRODUCTS . " p
                                  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                  on p.`products_id` = pd.`products_id`
                                  where p.`products_status` = '1'
                                  and p.`products_id` = '" . $_GET['pID'] . "'
                                  and pd.`language_id` = '" . $_SESSION['languages_id'] . "'");

//auto replace with defined missing image
  if ($products_image == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
    $products_image = PRODUCTS_IMAGE_NO_IMAGE;
  }

$products_image_extention = substr($products_image, strrpos($products_image, '.'));
$products_image_base = ereg_replace($products_image_extention, '', $products_image);
$products_image_medium = $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extention;
$products_image_large = $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extention;

// check for a medium image else use small
if (!file_exists(DIR_WS_IMAGES . 'medium/' . $products_image_medium)) {
  $products_image_medium = DIR_WS_IMAGES . $products_image;
} else {
  $products_image_medium = DIR_WS_IMAGES . 'medium/' . $products_image_medium;
}
// check for a large image else use medium else use small
if (!file_exists(DIR_WS_IMAGES . 'large/' . $products_image_large)) {
  if (!file_exists(DIR_WS_IMAGES . 'medium/' . $products_image_medium)) {
    $products_image_large = DIR_WS_IMAGES . $products_image;
  } else {
    $products_image_large = DIR_WS_IMAGES . 'medium/' . $products_image_medium;
  }
} else {
  $products_image_large = DIR_WS_IMAGES . 'large/' . $products_image_large;
}
*/
$products_image_medium = CommerceProduct::getImageUrl( $_GET['pID'], 'medium' );
$products_image_large = CommerceProduct::getImageUrl( $_GET['pID'], 'large' );
?>
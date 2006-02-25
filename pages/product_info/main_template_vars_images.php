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
// $Id: main_template_vars_images.php,v 1.7 2006/02/25 08:51:57 spiderr Exp $
//
?>
<?php
$products_image_extention = substr($products_image, strrpos($products_image, '.'));
$products_image_base = ereg_replace($products_image_extention, '', $products_image);
$products_image_medium = $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extention;
$products_image_large = $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extention;

// check for a medium image else use small
if (!file_exists('medium/' . $products_image_medium)) {
  $products_image_medium = $products_image;
} else {
  $products_image_medium = 'medium/' . $products_image_medium;
}
// check for a large image else use medium else use small
if (!file_exists('large/' . $products_image_large)) {
  if (!file_exists('medium/' . $products_image_medium)) {
    $products_image_large = $products_image;
  } else {
    $products_image_large = 'medium/' . $products_image_medium;
  }
} else {
  $products_image_large = 'large/' . $products_image_large;
}
/*
echo
'Base ' . $products_image_base . ' - ' . $products_image_extention . '<br>' .
'Medium ' . $products_image_medium . '<br><br>' .
'Large ' . $products_image_large . '<br><br>';
*/
// to be built into a single variable string
?>
<script language="javascript" type="text/javascript"><!--
document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . zen_href_link(FILENAME_POPUP_IMAGE, 'products_id=' . $_GET['products_id']) . '\\\')">' . zen_image( CommerceProduct::getImageUrl( $_GET['products_id'], 'medium' ), addslashes($products_name)) . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>');
//--></script>
<noscript>
<?php
  echo '<a href="' . zen_href_link(FILENAME_POPUP_IMAGE, 'products_id=' . $_GET['products_id']) . '" target="_blank">' . zen_image(CommerceProduct::getImageUrl( $_GET['products_id'], 'medium' ), $products_name) . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>';
?>
</noscript>

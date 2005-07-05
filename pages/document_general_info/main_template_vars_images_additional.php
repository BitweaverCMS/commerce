<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
// $Id: main_template_vars_images_additional.php,v 1.1 2005/07/05 05:59:12 bitweaver Exp $
//
?>
<?php
if ($products_image != '') {
// prepare image name
$products_image_extention = substr($products_image, strrpos($products_image, '.'));
$products_image_base = ereg_replace($products_image_extention, '', $products_image);

// if in a subdirectory
  if (strrpos($products_image, '/')) {
    $products_image_match = substr($products_image, strrpos($products_image, '/')+1);
//echo 'TEST 1: I match ' . $products_image_match . ' - ' . $file . ' -  base ' . $products_image_base . '<br>';
    $products_image_match = ereg_replace($products_image_extention, '', $products_image_match) . '_';
    $products_image_base = $products_image_match;
  }

$products_image_directory = ereg_replace($products_image, '', substr($products_image, strrpos($products_image, '/')));
if ($products_image_directory != '') {
  $products_image_directory = DIR_WS_IMAGES . ereg_replace($products_image_directory, '', $products_image) . "/";
} else {
  $products_image_directory = DIR_WS_IMAGES;
}

// Check for additional matching images
  $file_extension = $products_image_extention;
  $products_image_match_array = array();
  if ($dir = @dir($products_image_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($products_image_directory . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
//          if(preg_match("/" . $products_image_match . "/i", $file) == '1') {
          if(preg_match("/" . $products_image_base . "/i", $file) == '1') {
            if ($file != $products_image) {
              if ($products_image_base . ereg_replace($products_image_base, '', $file) == $file) {
                $products_image_match_array[] = $file;
//  echo 'I AM A MATCH ' . $file . '<br>';
              } else {
//  echo 'I AM NOT A MATCH ' . $file . '<br>';
              }
            }
          }
        }
      }
    }
    if (sizeof($products_image_match_array)) {
      sort($products_image_match_array);
    }
    $dir->close();
  }

  if (sizeof($products_image_match_array)) {
    echo '  <tr>';
    echo '    <td colspan="2" align="center" valign="top" class="plainBox">';
    echo '<table align="center">';
    echo "\n\n" . '<tr>';
    $new_images_cnt = 0;
    for ($i = 0, $n = sizeof($products_image_match_array); $i < $n; $i++) {
      if ($new_images_cnt >= IMAGES_AUTO_ADDED) {
        echo '<tr>';
        $new_images_cnt = 0;
      }

      $new_images_cnt ++;
      $file = $products_image_match_array[$i];
      $products_image_large_additional = ereg_replace(DIR_WS_IMAGES, DIR_WS_IMAGES . 'large/', $products_image_directory) . ereg_replace($products_image_extention, '', $file) . IMAGE_SUFFIX_LARGE . $products_image_extention;
      if (!file_exists($products_image_large_additional)) {
        $products_image_large_additional = $products_image_directory . $file;
      }
//echo 'WHO AM I ' . $products_image_large_additional . ' vs ' . $products_image_large_additional;
?>
  <td align="center" class="smallText">
<script language="javascript" type="text/javascript"><!--
document.write('<?php echo (file_exists($products_image_large_additional) ? '<a href="javascript:popupWindow(\\\'' . zen_href_link(FILENAME_POPUP_IMAGE_ADDITIONAL, 'pID=' . $_GET['products_id'] . '&pic=' . $i . '&products_image_large_additional=' . $products_image_large_additional) . '\\\')">' . zen_image($products_image_directory . $file, addslashes($products_name), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>' : zen_image($products_image_directory . $file, addslashes($products_name), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"')); ?>');
//--></script>
<noscript>
<?php
//$products_image_large_additional = $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extention;
  if (file_exists($products_image_large_additional)) {
    echo '<a href="' . zen_href_link(FILENAME_POPUP_IMAGE_ADDITIONAL, 'pID=' . $_GET['products_id'] . '&pic=' . $i . '&products_image_large_additional=' . $products_image_large_additional) . '" target="_blank">' . zen_image(DIR_WS_IMAGES . $products_image, $products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />' . TEXT_CLICK_TO_ENLARGE . '</a>';
  } else {
    echo zen_image(DIR_WS_IMAGES . $products_image, $products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br />';
  }
?>
</noscript>
  </td>
<?php
      if ($new_images_cnt >= IMAGES_AUTO_ADDED or $i == ($n-1)) {
        echo '</tr>' . "\n\n";
      }
    }
    echo '</table>';
    echo '     </td>';
    echo '  </tr>';
  }
}
?>
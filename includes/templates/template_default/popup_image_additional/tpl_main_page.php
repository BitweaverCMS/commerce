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
// $Id: tpl_main_page.php,v 1.1 2005/07/05 05:59:09 bitweaver Exp $
//

?>
<body onload="resize();">
<table width="100%" align="center" valign="center" class="popupimageadditional"><tr><td class="popupimages" align="center" valign="middle">
<?php
// $products_values->fields['products_image']
  if (file_exists($_GET['products_image_large_additional'])) {
    echo '<a href="javascript:window.close()">' . zen_image($_GET['products_image_large_additional'], $products_values->fields['products_name'] . ' ' . TEXT_CLOSE_WINDOW) . '</a>';
  } else {
    echo '<a href="javascript:window.close()">' . zen_image(DIR_WS_IMAGES . $products_image, $products_values->fields['products_name'] . ' ' . TEXT_CLOSE_WINDOW) . '</a>';
  }
?>
</td></tr></table>
</body>
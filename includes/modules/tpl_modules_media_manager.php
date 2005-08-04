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
// $Id: tpl_modules_media_manager.php,v 1.1 2005/08/04 07:24:06 spiderr Exp $
//
  require(DIR_WS_MODULES . 'media_manager.php');
  if ($zv_product_has_media) {
?>
  <tr>
    <td colspan="2"><?php echo TEXT_PRODUCT_COLLECTIONS; ?></td>
  </tr>
  <tr>
    <td colspan="2"><table width="100%">
<?php
  while (list($za_media_key, $za_media) = each($za_media_manager)) {
?>
    <tr>
      <td><?php echo $za_media['text'] . '&nbsp;&nbsp;' . '<br />'; ?></td>
<?php
    $zv_counter1 = 0;
    while(list($za_clip_key, $za_clip) = each($za_media_manager[$za_media_key]['clips'])) {
?>
      <td><a href="<?php echo zen_href_link(DIR_WS_MEDIA  . $za_clip['clip_filename'], '', 'NONSSL', true, true, true); ?>" target="_BLANK"><?php echo $za_clip['clip_type']; ?></a></td>
<?php

?>
<?php
    }
?>
    </tr>
<?php
  }
?>
    </table></td>
  </tr>
<?php
  }
?>
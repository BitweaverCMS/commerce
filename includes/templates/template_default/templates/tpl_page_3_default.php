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
// $Id: tpl_page_3_default.php,v 1.2 2005/07/14 04:55:16 spiderr Exp $
//
?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
  </tr>
  <tr>
    <td class="pageHeading" align="center"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
<?php if (TEXT_INFORMATION) { ?>
  <tr>
    <td class="main"><?php echo TEXT_INFORMATION; ?></td>
  </tr>
<?php } ?>
<?php if (DEFINE_PAGE_3_STATUS == '1') { ?>
  <tr>
    <td class="plainBox"><?php require($define_page_3); ?></td>
  </tr>
<?php } ?>
  <tr>
    <td class="main"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
  </tr>
</table>
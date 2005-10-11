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
// $Id: ssl_check.php,v 1.2 2005/10/11 03:50:14 spiderr Exp $
//
?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td width="60%" class="main"><?php echo TEXT_INFORMATION; ?></td>
    <td class="plainBox" rowspan="4" valign="top">
      <?php echo BOX_INFORMATION_HEADING; ?><br /><br /><?php echo BOX_INFORMATION; ?>
    </td>
  </tr>
  <tr>
    <td class="main"><?php echo TEXT_INFORMATION_2; ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo TEXT_INFORMATION_3; ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo TEXT_INFORMATION_4; ?></td>
  </tr>
  <tr>
    <td class="main">
    </td>
    <td class="main">
    </td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo TEXT_INFORMATION_5; ?></td>
  </tr>
  <tr>
    <td class="main">
    </td>
    <td class="main">
    </td>
  </tr>
  <tr>
    <td align="right" class="main" colspan="2"><?php echo '<a href="' . FILENAME_LOGIN . '">' . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></td>
  </tr>
</table>

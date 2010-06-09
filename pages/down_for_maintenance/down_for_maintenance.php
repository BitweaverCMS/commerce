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
// | Created by: Linda McGrath zencart@WebMakers.com |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$
//
?>
<!-- body_text //-->
    <table width="100%" border="0" cellspacing="2" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
        <td align="right"><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_DOWN_FOR_MAINTENANCE, OTHER_DOWN_FOR_MAINTENANCE_ALT, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
      </tr>
      <tr>
        <td width="100%" height="10px" colspan="2"></td>
      </tr>
      <tr>
        <td class="main" colspan="2"><?php echo DOWN_FOR_MAINTENANCE_TEXT_INFORMATION; ?></td>
      </tr>
<?php if (DISPLAY_MAINTENANCE_TIME == 'true') { ?>
      <tr>
        <td class="main" colspan="2"><?php echo TEXT_MAINTENANCE_ON_AT_TIME . '<br />' . TEXT_DATE_TIME; ?></td>
      </tr>
<?php } ?>
<?php if (DISPLAY_MAINTENANCE_PERIOD == 'true') { ?>
		  <tr>
        <td class="main" colspan="2"><?php echo TEXT_MAINTENANCE_PERIOD . TEXT_MAINTENANCE_PERIOD_TIME; ?></td>
      </tr>
<?php } ?>
      <tr>
        <td align="right" class="main" colspan="2"><?php echo DOWN_FOR_MAINTENANCE_STATUS_TEXT . '<br />' . '<a href="' . zen_href_link(FILENAME_DEFAULT) . '">' . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></td>
      </tr>
    </table>
<!-- body_text_eof //-->

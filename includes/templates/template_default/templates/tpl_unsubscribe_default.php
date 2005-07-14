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
// $Id: tpl_unsubscribe_default.php,v 1.2 2005/07/14 04:55:16 spiderr Exp $
//
?>
<?php if (!isset($_GET['action']) || ($_GET['action'] != 'unsubscribe')) { ?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
  </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo ($unsubscribe_address=='') ? UNSUBSCRIBE_TEXT_NO_ADDRESS_GIVEN : UNSUBSCRIBE_TEXT_INFORMATION; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="center" class="main"><br /><?php echo '<a href="' . zen_href_link(FILENAME_UNSUBSCRIBE, 'unsubscribe_address=' . $unsubscribe_address . '&action=unsubscribe', 'NONSSL') . '">' . zen_image_button(BUTTON_IMAGE_UNSUBSCRIBE, BUTTON_UNSUBSCRIBE) . '</a>'; ?></td>
      </tr>
</table>
<?php } elseif (isset($_GET['action']) && ($_GET['action'] == 'unsubscribe')) { ?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
             <td class="main"><?php echo $status_display; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="center" class="main"><br /><?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . zen_image_button(BUTTON_IMAGE_CONTINUE_SHOPPING, BUTTON_CONTINUE_SHOPPING_ALT) . '</a>'; ?></td>
      </tr>
    </table>
<?php } else {
        zen_redirect(FILENAME_DEFAULT,'','NONSSL');
   }
?>
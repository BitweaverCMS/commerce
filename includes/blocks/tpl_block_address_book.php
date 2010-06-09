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
// $Id$
//
?>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="2" cellpadding="2">
        <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses->fields['address_book_id'], 'SSL'); ?>'">
          <td class="main">
            <strong><?php echo zen_output_string_protected($addresses->fields['firstname'] . ' ' . $addresses->fields['lastname']); ?></strong><?php if ($addresses->fields['address_book_id'] == $_SESSION['customer_default_address_id']) echo '&nbsp;<small><i>' . PRIMARY_ADDRESS . '</i></small>'; ?>
          </td>
          <td class="main" align="right">
            <?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses->fields['address_book_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a> <a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $addresses->fields['address_book_id'], 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_DELETE_SMALL, BUTTON_DELETE_SMALL_ALT) . '</a>'; ?>
          </td>
        </tr>
        <tr>
          <td class="main"><?php echo zen_address_format($format_id, $addresses->fields, true, ' ', '<br />'); ?></td>
          <td class="main">&nbsp;</td>
        </tr>
      </table>
    </td>
  </tr>

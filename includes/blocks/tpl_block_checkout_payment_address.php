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
    <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
       if ($addresses->fields['address_book_id'] == $_SESSION['sendto']) {
          echo '      <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
        } else {
          echo '      <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
        }
?>
        <td class="main" colspan="2"><?php echo zen_output_string_protected($addresses->fields['firstname'] . ' ' . $addresses->fields['lastname']); ?> [ <a href="<?=BITCOMMERCE_PKG_URL?>?main_page=address_book_process&edit=<?=$addresses->fields['address_book_id']?>">Edit</a> ]</td>
        <td class="main" align="right"><?php echo zen_draw_radio_field('address', $addresses->fields['address_book_id'], ($addresses->fields['address_book_id'] == $_SESSION['sendto'])); ?></td>
      </tr>
      <tr>
        <td class="main" colspan="2"><?php echo zen_address_format($format_id, $addresses->fields, true, ' ', ', '); ?></td>
		<td class="main" align="right"></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
 

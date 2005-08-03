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
//  $Id: products_previous_next_display.php,v 1.2 2005/08/03 15:35:12 spiderr Exp $
//

// used following load of products_previous_next.php
?>
<!-- bof: products_previous_next_display -->
  <tr>
    <td><table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td colspan="3" class="main" align="left">
          <?php echo TEXT_CATEGORIES_PRODUCTS; ?>
        </td>
      </tr>
      <tr>
        <td colspan="3" class="main" align="left"><?php echo (zen_get_categories_status($current_category_id) == '0' ? TEXT_CATEGORIES_STATUS_INFO_OFF : '') . (zen_get_products_status($products_filter) == '0' ? ' ' . TEXT_PRODUCTS_STATUS_INFO_OFF : ''); ?></td>
      </tr>
      <tr>
        <td colspan="3" class="main" align="center"><?php echo ($counter > 0 ? (PREV_NEXT_PRODUCT) . ($position+1 . "/" . $counter) : '&nbsp;'); ?></td>
      </tr>
      <tr>
        <?php if ($counter > 0 ) { ?>
          <td align="center" class="main"><a href="<?php echo zen_href_link_admin($curr_page, "products_filter=" . $previous . '&current_category_id=' . $current_category_id); ?>"><?php echo zen_image_button('button_prev.gif', BUTTON_PREVIOUS_ALT); ?></a>&nbsp;&nbsp;</td>
        <?php } ?>
        <td align="left" class="main"><?php echo zen_draw_form('new_category', $curr_page, '', 'get'); ?>&nbsp;&nbsp;<?php echo zen_draw_pull_down_menu('current_category_id', zen_get_category_tree('', '', '0', '', '', true), '', 'onChange="this.form.submit();"'); ?><?php echo zen_draw_hidden_field('products_filter', $_GET['products_filter']); echo zen_draw_hidden_field('action', 'new_cat'); ?>&nbsp;&nbsp;</form></td>
        <?php if ($counter > 0 ) { ?>
          <td align="center" class="main">&nbsp;&nbsp;<a href="<?php echo zen_href_link_admin($curr_page, "products_filter=" . $next_item . '&current_category_id=' . $current_category_id); ?>"><?php echo zen_image_button('button_next.gif', BUTTON_NEXT_ALT); ?></a></td>
        <?php } ?>
      </tr>
    </table></td>
  </tr>
<!-- eof: products_previous_next_display -->

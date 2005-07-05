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
// $Id: tpl_product_info_noproduct.php,v 1.1 2005/07/05 05:59:04 bitweaver Exp $
//
?>
<?php echo zen_draw_form('cart_quantity', zen_href_link(FILENAME_PRODUCT_INFO, zen_get_all_get_params(array('action')) . 'action=add_product')); ?>

<?php echo TEXT_PRODUCT_NOT_FOUND; ?>
<?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT) . '">' . zen_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?>
</form>
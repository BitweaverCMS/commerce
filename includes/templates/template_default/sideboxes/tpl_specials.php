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
// $Id: tpl_specials.php,v 1.3 2005/07/18 14:24:19 spiderr Exp $
//
  $id = specials;
  $content = "";
  $content = '<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product->fields["products_id"]) . '">' . zen_image(CommerceProduct::getImageUrl( $random_product->fields['products_image'] ), $random_product->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br/><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product->fields['products_id']) . '">' . $random_product->fields['products_name'] . '</a><div><span class="normalprice">' . $currencies->display_price($random_product->fields['products_price'], zen_get_tax_rate($random_product->fields['products_tax_class_id'])) . '</span><span class="specialprice">' . $currencies->display_price($random_product->fields['specials_new_products_price'], zen_get_tax_rate($random_product->fields['products_tax_class_id'])) . '</span></div>';
?>

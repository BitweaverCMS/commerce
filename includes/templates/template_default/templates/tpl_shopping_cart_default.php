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
// $Id: tpl_shopping_cart_default.php,v 1.2 2005/07/08 06:13:05 spiderr Exp $
//
?>
<?php echo zen_draw_form('cart_quantity', zen_href_link(FILENAME_SHOPPING_CART, 'action=update_product')); ?> 
 
<h1><?php echo HEADING_TITLE; ?></h1>
<?php
  if ($_SESSION['cart']->count_contents() > 0) {
?>
<table border="0"  width="100%" cellspacing="2" cellpadding="2">
  <tr> 
    <td colspan="3"> 
      <?php
    $info_box_contents = array();
    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_REMOVE);

    $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_PRODUCTS);

    $info_box_contents[0][] = array('align' => 'center',
                                    'params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_QUANTITY);

    $info_box_contents[0][] = array('align' => 'right',
                                    'params' => 'class="productListing-heading"',
                                    'text' => TABLE_HEADING_TOTAL);

    $any_out_of_stock = 0;
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        while (list($option, $value) = each($products[$i]['attributes'])) {
          echo zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
          $attributes = "select popt.products_options_name, poval.products_options_values_name,
                                     pa.options_values_price, pa.price_prefix
                         from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                         where pa.products_id = '" . zen_get_prid($products[$i]['id']) . "'
                         and pa.options_id = '" . $option . "'
                         and pa.options_id = popt.products_options_id
                         and pa.options_values_id = '" . $value . "'
                         and pa.options_values_id = poval.products_options_values_id
                         and popt.language_id = '" . $_SESSION['languages_id'] . "'
                         and poval.language_id = '" . $_SESSION['languages_id'] . "'";

          $attributes_values = $db->Execute($attributes);

          $products[$i][$option]['products_options_name'] = $attributes_values->fields['products_options_name'];
          $products[$i][$option]['options_values_id'] = $value;
          $products[$i][$option]['products_options_values_name'] = $attributes_values->fields['products_options_values_name'];
          $products[$i][$option]['options_values_price'] = $attributes_values->fields['options_values_price'];
          $products[$i][$option]['price_prefix'] = $attributes_values->fields['price_prefix'];
        }
      }
    }

    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (($i/2) == floor($i/2)) {
        $info_box_contents[] = array('params' => 'class="er"');
      } else {
        $info_box_contents[] = array('params' => 'class="or"');
      }

      $cur_row = sizeof($info_box_contents) - 1;
      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data" valign="top"',
                                             'text' => zen_draw_checkbox_field('cart_delete[]', $products[$i]['id']));

      $products_name = '<table border="0"  cellspacing="2" cellpadding="2">' .
                       '  <tr>' .
                       '    <td class="productListing-data" align="center"><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . (IMAGE_SHOPPING_CART_STATUS == 1 ? zen_image( $products[$i]['image_url'], $products[$i]['name'], IMAGE_SHOPPING_CART_WIDTH, IMAGE_SHOPPING_CART_HEIGHT) : '') . '</a></td>' .
                       '    <td class="productListing-data" valign="top"><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '"><b>' . $products[$i]['name'] . '</b></a>';

      if (STOCK_CHECK == 'true') {
        $stock_check = zen_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if (zen_not_null($stock_check)) {
          $any_out_of_stock = 1;

          $products_name .= $stock_check;
        }
      }

      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        reset($products[$i]['attributes']);
        while (list($option, $value) = each($products[$i]['attributes'])) {
          $products_name .= '<br /><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
        }
      }

      $products_name .= '    </td>' .
                        '  </tr>' .
                        '</table>';

      $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data"',
                                             'text' => $products_name);

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data" valign="top"',
                                             'text' => zen_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4"') . zen_draw_hidden_field('products_id[]', $products[$i]['id']));

      $info_box_contents[$cur_row][] = array('align' => 'right',
                                             'params' => 'class="productListing-data" valign="top"',
                                             'text' => '<b>' . $currencies->display_price($products[$i]['final_price'], zen_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</b>');
    }

    new productListingBox($info_box_contents);
?>
    </td>
  </tr>
  <tr> 
    <td colspan="3"><?php echo zen_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
  </tr>
  <tr> 
    <td colspan="2"><?php echo zen_image_submit('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART); ?></td>
    <td align="right"><?php echo SUB_TITLE_SUB_TOTAL; ?> 
      <?php echo $currencies->format($_SESSION['cart']->show_total()); ?>&nbsp;</td>
  </tr>
</table>
<?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>
<?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?> 
<?php
      } else {
?>
<?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?> 
<?php
      }
    }
?>
<br class="clear" />
<div class="row">
<span class="right">
<?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . zen_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a>'; ?></span>
</div> 
<br class="clear" />

<?php
  } else {
?>
<?php echo TEXT_CART_EMPTY; ?>
<br class="clear" />
<div class="row">
<span class="right"><?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT) . '">' . zen_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></span>
</div> 
<?php
  }
?></form>

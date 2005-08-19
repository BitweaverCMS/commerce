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
// $Id: tpl_shopping_cart.php,v 1.4 2005/08/19 13:24:31 spiderr Exp $
//
?>
<?php echo zen_draw_form('cart_quantity', zen_href_link(FILENAME_SHOPPING_CART, 'action=update_product')); ?>
<table border="0"  width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="3"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>

<tr>
  <td class="smallText" colspan="3" align="center">
  <?php
    if (SHOW_TOTALS_IN_CART == '1') {
      echo TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . TEXT_TOTAL_WEIGHT . round( $_SESSION['cart']->show_weight(), 2 ) . TEXT_SHIPPING_WEIGHT . ' ( '. round( ($_SESSION['cart']->show_weight() * .45359), 2 ) .'kb ) '. TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total()) . '<br />';
    }
    if (SHOW_TOTALS_IN_CART == '2') {
      echo TEXT_TOTAL_ITEMS . $_SESSION['cart']->count_contents() . ($_SESSION['cart']->show_weight() > 0 ? TEXT_TOTAL_WEIGHT . $_SESSION['cart']->show_weight() . TEXT_SHIPPING_WEIGHT : '') . TEXT_TOTAL_AMOUNT . $currencies->format($_SESSION['cart']->show_total()) . '<br />';
    }
    // used for testing
    if (false) {
      echo 'I SHOW IN CART MIXED Product ID 168 on ' . $_SESSION['cart']->in_cart_mixed_discount_quantity('168:d89443fdf309475ce09268e1c1db12dc') . '<br>';
      echo 'I SHOW IN CART MIXED Product ID 169 off ' . $_SESSION['cart']->in_cart_mixed_discount_quantity('169:d370574074572c79a9d0c96b069f6e32') . '<br>';

      echo 'I AM GV ONLY ' . $_SESSION['cart']->gv_only() . ' - ' . $_SESSION['cart']->get_content_type() . '<br />';
      echo 'Free Products: ' .  $_SESSION['cart']->in_cart_check('product_is_free','1') . '<br />';
      echo 'Virtual Products: ' .  $_SESSION['cart']->in_cart_check('products_virtual','1') . '<br />';
      echo 'Free Shipping Products: ' .  $_SESSION['cart']->in_cart_check('product_is_always_free_ship','1') . '<br />';
    }
  ?>
  </td>
</tr>

<?php
  if ($_SESSION['cart']->count_contents() > 0) {
?>
  <tr>
    <td colspan="3" class="main">
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
          if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
            $options_order_by= ' order by LPAD(popt.products_options_sort_order,11,"0")';
          } else {
            $options_order_by= ' order by popt.products_options_name';
          }
        while (list($option, $value) = each($products[$i]['attributes'])) {
          //clr 030714 move hidden field to if statement below
//          echo zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
          $attributes = "select popt.products_options_name, poval.products_options_values_name,
                                     pa.options_values_price, pa.price_prefix
                         from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                         where pa.products_id = '" . (int)$products[$i]['id'] . "'
                         and pa.options_id = '" . $option . "'
                         and pa.options_id = popt.products_options_id
                         and pa.options_values_id = '" . $value . "'
                         and pa.options_values_id = poval.products_options_values_id
                         and popt.language_id = '" . $_SESSION['languages_id'] . "'
                         and poval.language_id = '" . $_SESSION['languages_id'] . "'" . $options_order_by;

          $attributes_values = $db->Execute($attributes);

          //clr 030714 determine if attribute is a text attribute and assign to $attr_value temporarily
          if ($value == PRODUCTS_OPTIONS_VALUES_TEXT_ID) {
            echo zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . TEXT_PREFIX . $option . ']',  $products[$i]['attributes_values'][$option]);
            $attr_value = $products[$i]['attributes_values'][$option];
          } else {
            echo zen_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
            $attr_value = $attributes_values->fields['products_options_values_name'];
          }


          $products[$i][$option]['products_options_name'] = $attributes_values->fields['products_options_name'];
          $products[$i][$option]['options_values_id'] = $value;
          //clr 030714 assign $attr_value

//          $products[$i][$option]['products_options_values_name'] = $attributes_values->fields['products_options_values_name'];
          $products[$i][$option]['products_options_values_name'] = $attr_value ;
          $products[$i][$option]['options_values_price'] = $attributes_values->fields['options_values_price'];
          $products[$i][$option]['price_prefix'] = $attributes_values->fields['price_prefix'];
        }
      }
    }

    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (($i/2) == floor($i/2)) {
        $info_box_contents[] = array('params' => 'class="even"');
      } else {
        $info_box_contents[] = array('params' => 'class="odd"');
      }

      $cur_row = sizeof($info_box_contents) - 1;

/*
      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data" valign="top"',
                                             'text' => zen_draw_checkbox_field('cart_delete[]', $products[$i]['id']));
*/

      switch (true) {
        case (SHOW_SHOPPING_CART_DELETE == 1):
          $zc_del_button = '<a href="' . zen_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&product_id=' . $products[$i]['id']) . '"> ' . zen_image( LIBERTY_PKG_URL.'icons/delete.png', BUTTON_DELETE_SMALL_ALT) . '</a> ';
          $zc_del_checkbox = '';
          break;
        case (SHOW_SHOPPING_CART_DELETE == 2):
          $zc_del_button = '';
          $zc_del_checkbox = zen_draw_checkbox_field('cart_delete[]', $products[$i]['id']);
          break;
        default:
          $zc_del_button = '<a href="' . zen_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&product_id=' . $products[$i]['id']) . '">' . zen_image( LIBERTY_PKG_URL.'icons/delete.png', BUTTON_DELETE_SMALL_ALT, NULL, NULL, 'align=center') . '</a> ';
          $zc_del_checkbox = zen_draw_checkbox_field('cart_delete[]', $products[$i]['id']);
          break;
      }

      $info_box_contents[$cur_row][] = array('align' => 'center',
                                             'params' => 'class="productListing-data"',
                                             'text' =>   $zc_del_button.$zc_del_checkbox);

      $products_name = '<table border="0"  cellspacing="2" cellpadding="2">' .
                       '  <tr>' .
                       '    <td class="productListing-data" align="center" width="100"><a href="' . zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']) . '">' . (IMAGE_SHOPPING_CART_STATUS == 1 ? zen_image( CommerceProduct::getImageUrl( $products[$i]['id'], 'avatar' ), $products[$i]['name']) : '') . '</a></td>' .
                       '    <td class="productListing-data" valign="top"><a href="' . zen_href_link(zen_get_info_page($products[$i]['id']), 'products_id=' . $products[$i]['id']) . '"><span class="cartproductname">' . $products[$i]['name'] . '</span></a>';

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
          $products_name .= '<br /><span class="cartproductoptionname"> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</span>';
        }
      }

      $products_name .= '    </td>' .
                        '  </tr>' .
                        '</table>';

      $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data"',
                                             'text' => $products_name);

      $show_products_quantity_max = zen_get_products_quantity_order_max($products[$i]['id']);

      if ($show_products_quantity_max == 1 or zen_get_products_qty_box_status($products[$i]['id']) == 0) {
        if (SHOW_SHOPPING_CART_UPDATE == 1 or SHOW_SHOPPING_CART_UPDATE == 3) {
        $info_box_contents[$cur_row][] = array('align' => 'center',
                                               'params' => 'class="productListing-data" valign="middle"',
                                               'text' => $products[$i]['quantity'] . zen_draw_hidden_field('products_id[]', $products[$i]['id']) . zen_draw_hidden_field('cart_quantity[]', 1) . '<br />' . zen_get_products_quantity_min_units_display((int)$products[$i]['id']) . '<br />' . zen_image_submit(BUTTON_IMAGE_UPDATE_CART, BUTTON_UPDATE_CART_ALT));
        } else {
        $info_box_contents[$cur_row][] = array('align' => 'center',
                                               'params' => 'class="productListing-data" valign="middle"',
                                               'text' => $products[$i]['quantity'] . zen_draw_hidden_field('products_id[]', $products[$i]['id']) . zen_draw_hidden_field('cart_quantity[]', 1) . '<br />' . zen_get_products_quantity_min_units_display((int)$products[$i]['id']));
        }
      } else {
        if (SHOW_SHOPPING_CART_UPDATE == 1 or SHOW_SHOPPING_CART_UPDATE == 3) {
        $info_box_contents[$cur_row][] = array('align' => 'center',
                                               'params' => 'class="productListing-data" valign="middle"',
                                               'text' => zen_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4"') . '<br />' . zen_image_submit(BUTTON_IMAGE_UPDATE_CART, BUTTON_UPDATE_CART_ALT) . zen_draw_hidden_field('products_id[]', $products[$i]['id']) . '<br />' . zen_get_products_quantity_min_units_display((int)$products[$i]['id']));
                                              } else {
        $info_box_contents[$cur_row][] = array('align' => 'center',
                                               'params' => 'class="productListing-data" valign="middle"',
                                               'text' => zen_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4"') . zen_draw_hidden_field('products_id[]', $products[$i]['id']) . '<br />' . zen_get_products_quantity_min_units_display((int)$products[$i]['id']));
                                              }
      }

      $info_box_contents[$cur_row][] = array('align' => 'right',
                                             'params' => 'class="productListing-data" valign="middle"',
                                             'text' => '<span class="cartproductprice">' .
                                             $currencies->display_price($products[$i]['final_price'], zen_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) .
                                             ($products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->display_price($products[$i]['onetime_charges'], zen_get_tax_rate($products[$i]['tax_class_id']), 1) : '') .
                                             '</span>');
    }

    new productListingBox($info_box_contents);
?>
    </td>
  </tr>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td align="right" class="main" colspan="3"><?php echo SUB_TITLE_SUB_TOTAL; ?> <?php echo $currencies->format($_SESSION['cart']->show_total()); ?></td>
  </tr>
<?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="stockWarning" align="center" colspan="3"><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></td>
  </tr>
<?php
      } else {
?>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="stockWarning" align="center" colspan="3"><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></td>
  </tr>
<?php
      }
    }
?>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
  <?php
// show update cart button
  if (SHOW_SHOPPING_CART_UPDATE == 2 or SHOW_SHOPPING_CART_UPDATE == 3) {
?>
    <td class="main" align="left"><?php echo zen_image_submit(BUTTON_IMAGE_UPDATE_CART, BUTTON_UPDATE_CART_ALT); ?></td>
    <td class="main" align="center"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_CONTINUE_SHOPPING, BUTTON_CONTINUE_SHOPPING_ALT) . '</a>'; ?></td>
<?php  } else { // don't show update button below cart ?>
    <td class="main" align="left"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_CONTINUE_SHOPPING, BUTTON_CONTINUE_SHOPPING_ALT) . '</a>'; ?></td>
    <td class="main" align="left"> </td>
  <?php } // show update cart button ?>
    <td class="main" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_CHECKOUT, BUTTON_CHECKOUT_ALT) . '</a>'; ?></td>
  </tr>
</form>

<?php
    switch (true) {
      case (SHOW_SHIPPING_ESTIMATOR_BUTTON == '1'):
?>
  <tr>
    <td colspan="3"><br />
       <?php echo '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_SHIPPING_ESTIMATOR) . '\')">' . zen_image_button(BUTTON_IMAGE_SHIPPING_ESTIMATOR, BUTTON_SHIPPING_ESTIMATOR_ALT) . '</a>'; ?>
    </td>
  </tr>
<?php
      break;
      case (SHOW_SHIPPING_ESTIMATOR_BUTTON == '2'):
?>
  <tr>
    <td colspan="3"><br />
      <?php require(DIR_WS_MODULES . 'shipping_estimator.php'); ?>
    </td>
  </tr>
<?php
        break;
      }
?>
<?php
  } else {
?>
  <tr>
    <td align="center" class="plainBox" colspan="3"><?php echo TEXT_CART_EMPTY; ?></td>
  </tr>
  <tr>
    <td colspan="3" align="right" class="main"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) . '</a>'; ?></td>
  </tr>
<?php
  }
?>
</table></form>

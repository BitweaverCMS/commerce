<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
// $Id: products_all_listing.php,v 1.8 2008/07/13 07:07:46 lsces Exp $
//

?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
          <tr>
            <td colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
          </tr>
<?php
  $group_id = zen_get_configuration_key_value('PRODUCT_ALL_LIST_GROUP_ID');

  if ($products_all_split->number_of_rows > 0) {
    $products_all = $gBitDb->Execute($products_all_split->sql_query);
    while (!$products_all->EOF) {

      if (PRODUCT_ALL_LIST_IMAGE != '0') {
        $display_products_image = '<a href="' . zen_href_link(zen_get_info_page($products_all->fields['products_id']), 'products_id=' . $products_all->fields['products_id']) . '">' . zen_image( CommerceProduct::getImageUrl( $products_all->fields , 'small' ), $products_all->fields['products_name'], IMAGE_PRODUCT_ALL_LISTING_WIDTH, IMAGE_PRODUCT_ALL_LISTING_HEIGHT) . '</a>' . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_IMAGE, 3, 1));
      } else {
        $display_products_image = '';
      }
      if (PRODUCT_ALL_LIST_NAME != '0') {
        $display_products_name = '<a href="' . zen_href_link(zen_get_info_page($products_all->fields['products_id']), 'products_id=' . $products_all->fields['products_id']) . '"><strong>' . $products_all->fields['products_name'] . '</strong></a>' . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_NAME, 3, 1));
      } else {
        $display_products_name = '';
      }

      if (PRODUCT_ALL_LIST_MODEL != '0' and zen_get_show_product_switch($products_all->fields['products_id'], 'model')) {
        $display_products_model = TEXT_PRODUCTS_MODEL . $products_all->fields['products_model'] . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_MODEL, 3, 1));
      } else {
        $display_products_model = '';
      }

      if (PRODUCT_ALL_LIST_WEIGHT != '0' and zen_get_show_product_switch($products_all->fields['products_id'], 'weight')) {
        $display_products_weight = TEXT_PRODUCTS_WEIGHT . $products_all->fields['products_weight'] . TEXT_SHIPPING_WEIGHT . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_WEIGHT, 3, 1));
      } else {
        $display_products_weight = '';
      }

      if (PRODUCT_ALL_LIST_QUANTITY != '0' and zen_get_show_product_switch($products_all->fields['products_id'], 'quantity')) {
        if ($products_all->fields['products_quantity'] <= 0) {
          $display_products_quantity = TEXT_OUT_OF_STOCK . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_QUANTITY, 3, 1));
        } else {
          $display_products_quantity = TEXT_PRODUCTS_QUANTITY . $products_all->fields['products_quantity'] . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_QUANTITY, 3, 1));
        }
      } else {
        $display_products_quantity = '';
      }

      if (PRODUCT_ALL_LIST_DATE_ADDED != '0' and zen_get_show_product_switch($products_all->fields['products_id'], 'date_added')) {
        $display_products_date_added = TEXT_DATE_ADDED . ' ' . zen_date_long($products_all->fields['products_date_added']) . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_DATE_ADDED, 3, 1));
      } else {
        $display_products_date_added = '';
      }

      if (PRODUCT_ALL_LIST_MANUFACTURER != '0' and zen_get_show_product_switch($products_all->fields['products_id'], 'manufacturer')) {
        $display_products_manufacturers_name = ($products_all->fields['manufacturers_name'] != '' ? TEXT_MANUFACTURER . ' ' . $products_all->fields['manufacturers_name'] . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_MANUFACTURER, 3, 1)) : '');
      } else {
        $display_products_manufacturers_name = '';
      }

      if ((PRODUCT_ALL_LIST_PRICE != '0' and zen_get_products_allow_add_to_cart($products_all->fields['products_id']) == 'Y') and zen_check_show_prices() == 'true') {
        $products_price = CommerceProduct::getDisplayPrice($products_all->fields['products_id']);
        $display_products_price = TEXT_PRICE . ' ' . $products_price . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_LIST_PRICE, 3, 1)) . (zen_get_show_product_switch($products_all->fields['products_id'], 'ALWAYS_FREE_SHIPPING_IMAGE_SWITCH') ? (zen_get_product_is_always_free_ship($products_all->fields['products_id']) ? TEXT_PRODUCT_FREE_SHIPPING_ICON . '<br />' : '') : '');
      } else {
        $display_products_price = '';
      }

// more info in place of buy now
      if (PRODUCT_ALL_BUY_NOW != '0' and zen_get_products_allow_add_to_cart($products_all->fields['products_id']) == 'Y') {
        if (zen_has_product_attributes($products_all->fields['products_id'])) {
          $link = '<a href="' . zen_href_link(zen_get_info_page($products_all->fields['products_id']), 'products_id=' . $products_all->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        } else {
//          $link= '<a href="' . zen_href_link(FILENAME_PRODUCTS_ALL, zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $products_all->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT) . '</a>';
          if (PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART > 0) {
//            $how_many++;
            $link = TEXT_PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART . "<input type=\"text\" name=\"products_id[" . $products_all->fields['products_id'] . "]\" value=0 size=\"4\">";
          } else {
            $link = '<a href="' . zen_href_link(FILENAME_PRODUCTS_ALL, zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $products_all->fields['products_id']) . '">' . zen_image_button(BUTTON_IMAGE_BUY_NOW, BUTTON_BUY_NOW_ALT) . '</a>&nbsp;';
          }
        }

        $the_button = $link;
        $products_link = '<a href="' . zen_href_link(zen_get_info_page($products_all->fields['products_id']), 'products_id=' . $products_all->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        $display_products_button = zen_get_buy_now_button($products_all->fields['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($products_all->fields['products_id']) . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_BUY_NOW, 3, 1));
      } else {
        $link = '<a href="' . zen_href_link(zen_get_info_page($products_all->fields['products_id']), 'products_id=' . $products_all->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        $the_button = $link;
        $products_link = '<a href="' . zen_href_link(zen_get_info_page($products_all->fields['products_id']), 'products_id=' . $products_all->fields['products_id']) . '">' . MORE_INFO_TEXT . '</a>';
        $display_products_button = zen_get_buy_now_button($products_all->fields['products_id'], $the_button, $products_link) . '<br />' . zen_get_products_quantity_min_units_display($products_all->fields['products_id']) . str_repeat('<br clear="all" />', substr(PRODUCT_ALL_BUY_NOW, 3, 1));
      }

      if (PRODUCT_ALL_LIST_DESCRIPTION != '0') {
        $disp_text = zen_get_products_description($products_all->fields['products_id']);
        $disp_text = zen_clean_html($disp_text);

        $display_products_description = stripslashes(zen_trunc_string($disp_text, 150, '<a href="' . zen_href_link(zen_get_info_page($products_all->fields['products_id']), 'products_id=' . $products_all->fields['products_id']) . '"> ' . MORE_INFO_TEXT . '</a>'));
      } else {
        $display_products_description = '';
      }

?>
          <tr>
            <td width="<?php echo IMAGE_PRODUCT_ALL_LISTING_WIDTH + 10; ?>" valign="top" class="main" align="center">
              <?php
                $disp_sort_order = $gBitDb->Execute("select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id='" . $group_id . "' and (configuration_value >= 1000 and configuration_value <= 1999) order by LPAD(configuration_value,11,0)");
                while (!$disp_sort_order->EOF) {
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_IMAGE') {
                    echo $display_products_image;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_QUANTITY') {
                    echo $display_products_quantity;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_BUY_NOW') {
                    echo $display_products_button;
                  }

                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_NAME') {
                    echo $display_products_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_MODEL') {
                    echo $display_products_model;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_MANUFACTURER') {
                    echo $display_products_manufacturers_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_PRICE') {
                    echo $display_products_price;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_WEIGHT') {
                    echo $display_products_weight;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_DATE_ADDED') {
                    echo $display_products_date_added;
                  }
                  $disp_sort_order->MoveNext();
                }
              ?>
            </td>
            <td colspan="2" valign="top" class="main">
              <?php
                $disp_sort_order = $gBitDb->Execute("select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_group_id='" . $group_id . "' and (configuration_value >= 2000 and configuration_value <= 2999) order by LPAD(configuration_value,11,0)");
                while (!$disp_sort_order->EOF) {
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_IMAGE') {
                    echo $display_products_image;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_QUANTITY') {
                    echo $display_products_quantity;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_BUY_NOW') {
                    echo $display_products_button;
                  }

                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_NAME') {
                    echo $display_products_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_MODEL') {
                    echo $display_products_model;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_MANUFACTURER') {
                    echo $display_products_manufacturers_name;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_PRICE') {
                    echo $display_products_price;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_WEIGHT') {
                    echo $display_products_weight;
                  }
                  if ($disp_sort_order->fields['configuration_key'] == 'PRODUCT_ALL_LIST_DATE_ADDED') {
                    echo $display_products_date_added;
                  }
                  $disp_sort_order->MoveNext();
                }
              ?>
            </td>
          </tr>
<?php if (PRODUCT_ALL_LIST_DESCRIPTION != 0) { ?>
          <tr>
            <td colspan="3" valign="top" class="main">
              <?php
                echo $display_products_description;
              ?>
            </td>
          </tr>
<?php } ?>
          <tr>
            <td colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
          </tr>
<?php
      $products_all->MoveNext();
    }
  } else {
?>
          <tr>
            <td class="main" colspan="2"><?php echo TEXT_NO_ALL_PRODUCTS; ?></td>
          </tr>
<?php
  }
?>
</table>

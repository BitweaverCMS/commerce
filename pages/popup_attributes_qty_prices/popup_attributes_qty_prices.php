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
<body>
<table width="98%" border="2" cellpadding="2" cellspacing ="2" align="center" class="popupattributeqty">
  <tr>
    <td><table width="100%" border="0" cellpadding="2" cellspacing ="2" class="popupattributeqty">
      <tr>
        <td class="main" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CURRENT_CLOSE_WINDOW . '</a>'; ?></td>
      </tr>
      <tr>
        <td class="pageHeading"><?php echo TEXT_ATTRIBUTES_QTY_PRICES_HELP ?></td>
      </tr>
      <tr>
        <td class="main">This is a paragraph of text in the window to see what it does to the size. The current size is pretty small and hard to read</td>
      </tr>
      <tr>
        <td>
<?php
$show_onetime= 'false';
// attributes_qty_price
      if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
        $options_order_by= ' order by LPAD(popt.products_options_sort_order,11,"0")';
      } else {
        $options_order_by= ' order by popt.products_options_name';
      }

      $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
                              popt.products_options_type, popt.products_options_length, popt.products_options_comment, popt.products_options_size
              from        " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
              where           patrib.`products_id`='" . (int)$_GET['products_id'] . "'
              and             patrib.options_id = popt.products_options_id
              and             popt.`language_id` = '" . (int)$_SESSION['languages_id'] . "' " .
              $options_order_by;

      $products_options_names_lookup = $gBitDb->Execute($sql);

      while (!$products_options_names_lookup->EOF) {

        if ( PRODUCTS_OPTIONS_SORT_BY_PRICE =='1' ) {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pov.products_options_values_name';
        } else {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pa.options_values_price';
        }

        $sql = "SELECT pa.*
                FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
					INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`productions_options_values_id`=pom.`products_options_values_id`)
                WHERE pom.`products_id` = ? AND pa.`products_options_id` = ?" .
                $order_by;
        $products_options_lookup = $gBitDb->query($sql, array( (int)$_GET['products_id'], (int)$products_options_names_lookup->fields['products_options_id'] ) );
        $cnt_qty_prices= 0;
        while (!$products_options_lookup->EOF) {
          // set for attributes_qty_prices_onetime
          if ($products_options_lookup->fields['attributes_qty_prices_onetime'] != '') {
            $show_onetime= 'true';
          }

          if ($products_options_lookup->fields['attributes_qty_prices'] != '') {
            $attribute_quantity= '';
            $attribute_quantity_price= '';
            $attribute_table_cost = split("[:,]" , $products_options_lookup->fields['attributes_qty_prices']);
            $size = sizeof($attribute_table_cost);
            for ($i=0, $n=$size; $i<$n; $i+=2) {
//                $attribute_quantity .= '<td align="center">' . (($i <= 1 and $attribute_table_cost[$i] != 1) ? '1-' . $attribute_table_cost[$i] : $attribute_table_cost[$i] . '+') . '</td>';
                $zc_disp_qty = '';
                switch (true) {
                  case ($i+2==$n):
                    $zc_disp_qty = $attribute_table_cost[$i-2]+1 . '+';
                    break;
                  case ($i <= 1 and $attribute_table_cost[$i] == 1):
                    $zc_disp_qty = '1';
                    break;
                  case ($i <= 1 and $attribute_table_cost[$i] != 1):
                    $zc_disp_qty = '1-' . $attribute_table_cost[$i];
                    break;
                  case ($i > 1 and $attribute_table_cost[$i-2]+1 != $attribute_table_cost[$i]):
                    $zc_disp_qty = $attribute_table_cost[$i-2]+1 . '-' . $attribute_table_cost[$i];
                    break;
                  case ($i > 1 and $attribute_table_cost[$i-2]+1 == $attribute_table_cost[$i]):
                    $zc_disp_qty = $attribute_table_cost[$i];
                    break;
                }
//                $attribute_quantity .= '<td align="center">' . (($i <= 1 and $attribute_table_cost[$i] != 1) ? '1-' . $attribute_table_cost[$i] : $attribute_table_cost[$i-2]+1 . '-' . $attribute_table_cost[$i]) . '</td>';
                $attribute_quantity .= '<td align="center">' . $zc_disp_qty . '</td>';
                $attribute_quantity_price .= '<td align="right">' . $currencies->display_price($attribute_table_cost[$i+1], zen_get_tax_rate($_GET['products_tax_class_id'])) . '</td>';
                $cnt_qty_prices++;
            }
            echo '<table border="1" cellpadding="2" cellspacing="2">';
            echo '  <tr><td colspan="' . ($cnt_qty_prices + 1) . '">' . $products_options_names_lookup->fields['products_options_name'] . ' ' . $products_options_lookup->fields['products_options_values_name'] . '</td></tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_QTY . '</td>' . $attribute_quantity;
            echo '  </tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_PRICE . '</td>' . $attribute_quantity_price;
            echo '  </tr>';
            echo '</table>';
          }
          $products_options_lookup->MoveNext();
        }
          $products_options_names_lookup->MoveNext();
      }
?>
        </td>
      </tr>

<?php
  if ($show_onetime == 'true') {
?>

      <tr>
        <td class="pageHeading"><?php echo TEXT_ATTRIBUTES_QTY_PRICES_ONETIME_HELP ?></td>
      </tr>
      <tr>
        <td>
<?php
// attributes_qty_price_onetime
      if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
        $options_order_by= ' order by LPAD(popt.products_options_sort_order,11,"0")';
      } else {
        $options_order_by= ' order by popt.products_options_name';
      }

      $sql = "select distinct popt.products_options_id, popt.products_options_name, popt.products_options_sort_order,
                              popt.products_options_type, popt.products_options_length, popt.products_options_comment, popt.products_options_size
              from        " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib
              where           patrib.`products_id`='" . (int)$_GET['products_id'] . "'
              and             patrib.options_id = popt.products_options_id
              and             popt.`language_id` = '" . (int)$_SESSION['languages_id'] . "' " .
              $options_order_by;

      $products_options_names_lookup = $gBitDb->Execute($sql);

      while (!$products_options_names_lookup->EOF) {

        if ( PRODUCTS_OPTIONS_SORT_BY_PRICE =='1' ) {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pov.products_options_values_name';
        } else {
          $order_by= ' order by LPAD(pa.products_options_sort_order,11,"0"), pa.options_values_price';
        }

        $sql = "SELECT pa.*
                FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
					INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`productions_options_values_id`=pom.`products_options_values_id`)
                WHERE pom.`products_id` = ? AND pa.`products_options_id` = ?" .
                $order_by;
        $products_options_lookup = $gBitDb->query($sql, array( (int)$_GET['products_id'], (int)$products_options_names_lookup->fields['products_options_id'] ) );
        $cnt_qty_prices= 0;
        while (!$products_options_lookup->EOF) {
          if ($products_options_lookup->fields['attributes_qty_prices_onetime'] != '') {
            $attribute_quantity= '';
            $attribute_quantity_price= '';
            $attribute_table_cost = split("[:,]" , $products_options_lookup->fields['attributes_qty_prices_onetime']);
            $size = sizeof($attribute_table_cost);
            for ($i=0, $n=$size; $i<$n; $i+=2) {
                $attribute_quantity .= '<td align="center">' . $attribute_table_cost[$i] . '</td>';
//                $attribute_quantity_price .= '<td align="right">' . $attribute_table_cost[$i+1] . '</td>';
                $attribute_quantity_price .= '<td align="right">' . $currencies->display_price($attribute_table_cost[$i+1], zen_get_tax_rate($_GET['products_tax_class_id'])) . '</td>';
                $cnt_qty_prices++;
            }
            echo '<table border="1" cellpadding="2" cellspacing="2">';
            echo '  <tr><td colspan="' . ($cnt_qty_prices + 1) . '">' . $products_options_names_lookup->fields['products_options_name'] . ' ' . $products_options_lookup->fields['products_options_values_name'] . '</td></tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_QTY . '</td>' . $attribute_quantity;
            echo '  </tr>';
            echo '  <tr>';
            echo '    <td>' . TABLE_ATTRIBUTES_QTY_PRICE_PRICE . '</td>' . $attribute_quantity_price;
            echo '  </tr>';
            echo '</table>';
          }
          $products_options_lookup->MoveNext();
        }
          $products_options_names_lookup->MoveNext();
      }

?>
        </td>
      </tr>
<?php } // show onetime ?>

      <tr>
        <td class="main" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CURRENT_CLOSE_WINDOW . '</a>'; ?></td>
      </tr>
    </table></td>
  </tr>
</table>
</body>

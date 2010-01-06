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
//  $Id: invoice.php,v 1.13 2010/01/06 18:25:04 spiderr Exp $
//

  require('includes/application_top.php');

  
  $currencies = new currencies();

  $oID = zen_db_prepare_input($_GET['oID']);

  include(DIR_WS_CLASSES . 'order.php');
  $order = new order($oID);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"/>
<script type="text/javascript" src="includes/menu.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<!-- body_text //-->
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="pageHeading"><?php echo nl2br(STORE_NAME_ADDRESS); ?></td>
        <td class="pageHeading" align="right"></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td colspan="2"><?php echo zen_draw_separator(); ?></td>
      </tr>

<?php
      $order_check = $gBitDb->Execute("select cc_cvv, customers_name, customers_company, customers_street_address,
                                    customers_suburb, customers_city, customers_postcode,
                                    customers_state, customers_country, customers_telephone,
                                    customers_email_address, customers_address_format_id, delivery_name,
                                    delivery_company, delivery_street_address, delivery_suburb,
                                    delivery_city, delivery_postcode, delivery_state, delivery_country,
                                    delivery_address_format_id, billing_name, billing_company,
                                    billing_street_address, billing_suburb, billing_city, billing_postcode,
                                    billing_state, billing_country, billing_address_format_id,
                                    payment_method, cc_type, cc_owner, cc_number, cc_expires, currency,
                                    currency_value, date_purchased, orders_status, `last_modified`
                             from " . TABLE_ORDERS . "
                             where `orders_id` = '" . (int)$oID . "'");
  $show_customer = 'false';
  if ($order_check->fields['billing_name'] != $order_check->fields['delivery_name']) {
    $show_customer = 'true';
  }
  if ($order_check->fields['billing_street_address'] != $order_check->fields['delivery_street_address']) {
    $show_customer = 'true';
  }
  if ($show_customer == 'true') {
?>
      <tr>
        <td class="main"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
      </tr>
      <tr>
        <td class="main"><?php echo zen_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></td>
      </tr>
<?php } ?>
      <tr>
        <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo ENTRY_SOLD_TO; ?></b></td>
          </tr>
          <tr>
            <td class="main"><?php echo zen_address_format($order->customer['format_id'], $order->billing, 1, '', '<br>'); ?></td>
          </tr>
          <tr>
            <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo $order->customer['telephone']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?></td>
          </tr>
        </table></td>
        <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo ENTRY_SHIP_TO; ?></b></td>
          </tr>
          <tr>
            <td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td class="main"><b><?php echo ENTRY_ORDER_ID . $oID; ?></b></td>
  </tr>
  <tr>
    <td><table border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main"><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong></td>
        <td class="main"><?php echo zen_date_long($order->info['date_purchased']); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></td>
        <td class="main"><?php echo $order->info['payment_method']; ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
        <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
      </tr>
<?php
    foreach ( array_keys( $order->contents ) as $opid ) {
      echo '      <tr class="dataTableRow">' . "\n" .
           '        <td class="dataTableContent" valign="top" align="right">' . $order->contents[$opid]['products_quantity'] . '&nbsp;x</td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->contents[$opid]['name'];

      if (isset($order->contents[$opid]['attributes']) && (($k = sizeof($order->contents[$opid]['attributes'])) > 0)) {
        for ($j = 0; $j < $k; $j++) {
          echo '<div style="white-space:nowrap;"><small>&nbsp;<i> - ' . $order->contents[$opid]['attributes'][$j]['option'] . ': ' . $order->contents[$opid]['attributes'][$j]['value'];
          if ($order->contents[$opid]['attributes'][$j]['price'] != '0') echo ' (' . $order->contents[$opid]['attributes'][$j]['prefix'] . $currencies->format($order->contents[$opid]['attributes'][$j]['price'] * $order->contents[$opid]['products_quantity'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          if ($order->contents[$opid]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->contents[$opid]['product_is_free'] == '1') echo TEXT_INFO_ATTRIBUTE_FREE;
          echo '</i></small></div>';
        }
      }

      echo '        </td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->contents[$opid]['model'] . '</td>' . "\n";
      echo '        <td class="dataTableContent" align="right" valign="top">' . zen_display_tax_value($order->contents[$opid]['tax']) . '%</td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' .
                      $currencies->format($order->contents[$opid]['final_price'], true, $order->info['currency'], $order->info['currency_value']) .
                      ($order->contents[$opid]['onetime_charges'] != 0 ? '<br />' . $currencies->format($order->contents[$opid]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                    '</b></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' .
                      $currencies->format(zen_add_tax($order->contents[$opid]['final_price'], $order->contents[$opid]['tax']), true, $order->info['currency'], $order->info['currency_value']) .
                      ($order->contents[$opid]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->contents[$opid]['onetime_charges'], $order->contents[$opid]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                    '</b></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' .
                      $currencies->format($order->contents[$opid]['final_price'] * $order->contents[$opid]['products_quantity'], true, $order->info['currency'], $order->info['currency_value']) .
                      ($order->contents[$opid]['onetime_charges'] != 0 ? '<br />' . $currencies->format($order->contents[$opid]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                    '</b></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' .
                      $currencies->format(zen_add_tax($order->contents[$opid]['final_price'], $order->contents[$opid]['tax']) * $order->contents[$opid]['products_quantity'], true, $order->info['currency'], $order->info['currency_value']) .
                      ($order->contents[$opid]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->contents[$opid]['onetime_charges'], $order->contents[$opid]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                    '</b></td>' . "\n";
      echo '      </tr>' . "\n";
    }
?>
      <tr>
        <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
<?php
  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Text">' . $order->totals[$i]['title'] . '</td>' . "\n" .
         '            <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Amount">' . $order->totals[$i]['text'] . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
<!-- body_text_eof //-->

<br>
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

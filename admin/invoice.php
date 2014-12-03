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
//  $Id$
//

  require('includes/application_top.php');

  
  $currencies = new currencies();

  $oID = zen_db_prepare_input($_GET['oID']);

  require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php' );
  $order = new order($oID);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"/>
</head>
<body>

<!-- body_text //-->
<div class="container">
	<div class="row">
		<div class="col-sm-6">
			<header>
				<h1 class="page-heading"><?php echo ENTRY_ORDER_ID . $oID; ?></h1>
			</header>
		</div>
		<div class="col-sm-6 text-right">
			<div><strong><?php echo ENTRY_DATE_PURCHASED; ?></strong> <?php echo ($order->info['date_purchased']); ?></div>
			<div><strong><?php echo ENTRY_PAYMENT_METHOD; ?></strong> <?php echo $order->info['payment_method']; ?></div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-6">
			<div class="panel panel-default height">
				<div class="panel-body">
					<?php echo nl2br(STORE_NAME_ADDRESS); ?>
				</div>
			</div>
		</div>
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
?>
		<div class="col-xs-12 col-sm-3">
			<div class="panel panel-default height">
				<div class="panel-heading">Billing Adddres</div>
				<div class="panel-body">
					<?php echo zen_address_format($order->customer['format_id'], $order->billing, 1, '', '<br>'); ?>
					<div><?php echo $order->customer['telephone']; ?></div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-3">
			<div class="panel panel-default height">
				<div class="panel-heading">Shipping Address</div>
				<div class="panel-body">
					<?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?>
				</div>
			</div>
		</div>

	</div>
	
	<table class="table">
      <tr>
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
          echo '<div><small>&nbsp;<i> - ' . $order->contents[$opid]['attributes'][$j]['option'] . ': ' . $order->contents[$opid]['attributes'][$j]['value'];
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

  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td colspan="7" class="text-right '. str_replace('_', '-', $order->totals[$i]['class']) . '-Text">' . $order->totals[$i]['title'] . '</td>' . "\n" .
         '            <td colspan="7" class="text-right '. str_replace('_', '-', $order->totals[$i]['class']) . '-Amount">' . $order->totals[$i]['text'] . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
    </table>
<!-- body_text_eof //-->

</div>

</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

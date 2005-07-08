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
// $Id: tpl_account_history_info_default.php,v 1.2 2005/07/08 06:13:05 spiderr Exp $
//
?>

<h1><?php echo HEADING_TITLE; ?></h1>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <td class="plainBoxHeading" colspan="2" valign="bottom"> <?php echo sprintf(HEADING_ORDER_NUMBER, $_GET['order_id']); ?><br />
    <?php echo HEADING_ORDER_DATE . ' ' . zen_date_long($order->info['date_purchased']); ?>
  </td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2"> <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>
        <tr>
          <td colspan="2"><b><?php echo HEADING_PRODUCTS; ?></b></td>
          <td class="smallText" align="right"><b><?php echo HEADING_TAX; ?></b></td>
          <td class="smallText" align="right"><b><?php echo HEADING_TOTAL; ?></b></td>
        </tr>
        <?php
  } else {
?>
        <tr>
          <td colspan="3"><b><?php echo HEADING_PRODUCTS; ?></b></td>
        </tr>
        <?php
  }

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td align="right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
         '            <td valign="top">' . $order->products[$i]['name'];

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '</td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) {
      echo '            <td valign="top" align="right">' . zen_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";
    }

    echo '            <td  align="right" valign="top">' . $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
      </table>
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <?php
  for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
    echo '              <tr>' . "\n" .
         '                <td align="right" width="100%">' . $order->totals[$i]['title'] . '</td>' . "\n" .
         '                <td align="right">' . $order->totals[$i]['text'] . '</td>' . "\n" .
         '              </tr>' . "\n";
  }
?>
      </table></td>
  </tr>
  <tr>
    <td colspan="2" width="100%">
      <?php
  if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');
?>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="plainBoxHeading"> <?php echo HEADING_ORDER_HISTORY; ?>
    </td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2"> <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <?php
  $statuses_query = "select os.orders_status_name, osh.date_added, osh.comments
                     from   " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh
                     where      osh.orders_id = '" . (int)$_GET['order_id'] . "'
                     and        osh.orders_status_id = os.orders_status_id
                     and        os.language_id = '" . (int)$_SESSION['languages_id'] . "'
                     order by   osh.date_added";

  $statuses = $db->Execute($statuses_query);

  while (!$statuses->EOF) {
    echo '              <tr>' . "\n" .
         '                <td valign="top" width="70">' . zen_date_short($statuses->fields['date_added']) . '</td>' . "\n" .
         '                <td valign="top" width="70">' . $statuses->fields['orders_status_name'] . '</td>' . "\n" .
         '                <td valign="top">' . (empty($statuses->fields['comments']) ? '&nbsp;' : nl2br(zen_output_string_protected($statuses->fields['comments']))) . '</td>' . "\n" .
         '              </tr>' . "\n";

    $statuses->MoveNext();
  }
?>
      </table></td>
  </tr>
  <tr>
    <td colspan="2" class="plainBoxHeading"> <?php echo HEADING_ADDRESS_INFORMATION; ?>
    </td>
  </tr>
  <?php
  if ($order->delivery != false) {
?>
  <tr>
    <td valign="top" width="50%" class="plainBox"> <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td><b><?php echo HEADING_DELIVERY_ADDRESS; ?></b></td>
        </tr>
        <tr>
          <td><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?></td>
        </tr>
        <?php
    if (zen_not_null($order->info['shipping_method'])) {
?>
        <tr>
          <td><b><?php echo HEADING_SHIPPING_METHOD; ?></b></td>
        </tr>
        <tr>
          <td><?php echo $order->info['shipping_method']; ?></td>
        </tr>
        <?php } else { // temporary just remove these 4 lines ?>
        <tr>
          <td><b>WARNING: Missing Shipping Information</b></td>
        </tr>
        <?php
    }
?>
      </table></td>
    <?php
  }
?>
    <td valign="top" width="50%" class="plainBox"> <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td><b><?php echo HEADING_BILLING_ADDRESS; ?></b></td>
        </tr>
        <tr>
          <td><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?></td>
        </tr>
        <tr>
          <td><b><?php echo HEADING_PAYMENT_METHOD; ?></b></td>
        </tr>
        <tr>
          <td><?php echo $order->info['payment_method']; ?></td>
        </tr>
      </table></td>
  </tr>
</table>
<?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?>
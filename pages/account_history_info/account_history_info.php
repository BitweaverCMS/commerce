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
// $Id: account_history_info.php,v 1.1 2005/10/06 19:38:26 spiderr Exp $
//
?>
<div align="center">
<table  style="width:650px" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?=tra( 'Order Receipt' )?></h1></td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="2" valign="bottom">
      <?php echo sprintf(HEADING_ORDER_NUMBER, $_GET['order_id']); ?><br />
      <?php echo HEADING_ORDER_DATE . ' ' . zen_date_long($order->info['date_purchased']); ?>
    </td>
  </tr>
  <tr>
    <td valign="top" width="50%" class="plainBox">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td class="main"><strong><?php echo HEADING_BILLING_ADDRESS; ?></strong></td>
        </tr>
        <tr>
          <td class="main"><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?></td>
        </tr>
        <tr>
          <td class="main"><strong><?php echo HEADING_PAYMENT_METHOD; ?></strong></td>
        </tr>
        <tr>
          <td class="main"><?php echo $order->info['payment_method']; ?></td>
        </tr>
      </table>
    </td>
<?php
  if ($order->delivery != false) {
?>
    <td valign="top" width="50%" class="plainBox">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td class="main"><strong><?php echo HEADING_DELIVERY_ADDRESS; ?></strong></td>
        </tr>
        <tr>
          <td class="main"><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?></td>
        </tr>
<?php
    if (zen_not_null($order->info['shipping_method'])) {
?>
        <tr>
          <td class="main"><strong><?php echo HEADING_SHIPPING_METHOD; ?></strong></td>
        </tr>
        <tr>
          <td class="main"><?php echo $order->info['shipping_method']; ?></td>
        </tr>
<?php } else { // temporary just remove these 4 lines ?>
        <tr>
          <td class="main"><strong>WARNING: Missing Shipping Information</strong></td>
        </tr>
<?php
    }
?>
      </table>
    </td>
 <?php
  }
?>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>
        <tr>
          <td class="main" colspan="2"><strong><?php echo HEADING_PRODUCTS; ?></strong></td>
          <td class="smallText" align="right"><strong><?php echo HEADING_TAX; ?></strong></td>
          <td class="smallText" align="right"><strong><?php echo HEADING_TOTAL; ?></strong></td>
        </tr>
<?php
  } else {
?>
        <tr>
          <td class="main" colspan="3"><strong><?php echo HEADING_PRODUCTS; ?></strong></td>
        </tr>
<?php
  }
  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '        <tr>' . "\n" .
         '          <td class="main" align="right" valign="top" width="30">' . $order->products[$i]['quantity'] . '&nbsp;x</td>' . "\n" .
         '          <td class="main" valign="top"><a href="' . CommerceProduct::getDisplayUrl( $order->products[$i]['id'], $order->products[$i]['type_handler'] ) .'">'. $order->products[$i]['name'] . '</a>';

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '          </td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) {
      echo '        <td class="main" valign="top" align="right">' . zen_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";
    }

    echo '          <td class="main" align="right" valign="top">' .
                      $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['quantity'], true, $order->info['currency'], $order->info['currency_value']) .
                      ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '').
                    '</td>' . "\n" .
         '        </tr>' . "\n";

  }
?>
      </table>
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
    echo '        <tr>' . "\n" .
         '          <td class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Text" align="right" width="100%">' . $order->totals[$i]['title'] . '</td>' . "\n" .
         '          <td class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Amount" align="right" nowrap="nowrap">' . $order->totals[$i]['text'] . '</td>' . "\n" .
         '        </tr>' . "\n";
  }
?>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2" width="100%">
<?php
  if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');
?>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="plainBoxHeading">
      <?php echo HEADING_ORDER_HISTORY; ?>
    </td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2">
      <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php require(DIR_FS_BLOCKS . 'blk_account_history_info.php'); ?>

      </table>
    </td>
  </tr>
</table>
</div>
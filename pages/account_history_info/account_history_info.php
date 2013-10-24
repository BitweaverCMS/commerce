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
          <td class="main">
<?php 
				echo $order->info['payment_method']; 
				foreach( array( 'cc_owner', 'cc_number', 'cc_ref_id' ) as $key ) {
					$value = trim( $order->getField( $key ) );
					if( $key == 'cc_number' ) {
						$value = substr($value, 0, 6) . str_repeat('X', (strlen($value) - 6)) . substr($value, -4);
					}
					if( !empty( $value ) ) {
						echo '<div>';
						echo '<em>'.tra( ucwords( str_replace( '_', ' ', $key ) ) ).'</em> ';
						echo $value;
						echo '</div>';
					}
				}
?>
		</td>
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
  foreach( array_keys( $order->contents ) as $opid ) {
    echo '        <tr>' . "\n" .
         '          <td class="main" align="right" valign="top" width="30">' . $order->contents[$opid]['products_quantity'] . '&nbsp;x</td>' . "\n" .
         '          <td class="main" valign="top"><a href="' . CommerceProduct::getDisplayUrlFromHash( $order->contents[$opid] ) .'">'. $order->contents[$opid]['name'] . '</a>';

    if ( !empty( $order->contents[$opid]['attributes'] ) ) {
      for ($j=0, $n2=sizeof($order->contents[$opid]['attributes']); $j<$n2; $j++) {
        echo '<div style="white-space:nowrap;"><small>&nbsp;<em> - ' . $order->contents[$opid]['attributes'][$j]['option'] . ': ' . $order->contents[$opid]['attributes'][$j]['value'] . '</em></small></div>';
      }
    }

	$ordersProductFile = BITCOMMERCE_PKG_PATH.'pages/'.$order->contents[$opid]['type_handler'].'_info/orders_product_inc.php';
	if( file_exists( $ordersProductFile ) ) {
		$ordersProductHash = $order->contents[$opid];
		include( $ordersProductFile );
	}

    echo '          </td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) {
      echo '        <td class="main" valign="top" align="right">' . zen_display_tax_value($order->contents[$opid]['tax']) . '%</td>' . "\n";
    }

    echo '          <td class="main" align="right" valign="top">' .
                      $currencies->format(zen_add_tax($order->contents[$opid]['final_price'], $order->contents[$opid]['tax']) * $order->contents[$opid]['products_quantity'], true, $order->info['currency'], $order->info['currency_value']) .
                      ($order->contents[$opid]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->contents[$opid]['onetime_charges'], $order->contents[$opid]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '').
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
<?php
		if( $order->loadHistory() ) {
?>
  <tr>
    <td colspan="2">
<?php
			$gBitSmarty->assign_by_ref( 'orderHistory', $order->mHistory );
			$gBitSmarty->display( 'bitpackage:bitcommerce/account_history_info_inc.tpl' );
?>
		<a href="mailto:<?php echo STORE_OWNER_EMAIL_ADDRESS; ?>">Contact Us</a>
    </td>
  </tr>
<?php
		}
?>
</table>
</div>

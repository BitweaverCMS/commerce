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
//  $Id: orders.php,v 1.41 2006/12/11 23:21:05 spiderr Exp $
//

	define('HEADING_TITLE', 'Order'.( (!empty( $_REQUEST['oID'] )) ? ' #'.$_REQUEST['oID'] : 's'));
  require('includes/application_top.php');
  require_once( DIR_FS_CLASSES.'order.php');


  $currencies = new currencies();

	if( !empty( $_REQUEST['oID'] ) && is_numeric( $_REQUEST['oID'] ) ) {
		$oID = zen_db_prepare_input($_REQUEST['oID']);
		if( $order_exists = $db->GetOne("select orders_id from " . TABLE_ORDERS . " where `orders_id` = ?", array( $oID ) ) ) {
		    $order = new order($oID);
			$gBitSmarty->assign_by_ref( 'gBitOrder', $order );
		} else {
			$messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
		}
	}

  if( empty( $_REQUEST['action'] ) ) {
  		require_once( BITCOMMERCE_PKG_PATH.'admin/orders_list_inc.php' );
  } else {
  
    switch( $_REQUEST['action'] ) {
      case 'edit':
      // reset single download to on
        if( !empty( $_GET['download_reset_on'] ) ) {
          // adjust download_maxdays based on current date
          $check_status = $db->Execute("select customers_name, customers_email_address, orders_status,
                                      date_purchased from " . TABLE_ORDERS . "
                                      where `orders_id` = '" . $_REQUEST['oID'] . "'");
          $zc_max_days = date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS;

          $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . DOWNLOAD_MAX_COUNT . "' where `orders_id`='" . $_REQUEST['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_on'] . "'";
          $db->Execute($update_downloads_query);
          unset($_GET['download_reset_on']);

          $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_ON, 'success');
          zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'SSL'));
        }
      // reset single download to off
        if( !empty( $_GET['download_reset_off'] ) ) {
          // adjust download_maxdays based on current date
          $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='0', download_count='0' where `orders_id`='" . $_REQUEST['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_off'] . "'";
          unset($_GET['download_reset_off']);
          $db->Execute($update_downloads_query);

          $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF, 'success');
          zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'SSL'));
        }
      break;
      case 'update_order':
		// demo active test
		if (zen_admin_demo()) {
			$_GET['action']= '';
			$messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
			zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'SSL'));
		}

        if( $order->updateStatus( $_REQUEST ) ) {
         if ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {
            // adjust download_maxdays based on current date
            $zc_max_days = date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS;

            $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . DOWNLOAD_MAX_COUNT . "' where `orders_id`='" . (int)$oID . "'";
            $db->Execute($update_downloads_query);
          }
          $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'SSL'));
        break;
	  case 'delete':
		$formHash['action'] = 'deleteconfirm';
		$formHash['oID'] = $oID;
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete order #'.$oID.'?', 'error' => 'This cannot be undone!' ) );
		break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')), 'NONSSL'));
        }
		$gBitUser->verifyTicket();
        zen_remove_order($oID, $_POST['restock']);
        bit_redirect( BITCOMMERCE_PKG_URL.'admin/' );
        break;
    }
  }
  
	$gBitSystem->setOnloadScript( 'init()' );
	require(DIR_FS_ADMIN_INCLUDES . 'header.php');

	if (($_GET['action'] == 'edit') && ($order_exists == true)) {
		if ($order->info['payment_module_code']) {
		  if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php')) {
			require(DIR_FS_CATALOG_MODULES . 'payment/' . $order->info['payment_module_code'] . '.php');
			$langFile = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/payment/' . $order->info['payment_module_code'] . '.php';
			if( file_exists( $langFile ) ) {
				require( $langFile );
			}
			$module = new $order->info['payment_module_code'];
	//        echo $module->admin_notification($oID);
		  }
		}
?>
<h1 class="header"><?php echo HEADING_TITLE; ?></h1>

<table>
<tr>
<td style="width:65%;" valign="top">

<table width="100%" border="0" cellspacing="0" cellpadding="2">
	  <tr>
		<td valign="top">
			<?php echo zen_date_long($order->info['date_purchased']); ?><br/>
			<?php echo $gBitUser->getDisplayName( TRUE, $order->customer ).' (ID: '.$order->customer['user_id'].' <a href="list_orders.php?user_id='.$order->customer['user_id'].'&amp;orders_status_id=all&amp;list_filter=all">customer orders</a>)'; ?><br/>
<?php if( !empty( $order->customer['telephone'] ) ) { ?>
		<?php echo $order->customer['telephone']; ?><br/>
<?php } ?>
			<?php echo '<a href="mailto:' . $order->customer['email_address'] . '">' . $order->customer['email_address'] . '</a>'; ?><br/>
			IP: <?php echo $order->info['ip_address']; ?><br/>
			<?php echo $order->info['payment_method']; ?>
			</td>
		</td>
		<td><table style="width:auto;">
		<?php
			if (zen_not_null($order->info['cc_type']) || zen_not_null($order->info['cc_owner']) || zen_not_null($order->info['cc_number'])) {
		?>
				  <tr>
					<td colspan="2"><strong>Credit Card Info</strong></td>
				  </tr>
				  <tr>
					<td class="main">Type:</td>
					<td class="main"><?php echo $order->info['cc_type']; ?></td>
				  </tr>
				  <tr>
					<td class="main">Owner:</td>
					<td class="main"><?php echo $order->info['cc_owner']; ?></td>
				  </tr>
				  <tr>
					<td class="main">Number:</td>
					<td class="main"><?php echo $order->info['cc_number']; ?></td>
				  </tr>
				  <tr>
					<td class="main">CVV:</td>
					<td class="main"><?php echo $order->getField( 'cc_cvv' ); ?></td>
				  </tr>
				  <tr>
					<td class="main">Expires:</td>
					<td class="main"><?php echo $order->info['cc_expires']; ?></td>
				  </tr>
		<?php
			}
		?>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<strong><?php echo ENTRY_SHIPPING_ADDRESS; ?></strong><br/>
			<?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?>
		</td>
		<td valign="top"><table>
				<strong><?php echo ENTRY_BILLING_ADDRESS; ?></strong><br/>
				<?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?>
		</td>
	  </tr>
	</table></td>
  </tr>
<?php
  if (method_exists($module, 'admin_notification')) {
?>
  <tr>
	<td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
	<?php echo $module->admin_notification($oID); ?>
  </tr>
  <tr>
	<td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
<?php
}
?>
</table>

	<table>
      <tr>
		<td>
		<table class="data" border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <th colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></th>
            <th><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></th>
            <th align="right"><?php echo TABLE_HEADING_TAX; ?></th>
            <th align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></th>
            <th align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></th>
            <th align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></th>
            <th align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></th>
          </tr>
<?php
	$foreignCurrency = $order->info['currency'] != DEFAULT_CURRENCY;
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '          <tr class="dataTableRow">' . "\n" .
           '            <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['quantity'] . '&nbsp;x</td>' . "\n" .
           '            <td class="dataTableContent" valign="top"><a href="'.$gBitProduct->getDisplayUrl( $order->products[$i]['products_id'] ).'">' . $order->products[$i]['name'].'</a>';

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['final_price'] * $order->products[$i]['quantity'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          if( !empty( $order->products[$i]['attributes'][$j]['product_attribute_is_free'] ) && $order->products[$i]['attributes'][$j]['product_attribute_is_free'] == '1' and $order->products[$i]['product_is_free'] == '1') echo TEXT_INFO_ATTRIBUTE_FREE;
          echo '</i></small></nobr>';
        }
      }

      echo '            </td>' . "\n" .
           '            <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' . zen_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' .
                          $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' .
                          $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' .
                          $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['quantity'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format($order->products[$i]['onetime_charges'], true, $order->info['currency'], $order->info['currency_value']) : '') .
                        '</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' .
                          $currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['quantity'], true, $order->info['currency'], $order->info['currency_value']) .
                          ($order->products[$i]['onetime_charges'] != 0 ? '<br />' . $currencies->format(zen_add_tax($order->products[$i]['onetime_charges'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) : '') .
                          ($foreignCurrency ? ' ('.$currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['quantity'], true, DEFAULT_CURRENCY).' )' : '' ) .
                        '</td>' . "\n";
      echo '          </tr>' . "\n";
    }
?>
          <tr>
            <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
<?php
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Text">' . $order->totals[$i]['title'] . '</td>' . "\n" .
           '                <td align="right" class="'. str_replace('_', '-', $order->totals[$i]['class']) . '-Amount">' . $order->totals[$i]['text'] .
			($foreignCurrency ? ' ( '.($currencies->format( $order->totals[$i]['orders_value'], true, DEFAULT_CURRENCY)).' ) ' : '' ) .
           '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>

<?php
  // show downloads
  require(DIR_WS_MODULES . 'orders_download.php');
?>
	</table>

<div>
<?php

	// scan fulfillment modules
	$fulfillDir = DIR_FS_MODULES . 'fulfillment/';
	if( is_readable( $fulfillDir ) && $fulfillHandle = opendir( $fulfillDir ) ) {
		while( $ffFile = readdir( $fulfillHandle ) ) {
			if( is_file( $fulfillDir.$ffFile.'/admin_order_inc.php' ) ) {
				include( $fulfillDir.$ffFile.'/admin_order_inc.php' );
			}
		}
	}
	
}

?>
</div>

</td>
<td valign="top" style="width:33%;">
	  <div>
        		<?php echo '<a href="' . zen_href_link_admin(FILENAME_ORDERS_INVOICE, 'oID=' . $_REQUEST['oID']) . '" TARGET="_blank">' . zen_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a>
				<a href="' . zen_href_link_admin(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_REQUEST['oID']) . '" TARGET="_blank">' . zen_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>
				<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_REQUEST['oID'] . '&action=delete', 'NONSSL') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>'; ?>
	  </div>
	  
	
<?php

	$gBitSmarty->assign( 'orderStatuses', commerce_get_statuses( TRUE ) );

	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_order_status_history_inc.tpl' );

// check if order has open gv
		$gv_check = $db->query("select `order_id`, `unique_id`
								from " . TABLE_COUPON_GV_QUEUE ."
								where `order_id` = '" . $_REQUEST['oID'] . "' and `release_flag`='N'");
		if ($gv_check->RecordCount() > 0) {
			$goto_gv = '<a href="' . zen_href_link_admin(FILENAME_GV_QUEUE, 'order=' . $_REQUEST['oID']) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
			echo '      <tr><td align="right"><table width="225"><tr>';
			echo '        <td align="center">';
			echo $goto_gv . '&nbsp;&nbsp;';
			echo '        </td>';
			echo '      </tr></table></td></tr>';
		}
?>
	</td>
</tr>
</table>

<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

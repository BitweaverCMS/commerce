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
//  $Id: orders.php,v 1.45 2007/08/07 05:58:37 spiderr Exp $
//

	define('HEADING_TITLE', 'Order'.( (!empty( $_REQUEST['oID'] )) ? ' #'.$_REQUEST['oID'] : 's'));

	require('includes/application_top.php');
	require_once( DIR_FS_CLASSES.'order.php');

	$gBitThemes->loadAjax( 'prototype' );

	$currencies = new currencies();

	if( !empty( $_REQUEST['oID'] ) && is_numeric( $_REQUEST['oID'] ) ) {
		$oID = zen_db_prepare_input($_REQUEST['oID']);
		if( $order_exists = $gBitDb->GetOne("select orders_id from " . TABLE_ORDERS . " where `orders_id` = ?", array( $oID ) ) ) {
		    $order = new order($oID);
			$gBitSmarty->assign_by_ref( 'gBitOrder', $order );
		} else {
			$messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
		}
	}

	if( empty( $order ) ) {
  		require_once( BITCOMMERCE_PKG_PATH.'admin/orders_list_inc.php' );
	} else {
		$gBitSmarty->assign_by_ref( 'order', $order ); 
		$gBitSmarty->assign_by_ref( 'currencies', $currencies ); 
		if( !empty( $_REQUEST['del_ord_prod_att_id'] ) ) {
			$gBitDb->StartTrans();
			$rs = $gBitDb->query( "DELETE FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE `orders_products_attributes_id`=? AND `orders_id`=? ", array( $_REQUEST['del_ord_prod_att_id'], $_REQUEST['oID'] ) );
			$gBitDb->CompleteTrans();
			bit_redirect( $_SERVER['PHP_SELF'].'?oID='.$_REQUEST['oID'] );
		}

		if( !empty( $_REQUEST['action'] ) ) {
		switch( $_REQUEST['action'] ) {
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
				$gBitDb->Execute($update_downloads_query);
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
		  default:
		  // reset single download to on
			if( !empty( $_REQUEST['ord_prod_att_id'] ) ) {
				
			}
			if( !empty( $_GET['download_reset_on'] ) ) {
			  // adjust download_maxdays based on current date
			  $check_status = $gBitDb->Execute("select customers_name, customers_email_address, orders_status,
										  date_purchased from " . TABLE_ORDERS . "
										  where `orders_id` = '" . $_REQUEST['oID'] . "'");
			  $zc_max_days = date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS;

			  $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . DOWNLOAD_MAX_COUNT . "' where `orders_id`='" . $_REQUEST['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_on'] . "'";
			  $gBitDb->Execute($update_downloads_query);
			  unset($_GET['download_reset_on']);

			  $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_ON, 'success');
			  zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'SSL'));
			}
		  // reset single download to off
			if( !empty( $_GET['download_reset_off'] ) ) {
			  // adjust download_maxdays based on current date
			  $update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='0', download_count='0' where `orders_id`='" . $_REQUEST['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_off'] . "'";
			  unset($_GET['download_reset_off']);
			  $gBitDb->Execute($update_downloads_query);

			  $messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF, 'success');
			  zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'SSL'));
			}
		  break;
		}
		}
	}
  
	$gBitSystem->setOnloadScript( 'init()' );
	require(DIR_FS_ADMIN_INCLUDES . 'header.php');

	if( $order_exists ) {
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

	if( method_exists( $module, 'admin_notification' ) ) {
		$gBitSmarty->assign( 'notificationBlock', $module->admin_notification($oID) );
	}
	$gBitSmarty->assign( 'isForeignCurrency', !empty( $order->info['currency'] ) && $order->info['currency'] != DEFAULT_CURRENCY );

	print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_order.tpl' );

?>

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
	$gv_check = $gBitDb->query("select `order_id`, `unique_id`
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
<?php

 }

 require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

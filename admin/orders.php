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
//  $Id: orders.php,v 1.48 2007/08/21 02:45:47 spiderr Exp $
//

	define('HEADING_TITLE', 'Order'.( (!empty( $_REQUEST['oID'] )) ? ' #'.$_REQUEST['oID'] : 's'));

	require('includes/application_top.php');
	require_once( DIR_FS_CLASSES.'order.php');

	$gBitThemes->loadAjax( 'prototype' );

	$currencies = new currencies();

	if( $gBitThemes->isAjaxRequest() ) {
		require( BITCOMMERCE_PKG_PATH.'classes/CommerceProductManager.php' );
		$productManager = new CommerceProductManager();

		if( !empty( $_REQUEST['new_option_id'] ) ) {
			if( $optionValues = $productManager->getOptionsList( array( 'products_options_id' => $_REQUEST['new_option_id'] ) ) ) {
				if( !empty( $optionValues[$_REQUEST['new_option_id']]['values'] ) ) {
					foreach( $optionValues[$_REQUEST['new_option_id']]['values'] as $optValId=>$optVal ) {
						$optionValuesList[$optValId] = $optVal['products_options_values_name'];
					}
					require_once $gBitSmarty->_get_plugin_filepath('function','html_options');
					print smarty_function_html_options(array('options'		=> $optionValuesList,
														'name'			=> 'newOrderOptionValue',
														'print_result'	=> FALSE ), $gBitSmarty );
					print '<input type="submit" value="save" name="save_new_option">';
				} else {
					print "<span class='error'>No Options</span>";
				}
			} else {
				print "<span class='error'>Unkown Option</span>";
			}
		} else {
				print "<span class='error'>Empty Option</span>";
		}

		exit;
	}

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
		require( BITCOMMERCE_PKG_PATH.'classes/CommerceProductManager.php' );
		$productManager = new CommerceProductManager();
		$optionsList = $productManager->getOptions();
		$optionsList[0] = "Add new order option...";
		$gBitSmarty->assign_by_ref( 'optionsList', $optionsList );
 
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
			case 'save_new_option':

				$query = "SELECT 
					cpo.`products_options_name` AS products_options,
					cpa.`products_options_values_name` AS products_options_values,
					options_values_price,
					price_prefix,
					product_attribute_is_free,
					products_attributes_wt,
					products_attributes_wt_pfix,
					attributes_discounted,
					attributes_price_base_inc,
					attributes_price_onetime,
					attributes_price_factor,
					attributes_pf_offset,
					attributes_pf_onetime,
					attributes_pf_onetime_offset,
					attributes_qty_prices,
					attributes_qty_prices_onetime,
					attributes_price_words,
					attributes_price_words_free,
					attributes_price_letters,
					attributes_price_letters_free,
					cpo.`products_options_id`,
					products_options_values_id
				FROM " . TABLE_PRODUCTS_OPTIONS . " cpo 
					INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " cpa ON(cpa.products_options_id=cpo.products_options_id) 
				WHERE cpa.`products_options_values_id`=?";
				$newOption = $gBitDb->getRow( $query, array( $_REQUEST['newOrderOptionValue'] ) );
				$newOption['orders_id'] = $_REQUEST['oID'];
				$newOption['orders_products_id'] = $_REQUEST['orders_products_id'];
				$gBitDb->associateInsert( TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $newOption );
				bit_redirect( BITCOMMERCE_PKG_URL.'admin/orders.php?oID='.$_REQUEST['oID'].'&action=edit' );
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
				$gBitDb->Execute($update_downloads_query);
			  }
			  $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
			} else {
			  $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
			}

			zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=edit', 'SSL'));
			break;
		  case 'combine':
			if( @BitBase::verifyId( $_REQUEST['combine_order_id'] ) ) {
				$combineOrder = new order( $_REQUEST['combine_order_id'] );
				$combineHash['source_orders_id'] =  $_REQUEST['oID'];
				$combineHash['dest_orders_id'] = $_REQUEST['combine_order_id'];
				$combineHash['combine_notify'] = !empty( $_REQUEST['combine_notify'] );
				if( $combineOrder->combineOrders( $combineHash ) ) {
					bit_redirect( BITCOMMERCE_PKG_URL.'admin/orders.php?action=edit&oID='.$_REQUEST['combine_order_id'] );
				} else {
					print "<span class='error'>".$combineOrder->mErrors['combine']."</span>";
				}
			}
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
	  <div style="margin-top:15px;">
        		<?php echo '<a href="' . zen_href_link_admin(FILENAME_ORDERS_INVOICE, 'oID=' . $_REQUEST['oID']) . '" TARGET="_blank">' . zen_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a>
				<a href="' . zen_href_link_admin(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_REQUEST['oID']) . '" TARGET="_blank">' . zen_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>
				<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_REQUEST['oID'] . '&action=delete', 'NONSSL') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>
<br/><br/>				<form method="post" action="' . zen_href_link_admin( FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action'))) . '&amp;oID=' . $_REQUEST['oID'] . '&amp;action=combine">Combine with order: <input type="text" name="combine_order_id" style="width:100px;" /><input type="submit" name="combine" value="' . tra( 'Combine' ) . '" class="button" /><br/><input type=checkbox name="combine_notify" value="on" checked="checked">Notify Customer
<br/><em class="small">Both orders must have status '. zen_get_order_status_name( DEFAULT_ORDERS_STATUS_ID ) .'. This order will deleted.</em></form>'; ?>
	  </div>
	  

</td>
<td valign="top" style="width:33%;">
	
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

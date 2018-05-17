<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
//	$Id: orders.php,v 1.61 2010/07/14 15:19:58 spiderr Exp $
//

require('includes/application_top.php');

$gBitThemes->loadAjax( 'jquery', array( UTIL_PKG_PATH.'javascript/jquery/plugins/colorbox/jquery.colorbox-min.js' ) );
$gBitThemes->loadCss( UTIL_PKG_PATH.'javascript/jquery/plugins/colorbox/colorbox.css', FALSE, 300, FALSE);

$tempBodyLayout = $gBitSystem->getConfig( 'layout-body' ); // Caching might save here. Save value and reset
$gBitSystem->mConfig['layout-body'] = '-fluid';

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
			} else {
				$optionValuesList[$optionValues[$_REQUEST['new_option_id']]['products_options_values_id']] = $optionValues[$_REQUEST['new_option_id']]['products_options_values_name'];
			}
			$gBitSmarty->loadPlugin( 'smarty_function_html_options' );
			print smarty_function_html_options(array( 'options'			=> $optionValuesList,
														'name'			=> 'newOrderOptionValue',
														'class'			=> 'form-control',
														'print_result'	=> FALSE ), $gBitSmarty );
			print '<input class="btn btn-sm btn-primary" type="submit" value="save" name="save_new_option">';
		} else {
			print "<span class='alert alert-danger'>Unkown Option</span>";
		}
	} elseif( !empty( $_REQUEST['address_type'] ) ) {
		$addressType = $_REQUEST['address_type'];
		$entry = $order->$addressType;
		if( isset( $entry['country']['countries_id'] ) ) {
			$countryId =	$entry['country']['countries_id'];
		} elseif( is_string( $entry['country'] ) ) {
			$countryId = zen_get_country_id( $entry['country'] );
		} else {
			$countryId = NULL;
		}
		if( defined( 'ACCOUNT_STATE' ) && ACCOUNT_STATE == 'true' ) {
			$statePullDown = zen_draw_input_field('state', $entry['state'] );
			$gBitSmarty->assign( 'statePullDown', $statePullDown );
		}

		$gBitSmarty->assign( 'countryPullDown', zen_get_country_list('country_id', $countryId ) );
		$gBitSmarty->assign_by_ref( 'address', $entry );
		$gBitSmarty->display( 'bitpackage:bitcommerce/order_address_edit.tpl' );
	} else {
			print "<span class='alert alert-danger'>Empty Option</span>";
	}

	exit;
}

require(DIR_FS_ADMIN_INCLUDES . 'header.php');

// Put this after header.php because we have a custom <header> when viewing an order
define('HEADING_TITLE', ( (!empty( $_REQUEST['oID'] )) ? ' #'.$_REQUEST['oID'] : tra( 'Orders' )));

if( !empty( $order ) ) {
	require( BITCOMMERCE_PKG_PATH.'classes/CommerceProductManager.php' );
	$productManager = new CommerceProductManager();
	$optionsList = $productManager->getOptions();
	$optionsList[0] = "Add new order option...";
	$gBitSmarty->assign_by_ref( 'optionsList', $optionsList );

	$gBitSmarty->assign_by_ref( 'order', $order ); 
	$gBitSmarty->assign_by_ref( 'currencies', $currencies ); 
	if( !empty( $_REQUEST['del_ord_prod_att_id'] ) ) {
		$gBitDb->StartTrans();
		$delOption = $gBitDb->getRow( "SELECT * FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE `orders_products_attributes_id`=? AND `orders_id`=? ", array( $_REQUEST['del_ord_prod_att_id'], $_REQUEST['oID'] ) );
		$rs = $gBitDb->query( "DELETE FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE `orders_products_attributes_id`=? AND `orders_id`=? ", array( $_REQUEST['del_ord_prod_att_id'], $_REQUEST['oID'] ) );
		$order->updateStatus( array( 'comments' => 'Deleted Product Option: '.$delOption['products_options'].' => '.$delOption['products_options_values'].' ('.$_REQUEST['del_ord_prod_att_id'].')' ) );
		$gBitDb->CompleteTrans();
		bit_redirect( $_SERVER['SCRIPT_NAME'].'?oID='.$_REQUEST['oID'] );
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
			$order->updateStatus( array( 'comments' => 'Added Product Option: '.$newOption['products_options'].' => '.$newOption['products_options_values'].' ('.$_REQUEST['newOrderOptionValue'].')' ) );
			bit_redirect( BITCOMMERCE_PKG_URL.'admin/orders.php?oID='.$_REQUEST['oID'] );
			break;
		case 'save_new_product':
			if( $order->addProductToOrder( BitBase::getParameter( $_REQUEST, 'new_product_id' ), BitBase::getParameter( $_REQUEST, 'new_quantity', 1 ) ) ) {
				$order->updateStatus( array( 'comments' => 'Added Product to order: '.BitBase::getParameter( $_REQUEST, 'new_quantity', 1 ).' x '.BitBase::getParameter( $_REQUEST, 'new_product_id' ) ) );
				bit_redirect( $_SERVER['SCRIPT_NAME'].'?oID='.$_REQUEST['oID'] );
			} else {
				$feedback = $order->mErrors['new_product'];
			}
			break;
		case 'save_address':
			$addressType = $_REQUEST['address_type'];
			$saveAddress[$addressType.'_name'] = $_REQUEST['name'];
			$saveAddress[$addressType.'_company'] = $_REQUEST['company'];
			$saveAddress[$addressType.'_street_address'] = $_REQUEST['street_address'];
			$saveAddress[$addressType.'_suburb'] = $_REQUEST['suburb'];
			$saveAddress[$addressType.'_city'] = $_REQUEST['city'];
			$saveAddress[$addressType.'_state'] = $_REQUEST['state'];
			$saveAddress[$addressType.'_postcode'] = $_REQUEST['postcode'];
			$saveAddress[$addressType.'_country'] = zen_get_country_name( $_REQUEST['country_id'] );
			$saveAddress[$addressType.'_telephone'] = $_REQUEST['telephone'];
			$gBitDb->StartTrans();
			$gBitDb->associateUpdate( TABLE_ORDERS, $saveAddress, array( 'orders_id'=>$_REQUEST['oID'] ) ); 
			$gBitDb->CompleteTrans();
			bit_redirect( $_SERVER['SCRIPT_NAME'].'?oID='.$_REQUEST['oID'] );
			exit;
			break;
		case 'update_order':
			if( !empty( $_REQUEST['charge_amount'] ) && !empty( $_REQUEST['additional_charge'] ) ) {
				$formatCharge = $currencies->format( $_REQUEST['charge_amount'], FALSE, BitBase::getParameter( $_REQUEST, 'charge_currency' ) );
				$_REQUEST['cc_ref_id'] = $order->info['cc_ref_id'];
				if( $paymentModule = $order->getPaymentModule() ) {
					if( $paymentModule->processPayment( $_REQUEST, $order ) ) {
						$statusMsg = tra( 'A payment adjustment has been made to this order for the following amount:' )."\n".$formatCharge.' '.tra( 'Transaction ID:' )."\n".$paymentModule->getTransactionReference();
						$_REQUEST['comments'] = (!empty( $_REQUEST['comments'] ) ? $_REQUEST['comments']."\n\n" : '').$statusMsg;
						
					} else {
						$statusMsg = tra( 'Additional charge could not be made:' ).' '.$formatCharge.'<br/>'.implode( $paymentModule->mErrors, '<br/>' );
						$hasError = TRUE;
						$messageStack->add_session( $statusMsg, 'error');
						$order->updateStatus( array( 'comments' => $statusMsg ) );
					}
				}
			}

			if( empty( $hasError ) ) {
				if( $order->updateStatus( $_REQUEST ) ) {
					$messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
				} else {
					$messageStack->add_session( 'The order was not updated: '.BitBase::getParameter( $order->mErrors, 'status' ), 'error');
				}
				zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')), 'SSL'));
			}
			break;
		case 'email':
			if( validate_email_syntax( $_REQUEST['email'] ) ) {
				$order->sendOrderEmail( $order->mOrdersId, $_REQUEST['email'], $_REQUEST['email_format'] );
				$messageStack->add_session('Copy of receipt emailed to '.$_REQUEST['email'], 'success');
				bit_redirect( BITCOMMERCE_PKG_URL.'admin/orders.php?oID='.$_REQUEST['oID'] );
			}
			break;
		case 'combine':
			if( @BitBase::verifyId( $_REQUEST['combine_order_id'] ) ) {
				$combineOrder = new order( $_REQUEST['combine_order_id'] );
				$combineHash['source_orders_id'] =	$_REQUEST['oID'];
				$combineHash['dest_orders_id'] = $_REQUEST['combine_order_id'];
				$combineHash['combine_notify'] = !empty( $_REQUEST['combine_notify'] );
				if( $combineOrder->combineOrders( $combineHash ) ) {
					bit_redirect( BITCOMMERCE_PKG_URL.'admin/orders.php?oID='.$_REQUEST['combine_order_id'] );
				} else {
					print "<span class='error'>".$combineOrder->mErrors['combine']."</span>";
				}
			}
			break;
		case 'delete':
			$formHash['action'] = 'deleteconfirm';
			$formHash['oID'] = $oID;
			$gBitSystem->confirmDialog( $formHash, array( 'confirm_item' => 'Are you sure you want to delete order #'.$oID.'?', 'error' => 'This cannot be undone!' ) );
			break;
		case 'deleteconfirm':
			$gBitUser->verifyTicket();
			if( $order->expunge( $_POST['restock'] ) ) {
				bit_redirect( BITCOMMERCE_PKG_URL.'admin/' );
			}
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
				$zc_max_days = zen_date_diff($check_status->fields['date_purchased'], date('Y-m-d H:i:s', time())) + DOWNLOAD_MAX_DAYS;

				$update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='" . $zc_max_days . "', download_count='" . DOWNLOAD_MAX_COUNT . "' where `orders_id`='" . $_REQUEST['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_on'] . "'";
				$gBitDb->Execute($update_downloads_query);
				unset($_GET['download_reset_on']);

				$messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_ON, 'success');
				zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')), 'SSL'));
			}
			// reset single download to off
			if( !empty( $_GET['download_reset_off'] ) ) {
				// adjust download_maxdays based on current date
				$update_downloads_query = "update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays='0', download_count='0' where `orders_id`='" . $_REQUEST['oID'] . "' and orders_products_download_id='" . $_GET['download_reset_off'] . "'";
				unset($_GET['download_reset_off']);
				$gBitDb->Execute($update_downloads_query);

				$messageStack->add_session(SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF, 'success');
				zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')), 'SSL'));
			}
			break;
		}
	}
	if( !empty( $_REQUEST['delete_status'] ) ) {
		if( $gBitUser->isAdmin() ) {
			$order->expungeStatus( $_REQUEST['delete_status'] );
			bit_redirect( $_SERVER['SCRIPT_NAME'].'?oID='.$_REQUEST['oID'] );
		}
	}

	
	global $gBitUser;
	// only super admin's can monkey with 
	if( $gBitUser->hasPermission( 'p_admin' ) ) {
		// scan fulfillment modules
		$fulfillmentFiles = array();
		$fulfillDir = DIR_FS_MODULES . 'fulfillment/';
		if( is_readable( $fulfillDir ) && $fulfillHandle = opendir( $fulfillDir ) ) {
			while( $ffFile = readdir( $fulfillHandle ) ) {
				if( is_file( $fulfillDir.$ffFile.'/admin_order_inc.php' ) ) {
					$fulfillmentFiles[] = $fulfillDir.$ffFile.'/admin_order_inc.php';
				}
			}
		}
		$gBitSmarty->assign_by_ref( 'fulfillmentFiles', $fulfillmentFiles );
	}

	$gBitSmarty->assign( 'customerStats', zen_get_customers_stats( $order->customer['id'] ) );
}

if( $order_exists ) {
	if( $paymentModule = $order->getPaymentModule() ) {
		if( method_exists( $paymentModule, 'admin_notification' ) ) {
			$gBitSmarty->assign( 'notificationBlock', $paymentModule->admin_notification($oID) );
		}
	}

	$gBitSmarty->assign( 'isForeignCurrency', !empty( $order->info['currency'] ) && $order->info['currency'] != DEFAULT_CURRENCY );
	$gBitSmarty->assign( 'orderStatuses', commerce_get_statuses( TRUE ) );
	$gBitSmarty->assign( 'customersInterests', CommerceCustomer::getCustomerInterests( $order->customer['id'] ) );

	print '<div class="row">';
	print '<div class="col-md-8">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_order.tpl' ).'</div>';
	print '<div class="col-md-4">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_order_status_history_inc.tpl' ).'</div>';
	print '</div>';

	// check if order has open gv
	$gv_check = $gBitDb->query("select `order_id`, `unique_id`
							from " . TABLE_COUPON_GV_QUEUE ."
							where `order_id` = '" . $_REQUEST['oID'] . "' and `release_flag`='N'");
	if ($gv_check->RecordCount() > 0) {
		echo '<a class="btn btn-default btn-sm" href="' . zen_href_link_admin(FILENAME_GV_QUEUE, 'order=' . $_REQUEST['oID']) . '">' . tra( 'Gift Queue' ) . '</a>';
	}
	?>
	</td>
</tr>
</table>
<?php

}

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php 
require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); 
$gBitSystem->mConfig['layout-body'] = $tempBodyLayout; // Caching might save here. Save value and reset
?>

<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers
// |
// | http://www.zen-cart.com/index.php
// |
// | Portions Copyright (c) 2003 osCommerce
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,
// | that is bundled with this package in the file LICENSE, and is
// | available through the world-wide-web at the following url:
// | http://www.zen-cart.com/license/2_0.txt.
// | If you did not receive a copy of the zen-cart license and are unable
// | to obtain it through the world-wide-web, please send a note to
// | license@zen-cart.com so we can mail you a copy immediately.
// +----------------------------------------------------------------------+
//	$Id: orders.php,v 1.61 2010/07/14 15:19:58 spiderr Exp $
//


require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrderManager.php' );

global $gBitThemes;
$gBitThemes->loadJavascript( CONFIG_PKG_PATH.'themes/bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.js');
$gBitThemes->loadCss( CONFIG_PKG_PATH.'themes/bootstrap/bootstrap-datepicker/css/bootstrap-datepicker3.css');
$gBitThemes->loadAjax( 'jquery', array( UTIL_PKG_PATH.'javascript/jquery/plugins/colorbox/jquery.colorbox-min.js' ) );
$gBitThemes->loadCss( UTIL_PKG_PATH.'javascript/jquery/plugins/colorbox/colorbox.css', FALSE, 300, FALSE);

$tempBodyLayout = $gBitSystem->getConfig( 'layout-body' ); // Caching might save here. Save value and reset
$gBitSystem->mConfig['layout-body'] = '-fluid';

$currencies = new currencies();

// Put this after header.php because we have a custom <header> when viewing an order
define('HEADING_TITLE', ( (!empty( $_REQUEST['oID'] )) ? ' #'.$_REQUEST['oID'] : tra( 'Orders' )));


if( !empty( $order ) && is_a( $order, 'CommerceOrder' ) ) {
	$gBitSmarty->assign( 'orderStatuses', commerce_get_statuses( TRUE ) );
	$gBitSmarty->assign_by_ref( 'order', $order ); 
	$gBitSmarty->assign_by_ref( 'currencies', $currencies ); 
	$gBitSmarty->assign_by_ref( 'order', $order ); 
	require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceProductManager.php' );
	$productManager = new CommerceProductManager();

	if( $gBitThemes->isAjaxRequest() ) {

		if( !empty( $_REQUEST['new_option_id'] ) ) {
			if( $optionValues = $productManager->getOptionsList( array( 'products_options_id' => $_REQUEST['new_option_id'] ) ) ) {
				$optionComment = '';
				if( !empty( $optionValues[$_REQUEST['new_option_id']]['values'] ) ) {
					foreach( $optionValues[$_REQUEST['new_option_id']]['values'] as $optValId=>$optVal ) {
						$optionValuesList[$optValId] = $optVal['products_options_values_name'];
						if( $optVal['products_options_values_comment'] ) {
							$optionComment .= '<span class="help-block">'.$optVal['products_options_values_comment'].'</span>';
						}
					}
				} else {
	//				$optionValuesList[$optionValues[$_REQUEST['new_option_id']]['products_options_values_id']] = $optionValues[$_REQUEST['new_option_id']]['products_options_values_name'];
				}
				if( !empty( $optionValuesList ) ) {
					$gBitSmarty->loadPlugin( 'smarty_function_html_options' );
					print smarty_function_html_options(array( 'options'			=> $optionValuesList,
																'name'			=> 'add_order_povid',
																'class'			=> 'form-control',
																'print_result'	=> FALSE ), $gBitSmarty );
				}
				if( $optionValues[$_REQUEST['new_option_id']]['products_options_types_id'] == PRODUCTS_OPTIONS_TYPE_TEXT ) {
					print '<input type="text" class="form-control" name="add_order_povid_text">';
				}
				print '<input class="btn btn-sm btn-primary" type="submit" value="save" name="save_new_option">';
				print $optionComment;
			} else {
				print "<span class='alert alert-danger'>Unkown Option</span>";
			}
		} else {
			$addressType = BitBase::getParameter( $_REQUEST, 'address_type', 'billing' );
			$entry = $order->$addressType;
			if( isset( $entry['countries_id'] ) ) {
				$countryId =	$entry['countries_id'];
			} elseif( is_string( $entry ) ) {
				$countryId = zen_get_country_id( $entry );
			} else {
				$countryId = NULL;
			}
			if( defined( 'ACCOUNT_STATE' ) && ACCOUNT_STATE == 'true' ) {
				$statePullDown = zen_draw_input_field('state', $entry['state'] );
				$gBitSmarty->assign( 'statePullDown', $statePullDown );
			}

			$gBitSmarty->assign( 'countryPullDown', zen_get_country_list('country_id', $countryId ) );
			$gBitSmarty->assign_by_ref( 'address', $entry );

			if( $editPayment = BitBase::getParameter( $_REQUEST, 'edit_payment' ) ) {
				$payment = array();
				if( is_numeric( $editPayment ) ) {
					$payment = $gCommerceOrderManager->getPayment();
				}
				$paymentTypes = $gCommerceOrderManager->getPaymentTypes();
				$gBitSmarty->assign( 'paymentTypes', array_combine( $paymentTypes, $paymentTypes ) );
				$gBitSmarty->assign_by_ref( 'payment', $payment );
				$gBitSmarty->assign_by_ref( 'gBitOrder', $order );
				$gBitSmarty->display( 'bitpackage:bitcommerce/order_payment_edit.tpl' );
			} elseif( !empty( $_REQUEST['address_type'] ) ) {
				$gBitSmarty->display( 'bitpackage:bitcommerce/order_address_edit.tpl' );
			} else {
					print "<span class='alert alert-danger'>Empty Option</span>";
			}
		}

		exit;
	} else {

		require(DIR_FS_ADMIN_INCLUDES . 'header.php');

		require( BITCOMMERCE_PKG_CLASS_PATH.'CommerceReview.php' );
		$reviewListHash = array( 'customers_id' => $order->customer['customers_id'] );
		$gBitSmarty->assign( 'orderReviews', CommerceReview::getList( $reviewListHash ) );

		$optionsList = $productManager->getOptions();
		$optionsList[0] = "Add new order option...";
		$gBitSmarty->assign_by_ref( 'optionsList', $optionsList );

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
			case 'process':
				if( $order->getField( 'orders_status_id' ) == $gCommerceSystem->getConfig( 'DEFAULT_ORDERS_STATUS_ID' ) ) {
					$order->updateStatus( array( 'status' => $gCommerceSystem->getConfig( 'PROCESSING_ORDERS_STATUS_ID', $gCommerceSystem->getConfig( 'DEFAULT_ORDERS_STATUS_ID' ) ) ) );
				}
				break;
			case 'save_new_option':
				$query = "SELECT 
					cpo.`products_options_name` AS products_options,
					cpa.`products_options_values_name` AS products_options_values,
					options_values_price,
					price_prefix,
					product_attribute_is_free,
					products_attributes_wt,
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
				$newOption = $gBitDb->getRow( $query, array( $_REQUEST['add_order_povid'] ) );
				$newOption['orders_id'] = $_REQUEST['oID'];
				$newOption['orders_products_id'] = $_REQUEST['orders_products_id'];
				if( !empty( trim( BitBase::getParameter( $_REQUEST, 'add_order_povid_text', NULL ) ) ) ) {
					$newOption['products_options_values'] .= '~'.trim( $_REQUEST['add_order_povid_text'] );
				}

				$gBitDb->associateInsert( TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $newOption );
				$order->updateStatus( array( 'comments' => 'Added Product Option: '.$newOption['products_options'].' => '.$newOption['products_options_values'].' ('.$_REQUEST['add_order_povid'].')' ) );
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
			case 'save_payment':
				if( $gCommerceOrderManager->storeOrdersPayment( $_REQUEST, $order ) ) {
					$messageStack->add_session( SUCCESS_ORDER_UPDATED, 'success' );
				} else {
					$messageStack->add_session( 'The payment was not recorded: '.BitBase::getParameter( $order->mErrors, 'status' ), 'error' );
				}
				zen_redirect( zen_href_link_admin( FILENAME_ORDERS, zen_get_all_get_params( array('action') ), 'SSL') );
				break;
			case 'save_address':
				$addressType = $_REQUEST['address_type'];
				$statusMsg = 'Updated '.$addressType." address. Previously:\n\n".$order->getFormattedAddress( $addressType, "\n" );
				$order->updateStatus( array( 'comments' => $statusMsg ) );
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
			case 'update_deadline':
				if( empty( $dateTime = BitBase::getParameter( $_REQUEST, 'deadline_date', NULL ) ) ) {
					$dateTime = NULL;
					$statusMsg = 'Removed order deadline of '.$order->getField( 'deadline_date' );
				} else {
					$statusMsg = 'Set order deadline of '.$dateTime;
				}
				$order->updateOrder( array( 'deadline_date' => $dateTime ) );
				$order->updateStatus( array( 'comments' => $statusMsg ) );
				zen_redirect(zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('action')), 'SSL'));
				break;
			case 'update_order':
				if( $order->adjustOrder( $_REQUEST, $_SESSION ) ) {
					$messageStack->add_session( SUCCESS_ORDER_UPDATED, 'success' );
				} else {
					$messageStack->add_session( 'The order was not updated: '.BitBase::getParameter( $order->mErrors, 'status' ), 'error' );
				}
				zen_redirect( zen_href_link_admin( FILENAME_ORDERS, zen_get_all_get_params( array('action') ), 'SSL') );
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
				if( $order->expunge( !empty( $_POST['restock'] ) ) ) {
					bit_redirect( BITCOMMERCE_PKG_URL.'admin/' );
				}
				break;
			default:
				// reset single download to on
				if( !empty( $_REQUEST['ord_prod_att_id'] ) ) {
					
				}
				if( !empty( $_GET['download_reset_on'] ) ) {
					// adjust download_maxdays based on current date
					$check_status = $gBitDb->Execute("select customers_name, customers_email_address, orders_status_id,
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

		$gBitSmarty->assign( 'customerStats', zen_get_customers_stats( $order->customer['customers_id'] ) );
	}
}

if( $order_exists ) {
	if( $paymentModule = $order->getPaymentModule() ) {
		if( method_exists( $paymentModule, 'admin_notification' ) ) {
			$gBitSmarty->assign( 'notificationBlock', $paymentModule->admin_notification($oID) );
		}
	}

	$siblingOrderIds = array();
/*
	if( $gCommerceSystem->getConfig( 'DEFAULT_ORDERS_STATUS_ID' ) == $order->getStatus() ) {
		$siblingOrderIds = $gCommerceOrderManager->getOrdersToAddress( $order->delivery, $order->getStatus() );
	}
*/
	$siblingOrderIds = $gCommerceOrderManager->getOrdersToAddress( $order->delivery, 39 ); // Crude hard code 
	$gBitSmarty->assign( 'siblingOrderIds', $siblingOrderIds );

	$gBitSmarty->assign( 'isForeignCurrency', !empty( $order->info['currency'] ) && $order->info['currency'] != DEFAULT_CURRENCY );
	$gBitSmarty->assign( 'customersInterests', CommerceCustomer::getCustomerInterests( $order->customer['customers_id'] ) );

	print '<div class="row">';
	print '<div class="col-md-8">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_order.tpl' ).'</div>';
	print '<div class="col-md-4 pt-1">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_order_status_history_inc.tpl' ).'</div>';
	print '</div>';

	// check if order has open gv
	$gv_check = $gBitDb->query("SELECT `order_id`, `unique_id` FROM " . TABLE_COUPON_GV_QUEUE ." WHERE `order_id` = ? AND `release_flag`='N'", array( $_REQUEST['oID'] ) );
	if ($gv_check->RecordCount() > 0) {
		echo '<a class="btn btn-default btn-sm" href="' . zen_href_link_admin(FILENAME_GV_QUEUE, 'order=' . $_REQUEST['oID']) . '">' . tra( 'Gift Queue' ) . '</a>';
	}
}

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 
require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); 

$gBitSystem->mConfig['layout-body'] = $tempBodyLayout; // Caching might save here. Save value and reset

<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 * Copyright (c) 2020 bitweaver.org, All Rights Reserved
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 * This source file is subject to the 2.0 GNU GENERAL PUBLIC LICENSE. 
 *
 * 
 *
 */

class CommercePaymentManager extends BitBase {
	private $selected_module;
	private $mPaymentObjects = array();

	public $mErrors = array();

	// class constructor
	function __construct($pPaymentModule = '') {
		global $gCommerceSystem;

		parent::__construct();

		$this->mPaymentObjects = $gCommerceSystem->scanModules( 'payment', TRUE );
		
		// if there is only one payment method, select it as default because in
		// checkout_confirmation.php the $payment variable is being assigned the
		// $_POST['payment_method'] value which will be empty (no radio button selection possible)
		if( count( $this->mPaymentObjects ) == 1 ) {
			$paymentModule = current( $this->mPaymentObjects );
			$_SESSION['payment_method'] = $paymentModule->code;
		}

		if( !empty( $pPaymentModule ) ) {
			if( !empty( $this->mPaymentObjects[$pPaymentModule] ) ) {
				$this->selected_module = $pPaymentModule;
			} else {
				if( $pPaymentModule == 'card' ) {
					// card, used in API, will select the first found CommercePluginPaymentCardBase plugin (should only ever be one activated anyway)
					foreach( array_keys( $this->mPaymentObjects ) as $moduleKey ) {
						if( is_a( $this->mPaymentObjects[$moduleKey], 'CommercePluginPaymentCardBase' ) ) {
							$this->selected_module = $this->mPaymentObjects[$moduleKey]->code;
							$_SESSION['payment_method'] = $this->selected_module;
							break;
						}
					}
				}
			}
		}
	}


	function isModuleActive( $pModuleName ) {
		return !empty( $this->mPaymentObjects[$pModuleName] );
	}

	// {{{ PAYMENT CLASS METHODS
	/* The following method is needed in the checkout_confirmation.php page
	 due to a chicken and egg problem with the payment class and order class.
	 The payment modules needs the order destination data for the dynamic status
	 feature, and the order class needs the payment module title.
	 The following method is a work-around to implementing the method in all
	 payment modules available which would break the modules in the contributions
	 section. This should be looked into again post 2.2.
	*/
	function update_status( $pPaymentParams ) {
			if ( !empty( $this->mPaymentObjects[$this->selected_module] ) && is_object($this->mPaymentObjects[$this->selected_module])) {
				if (method_exists($this->mPaymentObjects[$this->selected_module], 'update_status')) {
					$this->mPaymentObjects[$this->selected_module]->update_status( $pPaymentParams );
				}
			}
	}

	function javascript_validation() {
		$js = '<script language="javascript"	type="text/javascript"><!-- ' . "\n" .
					'function check_form() {' . "\n" .
					'	var error = 0;' . "\n" .
					'	var error_message = "' . JS_ERROR . '";' . "\n" .
					'	var payment_value = null;' . "\n" .
					'	if (document.checkout_payment.payment.length) {' . "\n" .
					'		for (var i=0; i<document.checkout_payment.payment.length; i++) {' . "\n" .
					'			if (document.checkout_payment.payment[i].checked) {' . "\n" .
					'				payment_value = document.checkout_payment.payment[i].value;' . "\n" .
					'			}' . "\n" .
					'		}' . "\n" .
					'	} else if (document.checkout_payment.payment.checked) {' . "\n" .
					'		payment_value = document.checkout_payment.payment.value;' . "\n" .
					'	} else if (document.checkout_payment.payment.value) {' . "\n" .
					'		payment_value = document.checkout_payment.payment.value;' . "\n" .
					'	}' . "\n\n";

		$moduleKeys = array_keys( $this->mPaymentObjects );
		foreach( $moduleKeys as $value ) { 
			$class = substr($value, 0, strrpos($value, '.'));
			if ( !empty($this->mPaymentObjects[$class]) && $this->mPaymentObjects[$class]->enabled) {
				$js .= $this->mPaymentObjects[$class]->javascript_validation();
			}
		}

		$js .= "\n" . '	if (payment_value == null && submitter != 1) {' . "\n" .
					 '		error_message = error_message + "' . JS_ERROR_NO_PAYMENT_MODULE_SELECTED . '";' . "\n" .
					 '		error = 1;' . "\n" .
					 '	}' . "\n\n" .
					 '	if (error == 1 && submitter != 1) {' . "\n" .
					 '		alert(error_message);' . "\n" .
					 '		return false;' . "\n" .
					 '	} else {' . "\n" .
					 '		return true;' . "\n" .
					 '	}' . "\n" .
					 '}' . "\n" .
					 '//--></script>' . "\n";

		return $js;
	}

	function selection() {
		$ret = array();

		$moduleKeys = array_keys( $this->mPaymentObjects );
		foreach( $moduleKeys as $moduleKey ) { 
			if( $selection = $this->mPaymentObjects[$moduleKey]->selection() ) {
				$ret[] = $selection;
			}
		}

		return $ret;
	}

	function confirmation( $pPaymentParams = NULL ) {
		if ( !empty( $this->mPaymentObjects[$this->selected_module] ) && is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) ) {
			return $this->mPaymentObjects[$this->selected_module]->confirmation( $pPaymentParams );
		}
	}

	function process_button( $pPaymentParams = NULL ) {
		if ( !empty( $this->mPaymentObjects[$this->selected_module] ) && is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) ) {
			return $this->mPaymentObjects[$this->selected_module]->process_button( $pPaymentParams );
		}
	}

	function admin_notification($zf_order_id) {
		if (is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) && (method_exists($this->mPaymentObjects[$this->selected_module], 'admin_notification'))) {
			return $this->mPaymentObjects[$this->selected_module]->admin_notification($zf_order_id);
		}
	}

	function get_form_action_url() {
		$formActionUrl = (is_object( $this->selected_module ) && !empty( $this->selected_module->form_action_url ) ? $this->selected_module->form_action_url : zen_href_link( FILENAME_CHECKOUT_PROCESS, '', 'SSL') );
		return $formActionUrl;
	}

	function get_error() {
		if (is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) ) {
			return $this->mPaymentObjects[$this->selected_module]->get_error();
		}
	}

	function after_order_create( $zf_order_id, $pOrder ) {
		global $gBitUser, $gBitProduct, $gCommerceSystem;
		$ret = NULL;
		if( round( $pOrder->getField( 'total', 2 ) ) > 0 && ($groupId = $gCommerceSystem->getConfig( 'CUSTOMERS_PURCHASE_GROUP' )) ) {
			$gBitUser->addUserToGroup( $gBitUser->mUserId, $groupId );
		}
		$gBitProduct->invokeServices( 'commerce_post_purchase_function', $pOrder );
		if (!empty($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) && (method_exists($this->mPaymentObjects[$this->selected_module], 'after_order_create'))) {
			return $this->mPaymentObjects[$this->selected_module]->after_order_create($zf_order_id);
		}
		return $ret;
	}
	// }}}

	// {{{ PAYMENT PROCESSING
	function verifyPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {
		$ret = FALSE;
		if( $pOrder->hasPaymentDue( $pPaymentParams ) ) {	
			if ( !empty( $this->mPaymentObjects[$this->selected_module] ) && is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) ) {
				if( !($ret = $this->mPaymentObjects[$this->selected_module]->verifyPayment( $pOrder, $pPaymentParams, $pSessionParams )) ) {
					$this->mErrors = $this->mPaymentObjects[$this->selected_module]->mErrors;
				}
			}
		} else {
			$ret = TRUE;
		}

		return $ret;
	}

	function processPayment( $pOrder, &$pPaymentParams, &$pSessionParams ) {
		global $gBitProduct;
		$ret = NULL;

		$gBitProduct->invokeServices( 'commerce_pre_purchase_function', $pOrder );
		if( !empty( $this->mPaymentObjects[$this->selected_module] ) && !empty( $this->mPaymentObjects[$this->selected_module]->enabled ) ) {
			if( $ret = $this->mPaymentObjects[$this->selected_module]->processPayment( $pOrder, $pPaymentParams, $pSessionParams ) ) {
				$pPaymentParams['initial_orders_status_id'] = $this->mPaymentObjects[$this->selected_module]->getProcessedOrdersStatus();
			} else {
				$this->mErrors = $this->mPaymentObjects[$this->selected_module]->mErrors;
			}
		} else {
			if( !empty( $this->selected_module ) ) {
				$this->mErrors['payment_method'] = 'Unknown payment method ( ' . $this->selected_module . ' )';
			} else {
				$this->mErrors['payment_method'] = 'No payment method specified.';
			}
		}

		return $ret;
	}
	// }}}

	// {{{ PAYMENT LOGGING
	private function verifyOrdersPayment( &$pParamHash, $pOrder ) {
		$ret = FALSE;

		global $gBitUser;
		$pParamHash['payment_store']['user_id'] = $gBitUser->mUserId;
		$pParamHash['payment_store']['customers_id'] = $pOrder->customer['customers_id'];
		$pParamHash['payment_store']['ip_address'] = $_SERVER['REMOTE_ADDR'];

		$columns = array( 
//			'address_street_address' => 'address_street', 
			'orders_id', 
			'payment_ref_id', 
			'payment_result', 
			'payment_auth_code', 
			'payment_message', 
			'payment_amount', 
			'payment_date', 
			'customers_id', 
			'is_success', 
			'customers_email', 
			'payment_type', 
			'payment_owner', 
			'payment_number', 
			'payment_expires', 
			'transaction_date', 
			'payment_module', 
			'payment_mode', 
			'payment_status', 
			'trans_parent_ref_id', 
			'payment_currency', 
			'exchange_rate', 
			'payment_parent_ref_id', 
			'pending_reason', 
			'first_name', 
			'last_name', 
			'address_company', 
			'address_name', 
			'address_suburb', 
			'address_city', 
			'address_postcode', 
			'address_country', 
			'num_cart_items' 
		);


		if( BitBase::verifyIdParameter( $pParamHash, 'country_id' ) ) {
			$pParamHash['address_country'] = zen_get_country_name( $pParamHash['country_id'] );
		}

		if( empty( $pParamHash['payment_status'] ) ) {
			$pParamHash['payment_status'] = ($pParamHash['is_success'] == 'y' ? 'PAID' : 'unsuccessful');
		}

		foreach( $columns as $colName ) {
			if( isset( $pParamHash[$colName] ) ) {
				$pParamHash['payment_store'][$colName] = $pParamHash[$colName];
			}
		}

		// No bounds checking yet
		$ret = TRUE;

		return $ret;
	}

	public function storeOrdersPayment( &$pParamHash, $pOrder ) {
		$ret = FALSE;
		$sessionParams = array();

		if( !empty( $pParamHash['adjust_total'] ) ) {
			$ret = $pOrder->adjustOrder( $pParamHash, $sessionParams );
		} else {
			$ret = TRUE;
			$this->mDb->StartTrans();
			if( $this->verifyOrdersPayment( $pParamHash, $pOrder ) ) {
				$ordersUpdate = array();
				$this->mDb->associateInsert( TABLE_ORDERS_PAYMENTS, $pParamHash['payment_store'] );
			
				$statusHash['comments'] = trim( BitBase::getParameter( $pParamHash, 'comments', NULL ) );
				$statusHash['status'] = BitBase::getParameter( $pParamHash, 'status' );
				$pOrder->updateStatus( $statusHash );
			} else {
bit_error_log( $pParamHash, $this->mErrors );
			}
			$this->mDb->CompleteTrans();
		}

		return $ret;
	}
	// }}}

	// {{{ INVOICE PAYMENTS
	private function prepGetDueList(&$pListHash){
		// keep a copy of user_id for later...
		$userId = parent::getParameter( $pListHash, 'user_id' );
		parent::prepGetList($pListHash);
	}

	public function getDueOrders( $pListHash = array() ) {
		global $gBitUser;

		$ret = array();
		$whereSql = '';
		$bindVars = array();

		$this->prepGetDueList( $pListHash );
		if( !$gBitUser->hasPermission( 'p_bitcommerce_admin' ) ) {
			$whereSql .= ' AND co.`customers_id`=? ';
			$bindVars[] = $gBitUser->mUserId;
		} elseif( $userId = BitBase::verifyIdParameter( $_REQUEST, 'customers_id' ) ) {
			$whereSql .= ' AND co.`customers_id`=? ';
			$bindVars[] = $userId;
		}

		if( !empty( $pListHash['payment_number'] ) ) {
			$whereSql .= ' AND cop.`payment_number`=? ';
			$bindVars[] = $pListHash['payment_number'];
		}

		if( $rs = $this->mDb->query( "SELECT * FROM " . TABLE_ORDERS . " co INNER JOIN " . TABLE_ORDERS_PAYMENTS . " cop ON (co.`orders_id`=cop.`orders_id`) WHERE co.`orders_status_id` > 0 AND co.`amount_due` > 0 $whereSql ORDER BY cop.`payment_number`", $bindVars ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[$row['customers_id']][$row['payment_number']]['orders'][] = $row;
				if( empty( $ret[$row['customers_id']][$row['payment_number']]['totals'] ) ) {
					$ret[$row['customers_id']][$row['payment_number']]['totals'] = array( 'count' => 0, 'due' => 0.0 );
				}
				$ret[$row['customers_id']][$row['payment_number']]['totals']['count']++;
				$ret[$row['customers_id']][$row['payment_number']]['totals']['due'] += $row['order_total'];
			}
		}

		return $ret;
	}

	public function payInvoice( $pParamHash ) {
		global $currencies;

		if( !empty( $pParamHash['invoice'] ) ) {
			// ['invoice'] = array( user_id => po_string )
			foreach( $pParamHash['invoice'] as $userId => $invoiceStrings ) {
				foreach( $invoiceStrings as $invoiceString ) {
					if( $dueOrders = $this->getDueOrders( array( 'customers_id' => $userId, 'payment_number' => $invoiceString ) ) ) {
						foreach( $dueOrders as $userId => $userOrders ) {
							foreach( $userOrders as $paymentNumber => $paymentOrders ) {
								$amountPaid = 0.00;
								$ordersPaid = 0;

								$paymentAmount = (string)BitBase::getParameter( $pParamHash, 'payment_amount' );
								// cast to string because of floating point precision WARNING here https://www.php.net/manual/en/language.types.float.php
								if( (string)$paymentOrders['totals']['due'] != $paymentAmount ) {
									$this->mErrors['errors'][] = tra( 'Charge amount does not equal invoice amount.' ).' ('.(string)$paymentOrders['totals']['due'].' != '.$paymentAmount.')';
								}
								if( !empty( $pParamHash['payment_method'] ) ) {

									// Fill out hashes and objects to process payment using payment modules that expect an order
									$tempOrder = new CommerceOrder();
									$pParamHash['charge_amount'] = $paymentAmount;
									foreach( array( 
										'name' => 'payment_owner',
										'company' => 'address_company',
										'street_address' => 'address_street_address', 
										'suburb' => 'address_suburb', 
										'city' => 'address_city',
										'state' => 'address_state', 
										'postcode' => 'address_postcode',
										'countries_id' => 'country_id' ) as $orderKey => $formKey ) {
										$tempOrder->billing[$orderKey] = $pParamHash[$formKey];
									}

									$tempOrder->customer['firstname'] = $tempOrder->billing['firstname'] = substr( $pParamHash['payment_owner'], 0, strpos( $pParamHash['payment_owner'], ' ' ) );
									$tempOrder->customer['lastname'] = $tempOrder->billing['lastname'] = substr( $pParamHash['payment_owner'], strpos( $pParamHash['payment_owner'], ' ' ) + 1 );

									if( $countryHash = zen_get_countries(	$tempOrder->billing['countries_id'] ) ) {
										$tempOrder->billing = array_merge( $tempOrder->billing, $countryHash );
									}
									$tempOrder->delivery = $tempOrder->billing;

									$tempOrder->customer['customers_id'] = $userId;
									$tempOrder->info['currency_value'] = 1.0;
									$tempOrder->info['currency'] = $pParamHash['charge_currency'];
									if( $tempUser = BitUser::getUserObject( $tempOrder->customer['customers_id'] ) ) {
										$tempOrder->customer['email_address'] = $tempUser->getField( 'email' );
									} else {
										$this->mErrors['errors'][] = tra( 'Could not load user.' ).' ('.$tempOrder->customer['customers_id'].')';
									}

									if( $pParamHash['payment_method'] == 'manual' ) {
										$pParamHash['payment_number'] = $pParamHash['manual']['payment_number'];
										$pParamHash['payment_type'] = $pParamHash['manual']['payment_type'];
										$pParamHash['is_success'] = 'y';
									} elseif( !empty( $this->mPaymentObjects[$this->selected_module] ) && !empty( $this->mPaymentObjects[$this->selected_module]->enabled ) ) {
										$sessionParams = array();
										if( $ret = $this->mPaymentObjects[$this->selected_module]->processPayment( $tempOrder, $pParamHash, $sessionParams ) ) {
											$pParamHash['payment_ref_id'] = $tempOrder->info['payment_ref_id'];
										} else {
											$this->mErrors['errors'][] = tra( 'Payment Failed' ).': '.$pParamHash['result']['payment_result'];
											break;
										}
									}
								}
								$masterPaymentHash = !empty( $pParamHash['result'] ) ? $pParamHash['result'] : $pParamHash;
								$ordersCount = count( $paymentOrders['orders'] );
								foreach( $paymentOrders['orders'] as $paymentOrderHash ) {
									$this->mDb->StartTrans();
									$order = new order( $paymentOrderHash['orders_id'] );
									if( $amountDue = $order->getField( 'amount_due' ) ) {
										$ordersPaid++;
										$amountPaid += $amountDue;
										$paymentHash = $masterPaymentHash;
										if( empty( $pParamHash['status'] ) && $order->getField( 'orders_status_id' ) < DEFAULT_ORDERS_STATUS_ID ) {
											// invoiced purchase orders can default to a lower initial status like PENDING, move to default paid status like NEW
											$paymentHash['status'] = DEFAULT_ORDERS_STATUS_ID;
										}
										$paymentHash['orders_id'] = $paymentOrderHash['orders_id'];
										$paymentHash['payment_amount'] = $amountDue;
										$paymentHash['comments'] = trim( "New Payment Recorded: " . $pParamHash['payment_number'] );
										if( $ordersCount > 1 ) {
											$paymentHash['comments'] .= "\n\n".trim( "PAID $ordersPaid of $ordersCount, ". $currencies->format( $amountPaid, FALSE, '', '', FALSE ) ." of " . $currencies->format( $pParamHash['payment_amount'], FALSE, '', '', FALSE ) . "\n\n" . BitBase::getParameter( $pParamHash, 'comments' ) );
										}
										if( $this->storeOrdersPayment( $paymentHash, $order ) ) {
											if( $order->getField( 'amount_due' ) ) {
												$amountDue = ($order->getField( 'amount_due' ) - $paymentHash['payment_amount']);
												$this->mDb->query( "UPDATE " . TABLE_ORDERS . " SET `amount_due` = ? WHERE `orders_id` = ?", array( $amountDue, $paymentHash['orders_id'] ) );
											}
										}
									}
									$this->mDb->CompleteTrans();
								}
							}
						}
					}
				}
			}
		} else {
			$this->mErrors['invoice'] = 'No invoices selected';
		}
		return empty( $this->mErrors );
	}
	// }}}

}


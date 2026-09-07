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
				$pPaymentParams['payment_method'] = $this->mPaymentObjects[$this->selected_module]->title;
				$pPaymentParams['payment_module_code'] = $this->mPaymentObjects[$this->selected_module]->code;
				$pPaymentParams['processed_orders_status_id'] = $this->mPaymentObjects[$this->selected_module]->getProcessedOrdersStatus();
			} else {
				$this->mErrors = $this->mPaymentObjects[$this->selected_module]->mErrors;
				$this->recordFailedPayment( $pOrder, $pPaymentParams, $this->mPaymentObjects[$this->selected_module] );
			}
		} else {
			if( !empty( $this->selected_module ) ) {
				$this->mErrors['payment_method'] = 'Unknown payment method ( ' . $this->selected_module . ' )';
			} else {
				$this->mErrors['payment_method'] = 'No payment method specified.';
			}
			$this->recordFailedPayment( $pOrder, $pPaymentParams );
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
			'address_street', 
			'address_suburb', 
			'address_city', 
			'address_state', 
			'address_postcode', 
			'address_country', 
			'num_cart_items' 
		);


		if( BitBase::verifyIdParameter( $pParamHash, 'country_id' ) ) {
			$pParamHash['address_country'] = zen_get_country_name( $pParamHash['country_id'] );
		}

		if( empty( $pParamHash['address_street'] ) && isset( $pParamHash['address_street_address'] ) ) {
			$pParamHash['address_street'] = $pParamHash['address_street_address'];
		}

		if( empty( $pParamHash['payment_status'] ) ) {
			$pParamHash['payment_status'] = ($pParamHash['is_success'] == 'y' ? 'PAID' : 'unsuccessful');
		}

		foreach( $columns as $colName ) {
			if( isset( $pParamHash[$colName] ) ) {
				$pParamHash['payment_store'][$colName] = $pParamHash[$colName];
			}
		}

		if( empty( $pParamHash['payment_store']['address_street'] ) && isset( $pParamHash['address_street_address'] ) ) {
			$pParamHash['payment_store']['address_street'] = $pParamHash['address_street_address'];
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

	/**
	 * Persist a failed attempt after any plugin RollbackTrans, then alert if needed.
	 * Does not update order status — checkout failures often have no order row yet.
	 */
	public function recordFailedPayment( $pOrder, &$pPaymentParams, $pPaymentModule = NULL ) {
		global $gBitUser, $gCommerceSystem;

		$errors = $pPaymentModule ? $pPaymentModule->mErrors : $this->mErrors;
		if( empty( $errors ) ) {
			$errors = $this->mErrors;
		}

		$logHash = BitBase::getParameter( $pPaymentParams, 'result', array() );
		if( empty( $logHash ) || !is_array( $logHash ) ) {
			$logHash = array();
		}
		if( empty( $logHash['payment_module'] ) && $pPaymentModule && method_exists( $pPaymentModule, 'prepPayment' ) ) {
			$logHash = array_merge( $pPaymentModule->prepPayment( $pOrder, $pPaymentParams ), $logHash );
		}

		if( $pPaymentModule ) {
			$failClass = $pPaymentModule->classifyPaymentFailure( $logHash, $errors );
			$paymentStatus = $pPaymentModule->paymentStatusForFailure( $failClass, $logHash, $errors );
		} else {
			$failClass = 'infra';
			$paymentStatus = 'infra';
		}

		$message = BitBase::getParameter( $logHash, 'payment_message' );
		if( $message === NULL || $message === '' ) {
			$message = BitBase::getParameter( $errors, 'process_payment', implode( ' ', array_filter( $errors, 'is_scalar' ) ) );
		}

		$resultCode = BitBase::getParameter( $logHash, 'payment_result', '' );
		if( $resultCode === NULL || $resultCode === '' ) {
			$resultCode = $failClass;
		}

		$storeHash = array(
			'user_id' => BitBase::getParameter( $logHash, 'user_id', $gBitUser->mUserId ),
			'customers_id' => BitBase::getParameter( $logHash, 'customers_id', BitBase::getParameter( $pOrder->customer, 'customers_id' ) ),
			'customers_email' => BitBase::getParameter( $logHash, 'customers_email', BitBase::getParameter( $pOrder->customer, 'email_address', '' ) ),
			'ip_address' => BitBase::getParameter( $logHash, 'ip_address', BitBase::getParameter( $_SERVER, 'REMOTE_ADDR', '' ) ),
			'is_success' => 'n',
			'payment_module' => BitBase::getParameter( $logHash, 'payment_module', ( $pPaymentModule ? $pPaymentModule->code : 'unknown' ) ),
			'payment_mode' => BitBase::getParameter( $logHash, 'payment_mode', 'charge' ),
			'payment_status' => $paymentStatus,
			'payment_ref_id' => BitBase::getParameter( $logHash, 'payment_ref_id', '' ),
			'payment_type' => BitBase::getParameter( $logHash, 'payment_type', '' ),
			'payment_owner' => BitBase::getParameter( $logHash, 'payment_owner', '' ),
			'payment_number' => BitBase::getParameter( $logHash, 'payment_number', '' ),
			'payment_expires' => BitBase::getParameter( $logHash, 'payment_expires', '' ),
			'payment_result' => (string)$resultCode,
			'payment_message' => (string)$message,
			'payment_amount' => (float)BitBase::getParameter( $logHash, 'payment_amount', BitBase::getParameter( $pPaymentParams, 'payment_amount', 0 ) ),
			'payment_currency' => BitBase::getParameter( $logHash, 'payment_currency', BitBase::getParameter( $pPaymentParams, 'payment_currency', DEFAULT_CURRENCY ) ),
			'exchange_rate' => (float)BitBase::getParameter( $logHash, 'exchange_rate', 1 ),
			'address_name' => BitBase::getParameter( $logHash, 'address_name', BitBase::getParameter( $logHash, 'payment_owner', '' ) ),
			'address_company' => BitBase::getParameter( $logHash, 'address_company', '' ),
			'address_street' => BitBase::getParameter( $logHash, 'address_street', BitBase::getParameter( $logHash, 'address_street_address', '' ) ),
			'address_suburb' => BitBase::getParameter( $logHash, 'address_suburb', '' ),
			'address_city' => BitBase::getParameter( $logHash, 'address_city', '' ),
			'address_state' => BitBase::getParameter( $logHash, 'address_state', '' ),
			'address_postcode' => BitBase::getParameter( $logHash, 'address_postcode', '' ),
			'address_country' => BitBase::getParameter( $logHash, 'address_country', '' ),
			'num_cart_items' => (int)BitBase::getParameter( $logHash, 'num_cart_items', 0 ),
		);

		if( BitBase::verifyIdParameter( $logHash, 'orders_id' ) ) {
			$storeHash['orders_id'] = $logHash['orders_id'];
		} elseif( !empty( $pOrder->mOrdersId ) && BitBase::verifyId( $pOrder->mOrdersId ) ) {
			$storeHash['orders_id'] = $pOrder->mOrdersId;
		}

		foreach( $storeHash as $key => $value ) {
			if( $value === NULL ) {
				$storeHash[$key] = ( $key === 'payment_amount' || $key === 'exchange_rate' || $key === 'num_cart_items' ) ? 0 : '';
			}
		}

		if( !BitBase::verifyId( $storeHash['customers_id'] ) ) {
			bit_error_log( 'recordFailedPayment skipped insert: missing customers_id', $storeHash );
		} else {
			$storeHash['user_id'] = (int)$storeHash['user_id'];
			$storeHash['customers_id'] = (int)$storeHash['customers_id'];
			$this->mDb->StartTrans();
			$this->mDb->associateInsert( TABLE_ORDERS_PAYMENTS, $storeHash );
			$this->mDb->CompleteTrans();
		}

		$logHash['is_success'] = 'n';
		$logHash['payment_status'] = $paymentStatus;
		$logHash['failure_class'] = $failClass;
		$pPaymentParams['result'] = $logHash;

		$this->alertFailedPayment( $failClass, $storeHash, $errors, $pPaymentParams );

		return $failClass;
	}

	private function alertFailedPayment( $pClass, $pStoreHash, $pErrors, $pPaymentParams ) {
		global $gCommerceSystem;

		$host = php_uname( 'n' );
		$message = BitBase::getParameter( $pStoreHash, 'payment_message', '' );
		$safeVars = $this->sanitizePaymentAlertVars( array(
			'payment' => $pStoreHash,
			'mErrors' => $pErrors,
		) );

		if( $pClass === 'infra' ) {
			bit_error_email(
				'PAYMENT INFRA on '.$host.': '.$message,
				$message."\n\n".( function_exists( 'bit_error_string' ) ? bit_error_string() : '' ),
				$safeVars
			);
			return;
		}

		$threshold = (int)$gCommerceSystem->getConfig( 'PAYMENT_FAIL_ALERT_THRESHOLD', 5 );
		$windowMin = (int)$gCommerceSystem->getConfig( 'PAYMENT_FAIL_ALERT_WINDOW_MINUTES', 15 );
		if( $threshold < 1 ) {
			$threshold = 5;
		}
		if( $windowMin < 1 ) {
			$windowMin = 15;
		}

		$count = $this->countRecentFailedPayments( $pStoreHash, $windowMin );
		if( $count == $threshold ) {
			bit_error_email(
				'PAYMENT REPEAT on '.$host.': '.$count.' failures in '.$windowMin.'m ('.$message.')',
				$count.' unsuccessful payment attempts for this account in the last '.$windowMin.' minutes.'."\n\n".$message."\n\n".( function_exists( 'bit_error_string' ) ? bit_error_string() : '' ),
				$safeVars
			);
		}
	}

	private function countRecentFailedPayments( $pStoreHash, $pWindowMinutes ) {
		$since = date( 'Y-m-d H:i:s', time() - ( (int)$pWindowMinutes * 60 ) );
		$customersId = BitBase::getParameter( $pStoreHash, 'customers_id' );
		if( BitBase::verifyId( $customersId ) ) {
			return (int)$this->mDb->getOne(
				"SELECT COUNT(*) FROM " . TABLE_ORDERS_PAYMENTS . " WHERE `is_success`='n' AND `customers_id`=? AND `payment_date` >= ?",
				array( $customersId, $since )
			);
		}
		$userId = BitBase::getParameter( $pStoreHash, 'user_id' );
		if( BitBase::verifyId( $userId ) ) {
			return (int)$this->mDb->getOne(
				"SELECT COUNT(*) FROM " . TABLE_ORDERS_PAYMENTS . " WHERE `is_success`='n' AND `user_id`=? AND `payment_date` >= ?",
				array( $userId, $since )
			);
		}
		$ip = BitBase::getParameter( $pStoreHash, 'ip_address' );
		if( $ip !== '' && $ip !== NULL ) {
			return (int)$this->mDb->getOne(
				"SELECT COUNT(*) FROM " . TABLE_ORDERS_PAYMENTS . " WHERE `is_success`='n' AND `ip_address`=? AND `payment_date` >= ?",
				array( $ip, $since )
			);
		}
		return 0;
	}

	private function sanitizePaymentAlertVars( $pVars ) {
		$secretKeys = array( 'payment_cvv', 'CVV2', 'PWD', 'USER', 'VENDOR', 'PARTNER', 'cvv', 'password', 'passwd' );
		if( !is_array( $pVars ) ) {
			return $pVars;
		}
		$ret = array();
		foreach( $pVars as $key => $value ) {
			if( in_array( $key, $secretKeys, TRUE ) ) {
				continue;
			}
			if( is_array( $value ) ) {
				$ret[$key] = $this->sanitizePaymentAlertVars( $value );
			} else {
				$ret[$key] = $value;
			}
		}
		return $ret;
	}

	public function getFailedPayments( $pListHash = array() ) {
		$whereSql = array( "cop.`is_success` = 'n'" );
		$bindVars = array();

		$days = (int)BitBase::getParameter( $pListHash, 'days', 7 );
		if( $days < 1 ) {
			$days = 7;
		}
		$whereSql[] = "cop.`payment_date` >= ?";
		$bindVars[] = date( 'Y-m-d H:i:s', time() - ( $days * 86400 ) );

		if( $status = BitBase::getParameter( $pListHash, 'payment_status' ) ) {
			$whereSql[] = "cop.`payment_status` = ?";
			$bindVars[] = $status;
		}
		if( BitBase::verifyIdParameter( $pListHash, 'customers_id' ) ) {
			$whereSql[] = "cop.`customers_id` = ?";
			$bindVars[] = $pListHash['customers_id'];
		}
		if( $email = BitBase::getParameter( $pListHash, 'customers_email' ) ) {
			$whereSql[] = "LOWER(cop.`customers_email`) LIKE ?";
			$bindVars[] = '%'.strtolower( $email ).'%';
		}
		if( $ip = BitBase::getParameter( $pListHash, 'ip_address' ) ) {
			$whereSql[] = "cop.`ip_address` = ?";
			$bindVars[] = $ip;
		}
		if( $module = BitBase::getParameter( $pListHash, 'payment_module' ) ) {
			$whereSql[] = "cop.`payment_module` = ?";
			$bindVars[] = $module;
		}

		$max = (int)BitBase::getParameter( $pListHash, 'max_records', 250 );
		if( $max < 1 || $max > 1000 ) {
			$max = 250;
		}

		$sql = "SELECT cop.*, co.`orders_id` AS `existing_orders_id`
			FROM " . TABLE_ORDERS_PAYMENTS . " cop
			LEFT JOIN " . TABLE_ORDERS . " co ON (co.`orders_id` = cop.`orders_id`)
			WHERE ".implode( ' AND ', $whereSql )."
			ORDER BY cop.`payment_date` DESC";

		$ret = array();
		if( $rs = $this->mDb->query( $sql, $bindVars, $max ) ) {
			while( $row = $rs->fetchRow() ) {
				$ret[] = $row;
			}
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

		if( $rs = $this->mDb->query( "SELECT * FROM " . TABLE_ORDERS . " co INNER JOIN " . TABLE_ORDERS_PAYMENTS . " cop ON (co.`orders_id`=cop.`orders_id`) WHERE co.`orders_status_id` > 0 AND co.`amount_due` > 0 $whereSql ORDER BY co.`orders_id`, cop.`payment_number`", $bindVars ) ) {
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
									break;
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
											$this->mErrors = $this->mPaymentObjects[$this->selected_module]->mErrors;
											$this->recordFailedPayment( $tempOrder, $pParamHash, $this->mPaymentObjects[$this->selected_module] );
											$this->mErrors['errors'][] = tra( 'Payment Failed' ).': '.BitBase::getParameter( $pParamHash['result'], 'payment_result' );
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


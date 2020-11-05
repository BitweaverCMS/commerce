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

class CommercePaymentManager {
	private $selected_module;
	private $mPaymentObjects;

	public $mErrors = array();

	// class constructor
	function __construct($module = '') {
		global $gCommerceSystem;

		$this->mPaymentObjects = $gCommerceSystem->scanModules( 'payment', TRUE );
		
		// if there is only one payment method, select it as default because in
		// checkout_confirmation.php the $payment variable is being assigned the
		// $_POST['payment'] value which will be empty (no radio button selection possible)
		if( count( $this->mPaymentObjects ) == 1 ) {
			$paymentModule = current( $this->mPaymentObjects );
			$_SESSION['payment'] = $paymentModule->code;
		}

		if( !empty( $module ) && !empty( $this->mPaymentObjects[$module] ) ) {
			$this->selected_module = $module;
		}
	}


	function isModuleActive( $pModuleName ) {
		return !empty( $this->mPaymentObjects[$pModuleName] );
	}

	// class methods
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

	function verifyPayment( &$pPaymentParams, &$pOrder ) {
		$ret = FALSE;
		if( $pOrder->hasPaymentDue() ) {	
			if ( !empty( $this->mPaymentObjects[$this->selected_module] ) && is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) ) {
				if( !($ret = $this->mPaymentObjects[$this->selected_module]->verifyPayment( $pPaymentParams, $pOrder )) ) {
					$this->mErrors = $this->mPaymentObjects[$this->selected_module]->mErrors;
				}
			}
		} else {
			$ret = TRUE;
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

	function processPayment( $pPaymentParams, $pOrder ) {
		global $gBitProduct;
		$ret = NULL;

		$gBitProduct->invokeServices( 'commerce_pre_purchase_function', $pOrder );
		if( !empty( $this->mPaymentObjects[$this->selected_module] ) && !empty( $this->mPaymentObjects[$this->selected_module]->enabled ) ) {
			if( $ret = $this->mPaymentObjects[$this->selected_module]->processPayment( $pPaymentParams, $pOrder ) ) {
				if( isset( $_SESSION['orders_id'] ) ) {
					unset( $_SESSION['orders_id'] );
				}
			}
		}
		return $ret;
	}

	function after_order_create($zf_order_id) {
		global $gBitUser, $gBitProduct, $gCommerceSystem, $order;
		$ret = NULL;
		if( round( $order->getField( 'total', 2 ) ) > 0 && ($groupId = $gCommerceSystem->getConfig( 'CUSTOMERS_PURCHASE_GROUP' )) ) {
			$gBitUser->addUserToGroup( $gBitUser->mUserId, $groupId );
		}
		$gBitProduct->invokeServices( 'commerce_post_purchase_function', $order );
		if (!empty($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) && (method_exists($this->mPaymentObjects[$this->selected_module], 'after_order_create'))) {
			return $this->mPaymentObjects[$this->selected_module]->after_order_create($zf_order_id);
		}
		return $ret;
	}

	function admin_notification($zf_order_id) {
		if (is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) && (method_exists($this->mPaymentObjects[$this->selected_module], 'admin_notification'))) {
			return $this->mPaymentObjects[$this->selected_module]->admin_notification($zf_order_id);
		}
	}

	function get_error() {
		if (is_object($this->mPaymentObjects[$this->selected_module]) && ($this->mPaymentObjects[$this->selected_module]->enabled) ) {
			return $this->mPaymentObjects[$this->selected_module]->get_error();
		}
	}
}
?>

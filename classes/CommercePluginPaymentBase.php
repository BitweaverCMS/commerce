<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 *
 * Copyright (c) 2013 bitweaver.org
 * All Rights Reserved. See below for details and a complete list of authors.
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE. See http://www.gnu.org/licenses/gpl.html for details
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginBase.php' );

abstract class CommercePluginPaymentBase extends CommercePluginBase {

	var $cc_type;
	var $cc_owner;
	var $cc_number;
	var $cc_cvv;
	var $cc_expires_month;
	var $cc_expires_year;

	public function __construct() {
		parent::__construct();
	}

	function clearSessionDetails() {
		foreach( $this->getSessionVars() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	function saveSessionDetails() {
		foreach( $this->getSessionVars() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	// Default methods
	protected function getSessionVars() {
		return array();
	}

	function selection() {
		return array( 'id' => $this->code, 'module' => $this->title );
	}

	function getTransactionReference() {
		// default implementation
		return NULL;
	}

	function processPayment( $pPaymentParameters ) {
		$this->mErrors['process_payment'] = 'This modules has not implemented the ::processPayment method. ('.$this->code.')';
		return FALSE;
	}

	function pre_confirmation_check( $pPaymentParameters ) {
		return false;
	}

	function confirmation( $pPaymentParameters ) {
		return false;
	}

	function process_button( $pPaymentParameters ) {
		return false;
	}

	function before_process( $pPaymentParameters ) {
		return false;
	}

	function after_process( $pPaymentParameters ) {
		return false;
	}

	function get_error() {
		return false;
	}

	function javascript_validation() {
		return false;
	}


}

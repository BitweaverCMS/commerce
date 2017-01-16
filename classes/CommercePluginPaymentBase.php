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

	public function __construct() {
		parent::__construct();
	}

	protected function getStatusKey() {
		return 'MODULE_PAYMENT_'.strtoupper( $this->code ).'_STATUS';
	}

	protected function clearSessionDetails() {
		foreach( $this->getSessionVars() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	protected function saveSessionDetails() {
		foreach( $this->getSessionVars() as $var ) {
			$_SESSION[$var] = $this->$var;
		}	
	}

	// Default methods that should be overridden in derived classes
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

	function processPayment( &$pPaymentParameters, &$pOrder ) {
		$this->mErrors['process_payment'] = 'This modules has not implemented the ::processPayment method. ('.$this->code.')';
		return FALSE;
	}

	function confirmation( $pPaymentParameters ) {
		return false;
	}

	function process_button( $pPaymentParameters ) {
		return false;
	}

	function verifyPayment( &$pPaymentParameters, &$pOrder ) {
		return false;
	}

	function get_error() {
		return false;
	}

	function javascript_validation() {
		return false;
	}


}

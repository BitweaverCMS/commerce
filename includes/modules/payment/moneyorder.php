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
// $Id$
//

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentCardBase.php' );

class moneyorder extends CommercePluginPaymentBase {

	function __construct() {
		parent::__construct();
		$this->title = tra( 'Check/Money Order' );
		$this->description = tra( 'Please make your check or money order payable to ...' );
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 20;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_PAYTO' => array(
				'configuration_title' => 'Make Payable to:',
				'configuration_description' => 'Who should payments be made payable to?',
				'configuration_value' => STORE_NAME,
			),
			$this->getModuleKeyTrunk().'_EMAIL_FOOTER' => array(
				'configuration_title' => 'Email Footer',
				'configuration_description' => 'Footer text appended to order email',
				'configuration_value' => "Please make your check or money order payable to:\n\n" . STORE_NAME . "\n\nMail your payment to:\n" . STORE_NAME_ADDRESS . "\n\nYour order will not ship until we receive payment.",
				'set_function' => 'zen_cfg_textarea(',
			),
		) );
	}
}

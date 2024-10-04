<?php
// +------------------------------------------------------------------------+
// |zen-cart Open Source E-commerce											|
// +------------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers								|
// | http://www.zen-cart.com/index.php										|
// | Portions Copyright (c) 2003 osCommerce									|
// +------------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			|
// | that is bundled with this package in the file LICENSE, and is			|
// | available through the world-wide-web at the following url:				|
// | http://www.zen-cart.com/license/2_0.txt.								|
// | If you did not receive a copy of the zen-cart license and are unable	|
// | to obtain it through the world-wide-web, please send a note to			|
// | license@zen-cart.com so we can mail you a copy immediately.			|
// +------------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginPaymentCardBase.php' );

class purchase_order extends CommercePluginPaymentBase {
	var $mPONumber;

	function __construct() {
		parent::__construct();
		$this->description = 'Payment via purchase request from verified organization';
	}

	function selection() {
		$ret = array();
		if( $this->isUserEnabled() ) {
			$ret = array(	'id' => $this->code,
								'module' => $this->title,
								'fields' => array(	
												array( 'title' => 'Purchaser Name', 'field' => zen_draw_input_field('payment_po_contact') ),
												array( 'title' => 'Purchaser Organization', 'field' => zen_draw_input_field('payment_po_org') ),
												array( 'title' => 'PO Number', 'field' => zen_draw_input_field('payment_po_number')),
											),
								);
		}
		return $ret;
	}

	protected function getSessionVars() {
		return array( 'payment_po_contact', 'payment_po_org', 'payment_po_number' );
	}

	function confirmation( $pPaymentParams ) {

		$confirmation = array(	'title' => $this->title,
								'fields' => array(
									array( 'title' => 'Purchaser Name', 'field' => $this->getParameter( $pPaymentParams, 'payment_po_contact' ) ),
									array( 'title' => 'Purchaser Organization', 'field' => $this->getParameter( $pPaymentParams, 'payment_po_org' ) ),
									array( 'title' => 'PO Number', 'field' => $this->getParameter( $pPaymentParams, 'payment_po_number' ) ),
								)
							);

		return $confirmation;
	}

	function processPayment( $pOrder, &$pPaymentParams ) {

		if( $ret = self::verifyPayment ( $pOrder, $pPaymentParams ) ) {
			$pOrder->info['amount_due'] = $this->getParameter( $pPaymentParams, 'payment_amount' );
			$logHash = $this->logTransactionPrep( $pOrder, $pPaymentParams );

			$defaultCurrency = $this->getDefaultCurrency();

			$logHash['is_success'] = 'y';
			$logHash['payment_status'] = 'pending';
			$logHash['payment_ref_id'] = trim( $pPaymentParams['payment_po_org'].' / '.$pPaymentParams['payment_po_contact'] .' / '.$pPaymentParams['payment_po_number'] );
			$logHash['payment_result'] = '1';
			$logHash['payment_message'] = trim( 'Purchase Order Recevied' );

			// DEFAULT CURRENCY only for now
			$logHash['payment_currency'] = $defaultCurrency; // $pPaymentParams['payment_currency'];
			$logHash['exchange_rate'] = 1.0; // $pPaymentParams['exchange_rate'];
			$logHash['payment_number'] = $pPaymentParams['payment_po_number'];
			$logHash['payment_type'] = 'Purchase Order';
			$logHash['payment_owner'] = trim( $pPaymentParams['payment_po_org'].' '.$pPaymentParams['payment_po_contact'] );
			$logHash['payment_expires'] = NULL;

			$this->logTransaction( $logHash );
		}

		return $ret;
	}

	protected function config() {
		$i = 20;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_PERMISSION' => array(
				'configuration_title' => 'Purchase Order Permission',
				'configuration_description' => '<strong>Ex: p_bitcommerce_purchase_order</strong> This permission is required to show and accept Purchase Order. Leave empty to give access to all customers. Make sure you have a <a href="/users/admin/edit_group.php">Group configured</a> with that permission, and desired users are also in that group.',
				'configuration_value' => 'p_bitcommerce_purchase_order',
				'sort_order' => $i++,
			)
		) );
	}
}

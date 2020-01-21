<?php
//
// +------------------------------------------------------------------------+
// |zen-cart Open Source E-commerce											|
// +------------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers								|
// |																		|
// | http://www.zen-cart.com/index.php										|
// |																		|
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
//

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentCardBase.php' );

class purchase_order extends CommercePluginPaymentBase {
	var $mPONumber;

	function __construct() {
		parent::__construct();
		$this->title = 'Purchase Order';
		$this->description = 'Payment via purchase request from verified organization';
	}

	function selection() {
		$selection = array(	'id' => $this->code,
							'module' => $this->title,
							'fields' => array(	
											array( 'title' => 'Purchaser Name', 'field' => zen_draw_input_field('po_contact') ),
											array( 'title' => 'Purchaser Organization', 'field' => zen_draw_input_field('po_org') ),
											array( 'title' => 'PO Number', 'field' => zen_draw_input_field('po_number')),
										),
							);

		return $selection;
	}

	function verifyPayment( &$pPaymentParameters, &$pOrder ) {
		foreach( array( 'po_contact' => 'Purchaser Name',  'po_org' => 'Purchaser Organization', 'po_number' => 'PO Number' ) as $key=>$title ) {
			if( empty( $pPaymentParameters[$key] ) ) {
				$this->mErrors[$key] = $title.' was not set.';
			}
		}
		return (count( $this->mErrors ) === 0);
	}

	function confirmation( $pPaymentParameters ) {
		global $_POST;

		$confirmation = array(	'title' => $this->title,
								'fields' => array(
									array( 'title' => 'Purchaser Name', 'field' => $_POST['po_contact'] ),
									array( 'title' => 'Purchaser Organization', 'field' => $_POST['po_org'] ),
									array( 'title' => 'PO Number', 'field' => $_POST['po_number'] ),
								)
							);

		return $confirmation;
	}

	function process_button( $pPaymentParameters ) {
		global $_POST;

		$ret =	zen_draw_hidden_field( 'po_contact', $_POST['po_contact'] ) .
				zen_draw_hidden_field( 'po_org', $_POST['po_org'] ) .
				zen_draw_hidden_field( 'po_number', $_POST['po_number'] );

		return $ret;
	}

	function processPayment( &$pPaymentParameters, &$pOrder ) {
		if( $ret = self::verifyPayment ( $pPaymentParameters, $pOrder ) ) {
			// Calculate the next expected order id
			$logHash = array( 'orders_id' => $pOrder->getNextOrderId() );
			$logHash['ref_id'] = trim( $pPaymentParameters['po_org'].' / '.$pPaymentParameters['po_contact'] .' / '.$pPaymentParameters['po_number'] );
			$logHash['trans_result'] = '1';
			$logHash['trans_message'] = trim( 'Purchase Order Recevied' );

			$pOrder->info['cc_number'] = $pPaymentParameters['po_number'];
			$pOrder->info['cc_type'] = 'Purchase Order';
			$pOrder->info['cc_owner'] = trim( $pPaymentParameters['po_org'].' '.$pPaymentParameters['po_contact'] );
			$pOrder->info['cc_expires'] = NULL;
			$pOrder->info['cc_cvv'] = NULL;
		}

		$this->logTransaction( $responseHash, $pOrder );
		return $ret;
	}

}

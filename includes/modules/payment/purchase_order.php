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
		$this->title = 'Purchase Order';
		$this->description = 'Payment via purchase request from verified organization';
	}

	function selection() {
		$ret = array();
		if( $this->isUserPermitted() ) {
			$ret = array(	'id' => $this->code,
								'module' => $this->title,
								'fields' => array(	
												array( 'title' => 'Purchaser Name', 'field' => zen_draw_input_field('po_contact') ),
												array( 'title' => 'Purchaser Organization', 'field' => zen_draw_input_field('po_org') ),
												array( 'title' => 'PO Number', 'field' => zen_draw_input_field('po_number')),
											),
								);
		}
		return $ret;
	}

	private function isUserPermitted( $pUserId=NULL ) {
		if( $ret = parent::isEnabled() ) {
			if( $poPerm = $this->getModuleConfigValue( '_PERMISSION' ) ) {
				global $gBitUser;
				if( BitBase::verifyId( $pUserId ) && $checkUser = BitUser::getUserObject( $uid ) ) {
				} else {
					$checkUser = &$gBitUser;
				}
				$ret = $checkUser->hasPermission( $poPerm );
			}
		}
		return $ret;
	}

	function verifyPayment( &$pPaymentParams, &$pOrder ) {
		if( parent::verifyPayment( $pPaymentParams, $pOrder ) ) {
			foreach( array( 'po_contact' => 'Purchaser Name',  'po_org' => 'Purchaser Organization', 'po_number' => 'PO Number' ) as $key=>$title ) {
				if( empty( $pPaymentParams[$key] ) ) {
					$this->mErrors[$key] = $title.' was not set.';
				}
			}
		}
		return (count( $this->mErrors ) === 0);
	}

	function confirmation( $pPaymentParams ) {
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

	function process_button( $pPaymentParams ) {
		global $_POST;

		$ret =	zen_draw_hidden_field( 'po_contact', $_POST['po_contact'] ) .
				zen_draw_hidden_field( 'po_org', $_POST['po_org'] ) .
				zen_draw_hidden_field( 'po_number', $_POST['po_number'] );

		return $ret;
	}

	function processPayment( &$pPaymentParams, &$pOrder ) {

		if( $ret = self::verifyPayment ( $pPaymentParams, $pOrder ) ) {
			$logHash = $this->logTransactionPrep( $pPaymentParams, $pOrder );

			$logHash['is_success'] = 'y';
			$logHash['payment_status'] = 'Success';
			$logHash['trans_ref_id'] = trim( $pPaymentParams['po_org'].' / '.$pPaymentParams['po_contact'] .' / '.$pPaymentParams['po_number'] );
			$logHash['trans_result'] = '1';
			$logHash['trans_message'] = trim( 'Purchase Order Recevied' );

			$pOrder->info['payment_number'] = $pPaymentParams['po_number'];
			$pOrder->info['payment_type'] = 'Purchase Order';
			$pOrder->info['payment_owner'] = trim( $pPaymentParams['po_org'].' '.$pPaymentParams['po_contact'] );
			$pOrder->info['payment_expires'] = NULL;

			$this->logTransaction( $logHash, $pOrder );
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

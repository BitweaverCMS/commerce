<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
// $Id$
//

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginPaymentCardBase.php' );

class purchase_order extends CommercePluginPaymentBase {
	var $mPONumber;

// class constructor
	function __construct() {
		global $order;

		parent::__construct();
		$this->code = 'purchase_order';
		$this->title = 'Purchase Order';
		$this->description = 'Payment via purchase request from verified organization';
		$this->sort_order = defined( 'MODULE_PAYMENT_PURCHASEORDER_SORT_ORDER' ) ? MODULE_PAYMENT_PURCHASEORDER_SORT_ORDER : 0;
		$this->enabled = $this->isEnabled();

		if( defined( 'MODULE_PAYMENT_PURCHASEORDER_ORDER_STATUS_ID' ) && (int)MODULE_PAYMENT_PURCHASEORDER_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_PURCHASEORDER_ORDER_STATUS_ID;
		}

		if (is_object($order)) $this->update_status();
	}

	protected function getStatusKey() {
		return 'MODULE_PAYMENT_PURCHASEORDER_STATUS';
	}

// class methods
	function update_status() {
		global $order, $gBitDb;

		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PURCHASEORDER_ZONE > 0) ) {
			$check_flag = false;
			$check = $gBitDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_PAYMENT_PURCHASEORDER_ZONE . "' and `zone_country_id` = '" . $order->delivery['country']['countries_id'] . "' order by `zone_id`");
			while (!$check->EOF) {
				if ($check->fields['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
					$check_flag = true;
					break;
				}
				$check->MoveNext();
			}

			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}

	function javascript_validation() {
		return false;
	}

    function selection() {

      $selection = array('id' => $this->code,
                         'module' => $this->title,
                         'fields' => array(array('title' => 'Purchaser Name',
                                                 'field' => zen_draw_input_field('po_contact')),
                                           array('title' => 'Purchaser Organization',
                                                 'field' => zen_draw_input_field('po_org')),
                                           array('title' => 'PO Number',
                                                 'field' => zen_draw_input_field('po_number'))));

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

      $confirmation = array('title' => $this->title,
                            'fields' => array(array( 'title' => 'Purchaser Name',
                                                    'field' => $_POST['po_contact']),
                                              array( 'title' => 'Purchaser Organization',
                                                    'field' => $_POST['po_org']),
                                              array( 'title' => 'PO Number',
                                                    'field' => $_POST['po_number'])));

      return $confirmation;
    }

    function process_button( $pPaymentParameters ) {
      global $_POST;

      $process_button_string = zen_draw_hidden_field('po_contact', $_POST['po_contact']) .
                               zen_draw_hidden_field('po_org', $_POST['po_org']) .
                               zen_draw_hidden_field('po_number', $_POST['po_number']);

      return $process_button_string;
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

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable Purchase Order Module', 'MODULE_PAYMENT_PURCHASEORDER_STATUS', 'True', 'Do you want to accept Purchase Order payments?', '6', '1', 'zen_cfg_select_option(array(''True'', ''False''), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Payment Zone', 'MODULE_PAYMENT_PURCHASEORDER_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort order of display.', 'MODULE_PAYMENT_PURCHASEORDER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_PAYMENT_PURCHASEORDER_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', 'NOW')");
 }

	function remove() {
		global $gBitDb;
		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
		return array('MODULE_PAYMENT_PURCHASEORDER_STATUS', 'MODULE_PAYMENT_PURCHASEORDER_ZONE', 'MODULE_PAYMENT_PURCHASEORDER_ORDER_STATUS_ID', 'MODULE_PAYMENT_PURCHASEORDER_SORT_ORDER');
	}
}
?>

<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginOrderTotalBase.php' );

class ot_loworderfee extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->code = 'ot_loworderfee';

		$this->title = MODULE_ORDER_TOTAL_LOWORDERFEE_TITLE;
		$this->description = MODULE_ORDER_TOTAL_LOWORDERFEE_DESCRIPTION;
		$this->sort_order = MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER;
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS';
	}

	function process() {
		parent::process();
		global $currencies;

		if (MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE == 'true') {
			switch (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION) {
				case 'national':
					if ($this->mOrder->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
				case 'international':
					if ($this->mOrder->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
				case 'both':
					$pass = true; break;
				default:
					$pass = false; break;
			}

//				if ( ($pass == true) && ( ($this->mOrder->info['total'] - $this->mOrder->info['shipping_cost']) < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER) ) {
			if ( ($pass == true) && ( $this->mOrder->subtotal < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER) ) {
				$charge_it = 'true';
				$cart_content_type = $gBitCustomer->mCart->get_content_type();
				$gv_content_only = $gBitCustomer->mCart->gv_only();
				if ($cart_content_type == 'physical' or $cart_content_type == 'mixed') {
					$charge_it = 'true';
				} else {
					// check to see if everything is virtual, if so - skip the low order fee.
					if ((($cart_content_type == 'virtual') and MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL == 'true')) {
						$charge_it = 'false';
						if ((($gv_content_only > 0) and MODULE_ORDER_TOTAL_LOWORDERFEE_GV == 'false')) {
							$charge_it = 'true';
						}
					}

					if ((($gv_content_only > 0) and MODULE_ORDER_TOTAL_LOWORDERFEE_GV == 'true')) {
					// check to see if everything is gift voucher, if so - skip the low order fee.
						$charge_it = 'false';
						if ((($cart_content_type == 'virtual') and MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL == 'false')) {
							$charge_it = 'true';
						}
					}
				}

				if ($charge_it == 'true') {
					$tax = zen_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);
					$tax_description = zen_get_tax_description(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);

// calculate from flat fee or percentage
					if (substr(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, -1) == '%') {
						$low_order_fee = ($this->mOrder->subtotal * (MODULE_ORDER_TOTAL_LOWORDERFEE_FEE/100));
					} else {
						$low_order_fee = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE;
					}


					$this->mOrder->info['tax'] += zen_calculate_tax($low_order_fee, $tax);
					$this->mOrder->info['tax_groups']["$tax_description"] += zen_calculate_tax($low_order_fee, $tax);
					$this->mOrder->info['total'] += $low_order_fee + zen_calculate_tax($low_order_fee, $tax);

					$this->mProcessingOutput = array( 'code' => $this->code,
														'sort_order' => $this->getSortOrder(),
														'title' => $this->title,
														'text' => $currencies->format(zen_add_tax($low_order_fee, $tax), true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
														'value' => zen_add_tax($low_order_fee, $tax));
				}
			}
		}
	}

	function keys() {
		return array('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', 'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', 'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS', 'MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL', 'MODULE_ORDER_TOTAL_LOWORDERFEE_GV');
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', '400', 'Sort order of display.', '6', '2', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Allow Low Order Fee', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 'false', 'Do you want to allow low order fees?', '6', '3', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `date_added`) values ('Order Fee For Orders Under', 'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', '50', 'Add the low order fee to orders under this amount.', '6', '4', 'currencies->format', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `date_added`) values ('Order Fee', 'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', '5', 'For Percentage Calculation - include a % Example: 10%<br />For a flat amount just enter the amount - Example: 5 for $5.00', '6', '5', '', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Attach Low Order Fee On Orders Made', 'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 'both', 'Attach low order fee for orders sent to the set destination.', '6', '6', 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS', '0', 'Use the following tax class on the low order fee.', '6', '7', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('No Low Order Fee on Virtual Products', 'MODULE_ORDER_TOTAL_LOWORDERFEE_VIRTUAL', 'false', 'Do not charge Low Order Fee when cart is Virtual Products Only', '6', '8', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('No Low Order Fee on Gift Vouchers', 'MODULE_ORDER_TOTAL_LOWORDERFEE_GV', 'false', 'Do not charge Low Order Fee when cart is Gift Vouchers Only', '6', '9', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
	}
}

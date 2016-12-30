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

class ot_group_pricing extends CommercePluginOrderTotalBase {

	function __construct( $pOrder ) {
		$this->code = 'ot_group_pricing';
		$this->mStatusKey = 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS'; 

		parent::__construct( $pOrder );

		if( $this->isEnabled() ) {
			$this->title = MODULE_ORDER_TOTAL_GROUP_PRICING_TITLE;
			$this->description = MODULE_ORDER_TOTAL_GROUP_PRICING_DESCRIPTION;
			$this->sort_order = MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER;
			$this->include_shipping = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING;
			$this->include_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX;
			$this->calculate_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX;
			$this->credit_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CREDIT_TAX;
			$this->credit_class = true;
		}
	}

	function process() {
		parent::process();
		global $currencies;

		if( $groupDiscount = $this->getGroupDiscount( $_SESSION['customer_id'] ) ) {
			$order_total = $this->get_order_total();
			$gift_vouchers = $gBitCustomer->mCart->gv_only();
			$discount = ($order_total - $gift_vouchers) * $groupDiscount['group_percentage'] / 100;
			$od_amount = zen_round($discount, 2);
			if ($this->calculate_tax != "none") {
				$tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax, true);
//					$od_amount = $this->calculate_credit($order_total);
			}
			$this->deduction = $od_amount;
			if ($discount > 0 ) {
				$this->mOrder->info['total'] -= $this->deduction;
				$this->mProcessingOutput = array( 'code' => $this->code,
													'title' => $this->title . ':',
													'text' => '-' . $currencies->format($this->deduction, true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
													'value' => $this->deduction);
			}
		}
	}

	function getGroupDiscount( $pCustomersId ) {
		$sql = "SELECT `customers_group_pricing`, `group_name`, `group_percentage` 
				FROM " . TABLE_CUSTOMERS . " 
					INNER JOIN " . TABLE_GROUP_PRICING . " ON (cc.`customers_group_pricing`=cgp.`group_id` 
				WHERE `customers_id` = ?"	
		return $this->mDb->GetRow( $sql, array( $pCustomersId ) );
	}

	function get_order_total() {
		$order_total = $this->mOrder->info['total'];
		if ($this->include_tax == 'false') $order_total = $order_total - $this->mOrder->info['tax'];
		if ($this->include_shipping == 'false') $order_total = $order_total - $this->mOrder->info['shipping_cost'];

		return $order_total;
	}

	function calculate_tax_deduction($amount, $od_amount, $method, $finalise = false) {
		$tax_address = zen_get_tax_locations();
		switch ($method) {
			case 'Standard':
			if ($amount == 0) {
				$ratio1 = 0;
			} else {
				$ratio1 = zen_round($od_amount / $amount,2);
			}
			$tod_amount = 0;
			reset($this->mOrder->info['tax_groups']);
			while (list($key, $value) = each($this->mOrder->info['tax_groups'])) {
				$tax_rate = zen_get_tax_rate_from_desc($key, $tax_address['country_id'], $tax_address['zone_id']);
				$total_net += $tax_rate * $value;
			}
			if ($od_amount > $total_net) $od_amount = $total_net;
			reset($this->mOrder->info['tax_groups']);
			while (list($key, $value) = each($this->mOrder->info['tax_groups'])) {
				$tax_rate = zen_get_tax_rate_from_desc($key, $tax_address['country_id'], $tax_address['zone_id']);
				$net = $tax_rate * $value;
				if ($net > 0) {
					$god_amount = $value * $ratio1;
					$tod_amount += $god_amount;
					if ($finalise) $this->mOrder->info['tax_groups'][$key] = $this->mOrder->info['tax_groups'][$key] - $god_amount;
				}
			}
			if ($finalise) $this->mOrder->info['tax'] -= $tod_amount;
			if ($finalise) $this->mOrder->info['total'] -= $tod_amount;
			break;
			case 'Credit Note':
				$tax_rate = zen_get_tax_rate($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
				$tax_desc = zen_get_tax_description($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
				$tod_amount = $this->deduction / (100 + $tax_rate)* $tax_rate;
				if ($finalise) $this->mOrder->info['tax_groups'][$tax_desc] -= $tod_amount;
				if ($finalise) $this->mOrder->info['tax'] -= $tod_amount;
				if ($finalise) $this->mOrder->info['total'] -= $tod_amount;
			break;
			default:
		}
		return zen_round($tod_amount, 2);
	}

	function getOrderDeduction( $pOrder ) {
		$order_total = $pOrder->getField( 'total' );
		if( $this->include_shipping == 'false') $order_total -= $this->mOrder->info['shipping_cost'];
		if( $this->include_tax == 'false') $order_total -= $this->mOrder->info['tax'];
		if( $groupDiscount = $this->getGroupDiscount( $_SESSION['customer_id'] ) {
			$order_total = $this->get_order_total();
			$discount = $order_total * $groupDiscount['group_percentage'] / 100;
			$od_amount = zen_round($discount, 2);
			if ($this->calculate_tax != "none") {
				$tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
			}
		}
		return $od_amount + $tod_amount;
	}

	function keys() {
		return array('MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS');
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', '290', 'Sort order of display.', '6', '2', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'false', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'true', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS', '0', 'Use the following tax class when treating Group Discount as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
	}
}

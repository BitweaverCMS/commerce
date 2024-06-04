<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginOrderTotalBase.php' );

class ot_group_pricing extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->code = 'ot_group_pricing';
		$this->title = MODULE_ORDER_TOTAL_GROUP_PRICING_TITLE;
		$this->description = MODULE_ORDER_TOTAL_GROUP_PRICING_DESCRIPTION;

		if( $this->isEnabled() ) {
			$this->sort_order = MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER;
			$this->include_shipping = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING;
			$this->include_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX;
			$this->calculate_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX;
			$this->credit_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CREDIT_TAX;
			$this->credit_class = true;
		}
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS';
	}

	function process( $pSessionParams = array() ) {
		parent::process( $pSessionParams );
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
				$this->mOrder->info['deduction'][$this->code] = $this->deduction;
				$this->mProcessingOutput = array( 'code' => $this->code,
													'sort_order' => $this->getSortOrder(),
													'title' => $this->title,
													'text' => '-' . $currencies->format($this->deduction, true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
													'value' => $this->deduction);
			}
		}
	}

	function getGroupDiscount( $pCustomersId ) {
		$sql = "SELECT `customers_group_pricing`, `group_name`, `group_percentage` 
				FROM " . TABLE_CUSTOMERS . " cc
					INNER JOIN " . TABLE_GROUP_PRICING . " cgp ON (cc.`customers_group_pricing`=cgp.`group_id`)
				WHERE `customers_id` = ?";
		return $this->mDb->GetRow( $sql, array( $pCustomersId ) );
	}

	function get_order_total() {
		$order_total = $this->mOrder->getField( 'total' );
		if ($this->include_tax == 'false') $order_total = $order_total - $this->mOrder->info['tax'];
		if ($this->include_shipping == 'false') $order_total = $order_total - $this->mOrder->info['shipping_cost'];

		return $order_total;
	}

	function calculate_tax_deduction($amount, $od_amount, $method, $finalise = false) {
		$ret = 0;
		if( $this->isEnabled() ) {
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
					if ($finalise) {
						$this->mOrder->info['tax'] -= $tod_amount;
						$this->setOrderDeduction( $tod_amount );
					}
					break;
				case 'Credit Note':
					$tax_rate = zen_get_tax_rate($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
					$tax_desc = zen_get_tax_description($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
					$tod_amount = $this->deduction / (100 + $tax_rate)* $tax_rate;
					if ($finalise) {
						$this->mOrder->info['tax_groups'][$tax_desc] -= $tod_amount;
						$this->mOrder->info['tax'] -= $tod_amount;
						$this->setOrderDeduction( $tod_amount );
					}
					break;
			}
			$ret = zen_round($tod_amount, 2);
		}
		return $ret;
	}

	function getOrderDeduction( $pOrder ) {
		$ret = 0;
		if( $this->isEnabled() ) {
			$order_total = $pOrder->getField( 'total' );
			$tod_amount = 0;
			if( $this->include_shipping == 'false') $order_total -= $this->mOrder->info['shipping_cost'];
			if( $this->include_tax == 'false') $order_total -= $this->mOrder->info['tax'];
			if( $groupDiscount = $this->getGroupDiscount( $_SESSION['customer_id'] ) ) {
				$order_total = $this->get_order_total();
				$discount = $order_total * $groupDiscount['group_percentage'] / 100;
				$od_amount = zen_round($discount, 2);
				if ($this->calculate_tax != "none") {
					$tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
				}
			}
			$ret = $od_amount + $tod_amount;
		}
		return $ret;
	}

	public function keys() {
		return array_merge(
					array_keys( $this->config() ),
					array('MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS')
				);
	}

	function install() {
		parent::install();
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'false', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'true', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS', '0', 'Use the following tax class when treating Group Discount as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
	}

	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$ret = parent::config();
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '290';
		return $ret;
	}
}

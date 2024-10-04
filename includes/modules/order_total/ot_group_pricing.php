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

		if( $this->isEnabled() ) {
			$this->include_shipping = $this->getModuleConfigValue( '_INC_SHIPPING' );
			$this->include_tax = $this->getModuleConfigValue( '_INC_TAX' );
			$this->calculate_tax = $this->getModuleConfigValue( '_CALC_TAX' );
			$this->credit_tax = $this->getModuleConfigValue( '_CREDIT_TAX' );
			$this->credit_class = true;
		}
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS';
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
		global $currencies;

		if( $groupDiscount = $this->getGroupDiscount( $pSessionParams['customer_id'] ) ) {
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

	function getOrderDeduction( $pOrder, &$pSessionParams ) {
		$ret = 0;
		if( $this->isEnabled() ) {
			$order_total = $pOrder->getField( 'total' );
			$tod_amount = 0;
			if( $this->include_shipping == 'false') $order_total -= $this->mOrder->info['shipping_cost'];
			if( $this->include_tax == 'false') $order_total -= $this->mOrder->info['tax'];
			if( $groupDiscount = $this->getGroupDiscount( $pSessionParams['customer_id'] ) ) {
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

	// {{{	++++++++ config ++++++++
	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$parentConfig = parent::config();
		$i = count( $parentConfig );
		return array_merge( $parentConfig, array( 
			$this->getModuleKeyTrunk().'_INC_SHIPPING' => array(
				'configuration_title' => 'Include Shipping',
				'configuration_description' => 'Include Shipping in calculation',
				'configuration_value' => 'true',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_INC_TAX' => array(
				'configuration_title' => 'Include Tax',
				'configuration_description' => 'Include Tax in calculation.',
				'configuration_value' => 'true',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_CALC_TAX' => array(
				'configuration_title' => 'Re-calculate Tax',
				'configuration_description' => 'Re-Calculate Tax',
				'configuration_value' => 'None',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('None', 'Standard', 'Credit Note'), ",
			),
			$this->getModuleKeyTrunk().'_TAX_CLASS' => array(
				'configuration_title' => 'Tax Class',
				'configuration_description' => 'Use the following tax class when treating Gift Voucher as Credit Note.',
				'configuration_value' => '0',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_pull_down_tax_classes(",
				'use_function' => "zen_get_tax_class_title",
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '290';
		return $ret;
	}
	// }}} ++++ config ++++

}

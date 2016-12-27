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

class ot_shipping extends CommercePluginOrderTotalBase {

	function __construct( $pOrder ) {
		$this->code = 'ot_shipping';
		$this->mStatusKey = 'MODULE_ORDER_TOTAL_SHIPPING_STATUS';

		parent::__construct( $pOrder );

		$this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
		$this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
		$this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;
	}

	function process() {
		parent::process();
		global $currencies;

		if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
			switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
				case 'national':
				if ($this->mOrder->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
				case 'international':
				if ($this->mOrder->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
				case 'both':
				$pass = true; break;
				default:
				$pass = false; break;
			}

			if ( ($pass == true) && ( ($this->mOrder->info['total'] - $this->mOrder->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
				$this->mOrder->info['shipping_method'] = $this->title;
				$this->mOrder->info['total'] -= $this->mOrder->info['shipping_cost'];
				$this->mOrder->info['shipping_cost'] = 0;
			}
		}
		if( !empty( $_SESSION['shipping'] ) ) {
			$module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));
			if( !empty( $this->mOrder->info['shipping_method'] ) ) {
				if( !empty( $GLOBALS[$module]->tax_class ) ) {
					if (!defined($GLOBALS[$module]->tax_basis)) {
						$shipping_tax_basis = STORE_SHIPPING_TAX_BASIS;
					} else {
						$shipping_tax_basis = $GLOBALS[$module]->tax_basis;
					}

					if ($shipping_tax_basis == 'Billing') {
						$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->billing['country']['countries_id'], $this->mOrder->billing['zone_id']);
						$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->billing['country']['countries_id'], $this->mOrder->billing['zone_id']);
					} elseif ($shipping_tax_basis == 'Shipping') {
						$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->delivery['country']['countries_id'], $this->mOrder->delivery['zone_id']);
						$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->delivery['country']['countries_id'], $this->mOrder->delivery['zone_id']);
					} else {
						if (STORE_ZONE == $this->mOrder->billing['zone_id']) {
							$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->billing['country']['countries_id'], $this->mOrder->billing['zone_id']);
							$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->billing['country']['countries_id'], $this->mOrder->billing['zone_id']);
						} elseif (STORE_ZONE == $this->mOrder->delivery['zone_id']) {
							$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->delivery['country']['countries_id'], $this->mOrder->delivery['zone_id']);
							$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->delivery['country']['countries_id'], $this->mOrder->delivery['zone_id']);
						} else {
							$shipping_tax = 0;
						}
					}
					$this->mOrder->info['tax'] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);
					$this->mOrder->info['tax_groups']["$shipping_tax_description"] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);
					$this->mOrder->info['total'] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);

					if (DISPLAY_PRICE_WITH_TAX == 'true') $this->mOrder->info['shipping_cost'] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);
				}

				if ($_SESSION['shipping'] == 'free_free') {
					$this->mOrder->info['shipping_method'] = FREE_SHIPPING_TITLE;
				}

				$this->mProcessingOutput = array( 'code' => $this->code,
													'title' => $this->mOrder->info['shipping_method'] . ':',
													'text' => $currencies->format($this->mOrder->info['shipping_cost'], true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
													'value' => $this->mOrder->info['shipping_cost']);
			}
		}
	}

	function keys() {
		return array('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION');
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '200', 'Sort order of display.', '6', '2', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Allow Free Shipping', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Do you want to allow free shipping?', '6', '3', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `date_added`) values ('Free Shipping For Orders Over', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '50', 'Provide free shipping for orders over the set amount.', '6', '4', 'currencies->format', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Provide Free Shipping For Orders Made', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Provide free shipping for orders sent to the set destination.', '6', '5', 'zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
	}
}

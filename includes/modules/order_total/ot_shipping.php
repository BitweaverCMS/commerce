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

class ot_shipping extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->code = 'ot_shipping';

		$this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
		$this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_SHIPPING_STATUS';
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
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

		if( !empty( $pSessionParams['shipping'] ) ) {
			if( $module = substr($pSessionParams['shipping']['id'], 0, strpos($pSessionParams['shipping']['id'], '_')) ) {
				$this->mOrder->info['shipping_method']  = BitBase::getParameter( $pSessionParams['shipping'], 'title' );
				$this->mOrder->info['shipping_module_code'] = $module;
				$this->mOrder->info['shipping_method_id'] = $pSessionParams['shipping']['id'];
				if( !empty( $GLOBALS[$module]->tax_class ) ) {
					if (!defined($GLOBALS[$module]->tax_basis)) {
						$shipping_tax_basis = STORE_SHIPPING_TAX_BASIS;
					} else {
						$shipping_tax_basis = $GLOBALS[$module]->tax_basis;
					}

					if ($shipping_tax_basis == 'Billing') {
						$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->billing['countries_id'], $this->mOrder->billing['zone_id']);
						$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->billing['countries_id'], $this->mOrder->billing['zone_id']);
					} elseif ($shipping_tax_basis == 'Shipping') {
						$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);
						$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);
					} else {
						if (STORE_ZONE == $this->mOrder->billing['zone_id']) {
							$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->billing['countries_id'], $this->mOrder->billing['zone_id']);
							$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->billing['countries_id'], $this->mOrder->billing['zone_id']);
						} elseif (STORE_ZONE == $this->mOrder->delivery['zone_id']) {
							$shipping_tax = zen_get_tax_rate($GLOBALS[$module]->tax_class, $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);
							$shipping_tax_description = zen_get_tax_description($GLOBALS[$module]->tax_class, $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);
						} else {
							$shipping_tax = 0;
						}
					}
					$this->mOrder->info['tax'] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);
					$this->mOrder->info['tax_groups']["$shipping_tax_description"] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);
					$this->mOrder->info['total'] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);

					if (DISPLAY_PRICE_WITH_TAX == 'true') $this->mOrder->info['shipping_cost'] += zen_calculate_tax($this->mOrder->info['shipping_cost'], $shipping_tax);
				}

				if ($pSessionParams['shipping_method'] == 'free_free') {
					$this->mOrder->info['shipping_method'] = FREE_SHIPPING_TITLE;
				}

				$this->mProcessingOutput = array( 'code' => $this->code,
													'sort_order' => $this->getSortOrder(),
													'title' => $this->mOrder->info['shipping_method'],
													'text' => $currencies->format($this->mOrder->info['shipping_cost'], true, $this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
													'value' => $this->mOrder->info['shipping_cost']);
			}
		}
	}

	// {{{	++++++++ config ++++++++
	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 20;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_FREE_SHIPPING' => array(
				'configuration_title' => 'Allow Free Shipping',
				'configuration_description' => 'Do you want to allow free shipping?',
				'configuration_value' => 'false',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_FREE_SHIPPING_OVER' => array(
				'configuration_title' => 'Free Shipping For Orders Over',
				'configuration_description' => 'Provide free shipping for orders over the set amount',
				'configuration_value' => '50',
				'sort_order' => $i++,
				'use_function' => 'currencies->format',
			),
			$this->getModuleKeyTrunk().'_DESTINATION' => array(
				'configuration_title' => 'Free Shipping For Orders To Destination',
				'configuration_description' => 'Provide free shipping for orders over the set amount.',
				'configuration_value' => 'national',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array(\'national\', \'international\', \'both\'), ",
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '200';
		return $ret;
	}
	// }}} ++++ config ++++
}

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

class ot_cod_fee extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->title = $this->getTitle( 'Cash On Delivery Fee' );
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
		global $currencies, $cod_cost, $cod_country;

		if( $this->isEnabled() ) {
			//Will become true, if cod can be processed.
			$cod_country = false;

			//check if payment method is cod. If yes, check if cod is possible.

			if ($pSessionParams['payment'] == 'cod') {
				//process installed shipping modules
				if (substr_count($pSessionParams['shipping']['id'], 'flat') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_FLAT' ));
				if (substr_count($pSessionParams['shipping']['id'], 'free') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_FREE' ));
				if (substr_count($pSessionParams['shipping']['id'], 'freeshipper') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_FREESHIPPER' ));
				if (substr_count($pSessionParams['shipping']['id'], 'item') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_ITEM' ));
				if (substr_count($pSessionParams['shipping']['id'], 'table') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_TABLE' ));
				if (substr_count($pSessionParams['shipping']['id'], 'ups') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_UPS' ));
				if (substr_count($pSessionParams['shipping']['id'], 'usps') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_USPS' ));
				if (substr_count($pSessionParams['shipping']['id'], 'fedex') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_FEDEX' ));
				if (substr_count($pSessionParams['shipping']['id'], 'zones') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_ZONES' ));
				if (substr_count($pSessionParams['shipping']['id'], 'ap') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_AP' ));
				if (substr_count($pSessionParams['shipping']['id'], 'dp') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_DP' ));
	//satt inn av Pompel
	if (substr_count($pSessionParams['shipping']['id'], 'servicepakke') !=0) $cod_zones = split("[:,]", $this->getModuleConfigValue( '_SERVICEPAKKE' ));

					for ($i = 0; $i < count($cod_zones); $i++) {
						if ($cod_zones[$i] == $this->mOrder->delivery['countries_iso_code_2']) {
								$cod_cost = $cod_zones[$i + 1];
								$cod_country = true;
								break;
							} elseif ($cod_zones[$i] == '00') {
								$cod_cost = $cod_zones[$i + 1];
								$cod_country = true;
								break;
							} else {
								//print('no match');
							}
						$i++;
					}
				} else {
					//COD selected, but no shipping module which offers COD
				}

			if ($cod_country) {
				$cod_tax_address = zen_get_tax_locations();
				$tax = zen_get_tax_rate(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $cod_tax_address['country_id'], $cod_tax_address['zone_id']);
				$this->mOrder->info['total'] += $cod_cost;
				if ($tax > 0) {
					$tax_description = zen_get_tax_description(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $cod_tax_address['country_id'], $cod_tax_address['zone_id']);
					$this->mOrder->info['tax'] += zen_calculate_tax($cod_cost, $tax);
					$this->mOrder->info['tax_groups'][$tax_description] += zen_calculate_tax($cod_cost, $tax);
					$this->mOrder->info['total'] += zen_calculate_tax($cod_cost, $tax);
				}

				$this->mProcessingOutput = array( 'code' => $this->code,
													'title' => $this->title,
													'text' => $currencies->format($cod_cost, true,	$this->mOrder->info['currency'], $this->mOrder->info['currency_value']),
													'value' => $cod_cost);
			} else {
//Following code should be improved if we can't get the shipping modules disabled, who don't allow COD
// as well as countries who do not have cod
//					$this->mProcessingOutput = array('title' => $this->title,
//																	'text' => 'No COD for this module.',
//																	'value' => '');
			}
		}
	}

	// {{{	++++++++ config ++++++++
	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$parentConfig = parent::config();
		$i = count( $parentConfig );
		return array_merge( $parentConfig, array( 
			$this->getModuleKeyTrunk().'_FEE_FLAT' => array(
				'configuration_description' => 'COD Fee for FLAT',
				'configuration_title' => 'FLAT: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'AT:3.00,DE:3.58,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_FREE' => array(
				'configuration_description' => 'COD Fee for Free Shipping by default',
				'configuration_title' => 'Free by default: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'US:3.00',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_FREESHIPPER' => array(
				'configuration_description' => 'COD Fee for Free Shipping Module',
				'configuration_title' => 'Free Module: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'CA:4.50,US:3.00,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_ITEM' => array(
				'configuration_description' => 'COD Fee for ITEM',
				'configuration_title' => 'ITEM: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'AT:3.00,DE:3.58,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_TABLE' => array(
				'configuration_description' => 'COD Fee for TABLE',
				'configuration_title' => 'TABLE: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'AT:3.00,DE:3.58,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_UPS' => array(
				'configuration_description' => 'COD Fee for UPS',
				'configuration_title' => 'UPS: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'CA:4.50,US:3.00,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_USPS' => array(
				'configuration_description' => 'COD Fee for USPS',
				'configuration_title' => 'USPS: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'CA:4.50,US:3.00,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_ZONES' => array(
				'configuration_description' => 'COD Fee for ZONES',
				'configuration_title' => 'ZONES: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'CA:4.50,US:3.00,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_AP' => array(
				'configuration_description' => 'COD Fee for Austrian Post',
				'configuration_title' => 'Austrian Post: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'AT:3.63,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_DP' => array(
				'configuration_description' => 'COD Fee for German Post',
				'configuration_title' => 'German Post: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'DE:3.58,00:9.99',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_SERVICEPAKKE' => array(
				'configuration_description' => 'COD Fee for Servicepakke',
				'configuration_title' => 'Servicepakke: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'NO:69',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FEE_FEDEX' => array(
				'configuration_description' => 'COD Fee for FedEx',
				'configuration_title' => 'FedEx: &lt;Country code&gt;:&lt;COD price&gt;, .... 00 as country code applies for all countries. If country code is 00, it must be the last statement. If no 00:9.99 appears, COD shipping in foreign countries is not calculated (not possible)',
				'configuration_value' => 'US:3.00',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_TAX_CLASS' => array(
				'configuration_title' => 'Tax Class',
				'configuration_description' => 'Use the following tax class on the COD fee.',
				'configuration_value' => '0',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_pull_down_tax_classes(",
				'use_function' => "zen_get_tax_class_title",
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '950';
		return $ret;
	}
	// }}} ++++ config ++++
}

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

class ot_loworderfee extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
		$this->title = $this->getTitle( 'Low Order Fee' );
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
		global $currencies;

		if ($this->getModuleConfigValue( '_LOW_ORDER_FEE' ) == 'true') {
			switch ($this->getModuleConfigValue( '_DESTINATION' )) {
				case 'national':
					if ($this->mOrder->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
				case 'international':
					if ($this->mOrder->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
				case 'both':
					$pass = true; break;
				default:
					$pass = false; break;
			}

//				if ( ($pass == true) && ( ($this->mOrder->info['total'] - $this->mOrder->info['shipping_cost']) < $this->getModuleConfigValue( '_ORDER_UNDER' )) ) {
			if ( ($pass == true) && ( $this->mOrder->subtotal < $this->getModuleConfigValue( '_ORDER_UNDER' )) ) {
				$charge_it = 'true';
				$cart_content_type = $gBitCustomer->mCart->get_content_type();
				$gv_content_only = $gBitCustomer->mCart->gv_only();
				if ($cart_content_type == 'physical' or $cart_content_type == 'mixed') {
					$charge_it = 'true';
				} else {
					// check to see if everything is virtual, if so - skip the low order fee.
					if ((($cart_content_type == 'virtual') and $this->getModuleConfigValue( '_VIRTUAL' ) == 'true')) {
						$charge_it = 'false';
						if ((($gv_content_only > 0) and $this->getModuleConfigValue( '_GV' ) == 'false')) {
							$charge_it = 'true';
						}
					}

					if ((($gv_content_only > 0) and $this->getModuleConfigValue( '_GV' ) == 'true')) {
					// check to see if everything is gift voucher, if so - skip the low order fee.
						$charge_it = 'false';
						if ((($cart_content_type == 'virtual') and $this->getModuleConfigValue( '_VIRTUAL' ) == 'false')) {
							$charge_it = 'true';
						}
					}
				}

				if ($charge_it == 'true') {
					$tax = zen_get_tax_rate($this->getModuleConfigValue( '_TAX_CLASS' ), $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);
					$tax_description = zen_get_tax_description($this->getModuleConfigValue( '_TAX_CLASS' ), $this->mOrder->delivery['countries_id'], $this->mOrder->delivery['zone_id']);

// calculate from flat fee or percentage
					if (substr($this->getModuleConfigValue( '_FEE' ), -1) == '%') {
						$low_order_fee = ($this->mOrder->subtotal * ($this->getModuleConfigValue( '_FEE' )/100));
					} else {
						$low_order_fee = $this->getModuleConfigValue( '_FEE' );
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

	// {{{	++++++++ config ++++++++
	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$parentConfig = parent::config();
		$i = count( $parentConfig );
		return array_merge( $parentConfig, array( 
			$this->getModuleKeyTrunk().'_LOW_ORDER_FEE' => array(
				'configuration_title' => 'Allow Low Order Fee',
				'configuration_description' => 'Do you want to allow low order fees?',
				'configuration_value' => 'false',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_ORDER_UNDER' => array(
				'configuration_title' => 'Order Fee For Orders Under',
				'configuration_description' => 'Add the low order fee to orders under this amount.',
				'configuration_value' => '50',
				'sort_order' => $i++,
				'use_function' => 'currencies->format',
			),
			$this->getModuleKeyTrunk().'_FEE' => array(
				'configuration_title' => 'Order Fee',
				'configuration_description' => 'For Percentage Calculation - include a % Example: 10%<br />For a flat amount just enter the amount - Example: 5 for $5.00',
				'configuration_value' => '5',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_DESTINATION' => array(
				'configuration_title' => 'Attach Low Order Fee On Orders Made',
				'configuration_description' => 'Attach low order fee for orders sent to the set destination.',
				'configuration_value' => 'both',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('national', 'international', 'both'), ",
			),
			$this->getModuleKeyTrunk().'_VIRTUAL' => array(
				'configuration_title' => 'Low Order Fee on Virtual Products',
				'configuration_description' => 'Charge Low Order Fee when cart is Virtual Products Only',
				'configuration_value' => 'false',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_GV' => array(
				'configuration_title' => 'Low Order Fee on Gift Vouchers',
				'configuration_description' => 'Charge Low Order Fee when cart is Gift Vouchers Only',
				'configuration_value' => 'false',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_TAX_CLASS' => array(
				'configuration_title' => 'Tax Class',
				'configuration_description' => 'Use the following tax class on the low order fee.',
				'configuration_value' => '0',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_pull_down_tax_classes(",
				'use_function' => "zen_get_tax_class_title",
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '400';
		return $ret;
	}
	// }}} ++++ config ++++
}

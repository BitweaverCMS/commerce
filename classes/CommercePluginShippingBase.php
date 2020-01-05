<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 * Copyright (c) 2020 bitweaver.org, All Rights Reserved
 * This source file is subject to the 2.0 GNU GENERAL PUBLIC LICENSE. 
 *
 * Base class for all shipping plugins.
 *
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginBase.php' );

abstract class CommercePluginShippingBase extends CommercePluginBase {

	protected $mShipZones = NULL;

	abstract public function quote( $pShipHash );

	public function __construct() {
		parent::__construct();
		$this->quotes = array();
		$this->icon = 'shipping_'.$this->code;
		$this->title = $this->getConfig( $this->getModuleKeyTrunk().'_TEXT_TITLE', $this->code );
		$this->description = $this->getConfig( $this->getModuleKeyTrunk().'_TEXT_DESCRIPTION' );
		$this->sort_order = $this->getConfig( $this->getModuleKeyTrunk().'_SORT_ORDER' );
		$this->tax_class = $this->getConfig( $this->getModuleKeyTrunk().'_TAX_CLASS' );
		$this->tax_basis = $this->getConfig( $this->getModuleKeyTrunk().'_TAX_BASIS' );
	}

	protected function getModuleType() {
		return 'shipping';
	}

	protected function getShipperZone() {
		return $this->getConfig( $this->getModuleKeyTrunk().'_ZONE' );
	}

	protected function getShippingTax() {
		$ret = 0;
		
		if( !empty( $this->tax_class ) ) {
			$ret = zen_get_tax_rate($this->tax_class, $pShipHash['destination']['countries_id'], $pShipHash['destination']['zone_id']);
		}

		return $ret;
	}

	public function maxShippingWeight() {
		return (float)$this->getConfig( 'SHIPPING_MAX_WEIGHT' );
	}

	protected function isInternationOrder( $pShipHash ) {
		return $pShipHash['origin']['countries_id'] != $pShipHash['destination']['countries_id'];	
	}

	protected function isEligibleShipper( $pShipHash ) {

		$freeShipping = FALSE;
/*
    $total_count = $total_count - $gBitCustomer->mCart->in_cart_check('product_is_free','1');
    $total_count = $total_count - $gBitCustomer->mCart->in_cart_check('product_is_always_free_ship','1');
		$total_count = $total_count - $gBitCustomer->mCart->free_shipping_items();

*/

/*
		if( $this->isEnabled() ) {
			if(defined("SHIPPING_ORIGIN_COUNTRY")) {
				if ((int)SHIPPING_ORIGIN_COUNTRY > 0) {
					$countries_array = zen_get_countries(SHIPPING_ORIGIN_COUNTRY);
					$this->country = $countries_array['countries_iso_code_2'];
				} else {
					$this->country = SHIPPING_ORIGIN_COUNTRY;
				}
			} else {
				$this->country = STORE_ORIGIN_COUNTRY;
			}
		}
*/
		$quoteBase = array();

		if( $this->isEnabled() && !empty( $pShipHash['shipping_weight_total'] ) ) {
			$pass = TRUE;
			// Check to see if shipping module is zone silo'ed
			if( ($shipperZone = $this->getShipperZone()) && !$freeShipping && $ret = !empty( $pShipHash['destination'] ) && !empty( $pShipHash['origin'] ) ) {
				if( is_null( $this->mShipZones ) ) {
					// cache mShipZones in memory
					$this->mShipZones = $this->mDb->getCol( "SELECT `zone_id` FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE `geo_zone_id` = ? ORDER BY `zone_id`", array( $shipperZone ), FALSE, BIT_QUERY_CACHE_TIME );
				}

				if( count( $this->mShipZones ) ) {
					$pass = FALSE;
					foreach( $this->mShipZones as $zoneId ) {
						if(  $pShipHash['destination']['countries_id'] && $zoneId == $pShipHash['destination']['zone_id']) {
							$pass = TRUE;
							break;
						}
					}
				} else {
					$pass = TRUE;
				}
			}

			// if ($error == true) $quotes['error'] = MODULE_'.$this->mModuleKey.'_ZONES_INVALID_ZONE;

			if( $pass ) {
				switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
					case (0):
						$show_box_weight = '';
						break;
					case (1):
						$show_box_weight = '(' . $pShipHash['shipping_num_boxes']. ' ' . TEXT_SHIPPING_BOXES . ')';
						break;
					case (2):
						$show_box_weight = '(' . number_format($pShipHash['shipping_weight_total'] * $pShipHash['shipping_num_boxes'],2) . tra( 'lbs' ) . ')';
						break;
					default:
						$show_box_weight = '(' . $pShipHash['shipping_num_boxes'] . ' x ' . number_format($pShipHash['shipping_weight_total'],2) . tra( 'lbs' ) . ')';
						break;
				}
				$quoteBase = array(
									'id' => $this->code,
									'module' => $this->title,
									'weight' => $show_box_weight,
									'icon' => $this->icon,
									'tax' => $this->getShippingTax( $pShipHash )
								);
			}
		}

		return $quoteBase;
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_ZONE' => array(
				'configuration_title' => 'Shipping Zone',
				'configuration_description' => 'If a zone is selected, only enable this shipping method for that zone.',
				'use_function' => 'zen_get_zone_class_title',
				'set_function' => 'zen_cfg_pull_down_zone_classes('
			),
			$this->getModuleKeyTrunk().'_TAX_CLASS' => array(
				'configuration_title' => 'Tax Class',
				'configuration_description' => 'Use the following tax class on the shipping fee.',
				'use_function' => 'zen_get_tax_class_title',
				'set_function' => 'zen_cfg_pull_down_tax_classes('
			),
			$this->getModuleKeyTrunk().'_TAX_BASIS' => array(
				'configuration_title' => 'Tax Basis',
				'configuration_value' => 'Shipping',
				'configuration_description' => 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone',
				'set_function' => "zen_cfg_select_option(array('Shipping', 'Billing', 'Store'), ",
			),
		) );
	}
}

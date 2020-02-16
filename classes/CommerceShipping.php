<?php
//
// +----------------------------------------------------------------------+
// zen-cart Open Source E-commerce
// +----------------------------------------------------------------------+
// Copyright (c) 2003 The zen-cart developers
//
// http://www.zen-cart.com/index.php
//
// Portions Copyright (c) 2003 osCommerce
// +----------------------------------------------------------------------+
// This source file is subject to version 2.0 of the GPL license,
// that is bundled with this package in the file LICENSE, and is
// available through the world-wide-web at the following url:
// http://www.zen-cart.com/license/2_0.txt.
// If you did not receive a copy of the PHP license and are unable to
// obtain it through the world-wide-web, please send a note to
// license@zen-cart.com so we can mail you a copy immediately.
// +----------------------------------------------------------------------+
// $Id$
//

class CommerceShipping extends BitSingleton {

	private $mShipModules = array();

	function __construct() {
		parent::__construct();
		$this->loadShippingModules();
	}

	public function __wakeup() {
		parent::__wakeup();
		$this->loadShippingModules();
	}

	private function loadShippingModules() {
		$this->mShipModules = CommerceSystem::scanModules( 'shipping', TRUE );
	}

	function getShippingModule( $pModuleCode ) {
		if( !empty( $this->mShipModules[$pModuleCode] ) ) {
			return $this->mShipModules[$pModuleCode];
		}
	}

	function isShippingAvailable() {
		return count( $this->mShipModules );
	}
	
	function quote( $pOrderBase, $method = '', $module = '' ) {
		global $currencies, $gCommerceSystem;

		$ret = array();

		if( !empty( $this->mShipModules ) ) {
			$shipHash['method'] = $method;
			$shipHash['shipping_weight_total'] = $pOrderBase->getWeight();
			$shipHash['is_fragile'] = FALSE; // needs implementation
			$shipHash['is_ground_only'] = FALSE; // needs implementation
			$shipHash['box_width'] = NULL;
			$shipHash['box_length'] = NULL;
			$shipHash['box_height'] = NULL;
			$shipHash['box_girth'] = NULL;
			$shipHash['weight_unit'] = $gCommerceSystem->getConfig( 'STORE_WEIGHT_UNIT' );

//				"('Shipping Delay', 'SHIPPING_DAYS_DELAY', '1', 'How many days from when an order is placed to when you ship it (Decimals are allowed). Arrival date estimations are based on this value.', 6, 7, NULL, NULL, now())",
//			'SHIPPING_DAYS_DELAY',
			if( ($shipHash['destination'] = $pOrderBase->getShippingDestination()) && ($shipHash['origin'] = $pOrderBase->getShippingOrigin()) ) {
				$shipHash['shipping_value'] = $pOrderBase->getShipmentValue();

				// Stuff from ancient ZenCart, probably still works
				$za_tare_array = preg_split("/[:,]/", SHIPPING_BOX_WEIGHT);
				$zc_tare_percent= $za_tare_array[0];
				$zc_tare_weight= $za_tare_array[1];

				$za_large_array = preg_split("/[:,]/" , SHIPPING_BOX_PADDING);
				$zc_large_percent = $za_large_array[0];
				$zc_large_weight = $za_large_array[1];
				foreach( $this->mShipModules as $shipModule ) {
					if( $shipModule->isEnabled() && empty( $module ) || ($shipModule->code == $module) ) {
						if ($shipHash['shipping_weight_total'] > $shipModule->maxShippingWeight() ) { // Split into many boxes
							$shipHash['shipping_num_boxes'] = ceil( $shipHash['shipping_weight_total'] / $shipModule->maxShippingWeight() );
							$shipHash['shipping_weight_box'] = $shipHash['shipping_weight_total'] / $shipHash['shipping_num_boxes'];
							// large box add padding
							$shipHash['shipping_weight_total'] = ($shipHash['shipping_weight_total'] * ($zc_large_percent/100)) + $zc_large_weight;
						} else {
							$shipHash['shipping_num_boxes'] = 1;
							$shipHash['shipping_weight_box'] = $shipHash['shipping_weight_total'];
							// add tare weight < large
							$shipHash['shipping_weight_total'] = ($shipHash['shipping_weight_total'] * ($zc_tare_percent/100)) + $zc_tare_weight;
						}
						if( $quotes = $shipModule->quote( $shipHash ) ) {
//eb( $method, $shipModule->code, $quotes, $shipHash );
							if( !empty( $quotes['methods'] ) ) {
								foreach( array_keys( $quotes['methods'] ) as $j ) {
									if( (empty( $method ) || $method == $quotes['methods'][$j]['id']) ) {
										if( !empty( $quotes['methods'][$j]['cost'] ) ) {	
											$quotes['methods'][$j]['cost_add_tax'] = zen_add_tax($quotes['methods'][$j]['cost'], (isset($quotes['tax']) ? $quotes['tax'] : 0));
											$quotes['methods'][$j]['format_add_tax'] = $currencies->format( $quotes['methods'][$j]['cost_add_tax'] );
										}
									} else {
										unset( $quotes['methods'][$j] );
									}
								}
								$quotes['origin'] = $shipHash['origin'];
							}
							$ret[] = $quotes;
						}
					}
				}
			}
		}

		return $ret;
	}

}

CommerceShipping::loadSingleton();


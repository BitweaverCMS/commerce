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

	function shipOrder( $pPodSalesId, $pShipmentHash ) {
		$ret = FALSE;

		list( $shipCarrier, $shipMethod ) = explode( '_', $pShipmentHash['shipment']['ship_method'], 2 );
		$orderHash = CommercePluginFulfillmentPrintBase::splitTransferOrderId( $pPodSalesId );
		$order = new order( $orderHash['orders_id'] );

		foreach( $this->mShipModules as &$shipModule ) {
			if( $shipModule->isEnabled() && ($shipModule->code == $shipCarrier) ) {
				$ret = $shipModule->createShipment( $order, $pShipmentHash );
			}
		}
		
		return $ret;
	}

	function quote( $pOrderBase, $method = '', $module = '' ) {
		global $currencies, $gCommerceSystem;

		$ret = array();

		if( !empty( $this->mShipModules ) ) {
			$shipHash = $pOrderBase->getBoxDimesions();
			$shipHash['method'] = $method;
			$orderTotalWeight = $pOrderBase->getWeight();
			$shipHash['is_fragile'] = FALSE; // needs implementation
			$shipHash['is_ground_only'] = FALSE; // needs implementation
			$shipHash['weight_unit'] = $gCommerceSystem->getConfig( 'STORE_WEIGHT_UNIT' );

//				"('Shipping Delay', 'SHIPPING_DAYS_DELAY', '1', 'How many days from when an order is placed to when you ship it (Decimals are allowed). Arrival date estimations are based on this value.', 6, 7, NULL, NULL, now())",
//			'SHIPPING_DAYS_DELAY',
			if( ($shipHash['destination'] = $pOrderBase->getShippingDestination()) && ($shipHash['origin'] = $pOrderBase->getShippingOrigin()) ) {
				$shipHash['shipping_value'] = $pOrderBase->getShipmentValue();
				$shipHash['shipping_value_currency'] = $pOrderBase->getOrderCurrency();

				// Stuff from ancient ZenCart, probably still works
				$boxWeights = preg_split("/[:,]/", SHIPPING_BOX_WEIGHT);
				$boxPercent= $boxWeights[0];
				$boxWeight= $boxWeights[1];

				$paddingWeights = preg_split("/[:,]/" , SHIPPING_BOX_PADDING);
				$paddingPercent = $paddingWeights[0];
				$paddingWeight = $paddingWeights[1];
				$shipHash['shipping_num_boxes'] = 1; // at least one parcel shipping
				foreach( $this->mShipModules as &$shipModule ) {
					if( $shipModule->isUserEnabled() && (empty( $module ) || ($shipModule->code == $module)) ) {
						$shipHash['shipping_weight_total'] = $orderTotalWeight;
						if ($shipHash['shipping_weight_total'] > $shipModule->maxShippingWeight() ) { // Split into many boxes
							$shipHash['shipping_num_boxes'] = ceil( $shipHash['shipping_weight_total'] / $shipModule->maxShippingWeight() );
							$shipHash['shipping_weight_total'] += ($shipHash['shipping_weight_total'] * ($paddingPercent/100)) + $paddingWeight;
							$shipHash['shipping_weight_box'] = $shipHash['shipping_weight_total'] / $shipHash['shipping_num_boxes'];
							
							// large box add padding
						} else {
							$shipHash['shipping_num_boxes'] = 1;
							// add tare weight < large
							$shipHash['shipping_weight_total'] += ($shipHash['shipping_weight_total'] * ($boxPercent/100)) + $boxWeight;
							$shipHash['shipping_weight_box'] = $shipHash['shipping_weight_total'];
						}

						if( $quotes = $shipModule->quote( $shipHash ) ) {
							if( !empty( $quotes['methods'] ) ) {
								foreach( array_keys( $quotes['methods'] ) as $j ) {
									if( (empty( $method ) || $method == $quotes['methods'][$j]['id']) ) {
										if( !empty( $quotes['methods'][$j]['cost'] ) ) {	
											$quotes['methods'][$j]['shipping_num_boxes'] = $shipHash['shipping_num_boxes'];
											$quotes['methods'][$j]['shipping_weight_box'] = $shipHash['shipping_weight_box'];
											$quotes['methods'][$j]['shipping_weight_total'] = $shipHash['shipping_weight_total'];
											$quotes['methods'][$j]['cost_add_tax'] = zen_add_tax($quotes['methods'][$j]['cost'], (isset($quotes['tax']) ? $quotes['tax'] : 0));
											$quotes['methods'][$j]['format_add_tax'] = $currencies->format( $quotes['methods'][$j]['cost_add_tax'] );
										}
									} else {
										unset( $quotes['methods'][$j] );
									}
								}
								$quotes['origin'] = $shipHash['origin'];
								$quotes['methods'] = array_values( $quotes['methods'] ); // reindex $quotes['methods'] in case any were unset above
							}
							$ret[] = $quotes;
						}
					}
				}
				// if package has been split, adjust height that was cumulatively added in ::getBoxDimensions()
				$shipHash['box_height'] = round( $shipHash['box_height'] / $shipHash['shipping_num_boxes'], 2 );
			}
		}

		return $ret;
	}

	function quoteToSession( $quote ) {
		unset( $_SESSION['shipping'] );
		$_SESSION['shipping'] = $this->quoteToHash( $quote );
		return !empty( $_SESSION['shipping'] );	
	}

	function quoteToHash( $quote ) {
		$ret = array();
		if( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) && isset( $quote[0]['id'] ) ) {
			$ret = array(
				'id' => $quote[0]['id'].'_'.$quote[0]['methods'][0]['id'],
				'title' => (($quote[0]['module'] == $quote[0]['methods'][0]['title']) ? $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
				'cost' => $quote[0]['methods'][0]['cost'],
				'code' => !empty( $quote[0]['methods'][0]['code'] ) ? $quote[0]['methods'][0]['code'] : NULL,
				'ship_date' => !empty( $quote[0]['methods'][0]['ship_date'] ) ? $quote[0]['origin']['ship_date'] : NULL
				);
		}
		return $ret;
	}

}

CommerceShipping::loadSingleton();


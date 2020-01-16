<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 * Copyright (c) 2020 bitweaver.org, All Rights Reserved
 * This source file is subject to the 2.0 GNU GENERAL PUBLIC LICENSE. 
 *
 * Base class for all fulfillment plugins.
 *
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginBase.php' );

abstract class CommercePluginFulfillmentBase extends CommercePluginBase {

	protected $mShipingOrigin = array();

	public function __construct() {
		parent::__construct();
	}

	protected function getModuleType() {
		return 'fulfillment';
	}

	public function isProduction() {
		return $this->getModuleConfigValue( '_MODE' ) == 'Production';
	}

	public function isTesting() {
		return $this->getModuleConfigValue( '_MODE' ) == 'Test';
	}

	protected function getFinalStatus() {
		return $this->getModuleConfigValue( '_FINAL_ORDER_STATUS_ID' );
	}

	protected function getOrignCountryCode() {
		return $this->getModuleConfigValue( '_ORIGIN_COUNTRY_CODE' );
	}

	protected function getDefaultPriority() {
		return $this->getModuleConfigValue( '_DEFAULT_PRIORITY', 0.0 );
	}

	protected function getFulfillmentDays( &$pOrder ) {
		return $this->getModuleConfigValue( '_FULFILLMENT_DAYS', 5 );
	}

	// Intended to be overridden
	// returns fraction 0..1 of the order that can be fulfilled through this plugin. Default 0.0 says none of the order can be fulfilled, 1.0 says entire order can be fulfilled
	protected function getOrderCompletion( &$pOrderBase ) {
		return 0.0;
	}

	// Intended to be overridden
	// Generic priority is the lowest of 0. Higher the more preferred, no upper limit
	function getPriority( $pOrderBase, $pCompletionHash ) {
		$ret = $this->getDefaultPriority();
		if( $this->isIntraCountry( $pOrderBase ) ) {
			$ret++;
		}
		$ret += count( $pCompletionHash );
		return $ret;
	}

	/**
	Determine if this fulfiller can deliver to the order delivery address
	*/
	protected function canDeliver( $pDeliveryHash ) {
		$allowedDestCodes = $this->getModuleConfigValue( '_DESTINATION_COUNTRY_CODES' );
		$ret = ($allowedDestCodes == 'ALL');
		if( !$ret ) {
			// if we can't go to ALL countries, see if our delivery code matches or origin
			$ret = (stripos( $allowedDestCodes, $pDeliveryHash['countries_iso_code_2'] ) !== FALSE);
		}
		return (bool)$ret;
	}

	public function getFulfillment( $pOrderBase ) {
		$ret = array();
		$delivery = $pOrderBase->getDelivery();

		if( $this->canDeliver( $delivery ) && $completion = $this->getOrderCompletion( $pOrderBase ) ) {
			$ret = $this->getShippingOrigin();
			$ret['code'] = $this->code;
			$ret['products'] = $completion;
			$ret['completion'] = count( $completion ) / count( $pOrderBase->contents );
			$ret['priority'] = $this->getPriority( $pOrderBase, $ret['completion'] );
			$ret['ship_date'] = $this->getEstimatedShipDate( $pOrderBase );
		}

		return $ret;
	}

	function getEstimatedShipDate( $pOrderBase ) {
		$shipString = '';
		$holidays = BitDate::getHolidays();

		if( $manfTime = $this->getFulfillmentDays( $pOrderBase ) ) {
			$delay = $manfTime;
			$dow = date( 'w' );
			$hour = date( 'H' );
			if( $hour > 22 && $dow > 0 && $dow <= 5 ) { $delay++; }

			$count = 1;
			while( $count <= $delay ) {
				$countTime = strtotime( '+'.$count.'days' );
				$dateStr = date( 'Y-m-d', $countTime );
				$countDow = date( 'w', $countTime );
				if( isset( $holidays[$dateStr] ) || $countDow == 0 || $countDow == 6 ) {
					$delay++;
				}
				$count++;
			}
			$shipTime = strtotime( '+'.$delay.'days' );
			$shipDate = date( 'Y-m-d', $shipTime );
			if( isset( $holidays[$shipDate] ) ) {
				$delay++;
				$shipTime = strtotime( '+'.($delay).'days' );
			}
			$shipString = date( 'Y-m-d', $shipTime );
			//$shipString = date( 'l', $shipTime ).', '.date( 'F j, Y', $shipTime );
		}
		return $shipString;
	}

	function isIntraCountry( $pOrderBase ) {
		global $gCommerceSystem;
		//default is same as the store
		$originCode = $this->getOrignCountryCode();
		$delivery = $pOrderBase->getDelivery();
		if( !($ret = ($originCode == $delivery['countries_iso_code_2']) ) ) {
			if( $delivery['countries_iso_code_3'] == 'PRI' || $delivery['countries_iso_code_3'] == 'VIR' || $delivery['countries_iso_code_3'] == 'UMI' ) {
				$storeCountry = zen_get_countries( $storeCountryId );
				if( $storeCountry['countries_iso_code_3'] == 'USA' ) {
					$ret = TRUE;
				}
			}
		}
		return $ret;
	}

	protected function getShippingOrigin() {
		global $gCommerceSystem;

		if( !($fulfillmentCountryId = $gCommerceSystem->getConfig( $this->getModuleKeyTrunk().'_ORIGIN_COUNTRY_CODE' ) ) ) {
			if( $ret = zen_get_countries( $storeCountryId ) ) {
				if( $ret['postcode'] = $gCommerceSystem->getConfig( '_ORIGIN_POSTAL_CODE' ) ) {
				}
			}
		} if( $storeCountryId = $gCommerceSystem->getConfig( 'SHIPPING_ORIGIN_COUNTRY' ) ) {
			if( $ret = zen_get_countries( $storeCountryId ) ) {
				if( $ret['postcode'] = $gCommerceSystem->getConfig( 'SHIPPING_ORIGIN_ZIP' ) ) {
				}
			}
		} elseif( $storeCountryId = $gCommerceSystem->getConfig( 'STORE_COUNTRY' ) ) {
			if( $ret = zen_get_countries( $storeCountryId ) ) {
				if( $ret['zone_id'] = $gCommerceSystem->getConfig( 'STORE_ZONE' ) ) {
					if( $zone = zen_get_zone_by_id( $storeCountryId, $ret['zone_id'] ) ) {
					}
				}
			}
		}
		return $ret;
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 1;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_MODE' => array(
				'configuration_title' => $this->title.' Activation Mode',
				'configuration_value' => 'Test',
				'configuration_description' => 'Select mode for ".$this->title." fulfillment.',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('Production', 'Test'), ",
			),
			$this->getModuleKeyTrunk().'_DEFAULT_PRIORITY' => array(
				'configuration_title' => 'Default Priority',
				'configuration_description' => 'Priority bump to encourage this fulfiller over others. Default is 0.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_ORIGIN_COUNTRY_CODE' => array(
				'configuration_title' => 'Origin Country Code',
				'configuration_description' => 'The ISO-2 country code where this fulfiller is located.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_ORIGIN_POSTAL_CODE' => array(
				'configuration_title' => 'Origin Postal Code',
				'configuration_description' => 'The postal (zip) code where this fulfiller is located.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_DESTINATION_COUNTRY_CODES' => array(
				'configuration_title' => 'Destination Country Codes',
				'configuration_description' => 'A semi-colon separated list of ISO-2 country codes where this fulfiller can send orders. e.g. "US;CA;MX". A value of "ALL" indicates unlimited global fulfillment.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FULFILLMENT_DAYS' => array(
				'configuration_title' => 'Fulfillment Days',
				'configuration_value' => '5',
				'configuration_description' => 'Days before shipping to produce the order.',
				'sort_order' => $i++,
			),
		) );
	}
}

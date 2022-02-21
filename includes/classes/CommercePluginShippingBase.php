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

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginBase.php' );

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

	protected function getShipperHandling() {
		return $this->getConfig( $this->getModuleKeyTrunk().'_HANDLING' );
	}

	protected function getShippingTax() {
		$ret = 0;
		
		if( !empty( $this->tax_class ) ) {
			$ret = zen_get_tax_rate($this->tax_class, $pShipHash['destination']['countries_id'], $pShipHash['destination']['zone_id']);
		}

		return $ret;
	}

	protected function getShippingCutoffTime( $pOrderBase ) {
		return (int)$this->getModuleConfigValue( '_SHIPPING_CUTOFF_TIME', 1600 );
	}

	// used for shipDate
	protected function getShippingDate( $pShipHash ) {
		$delay = 0;

		if( !($shipDate = BitBase::getParameter( $pShipHash['origin'], 'ship_date' ) ) ) {
			// no ship_date in the order
			$delay += (int)$this->getConfig( 'SHIPPING_FULFILLMENT_DAYS', '5' );

			$dow = date( 'w' );
			if( $dow > 5 ) {
				$delay++; // assume no shipping on Sat.
			}
			if( $dow > 6 ) {
				$delay++; // assume no shipping on Sun.
			}
		}
		$newDate = new DateTime( $shipDate );

		// safety calculation for cutoff time
		$cutoffTime = $this->getShippingCutoffTime( $pShipHash );
		$hourMin = (int)date( 'Hm' );
		$dayOfWeek = (int)date( 'w' );
		if( $hourMin > $cutoffTime ) { 
			$delay++;
		}

		if( $delay ) {
			$weekEndCount = (int)(($delay + $dayOfWeek) / 7);
			$shipDay = $delay + ($weekEndCount * 2);
			$shipDayOfWeek = (int)date( 'w', (strtotime( $shipDate ) + (86400 * $shipDay)) );
			if( $shipDayOfWeek === 0 )  {
				$shipDay += 1;
			} elseif( $shipDayOfWeek === 6 )  {
				$shipDay += 2;
			}
			$holidays = BitDate::getHolidays();
			$count = 1;
			while( $count <= $shipDay ) {
				$countTime = strtotime( $shipDate ) + strtotime( '+'.$count.'days' );
				$dateStr = date( 'Y-m-d', $countTime );
				$countDow = date( 'w', $countTime );
				if( isset( $holidays[$dateStr] ) ) { // && ($countDow == 0 || $countDow == 6) ) {
					$shipDay++;
				}
				$count++;
			}
			$newDate->add( new DateInterval( 'P'.$shipDay.'D') );
			$shipDate = $newDate->format( 'Y-m-d' );
		}

		return $shipDate;
	}

	public function maxShippingWeight() {
		return (float)$this->getCommerceConfig( 'SHIPPING_MAX_WEIGHT' );
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
		$explodeArray = array();
		$shipperOrigin = $this->getModuleConfigValue( '_ORIGIN_COUNTRY_CODE' );
		if( $excludeList = $this->getModuleConfigValue( '_EXCLUDE_COUNTRIES_ISO2' ) ) {
			$explodeArray =  array_map('trim', explode( ',', $excludeList ));
		}

		if( in_array( $pShipHash['destination']['countries_iso_code_2'], $explodeArray ) ) {
			// country is in module EXCLUDE list
		} elseif( $shipperOrigin && $pShipHash['origin']['countries_iso_code_2'] != $shipperOrigin ) {

		} elseif( $this->isEnabled() && !empty( $pShipHash['shipping_weight_total'] ) ) {
			$pass = TRUE;
			// Check to see if shipping module is zone silo'ed
			if( ($shipperZone = $this->getShipperZone()) && !$freeShipping && !empty( $pShipHash['destination'] ) && !empty( $pShipHash['origin'] ) ) {
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
						$show_box_weight = '(' . $pShipHash['shipping_num_boxes'] . ' x ' . number_format($pShipHash['shipping_weight_box'],2) . tra( 'lbs' ) . ')';
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

	protected function sortQuoteMethods( &$pMethods ) {
		
		if( count( $pMethods ) > 1 && ($quoteSort = $this->getModuleConfigValue( '_QUOTE_SORT', 'Price-LowToHigh' )) && ($quoteSort != 'Unsorted') ) {
			// sort results


			$checkKeys = array_keys( $pMethods );

			// remove methods that have the same price
			do {
				$checkKey = array_shift( $checkKeys );
				$methodKeys = array_keys( $pMethods );
				foreach( $methodKeys as $key ) {
					if( $key != $checkKey && !empty( $pMethods[$checkKey] ) ) {
						if( $pMethods[$key]['cost'] == $pMethods[$checkKey]['cost'] ) {
							if( $pMethods[$key]['transit_time'] > $pMethods[$checkKey]['transit_time'] ) {
								unset( $pMethods[$key] );
							} else {
								unset( $pMethods[$checkKey] );
							}
						}
					}
				}
				
			} while( !empty( $checkKeys ) );

			list($sortType, $sortDir) = explode( '-', $quoteSort );
			switch( $sortType ) {
				case 'Price':
					$sortKey = 'cost';
					break;
				case 'Transit':
					$sortKey = 'transit_time';
					break;
				case 'Alphabetical':
				default:
					$sortKey = 'title';
					break;
			}
			switch( $sortDir ) {
				case 'SlowToFast':
				case 'LowToHigh':
					$sortMode = SORT_ASC;
					break;
				default:
					$sortMode = SORT_ASC;
					break;
			}

			foreach($pMethods as $c=>$key) {
				$sortValues[] = $key[$sortKey];
				$sortIds[] = $key['id'];
			}
			array_multisort($sortValues, $sortMode, $sortIds, SORT_ASC, $pMethods);
		}
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 10;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_HANDLING' => array(
				'configuration_title' => 'Handling Fee',
				'configuration_description' => 'The handling cost for all orders using this shipping method.',
				'sort_order' => $i++,
				'configuration_value' => '0',
			),
			$this->getModuleKeyTrunk().'_ORIGIN_COUNTRY_CODE' => array(
				'configuration_title' => 'Shipper Origin Country Code',
				'configuration_description' => 'The ISO-2 Country Code for shipper if is it limited to a single country, like USPS, CanadaPost, etc.',
				'sort_order' => $i++,
				'configuration_value' => '',
			),
			$this->getModuleKeyTrunk().'_ZONE' => array(
				'configuration_title' => 'Shipping Zone',
				'configuration_description' => 'If a zone is selected, only enable this shipping method for that zone.',
				'use_function' => 'zen_get_zone_class_title',
				'set_function' => 'zen_cfg_pull_down_zone_classes('
			),
			$this->getModuleKeyTrunk().'_TAX_CLASS' => array(
				'configuration_title' => 'Tax Class',
				'configuration_description' => 'Use the following tax class on the shipping fee.',
				'sort_order' => $i++,
				'use_function' => 'zen_get_tax_class_title',
				'set_function' => 'zen_cfg_pull_down_tax_classes('
			),
			$this->getModuleKeyTrunk().'_TAX_BASIS' => array(
				'configuration_title' => 'Tax Basis',
				'configuration_value' => 'Shipping',
				'configuration_description' => 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('Shipping', 'Billing', 'Store'), ",
			),
			$this->getModuleKeyTrunk().'_SHIPPING_CUTOFF_TIME' => array(
				'configuration_title' => 'Shipping Cut-off Time',
				'configuration_description' => 'Time of day when shipments must be sent to make current day shipping. Enter a 4 digit number 24HR number like "1430" = 14:30 = 2:30pm ---- must be HHMM without punctuation. Default is 1600, ie 4pm local store time.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_EXCLUDE_COUNTRIES_ISO2' => array(
				'configuration_title' => 'Excluded Countries',
				'configuration_description' => 'Comma separated list of ISO-2 country codes not to quote for this method, even if the shipper has viable options. For example: CA,MX,NZ,AU',
				'sort_order' => $i++,
				'configuration_value' => '',
			),
			$this->getModuleKeyTrunk().'_QUOTE_SORT' => array(
				'configuration_title' => 'Quote Sort Order',
				'configuration_value' => 'Price-LowToHigh',
				'configuration_description' => 'Sorts the returned quotes using the service name Alphanumerically or by Price. Unsorted will give the order provided by the shipper.',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('Unsorted','Alphabetical', 'Price-LowToHigh', 'Price-HighToLow', 'Transit-FastToSlow', 'Transit-SlowToFast'),",
			),
		) );
	}
}

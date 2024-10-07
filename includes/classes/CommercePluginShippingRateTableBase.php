<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2019 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class CommercePluginShippingRateTableBase extends CommercePluginShippingBase {

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			if( $this->getModuleConfigValue( '_MODE' ) == 'price' ) {
				$shipMetric = BitBase::getParameter( $pShipHash, 'shipping_value', 0);
			} else {
				$shipMetric = BitBase::getParameter( $pShipHash, 'shipping_weight_box', 0);
			}

			$shipping = 0;

			$kRateCode = 0;
			$kRateTitle = 1;
			$kRateUnit = 2;
			$kRateCost = 3;
			$kRateTransit = 4;


			$methods = array();

			if( $fixedRates = preg_split ('/$\R?^/m', $this->getModuleConfigValue( '_RATES' ) ) ) {
				foreach( $fixedRates as $rateString ) {
					if( !empty( trim( $rateString ) ) ) {
						$rateHash = str_getcsv( trim( $rateString ) );
						for( $k = 0; $k < $pShipHash['shipping_num_boxes']; $k++ ) {
							if( $shipMetric <= $rateHash[$kRateUnit] ) {
								$deliveryDate = '';
								if( $transitDays = BitBase::getParameter( $rateHash, $kRateTransit, '' ) ) {
									$shipDate = new DateTime( $this->getShippingDate( $pShipHash ) );
									$shipDate->add( new DateInterval( 'P'.$transitDays.'D') );
									$deliveryDate = $shipDate->format( 'Y-m-d' );
								}
								array_push( $methods, array(	'id' => $rateHash[$kRateCode],
													'title' => $rateHash[$kRateTitle],
													'cost' => $rateHash[$kRateCost],
													'code' => $rateHash[$kRateCode],
													'transit_time' => $transitDays.' Days',
													'transit_days' => $transitDays,
													'delivery_date' => $deliveryDate,
												  ) );
								break;
							}
						}
					}
				}

				$this->sortQuoteMethods( $methods );
				$quotes['methods'] = $methods;
			}
		}

		return $quotes;
	}

	// {{{	++++++++ config ++++++++
	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$parentConfig = parent::config();
		$i = count( $parentConfig );
		return array_merge( $parentConfig, array( 
			$this->getModuleKeyTrunk().'_RATES' => array(
				'configuration_title' => 'Fixed Shipping Rates',
				'configuration_value' => "
cheapest,\"Slow and Cheap\",20,10.00,7
priority,\"Kinda in a Rush\",10,20.00,3
fastest,\"Super Fast\",5,30.00,1",
				'configuration_description' => "CSV shipping values where each line has format: <p><code>code,\"Title\",Max Quantity,Price,Ship Days(optional)</code></p> The shipping cost is based on the total cost or weight of items. Example: <p><code>\"Flat Rate\",25,8.50</br>\"Flat Rate 50\",50,8.50</br>etc..</code></p> Up to 25 charge 8.50, from there to 50 charge 5.50, etc",
				'sort_order' => $i++,
				'set_function' => 'zen_cfg_textarea(',
			),
			$this->getModuleKeyTrunk().'_MODE' => array(
				'configuration_title' => 'Fixed Quantity Mode',
				'configuration_value' => 'weight',
				'configuration_description' => 'Determines if quantity in the fixed rates is based on the order total or the total weight of the items ordered.',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('weight', 'price'), ",
			),
		) );
	}
	// }}}
}

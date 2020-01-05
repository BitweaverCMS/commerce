<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class fedexwebservices extends CommercePluginShippingBase {
	function __construct() {
		parent::__construct();
		$this->title			= tra( 'FedEx' );
		$this->description		= 'You will need to have registered an account with FedEx and proper approval from FedEx identity to use this module. Please see the README.TXT file for other requirements.';
		$this->icon				= 'shipping_fedex';
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
		
			require_once( dirname( __FILE__ ) . '/fedex-common.php5' );
			ini_set( "soap.wsdl_cache_enabled", "0" );

			$pShipHash['shipping_num_boxes'] = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

			$client = new SoapClient( dirname( __FILE__ ) . "/RateService_v10.wsdl", array('trace' => 1) );
			$this->types = array();
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_PRIORITY == 'true') {
				$this->types['INTERNATIONAL_PRIORITY'] = array( 'code' => 'FEDEX_INTERNATIONAL_PRIORITY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_INT_EXPRESS_HANDLING_FEE);
				$this->types['EUROPE_FIRST_INTERNATIONAL_PRIORITY'] = array( 'code' => 'FEDEX_EUROPE_FIRST_INTERNATIONAL_PRIORITY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_INT_EXPRESS_HANDLING_FEE);
			}
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_ECONOMY == 'true') {
				$this->types['INTERNATIONAL_ECONOMY'] = array( 'code' => 'FEDEX_INTERNATIONAL_ECONOMY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_INT_EXPRESS_HANDLING_FEE);
			}	
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_STANDARD_OVERNIGHT == 'true') {
				$this->types['STANDARD_OVERNIGHT'] = array( 'code' => 'FEDEX_STANDARD_OVERNIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
			}
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_FIRST_OVERNIGHT == 'true') {
				$this->types['FIRST_OVERNIGHT'] = array( 'code' => 'FEDEX_FIRST_OVERNIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
			}
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_PRIORITY_OVERNIGHT == 'true') {
				$this->types['PRIORITY_OVERNIGHT'] = array( 'code' => 'FEDEX_PRIORITY_OVERNIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
			}
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_2DAY == 'true') {
				$this->types['FEDEX_2_DAY'] = array( 'code' => 'FEDEX_2_DAY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
			}
			// because FEDEX_GROUND also is returned for Canadian Addresses, we need to check if the country matches the store country and whether international ground is enabled
			if( ($this->isEnabled( 'MODULE_SHIPPING_FEDEXWEBSERVICES_GROUND' ) && $pShipHash['destination']['countries_iso_code_2'] == 'US') || ($this->isEnabled( 'MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_GROUND' )  && $pShipHash['destination']['countries_iso_code_2'] == 'CA') ) {
				$isIntlOrder = $this->isInternationOrder( $pShipHash );	
				$this->types['FEDEX_GROUND'] = array( 'code' => 'FEDEX_GROUND', 'icon' => '', 'handling_fee' => ($isIntlOrder ? MODULE_SHIPPING_FEDEXWEBSERVICES_HANDLING_FEE : MODULE_SHIPPING_FEDEXWEBSERVICES_INT_HANDLING_FEE));
				$this->types['GROUND_HOME_DELIVERY'] = array( 'code' => 'FEDEX_GROUND_HOME_DELIVERY', 'icon' => '', 'handling_fee' => ($isIntlOrder ? MODULE_SHIPPING_FEDEXWEBSERVICES_HANDLING_FEE : MODULE_SHIPPING_FEDEXWEBSERVICES_INT_HANDLING_FEE));
			}
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_GROUND == 'true') {
				$this->types['INTERNATIONAL_GROUND'] = array( 'code' => 'FEDEX_INTERNATIONAL_GROUND', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_INT_HANDLING_FEE);
			}
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_SAVER == 'true') {
				$this->types['FEDEX_EXPRESS_SAVER'] = array( 'code' => 'FEDEX_EXPRESS_SAVER', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
			}
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_FREIGHT == 'true') {
				$this->types['FEDEX_FREIGHT'] = array( 'code' => 'FEDEX_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
				$this->types['FEDEX_NATIONAL_FREIGHT'] = array( 'code' => 'FEDEX_NATIONAL_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
				$this->types['FEDEX_1_DAY_FREIGHT'] = array( 'code' => 'FEDEX_1_DAY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
				$this->types['FEDEX_2_DAY_FREIGHT'] = array( 'code' => 'FEDEX_2_DAY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
				$this->types['FEDEX_3_DAY_FREIGHT'] = array( 'code' => 'FEDEX_3_DAY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE);
				$this->types['INTERNATIONAL_ECONOMY_FREIGHT'] = array( 'code' => 'FEDEX_INTERNATIONAL_ECONOMY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_INT_EXPRESS_HANDLING_FEE);
				$this->types['INTERNATIONAL_PRIORITY_FREIGHT'] = array( 'code' => 'FEDEX_INTERNATIONAL_PRIORITY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEXWEBSERVICES_INT_EXPRESS_HANDLING_FEE);
			}											
													 
			// customer details			
			$city = (!empty( $pShipHash['destination']['city'] ) ? $pShipHash['destination']['city'] : '');

			if ($pShipHash['shipping_value'] > $this->getConfig( 'MODULE_SHIPPING_FEDEXWEBSERVICES_INSURE' ) ) {
				$this->insurance = sprintf("%01.2f", (float)$pShipHash['shipping_value']);
			} else {
				$this->insurance = 0;
			}
			
			$request['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => MODULE_SHIPPING_FEDEXWEBSERVICES_KEY, 'Password' => MODULE_SHIPPING_FEDEXWEBSERVICES_PWD));
			$request['ClientDetail'] = array('AccountNumber' => MODULE_SHIPPING_FEDEXWEBSERVICES_ACT_NUM, 'MeterNumber' => MODULE_SHIPPING_FEDEXWEBSERVICES_METER_NUM );
			$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v10 using PHP ***');
			$request['Version'] = array('ServiceId' => 'crs', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0');
			$request['ReturnTransitAndCommit'] = true;
			$request['RequestedShipment']['DropoffType'] = $this->_setDropOff(); // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
			$request['RequestedShipment']['ShipTimestamp'] = date('c');
			//if (zen_not_null($method) && in_array($method, $this->types)) {
				//$request['RequestedShipment']['ServiceType'] = $method; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
			//}
			$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			$request['RequestedShipment']['TotalInsuredValue']=array( 'Amount'=> $this->insurance, 'Currency' => DEFAULT_CURRENCY );
			$request['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => MODULE_SHIPPING_FEDEXWEBSERVICES_KEY, 'Password' => MODULE_SHIPPING_FEDEXWEBSERVICES_PWD));										 
			$request['ClientDetail'] = array('AccountNumber' => MODULE_SHIPPING_FEDEXWEBSERVICES_ACT_NUM, 'MeterNumber' => MODULE_SHIPPING_FEDEXWEBSERVICES_METER_NUM );

			$addressKeys = array( 'City'=>'city', 'StateOrProvinceCode'=>'state', 'PostalCode'=>'postcode', 'CountryCode'=>'countries_iso_code_2' );

			foreach( array( 'Shipper'=>'origin', 'Recipient'=>'destination' ) as $xmlKey=>$dataKey ) {
				$request['RequestedShipment'][$xmlKey] = array( 'Address' => array(
																	'StreetLines' => array(
																		utf8_encode( BitBase::getParameter( $pShipHash[$dataKey], 'street_address') ),
																		utf8_encode( BitBase::getParameter( $pShipHash[$dataKey], 'suburb') )
																	),
																	'PostalCode' => str_replace(array(' ', '-'), '', $pShipHash[$dataKey]['postcode']),
																));
				foreach( $addressKeys as $fedexKey=>$hashKey ) {
					$request['RequestedShipment'][$xmlKey]['Address'][$fedexKey] = utf8_encode( BitBase::getParameter( $pShipHash[$dataKey], $hashKey ) );
				}
			}

			if( $stateLookup = BitBase::getParameter( $pShipHash['destination'], 'zone_id', BitBase::getParameter( $pShipHash['destination'], 'state' ) ) ) {
				if( $stateCode = zen_get_zone_code($pShipHash['destination']['countries_id'], $stateLookup, '' ) ) {
					if ($stateCode == "QC") {
						$stateCode = "PQ"; // is this needed? been here forever
					}
					if( in_array( $pShipHash['destination']['countries_iso_code_2'], array('US', 'CA') ) ) {
						$request['RequestedShipment']['Recipient']['Address']['StateOrProvinceCode'] = $stateCode;
					}
				}
			}
			
			$request['RequestedShipment']['ShippingChargesPayment'] = array(	'PaymentType' => 'SENDER',
																				'Payor' => array('AccountNumber' => MODULE_SHIPPING_FEDEXWEBSERVICES_ACT_NUM,
																				'CountryCode' => $pShipHash['origin']['countries_iso_code_2'] ) );
			$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
			$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
			$request['RequestedShipment']['RequestedPackageLineItems'] = array();
			
			$dimensions_failed = false;
			
			// check for ready to ship field
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_READY_TO_SHIP == 'true') {			
				if( $packages = BitBase::getParameter( $pShipHash, 'packages' ) ) {
					// Not fixed for bitcommerce
eb( $products );
					$pShipHash['shipping_num_boxes'] = 0;
					$pShipHash['shipping_weight_total'] = 0;
					foreach ($packages as $package) {
						if ($package['weight'] <= 0) {
							$package['weight'] = 0.1;
						}
						$pShipHash['shipping_num_boxes']++;
						$pShipHash['shipping_weight_total'] += $package['weight'];
						$request['RequestedShipment']['RequestedPackageLineItems'][] = array('Weight' => array('Value' => round( $package['weight'], 2), 'Units' => MODULE_SHIPPING_FEDEXWEBSERVICES_WEIGHT),
																							 'Dimensions' => array(	'Length' => $package['length'],
																													'Width' => $package['width'],
																													'Height' => $package['height'],
																													'Units' => $package['units'] 
																													),
																							  'GroupPackageCount' => 1,
																							 );
					}
					$pShipHash['shipping_weight_box'] = $pShipHash['shipping_weight_total'] / $pShipHash['shipping_num_boxes'];
				} else {
					for ($i=0; $i<$pShipHash['shipping_num_boxes']; $i++) {
						$request['RequestedShipment']['RequestedPackageLineItems'][] = array('Weight' => array('Value' => round( $pShipHash['shipping_weight_box'], 2), 'Units' => MODULE_SHIPPING_FEDEXWEBSERVICES_WEIGHT), 'GroupPackageCount' => 1 );
					}
				}
			} else {
				for ($i=0; $i<$pShipHash['shipping_num_boxes']; $i++) {
					$request['RequestedShipment']['RequestedPackageLineItems'][] = array('Weight' => array('Value' => round( $pShipHash['shipping_weight_box'], 2 ), 'Units' => MODULE_SHIPPING_FEDEXWEBSERVICES_WEIGHT), 'GroupPackageCount' => 1 );
				}
			}
			$request['RequestedShipment']['PackageCount'] = $pShipHash['shipping_num_boxes'];
			
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_SATURDAY == 'true') {
				$request['RequestedShipment']['ServiceOptionType'] = 'SATURDAY_DELIVERY';
			}
			
			if (MODULE_SHIPPING_FEDEXWEBSERVICES_SIGNATURE_OPTION >= 0 && $pShipHash['shipping_value'] >= MODULE_SHIPPING_FEDEXWEBSERVICES_SIGNATURE_OPTION) { 
				$request['RequestedShipment']['SpecialServicesRequested'] = 'SIGNATURE_OPTION'; 
			}

			try {
				$response = $client->getRates($request);

				if( !empty( $response ) && ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR' && !empty( $response->RateReplyDetails )) ) {
					if (is_object($response->RateReplyDetails)) {
						$response->RateReplyDetails = get_object_vars($response->RateReplyDetails);
					}
					$methods = array();
					foreach ($response->RateReplyDetails as $rateReply) {
						if( array_key_exists( $rateReply->ServiceType, $this->types ) && ( empty( $pShipHash['method'] ) || (str_replace('_', '', $rateReply->ServiceType) == $pShipHash['method']) ) ) {
							$cost = NULL;
							if(MODULE_SHIPPING_FEDEXWEBSERVICES_RATES=='LIST') {
								foreach($rateReply->RatedShipmentDetails as $ShipmentRateDetail) {
									if($ShipmentRateDetail->ShipmentRateDetail->RateType=='PAYOR_LIST_PACKAGE') {
										$cost = $ShipmentRateDetail->ShipmentRateDetail->TotalNetCharge->Amount;
										$cost = (float)round(preg_replace('/[^0-9.]/', '',	$cost), 2);
									}
								}
							} else {
								$cost = $rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
								$cost = (float)round(preg_replace('/[^0-9.]/', '',	$cost), 2);
							}

							$deliveryDays = '';
							switch( $rateReply->ServiceType ) {
								case 'FIRST_OVERNIGHT':
								case 'STANDARD_OVERNIGHT':
								case 'PRIORITY_OVERNIGHT':
									$deliveryDays = '1 Business Day'; break;
								case 'FEDEX_2_DAY':
									$deliveryDays = '2 Business Days'; break;
								case 'FEDEX_EXPRESS_SAVER':
									$deliveryDays = '3 Business Days'; break;
								case 'FEDEX_GROUND':
								case 'GROUND_HOME_DELIVERY':
									$deliveryDays = '4-7 Business Days'; break;
								case 'INTERNATIONAL_PRIORITY':
									$deliveryDays = '1-3 Business Days'; break;
								case 'INTERNATIONAL_ECONOMY':
									$deliveryDays = '2-7 Business Days'; break;
							}
							$methods[] = array(	'id' => str_replace('_', '', $rateReply->ServiceType),
												'title' => ucwords(strtolower(str_replace('_', ' ', $rateReply->ServiceType))),
												'cost' => $cost + (strpos($this->types[$rateReply->ServiceType]['handling_fee'], '%') ? ($cost * (float)$this->types[$rateReply->ServiceType]['handling_fee'] / 100) : (float)$this->types[$rateReply->ServiceType]['handling_fee']),
												'code' => $this->types[$rateReply->ServiceType]['code'],
												'transit_time' => $deliveryDays,
											  );
						}
					}
					$quotes['methods'] = $methods;
					if ($this->tax_class > 0) {
						$quotes['tax'] = zen_get_tax_rate($this->tax_class, $pShipHash['destination']['countries_id'], $pShipHash['destination']['zone_id']);
					} 
				} else {
					$message = ''; 
					if( is_array( $response->Notifications ) ) {
						foreach ($response->Notifications as $notification) {					 
							$message .= tra( $notification->Severity ).': '.tra( $notification->Message );
						}
					} elseif( is_object( $response->Notifications ) ) {
						$message .= tra( $response->Notifications->Severity ).': '.tra( $response->Notifications->Message );
					}
				}
			} catch( Exception $e ) {
				$message = $e->getMessage();
			}

			if( !empty( $message ) ) {
				$quotes = array('module' => $this->title, 'error'	=> $message);
			}

			if ( !empty( $this->icon ) ) {
				$quotes['icon'] = $this->icon;
			}
		}

		return $quotes;
	}

	// method added for expanded info in FEAC
	function info() {
		return $this->title;
	}
		
	function objectToArray($object) {
		if( !is_object( $object ) && !is_array( $object ) ) {
			return $object;
		}
		if( is_object( $object ) ) {
			$object = get_object_vars( $object );
		}
		return array_map( 'objectToArray', $object );
	}
	
	function _setDropOff() {
		switch(MODULE_SHIPPING_FEDEXWEBSERVICES_DROPOFF) {
			case '1':
				return 'REGULAR_PICKUP';
				break;
			case '2':
				return 'REQUEST_COURIER';
				break;
			case '3':
				return 'DROP_BOX';
				break;
			case '4':
				return 'BUSINESS_SERVICE_CENTER';
				break;
			case '5':
				return 'STATION';
				break;
		}
	}

	function install() {
		if( !$this->isInstalled() ) {
			$this->mDb->StartTrans();
			parent::install();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Version Installed', 'MODULE_SHIPPING_FEDEXWEBSERVICES_VERSION', '1.3.0', '', '6', '0', now())"); 
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Account Number', 'MODULE_SHIPPING_FEDEXWEBSERVICES_ACT_NUM', '', 'Enter FedEx Account Number', '6', '3', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Meter Number', 'MODULE_SHIPPING_FEDEXWEBSERVICES_METER_NUM', '', 'Enter FedEx Meter Number (You can get one at <a href=\"http://www.fedex.com/us/developer/\" target=\"_blank\">http://www.fedex.com/us/developer/</a>)', '6', '4', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Authentication Key', 'MODULE_SHIPPING_FEDEXWEBSERVICES_KEY', '', 'Enter FedEx Authentication Key (You can get one at <a href=\"http://www.fedex.com/us/developer/\" target=\"_blank\">http://www.fedex.com/us/developer/</a>)', '6', '4', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Password', 'MODULE_SHIPPING_FEDEXWEBSERVICES_PWD', '', 'Enter FedEx Password', '6', '4', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Weight Units', 'MODULE_SHIPPING_FEDEXWEBSERVICES_WEIGHT', 'LB', 'Weight Units:', '6', '10', 'zen_cfg_select_option(array('LB', 'KG'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('First line of street address', 'MODULE_SHIPPING_FEDEXWEBSERVICES_ADDRESS_1', '', 'Enter the first line of your ship-from street address, required', '6', '20', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Second line of street address', 'MODULE_SHIPPING_FEDEXWEBSERVICES_ADDRESS_2', '', 'Enter the second line of your ship-from street address, leave blank if you do not need to specify a second line', '6', '21', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('City name', 'MODULE_SHIPPING_FEDEXWEBSERVICES_CITY', '', 'Enter the city name for the ship-from street address, required', '6', '22', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('State or Province name', 'MODULE_SHIPPING_FEDEXWEBSERVICES_STATE', '', 'Enter the 2 letter state or province name for the ship-from street address, required for Canada and US', '6', '23', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Postal code', 'MODULE_SHIPPING_FEDEXWEBSERVICES_POSTAL', '', 'Enter the postal code for the ship-from street address, required', '6', '24', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Phone number', 'MODULE_SHIPPING_FEDEXWEBSERVICES_PHONE', '', 'Enter a contact phone number for your company, required', '6', '25', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable for Always Free Shipping', 'MODULE_SHIPPING_FEDEXWEBSERVICES_FREE_SHIPPING', 'false', 'Should this module be enabled even when all items in the cart are marked as ALWAYS FREE SHIPPING?', '6', '30', 'zen_cfg_select_option(array('true','false'),', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Drop off type', 'MODULE_SHIPPING_FEDEXWEBSERVICES_DROPOFF', '1', 'Dropoff type (1 = Regular pickup, 2 = request courier, 3 = drop box, 4 = drop at BSC, 5 = drop at station)?', '6', '30', 'zen_cfg_select_option(array('1','2','3','4','5'),', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Express Saver', 'MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_SAVER', 'true', 'Enable FedEx Express Saver', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Standard Overnight', 'MODULE_SHIPPING_FEDEXWEBSERVICES_STANDARD_OVERNIGHT', 'true', 'Enable FedEx Express Standard Overnight', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable First Overnight', 'MODULE_SHIPPING_FEDEXWEBSERVICES_FIRST_OVERNIGHT', 'true', 'Enable FedEx Express First Overnight', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Priority Overnight', 'MODULE_SHIPPING_FEDEXWEBSERVICES_PRIORITY_OVERNIGHT', 'true', 'Enable FedEx Express Priority Overnight', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable 2 Day', 'MODULE_SHIPPING_FEDEXWEBSERVICES_2DAY', 'true', 'Enable FedEx Express 2 Day', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Priority', 'MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_PRIORITY', 'true', 'Enable FedEx Express International Priority', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Economy', 'MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_ECONOMY', 'true', 'Enable FedEx Express International Economy', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Ground', 'MODULE_SHIPPING_FEDEXWEBSERVICES_GROUND', 'true', 'Enable FedEx Ground', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Ground', 'MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_GROUND', 'true', 'Enable FedEx International Ground', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Freight', 'MODULE_SHIPPING_FEDEXWEBSERVICES_FREIGHT', 'true', 'Enable FedEx Freight', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Saturday Delivery', 'MODULE_SHIPPING_FEDEXWEBSERVICES_SATURDAY', 'false', 'Enable Saturday Delivery', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Domestic Ground Handling Fee', 'MODULE_SHIPPING_FEDEXWEBSERVICES_INSURE', '250', 'Minimal amount to add package insurance', '6', '25', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Domestic Ground Handling Fee', 'MODULE_SHIPPING_FEDEXWEBSERVICES_HANDLING_FEE', '', 'Add a domestic handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Domestic Express Handling Fee', 'MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE', '', 'Add a domestic handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('International Ground Handling Fee', 'MODULE_SHIPPING_FEDEXWEBSERVICES_INT_HANDLING_FEE', '', 'Add an international handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('International Express Handling Fee', 'MODULE_SHIPPING_FEDEXWEBSERVICES_INT_EXPRESS_HANDLING_FEE', '', 'Add an international handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('FedEx Rates','MODULE_SHIPPING_FEDEXWEBSERVICES_RATES','LIST','FedEx Rates','6','0','zen_cfg_select_option(array('LIST','ACCOUNT'),',now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Signature Option', 'MODULE_SHIPPING_FEDEXWEBSERVICES_SIGNATURE_OPTION', '-1', 'Require a signature on orders greater than or equal to (set to -1 to disable):', '6', '25', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Ready to Ship', 'MODULE_SHIPPING_FEDEXWEBSERVICES_READY_TO_SHIP', 'false', 'Enable using products_ready_to_ship field (requires Numinix Product Fields optional dimensions fields) to identify products which ship separately?', '6', '10', 'zen_cfg_select_option(array('true', 'false'), '', now())");		
			$this->mDb->CompleteTrans();
		}
	}

	function keys() {
		return array_merge( parent::keys(), array(
			'MODULE_SHIPPING_FEDEXWEBSERVICES_VERSION', 
			'MODULE_SHIPPING_FEDEXWEBSERVICES_ACT_NUM',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_METER_NUM',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_KEY',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_PWD',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_WEIGHT',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_ADDRESS_1',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_ADDRESS_2',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_CITY',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_STATE',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_POSTAL',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_PHONE',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_DROPOFF',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_FREE_SHIPPING',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_SAVER',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_STANDARD_OVERNIGHT',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_FIRST_OVERNIGHT',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_PRIORITY_OVERNIGHT',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_2DAY',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_PRIORITY',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_ECONOMY',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_GROUND',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_FREIGHT',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_INTERNATIONAL_GROUND',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_SATURDAY',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_INSURE',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_HANDLING_FEE',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_EXPRESS_HANDLING_FEE',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_INT_HANDLING_FEE',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_INT_EXPRESS_HANDLING_FEE',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_SIGNATURE_OPTION',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_RATES',
			'MODULE_SHIPPING_FEDEXWEBSERVICES_READY_TO_SHIP',
		) );
	}
}

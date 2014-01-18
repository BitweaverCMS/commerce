<?php
class fedexwebservices extends BitBase {
 var $code, $title, $description, $icon, $sort_order, $enabled, $tax_class, $country;

//Class Constructor
	function __construct() {
		global $order, $customer_id;
		
		parent::__construct();

		@define('MODULE_SHIPPING_FEDEX_WEB_SERVICES_INSURE', 0); 
		$this->code				= "fedexwebservices";
		$this->title			= tra( 'FedEx' );
		$this->description		= 'You will need to have registered an account with FedEx and proper approval from FedEx identity to use this module. Please see the README.TXT file for other requirements.';
		$this->icon 			= 'shipping_fedex';
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_FREE_SHIPPING == 'true' || zen_get_shipping_enabled($this->code)) {
			$this->enabled = ((MODULE_SHIPPING_FEDEX_WEB_SERVICES_STATUS == 'true') ? true : false);
		}
		if (defined("SHIPPING_ORIGIN_COUNTRY")) {
			if ((int)SHIPPING_ORIGIN_COUNTRY > 0) {
				$countries_array = zen_get_countries(SHIPPING_ORIGIN_COUNTRY, true);
				$this->country = $countries_array['countries_iso_code_2'];
			} else {
				$this->country = SHIPPING_ORIGIN_COUNTRY;
			}
		} else {
			$this->country = STORE_ORIGIN_COUNTRY;
		}

		if( ($this->enabled == true) && ((int)MODULE_SHIPPING_FEDEX_WEB_SERVICES_ZONE > 0) ) {
			$this->sort_order			 = MODULE_SHIPPING_FEDEX_WEB_SERVICES_SORT_ORDER;
			$this->tax_class				= MODULE_SHIPPING_FEDEX_WEB_SERVICES_TAX_CLASS;
			$check_flag = false;
			$check = $this->mDb->query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FEDEX_WEB_SERVICES_ZONE . "' and zone_country_id = '" . $order->delivery['country']['countries_id'] . "' order by zone_id");
			while (!$check->EOF) {
				if ($check->fields['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
					$check_flag = true;
					break;
				}
				$check->MoveNext();
			}

			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}

	//Class Methods

	function quote( $pShipHash = array() ) {
		/* FedEx integration starts */
		global $gBitCustomer, $order;
		
		require_once( dirname( __FILE__ ) . '/fedex-common.php5' );
		ini_set( "soap.wsdl_cache_enabled", "0" );

		$shippingWeight = (!empty( $pShipHash['shipping_weight'] ) && $pShipHash['shipping_weight'] > 1 ? $pShipHash['shipping_weight'] : 1);
		$shippingNumBoxes = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

		$client = new SoapClient( dirname( __FILE__ ) . "/RateService_v10.wsdl", array('trace' => 1) );
		$this->types = array();
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_PRIORITY == 'true') {
			$this->types['INTERNATIONAL_PRIORITY'] = array( 'code' => 'FEDEX_INTERNATIONAL_PRIORITY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_EXPRESS_HANDLING_FEE);
			$this->types['EUROPE_FIRST_INTERNATIONAL_PRIORITY'] = array( 'code' => 'FEDEX_EUROPE_FIRST_INTERNATIONAL_PRIORITY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_EXPRESS_HANDLING_FEE);
		}
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_ECONOMY == 'true') {
			$this->types['INTERNATIONAL_ECONOMY'] = array( 'code' => 'FEDEX_INTERNATIONAL_ECONOMY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_EXPRESS_HANDLING_FEE);
		}	
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_STANDARD_OVERNIGHT == 'true') {
			$this->types['STANDARD_OVERNIGHT'] = array( 'code' => 'FEDEX_STANDARD_OVERNIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
		}
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_FIRST_OVERNIGHT == 'true') {
			$this->types['FIRST_OVERNIGHT'] = array( 'code' => 'FEDEX_FIRST_OVERNIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
		}
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_PRIORITY_OVERNIGHT == 'true') {
			$this->types['PRIORITY_OVERNIGHT'] = array( 'code' => 'FEDEX_PRIORITY_OVERNIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
		}
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_2DAY == 'true') {
			$this->types['FEDEX_2_DAY'] = array( 'code' => 'FEDEX_2_DAY', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
		}
		// because FEDEX_GROUND also is returned for Canadian Addresses, we need to check if the country matches the store country and whether international ground is enabled
		if ((MODULE_SHIPPING_FEDEX_WEB_SERVICES_GROUND == 'true' && $order->delivery['country']['countries_id'] == STORE_COUNTRY) || (MODULE_SHIPPING_FEDEX_WEB_SERVICES_GROUND == 'true' && ($order->delivery['country']['countries_id'] != STORE_COUNTRY) && MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_GROUND == 'true')) {
			$this->types['FEDEX_GROUND'] = array( 'code' => 'FEDEX_GROUND', 'icon' => '', 'handling_fee' => ($order->delivery['country']['countries_id'] == STORE_COUNTRY ? MODULE_SHIPPING_FEDEX_WEB_SERVICES_HANDLING_FEE : MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_HANDLING_FEE));
			$this->types['GROUND_HOME_DELIVERY'] = array( 'code' => 'FEDEX_GROUND_HOME_DELIVERY', 'icon' => '', 'handling_fee' => ($order->delivery['country']['countries_id'] == STORE_COUNTRY ? MODULE_SHIPPING_FEDEX_WEB_SERVICES_HANDLING_FEE : MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_HANDLING_FEE));
		}
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_GROUND == 'true') {
			$this->types['INTERNATIONAL_GROUND'] = array( 'code' => 'FEDEX_INTERNATIONAL_GROUND', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_HANDLING_FEE);
		}
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_SAVER == 'true') {
			$this->types['FEDEX_EXPRESS_SAVER'] = array( 'code' => 'FEDEX_EXPRESS_SAVER', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
		}
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_FREIGHT == 'true') {
			$this->types['FEDEX_FREIGHT'] = array( 'code' => 'FEDEX_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
			$this->types['FEDEX_NATIONAL_FREIGHT'] = array( 'code' => 'FEDEX_NATIONAL_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
			$this->types['FEDEX_1_DAY_FREIGHT'] = array( 'code' => 'FEDEX_1_DAY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
			$this->types['FEDEX_2_DAY_FREIGHT'] = array( 'code' => 'FEDEX_2_DAY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
			$this->types['FEDEX_3_DAY_FREIGHT'] = array( 'code' => 'FEDEX_3_DAY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE);
			$this->types['INTERNATIONAL_ECONOMY_FREIGHT'] = array( 'code' => 'FEDEX_INTERNATIONAL_ECONOMY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_EXPRESS_HANDLING_FEE);
			$this->types['INTERNATIONAL_PRIORITY_FREIGHT'] = array( 'code' => 'FEDEX_INTERNATIONAL_PRIORITY_FREIGHT', 'icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_EXPRESS_HANDLING_FEE);
		}											
												 
		// customer details			
		$street_address = (!empty( $order->delivery['street_address'] ) ? $order->delivery['street_address']: '');
		$street_address2 = (!empty( $order->delivery['suburb'] ) ? $order->delivery['suburb']: '');
		$city = (!empty( $order->delivery['city'] ) ? $order->delivery['city'] : '');
		$state = (!empty( $order->delivery['zone_id'] ) ? zen_get_zone_code($order->delivery['country']['countries_id'], $order->delivery['zone_id'], '') : '');
		if ($state == "QC") $state = "PQ";
		$postcode = str_replace(array(' ', '-'), '', $order->delivery['postcode']);
		$country_id = $order->delivery['country']['countries_iso_code_2'];
			
		if( is_object( $order ) ) {
			$totals = $order->subtotal;
		} elseif( is_object( $gBitCustomer->mCart ) ) {
			$totals= $gBitCustomer->mCart->show_total();
		}
		$this->_setInsuranceValue($totals);
		
		$request['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_KEY, 'Password' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_PWD));
		$request['ClientDetail'] = array('AccountNumber' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_ACT_NUM, 'MeterNumber' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_METER_NUM );
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v10 using PHP ***');
		$request['Version'] = array('ServiceId' => 'crs', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0');
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = $this->_setDropOff(); // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		//if (zen_not_null($method) && in_array($method, $this->types)) {
			//$request['RequestedShipment']['ServiceType'] = $method; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
		//}
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		$request['RequestedShipment']['TotalInsuredValue']=array('Amount'=> $this->insurance, 'Currency' => (!empty( $_SESSION['currency'] ) ? $_SESSION['currency'] : DEFAULT_CURRENCY) );
		$request['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_KEY, 'Password' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_PWD));										 
		$request['ClientDetail'] = array('AccountNumber' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_ACT_NUM, 'MeterNumber' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_METER_NUM );
		$request['RequestedShipment']['Shipper'] = array(	'Address' => array(
															'StreetLines' => array(MODULE_SHIPPING_FEDEX_WEB_SERVICES_ADDRESS_1, MODULE_SHIPPING_FEDEX_WEB_SERVICES_ADDRESS_2), // Origin details
															'City' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_CITY,
															'StateOrProvinceCode' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_STATE,
															'PostalCode' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_POSTAL,
															'CountryCode' => $this->country));					
		$request['RequestedShipment']['Recipient'] = array(	'Address' => array (
															'StreetLines' => array(utf8_encode($street_address), utf8_encode($street_address2)), // customer street address
															'City' => utf8_encode($city), //customer city
															//'StateOrProvinceCode' => $state, //customer state
															'PostalCode' => $postcode, //customer postcode
															'CountryCode' => $country_id,
															'Residential' => empty( $order->delivery['company'] ) ) ); //customer county code
		if (in_array($country_id, array('US', 'CA'))) {
			$request['RequestedShipment']['Recipient']['StateOrProvinceCode'] = $state;
		}
		$request['RequestedShipment']['ShippingChargesPayment'] = array(	'PaymentType' => 'SENDER',
																			'Payor' => array('AccountNumber' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_ACT_NUM,
																			'CountryCode' => $this->country));
		$request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
		$request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
		$request['RequestedShipment']['RequestedPackageLineItems'] = array();
		
		$dimensions_failed = false;
		
		// check for ready to ship field
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_READY_TO_SHIP == 'true') {			
			// Not fixed for bitcommerce
			$products = $gBitCustomer->mCart->get_products();
			$packages = array('default' => 0);
			$new_shipping_num_boxes = 0;
			foreach ($products as $product) {
				$dimensions_query = "SELECT products_length, products_width, products_height, products_ready_to_ship, products_dim_type FROM " . TABLE_PRODUCTS . " 
														 WHERE products_id = " . (int)$product['id'] . " 
														 AND products_length > 0 
														 AND products_width > 0
														 AND products_height > 0 
														 LIMIT 1;";
				$dimensions = $this->mDb->query($dimensions_query);
				if ($dimensions->RecordCount() > 0 && $dimensions->fields['products_ready_to_ship'] == 1) {
					for ($i = 1; $i <= $product['quantity']; $i++) {
						$packages[] = array('weight' => $product['weight'], 'length' => $dimensions->fields['products_length'], 'width' => $dimensions->fields['products_width'], 'height' => $dimensions->fields['products_height'], 'units' => strtoupper($dimensions->fields['products_dim_type']));
					}		
				} else {
					$packages['default'] += $product['weight'] * $product['quantity']; 
				}
			}
			if (count($packages) > 1) {
				$za_tare_array = preg_split("/[:,]/" , SHIPPING_BOX_WEIGHT);
				$zc_tare_percent= $za_tare_array[0];
				$zc_tare_weight= $za_tare_array[1];

				$za_large_array = preg_split("/[:,]/" , SHIPPING_BOX_PADDING);
				$zc_large_percent= $za_large_array[0];
				$zc_large_weight= $za_large_array[1];
			}
			foreach ($packages as $id => $values) {
				if ($id === 'default') {
					// divide the weight by the max amount to be shipped (can be done inside loop as this occurance should only ever happen once
					// note $values is not an array
					if ($values == 0) continue;
					$shippingNumBoxes = ceil((float)$values / (float)SHIPPING_MAX_WEIGHT);
					if ($shippingNumBoxes < 1) $shippingNumBoxes = 1;
					$shippingWeight = round((float)$values / $shippingNumBoxes, 2); // 2 decimal places max
					for ($i=0; $i<$shippingNumBoxes; $i++) {
						$new_shipping_num_boxes++;
						if (SHIPPING_MAX_WEIGHT <= $shippingWeight) {
							$shippingWeight = $shippingWeight + ($shippingWeight*($zc_large_percent/100)) + $zc_large_weight;
						} else {
							$shippingWeight = $shippingWeight + ($shippingWeight*($zc_tare_percent/100)) + $zc_tare_weight;
						}
						if ($shippingWeight <= 0) $shippingWeight = 0.1; 
						$new_shipping_weight += $shippingWeight;					 
						$request['RequestedShipment']['RequestedPackageLineItems'][] = array('Weight' => array('Value' => $shippingWeight, 'Units' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_WEIGHT), 'GroupPackageCount' => 1 );
					}
				} else {
					// note $values is an array
					$new_shipping_num_boxes++;
					if ($values['weight'] <= 0) $values['weight'] = 0.1;
					$new_shipping_weight += $values['weight'];
					$request['RequestedShipment']['RequestedPackageLineItems'][] = array('Weight' => array('Value' => $values['weight'], 'Units' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_WEIGHT),
																						 'Dimensions' => array(	'Length' => $values['length'],
																												'Width' => $values['width'],
																												'Height' => $values['height'],
																												'Units' => $values['units'] 
																												),
																						  'GroupPackageCount' => 1,
																						 );
				}
			}
			$shippingNumBoxes = $new_shipping_num_boxes;
			$shippingWeight = round($new_shipping_weight / $shippingNumBoxes, 2);
		} else {
			if ($shippingWeight == 0) $shippingWeight = 0.1;
			
			for ($i=0; $i<$shippingNumBoxes; $i++) {
				$request['RequestedShipment']['RequestedPackageLineItems'][] = array('Weight' => array('Value' => $shippingWeight, 'Units' => MODULE_SHIPPING_FEDEX_WEB_SERVICES_WEIGHT), 'GroupPackageCount' => 1 );
			}
		}
		$request['RequestedShipment']['PackageCount'] = $shippingNumBoxes;
		
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_SATURDAY == 'true') {
			$request['RequestedShipment']['ServiceOptionType'] = 'SATURDAY_DELIVERY';
		}
		
		if (MODULE_SHIPPING_FEDEX_WEB_SERVICES_SIGNATURE_OPTION >= 0 && $totals >= MODULE_SHIPPING_FEDEX_WEB_SERVICES_SIGNATURE_OPTION) { 
			$request['RequestedShipment']['SpecialServicesRequested'] = 'SIGNATURE_OPTION'; 
		}

		try {
			$response = $client->getRates($request);
			if( !empty( $response ) && ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR' && !empty( $response->RateReplyDetails )) ) {
				if (is_object($response->RateReplyDetails)) {
					$response->RateReplyDetails = get_object_vars($response->RateReplyDetails);
				}
				//echo '<pre>';
				//print_r($response->RateReplyDetails);
				//echo '</pre>';
				switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
					case (0):
					$show_box_weight = '';
					break;
					case (1):
					$show_box_weight = ' (' . $shippingNumBoxes. ' ' . TEXT_SHIPPING_BOXES . ')';
					break;
					case (2):
					$show_box_weight = ' (' . number_format($shippingWeight * $shippingNumBoxes,2) . tra( 'lbs' ) . ')';
					break;
					default:
					$show_box_weight = ' (' . $shippingNumBoxes . ' x ' . number_format($shippingWeight,2) . tra( 'lbs' ) . ')';
					break;
				}			
				$this->quotes = array( 'id' => $this->code,
										'module' => $this->title,
										'info' => $this->info(),
										'weight' => $show_box_weight, 
										);
				$methods = array();
				foreach ($response->RateReplyDetails as $rateReply) {
					if( array_key_exists( $rateReply->ServiceType, $this->types ) && ( empty( $pShipHash['method'] ) || (str_replace('_', '', $rateReply->ServiceType) == $pShipHash['method']) ) ) {
						$cost = NULL;
						if(MODULE_SHIPPING_FEDEX_WEB_SERVICES_RATES=='LIST') {
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
						$methods[] = array(	'id' => str_replace('_', '', $rateReply->ServiceType),
											'title' => ucwords(strtolower(str_replace('_', ' ', $rateReply->ServiceType))),
											'cost' => $cost + (strpos($this->types[$rateReply->ServiceType]['handling_fee'], '%') ? ($cost * (float)$this->types[$rateReply->ServiceType]['handling_fee'] / 100) : (float)$this->types[$rateReply->ServiceType]['handling_fee']),
											'code' => $this->types[$rateReply->ServiceType]['code'],
										  );
					}
				}
				$this->quotes['methods'] = $methods;
				if ($this->tax_class > 0) {
					$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
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
			$this->quotes = array('module' => $this->title, 'error'	=> $message);
		}

		if ( !empty( $this->icon ) ) {
			$this->quotes['icon'] = $this->icon;
		}
		//echo '<!-- Quotes: ';
		//print_r($this->quotes);
		//print_r($_SESSION['shipping']);
		//echo ' -->';
		return $this->quotes;
	}

	// method added for expanded info in FEAC
	function info() {
		return $this->title;
	}
		
	function _setInsuranceValue($order_amount){
		if ($order_amount > (float)MODULE_SHIPPING_FEDEX_WEB_SERVICES_INSURE) {
			$this->insurance = sprintf("%01.2f", $order_amount);
		} else {
			$this->insurance = 0;
		}
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
		switch(MODULE_SHIPPING_FEDEX_WEB_SERVICES_DROPOFF) {
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

	function check(){
		if(!isset($this->_check)){
			$check_query	= $this->mDb->query("SELECT configuration_value FROM ". TABLE_CONFIGURATION ." WHERE configuration_key = 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_STATUS'");
			$this->_check = $check_query->RecordCount();			
			if ($this->_check && defined(MODULE_SHIPPING_FEDEX_WEB_SERVICES_VERSION) && MODULE_SHIPPING_FEDEX_WEB_SERVICES_VERSION != '1.3.0') {
				$this->mDb->query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1.3.0' WHERE configuration_key = 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_VERSION' LIMIT 1;");
			}
		}
		return $this->_check;
	}

	function install() {
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable FedEx Web Services','MODULE_SHIPPING_FEDEX_WEB_SERVICES_STATUS','true','Do you want to offer FedEx shipping?','6','0','zen_cfg_select_option(array(''true'',''false''),',now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Version Installed', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_VERSION', '1.3.0', '', '6', '0', now())"); 
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Account Number', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ACT_NUM', '', 'Enter FedEx Account Number', '6', '3', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Meter Number', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_METER_NUM', '', 'Enter FedEx Meter Number (You can get one at <a href=\"http://www.fedex.com/us/developer/\" target=\"_blank\">http://www.fedex.com/us/developer/</a>)', '6', '4', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Authentication Key', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_KEY', '', 'Enter FedEx Authentication Key (You can get one at <a href=\"http://www.fedex.com/us/developer/\" target=\"_blank\">http://www.fedex.com/us/developer/</a>)', '6', '4', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Password', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_PWD', '', 'Enter FedEx Password', '6', '4', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Weight Units', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_WEIGHT', 'LB', 'Weight Units:', '6', '10', 'zen_cfg_select_option(array(''LB'', ''KG''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('First line of street address', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ADDRESS_1', '', 'Enter the first line of your ship-from street address, required', '6', '20', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Second line of street address', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ADDRESS_2', '', 'Enter the second line of your ship-from street address, leave blank if you do not need to specify a second line', '6', '21', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('City name', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_CITY', '', 'Enter the city name for the ship-from street address, required', '6', '22', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('State or Province name', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_STATE', '', 'Enter the 2 letter state or province name for the ship-from street address, required for Canada and US', '6', '23', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Postal code', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_POSTAL', '', 'Enter the postal code for the ship-from street address, required', '6', '24', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Phone number', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_PHONE', '', 'Enter a contact phone number for your company, required', '6', '25', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable for Always Free Shipping', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_FREE_SHIPPING', 'false', 'Should this module be enabled even when all items in the cart are marked as ALWAYS FREE SHIPPING?', '6', '30', 'zen_cfg_select_option(array(''true'',''false''),', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Drop off type', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_DROPOFF', '1', 'Dropoff type (1 = Regular pickup, 2 = request courier, 3 = drop box, 4 = drop at BSC, 5 = drop at station)?', '6', '30', 'zen_cfg_select_option(array(''1'',''2'',''3'',''4'',''5''),', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Express Saver', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_SAVER', 'true', 'Enable FedEx Express Saver', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Standard Overnight', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_STANDARD_OVERNIGHT', 'true', 'Enable FedEx Express Standard Overnight', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable First Overnight', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_FIRST_OVERNIGHT', 'true', 'Enable FedEx Express First Overnight', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Priority Overnight', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_PRIORITY_OVERNIGHT', 'true', 'Enable FedEx Express Priority Overnight', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable 2 Day', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_2DAY', 'true', 'Enable FedEx Express 2 Day', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Priority', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_PRIORITY', 'true', 'Enable FedEx Express International Priority', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Economy', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_ECONOMY', 'true', 'Enable FedEx Express International Economy', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Ground', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_GROUND', 'true', 'Enable FedEx Ground', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Ground', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_GROUND', 'true', 'Enable FedEx International Ground', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Freight', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_FREIGHT', 'true', 'Enable FedEx Freight', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Saturday Delivery', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_SATURDAY', 'false', 'Enable Saturday Delivery', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Domestic Ground Handling Fee', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_HANDLING_FEE', '', 'Add a domestic handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Domestic Express Handling Fee', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE', '', 'Add a domestic handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('International Ground Handling Fee', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_HANDLING_FEE', '', 'Add an international handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('International Express Handling Fee', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_EXPRESS_HANDLING_FEE', '', 'Add an international handling fee or leave blank (example: 15 or 15%)', '6', '25', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('FedEx Rates','MODULE_SHIPPING_FEDEX_WEB_SERVICES_RATES','LIST','FedEx Rates','6','0','zen_cfg_select_option(array(''LIST'',''ACCOUNT''),',now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Signature Option', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_SIGNATURE_OPTION', '-1', 'Require a signature on orders greater than or equal to (set to -1 to disable):', '6', '25', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Ready to Ship', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_READY_TO_SHIP', 'false', 'Enable using products_ready_to_ship field (requires Numinix Product Fields optional dimensions fields) to identify products which ship separately?', '6', '10', 'zen_cfg_select_option(array(''true'', ''false''), ', now())");		
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '98', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '25', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_SORT_ORDER', '0', 'Sort order of display.', '6', '99', now())"); 
	}

	function remove() {
		$this->mDb->query("DELETE FROM ". TABLE_CONFIGURATION ." WHERE configuration_key in ('". implode("','",$this->keys()). "')");
	}

	function keys() {
		return array('MODULE_SHIPPING_FEDEX_WEB_SERVICES_STATUS',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_VERSION', 
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ACT_NUM',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_METER_NUM',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_KEY',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_PWD',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_WEIGHT',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ADDRESS_1',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ADDRESS_2',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_CITY',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_STATE',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_POSTAL',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_PHONE',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_DROPOFF',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_FREE_SHIPPING',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_SAVER',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_STANDARD_OVERNIGHT',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_FIRST_OVERNIGHT',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_PRIORITY_OVERNIGHT',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_2DAY',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_PRIORITY',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_ECONOMY',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_GROUND',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_FREIGHT',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INTERNATIONAL_GROUND',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_SATURDAY',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_TAX_CLASS',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_HANDLING_FEE',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_EXPRESS_HANDLING_FEE',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_HANDLING_FEE',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_INT_EXPRESS_HANDLING_FEE',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_SIGNATURE_OPTION',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_RATES',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_READY_TO_SHIP',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_ZONE',
					 'MODULE_SHIPPING_FEDEX_WEB_SERVICES_SORT_ORDER'
					 );
	}
}

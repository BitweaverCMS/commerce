<?php
// Zen Cart Shipping module for shipping via FedEx
// Uses FedEx REST API 
// Replaces use of FedEx XML Web Services API (deprecated).
// Based on work by Numinix, Vinos de Frutas Tropicales, and many others.
// Portions Copyright 2003 osCommerce
// Portions Copyright 2003-2023 Zen Cart Development Team
// Portions Copyright Vinos de Frutas Tropicales
// Copyright 2023 That Software Guy 
// Additional documentation: 
// https://github.com/scottcwilson/zencart_fedexrest

/*
 * TODO LIST:
 * Street address for send and receive
 */

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class fedexrest extends CommercePluginShippingBase {
	const BASE_URL = 'https://apis.fedex.com';
//	const BASE_URL = 'https://apis-sandbox.fedex.com';
	const SAT_SUFFIX = 'SAT'; 

	protected $moduleVersion = '1.3.2';

	protected $fedex_act_num,
		$country,
		$debug,
		$logfile,
		$types,
		$fedex_shipping_num_boxes,
		$fedex_shipping_weight;


	function __construct() {
		parent::__construct();
		$this->title			= tra( 'FedEx' );
		$this->description		= 'You will need to have registered an account with FedEx and proper approval from FedEx identity to use this module. Please see the README.TXT file for other requirements.';
		$this->icon				= 'shipping_fedex';
/*
		if (defined("SHIPPING_ORIGIN_ORIGIN_COUNTRY_CODE")) {
			if ((int)SHIPPING_ORIGIN_ORIGIN_COUNTRY_CODE > 0) {
				$countries_array = zen_get_countries((int)SHIPPING_ORIGIN_ORIGIN_COUNTRY_CODE, true);
				$this->country = $countries_array['countries_iso_code_2'];
				if (!strlen($this->country) > 0) { //when country failed to be retrieved, likely because running from admin.
					$this->country = $this->country_iso('', (int)SHIPPING_ORIGIN_ORIGIN_COUNTRY_CODE);
				}
			} else {
				$this->country = SHIPPING_ORIGIN_ORIGIN_COUNTRY_CODE;
			}
		} else {
			$this->country = STORE_ORIGIN_ORIGIN_COUNTRY_CODE;
		}

		$this->debug = ($this->getModuleConfigValue( '_DEBUG' ) === 'true');
		$this->logfile = DIR_FS_LOGS . '/fedexrest-' . date('Ymd') . '.log';
		if (($this->enabled == true) && ((int)$this->getModuleConfigValue( '_ZONE' ) > 0) && !IS_ADMIN_FLAG) {

			$check_flag = false;
			if (isset($order->delivery['country']['id'])) {
			$check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . $this->getModuleConfigValue( '_ZONE' ) . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
			}

			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	  if ($this->enabled === true && $this->getOAuthToken() === false) {
			$this->enabled = false;
	  }
*/
		$this->setTypes();
	}

	function country_iso($country_name = '', $country_id = '')
	{
		global $db;
		$sql = 'SELECT countries_iso_code_2 FROM ' . TABLE_COUNTRIES . ' WHERE ';
		if (strlen($country_name) > 0) {
			$sql .= ' countries_name = \'' . $country_name . '\'';
		} elseif ($country_id > 0) {
			$sql .= ' countries_id = ' . $country_id;
		} else {
			return "";
		}

		$result = $db->Execute($sql);
		return $result->fields['countries_iso_code_2'];

	}

	function setTypes()
	{

		$this->types = [];
		if ($this->getModuleConfigValue( '_INTERNATIONAL_PRIORITY' ) == 'true') {
			$this->types['FEDEX_INTERNATIONAL_PRIORITY'] = array( 'code' => 'FEDEX_INTERNATIONAL_PRIORITY', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_INT_EXPRESS_HANDLING_FEE' ));
			$this->types['EUROPE_FIRST_INTERNATIONAL_PRIORITY'] = array( 'code' => 'FEDEX_EUROPE_FIRST_INTERNATIONAL_PRIORITY', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_INT_EXPRESS_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_INTERNATIONAL_ECONOMY' ) == 'true') {
			$this->types['INTERNATIONAL_ECONOMY'] = array( 'code' => 'FEDEX_INTERNATIONAL_ECONOMY', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_INT_EXPRESS_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_INTERNATIONAL_CONNECT_PLUS' ) == 'true') {
			$this->types['FEDEX_INTERNATIONAL_CONNECT_PLUS'] = array( 'code' => 'FEDEX_INTERNATIONAL_CONNECT_PLUS', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_INT_EXPRESS_HANDLING_FEE' )); 
		}
		if ($this->getModuleConfigValue( '_STANDARD_OVERNIGHT' ) == 'true') {
			$this->types['STANDARD_OVERNIGHT'] = array( 'code' => 'FEDEX_STANDARD_OVERNIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_FIRST_OVERNIGHT' ) == 'true') {
			$this->types['FIRST_OVERNIGHT'] = array( 'code' => 'FEDEX_FIRST_OVERNIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_PRIORITY_OVERNIGHT' ) == 'true') {
			$this->types['PRIORITY_OVERNIGHT'] = array( 'code' => 'FEDEX_PRIORITY_OVERNIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_2DAY' ) == 'true') {
			$this->types['FEDEX_2_DAY'] = array( 'code' => 'FEDEX_2_DAY', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
		}
		// because FEDEX_GROUND also is returned for Canadian Addresses, we need to check if the country matches the store country and whether international ground is enabled
		if ($this->getModuleConfigValue( '_GROUND' ) == 'true') {
			$this->types['FEDEX_GROUND'] = array( 'code' => 'FEDEX_GROUND', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_HANDLING_FEE' ));
			$this->types['GROUND_HOME_DELIVERY'] = array( 'code' => 'FEDEX_GROUND_HOME_DELIVERY', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_HOME_DELIVERY_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_INTERNATIONAL_GROUND' ) == 'true') {
			$this->types['INTERNATIONAL_GROUND'] = array( 'code' => 'FEDEX_INTERNATIONAL_GROUND', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_INT_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_EXPRESS_SAVER' ) == 'true') {
			$this->types['FEDEX_EXPRESS_SAVER'] = array( 'code' => 'FEDEX_EXPRESS_SAVER', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
		}
		if ($this->getModuleConfigValue( '_FREIGHT' ) == 'true') {
			$this->types['FEDEX_FREIGHT'] = array( 'code' => 'FEDEX_FREIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
			$this->types['FEDEX_NATIONAL_FREIGHT'] = array( 'code' => 'FEDEX_NATIONAL_FREIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
			$this->types['FEDEX_1_DAY_FREIGHT'] = array( 'code' => 'FEDEX_1_DAY_FREIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
			$this->types['FEDEX_2_DAY_FREIGHT'] = array( 'code' => 'FEDEX_2_DAY_FREIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
			$this->types['FEDEX_3_DAY_FREIGHT'] = array( 'code' => 'FEDEX_3_DAY_FREIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_EXPRESS_HANDLING_FEE' ));
			$this->types['INTERNATIONAL_ECONOMY_FREIGHT'] = array( 'code' => 'FEDEX_INTERNATIONAL_ECONOMY_FREIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_INT_EXPRESS_HANDLING_FEE' ));
			$this->types['INTERNATIONAL_PRIORITY_FREIGHT'] = array( 'code' => 'FEDEX_INTERNATIONAL_PRIORITY_FREIGHT', 'icon' => '', 'handling_fee' => $this->getModuleConfigValue( '_INT_EXPRESS_HANDLING_FEE' ));
		}
	}

	function getOAuthToken()
	{
		 if (isset($_SESSION['fedexrest_token_expires']) && $_SESSION['fedexrest_token_expires'] > time()) {
//			  $this->debugLog('Existing OAuth token is present.');
			  return true;
		 }

		// Get the bearer token
		// https://developer.fedex.com/api/en-us/catalog/authorization/v1/docs.html

//		$this->debugLog("Date and Time: " . date('Y-m-d H:i:s') . PHP_EOL . "FEDEX URL: " . self::BASE_URL, true);
		$url = self::BASE_URL . '/oauth/token';
		$timeout = 15;
		$ch = curl_init();
		$input = 'grant_type=' . 'client_credentials' . '&' .
			'client_id=' . $this->getModuleConfigValue( '_API_KEY' ) . '&' .
			'client_secret=' . $this->getModuleConfigValue( '_SECRET_KEY' );

		$curl_options = [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => [
				"cache-control: no-cache",
				"content-type: application/x-www-form-urlencoded"
			],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $input,
			CURLOPT_TIMEOUT => (int)$timeout,
		];
		curl_setopt_array($ch, $curl_options);

//		$this->debugLog("Auth Request: $input");

		$response = curl_exec($ch);
		if (curl_errno($ch) !== 0) {
			bit_error_email( 'FedEx Error from cURL: ' . sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch)) );
			curl_close($ch);
			return false;  
		}
		curl_close($ch);

		$arr_response = json_decode($response, true);
		$this->debugLog("Auth Response: " . print_r($arr_response, true));

		if (!isset($arr_response['access_token'])) {
			// Ruh roh.  How bad is it? 
			if (isset($arr_response['errors'])) {
				// look for bad client creds error 
				foreach ($arr_response['errors'] as $errobj) {
					if ($errobj['code'] == 'NOT.AUTHORIZED.ERROR') {
							global $db;
							$db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'false' WHERE configuration_key = 'MODULE_SHIPPING_FEDEX_REST_STATUS'");
							zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_SHIPPING_FEDEXREST_EMAIL_SUBJECT, "The API Key/Secret Key combination you set were not recognized by FedEx, and so the fedexrest shipping module has been automatically disabled.  Please work with your FedEx Account Rep to get working credentials.", STORE_NAME, EMAIL_FROM);
					}
				}
			}
			return false;
		}

		$_SESSION['fedexrest_token'] = $arr_response['access_token'];
		$_SESSION['fedexrest_token_expires'] = time() + $arr_response['expires_in'] - 3;

		return true; 
	}

	// {{{	++++++++ quote ++++++++
	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {

			$this->getOAuthToken();

			$pShipHash['shipping_num_boxes'] = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

			// Do the rate query
			// https://developer.fedex.com/api/en-us/catalog/rate/v1/docs.html
			$timeout = 15;
			$ch = curl_init();

			$url = self::BASE_URL . '/rate/v1/rates/quotes';
			$rate_hdrs = [
				"Authorization: Bearer " . $_SESSION['fedexrest_token'], 
				"X-locale: " . $this->getModuleConfigValue( '_TEXT_LOCALE' ),
				"Content-Type: application/json",
			];

			// customer details
/*
			$street_address = $order->delivery['street_address'] ?? '';
			$street_address2 = $order->delivery['suburb'] ?? '';
			$city = $order->delivery['city'] ?? '';
			if (isset($order->delivery['country']['id'])) {
				$state = zen_get_zone_code($order->delivery['country']['id'], intval($order->delivery['zone_id']), '');
			} else {
				$countryId = $db->Execute("SELECT countries_id FROM " . TABLE_COUNTRIES . " WHERE countries_name = '" . $order->delivery['country'] . "'");
				$state = zen_get_zone_code($countryId->fields['countries_id'], intval($order->delivery['zone_id']), '');
			}
			if ($state == "QC") $state = "PQ";
			if (empty($order->delivery['postcode'])) $order->delivery['postcode'] = '';
			$postcode = str_replace(array(' ', '-'), '', $order->delivery['postcode']);
			if (isset($order->delivery['country']['iso_code_2'])) {
				$country_id = $order->delivery['country']['iso_code_2'];
			} else {
				$country_id = $this->country_iso($order->delivery['country']);
			}

			//Skip the state if the state is over 2 characters, such as New Zealand, Germany and Spain. Otherwise no quote.
			if (strlen(trim($state)) > 2)
			{
				$state = '';
			}

			$this->fedex_shipping_num_boxes = ($shipping_num_boxes > 0 ? $shipping_num_boxes : 1);
			$this->fedex_shipping_weight = $shipping_weight;
*/


			$packages = [];
			$boxValue = round($pShipHash['shipping_value'] / $pShipHash['shipping_num_boxes'], 2);
			$packageSpecialServices = []; 
			if (!empty($this->getModuleConfigValue( '_SIGNATURE_OPTION' )) && $this->getModuleConfigValue( '_SIGNATURE_OPTION' ) > -1 && $pShipHash['shipping_value'] >= $this->getModuleConfigValue( '_SIGNATURE_OPTION' )) {
				// Only works in the US
				if ($country_id == 'US') { 
					$packageSpecialServices = [
						'signatureOptionType' => 'INDIRECT',
					]; 
				}
			}
	//MODULE_SHIPPING_FEDEX_REST_INSURE  
			for ($i = 0; $i < $pShipHash['shipping_num_boxes']; $i++) {
				$package = [
					"weight" => [
						"units" => $this->getModuleConfigValue( '_WEIGHT' ),
						"value" => $pShipHash['shipping_weight_box'],
					],
				];
				if( $pShipHash['shipping_value'] > (float)$this->getModuleConfigValue( '_INSURE' ) ) {
					$package += [
						"declaredValue" => [
							"amount" => $boxValue,
							"currency" => $pShipmentHash['shipping_value_currency'],
						], 
					]; 
				}
				if (!empty($packageSpecialServices)) { 
					$package += [
						'packageSpecialServices' => $packageSpecialServices
					]; 
				}
				$packages[] = $package;
			}

			$requestControlParms = [
				'returnTransitTimes' => ($this->getModuleConfigValue( '_TRANSIT_TIME' ) == 'true' ? 'TRUE' : 'FALSE'),
			];
			if ($this->getModuleConfigValue( '_SATURDAY' ) == 'true') {
			  $requestControlParms += [
				  'variableOptions' => 'SATURDAY_DELIVERY', 
			  ]; 
			}

			if ($this->getModuleConfigValue( '_SHIP_TO_RESIDENCE' ) == 'true') {
				$ship_to_residential = true; 
			} else if ($this->getModuleConfigValue( '_SHIP_TO_RESIDENCE' ) == 'false') {
				$ship_to_residential = false; 
			} else { 
				$ship_to_residential = empty( trim( $pShipHash['destination']['company'] ) ); 
			}

			$shipDate = new DateTime();
			// $shipDate->modify('next thursday');
			$rate_data = [
				"accountNumber" => [
					"value" => $this->getModuleConfigValue( '_ACT_NUM' )
				],
				"rateRequestControlParameters" => $requestControlParms,
				"requestedShipment" => [
					"shipper" => [
						"address" => [
							"city" => $this->getModuleConfigValue( '_CITY' ),
							"stateOrProvinceCode" => $this->getModuleConfigValue( '_STATE' ),
							"postalCode" => $this->getModuleConfigValue( '_POSTAL' ),
							"countryCode" => $this->getModuleConfigValue( '_ORIGIN_COUNTRY_CODE' ),
							"residential" => ($this->getModuleConfigValue( '_SHIP_FROM_RESIDENCE' ) == 'true' ? true : false),
						]
					],
					"recipient" => [
						"address" => [
							"city" => $pShipHash['destination']['city'],
							"stateOrProvinceCode" => $pShipHash['destination']['zone_code'],
							"postalCode" => $pShipHash['destination']['postcode'],
							"countryCode" => $pShipHash['destination']['countries_iso_code_2'],
							"residential" => $ship_to_residential, 
						]
					],
					"rateRequestType" => [
						($this->getModuleConfigValue( '_RATES' ) == 'LIST' ? 'LIST' : 'ACCOUNT'),
					],
					"shipDateStamp" => $shipDate->format("Y-m-d"),
					"pickupType" => $this->_setPickup(),
					"requestedPackageLineItems" => $packages,
					"documentShipment" => false,
					"packagingType" => "YOUR_PACKAGING",
					"totalPackageCount" => $pShipHash['shipping_num_boxes'], 
					"groupShipment" => true,
					"groundShipment" => true
				],
				"carrierCodes" => ["FDXG", "FDXE"],
			];

			if( $this->isInternationOrder( $pShipHash ) ) { 
				$rate_data ["requestedShipment"]["customsClearanceDetail"] = [ 
					"commodities" => [ 
						[ "description" => "Goods", 
						"quantity" => "1", 
						"quantityUnits" => "PCS", 
						"weight" => [
							"units" => $this->getModuleConfigValue( '_WEIGHT' ),
							"value" => $pShipHash['shipping_weight_box'],
						],
						"customsValue" => [ 
							"amount" => $pShipHash['shipping_value'], 
							"currency" => $pShipHash['shipping_value_currency'],
						], 
						], 
					], 
				]; 
			}

//vvd( $pShipHash, $rate_data );
			$curl_options = [
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => $rate_hdrs,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => json_encode($rate_data),
				CURLOPT_TIMEOUT => (int)$timeout,
				CURLOPT_ENCODING => 'gzip',
			];
			curl_setopt_array($ch, $curl_options);

			// $this->debugLog("Rate Request: " . print_r($rate_data, true));
			$this->debugLog("JSON Rate Request: " . json_encode($rate_data, JSON_PRETTY_PRINT));

			$response = curl_exec($ch);
			if (curl_errno($ch) !== 0) {
				$this->debugLog('Error from cURL: ' . sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch)));
				echo 'Error from cURL: ' . sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch));
			}
			curl_close($ch);

			$arr_response = json_decode($response, true);
			$this->debugLog("Rate Response: " . print_r($arr_response, true));
			if( !empty($arr_response['output']['rateReplyDetails']) ) {
				switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
					case (0):
						$show_box_weight = '';
						break;
					case (1):
						$show_box_weight = ' (' . $pShipHash['shipping_num_boxes'] . ' ' . TEXT_SHIPPING_BOXES . ')';
						break;
					case (2):
						$show_box_weight = ' (' . number_format($pShipHash['shipping_weight'] * $pShipHash['shipping_num_boxes'], 2) . tra( 'lbs' ) . ')';
						break;
					default:
						$show_box_weight = ' (' . $pShipHash['shipping_num_boxes'] . ' x ' . number_format($pShipHash['shipping_weight_total'], 2) . tra( 'lbs' ) . ')';
						break;
				}

				$methods = [];
				foreach ($arr_response['output']['rateReplyDetails'] as $rate) {
					// ensure key exists (i.e. service enabled) 
					$check_serviceType = str_replace('_', '', $rate['serviceType']); 
					$method_ok = false; 
					if (array_key_exists($rate['serviceType'], $this->types)) {
						if ($pShipHash['method'] == '') {
							$method_ok = true;
						} else if ($check_serviceType == $pShipHash['method']) {
							$method_ok = true;
						} else if ($check_serviceType . self::SAT_SUFFIX == $pShipHash['method']) {
							$method_ok = true;
						}
					} 
					if (!isset($rate['ratedShipmentDetails'][0])) $method_ok = false;
					if (!$method_ok) continue; 
					// We have to make sure it's not Saturday if not wanted
					if (!empty($pShipHash['method'])) {
						if (!empty($rate['commit']['saturdayDelivery']) && $rate['commit']['saturdayDelivery'] == 1) {
							if (($check_serviceType . self::SAT_SUFFIX) !== $pShipHash['method']) {
								continue; 
							}
						}
					}

					if ($method_ok) {
						$cost = $rate['ratedShipmentDetails'][0]['totalNetFedExCharge'];
						// add on specified fees - could be % or flat rate
						$fee = 0; 
						if (!empty($this->types[$rate['serviceType']]['handling_fee'])) { 
							$fee = $this->types[$rate['serviceType']]['handling_fee']; 
						}
						$cost = $cost + ((strpos($fee, '%') !== FALSE) ? ($cost * (float)$fee / 100) : (float)$fee);

						$transitDays = 0;
						$transitTime = '';
						$deliveryDate = '';
						if( !empty( $rate['operationalDetail']['deliveryDate'] ) ) {
							$deliveryDate = (new DateTime( $rate['operationalDetail']['deliveryDate'] ))->format( 'Y-m-d' );
						} elseif( !empty( $rate['operationalDetail']['transitTime'] ) ) {
							$transitDays = 0;
							switch( $rate['operationalDetail']['transitTime'] ) {
								case 'ONE_DAY':
									$transitDays = 1; break;
								case 'TWO_DAYS':
									$transitDays = 2; break;
								case 'THREE_DAYS':
									$transitDays = 3; break;
								case 'FOUR_DAYS':
									$transitDays = 4; break;
								case 'FIVE_DAYS':
									$transitDays = 5; break;
							}
							if( $transitDays ) {
								$shipDate = new DateTime( $this->getShippingDate( $pShipHash ) );
								$shipDate->add( new DateInterval( 'P'.$transitDays.'D') );
								$deliveryDate = $shipDate->format( 'Y-m-d' );
							}
						}

						if( empty( $transitDays ) ) {
							switch( $rate['serviceType'] ) {
								case 'FIRST_OVERNIGHT':
								case 'STANDARD_OVERNIGHT':
								case 'PRIORITY_OVERNIGHT':
									$transitDays = '1'; break;
								case 'FEDEX_2_DAY':
									$transitDays = '2'; break;
								case 'FEDEX_EXPRESS_SAVER':
									$transitDays = '3'; break;
								case 'FEDEX_GROUND':
								case 'GROUND_HOME_DELIVERY':
									$transitDays = '2-7'; break;
								case 'INTERNATIONAL_PRIORITY':
									$transitDays = '2'; break;
								case 'INTERNATIONAL_ECONOMY':
									$transitDays = '5'; break;
							}
						}

						if( $transitDays ) {
							$transitTime = $transitDays.' '.($transitDays == '1' ? tra( 'Day' ) : tra( 'Days' ));
						}
/*						
						// Show transit time? 
						$transitTime = '';
						if ($this->getModuleConfigValue( '_TRANSIT_TIME' ) == 'true' && in_array($rate['serviceType'], array('GROUND_HOME_DELIVERY', 'FEDEX_GROUND', 'INTERNATIONAL_GROUND'))) {
							$transitTime = ' (' . str_replace(array('_', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen'), array(' business ', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14), strtolower($rate['operationalDetail']['transitTime'] ?? '')) . ')';
						}
						$id_suffix = ''; 
						if (!empty($rate['commit']['saturdayDelivery']) && $rate['commit']['saturdayDelivery'] == 1) {
							$transitTime .= MODULE_SHIPPING_FEDEXREST_SATURDAY; 
							$id_suffix = self::SAT_SUFFIX; 
						}
*/
						$methods[] = array(	'id' => str_replace('_', '', $rate['serviceType']),
											'title' => ucwords(strtolower(str_replace('_', ' ', $rate['serviceType']))),
											'cost' => $cost + (strpos($this->types[$rate['serviceType']]['handling_fee'], '%') ? ($cost * (float)$this->types[$rate['serviceType']]['handling_fee'] / 100) : (float)$this->types[$rate['serviceType']]['handling_fee']),
											'code' => $this->types[$rate['serviceType']]['code'],
											'transit_days' => $transitDays,
											'transit_time' => $transitTime,
											'delivery_date' => $deliveryDate,
										  );
					}
				}

				$this->sortQuoteMethods( $methods );
				$quotes['methods'] = $methods;
				if ($this->tax_class > 0) {
					$quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
				}
			}

			if( !empty( $arr_response['errors'] ) ) {
				foreach( $arr_response['errors'] as $errorHash ) {
					$quotes['error'] .= $errorHash['code'].': '.$errorHash['message'];
				}
			}

			if ( !empty( $this->icon ) && !empty( $quotes ) ) {
				$quotes['icon'] = $this->icon;
			}
		}

		return $quotes;
	}
	
	function _setPickup() {
		switch ($this->getModuleConfigValue( '_PICKUP' )) {
			case '1':
				return 'USE_SCHEDULED_PICKUP';
				break;
			case '2':
				return 'CONTACT_FEDEX_TO_SCHEDULE';
				break;
			case '3':
				return 'DROPOFF_AT_FEDEX_LOCATION';
				break;
		}
	}
	// }}} Quote

	// {{{  ++++++++ createShipment ++++++++
	function createShipment( $pOrder, $pShipmentHash ) {
		global $gCommerceSystem;

		list( $shipCarrier, $shipMethod ) = explode( '_', $pShipmentHash['shipment']['ship_method'] );

		$requestJson = '{
  "mergeLabelDocOption": "LABELS_AND_DOCS",
	"requestedShipment": {
	"shipDatestamp": "'.date( 'Y-m-d' ).'",
	"totalDeclaredValue": {
		"amount": '.round( $pOrder->getField( 'total', 0 ) / 4, 2 ).',
		"currency": "'. $pOrder->getField( 'currency' ).'"
	},
	"shipper": {
		"address": {
			"streetLines": [
				"'.mb_strimwidth( $this->getModuleConfigValue( '_ADDRESS_1' ), 0, 35 ).'"';
			if( $this->getModuleConfigValue( '_ADDRESS_2' ) ) {
				$requestJson .= ',
				"'.mb_strimwidth( $this->getModuleConfigValue( '_ADDRESS_2' ), 0, 35 ).'"';
			}
			$requestJson .= '
			],
			"city": "'.mb_strimwidth( $this->getModuleConfigValue( '_CITY' ), 0, 35 ).'",
			"stateOrProvinceCode": "'.mb_strimwidth( $this->getModuleConfigValue( '_STATE' ), 0, 10 ).'",
			"postalCode": "'.mb_strimwidth( $this->getModuleConfigValue( '_POSTAL' ), 0, 10 ).'",
			"countryCode": "'.$this->getModuleConfigValue( '_US' ).'",
			"residential": false
		},
		"contact": {
			"personName": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'STORE_OWNER' ), 0, 75 ).'",
			"emailAddress": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'EMAIL_FROM' ), 0, 80 ).'",
			"phoneNumber": "'.mb_strimwidth( $this->getModuleConfigValue( '_PHONE' ), 0, 15 ).'",
			"companyName": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'STORE_NAME' ), 0, 35 ).'"
		},'.
/*
			"tins": [
				{
					"number": "XXX567",
					"tinType": "FEDERAL",
					"usage": "usage",
					"effectiveDate": "2024-06-13",
					"expirationDate": "2024-06-13"
				}
			]
*/'
	},
	"soldTo": {
		"address": {
			"streetLines": [
				"'.mb_strimwidth( $pOrder->billing['street_address'], 0, 35 ).'"';
			if( $pOrder->billing['suburb'] ) {
				$requestJson .= ',
				"'.mb_strimwidth( $pOrder->billing['suburb'], 0, 35 ).'"';
			}
			$requestJson .= '
			],
			"city": "'.mb_strimwidth( $pOrder->billing['city'], 0, 35 ).'",
			"stateOrProvinceCode": "'.mb_strimwidth( $pOrder->billing['zone_code'], 0, 2 ).'",
			"postalCode": "'.mb_strimwidth( $pOrder->billing['postcode'], 0, 10 ).'",
			"countryCode": "'.mb_strimwidth( $pOrder->billing['countries_iso_code_2'], 0, 2 ).'"
		},
		"contact": {
			"personName": "'.mb_strimwidth( $pOrder->billing['name'], 0, 70 ).'",
			"emailAddress": "'.mb_strimwidth( $pOrder->customer['email_address'], 0, 80 ).'",
			"phoneNumber": "'.mb_strimwidth( (BitBase::getParameter( $pOrder->billing, 'telephone', BitBase::getParameter( $pOrder->delivery, 'telephone', BitBase::getParameter( $pOrder->customer, 'telephone', $this->getModuleConfigValue( '_PHONE' ) ) ) ) ), 0, 15 ).',
			"companyName": "'.mb_strimwidth( $pOrder->billing['company'], 0, 35 ).'"
		},'.
/*
		"tins": [
			{
				"number": "123567",
				"tinType": "FEDERAL",
				"usage": "usage",
				"effectiveDate": "2000-01-23T04:56:07.000+00:00",
				"expirationDate": "2000-01-23T04:56:07.000+00:00"
			}
		],
		"accountNumber": {
			"value": "'.$this->getModuleConfigValue( '_ACT_NUM' ).'"
		}
*/'
	},
	"recipients": [
	{
		"address": {
			"streetLines": [
				"'.mb_strimwidth( $pOrder->delivery['street_address'], 0, 35 ).'"';
			if( $pOrder->delivery['suburb'] ) {
				$requestJson .= ',
				"'.mb_strimwidth( $pOrder->delivery['suburb'], 0, 35 ).'"';
			}
			$requestJson .= '
			],
			"city": "'.mb_strimwidth( $pOrder->delivery['city'], 0, 35 ).'",
			"stateOrProvinceCode": "'.mb_strimwidth( $pOrder->delivery['zone_code'], 0, 2 ).'",
			"postalCode": "'.mb_strimwidth( $pOrder->delivery['postcode'], 0, 10 ).'",
			"countryCode": "'.mb_strimwidth( $pOrder->delivery['countries_iso_code_2'], 0, 2 ).'"
		},
		"contact": {
			"personName": "'.mb_strimwidth( $pOrder->delivery['name'], 0, 70 ).'",
			"emailAddress": "'.mb_strimwidth( $pOrder->customer['email_address'], 0, 80 ).'",
			"phoneNumber": "'.mb_strimwidth( (BitBase::getParameter( $pOrder->delivery, 'telephone', BitBase::getParameter( $pOrder->billing, 'telephone', BitBase::getParameter( $pOrder->customer, 'telephone', $this->getModuleConfigValue( '_PHONE' ) ) ) ) ), 0, 15 ).',
			"companyName": "'.mb_strimwidth( $pOrder->delivery['company'], 0, 35 ).'"
		},'.
/*
	"tins": [
		{
			"number": "123567",
			"tinType": "FEDERAL",
			"usage": "usage",
			"effectiveDate": "2000-01-23T04:56:07.000+00:00",
			"expirationDate": "2000-01-23T04:56:07.000+00:00"
		}
	],
*/
//				"deliveryInstructions": "Delivery Instructions"
$requestJson .= '
	}
	],'.
/*
		"recipientLocationNumber": "1234567",
*/'
	"pickupType": "USE_SCHEDULED_PICKUP",
	"serviceType": "'.$shipMethod.'",
	"packagingType": "YOUR_PACKAGING",
	"totalWeight": 20.6,
	"origin": {
		"address": {
			"streetLines": [
				"'.mb_strimwidth( $this->getModuleConfigValue( '_ADDRESS_1' ), 0, 35 ).'"';
			if( $this->getModuleConfigValue( '_ADDRESS_2' ) ) {
				$requestJson .= ',
				"'.mb_strimwidth( $this->getModuleConfigValue( '_ADDRESS_2' ), 0, 35 ).'"';
			}
			$requestJson .= '
			],
			"city": "'.mb_strimwidth( $this->getModuleConfigValue( '_CITY' ), 0, 35 ).'",
			"stateOrProvinceCode": "'.mb_strimwidth( $this->getModuleConfigValue( '_STATE' ), 0, 10 ).'",
			"postalCode": "'.mb_strimwidth( $this->getModuleConfigValue( '_POSTAL' ), 0, 10 ).'",
			"countryCode": "'.$this->getModuleConfigValue( '_US' ).'",
			"residential": false
		},
		"contact": {
			"personName": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'STORE_OWNER' ), 0, 75 ).'",
			"emailAddress": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'EMAIL_FROM' ), 0, 80 ).'",
			"phoneNumber": "'.mb_strimwidth( $this->getModuleConfigValue( '_PHONE' ), 0, 15 ).'",
			"companyName": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'STORE_NAME' ), 0, 35 ).'"
		},
		"shippingChargesPayment": {
			"paymentType": "SENDER",
			"payor": {
				"responsibleParty": {
					"address": {
						"streetLines": [
							"'.mb_strimwidth( $this->getModuleConfigValue( '_ADDRESS_1' ), 0, 35 ).'"';
						if( $this->getModuleConfigValue( '_ADDRESS_2' ) ) {
							$requestJson .= ',
							"'.mb_strimwidth( $this->getModuleConfigValue( '_ADDRESS_2' ), 0, 35 ).'"';
						}
						$requestJson .= '
						],
						"city": "'.mb_strimwidth( $this->getModuleConfigValue( '_CITY' ), 0, 35 ).'",
						"stateOrProvinceCode": "'.mb_strimwidth( $this->getModuleConfigValue( '_STATE' ), 0, 10 ).'",
						"postalCode": "'.mb_strimwidth( $this->getModuleConfigValue( '_POSTAL' ), 0, 10 ).'",
						"countryCode": "'.$this->getModuleConfigValue( '_US' ).'",
						"residential": false
					},
					"contact": {
						"personName": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'STORE_OWNER' ), 0, 75 ).'",
						"emailAddress": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'EMAIL_FROM' ), 0, 80 ).'",
						"phoneNumber": "'.mb_strimwidth( $this->getModuleConfigValue( '_PHONE' ), 0, 15 ).'",
						"companyName": "'.mb_strimwidth( $gCommerceSystem->getConfig( 'STORE_NAME' ), 0, 35 ).'"
					},
				}
			}
		},'.
/*
		"shipmentSpecialServices": {
			"specialServiceTypes": [
				"THIRD_PARTY_CONSIGNEE",
				"PROTECTION_FROM_FREEZING"
			],
			"etdDetail": {
				"attributes": [
					"POST_SHIPMENT_UPLOAD_REQUESTED"
				],
				"attachedDocuments": [
					{
						"documentType": "PRO_FORMA_INVOICE",
						"documentReference": "DocumentReference",
						"description": "PRO FORMA INVOICE",
						"documentId": "090927d680038c61"
					}
				],
				"requestedDocumentTypes": [
					"VICS_BILL_OF_LADING",
					"GENERAL_AGENCY_AGREEMENT"
				]
			},
			"returnShipmentDetail": {
				"returnEmailDetail": {
					"merchantPhoneNumber": "19012635656",
					"allowedSpecialService": [
						"SATURDAY_DELIVERY"
					]
				},
				"rma": {
					"reason": "Wrong Size or Color"
				},
				"returnAssociationDetail": {
					"shipDatestamp": "2019-10-01",
					"trackingNumber": "123456789"
				},
				"returnType": "PRINT_RETURN_LABEL"
			},
			"deliveryOnInvoiceAcceptanceDetail": {
				"recipient": {
					"address": {
						"streetLines": [
							"23, RUE JOSEPH-DE MA",
							"Suite 302"
						],
						"city": "Beverly Hills",
						"stateOrProvinceCode": "CA",
						"postalCode": "90210",
						"countryCode": "US",
						"residential": false
					},
					"contact": {
						"personName": "John Taylor",
						"emailAddress": "sample@company.com",
						"phoneExtension": "000",
						"phoneNumber": "1234567890",
						"companyName": "Fedex"
					},
					"tins": [
						{
							"number": "123567",
							"tinType": "FEDERAL",
							"usage": "usage",
							"effectiveDate": "2000-01-23T04:56:07.000+00:00",
							"expirationDate": "2000-01-23T04:56:07.000+00:00"
						}
					],
					"deliveryInstructions": "Delivery Instructions"
				}
			},
			"internationalTrafficInArmsRegulationsDetail": {
				"licenseOrExemptionNumber": "9871234"
			},
			"pendingShipmentDetail": {
				"pendingShipmentType": "EMAIL",
				"processingOptions": {
					"options": [
						"ALLOW_MODIFICATIONS"
					]
				},
				"recommendedDocumentSpecification": {
					"types": [
						"ANTIQUE_STATEMENT_EUROPEAN_UNION",
						"ANTIQUE_STATEMENT_UNITED_STATES"
					]
				},
				"emailLabelDetail": {
					"recipients": [
						{
							"emailAddress": "nnnnneena@fedex.com",
							"optionsRequested": {
								"options": [
									"PRODUCE_PAPERLESS_SHIPPING_FORMAT",
									"SUPPRESS_ACCESS_EMAILS"
								]
							},
							"role": "SHIPMENT_COMPLETOR",
							"locale": "en_US"
						}
					],
					"message": "your optional message"
				},
				"attachedDocuments": [
					{
						"documentType": "PRO_FORMA_INVOICE",
						"documentReference": "DocumentReference",
						"description": "PRO FORMA INVOICE",
						"documentId": "090927d680038c61"
					}
				],
				"expirationTimeStamp": "2020-01-01"
			},
			"holdAtLocationDetail": {
				"locationId": "YBZA",
				"locationContactAndAddress": {
					"address": {
						"streetLines": [
							"10 FedEx Parkway",
							"Suite 302"
						],
						"city": "Beverly Hills",
						"stateOrProvinceCode": "CA",
						"postalCode": "38127",
						"countryCode": "US",
						"residential": false
					},
					"contact": {
						"personName": "person name",
						"emailAddress": "email address",
						"phoneNumber": "phone number",
						"phoneExtension": "phone extension",
						"companyName": "company name",
						"faxNumber": "fax number"
					}
				},
				"locationType": "FEDEX_ONSITE"
			},
			"shipmentCODDetail": {
				"addTransportationChargesDetail": {
					"rateType": "ACCOUNT",
					"rateLevelType": "BUNDLED_RATE",
					"chargeLevelType": "CURRENT_PACKAGE",
					"chargeType": "COD_SURCHARGE"
				},
				"codRecipient": {
					"address": {
						"streetLines": [
							"10 FedEx Parkway",
							"Suite 302"
						],
						"city": "Beverly Hills",
						"stateOrProvinceCode": "CA",
						"postalCode": "90210",
						"countryCode": "US",
						"residential": false
					},
					"contact": {
						"personName": "John Taylor",
						"emailAddress": "sample@company.com",
						"phoneExtension": "000",
						"phoneNumber": "XXXX345671",
						"companyName": "Fedex"
					},
					"accountNumber": {
						"value": "Your account number"
					},
					"tins": [
						{
							"number": "123567",
							"tinType": "FEDERAL",
							"usage": "usage",
							"effectiveDate": "2000-01-23T04:56:07.000+00:00",
							"expirationDate": "2000-01-23T04:56:07.000+00:00"
						}
					]
				},
				"remitToName": "remitToName",
				"codCollectionType": "CASH",
				"financialInstitutionContactAndAddress": {
					"address": {
						"streetLines": [
							"10 FedEx Parkway",
							"Suite 302"
						],
						"city": "Beverly Hills",
						"stateOrProvinceCode": "CA",
						"postalCode": "38127",
						"countryCode": "US",
						"residential": false
					},
					"contact": {
						"personName": "person name",
						"emailAddress": "email address",
						"phoneNumber": "phone number",
						"phoneExtension": "phone extension",
						"companyName": "company name",
						"faxNumber": "fax number"
					}
				},
				"codCollectionAmount": {
					"amount": 12.45,
					"currency": "USD"
				},
				"returnReferenceIndicatorType": "INVOICE",
				"shipmentCodAmount": {
					"amount": 12.45,
					"currency": "USD"
				}
			},
			"shipmentDryIceDetail": {
				"totalWeight": {
					"units": "LB",
					"value": 10
				},
				"packageCount": 12
			},
			"internationalControlledExportDetail": {
				"licenseOrPermitExpirationDate": "2019-12-03",
				"licenseOrPermitNumber": "11",
				"entryNumber": "125",
				"foreignTradeZoneCode": "US",
				"type": "WAREHOUSE_WITHDRAWAL"
			},
			"homeDeliveryPremiumDetail": {
				"phoneNumber": {
					"areaCode": "901",
					"localNumber": "3575012",
					"extension": "200",
					"personalIdentificationNumber": "98712345"
				},
				"deliveryDate": "2019-06-26",
				"homedeliveryPremiumType": "APPOINTMENT"
			}
		},
		"emailNotificationDetail": {
			"aggregationType": "PER_PACKAGE",
			"emailNotificationRecipients": [
				{
					"name": "Joe Smith",
					"emailNotificationRecipientType": "SHIPPER",
					"emailAddress": "jsmith3@aol.com",
					"notificationFormatType": "TEXT",
					"notificationType": "EMAIL",
					"locale": "en_US",
					"notificationEventType": [
						"ON_PICKUP_DRIVER_ARRIVED",
						"ON_SHIPMENT"
					]
				}
			],
			"personalMessage": "your personal message here"
		},
		"expressFreightDetail": {
			"bookingConfirmationNumber": "123456789812",
			"shippersLoadAndCount": 123,
			"packingListEnclosed": true
		},
		"variableHandlingChargeDetail": {
			"rateType": "PREFERRED_CURRENCY",
			"percentValue": 12.45,
			"rateLevelType": "INDIVIDUAL_PACKAGE_RATE",
			"fixedValue": {
				"amount": 24.45,
				"currency": "USD"
			},
			"rateElementBasis": "NET_CHARGE_EXCLUDING_TAXES"
		},
		"customsClearanceDetail": {
			"regulatoryControls": [
				"NOT_IN_FREE_CIRCULATION",
				"USMCA"
			],
			"brokers": [
				{
					"broker": {
						"address": {
							"streetLines": [
								"10 FedEx Parkway",
								"Suite 302"
							],
							"city": "Beverly Hills",
							"stateOrProvinceCode": "CA",
							"postalCode": "90210",
							"countryCode": "US",
							"residential": false
						},
						"contact": {
							"personName": "John Taylor",
							"emailAddress": "sample@company.com",
							"phoneNumber": "1234567890",
							"phoneExtension": 91,
							"companyName": "Fedex",
							"faxNumber": 1234567
						},
						"accountNumber": {
							"value": "Your account number"
						},
						"tins": [
							{
								"number": "number",
								"tinType": "FEDERAL",
								"usage": "usage",
								"effectiveDate": "2000-01-23T04:56:07.000+00:00",
								"expirationDate": "2000-01-23T04:56:07.000+00:00"
							}
						],
						"deliveryInstructions": "deliveryInstructions"
					},
					"type": "IMPORT"
				}
			],
			"commercialInvoice": {
				"originatorName": "originator Name",
				"comments": [
					"optional comments for the commercial invoice"
				],
				"customerReferences": [
					{
						"customerReferenceType": "DEPARTMENT_NUMBER",
						"value": "3686"
					}
				],
				"taxesOrMiscellaneousCharge": {
					"amount": 12.45,
					"currency": "USD"
				},
				"taxesOrMiscellaneousChargeType": "COMMISSIONS",
				"freightCharge": {
					"amount": 12.45,
					"currency": "USD"
				},
				"packingCosts": {
					"amount": 12.45,
					"currency": "USD"
				},
				"handlingCosts": {
					"amount": 12.45,
					"currency": "USD"
				},
				"declarationStatement": "declarationStatement",
				"termsOfSale": "FCA",
				"specialInstructions": "specialInstructions\"",
				"shipmentPurpose": "REPAIR_AND_RETURN",
				"emailNotificationDetail": {
					"emailAddress": "neena@fedex.com",
					"type": "EMAILED",
					"recipientType": "SHIPPER"
				}
			},
			"freightOnValue": "OWN_RISK",
			"dutiesPayment": {
				"payor": {
					"responsibleParty": {
						"address": {
							"streetLines": [
								"10 FedEx Parkway",
								"Suite 302"
							],
							"city": "Beverly Hills",
							"stateOrProvinceCode": "CA",
							"postalCode": "38127",
							"countryCode": "US",
							"residential": false
						},
						"contact": {
							"personName": "John Taylor",
							"emailAddress": "sample@company.com",
							"phoneNumber": "1234567890",
							"phoneExtension": "phone extension",
							"companyName": "Fedex",
							"faxNumber": "fax number"
						},
						"accountNumber": {
							"value": "Your account number"
						},
						"tins": [
							{
								"number": "number",
								"tinType": "FEDERAL",
								"usage": "usage",
								"effectiveDate": "2024-06-13",
								"expirationDate": "2024-06-13"
							},
							{
								"number": "number",
								"tinType": "FEDERAL",
								"usage": "usage",
								"effectiveDate": "2024-06-13",
								"expirationDate": "2024-06-13"
							}
						]
					}
				},
				"billingDetails": {
					"billingCode": "billingCode",
					"billingType": "billingType",
					"aliasId": "aliasId",
					"accountNickname": "accountNickname",
					"accountNumber": "Your account number",
					"accountNumberCountryCode": "US"
				},
				"paymentType": "SENDER"
			},
			"commodities": [
				{
					"unitPrice": {
						"amount": 12.45,
						"currency": "USD"
					},
					"additionalMeasures": [
						{
							"quantity": 12.45,
							"units": "KG"
						}
					],
					"numberOfPieces": 12,
					"quantity": 125,
					"quantityUnits": "Ea",
					"customsValue": {
						"amount": "1556.25",
						"currency": "USD"
					},
					"countryOfManufacture": "US",
					"cIMarksAndNumbers": "87123",
					"harmonizedCode": "0613",
					"description": "description",
					"name": "non-threaded rivets",
					"weight": {
						"units": "KG",
						"value": 68
					},
					"exportLicenseNumber": "26456",
					"exportLicenseExpirationDate": "2024-08-07T00:15:25Z",
					"partNumber": "167",
					"purpose": "BUSINESS",
					"usmcaDetail": {
						"originCriterion": "A"
					}
				}
			],
			"isDocumentOnly": false,
			"recipientCustomsId": {
				"type": "PASSPORT",
				"value": "123"
			},
			"customsOption": {
				"description": "Description",
				"type": "COURTESY_RETURN_LABEL"
			},
			"importerOfRecord": {
				"address": {
					"streetLines": [
						"10 FedEx Parkway",
						"Suite 302"
					],
					"city": "Beverly Hills",
					"stateOrProvinceCode": "CA",
					"postalCode": "90210",
					"countryCode": "US",
					"residential": false
				},
				"contact": {
					"personName": "John Taylor",
					"emailAddress": "sample@company.com",
					"phoneExtension": "000",
					"phoneNumber": "XXXX345671",
					"companyName": "Fedex"
				},
				"accountNumber": {
					"value": "Your account number"
				},
				"tins": [
					{
						"number": "123567",
						"tinType": "FEDERAL",
						"usage": "usage",
						"effectiveDate": "2000-01-23T04:56:07.000+00:00",
						"expirationDate": "2000-01-23T04:56:07.000+00:00"
					}
				]
			},
			"generatedDocumentLocale": "en_US",
			"exportDetail": {
				"destinationControlDetail": {
					"endUser": "dest country user",
					"statementTypes": "DEPARTMENT_OF_COMMERCE",
					"destinationCountries": [
						"USA",
						"India"
					]
				},
				"b13AFilingOption": "NOT_REQUIRED",
				"exportComplianceStatement": "12345678901234567",
				"permitNumber": "12345"
			},
			"totalCustomsValue": {
				"amount": 12.45,
				"currency": "USD"
			},
			"partiesToTransactionAreRelated": true,
			"declarationStatementDetail": {
				"usmcaLowValueStatementDetail": {
					"countryOfOriginLowValueDocumentRequested": true,
					"customsRole": "EXPORTER"
				}
			},
			"insuranceCharge": {
				"amount": 12.45,
				"currency": "USD"
			}
		},
		"smartPostInfoDetail": {
			"ancillaryEndorsement": "RETURN_SERVICE",
			"hubId": "5015",
			"indicia": "PRESORTED_STANDARD",
			"specialServices": "USPS_DELIVERY_CONFIRMATION"
		},
		"blockInsightVisibility": true,
		"labelSpecification": {
			"labelFormatType": "COMMON2D",
			"labelOrder": "SHIPPING_LABEL_FIRST",
			"customerSpecifiedDetail": {
				"maskedData": [
					"PACKAGE_SEQUENCE_AND_COUNT",
					"TOTAL_WEIGHT"
				],
				"regulatoryLabels": [
					{
						"generationOptions": "CONTENT_ON_SHIPPING_LABEL_ONLY",
						"type": "ALCOHOL_SHIPMENT_LABEL"
					}
				],
				"additionalLabels": [
					{
						"type": "MANIFEST",
						"count": 1
					}
				],
				"docTabContent": {
					"docTabContentType": "BARCODED",
					"zone001": {
						"docTabZoneSpecifications": [
							{
								"zoneNumber": 0,
								"header": "string",
								"dataField": "string",
								"literalValue": "string",
								"justification": "RIGHT"
							}
						]
					},
					"barcoded": {
						"symbology": "UCC128",
						"specification": {
							"zoneNumber": 0,
							"header": "string",
							"dataField": "string",
							"literalValue": "string",
							"justification": "RIGHT"
						}
					}
				}
			},
			"printedLabelOrigin": {
				"address": {
					"streetLines": [
						"10 FedEx Parkway",
						"Suite 302"
					],
					"city": "Beverly Hills",
					"stateOrProvinceCode": "CA",
					"postalCode": "38127",
					"countryCode": "US",
					"residential": false
				},
				"contact": {
					"personName": "person name",
					"emailAddress": "email address",
					"phoneNumber": "phone number",
					"phoneExtension": "phone extension",
					"companyName": "company name",
					"faxNumber": "fax number"
				}
			},
			"labelStockType": "PAPER_7X475",
			"labelRotation": "UPSIDE_DOWN",
			"imageType": "PDF",
			"labelPrintingOrientation": "TOP_EDGE_OF_TEXT_FIRST",
			"returnedDispositionDetail": "RETURNED",
			"resolution": 300
		},
		"shippingDocumentSpecification": {
			"generalAgencyAgreementDetail": {
				"documentFormat": {
					"provideInstructions": true,
					"optionsRequested": {
						"options": [
							"SUPPRESS_ADDITIONAL_LANGUAGES",
							"SHIPPING_LABEL_LAST"
						]
					},
					"stockType": "PAPER_LETTER",
					"dispositions": [
						{
							"eMailDetail": {
								"eMailRecipients": [
									{
										"emailAddress": "email@fedex.com",
										"recipientType": "THIRD_PARTY"
									}
								],
								"locale": "en_US",
								"grouping": "NONE"
							},
							"dispositionType": "CONFIRMED"
						}
					],
					"locale": "en_US",
					"docType": "PDF"
				}
			},
			"returnInstructionsDetail": {
				"customText": "This is additional text printed on Return instr",
				"documentFormat": {
					"provideInstructions": true,
					"optionsRequested": {
						"options": [
							"SUPPRESS_ADDITIONAL_LANGUAGES",
							"SHIPPING_LABEL_LAST"
						]
					},
					"stockType": "PAPER_LETTER",
					"dispositions": [
						{
							"eMailDetail": {
								"eMailRecipients": [
									{
										"emailAddress": "email@fedex.com",
										"recipientType": "THIRD_PARTY"
									}
								],
								"locale": "en_US",
								"grouping": "NONE"
							},
							"dispositionType": "CONFIRMED"
						}
					],
					"locale": "en_US\"",
					"docType": "PNG"
				}
			},
			"op900Detail": {
				"customerImageUsages": [
					{
						"id": "IMAGE_5",
						"type": "SIGNATURE",
						"providedImageType": "SIGNATURE"
					}
				],
				"signatureName": "Signature Name",
				"documentFormat": {
					"provideInstructions": true,
					"optionsRequested": {
						"options": [
							"SUPPRESS_ADDITIONAL_LANGUAGES",
							"SHIPPING_LABEL_LAST"
						]
					},
					"stockType": "PAPER_LETTER",
					"dispositions": [
						{
							"eMailDetail": {
								"eMailRecipients": [
									{
										"emailAddress": "email@fedex.com",
										"recipientType": "THIRD_PARTY"
									}
								],
								"locale": "en_US",
								"grouping": "NONE"
							},
							"dispositionType": "CONFIRMED"
						}
					],
					"locale": "en_US",
					"docType": "PDF"
				}
			},
			"usmcaCertificationOfOriginDetail": {
				"customerImageUsages": [
					{
						"id": "IMAGE_5",
						"type": "SIGNATURE",
						"providedImageType": "SIGNATURE"
					}
				],
				"documentFormat": {
					"provideInstructions": true,
					"optionsRequested": {
						"options": [
							"SUPPRESS_ADDITIONAL_LANGUAGES",
							"SHIPPING_LABEL_LAST"
						]
					},
					"stockType": "PAPER_LETTER",
					"dispositions": [
						{
							"eMailDetail": {
								"eMailRecipients": [
									{
										"emailAddress": "email@fedex.com",
										"recipientType": "THIRD_PARTY"
									}
								],
								"locale": "en_US",
								"grouping": "NONE"
							},
							"dispositionType": "CONFIRMED"
						}
					],
					"locale": "en_US",
					"docType": "PDF"
				},
				"certifierSpecification": "EXPORTER",
				"importerSpecification": "UNKNOWN",
				"producerSpecification": "SAME_AS_EXPORTER",
				"producer": {
					"address": {
						"streetLines": [
							"10 FedEx Parkway",
							"Suite 302"
						],
						"city": "Beverly Hills",
						"stateOrProvinceCode": "CA",
						"postalCode": "90210",
						"countryCode": "US",
						"residential": false
					},
					"contact": {
						"personName": "John Taylor",
						"emailAddress": "sample@company.com",
						"phoneExtension": "000",
						"phoneNumber": "XXXX345671",
						"companyName": "Fedex"
					},
					"accountNumber": {
						"value": "Your account number"
					},
					"tins": [
						{
							"number": "123567",
							"tinType": "FEDERAL",
							"usage": "usage",
							"effectiveDate": "2000-01-23T04:56:07.000+00:00",
							"expirationDate": "2000-01-23T04:56:07.000+00:00"
						}
					]
				},
				"blanketPeriod": {
					"begins": "22-01-2020",
					"ends": "2-01-2020"
				},
				"certifierJobTitle": "Manager"
			},
			"usmcaCommercialInvoiceCertificationOfOriginDetail": {
				"customerImageUsages": [
					{
						"id": "IMAGE_5",
						"type": "SIGNATURE",
						"providedImageType": "SIGNATURE"
					}
				],
				"documentFormat": {
					"provideInstructions": true,
					"optionsRequested": {
						"options": [
							"SUPPRESS_ADDITIONAL_LANGUAGES",
							"SHIPPING_LABEL_LAST"
						]
					},
					"stockType": "PAPER_LETTER",
					"dispositions": [
						{
							"eMailDetail": {
								"eMailRecipients": [
									{
										"emailAddress": "email@fedex.com",
										"recipientType": "THIRD_PARTY"
									}
								],
								"locale": "en_US",
								"grouping": "NONE"
							},
							"dispositionType": "CONFIRMED"
						}
					],
					"locale": "en_US",
					"docType": "PDF"
				},
				"certifierSpecification": "EXPORTER",
				"importerSpecification": "UNKNOWN",
				"producerSpecification": "SAME_AS_EXPORTER",
				"producer": {
					"address": {
						"streetLines": [
							"10 FedEx Parkway",
							"Suite 302"
						],
						"city": "Beverly Hills",
						"stateOrProvinceCode": "CA",
						"postalCode": "90210",
						"countryCode": "US",
						"residential": false
					},
					"contact": {
						"personName": "John Taylor",
						"emailAddress": "sample@company.com",
						"phoneExtension": "000",
						"phoneNumber": "XXXX345671",
						"companyName": "Fedex"
					},
					"accountNumber": {
						"value": "Your account number"
					},
					"tins": [
						{
							"number": "123567",
							"tinType": "FEDERAL",
							"usage": "usage",
							"effectiveDate": "2000-01-23T04:56:07.000+00:00",
							"expirationDate": "2000-01-23T04:56:07.000+00:00"
						}
					]
				},
				"certifierJobTitle": "Manager"
			},
			"shippingDocumentTypes": [
				"RETURN_INSTRUCTIONS"
			],
			"certificateOfOrigin": {
				"customerImageUsages": [
					{
						"id": "IMAGE_5",
						"type": "SIGNATURE",
						"providedImageType": "SIGNATURE"
					}
				],
				"documentFormat": {
					"provideInstructions": true,
					"optionsRequested": {
						"options": [
							"SUPPRESS_ADDITIONAL_LANGUAGES",
							"SHIPPING_LABEL_LAST"
						]
					},
					"stockType": "PAPER_LETTER",
					"dispositions": [
						{
							"eMailDetail": {
								"eMailRecipients": [
									{
										"emailAddress": "email@fedex.com",
										"recipientType": "THIRD_PARTY"
									}
								],
								"locale": "en_US",
								"grouping": "NONE"
							},
							"dispositionType": "CONFIRMED"
						}
					],
					"locale": "en_US",
					"docType": "PDF"
				}
			},
			"commercialInvoiceDetail": {
				"customerImageUsages": [
					{
						"id": "IMAGE_5",
						"type": "SIGNATURE",
						"providedImageType": "SIGNATURE"
					}
				],
				"documentFormat": {
					"provideInstructions": true,
					"optionsRequested": {
						"options": [
							"SUPPRESS_ADDITIONAL_LANGUAGES",
							"SHIPPING_LABEL_LAST"
						]
					},
					"stockType": "PAPER_LETTER",
					"dispositions": [
						{
							"eMailDetail": {
								"eMailRecipients": [
									{
										"emailAddress": "email@fedex.com",
										"recipientType": "THIRD_PARTY"
									}
								],
								"locale": "en_US",
								"grouping": "NONE"
							},
							"dispositionType": "CONFIRMED"
						}
					],
					"locale": "en_US",
					"docType": "PDF"
				}
			}
		},
		"rateRequestType": [
			"LIST",
			"PREFERRED"
		],
		"preferredCurrency": "USD",
		"totalPackageCount": 25,
		"masterTrackingId": {
			"formId": "0201",
			"trackingIdType": "EXPRESS",
			"uspsApplicationId": "92",
			"trackingNumber": "49092000070120032835"
		},
		"requestedPackageLineItems": [
			{
				"sequenceNumber": "1",
				"subPackagingType": "BUCKET",
				"customerReferences": [
					{
						"customerReferenceType": "INVOICE_NUMBER",
						"value": "3686"
					}
				],
				"declaredValue": {
					"amount": 12.45,
					"currency": "USD"
				},
				"weight": {
					"units": "KG",
					"value": 68
				},
				"dimensions": {
					"length": 100,
					"width": 50,
					"height": 30,
					"units": "CM"
				},
				"groupPackageCount": 2,
				"itemDescriptionForClearance": "description",
				"contentRecord": [
					{
						"itemNumber": "2876",
						"receivedQuantity": 256,
						"description": "Description",
						"partNumber": "456"
					}
				],
				"itemDescription": "item description for the package",
				"variableHandlingChargeDetail": {
					"rateType": "PREFERRED_CURRENCY",
					"percentValue": 12.45,
					"rateLevelType": "INDIVIDUAL_PACKAGE_RATE",
					"fixedValue": {
						"amount": 24.45,
						"currency": "USD"
					},
					"rateElementBasis": "NET_CHARGE_EXCLUDING_TAXES"
				},
				"packageSpecialServices": {
					"specialServiceTypes": [
						"ALCOHOL",
						"NON_STANDARD_CONTAINER",
						"DANGEROUS_GOODS",
						"SIGNATURE_OPTION",
						"PRIORITY_ALERT"
					],
					"signatureOptionType": "ADULT",
					"priorityAlertDetail": {
						"enhancementTypes": [
							"PRIORITY_ALERT_PLUS"
						],
						"content": [
							"string"
						]
					},
					"signatureOptionDetail": {
						"signatureReleaseNumber": "23456"
					},
					"alcoholDetail": {
						"alcoholRecipientType": "LICENSEE",
						"shipperAgreementType": "Retailer"
					},
					"dangerousGoodsDetail": {
						"cargoAircraftOnly": false,
						"accessibility": "INACCESSIBLE",
						"options": [
							"LIMITED_QUANTITIES_COMMODITIES",
							"ORM_D"
						]
					},
					"packageCODDetail": {
						"codCollectionAmount": {
							"amount": 12.45,
							"currency": "USD"
						}
					},
					"pieceCountVerificationBoxCount": 0,
					"batteryDetails": [
						{
							"batteryPackingType": "CONTAINED_IN_EQUIPMENT",
							"batteryRegulatoryType": "IATA_SECTION_II",
							"batteryMaterialType": "LITHIUM_METAL"
						}
					],
					"dryIceWeight": {
						"units": "KG",
						"value": 68
					},
					"standaloneBatteryDetails": [
						{
							"batteryMaterialType": "LITHIUM_METAL"
						}
					]
				}
			}
		]
	},
*/'
	"labelResponseOptions": "URL_ONLY",
	"accountNumber": {
		"value": "'.$this->getModuleConfigValue( '_ACT_NUM' ).'"
	},
	"shipAction": "CONFIRM",
	"processingOptionType": "ALLOW_ASYNCHRONOUS",
	"oneLabelAtATime": true
}
';
		eb( $requestJson, $pShipmentHash, $pOrder->info, $pOrder->billing, $pOrder->delivery, $pOrder->customer );
	}
	// }}} ++++ createShipment ++++

	// {{{	++++++++ config ++++++++
	protected function config() {
		$i = 3;
		return array_merge( parent::config(), array( 

			$this->getModuleKeyTrunk().'_ACT_NUM' => array(
				'configuration_title' => 'FedEx Account Number',
				'configuration_description' => 'Enter FedEx Account Number',
				'sort_order' => $i++,
			),
			 $this->getModuleKeyTrunk().'_API_KEY' => array(
				'configuration_title' => 'FedEx API Key',
				'configuration_description' => 'Enter FedEx API Key',
				'sort_order' => $i++,
				'configuration_value' => '',
			),
			$this->getModuleKeyTrunk().'_SECRET_KEY' => array(
				'configuration_title' => 'FedEx Secret Key',
				'configuration_description' => 'Enter FedEx Secret Key',
				'sort_order' => $i++,
				'configuration_value' => '',
			),
			$this->getModuleKeyTrunk().'_SHIP_FROM_RESIDENCE' => array(
				'configuration_title' => 'Ship From address is residential',
				'configuration_description' => 'Is pickup address residential? (Only applies for Pickup type = 1)',
				'sort_order' => $i++,
				'configuration_value' => 'false',
				'set_function' => 'zen_cfg_select_option(array(\'true\', \'false\'), ',
			),
			$this->getModuleKeyTrunk().'_SHIP_TO_RESIDENCE' => array(
				'configuration_title' => 'Ship To address is residential',
				'configuration_description' => 'Is ship to address residential?',
				'sort_order' => $i++,
				'configuration_value' => 'false',
				'set_function' => 'zen_cfg_select_option(array(\'true\', \'false\', \'false if Company set in ship-to address, true otherwise\'), ',
			),
			$this->getModuleKeyTrunk().'_PICKUP' => array(
				'configuration_title' => 'Pickup type',
				'configuration_description' => 'Pickup type (1 = Use scheduled pickup, 2 = Contact FedEx to schedule, 3 = Dropoff at FedEx location)?',
				'sort_order' => $i++,
				'configuration_value' => '1',
				'set_function' => 'zen_cfg_select_option(array(\'1\',\'2\',\'3\'),',
			),
			$this->getModuleKeyTrunk().'_WEIGHT' => array(
				'configuration_title' => 'Weight Units',
				'configuration_value' => 'LB',
				'configuration_description' => 'Weight Units:',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('LB','KG'),",
			),
			$this->getModuleKeyTrunk().'_TRANSIT_TIME' => array(
				'configuration_title' => 'Show Estimated Transit Time',
				'configuration_description' => 'Display the transit time for ground methods?',
				'sort_order' => $i++,
				'configuration_value' => 'false',
				'set_function' => 'zen_cfg_select_option(array(\'true\', \'false\'), ',
			),
			$this->getModuleKeyTrunk().'_ZONE' => array(
				'configuration_title' => 'Shipping Zone',
				'configuration_description' => 'If a zone is selected, only enable this shipping method for that zone.',
				'sort_order' => $i++,
				'configuration_value' => '0',
				'use_function' => 'zen_get_zone_class_title',
				'set_function' => 'zen_cfg_pull_down_zone_classes(',
			),
			$this->getModuleKeyTrunk().'_ADDRESS_1' => array(
				'configuration_title' => 'First line of street address',
				'configuration_description' => 'Enter the first line of your ship-from street address, required',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_ADDRESS_2' => array(
				'configuration_title' => 'Second line of street address',
				'configuration_description' => 'Enter the second line of your ship-from street address, leave blank if you do not need to specify a second line',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_CITY' => array(
				'configuration_title' => 'City name',
				'configuration_description' => 'Enter the city name for the ship-from street address, required',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_STATE' => array(
				'configuration_title' => 'State or Province name',
				'configuration_description' => 'Enter the 2 letter state or province name for the ship-from street address, required for Canada and US',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_POSTAL' => array(
				'configuration_title' => 'Postal code',
				'configuration_description' => 'Enter the postal code for the ship-from street address, required',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_PHONE' => array(
				'configuration_title' => 'Phone number',
				'configuration_description' => 'Enter a contact phone number for your company, required',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_FREE_SHIPPING' => array(
				'configuration_title' => 'Enable for Always Free Shipping',
				'configuration_value' => 'false',
				'configuration_description' => 'Should this module be enabled even when all items in the cart are marked as ALWAYS FREE SHIPPING?',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_DROPOFF' => array(
				'configuration_title' => 'Drop off type',
				'configuration_value' => '1',
				'configuration_description' => 'Dropoff type (1 = Regular pickup, 2 = request courier, 3 = drop box, 4 = drop at BSC, 5 = drop at station)?',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('1','2','3','4','5'),",
			),
			$this->getModuleKeyTrunk().'_EXPRESS_SAVER' => array(
				'configuration_title' => 'Enable Express Saver',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Express Saver',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_STANDARD_OVERNIGHT' => array(
				'configuration_title' => 'Enable Standard Overnight',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Express Standard Overnight',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_FIRST_OVERNIGHT' => array(
				'configuration_title' => 'Enable First Overnight',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Express First Overnight',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_PRIORITY_OVERNIGHT' => array(
				'configuration_title' => 'Enable Priority Overnight',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Express Priority Overnight',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_2DAY' => array(
				'configuration_title' => 'Enable 2 Day',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Express 2 Day',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_GROUND' => array(
				'configuration_title' => 'Enable Ground',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Ground',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_INTERNATIONAL_PRIORITY' => array(
				'configuration_title' => 'Enable International Priority',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Express International Priority',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_INTERNATIONAL_ECONOMY' => array(
				'configuration_title' => 'Enable International Economy',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Express International Economy',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			 $this->getModuleKeyTrunk().'_INTERNATIONAL_CONNECT_PLUS' => array(
				'configuration_title' => 'Enable International Connect Plus',
				'configuration_description' => 'Enable FedEx Express International Connect Plus',
				'sort_order' => $i++,
				'configuration_value' => 'true',
				'set_function' => 'zen_cfg_select_option(array(\'true\', \'false\'), ',
			),
			$this->getModuleKeyTrunk().'_INTERNATIONAL_GROUND' => array(
				'configuration_title' => 'Enable International Ground',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx International Ground',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_FREIGHT' => array(
				'configuration_title' => 'Enable Freight',
				'configuration_value' => 'true',
				'configuration_description' => 'Enable FedEx Freight',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_SATURDAY' => array(
				'configuration_title' => 'Enable Saturday Delivery',
				'configuration_value' => 'false',
				'configuration_description' => 'Enable Saturday Delivery',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
			$this->getModuleKeyTrunk().'_INSURE' => array(
				'configuration_title' => 'Domestic Ground Handling Fee',
				'configuration_value' => '120',
				'configuration_description' => 'Insure packages when subtotal is greater than or equal to (set to -1 to disable):',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_HANDLING_FEE' => array(
				'configuration_title' => 'Domestic Ground Handling Fee',
				'configuration_description' => 'Add a domestic handling fee or leave blank (example: 15 or 15%)',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_EXPRESS_HANDLING_FEE' => array(
				'configuration_title' => 'Domestic Express Handling Fee',
				'configuration_description' => 'Add a domestic handling fee or leave blank (example: 15 or 15%)',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_INT_HANDLING_FEE' => array(
				'configuration_title' => 'International Ground Handling Fee',
				'configuration_description' => 'Add an international handling fee or leave blank (example: 15 or 15%)',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_INT_EXPRESS_HANDLING_FEE' => array(
				'configuration_title' => 'International Express Handling Fee',
				'configuration_description' => 'Add an international handling fee or leave blank (example: 15 or 15%)',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_RATES' => array(
				'configuration_title' => 'FedEx Rates',
				'configuration_value' => 'LIST',
				'configuration_description' => 'FedEx Rates',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('LIST', 'ACCOUNT'),",
			),
			$this->getModuleKeyTrunk().'_SIGNATURE_OPTION' => array(
				'configuration_title' => 'Signature Option',
				'configuration_value' => '-1',
				'configuration_description' => 'Require a signature on orders greater than or equal to (set to -1 to disable):',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_READY_TO_SHIP' => array(
				'configuration_title' => 'Enable Ready to Ship',
				'configuration_value' => 'false',
				'configuration_description' => 'Enable using products_ready_to_ship field (requires Numinix Product Fields optional dimensions fields) to identify products which ship separately?',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'),",
			),
		) );
	}
	// }}} ++++ config ++++

      protected function debugLog($message, $include_spacer = false)
      {
//vd( $message );
         if ($this->debug === true) {
            $spacer = ($include_spacer === false) ? '' : "------------------------------------------\n";
            error_log($spacer . date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, 3, $this->logfile);
         }
      }
}

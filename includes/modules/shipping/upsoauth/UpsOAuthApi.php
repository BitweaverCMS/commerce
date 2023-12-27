<?php
// -----
// API/Rate-generation interfaces that support shipping modules that use the
// UPS RESTful API with OAuth authentication.
//
// Last updated: v1.2.1
//
// Copyright 2023, Vinos de Frutas Tropicales
//

class UpsOAuthApi extends CommerceBase
{
	// -----
	// Constants that define the test and production endpoints for the API requests.
	//
	const ENDPOINT_TEST = 'https://wwwcie.ups.com/';
	const ENDPOINT_PRODUCTION = 'https://onlinetools.ups.com/';

	// -----
	// Constants used when making the various API requests to UPS; appended to the currently
	// configured endpoint.
	//
	const API_OAUTH_TOKEN = 'security/v1/oauth/token';
	const API_RATING = 'api/rating/v1/Shop';	//- Gives *all* UPS shipping methods for a given From->To address.

	protected
		$endpoint,

		$currencyCode,

		$packagingTypes,
		$pickupMethods,
		$serviceCodes,

		$debug,
		$logfile;

	// -----
	// This value indicates the API version, which is not the same as the version of the
	// shipping-module itself.  It'll be updated if any new methods are introduced or additional
	// parameters added to existing methods.
	//
	private
		$upsOAuthApiVersion = '1.2.0';

	// -----
	// Class constructor:
	//
	// - $endpoint_type: Identifies whether the test ('Test') or production (anything else) URL is to be used for API requests.
	// - $debug: Indicates whether ((bool)true) or not the class debug is to be enabled.
	// - $debug_logfile: Identifies the filename (including path) to which debug output is to be written.
	//
	public function __construct($endpoint_type, $debug, $debug_logfile)
	{
		$this->endpoint = ($endpoint_type === 'Test') ? self::ENDPOINT_TEST : self::ENDPOINT_PRODUCTION;

		$this->debug = ($debug === true);
		$this->logfile = $debug_logfile;
		$this->currencyCode = DEFAULT_CURRENCY;

		$this->initializeValueMappings();
	}

	// -----
	// This method returns the *private* API version, which indicates the version
	// associated with class methods *in this base class*.  It cannot be overridden
	// by a class extension, but the value returned can be used by class extensions
	// to "do the right thing" if/when another method or method-parameter is introduced.
	//
	final public function getUpsOAuthApiVersion()
	{
		return $this->upsOAuthApiVersion;
	}

	protected function initializeValueMappings()
	{
		// -----
		// UPS "Pickup Methods", mapped from the MODULE_SHIPPING_UPSOAUTH_PICKUP_METHOD configuration
		// setting.
		//
		$this->pickupMethods = [
			'Daily Pickup' => '01',
			'Customer Counter' => '03',
			'One Time Pickup' => '06',
			'On Call Air Pickup' => '07',
			'Letter Center' => '19',
			'Air Service Center' => '20'
		];

		// -----
		// UPS "Packaging Types", mapped from the MODULE_SHIPPING_UPSOAUTH_PACKAGE_TYPE configuration
		// setting.
		//
		$this->packagingTypes = [
			'Unknown' => '00',
			'UPS Letter' => '01',
			'Customer Package' => '02',
			'UPS Tube' => '03',
			'UPS Pak' => '04',
			'UPS Express Box' => '21',
			'UPS 25kg Box' => '24',
			'UPS 10kg Box' => '25'
		];

		// -----
		// Human-readable Service Code lookup table. The values returned by the Rates and Service "shop" method are numeric.
		// Using these codes, and the administratively defined Origin, the proper human-readable service name is returned.
		//
		// Notes:
		// 1) The origin specified in the admin configuration affects only the product name as displayed to the user.
		// 2) These code-to-name correlations were last verified with the "UPS Rating Package RESTful Developer Guide" dated 2023-02-17.
		//
		$this->serviceCodes = [
			// US Origin
			'US Origin' => [
				'01' => 'UPS Next Day Air',
				'02' => 'UPS 2nd Day Air',
				'03' => 'UPS Ground',
				'07' => 'UPS Worldwide Express',
				'08' => 'UPS Worldwide Expedited',
				'11' => 'UPS Standard',
				'12' => 'UPS 3 Day Select',
				'13' => 'UPS Next Day Air Saver',
				'14' => 'UPS Next Day Air Early',
				'54' => 'UPS Worldwide Express Plus',
				'59' => 'UPS 2nd Day Air A.M.',
				'65' => 'UPS Worldwide Saver',
				'75' => 'UPS Heavy Goods',    //- new to OAuth
			],
			// Canada Origin
			'Canada Origin' => [
				'01' => 'UPS Express',
				'02' => 'UPS Expedited',
				'07' => 'UPS Worldwide Express',
				'08' => 'UPS Worldwide Expedited',
				'11' => 'UPS Standard',
				'12' => 'UPS 3 Day Select',
				'13' => 'UPS Express Saver',
				'14' => 'UPS Express Early',
				'54' => 'UPS Worldwide Express Plus',
				'65' => 'UPS Express Saver',
				'70' => 'UPS Access Point Economy',   //- new to OAuth
			],
			// European Union Origin
			'European Union Origin' => [
				'07' => 'UPS Express',
				'08' => 'UPS Expedited',
				'11' => 'UPS Standard',
				'54' => 'UPS Worldwide Express Plus',
				'65' => 'UPS Worldwide Saver',
				'70' => 'UPS Access Point Economy',   //- new to OAuth
			],
			// Puerto Rico Origin
			'Puerto Rico Origin' => [
				'01' => 'UPS Next Day Air',
				'02' => 'UPS 2nd Day Air',
				'03' => 'UPS Ground',
				'07' => 'UPS Worldwide Express',
				'08' => 'UPS Worldwide Expedited',
				'14' => 'UPS Next Day Air Early',
				'54' => 'UPS Worldwide Express Plus',
				'65' => 'UPS Worldwide Saver',
			],
			// Mexico Origin
			'Mexico Origin' => [
				'07' => 'UPS Express',
				'08' => 'UPS Expedited',
				'11' => 'UPS Standard',
				'54' => 'UPS Worldwide Express Plus',
				'65' => 'UPS Worldwide Saver',
			],
			// All other origins
			'All other origins' => [
				'07' => 'UPS Worldwide Express',
				'08' => 'UPS Worldwide Expedited',
				'11' => 'UPS Standard',
				'54' => 'UPS Worldwide Express Plus',
				'65' => 'UPS Worldwide Saver',
			],
		];
	}

	public function setCurrencyCode($currency_code)
	{
		$this->currencyCode = $currency_code;
	}

	public function getUpsEndpoint()
	{
		return $this->endpoint;
	}

	// -----
	// Retrieves an OAuth token from UPS to use in follow-on requests, returning that value to
	// the caller.
	//
	public function getOAuthToken($client_id, $client_secret)
	{
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/x-www-form-urlencoded',
				'x-merchant-id: ' . $client_id,
				'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)
			],
			CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
			CURLOPT_URL => $this->endpoint . self::API_OAUTH_TOKEN,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'POST',
		]);

		$response = curl_exec($ch);
		$token = false;
		if ($response === false) {
			$this->debugLog('CURL error requesting Token (' . curl_errno($ch) . ', ' . curl_error($ch) . ')');
		} else {
			$token = json_decode($response);
		 }

		curl_close($ch);

		return $token;
	}

	// -----
	// Retrieve the requested UPS quotes.  This method will return either a JSON-decoded
	// object that represents the received quote information or (bool)false if an error, either
	// CURL or UPS, is indicated.
	//
	public function getAllUpsQuotes( $oauth_token, $pShipHash )
	{
		$ch = curl_init();
		$rateRequest = $this->buildRateRequest( $pShipHash );
		curl_setopt_array($ch, [
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $oauth_token,
				'Content-Type: application/json',
				'transId: string',
				'transactionSrc: testing',
			],
			CURLOPT_POSTFIELDS => $rateRequest,
			//  CURLOPT_URL => "https://wwwcie.ups.com/api/rating/" . $version . "/" . $requestoption . "?" . http_build_query($query),
			CURLOPT_URL => $this->endpoint . self::API_RATING,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => 'POST',
		]);

		$response = curl_exec($ch);

		$ret = false;
		if ($response === false) {
			$this->debugLog('CURL error requesting Rates (' . curl_errno($ch) . ', ' . curl_error($ch) . ')');
		} else {
			$ret = json_decode($response);
		}
		curl_close($ch);

		return $ret;
	}

	// -----
	// This function builds an array containing the to-be-issued Rate Request, returning
	// that array in a JSON-encoded format.
	//
	protected function buildRateRequest( $pShipHash )
	{
		$rate_request = [
			'RateRequest' => [
				'Request' => [
					'TransactionReference' => [
						'CustomerContext' => 'CustomerContext',
						'TransactionIdentifier' => 'TransactionIdentifier'
					],
				],
				'PickupType' => [
					'Code' => $this->pickupMethods[$this->getPickupMethod()],
				],
				'CustomerClassification' => [
					'Code' => $this->getCustomerClassificationCode(),
				],
				'Shipment' => [
					'Shipper' => [
						'Address' => [
	//						'City' => (!empty($pShipHash['origin']['city'])) ? $pShipHash['origin']['city'] : '',
							'StateProvinceCode' => zen_get_zone_code((int)$pShipHash['origin']['countries_id'], (int)$pShipHash['origin']['zone_id'], ''),
							'PostalCode' => (!empty($pShipHash['origin']['postcode'])) ? $pShipHash['origin']['postcode'] : '',
							'CountryCode' => $pShipHash['origin']['countries_iso_code_2'],
						],
					],
					// -----
					// When rates are requested from the shipping-estimator, the city isn't set and the postcode might not be.  Provide
					// defaults for the request.
					//
					'ShipFrom' => [
						'Name' => STORE_NAME,
						'Address' => [
	//						'City' => (!empty($pShipHash['origin']['city'])) ? $pShipHash['origin']['city'] : '',
							'StateProvinceCode' => zen_get_zone_code((int)$pShipHash['origin']['countries_id'], (int)$pShipHash['origin']['zone_id'], ''),
							'PostalCode' => (!empty($pShipHash['origin']['postcode'])) ? $pShipHash['origin']['postcode'] : '',
							'CountryCode' => $pShipHash['origin']['countries_iso_code_2'],
						]
					],
					'ShipTo' => [
						'Address' => [
							'City' => (!empty($pShipHash['destination']['city'])) ? $pShipHash['destination']['city'] : '',
							'StateProvinceCode' => zen_get_zone_code((int)$pShipHash['destination']['countries_id'], (int)$pShipHash['destination']['zone_id'], ''),
							'PostalCode' => (!empty($pShipHash['destination']['postcode'])) ? $pShipHash['destination']['postcode'] : '',
							'CountryCode' => $pShipHash['destination']['countries_iso_code_2'],
						]
					],
				   'DeliveryTimeInformation' => [
						'PackageBillType' => $this->packagingTypes[$this->getPackageType()],
					],
				]
			]
		];

		// -----
		// Include the ResidentialAddressIndicator, if so (er) indicated.
		//
		if ((bool)$this->isResidentialAddress() === true) {
			$rate_request['RateRequest']['Shipment']['ShipTo']['Address']['ResidentialAddressIndicator'] = 'Y';
		}

		if( $shipper_number = $this->getShipperNumber() ) {
			$rate_request['RateRequest']['Shipment']['Shipper']['ShipperNumber'] = $shipper_number;
			$rate_request['RateRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator'] = 'Y';
		}

		// -----
		// Determine the package 'value'.  It'll be 0 (uninsured) if the module's configuration
		// indicates that packages are not to be insured.
		//
		$package_value = 0.0;
		if ($this->packagesAreInsured() === true) {
			$package_value = $pShipHash['shipping_value'];
		}
		$package_value = number_format(ceil($package_value / $pShipHash['shipping_num_boxes']), 0, '.', '');

		// -----
		// Build the 'base' Package information.  It's the same for each of the shipping boxes.
		//
		$package_info = [
			'PackagingType' => [
				'Code' => $this->packagingTypes[$this->getPackageType()],
			],
			'PackageWeight' => [
				'UnitOfMeasurement' => [
					'Code' => $this->getWeightUnit(),
				],
				'Weight' => number_format($pShipHash['shipping_weight_box'], 5),
			],
			'PackageServiceOptions' => [
				'DeclaredValue' => [
					'CurrencyCode' => $this->currencyCode,
					'MonetaryValue' => (string)$pShipHash['shipping_value'],
				],
			],
		];

		// -----
		// Now, add the package(s) to the request (one for each shipping-box).
		//
		$rate_request['RateRequest']['Shipment']['Package'] = [];
		for ($i = 0; $i < $pShipHash['shipping_num_boxes']; $i++) {
			$rate_request['RateRequest']['Shipment']['Package'][] = $package_info;
		}

//eb( $pShipHash, $rate_request );
		return json_encode($rate_request);
	}
	protected function isResidentialAddress()
	{
		return ($this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_QUOTE_TYPE' ) === 'Residential');
	}

	// -----
	// From *all* UPS quotes returned, grab only those that the store owner is interested in.  Returns
	// an array of the 'interesting' quotes or (bool)false if none of the returned quotes were
	// 'interesting'.
	//
	public function getConfiguredUpsQuotes($all_ups_quotes)
	{
		$quotes = [];
		$ups_service_types = $this->getServiceTypes();
		$ups_shipping_origin = $this->getShippingOrigin();
		foreach ($all_ups_quotes->RateResponse->RatedShipment as $next_shipment) {
			$service_code = $next_shipment->Service->Code;
			if (strpos($ups_service_types, "[$service_code]") === false) {
				continue;
			}
			$quotes[$service_code] = [
				'cost' => $this->getShipmentCost($next_shipment),
				'business_days_in_transit' => $this->getDaysInTransit($next_shipment),
				'title' => $this->serviceCodes[$ups_shipping_origin][$service_code],
			];
		}

		$this->debugLog('getConfiguredUpsQuotes, returning: ' . PHP_EOL . var_export($quotes, true));
		return (count($quotes) === 0) ? false : $quotes;
	}

	protected function getHandlingFee()
	{
		return ($this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_HANDLING_FEE' ) === '') ? '0' : $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_HANDLING_FEE' );
	}
	protected function getTransitWeightDisplayOptions()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_OPTIONS' );
	}
	public function getShippingMethodsFromQuotes($ups_quotes, $method='')
	{
		// -----
		// Any handling-fee can be represented as either a fixed or a percentage.  Determine which
		// and set the fee's adder/multiplier value for use in the quote-generation loop below.
		//
		// Note that no checking of malformed values is performed; PHP Warnings and Notices will be
		// issued if the value's not numeric or a percentage value doesn't end in %.
		//
		if (strpos($this->getHandlingFee(), '%') === false) {
			$handling_fee_adder = $this->getHandlingFee() * $this->getFixedHandlingFeeMultiplier();
			$handling_fee_multiplier = 1;
		} else {
			$handling_fee_adder = 0;
			$handling_fee_multiplier = 1 + (rtrim($this->getHandlingFee(), '%') / 100);
		}

		// -----
		// Create the array that maps the UPS service codes to their names.
		//
		$methods = [];

		foreach ($ups_quotes as $service_code => $quote_info) {
			$type = $quote_info['title'];
			$cost = $quote_info['cost'];
			if ($method === '' || $method === $type) {
				$methods[] = array(
					'id' => $type,
					'title' => $type,
					'cost' => ($handling_fee_multiplier * $cost) + $handling_fee_adder,
				);
			}
		}
		return $methods;
	}
	protected function getFixedHandlingFeeMultiplier()
	{
		global $shipping_num_boxes;
		return ( $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_HANDLING_APPLIES' ) === 'Box' ? $shipping_num_boxes : 1 );
	}
	protected function getCurrentMethodQuote(array $quote_info, string $method, string $type, string $cost, $handling_fee_multiplier, $handling_fee_adder)
	{
		$title = $type;
		return [
			'id' => $type,
			'title' => $title,
			'cost' => ($handling_fee_multiplier * $cost) + $handling_fee_adder,
		];

	}

	protected function getUnitWeight()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_UNIT_WEIGHT' );
	}
	public function getWeightInfo()
	{
		global $shipping_num_boxes, $shipping_weight;

		$weight_info = '';
		if ((strpos($this->getTransitWeightDisplayOptions(), 'weight') !== false)) {
			$weight_info = ' (' . $shipping_num_boxes . ($shipping_num_boxes > 1 ? ' pkg(s) x ' : ' pkg x ') . number_format($shipping_weight, 2) . ' ' . strtolower($this->getUnitWeight()) . ' total)';
		}
		return $weight_info;
	}

	// -----
	// "Helper" methods to enable an extended class to provide values different than those
	// configured for the 'base' upsoauth shipping module.
	//
	protected function getPickupMethod()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_PICKUP_METHOD' );
	}
	protected function getCustomerClassificationCode()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_CUSTOMER_CLASSIFICATION_CODE' );
	}
	protected function getOriginShippingAddress()
	{
		return [
			'City' => $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_ORIGIN_CITY' ),
			'StateProvinceCode' => $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_ORIGIN_STATEPROV' ),
			'PostalCode' => $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_ORIGIN_POSTALCODE' ),
			'CountryCode' => $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_ORIGIN_COUNTRY_CODE' ),
		];
	}
	protected function getPackageType()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_PACKAGE_TYPE' );
	}
	protected function getShipperNumber()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_SHIPPER_NUMBER' );
	}
	protected function packagesAreInsured()
	{
		return ($this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_INSURE' ) === 'True');
	}
	protected function getWeightUnit()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_UNIT_WEIGHT' );
	}
	protected function getServiceTypes()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_TYPES' );
	}
	protected function getShippingOrigin()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_ORIGIN' );
	}
	protected function getShippingDaysDelay()
	{
		return $this->getCommerceConfig( 'MODULE_SHIPPING_UPSOAUTH_SHIPPING_DAYS_DELAY' );
	}
	protected function getDaysInTransit($next_shipment)
	{
		$days_in_transit = isset($next_shipment->GuaranteedDelivery->BusinessDaysInTransit) ? $next_shipment->GuaranteedDelivery->BusinessDaysInTransit : false;
		if ($days_in_transit !== false) {
			$days_in_transit += ceil((float)$this->getShippingDaysDelay());
		}
		return $days_in_transit;
	}
	protected function getShipmentCost($next_shipment)
	{
		if (isset($next_shipment->NegotiatedRateCharges->TotalCharge->MonetaryValue)) {
			$cost = $next_shipment->NegotiatedRateCharges->TotalCharge->MonetaryValue;
		} else {
			$cost = $next_shipment->TotalCharges->MonetaryValue;
		}
		return $cost;
	}

	protected function debugLog($message, $include_spacer = false)
	{
		if ($this->debug === true) {
			$spacer = ($include_spacer === false) ? '' : "------------------------------------------\n";
			error_log($spacer . date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, 3, $this->logfile);
		}
	}
}


<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org							|
// +----------------------------------------------------------------------+
// | Processing file for the Zen Cart implementation of the UPS shipping  |
// | module which uses the UPS RESTful API with OAuth authentication.	 |
// | Copyright (c) 2017 bitcommerce.org								   |
// | This source file is subject to version 3.0 of the GPL license		|
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com				  |
// | Copyright 2023, Vinos de Frutas Tropicales
// Last updated: v1.2.2
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );
require_once( BITCOMMERCE_PKG_PATH . DIR_WS_MODULES . 'shipping/upsoauth/UpsOAuthApi.php' );

class upsoauth extends CommercePluginShippingBase
{
	// -----
	// Zen Cart "Plugin ID", used for version-update checks.
	//
	const ZEN_CART_PLUGIN_ID = 2374;

	// $moduleVersion = '1.2.2',

	public function __construct()
	{
		parent::__construct();
		$this->title = 'United Parcel Service';
		$this->description = 'UPS shipping module which uses the UPS RESTful API with OAuth authentication.';
		$this->icon = 'shipping_ups';
	}

	protected function debugLog($message, $include_spacer = false)
	{
			$spacer = ($include_spacer === false) ? '' : "------------------------------------------\n";
			bit_error_log($spacer . date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL);
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {

			$quoteError = NULL;

			$this->upsApi = new UpsOAuthApi($this->getModuleConfigValue( '_MODE' ), false, false);

			if( !isset($_SESSION['upsoauth_token_expires']) || $_SESSION['upsoauth_token_expires'] <= time()) {
				if( $oauth_token = $this->upsApi->getOAuthToken($this->getModuleConfigValue( '_CLIENT_ID' ), $this->getModuleConfigValue( '_CLIENT_SECRET' )) ) {
					// -----
					// If the response from UPS for the OAuth-Token request indicates that the Client ID and/or
					// Client Secret are invalid, auto-disable this shipping method and send an email to let the
					// store owner know.
					//
					if (isset($oauth_token->response->errors)) {
						$log_message = $quoteError = 'UPS error returned when requesting OAuth token:' . PHP_EOL;
						foreach ($oauth_token->response->errors as $next_error) {
							$log_message .= $next_error->code . ': ' . $next_error->message . PHP_EOL;
							if ($next_error->code == 10401) {
								$quoteError .= 'The \'Client ID\' and \'Client Secret\' you supplied are not recognized by UPS; the \'upsoauth\' shipping module has been automatically disabled.';
							}
						}
						$this->debugLog($log_message, true);
					} else {
						$token_retrieved = true;
						$this->debugLog('OAuth Token successfully retrieved, expires in ' . ($oauth_token->expires_in - 3) . ' seconds.');
						$_SESSION['upsoauth_token'] = $oauth_token->access_token;
						$_SESSION['upsoauth_token_expires'] = time() + $oauth_token->expires_in - 3;
					}
				}
			}

			if( !empty( $_SESSION['upsoauth_token'] ) ) {

				// -----
				// Retrieve *all* the UPS quotes for the current shipment, noting that there might be
				// shipping methods not requested by the site via configuration.  If an error (either CURL or
				// UPS) occurs in this retrieval, report that no quotes are available from this shipping module.
				//
				if( $all_ups_quotes = $this->upsApi->getAllUpsQuotes( $_SESSION['upsoauth_token'], $pShipHash ) ) {
					if( !empty( $all_ups_quotes->response->errors) ) {
						$quoteError = '';
						foreach( $all_ups_quotes->response->errors as $alertObject ) {
							$quoteError .= $alertObject->code .' : ' . $alertObject->message."<br/>\n";
						}
					}

					if( !empty( $all_ups_quotes->RateResponse->Response->Alert ) ) {
						$quoteError = '';
						foreach( $all_ups_quotes->RateResponse->Response->Alert as $alertObject ) {
							$quoteError .= $alertObject->Code .' : ' . $alertObject->Description."<br/>\n";
						}
					}

					// -----
					// Determine which, if any, of the quotes returned are applicable for the current store.  If none are,
					// report that no quotes are available from this shipping module.
					//

					if( !empty( $all_ups_quotes->RateResponse->RatedShipment ) ) {
						$ups_quotes = $this->upsApi->getConfiguredUpsQuotes($all_ups_quotes);
						if ($ups_quotes === false) {
							return false;
						}

						if( $methods = $this->upsApi->getShippingMethodsFromQuotes( $ups_quotes, $pShipHash['method'] ) ) {
							// -----
							// Sort the shipping methods to be returned in ascending order of cost.
							//
							usort($methods, function($a, $b) {
								if ($a['cost'] === $b['cost']) {
									return 0;
								}
								return ($a['cost'] < $b['cost']) ? -1 : 1;
							});

							$quotes['methods'] = $methods;
						} else {
							$quoteError .= "No available methods matching required '$pShipHash[method]'; no UPS quotes available. <br>";
						}
					}
				}

				if ((int)$this->getModuleConfigValue( '_TAX_CLASS' ) > 0) {
					$quotes['tax'] = zen_get_tax_rate((int)$this->getModuleConfigValue( '_TAX_CLASS' ), $order->delivery['country']['id'], $order->delivery['zone_id']);
				}


				if( !empty( $message ) ) {
					$quotes = array('module' => $this->title, 'error' => $message);
				}

				if ( !empty( $this->icon ) && !empty( $quotes ) ) {
					$quotes['icon'] = $this->icon;
				}
			}
			if( $quoteError ) {
				$quotes['error'] = $quoteError;
			}
			$quotes['icon'] = $this->icon;
		}

		return $quotes;
	}

	protected function config() {
		$i = 3;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_VERSION' => array(
				'configuration_title' => 'Version Installed',
				'configuration_value' => '1.3.0',
				'sort_order' => $i++, 
			),

			$this->getModuleKeyTrunk().'_API_CLASS' => array(
			'configuration_title' => 'UPS Api Class',
			'configuration_value' => 'UpsOAuthApi',
			'configuration_description' => 'If your site has an class-override for the shipping modules default (<var>UpsOAuthApi</var>) enter it here. If the class-file does not exist this module will be automatically disabled!',
			'configuration_group_id' => 6,
			'sort_order' => 2,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_ZONE' => array(
			'configuration_title' => 'Shipping Zone',
			'configuration_value' => '0',
			'configuration_description' => 'If a zone is selected only enable this shipping method for that zone.',
			'configuration_group_id' => 6,
			'sort_order' => 18,
			'use_function' => 'zen_get_zone_class_title',
			'set_function' => 'zen_cfg_pull_down_zone_classes(',
			),

			$this->getModuleKeyTrunk().'_CLIENT_ID' => array(
			'configuration_title' => 'UPS Rates Client ID',
			'configuration_value' => NULL,
			'configuration_description' => 'Enter the OAuth <code>Client ID</code> assigned to you by UPS; see <a href=\"https://developer.ups.com/get-started?loc=en_US\" target=\"_blank\" rel=\"noreferrer noopener\">this</a> UPS link for more information.',
			'configuration_group_id' => 6,
			'sort_order' => 1,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_CLIENT_SECRET' => array(
			'configuration_title' => 'UPS Rates Client Secret',
			'configuration_value' => NULL,
			'configuration_description' => 'Enter your OAuth <code>Client Secret</code> assigned to you by UPS.',
			'configuration_group_id' => 6,
			'sort_order' => 2,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_MODE' => array(
			'configuration_title' => 'Test or Production Mode',
			'configuration_value' => 'Test',
			'configuration_description' => 'Use this module in Test or Production mode?',
			'configuration_group_id' => 6,
			'sort_order' => 12,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'Test\', \'Production\'], ',
			),

			$this->getModuleKeyTrunk().'_SHIPPER_NUMBER' => array(
			'configuration_title' => 'UPS Rates <em>Shipper Number</em>',
			'configuration_value' => NULL,
			'configuration_description' => 'Enter your UPS Services <em>Shipper Number</em> if you want to receive your account\'s negotiated rates!',
			'configuration_group_id' => 6,
			'sort_order' => 3,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_ORIGIN' => array(
			'configuration_title' => 'Shipping Origin',
			'configuration_value' => 'US Origin',
			'configuration_description' => 'What origin point should be used (this setting affects only what UPS product names are shown to the customer).',
			'configuration_group_id' => 6,
			'sort_order' => 7,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'US Origin\', \'Canada Origin\', \'European Union Origin\', \'Puerto Rico Origin\', \'Mexico Origin\', \'All other origins\'], ',
			),

			$this->getModuleKeyTrunk().'_ORIGIN_STATEPROV' => array(
			'configuration_title' => 'Origin State/Province',
			'configuration_value' => NULL,
			'configuration_description' => 'Enter the two-letter code for your origin state/province.',
			'configuration_group_id' => 6,
			'sort_order' => 9,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_ORIGIN_CITY' => array(
			'configuration_title' => 'Origin City',
			'configuration_value' => NULL,
			'configuration_description' => 'Enter the name of the origin city.',
			'configuration_group_id' => 6,
			'sort_order' => 8,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_ORIGIN_POSTALCODE' => array(
			'configuration_title' => 'Origin Zip/Postal Code',
			'configuration_value' => NULL,
			'configuration_description' => 'Enter your origin zip/postalcode.',
			'configuration_group_id' => 6,
			'sort_order' => 11,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_PICKUP_METHOD' => array(
			'configuration_title' => 'Pickup Method',
			'configuration_value' => 'Daily Pickup',
			'configuration_description' => 'How do you give packages to UPS?',
			'configuration_group_id' => 6,
			'sort_order' => 4,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'Daily Pickup\', \'Customer Counter\', \'One Time Pickup\', \'On Call Air Pickup\', \'Letter Center\', \'Air Service Center\'], ',
			),

			$this->getModuleKeyTrunk().'_PACKAGE_TYPE' => array(
			'configuration_title' => 'Packaging Type',
			'configuration_value' => 'Customer Package',
			'configuration_description' => 'What kind of packaging do you use?',
			'configuration_group_id' => 6,
			'sort_order' => 5,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'Customer Package\', \'UPS Letter\', \'UPS Tube\', \'UPS Pak\', \'UPS Express Box\', \'UPS 25kg Box\', \'UPS 10kg box\'], ',
			),

			$this->getModuleKeyTrunk().'_CUSTOMER_CLASSIFICATION_CODE' => array(
			'configuration_title' => 'Customer Classification Code',
			'configuration_value' => '04',
			'configuration_description' => '<br>Choose the type of rates to be returned:<ul><li><b>00</b>: Rates associated with your <em>Shipper Number</em></li><li><b>01</b>: Daily Rates</li><li><b>04</b>: Retail Rates (default)</li><li><b>05</b>: Regional Rates</li><li><b>06</b>: General List Rates</li><li><b>53</b>: Standard List Rates</li></ul>',
			'configuration_group_id' => 6,
			'sort_order' => 6,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'00\', \'01\', \'04\', \'05\', \'06\', \'53\'], ',
			),

			$this->getModuleKeyTrunk().'_OPTIONS' => array(
			'configuration_title' => 'UPS Display Options',
			'configuration_value' => '--none--',
			'configuration_description' => 'Select from the following the UPS options.',
			'configuration_group_id' => 6,
			'sort_order' => 16,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_multioption([\'Display weight\', \'Display transit time\'], ',
			),

			$this->getModuleKeyTrunk().'_SHIPPING_DAYS_DELAY' => array(
			'configuration_title' => 'Shipping Delay',
			'configuration_value' => '0',
			'configuration_description' => 'How many business days after an order is placed is the order shipped? This value is added to the number of business days that UPS indicates in its rate quote.',
			'configuration_group_id' => 6,
			'sort_order' => 7,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_UNIT_WEIGHT' => array(
			'configuration_title' => 'Unit Weight',
			'configuration_value' => 'LBS',
			'configuration_description' => 'By what unit are your packages weighed?',
			'configuration_group_id' => 6,
			'sort_order' => 13,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'LBS\', \'KGS\'], ',
			),

			$this->getModuleKeyTrunk().'_QUOTE_TYPE' => array(
			'configuration_title' => 'Quote Type',
			'configuration_value' => 'Commercial',
			'configuration_description' => 'Quote for Residential or Commercial Delivery',
			'configuration_group_id' => 6,
			'sort_order' => 15,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'Commercial\', \'Residential\'], ',
			),

			$this->getModuleKeyTrunk().'_HANDLING_FEE' => array(
			'configuration_title' => 'Handling Fee',
			'configuration_value' => '0',
			'configuration_description' => 'Handling fee for this shipping method.  The value you enter is either a fixed value for all shipping quotes or a percentage. e.g. 10% of each UPS quote\'s value.',
			'configuration_group_id' => 6,
			'sort_order' => 16,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_CURRENCY_CODE' => array(
			'configuration_title' => 'UPS Currency Code',
			'configuration_value' => DEFAULT_CURRENCY,
			'configuration_description' => 'Enter the 3 letter currency code for your country of origin. United States (USD)',
			'configuration_group_id' => 6,
			'sort_order' => 2,
			'use_function' => NULL,
			'set_function' => NULL,
			),

			$this->getModuleKeyTrunk().'_INSURE' => array(
			'configuration_title' => 'Enable Insurance',
			'configuration_value' => 'True',
			'configuration_description' => 'Do you want to insure packages shipped by UPS?',
			'configuration_group_id' => 6,
			'sort_order' => 0,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_option([\'True\', \'False\'], ',
			),

			$this->getModuleKeyTrunk().'_TYPES' => array(
			'configuration_title' => 'Shipping Methods',
			'configuration_value' => 'Next Day Air [01], 2nd Day Air [02], Ground [03], Worldwide Express [07], Standard [11], 3 Day Select [12]',
			'configuration_description' => 'Select the UPS services to be offered.',
			'configuration_group_id' => 6,
			'sort_order' => 20,
			'use_function' => NULL,
			'set_function' => 'zen_cfg_select_multioption([\'Next Day Air [01]\', \'2nd Day Air [02]\', \'Ground [03]\', \'Worldwide Express [07]\', \'Worldwide Expedited [08]\', \'Standard [11]\', \'3 Day Select [12]\', \'Next Day Air Saver [13]\', \'Next Day Air Early [14]\', \'Worldwide Express Plus [54]\', \'2nd Day Air A.M. [59]\', \'Express Saver [65]\'], ',
			),
	  ) );
	}

}

<?php
/**
 * @package shippingMethod
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ups.php	2019-01-13 $
 */
/**
 * UPS Shipping Module class
 * NOTE: This retrieves generic shipping quotes, not specific to a given shipper account's negotiated rates. For negotiated rates, use the UPSXML module instead.
 */
require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class ups extends CommercePluginShippingBase {

	var $types = array();

	function __construct() {

		parent::__construct();

		$this->title = tra( 'United Parcel Service' );
		$this->description = tra( 'This retrieves generic shipping quotes. For negotiated rates, use the UPSXML module instead.' );


		if( $this->isEnabled() ) {
			$this->sort_order		= MODULE_SHIPPING_UPS_SORT_ORDER;
			$this->tax_class 		= MODULE_SHIPPING_UPS_TAX_CLASS;
			$this->tax_basis 		= MODULE_SHIPPING_UPS_TAX_BASIS;

			if( (int)MODULE_SHIPPING_UPS_ZONE > 0 ) {
				$check_flag = false;
				$check = $this->mDb->query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_UPS_ZONE . "' and zone_country_id = '" . (int)$pShipHash['destination']['id'] . "' order by zone_id");
				while (!$check->EOF) {
					if ($check->fields['zone_id'] < 1) {
						$check_flag = true;
						break;
					} elseif ($check->fields['zone_id'] == $pShipHash['destination']['zone_id']) {
						$check_flag = true;
						break;
					}
					$check->MoveNext();
				}

				if ($check_flag == false) {
					$this->enabled = false;
				}
			}

			$this->types = array('1DM' => 'Next Day Air Early AM',
													 '1DML' => 'Next Day Air Early AM Letter',
													 '1DA' => 'Next Day Air',
													 '1DAL' => 'Next Day Air Letter',
													 '1DAPI' => 'Next Day Air Intra (Puerto Rico)',
													 '1DP' => 'Next Day Air Saver',
													 '1DPL' => 'Next Day Air Saver Letter',
													 '2DM' => '2nd Day Air AM',
													 '2DML' => '2nd Day Air AM Letter',
													 '2DA' => '2nd Day Air',
													 '2DAL' => '2nd Day Air Letter',
													 '3DS' => '3 Day Select',
													 'GND' => 'Ground',
													 'GNDCOM' => 'Ground Commercial',
													 'GNDRES' => 'Ground Residential',
													 'STD' => 'Canada Standard',
													 'XPR' => 'Worldwide Express',
													 'XPRL' => 'Worldwide Express Letter',
													 'XDM' => 'Worldwide Express Plus',
													 'XDML' => 'Worldwide Express Plus Letter',
													 'XPD' => 'Worldwide Expedited',
													 'WXS' => 'Worldwide Saver');
		}
	}
	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			if( $upsQuote = $this->_upsGetQuote( $pShipHash ) ) {
				$methods = array();
				// BOF: UPS 
				$allowed_methods = explode(", ", MODULE_SHIPPING_UPS_TYPES);
				$std_rcd = false;
				// EOF: UPS 
				$qsize = sizeof($upsQuote);
				for ($i=0; $i<$qsize; $i++) {
					list($type, $cost) = each($upsQuote[$i]);
					// BOF: UPS 
					if ($type=='STD') {
						if ($std_rcd) continue;
						else $std_rcd = true;
					}
					if (!in_array($type, $allowed_methods)) continue;
					// EOF: UPS 
					$cost = preg_replace('/[^0-9.]/', '',	$cost);
					$methods[] = array( 'id' => $type,
										'title' => $this->types[$type],
										'cost' => ($cost * $pShipHash['shipping_num_boxes']) + (MODULE_SHIPPING_UPS_HANDLING_METHOD == 'Box' ? MODULE_SHIPPING_UPS_HANDLING * $pShipHash['shipping_num_boxes'] : MODULE_SHIPPING_UPS_HANDLING) );
				}

				$quotes['methods'] = $methods;
			} else {
				$quotes = array('module' => $this->title,
								'error' => 'We are unable to obtain a rate quote for UPS shipping.<br />Please contact the store if no other alternative is shown.');
			}
		}

		return $quotes;
	}

	/**
	 * Set UPS Destination information
	 *
	 * @param string $postal
	 * @param string $country
	 */
	private function _upsPostal( $postalCode, $pCountryIso2 ) {
		$zipLength = ($pCountryIso2 == 'US' ? 5 : 6);
		return substr( str_replace(' ', '', $postalCode ), 0, $zipLength );
	}
	/**
	 * Set UPS rate-quote method
	 *
	 * @param string $foo
	 */
	private function _upsRate( $pRateCode ) {
		$ret = '';
		switch ( $pRateCode ) {
			case 'RDP':
				$ret = 'Regular+Daily+Pickup';
				break;
			case 'OCA':
				$ret = 'On+Call+Air';
				break;
			case 'OTP':
				$ret = 'One+Time+Pickup';
				break;
			case 'LC':
				$ret = 'Letter+Center';
				break;
			case 'CC':
				$ret = 'Customer+Counter';
				break;
		}
		return $ret;
	}
	/**
	 * Set UPS Container type
	 *
	 * @param string $foo
	 */
	private function _upsContainer( $pPackageCode ) {
		$ret = '';
		switch( $pPackageCode ) {
			case 'CP': // Customer Packaging
				$ret = '00';
				break;
			case 'ULE': // UPS Letter Envelope
				$ret = '01';
				break;
			case 'UT': // UPS Tube
				$ret = '03';
				break;
			case 'UEB': // UPS Express Box
				$ret = '21';
				break;
			case 'UW25': // UPS Worldwide 25 kilo
				$ret = '24';
				break;
			case 'UW10': // UPS Worldwide 10 kilo
				$ret = '25';
				break;
		}
		return $ret;
	}
	/**
	 * Sent request for quote to UPS via older HTML method
	 *
	 * @return array
	 */
	private function _upsGetQuote( $pShipHash ) {
		$upsAction = (!empty( $pShipHash['method'] ) ? '3' : '4');

		if ( !empty( $pShipHash['method'] ) && (isset($this->types[$pShipHash['method']])) ) {
			$prod = $pShipHash['method'];
		} else if ($pShipHash['destination']['countries_iso_code_2'] == 'CA') {
			$prod = 'STD';
		} else {
			$prod = 'GNDRES';
		}

		$upsResComCode = (int)empty( $pShipHash['destination']['company'] );
		$host = 'https://www.ups.com/using/services/rave/qcostcgi.cgi?';
		$request = implode('&', array(	'accept_UPS_license_agreement=yes',
										'10_action=' . $upsAction,
										'13_product=' . $prod,
										'14_origCountry=' . $pShipHash['origin']['countries_iso_code_2'],
										'15_origPostal=' . $this->_upsPostal( $pShipHash['origin']['postcode'], $pShipHash['origin']['countries_iso_code_2'] ),
										'19_destPostal=' . $this->_upsPostal( $pShipHash['destination']['postcode'], $pShipHash['destination']['countries_iso_code_2'] ),
										'22_destCountry=' . $pShipHash['destination']['countries_iso_code_2'],
										'23_weight=' . $pShipHash['shipping_weight_total'],
										'47_rate_chart=' . $this->_upsRate( MODULE_SHIPPING_UPS_PICKUP ),
										'48_container=' . $this->_upsContainer( MODULE_SHIPPING_UPS_PACKAGE ),
										'49_residential=' . $upsResComCode
									));
		$url = $host . $request;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart quote inquiry');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$error = curl_error($ch);

		if ($error > 0) {
			curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url));
			$response = curl_exec($ch);
			$error = curl_error($ch);
		}
		curl_close($ch);
		if ($error > 0 || $response == '') {
			$response = file_get_contents($url);
		}
		if ($response === false) {
			$response = file_get_contents(str_replace('tps:', 'tp:', $url));
		}
		if ($response === false) return 'error';

		$body = $response;

		// BOF: UPS 
		/*
		TEST by checking out in the catalog; try a variety of shipping destinations to be sure
		your customers will be properly served.	If you are not getting any quotes, try enabling
		more alternatives in admin. Make sure your store's postal code is set in Admin ->
		Configuration -> Shipping/Packaging, since you won't get any quotes unless there is
		a origin that UPS recognizes.

		If you STILL don't get any quotes, here is a way to find out exactly what UPS is sending
		back in response to rate quote request, you can uncomment the following mail() line and
		then check your email after visiting the shipping page in checkout ...
		*/
		//mail(STORE_OWNER_EMAIL_ADDRESS, 'UPS response', $body, 'From: <'.STORE_OWNER_EMAIL_ADDRESS.'>');

		// EOF: UPS 

		$body_array = explode("\n", $body);

/* //DEBUG ONLY
		$n = sizeof($body_array);
		for ($i=0; $i<$n; $i++) {
			$result = explode('%', $body_array[$i]);
			print_r($result);
		}
		die('END');
*/

		$returnval = array();
		$errorret = 'error'; // only return 'error' if NO rates returned

		$n = sizeof($body_array);
		for ($i=0; $i<$n; $i++) {
			$result = explode('%', $body_array[$i]);
			$errcode = substr($result[0], -1);
			switch ($errcode) {
				case 3:
				if (is_array($returnval)) $returnval[] = array($result[1] => $result[10]);
				break;
				case 4:
				if (is_array($returnval)) $returnval[] = array($result[1] => $result[10]);
				break;
				case 5:
				$errorret = $result[1];
				break;
				case 6:
				if (is_array($returnval)) $returnval[] = array($result[3] => $result[10]);
				break;
			}
		}
		if (empty($returnval)) $returnval = $errorret;

		return $returnval;
	}

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$i = 3;
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_VERSION' => array(
				'configuration_title' => 'UPS Version Date',
				'configuration_value' => '2019-01-13',
				'configuration_description' => 'You have installed:',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array(`2019-01-13`), ",
			),
			$this->getModuleKeyTrunk().'_PICKUP' => array(
				'configuration_title' => 'UPS Pickup Method',
				'configuration_value' => 'CC',
				'configuration_description' => 'How do you give packages to UPS? CC - Customer Counter, RDP - Daily Pickup, OTP - One Time Pickup, LC - Letter Center, OCA - On Call Air',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_PACKAGE' => array(
				'configuration_title' => 'UPS Packaging?',
				'configuration_value' => 'CP',
				'configuration_description' => 'CP - Your Packaging, ULE - UPS Letter, UT - UPS Tube, UBE - UPS Express Box',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_RES' => array(
				'configuration_title' => 'Residential Delivery?',
				'configuration_value' => 'RES',
				'configuration_description' => 'Quote for Residential (RES) or Commercial Delivery (COM)',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_HANDLING_METHOD' => array(
				'configuration_title' => 'Handling Per Order or Per Box',
				'configuration_value' => 'Box',
				'configuration_description' => 'Do you want to charge Handling Fee Per Order or Per Box?',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array(`Order`, `Box`), ",
			),
			$this->getModuleKeyTrunk().'_TYPES' => array(
				'configuration_title' => 'Shipping Methods: <br />Nxt AM, Nxt AM Ltr, Nxt, Nxt Ltr, Nxt PR, Nxt Save, Nxt Save Ltr, 2nd AM, 2nd AM Ltr, 2nd, 2nd Ltr, 3 Day Select, Ground, Canada,World Xp, World Xp Ltr, World Xp Plus, World Xp Plus Ltr, World Expedite, WorldWideSaver',
				'configuration_value' => '1DM, 1DML, 1DA, 1DAL, 1DAPI, 1DP, 1DPL, 2DM, 2DML, 2DA, 2DAL, 3DS, GND, STD, XPR, XPRL, XDM, XDML, XPD, WXS',
				'configuration_description' => 'Select the UPS services to be offered.',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_multioption(array('1DM','1DML', '1DA', '1DAL', '1DAPI', '1DP', '1DPL', '2DM', '2DML', '2DA', '2DAL', '3DS','GND', 'STD', 'XPR', 'XPRL', 'XDM', 'XDML', 'XPD', 'WXS'), ",
			),
		) );
	}
}


/**
 * this is ONLY here to offer compatibility with ZC versions prior to v1.5.2
 */
if (!function_exists('plugin_version_check_for_updates')) {
	function plugin_version_check_for_updates($plugin_file_id = 0, $version_string_to_compare = '')
	{
		if ($plugin_file_id == 0) {
			return false;
		}
		$new_version_available = false;
		$lookup_index					= 0;
		$url									 = 'https://www.zen-cart.com/downloads.php?do=versioncheck' . '&id=' . (int)$plugin_file_id;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Plugin Version Check [' . (int)$plugin_file_id . '] ' . HTTP_SERVER);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$error		= curl_error($ch);

		if ($error > 0) {
			curl_setopt($ch, CURLOPT_URL, str_replace('tps:', 'tp:', $url));
			$response = curl_exec($ch);
			$error		= curl_error($ch);
		}
		curl_close($ch);
		if ($error > 0 || $response == '') {
			$response = file_get_contents($url);
		}
		if ($response === false) {
			$response = file_get_contents(str_replace('tps:', 'tp:', $url));
		}
		if ($response === false) {
			return false;
		}

		$data = json_decode($response, true);
		if (!$data || !is_array($data)) {
			return false;
		}
		// compare versions
		if (strcmp($data[$lookup_index]['latest_plugin_version'], $version_string_to_compare) > 0) {
			$new_version_available = true;
		}
		// check whether present ZC version is compatible with the latest available plugin version
		if (!in_array('v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR, $data[$lookup_index]['zcversions'])) {
			$new_version_available = false;
		}

		return ($new_version_available) ? $data[$lookup_index] : false;
	}
}

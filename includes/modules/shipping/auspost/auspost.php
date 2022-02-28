<?php
/*
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2022 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyright (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

  Copyright (c) 2007-2009 Rod Gasson / VCSWEB
 
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 $Id: auspost.php,v2.2.1  Nov 2016

*/

// class constructor

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class auspost extends CommercePluginShippingBase {

	public function __construct() {
		parent::__construct();
		$this->title			= tra( 'Australia Post' );
		$this->description		= tra( 'Australia Post Parcel Service <p>You will need to register at the <a href="https://developers.auspost.com.au/" target="_new">Developer Program</a></p>' );
		$this->icon				= 'shipping_auspost';
	}

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	public function quote( $pShipHash ) {

		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			$parcelWeight = $pShipHash['shipping_weight_total'];

			$unitConversion = 1;
			if( $pShipHash['weight_unit'] == 'lb' ) {
				// convert pounds to kilograms
				$parcelWeight = $parcelWeight * .45;
				$unitConversion = 2.54;
			}

			if( $tare = $this->getModuleConfigValue( '_TARE' ) ) {
			    $parcelWeight = $parcelWeight + (($parcelWeight*$tare)/100) ;
			}

			$apiDestination = (BitBase::getParameter( $pShipHash['destination'], 'countries_iso_code_2' ) == 'AU' ? 'domestic' : 'international');

			$auPostUri = "https://digitalapi.auspost.com.au/postage/parcel/$apiDestination/service.json"; 

			$queryParams = array (
			  "from_postcode" => $pShipHash['origin']['postcode'],
			  "to_postcode" => $pShipHash['destination']['postcode'],
			  "length" => (BitBase::getParameter( $pShipHash, 'box_length', 22 ) * $unitConversion),
			  "width" => (BitBase::getParameter( $pShipHash, 'box_width', 16 ) * $unitConversion),
			  "height" => (BitBase::getParameter( $pShipHash, 'box_height', 7.7 ) * $unitConversion),
			  "weight" => $parcelWeight
			);

			if( $apiDestination == 'international' ) {
				$queryParams['country_code'] = $pShipHash['destination']['countries_iso_code_2'];
			}

			$allowedTypes = $this->getAllowedServiceTypes();
			$methods = array() ;

			// Server query string //
			if( $auspostQuote = $this->get_auspost_api( $auPostUri, $queryParams ) ) {
				foreach( $auspostQuote['services']['service'] as $service ) {
					global $currencies;

					if( ($serviceCode = BitBase::getParameter( $service, 'code' )) && empty( $allowedTypes ) || in_array( $serviceCode, $allowedTypes ) || (!empty( $pShipHash['method'] ) && ($pShipHash['method'] == $serviceCode)) ) {
						$costAud = (float)$service['price'];
						$costStore = $currencies->convert( $costAud, DEFAULT_CURRENCY, 'AUD' ) + (float)$this->getShipperHandling();
						if( in_array( $service['code'], $allowedTypes ) ) {
								$methods[] = array( 'id' => $serviceCode,
													'title' => $service['name'],
													'cost' => $costStore,
													'code' => $serviceCode
													);
						}
					}
				}
				if( !empty( $methods ) ) {
					$this->sortQuoteMethods( $methods );
					$quotes['methods'] = $methods;
				}
			} else {
				if ($auspostQuote != false) {
					$errmsg = tra( 'No shipping options are available for this delivery address using this shipping service.' );
				} else {
					$errmsg = tra( 'An unknown error occured with the Australia Post shipping calculations.' );
				}
				$quotes['error'] = $errmsg;
			}
		}

		return $quotes;
	}

	private function getAllowedServiceTypes() {
		$ret = array();

		global $gCommerceSystem;
		if( $allowedTypes = $gCommerceSystem->getConfig( $this->getModuleKeyTrunk().'_TYPES' ) ) {
			$ret = array_map('trim', explode(',', $allowedTypes));
		}

		return $ret;
	}
				

	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$ret = array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_AUTHKEY' => array(
				'configuration_title' => 'Auspost API Key',
				'configuration_description' => 'To use this module, you must obtain a 36 digit API Key from the <a href=\"https://developers.auspost.com.au/\" target=\"_new\">Auspost Development Centre</a>',
			),
			$this->getModuleKeyTrunk().'_SPCODE' => array(
				'configuration_title' => 'Dispatch Postcode',
				'configuration_value' => '2000',
				'configuration_description' => 'Dispatch Postcode?',
			),
			$this->getModuleKeyTrunk().'_DIMS' => array(
				'configuration_title' => 'Default Parcel Dimensions',
				'configuration_value' => '10,10,2',
				'configuration_description' => 'Default Parcel dimensions (in cm). Three comma seperated values (eg 10,10,2 = 10cm x 10cm x 2cm). These are used if the dimensions of individual products are not set',
			),
			$this->getModuleKeyTrunk().'_TARE' => array(
				'configuration_title' => 'Tare percent.',
				'configuration_value' => '10',
				'configuration_description' => 'Add this percentage of the items total weight as the tare weight. (This module ignores the global settings that seems to confuse many users. 10% seems to work pretty well.).',
			),
			$this->getModuleKeyTrunk().'_WEIGHT_FORMAT' => array(
				'configuration_title' => 'Parcel Weight format',
				'configuration_value' => 'kgs',
				'configuration_description' => 'Are your store items weighted by grams or Kilos? (required so that we can pass the correct weight to the server).',
				'set_function' => 'zen_cfg_select_option(array(\'gms\', \'kgs\'), ',
			),
			$this->getModuleKeyTrunk().'_TYPES' => array(
				'configuration_title' => 'Shipping Methods for Australia Post',
				'configuration_value' => 'AUS_PARCEL_EXPRESS,AUS_PARCEL_REGULAR',
				'configuration_description' => 'Select the methods you wish to allow',
				'set_function' => 'zen_cfg_select_multioption(array(\'AUS_PARCEL_EXPRESS\',\'AUS_PARCEL_EXPRESS_SATCHEL_MEDIUM\',\'AUS_PARCEL_EXPRESS_PACKAGE_MEDIUM\',\'AUS_PARCEL_EXPRESS_SATCHEL_1KG\',\'AUS_PARCEL_REGULAR\',\'AUS_PARCEL_REGULAR_SATCHEL_MEDIUM\',\'AUS_PARCEL_REGULAR_PACKAGE_MEDIUM\',\'AUS_PARCEL_REGULAR_SATCHEL_1KG\',\'INT_PARCEL_COR_OWN_PACKAGING\',\'INT_PARCEL_EXP_OWN_PACKAGING\',\'INT_PARCEL_STD_OWN_PACKAGING\',\'INT_PARCEL_AIR_OWN_PACKAGING\'), ',
			)
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_ORIGIN_COUNTRY_CODE']['configuration_value'] = 'AU';
		return $ret;
	}



	function get_auspost_api( $pUrl, $pQueryParams ) {
		$ret = array();

		$ch = curl_init();
		$timeout = 60;
		// Calculate the final domestic parcel delivery price
		curl_setopt($ch, CURLOPT_URL, $pUrl.'?'.http_build_query( $pQueryParams ));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('AUTH-KEY: ' . $this->getModuleConfigValue( '_AUTHKEY' ) ));
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		if( $rawBody = curl_exec($ch) ) {
			$ret = json_decode( $rawBody, TRUE );
		}

		curl_close( $ch );

		return $ret;
	}

}

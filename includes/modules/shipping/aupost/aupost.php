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

 $Id: aupost.php,v2.2.1  Nov 2016

*/

// class constructor

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class aupost extends CommercePluginShippingBase {

	public function __construct() {
		parent::__construct();
		$this->title			= tra( 'Australia Post' );
		$this->description		= tra( 'Australia Post Parcel Service <p>You will need to register at the <a href="https://developers.auspost.com.au/" target="_new">Developer Program</a></p>' );
		$this->mTypesDomestic	= explode(', ', $this->getModuleConfigValue( '_TYPES_DOMESTIC' ));
		$this->icon				= 'shipping_aupost';
	}
/*
function __construct()
{
    global $order, $db, $template ;

    // disable only when entire cart is free shipping
    if (zen_get_shipping_enabled($this->code))  $this->enabled = ((MODULE_SHIPPING_AUPOST_STATUS == 'True') ? true : false);


    $this->code = 'aupost';
    $this->title = MODULE_SHIPPING_AUPOST_TEXT_TITLE ;
    $this->description = MODULE_SHIPPING_AUPOST_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_SHIPPING_AUPOST_SORT_ORDER;
    $this->icon = $template->get_template_dir('aupost.jpg', '' ,'','images/icons'). '/aupost.jpg';
    if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);
    $this->logo = $template->get_template_dir('aupost_logo.jpg', '','' ,'images/icons'). '/aupost_logo.jpg';
    $this->tax_class = MODULE_SHIPPING_AUPOST_TAX_CLASS;
    $this->tax_basis = 'Shipping' ;    // It'll always work this way, regardless of any global settings

     if (MODULE_SHIPPING_AUPOST_ICONS != "No" )
	 {
        if (zen_not_null($this->logo)) $this->title = zen_image($this->logo, $this->title) ;
    }

    $this->allowed_methods = explode(", ", MODULE_SHIPPING_AUPOST_TYPES1) ;
}
*/
// class methods
//////////////////////////////////////////////////////////////

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

			$auPostUri = "https://digitalapi.auspost.com.au/postage/parcel/domestic/service.json"; 

			$queryParams = array (
			  "from_postcode" => $pShipHash['origin']['postcode'],
			  "to_postcode" => $pShipHash['destination']['postcode'],
			  "length" => (BitBase::getParameter( $pShipHash, 'box_length', 22 ) * $unitConversion),
			  "width" => (BitBase::getParameter( $pShipHash, 'box_width', 16 ) * $unitConversion),
			  "height" => (BitBase::getParameter( $pShipHash, 'box_height', 7.7 ) * $unitConversion),
			  "weight" => $parcelWeight,
			  "service_code" => 'AUS_PARCEL_REGULAR'
			);

			$this->quotes = array('id' => $this->code, 'module' => $this->title);
			$methods = array() ;

			// Server query string //
			if( $jsonHash = $this->get_auspost_api( $auPostUri, $queryParams ) ) {
				foreach( $jsonHash['services']['service'] as $service ) {
					global $currencies;

					$costAud = (float)$service['price'];
					$costStore = $currencies->convert( $costAud, DEFAULT_CURRENCY, 'CAD' ) + (float)$this->getShipperHandling();
					if( in_array( $service['code'], $this->mTypesDomestic ) ) {
							$methods[] = array( 'id' => $service['code'],
												'title' => $service['name'],
												'cost' => $costStore,
												'code' => $service['code'],
												);
					}
				}
			}
		 // print_r($xml) ; exit ;
			/////  Initialise our quote array(s)
			
			$this->sortQuoteMethods( $methods );

			$quotes['methods'] = $methods;
		}

		return $quotes;
	}

	function _get_error_cost($dest_country) {
		
		$x = explode(',', MODULE_SHIPPING_AUPOST_COST_ON_ERROR) ;

		unset($_SESSION['aupostParcel']) ;  // don't cache errors.

		$cost = $dest_country == "AU" ?  $x[0]:$x[1] ;

		if ($cost == 0) {
			$this->enabled = FALSE ;
			unset($_SESSION['aupostQuotes']) ;
		} else {  
			$this->quotes = array('id' => $this->code, 'module' => 'Flat Rate'); 
		}

		return $cost;
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
			$this->getModuleKeyTrunk().'_RPP_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Regular parcels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee Regular parcels',
			),
			$this->getModuleKeyTrunk().'_PPS_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Prepaid Satchels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Prepaid Satchels.',
			),
			$this->getModuleKeyTrunk().'_PPSE_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Prepaid Satchels - Express',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Prepaid Express Satchels.',
			),
			$this->getModuleKeyTrunk().'_EXP_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Express parcels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Express parcels.',
			),
			$this->getModuleKeyTrunk().'_PLAT_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Platinum parcels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Platinum parcels.',
			),
			$this->getModuleKeyTrunk().'_PLATSATCH_HANDLING' => array(
				'configuration_title' => 'Handling Fee - Platinum Satchels',
				'configuration_value' => '0.00',
				'configuration_description' => 'Handling Fee for Platinum Satchels.',
			),
			$this->getModuleKeyTrunk().'_DIMS' => array(
				'configuration_title' => 'Default Parcel Dimensions',
				'configuration_value' => '10,10,2',
				'configuration_description' => 'Default Parcel dimensions (in cm). Three comma seperated values (eg 10,10,2 = 10cm x 10cm x 2cm). These are used if the dimensions of individual products are not set',
			),
			$this->getModuleKeyTrunk().'_COST_ON_ERROR' => array(
				'configuration_title' => 'Cost on Error',
				'configuration_value' => '25',
				'configuration_description' => 'If an error occurs this Flat Rate fee will be used.</br> A value of zero will disable this module on error.',
			),
			$this->getModuleKeyTrunk().'_TARE' => array(
				'configuration_title' => 'Tare percent.',
				'configuration_value' => '10',
				'configuration_description' => 'Add this percentage of the items total weight as the tare weight. (This module ignores the global settings that seems to confuse many users. 10% seems to work pretty well.).',
			),
			$this->getModuleKeyTrunk().'_HIDE_HANDLING' => array(
				'configuration_title' => 'Hide Handling Fees?',
				'configuration_value' => 'Yes',
				'configuration_description' => 'The handling fees are still in the total shipping cost but the Handling Fee is not itemised on the invoice.',
				'set_function' => 'zen_cfg_select_option(array(\'Yes\', \'No\'), ',
			),
			$this->getModuleKeyTrunk().'_WEIGHT_FORMAT' => array(
				'configuration_title' => 'Parcel Weight format',
				'configuration_value' => 'kgs',
				'configuration_description' => 'Are your store items weighted by grams or Kilos? (required so that we can pass the correct weight to the server).',
				'set_function' => 'zen_cfg_select_option(array(\'gms\', \'kgs\'), ',
			),
			$this->getModuleKeyTrunk().'_DEBUG' => array(
				'configuration_title' => 'Enable Debug?',
				'configuration_value' => 'No',
				'configuration_description' => 'See how parcels are created from individual items.</br>Shows all methods returned by the server, including possible errors. <strong>Do not enable in a production environment</strong>',
				'set_function' => 'zen_cfg_select_option(array(\'No\', \'Yes\'), ',
			),
			$this->getModuleKeyTrunk().'_TYPES_DOMESTIC' => array(
				'configuration_title' => 'Shipping Methods for Australia',
				'configuration_value' => 'AUS_PARCEL_EXPRESS,AUS_PARCEL_REGULAR',
				'configuration_description' => 'Select the methods you wish to allow',
				'set_function' => 'zen_cfg_select_multioption(array(\'AUS_PARCEL_EXPRESS\',\'AUS_PARCEL_EXPRESS_SATCHEL_MEDIUM\',\'AUS_PARCEL_EXPRESS_PACKAGE_MEDIUM\',\'AUS_PARCEL_EXPRESS_SATCHEL_1KG\',\'AUS_PARCEL_REGULAR\',\'AUS_PARCEL_REGULAR_SATCHEL_MEDIUM\',\'AUS_PARCEL_REGULAR_PACKAGE_MEDIUM\',\'AUS_PARCEL_REGULAR_SATCHEL_1KG\'), ',
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

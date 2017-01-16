<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
/*
  $Id: purolator.php,v 5.2 Jan 24, 2011

  Released under the GNU General Public License
  Updated to PHP 5 Sept /2010
  Updated to Zen Cart v1.3.0 April 9/2006
  Updated to Zen Cart v1.3.8a May 2010 by Dave Bakker
  geek4hire@gmail.com
  Props to bitsmith2k for helping me solve the 1.39h issue with the _xmlentities function
 */

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class purolator extends CommercePluginShippingBase {
  var $types;
  var $boxcount;


	function __construct() {
		global $order, $gBitDb, $gBitLanguage;
		parent::__construct();

		$this->title = tra( 'Purolator e-Ship' );
		$this->description = tra( 'Purolator Parcel Service<p><strong>eShip Profile Information </strong>can be obtained at http://eship.purolator.com' );
		$this->icon = 'shipping_purolator';
		if( $this->isEnabled() ) {
			$this->language = (in_array( $gBitLanguage->getLanguage(), array('en' , 'fr'))) ? strtolower( $gBitLanguage->getLanguage() ) : MODULE_SHIPPING_CANADAPOST_LANGUAGE;
			if ( ((int) MODULE_SHIPPING_PUROLATOR_ZONE > 0)) {
				$this->uri = MODULE_SHIPPING_PUROLATOR_SERVERURI;
				$this->location = MODULE_SHIPPING_PUROLATOR_SERVERLOC;
				$this->key = MODULE_SHIPPING_PUROLATOR_KEY;
				$this->pass = MODULE_SHIPPING_PUROLATOR_PASS;
				$this->acct_num = MODULE_SHIPPING_PUROLATOR_ACCTNUM;
				$this->packaging = MODULE_SHIPPING_PUROLATOR_PACKAGING;
				$this->sort_order = MODULE_SHIPPING_PUROLATOR_SORT_ORDER;
				$this->handling_fee = MODULE_SHIPPING_PUROLATOR_HANDLING;
				$this->items_qty = 0;
				$this->items_price = 0;
				$this->tax_class = MODULE_SHIPPING_PUROLATOR_TAX_CLASS;
				$this->tax_basis = MODULE_SHIPPING_PUROLATOR_TAX_BASIS;
				$check_flag = false;
				$check = $gBitDb->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_PUROLATOR_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
				if ($check_flag == false)
					$this->enabled = false;
			}
		}

	} // end constructor purolator

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	function quote( $pShipHash = array() )
	{
		global $order, $handling_cp;
		$shippingWeight = (!empty( $pShipHash['shipping_weight'] ) && $pShipHash['shipping_weight'] > 0.1 ? $pShipHash['shipping_weight'] : 0.1);
		$shippingNumBoxes = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

		// will round to 2 decimals 9.112 becomes 9.11 thus a product can be 0.1 of a KG
		$shippingWeight = round($shippingWeight, 2);

		$ret = array();

		if ($shippingWeight < MODULE_SHIPPING_PUROLATOR_MAXWEIGHT ) {
			$ret = array( 
				'id' => $this->code, 
				'module' => $this->title, 
				'icon' => $this->icon,
			);

			$country_name = zen_get_countries(STORE_COUNTRY, true);
			$this->_purolatorOrigin(SHIPPING_ORIGIN_ZIP, $country_name['countries_iso_code_2']);
			if (strlen($order->delivery['state']) > 2 && $order->delivery['zone_id'] > 0) {
				$state_name = zen_get_zone_code($order->delivery['country_id'], $order->delivery['zone_id'], '');
				$order->delivery['state'] = $state_name;
			}
			$this->_purolatorDest(
					$order->delivery['city'],
					$order->delivery['state'],
					$order->delivery['country']['countries_iso_code_2'],
					$order->delivery['postcode']
			);


			/** Purpose : Creates a SOAP Client in Non-WSDL mode with the appropriate authentication and
			 *		   header information
			 * */

			require_once( dirname( __FILE__ ).'/nusoap/nusoap.php');

			//Set the parameters for the Non-WSDL mode SOAP communication with your Development/Production credentials
			$this->client = new nusoap_client(HTTP_SERVER . "/EstimatingService.wsdl", 'wsdl');
			$this->client->setCredentials($this->key, $this->pass, 'basic');
			//if($this->client->getError()) echo '<!--Auth Error: '.$this->client->getError().'-->'; //commented, used to test for authorisation errors
			//Define the SOAP Envelope Headers
			$handheader = '<ns1:RequestContext xmlns:ns1="' . $this->uri . '"><ns1:Version>1.0</ns1:Version><ns1:Language>' . $this->language . '</ns1:Language><ns1:GroupID>xxx</ns1:GroupID><ns1:RequestReference>Rating Example</ns1:RequestReference></ns1:RequestContext>';
			//Apply the SOAP Header to your client
			$this->client->setHeaders($handheader);
			//if($this->client->getError()) echo PHP_EOL.'<!--Header Error: '.$this->client->getError().'-->'. PHP_EOL;  //commented, used to test for header errors
			$params = array(
				"BillingAccountNumber" => $this->acct_num,
				"SenderPostalCode" => $this->_purolatorOriginPostalCode,
				"ReceiverAddress" => array(
					// "City" => $this->dest_city, // removed to stop failures on array returns.
					"City" => '', // sadly, Puro can return an array for one Postal
					"Province" => $this->dest_province,
					"Country" => $this->dest_country,
					"PostalCode" => $this->dest_zip),
				"PackageType" => $this->packaging,
				"TotalWeight" => array(
					"Value" => $shippingWeight, "WeightUnit" => "lb")
			);
			//Execute the request and capture the response
			$this->response = $this->client->call('GetQuickEstimate', array('GetQuickEstimateRequest' => $params));
			//  echo PHP_EOL.'<!--'; print_r($params); echo '-->'. PHP_EOL; // for testing. Prints out the entire send in a comment.
			//start error checkin module (ya, I know it's overkill)
			/*
			  if ($this->client->fault) {
			  echo '<!--Fault: ';
			  print_r($result);
			  echo '-->';
			  } else {
			  // Check for errors
			  $err = $this->client->getError();
			  if ($err) {
			  // Display the error
			  echo '<!--Error: ' . $err . '-->';
			  } else {
			  //Success! Display the result
			  echo PHP_EOL.'<!--Success: ';
			  print_r($this->response);
			  echo '-->'. PHP_EOL;
			  }
			  }
			  if($this->client->getError()) {
			  echo '<!-- Debug:';
			  print_r($this->client->getDebug());
			  echo '-->';
			  }
			 */
			// end error checking module
			if( !$this->client->fault && $purolatorQuote = $this->_parserResult($this->response) ) {
					$ret['weight'] = $shippingWeight. ' Lb / '. round( $shippingWeight / 2.2 ) .' Kg';
						$methods = array();
					for ($i = 0; $i < sizeof($purolatorQuote); $i++) {
						list($type, $cost) = each($purolatorQuote[$i]);
						$type = html_entity_decode($type);
						if ($method == '' || $method == $type) {
							$methods[] = array('id' => $type , 'title' => $type , 'cost' => (MODULE_SHIPPING_PUROLATOR_HANDLING + $cost));
						}
					}
					if ($this->tax_class > 0) {
						$ret['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
					}
					$ret['methods'] = $methods;
				} else {
					$errmsg = tra( 'An unknown error occured with the Purolator shipping calculations.' );
				}

				$errmsg .= ' '.tra( 'If you prefer to use Canada Post as your shipping method, please <a href="mailto:'.STORE_OWNER_EMAIL_ADDRESS.'">send us an email</a>.' );
				$ret['error'] = $errmsg;
	   }
		return $ret;
	} // end function quote

	/**
	 * check status of module
	 *
	 * @return boolean
	 */
	public function check() {
		global $gBitDb;
		if (!isset($this->_check)) {
			$check_query = $gBitDb->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_PUROLATOR_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	} // end function check

	/**
	 * Install this module
	 * Should probably add a return code that says if it's successful, but that doesn't seem
	 * to be implemented yet in Zencart.
	 */
	public function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Purolator Shipping', 'MODULE_SHIPPING_PUROLATOR_STATUS', 'True', 'Do you want to offer Purolator shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Purolator datatypes URI', 'MODULE_SHIPPING_PUROLATOR_SERVERURI', 'http://purolator.com/pws/datatypes/v1', 'Purolator datatypes URI. <br>(default: http://purolator.com/pws/datatypes/v1)', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Purolator Server URI', 'MODULE_SHIPPING_PUROLATOR_SERVERLOC', 'https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx', 'Purolator server Location. <br>(default: https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx)', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Purolator Account Number', 'MODULE_SHIPPING_PUROLATOR_ACCTNUM', '', 'Purolator account number', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Packaging', 'MODULE_SHIPPING_PUROLATOR_PACKAGING', 'CustomerPackaging', 'What packaging will you be using?', '6', '0', 'zen_cfg_select_option(array(\'ExpressEnvelope\', \'ExpressPack\', \'ExpressBox\',\'CustomerPackaging\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Selected Language-optional', 'MODULE_SHIPPING_PUROLATOR_LANGUAGE', 'en', 'Purolator supports two languages:<br><strong>en</strong>-english<br><strong>fr</strong>-french.', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Your Purolator Production Key', 'MODULE_SHIPPING_PUROLATOR_KEY', '', 'Purolator Production Key.', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Your Purolator Production Password', 'MODULE_SHIPPING_PUROLATOR_PASS', '0', 'Purolator Production Password.', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_PUROLATOR_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_PUROLATOR_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_PUROLATOR_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Turnaround Time', 'MODULE_SHIPPING_PUROLATOR_TURNAROUND', '0', 'Add the hours turnaround per shipment', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum Weight', 'MODULE_SHIPPING_PUROLATOR_MAXWEIGHT', '140', 'Maximum weight (Lb)', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Charge per box', 'MODULE_SHIPPING_PUROLATOR_HANDLING', '0', 'Add the following handling fee per box', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_PUROLATOR_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
		global $sniffer;
		if (method_exists($sniffer, 'field_type')) {
			if (!$sniffer->field_exists(TABLE_PRODUCTS, 'products_weight_type'))
				$gBitDb->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_weight_type ENUM('lbs','kgs') NOT NULL default 'kgs' after products_weight");
		}
	} //end function install

	/**
	 * Removes this module
	 *  Again, should have a return function that checks if it was successful.
	 */
	public function remove() {
		global $gBitDb;
		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	} // end function remove

	/**
	 * Build array of keys used for installing/managing this module
	 *
	 * @return array
	 */
	public function keys() {
		return array('MODULE_SHIPPING_PUROLATOR_STATUS',
			'MODULE_SHIPPING_PUROLATOR_ACCTNUM',
			'MODULE_SHIPPING_PUROLATOR_SERVERURI',
			'MODULE_SHIPPING_PUROLATOR_SERVERLOC',
			'MODULE_SHIPPING_PUROLATOR_LANGUAGE',
			'MODULE_SHIPPING_PUROLATOR_KEY',
			'MODULE_SHIPPING_PUROLATOR_PASS',
			'MODULE_SHIPPING_PUROLATOR_TAX_CLASS',
			'MODULE_SHIPPING_PUROLATOR_TAX_BASIS',
			'MODULE_SHIPPING_PUROLATOR_ZONE',
			'MODULE_SHIPPING_PUROLATOR_PACKAGING',
			'MODULE_SHIPPING_PUROLATOR_HANDLING',
			'MODULE_SHIPPING_PUROLATOR_MAXWEIGHT',
			'MODULE_SHIPPING_PUROLATOR_TURNAROUND',
			'MODULE_SHIPPING_PUROLATOR_SORT_ORDER');
	} // end function keys

	private function _purolatorOrigin($postal, $country) {
		$this->_purolatorOriginPostalCode = str_replace(' ', '', $postal);
		$this->_purolatorOriginCountryCode = $country;
	}//end function _purolatorOrigin

	private function _purolatorDest($dest_city, $dest_province, $dest_country, $dest_zip) {
		//$this->dest_city = $dest_city; //disabled currently as unnecessary and can fail on some p-codes
		$this->dest_city = '';
		$this->dest_province = $dest_province;
		$this->dest_country = $dest_country;
		$this->dest_zip = str_replace(' ', '', $dest_zip);
	} // end function _purolatorDest


	/*
	  Parser XML message returned by purolator server.
	 * @param array $response
	 * @return array
	 */
	private function _parserResult($response) {
		$index = 0;
		$aryProducts = false;
		// echo '<!-- ??'; print_r($response['ShipmentEstimates']['ShipmentEstimate']); echo '-->'; //prints estimates in a comment
		if ($response && $response['ShipmentEstimates']['ShipmentEstimate']) {
			if (array_key_exists('0', $response['ShipmentEstimates']['ShipmentEstimate'])) {
				//Loop through each Service returned and display the ID and TotalPrice
				foreach ($response['ShipmentEstimates']['ShipmentEstimate'] as $estimate) {
					$aryProducts[$index] = array($estimate['ServiceID'] . ', ' . $earliestDelivery => $estimate['TotalPrice']);
					$aryProducts[$index] = array($estimate['ServiceID'] => $estimate['TotalPrice']);
					$index++;
				}
			} else { //we only have 1 option
				$estimate = $response['ShipmentEstimates']['ShipmentEstimate'];
				$aryProducts[] = array($estimate['ServiceID'] => $estimate['TotalPrice']);
			}
		} else if ($response['ResponseInformation']['Errors']) { // if there's errors, cycle through the failure codes.
			$error = $response['ResponseInformation']['Errors']['Error']['Code'];
			$code = $response['ResponseInformation']['Errors']['Error']['Description'];
			switch ($error) {
				case 1100674:  // I left one in here so you could see how it looks. There's like 400 of them, potentially.
					$f = explode(' ', $response['ResponseInformation']['Errors']['Error']['Description']);
					$a = trim(substr($f[7], 0, -1));
					$b = trim(substr($f[8], 0, -1));
					$c = tra( 'The Postal Code given is only good for the city:' ). ' ' . $a . ' ' . $b . '<a href="'.BITCOMMERCE_PKG_URL.'?main_page=address_book">'. tra( 'Please adjust your customer information' ).'</a>';
					$aryProducts = $c;
					break;
			}
		}
		if ($aryProducts)
			return $aryProducts;
		else
			return false;
	} //end function _parserResult
	 function _xmlentities($string, $quote_style = ENT_QUOTES)
  {
	static $trans;
	if (! isset($trans))
	{
	  $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
	  foreach ($trans as $key => $value)
		$trans[$key] = '&#' . ord($key) . ';';
		// dont translate the '&' in case it is part of &xxx;
	  $trans[chr(38)] = '&';
	}
	// after the initial translation, _do_ map standalone '&' into '&#38;'
	return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/", "&#38;", strtr($string, $trans));
  }
} //end class purolator
?>

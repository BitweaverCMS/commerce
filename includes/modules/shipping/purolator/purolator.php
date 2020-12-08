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

	function __construct() {
		parent::__construct();
		$this->title = tra( 'Purolator e-Ship' );
		$this->description = tra( 'Purolator Parcel Service<p><strong><a href="http://eship.purolator.com">eShip Profile</a> required.' );
		if( $this->isEnabled() ) {
			global $gBitLanguage;
			$this->language = (in_array( $gBitLanguage->getLanguage(), array('en' , 'fr'))) ? strtolower( $gBitLanguage->getLanguage() ) : MODULE_SHIPPING_PUROLATOR_LANGUAGE;
			$this->uri = MODULE_SHIPPING_PUROLATOR_SERVERURI;
			$this->location = MODULE_SHIPPING_PUROLATOR_SERVERLOC;
			$this->key = MODULE_SHIPPING_PUROLATOR_KEY;
			$this->pass = MODULE_SHIPPING_PUROLATOR_PASS;
			$this->acct_num = MODULE_SHIPPING_PUROLATOR_ACCTNUM;
			$this->packaging = MODULE_SHIPPING_PUROLATOR_PACKAGING;
			$this->handling_fee = MODULE_SHIPPING_PUROLATOR_HANDLING;
		}

	} // end constructor purolator


	protected function isEligibleShipper( $pShipHash ) {
		$ret = array();
		if( $pShipHash['shipping_weight_box'] < MODULE_SHIPPING_PUROLATOR_MAXWEIGHT ) {
			$ret = parent::isEligibleShipper( $pShipHash );
		}
		return $ret;
	}

	function maxShippingWeight() {
		return (float)$this->getConfig( 'MODULE_SHIPPING_PUROLATOR_MAXWEIGHT' );
	}

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {

			if (strlen($pShipHash['destination']['state']) > 2 && $pShipHash['destination']['zone_id'] > 0) {
				$state_name = zen_get_zone_code($pShipHash['destination']['country_id'], $pShipHash['destination']['zone_id'], '');
				$pShipHash['destination']['state'] = $state_name;
			}

			/** Purpose : Creates a SOAP Client in Non-WSDL mode with the appropriate authentication and
			 *		   header information
			 * */

			require_once( dirname( __FILE__ ).'/nusoap/nusoap.php');

			//Set the parameters for the Non-WSDL mode SOAP communication with your Development/Production credentials
			$this->client = new nusoap_client(BITCOMMERCE_PKG_URI . "/EstimatingService.wsdl", 'wsdl');
			$this->client->setCredentials($this->key, $this->pass, 'basic');
			//if($this->client->getError()) echo '<!--Auth Error: '.$this->client->getError().'-->'; //commented, used to test for authorisation errors
			//Define the SOAP Envelope Headers
			$handheader = '<ns1:RequestContext xmlns:ns1="' . $this->uri . '"><ns1:Version>1.0</ns1:Version><ns1:Language>' . $this->language . '</ns1:Language><ns1:GroupID>xxx</ns1:GroupID><ns1:RequestReference>Rating Example</ns1:RequestReference></ns1:RequestContext>';
			//Apply the SOAP Header to your client
			$this->client->setHeaders($handheader);
			//if($this->client->getError()) echo PHP_EOL.'<!--Header Error: '.$this->client->getError().'-->'. PHP_EOL;  //commented, used to test for header errors
			$params = array(
				"BillingAccountNumber" => $this->acct_num,
				"SenderPostalCode" => $pShipHash['origin']['postcode'],
				"ReceiverAddress" => array(
					// "City" => $pShipHash['destination']['city, // removed to stop failures on array returns.
					"City" => '', // sadly, Puro can return an array for one Postal
					"Province" => $pShipHash['destination']['state'],
					"Country" => $pShipHash['destination']['countries_name'],
					"PostalCode" => $pShipHash['destination']['postcode']),
				"PackageType" => $this->packaging,
				"TotalWeight" => array(
					"Value" => $pShipHash['shipping_weight_total'], "WeightUnit" => "lb")
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
			if( $this->client->fault ) {
				$qutoes = array();
			} elseif( $purolatorQuote = $this->_parserResult($this->response) ) {
				$quotes['weight'] = $shippingWeight. ' Lb / '. round( $shippingWeight / 2.2 ) .' Kg';
					$methods = array();
				for ($i = 0; $i < sizeof($purolatorQuote); $i++) {
					list($type, $cost) = each($purolatorQuote[$i]);
					$type = html_entity_decode($type);
					if ($method == '' || $method == $type) {
						$methods[] = array('id' => $type , 'title' => $type , 'cost' => (MODULE_SHIPPING_PUROLATOR_HANDLING + $cost));
					}
				}
				if ($this->tax_class > 0) {
					$quotes['tax'] = zen_get_tax_rate($this->tax_class, $pShipHash['destination']['id'], $pShipHash['destination']['zone_id']);
				}
				$quotes['methods'] = $methods;
			} else {
				$errmsg = tra( 'An unknown error occured with the Purolator shipping calculations.' );
				$quotes['error'] = $errmsg;
			}
		}
		return $quotes;
	} // end function quote

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

	function _xmlentities($string, $quote_style = ENT_QUOTES) {
		static $trans;
		if( !isset( $trans ) ) {
			$trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
			foreach ($trans as $key => $value)
			$trans[$key] = '&#' . ord($key) . ';';
			// dont translate the '&' in case it is part of &xxx;
			$trans[chr(38)] = '&';
		}
		// after the initial translation, _do_ map standalone '&' into '&#38;'
		return preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/", "&#38;", strtr($string, $trans));
	}


	protected function config() {
		$i = 3;
		$ret = array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_SERVERURI' => array(
				'configuration_title' => 'Enter Purolator datatypes URI',
				'configuration_value' => 'http://purolator.com/pws/datatypes/v1',
				'configuration_description' => 'Purolator datatypes URI.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_SERVERLOC' => array(
				'configuration_title' => 'Enter Purolator Server URI',
				'configuration_value' => 'https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx',
				'configuration_description' => 'Purolator server Location.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_ACCTNUM' => array(
				'configuration_title' => 'Enter Purolator Account Number',
				'configuration_description' => 'Purolator account number',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_PACKAGING' => array(
				'configuration_title' => 'Packaging',
				'configuration_value' => 'CustomerPackaging',
				'configuration_description' => 'What packaging will you be using?',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('ExpressEnvelope', 'ExpressPack', 'ExpressBox','CustomerPackaging'),",
			),
			$this->getModuleKeyTrunk().'_LANGUAGE' => array(
				'configuration_title' => 'Enter Selected Language-optional',
				'configuration_value' => 'en',
				'configuration_description' => 'Purolator supports two languages:<br><strong>en</strong>-english<br><strong>fr</strong>-french.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_KEY' => array(
				'configuration_title' => 'Enter Your Purolator Production Key',
				'configuration_description' => 'Purolator Production Key.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_PASS' => array(
				'configuration_title' => 'Enter Your Purolator Production Password',
				'configuration_description' => 'Purolator Production Password.',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_TURNAROUND' => array(
				'configuration_title' => 'Turnaround Time',
				'configuration_description' => 'Add the hours turnaround per shipment',
				'sort_order' => $i++,
			),
			$this->getModuleKeyTrunk().'_MAXWEIGHT' => array(
				'configuration_title' => 'Maximum Weight',
				'configuration_value' => '140',
				'configuration_description' => 'Maximum weight (Lb)',
				'sort_order' => $i++,
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_ORIGIN_COUNTRY_CODE']['configuration_value'] = 'CA';
		return $ret;
	}
} //end class purolator

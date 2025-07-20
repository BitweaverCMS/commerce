<?php
/*
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

	XML connection method with Canada Post.
	Before using this module, you should open a Canada Post SellOnline Account. Visit www.canadapost.ca for details.
	You will need to put your CPC ID into the admin settings in order to get rates for your account.

	Released under the GNU General Public License

	Adapted from GPL code by Copyright (c) 2002,2003 Kelvin Zhang (kelvin@syngear.com), Kenneth Wang (kenneth@cqww.net) 2002.11.12, LXWXH added by Tom St.Croix (management@betterthannature.com)

	Updated for Zen Cart v1.3.0 April 9/2006
	Lettermail table rates added 6 May 2008 by Gord Dimitrieff (gord@aporia-records.com)
	Updated for Zen Cart v1.5.0 July 2012
	
	v1.6.1 Updated Sept 2019 for SellOnline HTTP connections (no longer uses port 30000, and now uses CURL to connect via http/s)

	Ref https://qa-sellonline.canadapost.ca/DevelopersResources/protocolV3/index.html
*/

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginShippingBase.php' );

class canadapost extends CommercePluginShippingBase {

	var $mServiceCodes = array(
		'DOM.RP' => 'Regular Parcel',
		'DOM.EP' => 'Expedited Parcel',
		'DOM.XP' => 'Xpresspost',
		'DOM.XP.CERT' => 'Xpresspost Certified',
		'DOM.PC' => 'Priority',
		'DOM.LIB' => 'Library Materials',
		'USA.EP' => 'Expedited Parcel USA',
		'USA.PW.ENV' => 'Priority Worldwide Envelope USA',
		'USA.PW.PAK' => 'Priority Worldwide pak USA',
		'USA.PW.PARCEL' => 'Priority Worldwide Parcel USA',
		'USA.SP.AIR' => 'Small Packet USA Air',
		'USA.TP' => 'Tracked Packet – USA',
		'USA.TP.LVM' => 'Tracked Packet – USA (LVM) (large volume mailers)',
		'USA.XP' => 'Xpresspost USA',
		'INT.XP' => 'Xpresspost International',
		'INT.IP.AIR' => 'International Parcel Air',
		'INT.IP.SURF' => 'International Parcel Surface',
		'INT.PW.ENV' => 'Priority Worldwide Envelope Int’l',
		'INT.PW.PAK' => 'Priority Worldwide pak Int’l',
		'INT.PW.PARCEL' => 'Priority Worldwide parcel Int’l',
		'INT.SP.AIR' => 'Small Packet International Air',
		'INT.SP.SURF' => 'Small Packet International Surface',
		'INT.TP' => 'Tracked Packet – International',
	);

	public function __construct() {
		parent::__construct();
		$this->title			= tra( 'Canada Post' );
		$this->description		= tra( 'Canada Post Parcel Service <p>You will need a Account Number, Username and Password from the <a href="https://www.canadapost.ca/cpotools/apps/drc/home?execution=e1s1">Developer Program</a></p>' );
		$this->booticon				= 'fab fa-canadian-maple-leaf';
	}

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	public function quote( $pShipHash ) {
		global $currencies;
		$quotes = array();
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			$methods = array();
			list( $responseCode, $canadapostQuote ) = $this->_canadapostGetQuote( $pShipHash );

			// Example of using SimpleXML to parse xml response
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string('<root>' . preg_replace('/<\?xml.*\?>/','',$canadapostQuote) . '</root>');

			$quotes['error'] = '';
			if ($xml->{'price-quotes'} ) {
				$priceQuotes = $xml->{'price-quotes'}->children('http://www.canadapost.ca/ws/ship/rate-v4');
				if( $priceQuotes->{'price-quote'} ) {
					$allowedTypes = $this->getAllowedServiceTypes();
					foreach ( $priceQuotes as $priceQuote ) {  
						$serviceCode = (string)$priceQuote->{'service-code'};
						if( empty( $allowedTypes ) || in_array( $serviceCode, $allowedTypes ) || (!empty( $pShipHash['method'] ) && ($pShipHash['method'] == $serviceCode)) ) {
							$transitTime = '';
							if( $transitDays = (string)$priceQuote->{'service-standard'}->{'expected-transit-time'} ) {
								$transitTime = $transitDays.' '.($transitDays > 1 ? tra( 'Days' ) : tra( 'Day' ));
							}
							$costCad = (float)$priceQuote->{'price-details'}->{'due'};
							$costStore = $currencies->convert( (float)$priceQuote->{'price-details'}->{'due'}, DEFAULT_CURRENCY, 'CAD' ) + (float)$this->getShipperHandling();
							$methods[] = array(
											'id' => (string)$priceQuote->{'service-code'},
											'title' => (string)$priceQuote->{'service-name'},
											'cost' => $costStore,
											'code' => (string)$priceQuote->{'service-code'},
											'transit_days' => $transitDays,
											'transit_time' => $transitTime,
											'delivery_date' => (string)$priceQuote->{'service-standard'}->{'expected-delivery-date'},
										);
						}
					}
				}
			} else {
				$quotes['error'] .= 'Failed loading XML <ul>';
				foreach(libxml_get_errors() as $error) {
					$quotes['error'] .= '<li>' . $error->message . '</li>';
				}
				$quotes['error'] .= '</ul>';
			}
			if ($xml->{'messages'} ) {					
				$messages = $xml->{'messages'}->children('http://www.canadapost.ca/ws/messages');		
				foreach ( $messages as $message ) {
					$quotes['error'] .= $message->description . '(' . $message->code . ')';
				}
			}

			if( !empty( $methods ) ) {
				if ($this->tax_class > 0) {
					$quotes['tax'] = zen_get_tax_rate($this->tax_class, $pShipHash['destination']['countries_id'], $pShipHash['destination']['zone_id']);
				}
				$this->sortQuoteMethods( $methods );
				$quotes['methods'] = $methods;
			} else {
				if( $responseCode != 200 ) {
					$errmsg = $canadapostQuote;
				} elseif ($canadapostQuote != false) {
					$errmsg = tra( 'No shipping options are available for this delivery address using this shipping service.' );
				} else {
					$errmsg = tra( 'An unknown error occured with the Canada Post shipping calculations.' );
				}
				$quotes['error'] = $errmsg;
			}
		}

		return $quotes;
	}

	/**
	 * using HTTP/POST send message to canada post server
	 * (will timeout after 3 seconds, so that customers aren't left wondering what's going on in case the CP server is slow or down unexpectedly)
	 */
	private function _sendToHost( $pXmlRequest ) {
		$response = FALSE;

		$username = $this->getModuleConfigValue( '_USERNAME' ); 
		$password = $this->getModuleConfigValue( '_PASSWORD' );

		// REST URL
		$service_url = 'https://soa-gw.canadapost.ca/rs/ship/price';

		$curl = curl_init($service_url); // Create REST Request
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
//		curl_setopt($curl, CURLOPT_CAINFO, realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/../../../third-party/cert/cacert.pem');
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $pXmlRequest);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . $password);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.cpc.ship.rate-v4+xml', 'Accept: application/vnd.cpc.ship.rate-v4+xml'));
		$response = curl_exec($curl); // Execute REST Request
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$error    = curl_error($curl);
		$errno    = curl_errno($curl);
		curl_close($curl);

		if ($errno > 0) {
			$response = '<?xml version="1.0" ?><eparcel><error><statusMessage>' . tra( 'Cannot reach Canada Post Server. You may reload this page in your browser to try again.' ). ($errno != 0 ? '<br /><strong>' . $errno . ' ' . $error . '</strong>' : '') . '</statusMessage></error></eparcel>';
		}

		return array( $httpcode, $response );
	}

	/**
	 * assemble and submit quote request
	 */
	private function _canadapostGetQuote( $pShipHash ) {

		global $gBitCustomer;
		$quoteLang = (in_array( $gBitCustomer->getLanguage(), array('en' , 'fr'))) ? $gBitCustomer->getLanguage() : MODULE_SHIPPING_CANADAPOST_LANGUAGE;

		$weightConverted = round( $pShipHash['shipping_weight_total'] * ($pShipHash['weight_unit'] == 'lb' ? (453597 / 1000000) : 1.0), 2 );

		if( $customerNum = $this->getModuleConfigValue( '_CUSTOMER_NUMBER' ) ) {
			$xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v4">
  <customer-number>'.$customerNum.'</customer-number>
  <expected-mailing-date>'.$this->getShippingDate( $pShipHash ).'</expected-mailing-date>
  <parcel-characteristics>
    <weight>'.$weightConverted.'</weight>
  </parcel-characteristics>
  <origin-postal-code>'.preg_replace( '/[^0-9A-Z]/', '', strtoupper( $pShipHash['origin']['postcode'] ) ).'</origin-postal-code>
  <destination>
    <domestic>
      <postal-code>'.preg_replace( '/[^0-9A-Z]/', '', strtoupper( $pShipHash['destination']['postcode'] ) ).'</postal-code>
    </domestic>
  </destination>
</mailing-scenario>
';
		}

		return $this->_sendToHost( $xmlRequest );
	}

	/**
	 * Parser XML message returned by canada post server.
	 */
	private function _parserResult($resultXML) {
		$statusMessage = substr($resultXML, strpos($resultXML, "<statusMessage>") + strlen("<statusMessage>"), strpos($resultXML, "</statusMessage>") - strlen("<statusMessage>") - strpos($resultXML, "<statusMessage>"));
		//print "message = $statusMessage";
		$cphandling = substr($resultXML, strpos($resultXML, "<handling>") + strlen("<handling>"), strpos($resultXML, "</handling>") - strlen("<handling>") - strpos($resultXML, "<handling>"));
		$this->handling_cp = $cphandling;
		if( $statusMessage == 'OK' ) {
			$packing_xml = $this->parsetag("packing", $resultXML); //pull out the packaging info
			$strProduct = substr($resultXML, strpos($resultXML, "<product id=") + strlen("<product id=>"), strpos($resultXML, "</product>") - strlen("<product id=>") - strpos($resultXML, "<product id="));
			$index = 0;
			$aryProducts = false;
			while (strpos($resultXML, "</product>")) {
				$cpnumberofboxes = substr_count($resultXML, "<expediterWeight");
				$this->boxCount = $cpnumberofboxes;
				$name = substr($resultXML, strpos($resultXML, "<name>") + strlen("<name>"), strpos($resultXML, "</name>") - strlen("<name>") - strpos($resultXML, "<name>"));
				$rate = substr($resultXML, strpos($resultXML, "<rate>") + strlen("<rate>"), strpos($resultXML, "</rate>") - strlen("<rate>") - strpos($resultXML, "<rate>"));
				$shippingDate = substr($resultXML, strpos($resultXML, "<shippingDate>") + strlen("<shippingDate>"), strpos($resultXML, "</shippingDate>") - strlen("<shippingDate>") - strpos($resultXML, "<shippingDate>"));
				$deliveryDate = substr($resultXML, strpos($resultXML, "<deliveryDate>") + strlen("<deliveryDate>"), strpos($resultXML, "</deliveryDate>") - strlen("<deliveryDate>") - strpos($resultXML, "<deliveryDate>"));
				$deliveryDayOfWeek = substr($resultXML, strpos($resultXML, "<deliveryDayOfWeek>") + strlen("<deliveryDayOfWeek>"), strpos($resultXML, "</deliveryDayOfWeek>") - strlen("<deliveryDayOfWeek>") - strpos($resultXML, "<deliveryDayOfWeek>"));
				$nextDayAM = substr($resultXML, strpos($resultXML, "<nextDayAM>") + strlen("<nextDayAM>"), strpos($resultXML, "</nextDayAM>") - strlen("<nextDayAM>") - strpos($resultXML, "<nextDayAM>"));
				$packingID = substr($resultXML, strpos($resultXML, "<packingID>") + strlen("<packingID>"), strpos($resultXML, "</packingID>") - strlen("<packingID>") - strpos($resultXML, "<packingID>"));
				$aryProducts[$index] = array($name . ', ' . $deliveryDate => $rate);
				$index ++;
				$resultXML = substr($resultXML, strpos($resultXML, "</product>") + strlen("</product>"));
			}
			return $aryProducts;
		} else {
			if (strpos($resultXML, "<error>")) {
				return $statusMessage;
			} else {
				return false;
			}
		}
	}


	private function parsetag($tag, $string) {
		$start = strpos($string, "<" . $tag . ">");
		if (! $start) return FALSE;
		$start = $start + strlen("<" . $tag . ">");
		$end = (strpos($string, "</" . $tag . ">"));
		$num = ($end - $start);
		$val = substr($string, $start, $num);
		return $val;
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
			$this->getModuleKeyTrunk().'_CANADAPOST_LANGUAGE' => array(
				'configuration_title' => 'Selected Language',
				'configuration_value' => 'en',
				'configuration_description' => 'Canada Post supports English and French.',
				'set_function' => "zen_cfg_select_option(array('en', 'fr'), ",
			),
			$this->getModuleKeyTrunk().'_CUSTOMER_NUMBER' => array(
				'configuration_title' => 'CanadaPost Customer Number',
				'configuration_description' => 'Canada Post Customer Number assigned by Canada Post.',
			),
			$this->getModuleKeyTrunk().'_USERNAME' => array(
				'configuration_title' => 'Username',
				'configuration_description' => 'API KEY Username hex code issued by CanadaPost.',
			),
			$this->getModuleKeyTrunk().'_PASSWORD' => array(
				'configuration_title' => 'CanadaPost Password',
				'configuration_description' => 'API KEY Password hex code issued by CanadaPost.',
			),
			$this->getModuleKeyTrunk().'_TYPES' => array(
				'configuration_title' => 'Shipping Methods',
				'configuration_value' => implode( ',', array_keys( $this->mServiceCodes ) ),
				'configuration_description' => 'Select the Services to be offered.',
				'set_function' => "zen_cfg_select_multioption(array('".implode( "','", array_keys( $this->mServiceCodes ) )."'), ",
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_ORIGIN_COUNTRY_CODE']['configuration_value'] = 'CA';
		return $ret;
	}
}

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

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class canadapost extends CommercePluginShippingBase {

	function __construct() {
		parent::__construct();
		$this->title			= tra( 'Canada Post' );
		$this->description		= tra( 'Canada Post Parcel Service <p><strong>CPC Profile Information</strong> can be obtained at https://sellonline.canadapost.ca<br /><a href=https://sellonline.canadapost.ca/servlet/LogonServlet?Language=0 target="_blank">Modify my profile</a></p>' );
		if( $this->isEnabled() ) {
			$this->CPCID = MODULE_SHIPPING_CANADAPOST_CPCID;
			$this->turnaround_time = MODULE_SHIPPING_CANADAPOST_TIME;
			$this->cp_online_handling = ((MODULE_SHIPPING_CANADAPOST_CP_HANDLING == 'True') ? true : false);
			$this->lettermail = ((MODULE_SHIPPING_CANADAPOST_LETTERMAIL_STATUS == 'True') ? true : false);
			$this->lettermail_max_weight = MODULE_SHIPPING_CANADAPOST_LETTERMAIL_MAX;
			$this->lettermail_available = false;
		}
	}

	protected function isEligibleShipper( $pShipHash ) {
		$ret = FALSE;
		if( $pShipHash['origin']['countries_iso_code_2'] == 'CA' ) {
			$ret = parent::isEligibleShipper( $pShipHash );
		}
		return $ret;
	}

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	public function quote( $pShipHash ) {
		$quotes = array();
		if( $this->isEligibleShipper( $pShipHash ) ) {

			$canadapostQuote = $this->_canadapostGetQuote( $pShipHash );

			if ($this->lettermail_available && ($pShipHash['shipping_weight_total'] <= $this->lettermail_max_weight)) {
				/* Select the correct rate table based on destination country */
				switch ($pShipHash['destination']['countries_iso_code_2']) {
					case 'CA':
						$table_cost = preg_split("/[:,]/", constant('MODULE_SHIPPING_CANADAPOST_LETTERMAIL_CAN'));
						$lettermail_service = sprintf("Lettermail: estimated %d-%d business days", round($this->turnaround_time / 24 + 2), round($this->turnaround_time / 24 + 4)); //factor in turnaround time
						break;
					case 'US':
						$table_cost = preg_split("/[:,]/", constant('MODULE_SHIPPING_CANADAPOST_LETTERMAIL_USA'));
						$lettermail_service = "U.S.A Letter-post, up to 2 weeks";
						break;
					default:
						$table_cost = preg_split("/[:,]/", constant('MODULE_SHIPPING_CANADAPOST_LETTERMAIL_INTL')); //Use overseas rate if not Canada or US
						$lettermail_service = "INTL Letter-post, up to 2 weeks";
						break;
				}

				for ($i = 0; $i < sizeof($table_cost); $i += 2) {
					//Lookup the correct rate
					if (round($pShipHash['shipping_weight_total'], 3) <= $table_cost[$i])
					{
						$lettermail_cost = $table_cost[$i + 1];
						break;
					}
				}

				if ($lettermail_cost > 0) {
					$canadapostQuote[count($canadapostQuote)] = array($lettermail_service => $lettermail_cost);
				}
			}

			if (is_array($canadapostQuote) && sizeof($canadapostQuote) > 0) {
				$quotes = array('id' => $this->code , 'module' => $this->title . ' <!--(' . $this->boxCount . tra( ' box(es) to be shipped' ). ')-->');
				$methods = array();
				for ($i = 0; $i < sizeof($canadapostQuote); $i ++) {
					list ($type, $cost) = each($canadapostQuote[$i]);
					$type = html_entity_decode($type);
					if ($this->cp_online_handling == true) {
						if ($method == '' || $method == $type) {
							$methods[] = array('id' => $type , 'title' => $type , 'cost' => $cost + $this->handling_cp);
						}
					} else {
						if ($method == '' || $method == $type)
						{
							$methods[] = array('id' => $type , 'title' => $type , 'cost' => (MODULE_SHIPPING_CANADAPOST_SHIPPING_HANDLING + $cost));
						}
					}
				}
				if ($this->tax_class > 0) {
					$quotes['tax'] = zen_get_tax_rate($this->tax_class, $pShipHash['destination']['countries_id'], $pShipHash['destination']['zone_id']);
				}
				$quotes['methods'] = $methods;
			} else {
				if ($canadapostQuote != false) {
					$errmsg = $canadapostQuote;
				} else {
					$errmsg = tra( 'An unknown error occured with the Canada Post shipping calculations.' );
				}
				$quotes = array('module' => $this->title , 'error' => $errmsg);
			}
		}

		return $quotes;
	}

	function _canadapostOrigin($postal, $country) {
		$this->_canadapostOriginPostalCode = str_replace(' ', '', $postal);
		$this->_canadapostOriginCountryCode = $country;
	}

	/**
	 * using HTTP/POST send message to canada post server
	 * (will timeout after 3 seconds, so that customers aren't left wondering what's going on in case the CP server is slow or down unexpectedly)
	 */
	function _sendToHost($data) {
		$response = FALSE;

		$url  = 'https://' . (MODULE_SHIPPING_CANADAPOST_CPCID == 'CPC_DEMO_XML' ? 'qa-' : '') . 'sellonline.canadapost.ca/sellonline/Rating';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
//        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart merchant at ' . urlencode(HTTPS_SERVER));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error    = curl_error($ch);
		$errno    = curl_errno($ch);
		curl_close($ch);

		if ($errno > 0) {
			$response = '<?xml version="1.0" ?><eparcel><error><statusMessage>' . tra( 'Cannot reach Canada Post Server. You may reload this page in your browser to try again.' ). ($errno != 0 ? '<br /><strong>' . $errno . ' ' . $error . '</strong>' : '') . '</statusMessage></error></eparcel>';
		}
		return $response;
	}

	/**
	 * assemble and submit quote request
	 */
	function _canadapostGetQuote( $pShipHash ) {

		global $gBitCustomer;
		$quoteLang = (in_array( $gBitCustomer->getLanguage(), array('en' , 'fr'))) ? $gBitCustomer->getLanguage() : MODULE_SHIPPING_CANADAPOST_LANGUAGE;

		$items = array();
		if( $packages = BitBase::getParameter( $pShipHash, 'packages' ) ) {
			// Multiple, variable packages not working for bitcommerce
/*
			foreach( $packages as $package ) {
				$newItem['quantity'] = (string)$quantity;
				$newItem['weight'] = ($weight ? (string)$weight : '0');
				$newItem['length'] = ($length ? (string)$length : '0');
				$newItem['width'] = ($width ? (string)$width : '0');
				$newItem['height'] = ($height ? (string)$height : '0');
				$newItem['description'] = $description;
				$newItem['ready_to_ship'] = (string)$ready_to_ship;
				$newItem['dim_type'] = (string)$dim_type;
				$newItem['weight_type'] = (string)$weight_type;
			}
*/
		} else {
			for( $i = 0; $i < $pShipHash['shipping_num_boxes']; $i++ ) {
				$newItem['quantity'] = 1;
				$newItem['weight'] = $pShipHash['shipping_weight_box'];
				// canada post medium box size
				$newItem['length'] = 12;
				$newItem['width'] = 9;
				$newItem['height'] = 5;
				$newItem['description'] = '';
				$newItem['ready_to_ship'] = '1';
				$newItem['dim_type'] = 'in';
				$newItem['weight_type'] = 'lbs';
				$items[] = $newItem;
			}
		}

		$strXML = "<?xml version=\"1.0\" ?>";
		// set package configuration.
		$strXML .= "<eparcel>\n";
		$strXML .= "        <language>" . $quoteLang . "</language>\n";
		$strXML .= "        <ratesAndServicesRequest>\n";
		$strXML .= "                <merchantCPCID>" . $this->CPCID . "</merchantCPCID>\n";
		$strXML .= "                <fromPostalCode>" . $pShipHash['origin']['postcode'] . "</fromPostalCode>\n";
		$strXML .= "                <turnAroundTime>" . $this->turnaround_time . "</turnAroundTime>\n";
		$strXML .= "                <itemsPrice>" . (string)$pShipHash['shipping_value'] . "</itemsPrice>\n";
		// add items information.
		$strXML .= "            <lineItems>\n";
		foreach( $items as $item ) {
			$strXML .= "      <item>\n";
			$strXML .= "                <quantity>" . $item['quantity'] . "</quantity>\n";
			//convert to kilograms
			$weightConversion = ($item['weight_type'] == 'lbs' ? (453597 / 1000000) : 1.0);
			$strXML .= "                <weight>" . ($item['weight'] * $weightConversion) . "</weight>\n";

			//convert to centimeters
			$lengthConversion = ($item['dim_type'] == 'in' ? 2.54 : 1.0);
			$strXML .= "                <length>" . ($item['length'] * $lengthConversion) . "</length>\n";
			$strXML .= "                <width>" . ($item['width'] * $lengthConversion) . "</width>\n";
			$strXML .= "                <height>" . ($item['height'] * $lengthConversion) . "</height>\n";

			$strXML .= "                <description>" . $this->_xmlentities($item['description']) . "</description>\n";
			if ($item['ready_to_ship'] == '1') {
				$strXML .= "                <readyToShip/>\n";
			}
			$strXML .= "      </item>\n";
		}
		$strXML .= "           </lineItems>\n";
		// add destination information.
		$strXML .= "               <city>" . $pShipHash['destination']['city'] . "</city>\n";
		$strXML .= "               <provOrState>" . $pShipHash['destination']['state'] . "</provOrState>\n";
		$strXML .= "               <country>" . $pShipHash['destination']['countries_iso_code_2'] . "</country>\n";
		$strXML .= "               <postalCode>" . $pShipHash['destination']['postcode'] . "</postalCode>\n";
		$strXML .= "        </ratesAndServicesRequest>\n";
		$strXML .= "</eparcel>\n";

		return $this->_sendToHost( $strXML );
	}

	/**
	 * Parser XML message returned by canada post server.
	 */
	function _parserResult($resultXML) {
		$statusMessage = substr($resultXML, strpos($resultXML, "<statusMessage>") + strlen("<statusMessage>"), strpos($resultXML, "</statusMessage>") - strlen("<statusMessage>") - strpos($resultXML, "<statusMessage>"));
		//print "message = $statusMessage";
		$cphandling = substr($resultXML, strpos($resultXML, "<handling>") + strlen("<handling>"), strpos($resultXML, "</handling>") - strlen("<handling>") - strpos($resultXML, "<handling>"));
		$this->handling_cp = $cphandling;
		if ($statusMessage == 'OK')
		{
			$packing_xml = $this->parsetag("packing", $resultXML); //pull out the packaging info
			$strProduct = substr($resultXML, strpos($resultXML, "<product id=") + strlen("<product id=>"), strpos($resultXML, "</product>") - strlen("<product id=>") - strpos($resultXML, "<product id="));
			$index = 0;
			$aryProducts = false;
			while (strpos($resultXML, "</product>"))
			{
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
			/* Lettermail is available if the only user-defined 'box' that Canada Post returns is one that begins with "lettermail" */
			if ($this->boxCount == 1 && strtolower(substr($this->parsetag("name", $packing_xml), 0, 10)) == 'lettermail') $this->lettermail_available = true;
			return $aryProducts;
		} else
		{
			if (strpos($resultXML, "<error>"))
			{
				return $statusMessage;
			} else
			{
				return false;
			}
		}
	}

	/**
	 * translate regular ascii chars to xml
	 */
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

	function parsetag($tag, $string)
	{
		$start = strpos($string, "<" . $tag . ">");
		if (! $start) return FALSE;
		$start = $start + strlen("<" . $tag . ">");
		$end = (strpos($string, "</" . $tag . ">"));
		$num = ($end - $start);
		$val = substr($string, $start, $num);
		return $val;
	}

				
	/**
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		return array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_LETTERMAIL_STATUS' => array(
				'configuration_title' => 'Enable Lettermail Rates',
				'configuration_value' => 'True',
				'configuration_description' => 'Do you want to offer Lettermail rates?',
				'set_function' => "zen_cfg_select_option(array('True', 'False'), ",
			),
			$this->getModuleKeyTrunk().'_LETTERMAIL_MAX' => array(
				'configuration_title' => 'Max weight for Lettermail',
				'configuration_value' => '0.500',
				'configuration_description' => 'Weight limit for Lettermail (default 0.5kg)',
			),
			$this->getModuleKeyTrunk().'_LETTERMAIL_CAN' => array(
				'configuration_title' => 'Table rates for Canada',
				'configuration_value' => '0.030:0.57, 0.050:1.00, 0.100:1.22, 0.200:2.00, 0.300:2.75, 0.400:3.00, 0.500:3.25',
				'configuration_description' => 'Rates for Canadian destinations',
			),
			$this->getModuleKeyTrunk().'_LETTERMAIL_USA' => array(
				'configuration_title' => 'Table rates for USA',
				'configuration_value' => '0.030:1.00, 0.050:1.22, 0.100:2.00, 0.200:3.50, 0.500:7.00',
				'configuration_description' => 'Rates for US destinations',
			),
			$this->getModuleKeyTrunk().'_LETTERMAIL_INTL' => array(
				'configuration_title' => 'Table rates for International',
				'configuration_value' => '0.030:1.70, 0.050:2.44, 0.100:4.00, 0.200:7.00, 0.500:14.00',
				'configuration_description' => 'Rates for International destinations',
			),
			$this->getModuleKeyTrunk().'_CANADAPOST_LANGUAGE' => array(
				'configuration_title' => 'Enter Selected Language-optional',
				'configuration_value' => 'en',
				'configuration_description' => 'Canada Post supports two languages:<br><strong>en</strong>-English<br><strong>fr</strong>-French.',
			),
			$this->getModuleKeyTrunk().'_CPCID' => array(
				'configuration_title' => 'Enter Your CanadaPost Customer ID',
				'configuration_value' => 'CPC_DEMO_XML',
				'configuration_description' => 'Canada Post Customer ID Merchant Identification assigned by Canada Post.',
			),
			$this->getModuleKeyTrunk().'_TIME' => array(
				'configuration_title' => 'Enter Turn Around Time(optional)',
				'configuration_description' => 'Turn Around Time -hours.',
			),
			$this->getModuleKeyTrunk().'_CP_HANDLING' => array(
				'configuration_title' => 'Use CP Handling Charge System',
				'configuration_value' => 'False',
				'configuration_description' => 'Use the Canada Post shipping and handling charge system (instead of the handling charge feature built-in to this module)?',
				'set_function' => "zen_cfg_select_option(array('True', 'False'), ",
			),
			$this->getModuleKeyTrunk().'_SHIPPING_HANDLING' => array(
				'configuration_title' => 'Handling Charge per box',
				'configuration_description' => 'Handling Charge is only used if the CP Handling System is set to false',
			),
		) );
	}

	/**
	 * Build array of keys used for installing/managing this module
	 *
	 * @return array
	 */
	function keys() {
		return array_merge( parent::keys(), array(
					'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_STATUS',
					'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_MAX',
					'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_CAN',
					'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_USA',
					'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_INTL',
					'MODULE_SHIPPING_CANADAPOST_LANGUAGE',
					'MODULE_SHIPPING_CANADAPOST_CPCID',
					'MODULE_SHIPPING_CANADAPOST_TIME',
					'MODULE_SHIPPING_CANADAPOST_CP_HANDLING',
					'MODULE_SHIPPING_CANADAPOST_SHIPPING_HANDLING',
		) );
	}
}

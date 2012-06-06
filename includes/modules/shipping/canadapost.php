<?php
/*
	$Id: canadapost.php,v 3.7 October 23 2004

	Before using this class, you should open a Canada Post SellOnline Account,
	and change the CPC_DEMO_XML ID to your ID. Visit www.canadapost.ca for details.

	XML connection method with Canada Post.

	Copyright (c) 2002,2003 Kelvin Zhang (kelvin@syngear.com)
	Modified by Kenneth Wang (kenneth@cqww.net), 2002.11.12
	LXWXH added by Tom St.Croix (management@betterthannature.com)
	All thanks to Kelvin and Kenneth and many others.

	Released under the GNU General Public License

	Updated to Zen Cart v1.3.0 April 9/2006
	Lettermail table rates added 6 May 2008 by Gord Dimitrieff (gord@aporia-records.com)
*/
/**
 * Canada Post Shipping Module class
 *
 */
class canadapost
{
	/**
	 * Declare shipping module alias code
	 *
	 * @var string
	 */
	var $code;
	/**
	 * Shipping module display name
	 *
	 * @var string
	 */
	var $title;
	/**
	 * Shipping module display description
	 *
	 * @var string
	 */
	var $description;
	/**
	 * Shipping module icon filename/path
	 *
	 * @var string
	 */
	var $icon;
	/**
	 * Shipping module status
	 *
	 * @var boolean
	 */
	var $enabled;
	/**
	 * Shipping Types
	 *
	 * @var array
	 */
	var $types;
	var $boxcount;

	/**
	 * Constructor
	 *
	 * @return usps
	 */
	function canadapost()
	{
		global $order, $gBitDb, $template, $gBitLanguage;
		$this->code = 'canadapost';
		$this->title = tra( 'Canada Post' );
		$this->description = tra( 'Canada Post Parcel Service<p><strong>CPC Profile Information </strong>can be obtained at http://sellonline.canadapost.ca<br /><a href=http://sellonline.canadapost.ca/servlet/LogonServlet?Language=0 target="_blank">Modify my profile</a>' );
		$this->icon = 'shipping_canadapost';
		$this->enabled = zen_get_shipping_enabled($this->code) && CommerceSystem::isConfigActive( 'MODULE_SHIPPING_CANADAPOST_STATUS' );
		if( $this->enabled == true ) {
			$this->server = MODULE_SHIPPING_CANADAPOST_SERVERIP;
			$this->port = MODULE_SHIPPING_CANADAPOST_SERVERPOST;
			$this->language = (in_array( $gBitLanguage->getLanguage(), array('en' , 'fr'))) ? strtolower( $gBitLanguage->getLanguage() ) : MODULE_SHIPPING_CANADAPOST_LANGUAGE;
			$this->CPCID = MODULE_SHIPPING_CANADAPOST_CPCID;
			$this->turnaround_time = MODULE_SHIPPING_CANADAPOST_TIME;
			$this->sort_order = MODULE_SHIPPING_CANADAPOST_SORT_ORDER;
			$this->items_qty = 0;
			$this->items_price = 0;
			$this->tax_class = MODULE_SHIPPING_CANADAPOST_TAX_CLASS;
			$this->tax_basis = MODULE_SHIPPING_CANADAPOST_TAX_BASIS;
			$this->cp_online_handling = ((MODULE_SHIPPING_CANADAPOST_CP_HANDLING == 'True') ? true : false);
			$this->lettermail = ((MODULE_SHIPPING_CANADAPOST_LETTERMAIL_STATUS == 'True') ? true : false);
			$this->lettermail_max_weight = MODULE_SHIPPING_CANADAPOST_LETTERMAIL_MAX;
			$this->lettermail_available = false;
			if( MODULE_SHIPPING_CANADAPOST_ZONE ) {
				$this->enabled = $gBitDb->query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = ?, and zone_country_id = ?", array( MODULE_SHIPPING_CANADAPOST_ZONE, $order->delivery['country']['id'] ) );
			}
		}
	}

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	function quote( $pShipHash = array() ) {
		global $order, $total_weight, $boxcount, $handling_cp;
		$shippingWeight = (!empty( $pShipHash['shipping_weight'] ) && $pShipHash['shipping_weight'] > 0.1 ? $pShipHash['shipping_weight'] : 0.1);
		$shippingNumBoxes = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

		// will round to 2 decimals 9.112 becomes 9.11 thus a product can be 0.1 of a KG
		$shippingWeight = round($shippingWeight, 2);
		$country_name = zen_get_countries(STORE_COUNTRY, true);
		$this->_canadapostOrigin(SHIPPING_ORIGIN_ZIP, $country_name['countries_iso_code_2']);
		if (! zen_not_null($order->delivery['state']) && $order->delivery['zone_id'] > 0)
		{
			$state_name = zen_get_zone_code($order->delivery['country_id'], $order->delivery['zone_id'], '');
			$order->delivery['state'] = $state_name;
		}

		$strXml = "<?xml version=\"1.0\" ?>";
		// set package configuration.
		$strXml .= "<eparcel>\n";
		$strXml .= "	<language>" . $this->language . "</language>\n";
		$strXml .= "	<ratesAndServicesRequest>\n";
		$strXml .= "		<merchantCPCID>" . $this->CPCID . "</merchantCPCID>\n";
		$strXml .= "		<fromPostalCode>" . $this->_canadapostOriginPostalCode . "</fromPostalCode>\n";
		$strXml .= "		<turnAroundTime>" . $this->turnaround_time . "</turnAroundTime>\n";
		$strXml .= "		<itemsPrice>" . (string)$this->items_price . "</itemsPrice>\n";
		// add items information.
		$itemXml = '';
		foreach( array_keys( $order->contents ) as $i ) {
			$productObject = $order->getProductObject( $i );
			$itemXml .= "	<item>\n";
			$itemXml .= "		<quantity>" . $order->contents[$i]['products_quantity'] . "</quantity>\n";
			$itemXml .= "		<weight>" . $productObject->getWeight() . "</weight>\n";
/*
			if ($this->item_dim_type[$i] == 'in') //convert to centimeters
			{
				$itemXml .= "		<length>" . ($this->item_length[$i] * (254 / 100)) . "</length>\n";
				$itemXml .= "		<width>" . ($this->item_width[$i] * (254 / 100)) . "</width>\n";
				$itemXml .= "		<height>" . ($this->item_height[$i] * (254 / 100)) . "</height>\n";
			} else {
*/
				$itemXml .= "		<length>30</length>\n";
				$itemXml .= "		<width>30</width>\n";
				$itemXml .= "		<height>5</height>\n";

//			}
			$itemXml .= "		<description>" . xmlentities( $productObject->getProductsModel() ) . "</description>\n";
// Not sure what this means at the moment
//			if ($this->item_ready_to_ship[$i] == '1') {
//				$itemXml .= "		<readyToShip/>\n";
//			}
			$itemXml .= "	</item>\n";
		}

		if( $itemXml ) {
			$strXml .= "	<lineItems>\n".$itemXml."\n	</lineItems>\n";
		}
		// add destination information.
		$strXml .= "	 <city>" . $order->delivery['city'] . "</city>\n";
		$strXml .= "	 <provOrState>" . $order->delivery['state'] . "</provOrState>\n";
		$strXml .= "	 <country>" . $order->delivery['country']['countries_iso_code_2'] . "</country>\n";
		$strXml .= "	 <postalCode>" . str_replace( ' ', '', $order->delivery['postcode'] ) . "</postalCode>\n";
		$strXml .= "	</ratesAndServicesRequest>\n";
		$strXml .= "</eparcel>\n";

		$ret = array( 
			'id' => $this->code, 
			'module' => $this->title, 
			'icon' => $this->icon,
		);
		//printf("\n\n<!--\n%s\n-->\n\n",$strXml); //debug xml
		$resultXml = $this->_sendToHost( $this->server, $this->port, 'POST', '', $strXml );
		if( $resultXml && $canadapostQuote = $this->_parserResult($resultXml) ) {
			if ($this->lettermail_available && ($shippingWeight <= $this->lettermail_max_weight)) {
				/* Select the correct rate table based on destination country */
				switch ($order->delivery['country']['iso_code_2'])
				{
					case 'CA':
						$table_cost = preg_split("/[:,]/", constant('MODULE_SHIPPING_CANADAPOST_LETTERMAIL_CAN'));
						$lettermailName = "Lettermail";
						$lettermailDelivery = sprintf( "estimated %d-%d business days", round( $this->turnaround_time / 24 + 2 ), round( $this->turnaround_time / 24 + 4 ) ); 
						//factor in turnaround time
						break;
					case 'US':
						$table_cost = preg_split("/[:,]/", constant('MODULE_SHIPPING_CANADAPOST_LETTERMAIL_USA'));
						$lettermailName = "U.S.A Letter-post";
						$lettermailDelivery = "up to 2 weeks";
						break;
					default:
						$table_cost = preg_split("/[:,]/", constant('MODULE_SHIPPING_CANADAPOST_LETTERMAIL_INTL')); //Use overseas rate if not Canada or US
						$lettermailName = "INTL Letter-post";
						$lettermailDelivery = "up to 2 weeks";
				}
				for ($i = 0; $i < sizeof($table_cost); $i += 2) //Lookup the correct rate
				{
					if (round($shippingWeight, 3) <= $table_cost[$i])
					{
						$lettermailCost = $table_cost[$i + 1];
						break;
					}
				}
				if( !empty( $lettermailCost ) ) {
					$canadapostQuote[] = array( 'name' => $lettermailName, 'cost' => $lettermailCost, 'delivery' => $lettermailDelivery );
				}
			}

			if( !empty( $canadapostQuote ) ) {
				$methods = array();
				foreach( $canadapostQuote as $quoteCode => $quote ) {
					if( empty( $pShipHash['method'] ) || $quoteCode == $pShipHash['method'] ) {
						$method = array( 'id' => $quote['code'], 'code' => $quote['code'], 'title' => $quote['name'], 'delivery' => $quote['delivery'], 'cost' => $quote['cost'] );
						if( $this->cp_online_handling == true ) {
							$method['cost'] += $this->handling_cp;
						} else {
							$method['cost'] += (float)MODULE_SHIPPING_CANADAPOST_SHIPPING_HANDLING;
						}
						$methods[] = $method;
					}
				}
				if ($this->tax_class > 0)
				{
					$ret['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
				}
				$ret['methods'] = $methods;
			} else {
				$errmsg = $canadapostQuote;
			}
		} else {
			$errmsg = tra( 'There was no response from the Canada Post shipping estimate server.' );
		}
		if( !empty( $errmsg ) ) {
			$errmsg .= ' '.tra( 'If you prefer to use Canada Post as your shipping method, please <a href="mailto:'.STORE_OWNER_EMAIL_ADDRESS.'">send us an email</a>.' );
			$ret['error'] = $errmsg;
		}
		return $ret;
	}

	/**
	 * check status of module
	 *
	 * @return boolean
	 */
	function check()
	{
		global $gBitDb;
		if (! isset($this->_check))
		{
			$check_query = $gBitDb->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_CANADAPOST_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}

	/**
	 * Remove this module
	 *
	 */
	function remove()
	{
		global $gBitDb;
		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_SHIPPING\_CANADAPOST\_%'");
	}

	function _canadapostOrigin($postal, $country)
	{
		$this->_canadapostOriginPostalCode = str_replace(' ', '', $postal);
		$this->_canadapostOriginCountryCode = $country;
	}

	/**
	 * using HTTP/POST send message to canada post server
	 * (will timeout after 3 seconds, so that customers aren't left wondering what's going on in case the CP server is slow or down unexpectedly)
	 */
	function _sendToHost($host, $port, $method = 'GET', $path, $data, $useragent = 0)
	{
		// Supply a default method of GET if the one passed was empty
		if (empty($method)) $method = 'GET';
		$method = strtoupper($method);
		if ($method == 'GET') $path .= '?' . $data;
		$buf = "";
		// try to connect to Canada Post server, for 3 seconds
		$fp = @fsockopen($host, $port, $errno, $errstr, 3);
		//echo 'errno='.$errno.'<br>errstr='.$errstr . '<br>';
		if ($fp) {
			fputs($fp, "$method $path HTTP/1.1\n");
			fputs($fp, "Host: $host\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
			fputs($fp, "Content-length: " . strlen($data) . "\n");
			if ($useragent) fputs($fp, "User-Agent: Zen Cart site\n");
			fputs($fp, "Connection: close\n\n");
			if ($method == 'POST') fputs($fp, $data);
			while (! feof($fp))
			{
				$buf .= fgets($fp, 128);
			}
			fclose($fp);
		} else {
//vd( "$this->code quote failed: fsockopen($host, $port, $errno, $errstr, 3)" );
		}
		return $buf;
	}

	/**
	 * Parser XML message returned by canada post server.
	 */
	function _parserResult($resultXml)
	{
		$statusMessage = substr($resultXml, strpos($resultXml, "<statusMessage>") + strlen("<statusMessage>"), strpos($resultXml, "</statusMessage>") - strlen("<statusMessage>") - strpos($resultXml, "<statusMessage>"));
		//print "message = $statusMessage";
		$cphandling = substr($resultXml, strpos($resultXml, "<handling>") + strlen("<handling>"), strpos($resultXml, "</handling>") - strlen("<handling>") - strpos($resultXml, "<handling>"));
		$this->handling_cp = $cphandling;
		if ($statusMessage == 'OK')
		{
			$packing_xml = $this->parsetag("packing", $resultXml); //pull out the packaging info
			$strProduct = substr($resultXml, strpos($resultXml, "<product id=") + strlen("<product id=>"), strpos($resultXml, "</product>") - strlen("<product id=>") - strpos($resultXml, "<product id="));
			$aryProducts = false;
			while (strpos($resultXml, "</product>"))
			{
				$cpnumberofboxes = substr_count($resultXml, "<expediterWeight");
				$this->boxCount = $cpnumberofboxes;
				$name = html_entity_decode( substr($resultXml, strpos($resultXml, "<name>") + strlen("<name>"), strpos($resultXml, "</name>") - strlen("<name>") - strpos($resultXml, "<name>")) );
				$rate = substr($resultXml, strpos($resultXml, "<rate>") + strlen("<rate>"), strpos($resultXml, "</rate>") - strlen("<rate>") - strpos($resultXml, "<rate>"));
				if( preg_match( '/<product id="([0-9]+)"/', $resultXml, $matches ) ) {
					$code = $matches[1];
				} else {
					$code = $name;
				}

				$shippingDate = substr($resultXml, strpos($resultXml, "<shippingDate>") + strlen("<shippingDate>"), strpos($resultXml, "</shippingDate>") - strlen("<shippingDate>") - strpos($resultXml, "<shippingDate>"));
				$deliveryDate = substr($resultXml, strpos($resultXml, "<deliveryDate>") + strlen("<deliveryDate>"), strpos($resultXml, "</deliveryDate>") - strlen("<deliveryDate>") - strpos($resultXml, "<deliveryDate>"));
				$deliveryDayOfWeek = substr($resultXml, strpos($resultXml, "<deliveryDayOfWeek>") + strlen("<deliveryDayOfWeek>"), strpos($resultXml, "</deliveryDayOfWeek>") - strlen("<deliveryDayOfWeek>") - strpos($resultXml, "<deliveryDayOfWeek>"));
				$nextDayAM = substr($resultXml, strpos($resultXml, "<nextDayAM>") + strlen("<nextDayAM>"), strpos($resultXml, "</nextDayAM>") - strlen("<nextDayAM>") - strpos($resultXml, "<nextDayAM>"));
				$packingID = substr($resultXml, strpos($resultXml, "<packingID>") + strlen("<packingID>"), strpos($resultXml, "</packingID>") - strlen("<packingID>") - strpos($resultXml, "<packingID>"));
				$aryProducts[$code] = array( 'cost' => $rate, 'code' => $code, 'name' => tra('Canada Post').' '.tra( $name ), 'delivery' => $deliveryDate, 'cost' => $rate );
				$resultXml = substr($resultXml, strpos($resultXml, "</product>") + strlen("</product>"));
			}
			/* Lettermail is available if the only user-defined 'box' that Canada Post returns is one that begins with "lettermail" */
			if ($this->boxCount == 1 && strtolower(substr($this->parsetag("name", $packing_xml), 0, 10)) == 'lettermail') $this->lettermail_available = true;
			return $aryProducts;
		} else {
			if (strpos($resultXml, "<error>"))
			{
				return $statusMessage;
			} else {
				return false;
			}
		}
	}


/*
	function _canadapost_get_service_name( $pServiceCode ) {
		$services = _canadapost_service_list();
		$ret = '';
		if( !empty( $services[$pServiceCode] ) ) {
			$ret = $services[$pServiceCode];
		}
		return $ret;
	}

	function _canadapost_service_list() {
	  return array(
		// Domestic Products 
		'1010' => tra('Canada Post').' '.tra('Regular'),
		'1020' => tra('Canada Post').' '.tra('Expedited'),
		'1030' => tra('Canada Post').' '.tra('Xpresspost'),
		'1040' => tra('Canada Post').' '.tra('Priority Courier'),

		// US Products
		'2005' => tra('Canada Post').' '.tra('Small Packets Surface USA'),
		'2015' => tra('Canada Post').' '.tra('Small Packets Air USA'),
		'2020' => tra('Canada Post').' '.tra('Expedited US Business Contract'),
		'2030' => tra('Canada Post').' '.tra('Xpresspost USA'),
		'2040' => tra('Canada Post').' '.tra('Priority Worldwide USA'),
		'2050' => tra('Canada Post').' '.tra('Priority Worldwide PAK USA'),

		// International Products
		'3005' => tra('Canada Post').' '.tra('Small Packets Surface International'),
		'3010' => tra('Canada Post').' '.tra('Surface International'),
		'3015' => tra('Canada Post').' '.tra('Small Packets Air International'),
		'3020' => tra('Canada Post').' '.tra('Air International'),
		'3025' => tra('Canada Post').' '.tra('Xpresspost International'),
		'3040' => tra('Canada Post').' '.tra('Priority Worldwide International'),
		'3050' => tra('Canada Post').' '.tra('Priority Worldwide PAK International'),
	  );

	  // No longer supported?
	  //'1120' => tra('Canada Post').' '.tra('Expedited Evening'),
	  //'1130' => tra('Canada Post').' '.tra('Xpresspost Evening'),
	  //'1220' => tra('Canada Post').' '.tra('Expedited Saturday'),
	  //'1230' => tra('Canada Post').' '.tra('Xpresspost Saturday'),
	  //'2025' => tra('Canada Post').' '.tra('Expedited US Commercial'),
	}
*/

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
	 * Install this module
	 *
	 */
	function install()
	{
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable CanadaPost Shipping', 'MODULE_SHIPPING_CANADAPOST_STATUS', 'True', 'Do you want to offer Canada Post shipping?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Lettermail Rates', 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_STATUS', 'True', 'Do you want to offer Lettermail rates?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Max weight for Lettermail', 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_MAX', '0.500', 'Weight limit for Lettermail (default 0.5kg)', '6', '0', now())");
	 $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Table rates for Canada', 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_CAN', '0.030:0.57, 0.050:1.00, 0.100:1.22, 0.200:2.00, 0.300:2.75, 0.400:3.00, 0.500:3.25', 'Rates for Canadian destinations', '6', '0', now())");
	 $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Table rates for USA', 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_USA', '0.030:1.00, 0.050:1.22, 0.100:2.00, 0.200:3.50, 0.500:7.00', 'Rates for US destinations', '6', '0', now())");
	 $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Table rates for International', 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_INTL', '0.030:1.70, 0.050:2.44, 0.100:4.00, 0.200:7.00, 0.500:14.00', 'Rates for International destinations', '6', '0', now())");
	 $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter CanadaPost Server', 'MODULE_SHIPPING_CANADAPOST_SERVERIP', 'sellonline.canadapost.ca', 'Canada Post server. <br>(default: sellonline.canadapost.ca)', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter CanadaPost Server Port', 'MODULE_SHIPPING_CANADAPOST_SERVERPOST', '30000', 'Service Port of Canada Post server. <br>(default: 30000)', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Selected Language-optional', 'MODULE_SHIPPING_CANADAPOST_LANGUAGE', 'en', 'Canada Post supports two languages:<br><strong>en</strong>-english<br><strong>fr</strong>-french.', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Your CanadaPost Customer ID', 'MODULE_SHIPPING_CANADAPOST_CPCID', 'CPC_DEMO_XML', 'Canada Post Customer ID Merchant Identification assigned by Canada Post.', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter Turn Around Time(optional)', 'MODULE_SHIPPING_CANADAPOST_TIME', '0', 'Turn Around Time -hours.', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_CANADAPOST_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_CANADAPOST_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(''Shipping'', ''Billing'', ''Store''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_CANADAPOST_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Use CP Handling Charge System', 'MODULE_SHIPPING_CANADAPOST_CP_HANDLING', 'False', 'Use the Canada Post shipping and handling charge system (instead of the handling charge feature built-in to this module)?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Charge per box', 'MODULE_SHIPPING_CANADAPOST_SHIPPING_HANDLING', '0', 'Handling Charge is only used if the CP Handling System is set to false', '6', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_CANADAPOST_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
		global $sniffer;
		if (method_exists($sniffer, 'field_type'))
		{
			if (! $sniffer->field_exists(TABLE_PRODUCTS, 'products_weight_type')) $gBitDb->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_weight_type ENUM('lbs','kgs') NOT NULL default 'kgs' after products_weight");
			if (! $sniffer->field_exists(TABLE_PRODUCTS, 'products_dim_type')) $gBitDb->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_dim_type ENUM('in','cm') NOT NULL default 'cm' after products_weight_type");
			if (! $sniffer->field_exists(TABLE_PRODUCTS, 'products_length')) $gBitDb->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_length DECIMAL(6,2) DEFAULT '12' NOT NULL after products_dim_type");
			if (! $sniffer->field_exists(TABLE_PRODUCTS, 'products_width')) $gBitDb->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_width DECIMAL(6,2) DEFAULT '12' NOT NULL after products_length");
			if (! $sniffer->field_exists(TABLE_PRODUCTS, 'products_height')) $gBitDb->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_height DECIMAL(6,2) DEFAULT '12' NOT NULL after products_width");
			if (! $sniffer->field_exists(TABLE_PRODUCTS, 'products_ready_to_ship')) $gBitDb->Execute("ALTER TABLE " . TABLE_PRODUCTS . " ADD products_ready_to_ship ENUM('0','1') NOT NULL default '1' after products_height");
		}
	}

	/**
	 * Build array of keys used for installing/managing this module
	 *
	 * @return array
	 */
	function keys()
	{
		return array('MODULE_SHIPPING_CANADAPOST_STATUS' , 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_STATUS' , 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_MAX' , 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_CAN' , 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_USA' , 'MODULE_SHIPPING_CANADAPOST_LETTERMAIL_INTL' , 'MODULE_SHIPPING_CANADAPOST_SERVERIP' , 'MODULE_SHIPPING_CANADAPOST_SERVERPOST' , 'MODULE_SHIPPING_CANADAPOST_LANGUAGE' , 'MODULE_SHIPPING_CANADAPOST_CPCID' , 'MODULE_SHIPPING_CANADAPOST_TIME' , 'MODULE_SHIPPING_CANADAPOST_TAX_CLASS' , 'MODULE_SHIPPING_CANADAPOST_TAX_BASIS' , 'MODULE_SHIPPING_CANADAPOST_ZONE' , 'MODULE_SHIPPING_CANADAPOST_CP_HANDLING' , 'MODULE_SHIPPING_CANADAPOST_SHIPPING_HANDLING' , 'MODULE_SHIPPING_CANADAPOST_SORT_ORDER');
	}

}

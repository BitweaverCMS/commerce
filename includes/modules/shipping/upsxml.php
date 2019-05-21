<?php
/**
 * UPS XML v1.7.2

+----------------------------------------------------------------------+
| bitcommerce,	http://www.bitcommerce.org                             |
+----------------------------------------------------------------------+
| Forked from ZenCart UPS XML v1.7.2                                   |
| Copyright (c) 2019 bitcommerce.org                                   |
| This source file is subject to version 3.0 of the GPL license        |
| Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
| Portions Copyright (c) 2003 osCommerce                               |
+------------------------------------------------------------------------------+
| Original $Id: upsxml.php,v 1.1.4 2004/12/19 13:30:00 sgo Exp $               |
| Written by Torin Walker                                                      |
| torinwalker@rogers.com                                                       |
| Original copyright (c) 2003 Torin Walker                                     |
| Copyright(c) 2003 by Torin Walker, All rights reserved.                      |
+------------------------------------------------------------------------------+
| Some code/style borrowed from both Fritz Clapp's UPS Choice 1.7 Module,      |
| and Kelvin, Kenneth, and Tom St.Croix's Canada Post 3.1 Module.              |
| Insurance support by Joe McFrederick                                         |
+------------------------------------------------------------------------------+
| Modifyed for zen-cart 1.2.5d by Dennis Sayer - July 9, 2005                  |
| Indention corrections by Dennis Sayer - July 9, 2005                         | 
| Tested for zen-cart 1.3 by Dennis Sayer - July 03 2006                       | 
| dennis.s.sayer@brandnamebatteries.com                                        |    
+------------------------------------------------------------------------------+
| Released under the GNU General Public License                                |
| This program is free software; you can redistribute it and/or modify it      |
| under the terms of the GNU General Public License as published by the Free   |
| Software Foundation; either version 2 of the License, or (at your option)    |
| any later version. This program is distributed in the hope that it will be   |
| useful, but WITHOUT ANY WARRANTY; without even the implied warranty of       |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General     |
| Public License for more details. You should have received a copy of the      |
| GNU General Public License along with this program; If not, you may obtain   |
| one by writing to and requesting one from:                                   |
| The Free Software Foundation, Inc.,                                          |
| 59 Temple Place, Suite 330,                                                  |
| Boston, MA 02111-1307 USA                                                    |
+------------------------------------------------------------------------------+
*/
require (DIR_FS_CATALOG . 'includes/classes/xmldocument.php');

// if using the optional dimensional support, set to 1, otherwise leave as 0
define('DIMENSIONS_SUPPORTED', 0);

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class upsxml extends CommercePluginShippingBase {
	var $types, $boxcount;
	public $moduleVersion = '1.7.2';

	var $logfile = NULL;

	//***************
	function __construct() {
	
		parent::__construct();

		$this->title			= tra( 'United Parcel Service Rates' );
		$this->description		= 'You will need to have registered an account with UPS and proper approval to use this module.';
		$this->icon 			= 'shipping_ups';

		if( $this->isEnabled() ) {

			$this->sort_order			= MODULE_SHIPPING_UPSXML_SORT_ORDER;
			$this->tax_class			= MODULE_SHIPPING_UPSXML_TAX_CLASS;
			$this->access_key			= MODULE_SHIPPING_UPSXML_ACCESS_KEY;
			$this->access_username		= MODULE_SHIPPING_UPSXML_USERNAME;
			$this->access_password		= MODULE_SHIPPING_UPSXML_PASSWORD;
			$this->origin				= MODULE_SHIPPING_UPSXML_ORIGIN;
			$this->origin_city			= MODULE_SHIPPING_UPSXML_CITY;
			$this->origin_stateprov		= MODULE_SHIPPING_UPSXML_STATEPROV;
			$this->origin_country		= MODULE_SHIPPING_UPSXML_COUNTRY;
			$this->origin_postalcode	= MODULE_SHIPPING_UPSXML_POSTALCODE;
			$this->pickup_method		= MODULE_SHIPPING_UPSXML_PICKUP_METHOD;
			$this->package_type			= MODULE_SHIPPING_UPSXML_PACKAGE_TYPE;
			$this->unit_weight			= MODULE_SHIPPING_UPSXML_UNIT_WEIGHT;
			$this->unit_length			= MODULE_SHIPPING_UPSXML_UNIT_LENGTH;
			$this->handling_fee			= MODULE_SHIPPING_UPSXML_HANDLING;
			$this->quote_type			= MODULE_SHIPPING_UPSXML_QUOTE_TYPE;
			$this->upsShipperNumber		= MODULE_SHIPPING_UPSXML_SHIPPER_NUMBER;
			$this->displayWeight		= (strpos(MODULE_SHIPPING_UPSXML_OPTIONS, 'weight') !== false);
			$this->displayTransitTime	= (strpos(MODULE_SHIPPING_UPSXML_OPTIONS, 'transit') !== false);
			
			// -----
			// Ensure that the currency code supplied is supported by the store, default to
			// the store's default currency (with a warning log generated) if not.
			//
			$this->currencyCode = $this->initCurrencyCode();
			
			$this->customer_classification = MODULE_SHIPPING_UPSXML_CUSTOMER_CLASSIFICATION_CODE;
			$this->protocol = 'https';
			$this->host = ((MODULE_SHIPPING_UPSXML_MODE == 'Test') ? 'wwwcie.ups.com' : 'onlinetools.ups.com');
			$this->port = '443';
			$this->path = '/ups.app/xml/Rate';
			$this->transitpath = '/ups.app/xml/TimeInTransit';
			$this->version = 'UPSXML Rate 1.0001';
			$this->transitversion = 'UPSXML Time In Transit 1.0002';
			$this->timeout = '60';
			$this->xpci_version = '1.0001';
			$this->transitxpci_version = '1.0002';
			$this->items_qty = 0;
			$this->timeintransit = '0';
			$this->today = date("Ymd");

			// insurance addition
			if (MODULE_SHIPPING_UPSXML_INSURE == 'False'){
				$this->pkgvalue = 100;
			}
			// end insurance addition
			if (MODULE_SHIPPING_UPSXML_DEBUG == 'true') {
				$this->logfile = DIR_FS_CATALOG . 'logs/upsxml.log';
			}

			// Available pickup types - set in admin
			$this->pickup_methods = array(
				'Daily Pickup' => '01',
				'Customer Counter' => '03',
				'One Time Pickup' => '06',
				'On Call Air Pickup' => '07',
				'Letter Center' => '09',
				'Air Service Center' => '10'
			);

			// Available package types
			$this->package_types = array(
				'Unknown' => '00',
				'UPS Letter' => '01',
				'Customer Package' => '02',
				'UPS Tube' => '03',
				'UPS Pak' => '04',
				'UPS Express Box' => '21',
				'UPS 25kg Box' => '24',
				'UPS 10kg Box' => '25'
			);

			// Human-readable Service Code lookup table. The values returned by the Rates and Service "shop" method are numeric.
			// Using these codes, and the administratively defined Origin, the proper human-readable service name is returned.
			// Note: The origin specified in the admin configuration affects only the product name as displayed to the user.
			$this->service_codes = array(
				// US Origin
				'US Origin' => array(
					'01' => 'UPS Next Day Air',
					'02' => 'UPS 2nd Day Air',
					'03' => 'UPS Ground',
					'07' => 'UPS Worldwide Express',
					'08' => 'UPS Worldwide Expedited',
					'11' => 'UPS Standard',
					'12' => 'UPS 3 Day Select',
					'13' => 'UPS Next Day Air Saver',
					'14' => 'UPS Next Day Air Early A.M.',
					'54' => 'UPS Worldwide Express Plus',
					'59' => 'UPS 2nd Day Air A.M.',
					'65' => 'UPS Express Saver',
				),
				// Canada Origin
				'Canada Origin' => array(
					'01' => 'UPS Express',
                    '02' => 'UPS Expedited',
					'07' => 'UPS Worldwide Express',
					'08' => 'UPS Worldwide Expedited',
					'11' => 'UPS Standard',
					'12' => 'UPS 3 Day Select',
					'13' => 'UPS Express Saver',
					'14' => 'UPS Express Early A.M.',
					'54' => 'UPS Worldwide Express Plus',
				),
				// European Union Origin
				'European Union Origin' => array(
					'07' => 'UPS Express',
					'08' => 'UPS Expedited',
					'11' => 'UPS Standard',
					'54' => 'UPS Worldwide Express Plus',
					'64' => 'UPS Express NA1',
					'65' => 'UPS Express Saver',
				),
				// Puerto Rico Origin
				'Puerto Rico Origin' => array(
					'01' => 'UPS Next Day Air',
					'02' => 'UPS 2nd Day Air',
					'03' => 'UPS Ground',
					'07' => 'UPS Worldwide Express',
					'08' => 'UPS Worldwide Expedited',
					'14' => 'UPS Next Day Air&reg; Early A.M.',
					'54' => 'UPS Worldwide Express Plus',
				),
				// Mexico Origin
				'Mexico Origin' => array(
					'07' => 'UPS Worldwide Express',
					'08' => 'UPS Worldwide Expedited',
					'54' => 'UPS Worldwide Express Plus',
				),
				// All other origins
				'All other origins' => array(
					'07' => 'UPS Worldwide Express',
					'08' => 'UPS Worldwide Expedited',
					'54' => 'UPS Worldwide Express Plus',
				)
			);

			define('MODULE_SHIPPING_UPSXML_INVALID_CURRENCY_CODE', "Unknown currency code specified (" . MODULE_SHIPPING_UPSXML_CURRENCY_CODE . "), using store default (" . DEFAULT_CURRENCY . ").");
		}
	}
	
	// ----
	// Make sure that the currency-code specified is within the range of those currently enabled for the
	// store, defaulting to the store's default currency (with a log generated) if not.
	//
	private function initCurrencyCode() {
		$currency_code = DEFAULT_CURRENCY;
		if (defined('MODULE_SHIPPING_UPSXML_STATUS') && MODULE_SHIPPING_UPSXML_STATUS == 'True') {
			if (!class_exists('currencies')) {
				require DIR_WS_CLASSES . 'currencies.php';
			}
			$currencies = new currencies();
			$currency_code = MODULE_SHIPPING_UPSXML_CURRENCY_CODE;
			if (!isset($currencies->currencies[MODULE_SHIPPING_UPSXML_CURRENCY_CODE])) {
				trigger_error(MODULE_SHIPPING_UPSXML_INVALID_CURRENCY_CODE, E_USER_WARNING);
				
				if (IS_ADMIN_FLAG === true) {
					$GLOBALS['messageStack']->add_session('<b>upsxml</b>: ' . MODULE_SHIPPING_UPSXML_INVALID_CURRENCY_CODE, 'error');
				}
			}
		}
		return $currency_code;
	}

	// class methods
	function quote( $pShipHash = array() ) {
		/* FedEx integration starts */
		global $gBitCustomer, $order;

		if (MODULE_SHIPPING_UPSXML_INSURE == 'True'){
			$this->pkgvalue = ceil($order->info['total']);
		}
		/*
			// this was in the constructor to verify if the order is in a zone for UPS
			if (($this->enabled == true) && ((int)MODULE_SHIPPING_UPSXML_ZONE > 0)) {
				$check_flag = false;
				$check = $this->mDb->query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . (int)MODULE_SHIPPING_UPSXML_ZONE . "' and zone_country_id = '" . (int)$order->delivery['country']['id'] . "' order by zone_id");
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

				if ($check_flag == false) {
					$this->enabled = false;
				}
			}
		*/

		$shippingWeight = (!empty( $pShipHash['shipping_weight'] ) && $pShipHash['shipping_weight'] > 1 ? $pShipHash['shipping_weight'] : 1);
		$shippingNumBoxes = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

		if ( !empty( $pShipHash['method'] ) && (isset($this->types[$pShipHash['method']])) ) {
			$prod = $pShipHash['method'];
			// BOF: UPS 
		} else if ($order->delivery['country']['countries_iso_code_2'] == 'CA') {
			$prod = 'STD';
			// EOF: UPS 
		} else {
			$prod = 'GNDRES';
		}

		$state = zen_get_zone_code($order->delivery['country']['countries_id'], $order->delivery['state'], '');
		$this->_upsOrigin(MODULE_SHIPPING_UPSXML_CITY, MODULE_SHIPPING_UPSXML_STATEPROV, MODULE_SHIPPING_UPSXML_COUNTRY, MODULE_SHIPPING_UPSXML_POSTALCODE);
		$this->_upsDest($order->delivery['city'], $state, $order->delivery['country']['countries_iso_code_2'], $order->delivery['postcode']);

		if (DIMENSIONS_SUPPORTED) {
			$productsArray = $_SESSION['cart']->get_products();
			// sort $productsArray according to ready-to-ship (first) and not-ready-to-ship (last)
			function ready_to_shipCmp( $a, $b) {
			if ( $a['ready_to_ship'] == $b['ready_to_ship'] ) {
				return 0;
			}
			if ( $a['ready_to_ship'] > $b['ready_to_ship'] ) {
				return -1;
			}
			return 1;
			}

			usort($productsArray, ready_to_shipCmp);
			// Use packing algorithm to return the number of boxes we'll ship
			$boxesToShip = $this->packProducts($productsArray);
			// Quote for the number of boxes
			for ($i = 0; $i < count($boxesToShip); $i++) {
				$this->_addItem($boxesToShip[$i]['length'], $boxesToShip[$i]['width'], $boxesToShip[$i]['height'], $boxesToShip[$i]['current_weight']);
				$totalWeight += $boxesToShip[$i]['current_weight'];
			}
		} else {
			// The old method. Let zen-cart tell us how many boxes, plus the weight of each (or total? - might be sw/num boxes)
			$this->items_qty = 0; //reset quantities
			for ($i = 0; $i < $shippingNumBoxes; $i++) {
				$this->_addItem (0, 0, 0, $shippingWeight);
			}
		}
		if ($this->displayTransitTime) {
			// BOF Time In Transit: 
			// comment out this section if you don't want/need to have expected delivery dates
			$this->servicesTimeintransit = $this->_upsGetTimeServices();
			if ($this->logfile) {
				error_log("------------------------------------------\n", 3, $this->logfile);
				error_log("Time in Transit: " . $this->timeintransit . "\n", 3, $this->logfile);
				error_log("Shipping weight: $shippingWeight" . PHP_EOL, 3, $this->logfile);
				error_log("Shipping Num Boxes: $shippingNumBoxes" . PHP_EOL, 3, $this->logfile);
				error_log('this: ' . var_export($this, true) . PHP_EOL, 3, $this->logfile);
			}
			// EOF Time In Transit
		}

		$upsQuote = $this->_upsGetQuote();
		if ((is_array($upsQuote)) && (sizeof($upsQuote) > 0)) {
			$weight_info = '';
			if ($this->displayWeight) {
				if (DIMENSIONS_SUPPORTED) {
					$weight_info = ' (' . $this->boxCount . ($this->boxCount > 1 ? ' pkg(s), ' : ' pkg, ') . $totalWeight . ' ' . strtolower($this->unit_weight) . ' total)';
				} else {
					$weight_info = ' (' . $shippingNumBoxes . ($this->boxCount > 1 ? ' pkg(s) x ' : ' pkg x ') . number_format($shippingWeight,2) . ' ' . strtolower($this->unit_weight) . ' total)';
				}
			}
			$this->quotes = array(
				'id' => $this->code,
				'module' => $this->title . $weight_info
			);
			$methods = array();
			foreach ($upsQuote as $quote) {
				foreach ($quote as $type => $cost) {
					// BOF limit choices
					if (!$this->exclude_choices($type)) continue;
					// EOF limit choices
					if ( empty( $pShipHash['method'] ) || $pShipHash['method'] == $type ) {
						$_type = $type;
						if ($this->displayTransitTime) {
							//if (isset($this->servicesTimeintransit[$type])) {
							//	$_type = $_type . ", ".$this->servicesTimeintransit[$type]["date"];
							//}
							// instead of just adding the expected delivery date as ", yyyy-mm-dd"
							// you might like to change this to your own liking for example by commenting the
							// three lines above this and uncommenting/changing the next:
							// START doing things differently
							if (isset($this->servicesTimeintransit[$type])) {
								$eta_array = explode("-", $this->servicesTimeintransit[$type]["date"]);
								$months = array (" ", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
								$eta_arrival_date = $months[(int)$eta_array[1]]." ".$eta_array[2].", ".$eta_array[0];
								$_type .= ", ETA: ".$eta_arrival_date;
							}
							// END of doing things differently:
						}

						$methods[] = array('id' => $type, 'title' => $_type, 'cost' => ($this->handling_fee + $cost));
					}
				}
			}
			if ($this->tax_class > 0) {
				$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
			}
			$this->quotes['methods'] = $methods;
		} else {
			if ( $upsQuote != false ) {
				$errmsg = $upsQuote;
			} else {
				$errmsg = MODULE_SHIPPING_UPSXML_TEXT_UNKNOWN_ERROR;
			}
			$errmsg .= '<br>' . MODULE_SHIPPING_UPSXML_TEXT_IF_YOU_PREFER . ' ' . STORE_NAME.' via <a href="mailto:'.STORE_OWNER_EMAIL_ADDRESS.'"><u>Email</U></a>.';
			$this->quotes = array('module' => $this->title, 'error' => $errmsg);
		}
		$this->quotes['icon'] = $this->icon;

		return $this->quotes;
	}

	//***********************
	function _upsProduct($prod){
		$this->_upsProductCode = $prod;
	}

	//**********************************************
	function _upsOrigin($city, $stateprov, $country, $postal){
		$this->_upsOriginCity = $city;
		$this->_upsOriginStateProv = $stateprov;
		$this->_upsOriginCountryCode = $country;
		$postal = str_replace(' ', '', $postal);
		if ($country == 'US') {
			$this->_upsOriginPostalCode = substr($postal, 0, 5);
		} else {
			$this->_upsOriginPostalCode = $postal;
		}
	}

	//**********************************************
	function _upsDest($city, $stateprov, $country, $postal) {
		$this->_upsDestCity = $city;
		$this->_upsDestStateProv = $stateprov;
		$this->_upsDestCountryCode = $country;
		$postal = str_replace(' ', '', $postal);
		if ($country == 'US') {
			$this->_upsDestPostalCode = substr($postal, 0, 5);
		} else {
			$this->_upsDestPostalCode = $postal;
		}
	}

	//************************
	function _upsAction($action) {
		// rate - Single Quote; shop - All Available Quotes
		$this->_upsActionCode = $action;
	}

	//********************************************
	function _addItem($length, $width, $height, $weight) {
		// Add box or item to shipment list. Round weights to 1 decimal places.
		if ((float)$weight < 1.0) {
			$weight = 1;
		} else {
			$weight = round($weight, 1);
		}
		$index = $this->items_qty;
		$this->item_length[$index] = ($length ? (string)$length : '0' );
		$this->item_width[$index] = ($width ? (string)$width : '0' );
		$this->item_height[$index] = ($height ? (string)$height : '0' );
		$this->item_weight[$index] = ($weight ? (string)$weight : '0' );
		$this->items_qty++;
	}

	//********************
	function getPackages() {
		global $db;
		$packages = array();
		$result = $this->mDb->query("select * from " . TABLE_PACKAGING . " order by package_cost;");
		while (!$result->EOF) {
			$packages[] = array(
			'id' => $result->fields['package_id'],
			'name' => $result->fields['package_name'],
			'description' => $result->fields['package_description'],
			'length' => $result->fields['package_length'],
			'width' => $result->fields['package_width'],
			'height' => $result->fields['package_height'],
			'empty_weight' => $result->fields['package_empty_weight'],
			'max_weight' => $result->fields['package_max_weight'],
			'cost' => $result->fields['package_cost']);
			$result->MoveNext();
		}
		return $packages;
	}

	//********************************
	function packProducts($productsArray) {
		// A very simple box packing algorithm. Given a list of packages, returns an array of boxes.
		// This algorithm is trivial. It works on the premise that you have selected boxes that fit your products, and that their volumes are resonable multiples
		// of the products they store. For example, if you sell CDs and these CDs are 5x5x0.5", your boxes should be 5x5x0.5 (1 CD mailer), 5x5x2.5 (5 CD mailer)
		// and 5x5x5 (10 CD mailer). No matter how many CDs a customer buys, this routine will always find the optimal packing.
		// Your milage may differ, depending on what variety of products you sell, and how they're boxed. I just made up this algorithm in a hurry to fill a small
		// niche. You are encouraged to find better algorithms. Better algorithms mean better packaging, resulting in higher quoting accuracy and less loss due to
		// inaccurate quoting. The algorithm proceeds as follows:
		// Get the first, smallest box, and try to put everything into it. If not all of it fits, try fitting it all into the next largest box. Keep increasing
		// the size of the box until no larger box can be obtained, then spill over into a second, smallest box. Once again, increase the box size until
		// everything fits, or spill over again. Repeat until everything is boxed. The cost of a box determines the order in which it is tried. There will definitely
		// be cases where it is cheaper to send two small packages rather than one larger one. In that case, you'll need a better algorithm.
		// Get the available packages and "prepare" empty boxes with weight and remaining volume counters. (Take existing box and add 'remaining_volume' and 'current_weight';
		$definedPackages = $this->getPackages();
		$emptyBoxesArray = array();
		for ($i = 0; $i < count($definedPackages); $i++) {
			$definedBox = $definedPackages[$i];
			$definedBox['remaining_volume'] = $definedBox['length'] * $definedBox['width'] * $definedBox['height'];
			$definedBox['current_weight'] = $definedBox['empty_weight'];
			$emptyBoxesArray[] = $definedBox;
		}
		$packedBoxesArray = array();
		$currentBox = NULL;
		// Get the product array and expand multiple qty items.
		$productsRemaining = array();
		for ($i = 0; $i < count($productsArray); $i++) {
			$product = $productsArray[$i];
			for ($j = 0; $j < $productsArray[$i]['quantity']; $j++) {
				$productsRemaining[] = $product;
			}
		}
		// Worst case, you'll need as many boxes as products ordered.
		while (count($productsRemaining)) {
			// Immediately set aside products that are already packed and ready.
			if ($productsRemaining[0]['ready_to_ship'] == '1') {
				$packedBoxesArray[] = array (
				'length' => $productsRemaining[0]['length'],
				'width' => $productsRemaining[0]['width'],
				'height' => $productsRemaining[0]['height'],
				'current_weight' => $productsRemaining[0]['weight']);
				$productsRemaining = array_slice($productsRemaining, 1);
				continue;
			}
			//Cylcle through boxes, increasing box size if all doesn't fit.
			if (count($emptyBoxesArray) == 0) {
				print_r("ERROR: No boxes to ship unpackaged product<br>");
				break;
			}
			for ($b = 0; $b < count($emptyBoxesArray); $b++) {
				$currentBox = $emptyBoxesArray[$b];
				//Try to fit each product in box
				for ($p = 0; $p < count($productsRemaining); $p++) {
					if ($this->fitsInBox($productsRemaining[$p], $currentBox)) {
						//It fits. Put it in the box.
						$currentBox = $this->putProductInBox($productsRemaining[$p], $currentBox);
						if ($p == count($productsRemaining) - 1) {
							$packedBoxesArray[] = $currentBox;
							$productsRemaining = array_slice($productsRemaining, $p + 1);
							break 2;
						}
					} else {
						if ($b == count($emptyBoxesArray) - 1) {
							//We're at the largest box already, and it's full. Keep what we've packed so far and get another box.
							$packedBoxesArray[] = $currentBox;
							$productsRemaining = array_slice($productsRemaining, $p + 1);
							break 2;
						}
						// Not all of them fit. Stop packing remaining products and try next box.
						break;
					}
				}
			}
		}
		return $packedBoxesArray;
	}

	//*****************************
	function fitsInBox($product, $box) {
		$productVolume = $product['length'] * $product['width'] * $product['height'];
		if ($productVolume <= $box['remaining_volume']) {
			if ($box['max_weight'] == 0 || ($box['current_weight'] + $product['weight'] <= $box['max_weight'])) {
				return true;
			}
		}
		return false;
	}

	//***********************************
	function putProductInBox($product, $box) {
		$productVolume = $product['length'] * $product['width'] * $product['height'];
		$box['remaining_volume'] -= $productVolume;
		$box['products'][] = $product;
		$box['current_weight'] += $product['weight'];
		return $box;
	}

	//*********************
	function _upsGetQuote() {
		// Create the access request
		$accessRequestHeader =
		"<?xml version=\"1.0\"?>\n".
		"<AccessRequest xml:lang=\"en-US\">\n".
		"	 <AccessLicenseNumber>". $this->access_key ."</AccessLicenseNumber>\n".
		"	 <UserId>". $this->access_username ."</UserId>\n".
		"	 <Password>". $this->access_password ."</Password>\n".
		"</AccessRequest>\n";

		$ratingServiceSelectionRequestHeader =
		"<?xml version=\"1.0\"?>\n".
		"<RatingServiceSelectionRequest xml:lang=\"en-US\">\n".
		"	 <Request>\n".
		"		 <TransactionReference>\n".
		"			 <CustomerContext>Rating and Service</CustomerContext>\n".
		"			 <XpciVersion>". $this->xpci_version ."</XpciVersion>\n".
		"		 </TransactionReference>\n".
		"		 <RequestAction>Rate</RequestAction>\n".
		"		 <RequestOption>shop</RequestOption>\n".
		"	 </Request>\n".
		"	 <PickupType>\n".
		"		 <Code>". $this->pickup_methods[$this->pickup_method] ."</Code>\n".
		"	 </PickupType>\n".
		"	 <Shipment>\n".
		(($this->upsShipperNumber == '' || $this->_upsDestStateProv == '') ? '' : ('<RateInformation><NegotiatedRatesIndicator /></RateInformation>' . PHP_EOL)) .
		"		 <Shipper>\n".
		"			 <Address>\n".
		"				 <City>". $this->_upsOriginCity ."</City>\n".
		"				 <StateProvinceCode>". $this->_upsOriginStateProv ."</StateProvinceCode>\n".
		"				 <CountryCode>". $this->_upsOriginCountryCode ."</CountryCode>\n".
		"				 <PostalCode>". $this->_upsOriginPostalCode ."</PostalCode>\n".
		"			 </Address>\n".
		(($this->upsShipperNumber == '' || $this->_upsDestStateProv == '') ? '' : ('<ShipperNumber>' . $this->upsShipperNumber . '</ShipperNumber>' . PHP_EOL)) .
		"		 </Shipper>\n".
		"		 <ShipTo>\n".
		"			 <Address>\n".
		"				 <City>". $this->_upsDestCity ."</City>\n".
		"				 <StateProvinceCode>". $this->_upsDestStateProv ."</StateProvinceCode>\n".
		"				 <CountryCode>". $this->_upsDestCountryCode ."</CountryCode>\n".
		"				 <PostalCode>". $this->_upsDestPostalCode ."</PostalCode>\n".
		($this->quote_type == "Residential" ? "<ResidentialAddressIndicator/>\n" : "") .
		"			 </Address>\n".
		"		 </ShipTo>\n";
		$ratingServiceSelectionRequestPackageContent = '';
		for ($i = 0; $i < $this->items_qty; $i++) {

			$ratingServiceSelectionRequestPackageContent .=
			"		 <Package>\n".
			"			 <PackagingType>\n".
			"				 <Code>". $this->package_types[$this->package_type] ."</Code>\n".
			"			 </PackagingType>\n";
			if (DIMENSIONS_SUPPORTED) {

				$ratingServiceSelectionRequestPackageContent .=
				"			 <Dimensions>\n".
				"				 <UnitOfMeasurement>\n".
				"					 <Code>". $this->unit_length ."</Code>\n".
				"				 </UnitOfMeasurement>\n".
				"				 <Length>". $this->item_length[$i] ."</Length>\n".
				"				 <Width>". $this->item_width[$i] ."</Width>\n".
				"				 <Height>". $this->item_height[$i] ."</Height>\n".
				"			 </Dimensions>\n";
			}

			$ratingServiceSelectionRequestPackageContent .=
			"			 <PackageWeight>\n".
			"				 <UnitOfMeasurement>\n".
			"					 <Code>". $this->unit_weight ."</Code>\n".
			"				 </UnitOfMeasurement>\n".
			"				 <Weight>". $this->item_weight[$i] ."</Weight>\n".
			"			 </PackageWeight>\n".
			"			 <PackageServiceOptions>\n".
			//"				 <COD>\n".
			//"					 <CODFundsCode>0</CODFundsCode>\n".
			//"					 <CODCode>3</CODCode>\n".
			//"					 <CODAmount>\n".
			//"						 <CurrencyCode>USD</CurrencyCode>\n".
			//"						 <MonetaryValue>1000</MonetaryValue>\n".
			//"					 </CODAmount>\n".
			//"				 </COD>\n".
			"				 <InsuredValue>\n".
			"					 <CurrencyCode>" . $this->currencyCode . "</CurrencyCode>\n".
			"					 <MonetaryValue>".$this->pkgvalue."</MonetaryValue>\n".
			"				 </InsuredValue>\n".
			"			 </PackageServiceOptions>\n".
			"		 </Package>\n";
		}

		$ratingServiceSelectionRequestFooter =
		//"	 <ShipmentServiceOptions/>\n".
		"	 </Shipment>\n".
		"	 <CustomerClassification>\n".
		"		 <Code>". $this->customer_classification ."</Code>\n".
		"	 </CustomerClassification>\n".
		"</RatingServiceSelectionRequest>\n";

		$xmlRequest = $accessRequestHeader .
		$ratingServiceSelectionRequestHeader .
		$ratingServiceSelectionRequestPackageContent .
		$ratingServiceSelectionRequestFooter;

		//post request $strXML;
		$xmlResult = $this->_post($this->protocol, $this->host, $this->port, $this->path, $this->version, $this->timeout, $xmlRequest);
		return $this->_parseResult($xmlResult);
	}

	//******************************************************************
	function _post($protocol, $host, $port, $path, $version, $timeout, $xmlRequest) {
		$url = $protocol."://".$host.":".$port.$path;
		if ($this->logfile) {
			error_log("------------------------------------------\n", 3, $this->logfile);
			error_log("DATE AND TIME: ".date('Y-m-d H:i:s')."\n", 3, $this->logfile);
			error_log("UPS URL: " . $url . "\n", 3, $this->logfile);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_TIMEOUT, (int)$timeout);
		if ($this->logfile) {
			error_log("UPS REQUEST: " . $xmlRequest . "\n", 3, $this->logfile);
		}
		$xmlResponse = curl_exec ($ch);
		if (curl_errno($ch) && $this->logfile) {
			$error_from_curl = sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch));
			error_log("Error from cURL: " . $error_from_curl . "\n", 3, $this->logfile);
		}
		if ($this->logfile) {
			error_log("UPS RESPONSE: " . $xmlResponse . "\n", 3, $this->logfile);
		}
		curl_close ($ch);

		if(!$xmlResponse)	{
			$xmlResponse = "<?xml version=\"1.0\"?>\n".
			"<RatingServiceSelectionResponse>\n".
			"	 <Response>\n".
			"		 <TransactionReference>\n".
			"			 <CustomerContext>Rating and Service</CustomerContext>\n".
			"			 <XpciVersion>1.0001</XpciVersion>\n".
			"		 </TransactionReference>\n".
			"		 <ResponseStatusCode>0</ResponseStatusCode>\n".
			"		 <ResponseStatusDescription>". tra( 'An unknown error occured while attempting to contact the UPS gateway' ) ."</ResponseStatusDescription>\n".
			"	 </Response>\n".
			"</RatingServiceSelectionResponse>\n";
		}
		return $xmlResponse;
	}

	//*****************************
	function _parseResult($xmlResult) {
		// Parse XML message returned by the UPS post server.
		$doc = new XMLDocument();
		$xp = new XMLParser();
		$xp->setDocument($doc);
		$xp->parse($xmlResult);
		$doc = $xp->getDocument();
		// Get version. Must be xpci version 1.0001 or this might not work.
		$responseVersion = $doc->getValueByPath('RatingServiceSelectionResponse/Response/TransactionReference/XpciVersion');
		if ($this->xpci_version != $responseVersion) {
			$message = tra( 'This module supports only xpci version 1.0001 of the UPS Rates Interface. Please contact the webmaster for additional assistance.' );
			return $message;
		}
		// Get response code. 1 = SUCCESS, 0 = FAIL
		$responseStatusCode = $doc->getValueByPath('RatingServiceSelectionResponse/Response/ResponseStatusCode');
		if ($responseStatusCode != '1') {
			$errorMsg = $doc->getValueByPath('RatingServiceSelectionResponse/Response/Error/ErrorCode');
			$errorMsg .= ": ";
			$errorMsg .= $doc->getValueByPath('RatingServiceSelectionResponse/Response/Error/ErrorDescription');
			return $errorMsg;
		}
		$root = $doc->getRoot();
		$ratedShipments = $root->getElementsByName("RatedShipment");
		$aryProducts = false;
		for ($i = 0; $i < count($ratedShipments); $i++) {
			$serviceCode = $ratedShipments[$i]->getValueByPath("/Service/Code");
			$totalCharge = false;
			if ($this->upsShipperNumber != '') {
				$totalCharge = $ratedShipments[$i]->getValueByPath("/NegotiatedRates/NetSummaryCharges/GrandTotal/MonetaryValue");
			}
			if ($totalCharge === false) {
				$totalCharge = $ratedShipments[$i]->getValueByPath("/TotalCharges/MonetaryValue");
			}
			if (!($serviceCode && $totalCharge)) {
				continue;
			}
			$ratedPackages = $ratedShipments[$i]->getElementsByName("RatedPackage");
			$this->boxCount = count($ratedPackages);
			$gdaysToDelivery = $ratedShipments[$i]->getValueByPath("/GuaranteedDaysToDelivery");
			$scheduledTime = $ratedShipments[$i]->getValueByPath("/ScheduledDeliveryTime");
			$title = '';
			$title = $this->service_codes[$this->origin][$serviceCode];

			/* we don't want to use this, it may conflict with time estimation
			if ($gdaysToDelivery) {
				$title .= ' (';
				$title .= $gdaysToDelivery . " Business Days";
				if ($scheduledTime) {
					$title .= ' @ ' . $scheduledTime;
				}
				$title .= ')';
			} elseif ($this->timeintransit > 0) {
				$title .= ' (';
				$title .= $this->timeintransit . " Business Days";
				$title .= ')';
			}
			*/
			$aryProducts[$i] = array($title => $totalCharge);
		}
		return $aryProducts;
	}

	//********************
	function _upsGetTimeServices() {
		if (defined('SHIPPING_DAYS_DELAY')) {
			$shipdate = date("Ymd", time()+(86400*SHIPPING_DAYS_DELAY));
		} else {
			$shipdate = $this->today;
		}

		// Create the access request
		$accessRequestHeader =
		"<?xml version=\"1.0\"?>\n".
		"<AccessRequest xml:lang=\"en-US\">\n".
		"	 <AccessLicenseNumber>". $this->access_key ."</AccessLicenseNumber>\n".
		"	 <UserId>". $this->access_username ."</UserId>\n".
		"	 <Password>". $this->access_password ."</Password>\n".
		"</AccessRequest>\n";

		$timeintransitSelectionRequestHeader =
		"<?xml version=\"1.0\"?>\n".
		"<TimeInTransitRequest xml:lang=\"en-US\">\n".
		"	 <Request>\n".
		"		 <TransactionReference>\n".
		"			 <CustomerContext>Time in Transit</CustomerContext>\n".
		"			 <XpciVersion>". $this->transitxpci_version ."</XpciVersion>\n".
		"		 </TransactionReference>\n".
		"		 <RequestAction>TimeInTransit</RequestAction>\n".
		"	 </Request>\n".
		"	 <TransitFrom>\n".
		"		 <AddressArtifactFormat>\n".
		"			 <PoliticalDivision2>". $this->origin_city ."</PoliticalDivision2>\n".
		"			 <PoliticalDivision1>". $this->origin_stateprov ."</PoliticalDivision1>\n".
		"			 <CountryCode>". $this->_upsOriginCountryCode ."</CountryCode>\n".
		"			 <PostcodePrimaryLow>". $this->origin_postalcode ."</PostcodePrimaryLow>\n".
		"		 </AddressArtifactFormat>\n".
		"	 </TransitFrom>\n".
		"	 <TransitTo>\n".
		"		 <AddressArtifactFormat>\n".
		"			 <PoliticalDivision2>". $this->_upsDestCity ."</PoliticalDivision2>\n".
		"			 <PoliticalDivision1>". $this->_upsDestStateProv ."</PoliticalDivision1>\n".
		"			 <CountryCode>". $this->_upsDestCountryCode ."</CountryCode>\n".
		"			 <PostcodePrimaryLow>". $this->_upsDestPostalCode ."</PostcodePrimaryLow>\n".
		"			 <PostcodePrimaryHigh>". $this->_upsDestPostalCode ."</PostcodePrimaryHigh>\n".
		"		 </AddressArtifactFormat>\n".
		"	 </TransitTo>\n".
		"	 <PickupDate>" . $shipdate . "</PickupDate>\n".
		"	 <ShipmentWeight>\n".
		"		 <UnitOfMeasurement>\n".
		"			 <Code>" . $this->unit_weight . "</Code>\n".
		"		 </UnitOfMeasurement>\n".
		"		 <Weight>10</Weight>\n".
		"	 </ShipmentWeight>\n".
		"	 <InvoiceLineTotal>\n".
		"		 <CurrencyCode>USD</CurrencyCode>\n".
		"		 <MonetaryValue>100</MonetaryValue>\n".
		"	 </InvoiceLineTotal>\n".
		"</TimeInTransitRequest>\n";

		$xmlTransitRequest = $accessRequestHeader .
		$timeintransitSelectionRequestHeader;

		//post request $strXML;
		$xmlTransitResult = $this->_post($this->protocol, $this->host, $this->port, $this->transitpath, $this->transitversion, $this->timeout, $xmlTransitRequest);
		return $this->_transitparseResult($xmlTransitResult);
	}

	//***************************************
	// GM 11-15-2004: modified to return array with time for each service, as
	//				opposed to single transit time for hardcoded "GND" code

	function _transitparseResult($xmlTransitResult) {
		$transitTime = array();
		// Parse XML message returned by the UPS post server.
		$doc = new XMLDocument();
		$xp = new XMLParser();
		$xp->setDocument($doc);
		$xp->parse($xmlTransitResult);
		$doc = $xp->getDocument();
		// Get version. Must be xpci version 1.0001 or this might not work.
		$responseVersion = $doc->getValueByPath('TimeInTransitResponse/Response/TransactionReference/XpciVersion');
		if ($this->transitxpci_version != $responseVersion) {
			$message = MODULE_SHIPPING_UPSXML_TEXT_COMM_VERSION_ERROR;
			return $message;
		}
		// Get response code. 1 = SUCCESS, 0 = FAIL
		$responseStatusCode = $doc->getValueByPath('TimeInTransitResponse/Response/ResponseStatusCode');
		if ($responseStatusCode != '1') {
			$errorMsg = $doc->getValueByPath('TimeInTransitResponse/Response/Error/ErrorCode');
			$errorMsg .= ": ";
			$errorMsg .= $doc->getValueByPath('TimeInTransitResponse/Response/Error/ErrorDescription');
			return $errorMsg;
		}
		$root = $doc->getRoot();
		$rootChildren = $root->getChildren();
		for ($r = 0; $r < count($rootChildren); $r++) {
			$elementName = $rootChildren[$r]->getName();
			if ($elementName == "TransitResponse") {
				$transitResponse = $root->getElementsByName("TransitResponse");
				$serviceSummary = $transitResponse['0']->getElementsByName("ServiceSummary");
				$this->numberServices = count($serviceSummary);
				for ($s = 0; $s < $this->numberServices ; $s++) {
					// index by Desc because that's all we can relate back to the service with
					// (though it can probably return the code as well..)
					$serviceDesc = $serviceSummary[$s]->getValueByPath("Service/Description");
					$transitTime[$serviceDesc]["days"] = $serviceSummary[$s]->getValueByPath("EstimatedArrival/BusinessTransitDays");
					$transitTime[$serviceDesc]["date"] = $serviceSummary[$s]->getValueByPath("EstimatedArrival/Date");
					$transitTime[$serviceDesc]["guaranteed"] = $serviceSummary[$s]->getValueByPath("Guaranteed/Code");
				}
			}
		}
		if ($this->logfile) {
			error_log("------------------------------------------\n", 3, $this->logfile);
			foreach($transitTime as $desc => $time) {
				error_log("Business Transit: " . $desc ." = ". $time["date"] . "\n", 3, $this->logfile);
			}
		}
		return $transitTime;
	}
	//EOF Time In Transit

	//***************************
	function exclude_choices($type) {
		// used for exclusion of UPS shipping options, read from db
		$allowed_types = explode(",", MODULE_SHIPPING_UPSXML_TYPES);
		if (strstr($type, "UPS")) {
			// this will chop off "UPS" from the beginning of the line - typically something like UPS Next Day Air (1 Business Days)
			$type_minus_ups = explode("UPS", $type );
				if (strstr($type, "(")) {
				// this will chop off (x Business Days)
				$type_minus_bd = explode("(", $type_minus_ups[1] );
				// get rid of white space with trim
				$type_root = trim($type_minus_bd[0]);
			} else { // end if (strstr($type, "("))
				// if service description contains UPS but not (x Business days):
				$type_root = trim($type_minus_ups[1]);
			} // end if (strstr($type, "UPS"):
		} elseif (strstr($type, "(")) {
			// if service description doesn't contain UPS, but does (x Business Days):
			$type_minus_ups_bd = explode("(", $type );
			$type_root = trim($type_minus_ups_bd[0]);
		} else { // service description neither contain UPS nor (x Business Days)
			$type_root = trim($type);
		}
		for ($za = 0; $za < count ($allowed_types); $za++ ) {
			if ($type_root == trim($allowed_types[$za])) {
				return true;
				exit;
			} // end if ($type_root == $allowed_types[$za] ...
		}
		// if the type is not allowed:
		return false;
	}

	//**************
	function install() {
		global $db;
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable UPS Shipping', 'MODULE_SHIPPING_UPSXML_STATUS', 'True', 'Do you want to offer UPS shipping?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS Rates Access Key', 'MODULE_SHIPPING_UPSXML_ACCESS_KEY', '', 'Enter the XML rates access key assigned to you by UPS; see https://www.ups.com/upsdeveloperkit', '6', '1', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS Rates Username', 'MODULE_SHIPPING_UPSXML_USERNAME', '', 'Enter your UPS Services account username.', '6', '2', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS Rates Password', 'MODULE_SHIPPING_UPSXML_PASSWORD', '', 'Enter your UPS Services account password.', '6', '3', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS Rates &quot;Shipper Number&quot;', 'MODULE_SHIPPING_UPSXML_SHIPPER_NUMBER', '', 'Enter your UPS Services <em>Shipper Number</em>, if you want to receive your account''s negotiated rates!.', '6', '3', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('UPS XML Display Options', 'MODULE_SHIPPING_UPSXML_OPTIONS', '--none--', 'Select from the following the UPS options.', '6', '16', 'zen_cfg_select_multioption(array(''Display weight'', ''Display transit time''), ',	now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable debug?', 'MODULE_SHIPPING_UPSXML_DEBUG', 'false', 'Enable the shipping-module''s debug and the file /logs/upsxml.log will be updated each time a quote is requested.', '6', '16', 'zen_cfg_select_option(array(''true'', ''false''), ',	now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Pickup Method', 'MODULE_SHIPPING_UPSXML_PICKUP_METHOD', 'Daily Pickup', 'How do you give packages to UPS?', '6', '4', 'zen_cfg_select_option(array(''Daily Pickup'', ''Customer Counter'', ''One Time Pickup'', ''On Call Air Pickup'', ''Letter Center'', ''Air Service Center''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Packaging Type', 'MODULE_SHIPPING_UPSXML_PACKAGE_TYPE', 'Customer Package', 'What kind of packaging do you use?', '6', '5', 'zen_cfg_select_option(array(''Customer Package'', ''UPS Letter'', ''UPS Tube'', ''UPS Pak'', ''UPS Express Box'', ''UPS 25kg Box'', ''UPS 10kg box''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Customer Classification Code', 'MODULE_SHIPPING_UPSXML_CUSTOMER_CLASSIFICATION_CODE', '01', '00 - Account Rates, 01 - If you are billing to a UPS account and have a daily UPS pickup, 04 - If you are shipping from a retail outlet, 53 - Standard Rates', '6', '6', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Origin', 'MODULE_SHIPPING_UPSXML_ORIGIN', 'US Origin', 'What origin point should be used (this setting affects only what UPS product names are shown to the user)', '6', '7', 'zen_cfg_select_option(array(''US Origin'', ''Canada Origin'', ''European Union Origin'', ''Puerto Rico Origin'', ''Mexico Origin'', ''All other origins''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin City', 'MODULE_SHIPPING_UPSXML_CITY', '', 'Enter the name of the origin city.', '6', '8', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin State/Province', 'MODULE_SHIPPING_UPSXML_STATEPROV', '', 'Enter the two-letter code for your origin state/province.', '6', '9', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin Country', 'MODULE_SHIPPING_UPSXML_COUNTRY', '', 'Enter the two-letter code for your origin country.', '6', '10', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin Zip/Postal Code', 'MODULE_SHIPPING_UPSXML_POSTALCODE', '', 'Enter your origin zip/postalcode.', '6', '11', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test or Production Mode', 'MODULE_SHIPPING_UPSXML_MODE', 'Test', 'Use this module in Test or Production mode?', '6', '12', 'zen_cfg_select_option(array(''Test'', ''Production''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Unit Weight', 'MODULE_SHIPPING_UPSXML_UNIT_WEIGHT', 'LBS', 'By what unit are your packages weighed?', '6', '13', 'zen_cfg_select_option(array(''LBS'', ''KGS''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Unit Length', 'MODULE_SHIPPING_UPSXML_UNIT_LENGTH', 'IN', 'By what unit are your packages sized?', '6', '14', 'zen_cfg_select_option(array(''IN'', ''CM''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Quote Type', 'MODULE_SHIPPING_UPSXML_QUOTE_TYPE', 'Commercial', 'Quote for Residential or Commercial Delivery', '6', '15', 'zen_cfg_select_option(array(''Commercial'', ''Residential''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_UPSXML_HANDLING', '0', 'Handling fee for this shipping method.', '6', '16', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS Currency Code', 'MODULE_SHIPPING_UPSXML_CURRENCY_CODE', '" . DEFAULT_CURRENCY . "', 'Enter the 3 letter currency code for your country of origin. United States (USD)', '6', '2', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Insurance', 'MODULE_SHIPPING_UPSXML_INSURE', 'True', 'Do you want to insure packages shipped by UPS?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_UPSXML_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '17', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_UPSXML_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '18', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_SHIPPING_UPSXML_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '19', now())");
		// add key for allowed shipping methods
		$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Methods', 'MODULE_SHIPPING_UPSXML_TYPES', 'Next Day Air, 2nd Day Air, Ground, Worldwide Express, Standard, 3 Day Select', 'Select the UPS services to be offered.', '6', '20', 'zen_cfg_select_multioption(array(''Next Day Air'', ''2nd Day Air'', ''Ground'', ''Worldwide Express'', ''Worldwide Expedited'', ''Standard'', ''3 Day Select'', ''Next Day Air Saver'', ''Next Day Air Early A.M.'', ''Worldwide Express Plus'', ''2nd Day Air A.M.'', ''Express NA1'', ''Express Saver''), ',	now())");
		// add key for shipping delay
	}

	//*************
	function keys() {
		return array('MODULE_SHIPPING_UPSXML_STATUS',
					 'MODULE_SHIPPING_UPSXML_TAX_CLASS',
					 'MODULE_SHIPPING_UPSXML_ZONE',
					 'MODULE_SHIPPING_UPSXML_SORT_ORDER',
					 'MODULE_SHIPPING_UPSXML_PICKUP_METHOD',
					 'MODULE_SHIPPING_UPSXML_PACKAGE_TYPE',
					 'MODULE_SHIPPING_UPSXML_CUSTOMER_CLASSIFICATION_CODE',
					 'MODULE_SHIPPING_UPSXML_ORIGIN',
					 'MODULE_SHIPPING_UPSXML_CITY',
					 'MODULE_SHIPPING_UPSXML_STATEPROV',
					 'MODULE_SHIPPING_UPSXML_COUNTRY',
					 'MODULE_SHIPPING_UPSXML_POSTALCODE',
					 'MODULE_SHIPPING_UPSXML_CURRENCY_CODE',
					 'MODULE_SHIPPING_UPSXML_UNIT_WEIGHT',
					 'MODULE_SHIPPING_UPSXML_UNIT_LENGTH',
					 'MODULE_SHIPPING_UPSXML_QUOTE_TYPE',
					 'MODULE_SHIPPING_UPSXML_HANDLING',
					 'MODULE_SHIPPING_UPSXML_INSURE',
					 'MODULE_SHIPPING_UPSXML_TYPES',
					 'MODULE_SHIPPING_UPSXML_ACCESS_KEY',
					 'MODULE_SHIPPING_UPSXML_USERNAME',
					 'MODULE_SHIPPING_UPSXML_PASSWORD',
					 'MODULE_SHIPPING_UPSXML_SHIPPER_NUMBER',
					 'MODULE_SHIPPING_UPSXML_OPTIONS',
					 'MODULE_SHIPPING_UPSXML_DEBUG',
					 'MODULE_SHIPPING_UPSXML_MODE',
					 );
	}

}

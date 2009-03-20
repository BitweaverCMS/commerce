<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: usps.php,v 1.13 2009/03/20 04:39:37 spiderr Exp $
//
require_once( BITCOMMERCE_PKG_PATH.'includes/classes/http_client.php' );

class usps {
	var $code, $title, $description, $icon, $enabled, $countries;

// class constructor
	function usps() {
		global $order, $gBitDb, $template, $current_page_base;

		$this->code = 'usps';
		$this->title = MODULE_SHIPPING_USPS_TEXT_TITLE;
		$this->description = MODULE_SHIPPING_USPS_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_SHIPPING_USPS_SORT_ORDER;
		$this->icon = 'shipping_usps';
		$this->tax_class = MODULE_SHIPPING_USPS_TAX_CLASS;
		$this->tax_basis = MODULE_SHIPPING_USPS_TAX_BASIS;
		$this->enabled = ((MODULE_SHIPPING_USPS_STATUS == 'True') ? true : false);

		if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_USPS_ZONE > 0) ) {
			$check_flag = false;
			$check = $gBitDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_SHIPPING_USPS_ZONE . "' and `zone_country_id` = ? order by `zone_id`", array( $order->delivery['country']['countries_id'] ) );
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

		$this->types = array('EXPRESS' => 'Express Mail',
							'FIRST CLASS' => 'First-Class Mail',
							'PRIORITY' => 'Priority Mail',
							'PARCEL' => 'Parcel Post',
							'MEDIA' => 'Media Mail',
							'BPM' => 'Bound Printed Material',
							'LIBRARY' => 'Library'
							);

		$this->codes = array('EXPRESS' => 'USPSEXP',
							'FIRST CLASS' => 'First-Class Mail',
							'PRIORITY' => 'USPSPRI',
							'PARCEL' => 'USPSPAR',
							'MEDIA' => 'USPSREG',
							'Global Express Mail (EMS)' => 'USPSIGEM',
							'Global Express Guaranteed Non-Document Service' => 'USPSIGDX',
							'Airmail Parcel Post' => 'USPSIAPP',
							);


		$this->intl_types = array('GXG Document' => 'Global Express Guaranteed Document Service',
									'GXG Non-Document' => 'Global Express Guaranteed Non-Document Service',
									'Express' => 'Global Express Mail (EMS)',
									'Priority Lg' => 'Global Priority Mail - Flat-rate Envelope (Large)',
									'Priority Sm' => 'Global Priority Mail - Flat-rate Envelope (Small)',
									'Priority Var' => 'Global Priority Mail - Variable Weight (Single)',
									'Airmail Letter' => 'Airmail Letter-post',
									'Airmail Parcel' => 'Airmail Parcel Post',
									'Surface Letter' => 'Economy (Surface) Letter-post',
									'Surface Post' => 'Economy (Surface) Parcel Post');


		$this->countries = $this->country_list();
	}

// class methods
	function quote( $pShipHash = array() ) {
		global $order, $transittime;

		if ( !empty( $pShipHash['method'] ) && (isset($this->types[$pShipHash['method']]) || in_array($pShipHash['method'], $this->intl_types)) ) {
			$this->_setService( $pShipHash['method'] );
		}


		// usps doesnt accept zero weight
		$shippingWeight = (!empty( $pShipHash['shipping_weight'] ) && $pShipHash['shipping_weight'] > 0.1 ? $pShipHash['shipping_weight'] : 0.1);
		$shippingNumBoxes = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);
		$shipping_pounds = floor ($shippingWeight);
		$shipping_ounces = round(16 * ($shippingWeight - floor($shippingWeight)));

		// weight must be less than 35lbs and greater than 6 ounces or it is not machinable
		switch(true) {
			case ($shipping_pounds == 0 and $shipping_ounces < 6):
				// override admin choice too light
				$is_machinable = 'False';
				break;
			case ($shippingWeight > 35):
				// override admin choice too heavy
				$is_machinable = 'False';
				break;
			default:
				// admin choice on what to use
				$is_machinable = MODULE_SHIPPING_USPS_MACHINABLE;
		}

		$this->_setMachinable($is_machinable);
		$this->_setContainer('None');
		$this->_setSize('REGULAR');

		$this->_setWeight($shipping_pounds, $shipping_ounces);
		$uspsQuote = $this->_getQuote();

		if (is_array($uspsQuote)) {
			if (isset($uspsQuote['error'])) {
				$this->quotes = array('module' => $this->title, 'error' => $uspsQuote['error']);
			} else {

				if (in_array('Display weight', explode(', ', MODULE_SHIPPING_USPS_OPTIONS))) {
					switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
						case (0):
							$show_box_weight = '';
							break;
						case (1):
							$show_box_weight = $shippingNumBoxes . ' ' . TEXT_SHIPPING_BOXES;
							break;
						case (2):
							$show_box_weight = number_format($shippingWeight * $shippingNumBoxes,2) . tra( 'lbs' );
							break;
						default:
							$show_box_weight = $shippingNumBoxes . ' x ' . number_format($shippingWeight,2) . tra( 'lbs' );
							break;
					}
				}

				$this->quotes = array('id' => $this->code, 'module' => $this->title, 'weight' => $show_box_weight);

				$methods = array();
				$size = sizeof($uspsQuote);
				for ($i=0; $i<$size; $i++) {
					list($type, $cost) = each($uspsQuote[$i]);
					$type = strtoupper( $type ); //safely upper casing as USPS has fiddled with case unannounced before, see 2007-NOV-19
					$title = ((isset($this->types[$type])) ? $this->types[$type] : $type);
					$code = ((isset($this->codes[$type])) ? $this->codes[$type] : '');
					if(in_array('Display transit time', explode(', ', MODULE_SHIPPING_USPS_OPTIONS))) {
						$title .= $transittime[$type];
					}
					$methods[] = array('id' => $type,
									'code' => $code,
									'title' => $title,
									'cost' => ($cost + MODULE_SHIPPING_USPS_HANDLING) * $shippingNumBoxes);
				}

				$this->quotes['methods'] = $methods;

				if ($this->tax_class > 0) {
					$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
				}
			}
		} else {
			// Lack of quotes is not necessarily an error
//			$this->quotes = array('module' => $this->title, 'error' => MODULE_SHIPPING_USPS_TEXT_ERROR);
		}

		if (zen_not_null($this->icon)) {
			$this->quotes['icon'] = $this->icon;
		}

		return $this->quotes;
	}

	function check() {
		global $gBitDb;
		if (!isset($this->_check)) {
			$check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_SHIPPING_USPS_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable USPS Shipping', 'MODULE_SHIPPING_USPS_STATUS', 'True', 'Do you want to offer USPS shipping?', '7', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Enter the USPS User ID', 'MODULE_SHIPPING_USPS_USERID', 'NONE', 'Enter the USPS USERID assigned to you.', '7', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Enter the USPS Password', 'MODULE_SHIPPING_USPS_PASSWORD', 'NONE', 'See USERID, above.', '7', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Which USPS server to use', 'MODULE_SHIPPING_USPS_SERVER', 'production', 'An account at USPS is needed to use the Production server', '7', '0', 'zen_cfg_select_option(array(\'test\', \'production\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('All Packages are Machinable', 'MODULE_SHIPPING_USPS_MACHINABLE', 'False', 'Are all products shipped machinable based on C700 Package Services 2.0 Nonmachinable PARCEL POST USPS Rules and Regulations?<br /><br /><strong>Note: Nonmachinable packages will usually result in a higher Parcel Post Rate Charge.<br /><br />Packages 35lbs or more, or less than 6 ounces (.375), will be overridden and set to False</strong>', '7', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Handling Fee', 'MODULE_SHIPPING_USPS_HANDLING', '0', 'Handling fee for this shipping method.', '7', '0', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_SHIPPING_USPS_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '7', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Tax Basis', 'MODULE_SHIPPING_USPS_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '7', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Shipping Zone', 'MODULE_SHIPPING_USPS_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '7', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_SHIPPING_USPS_SORT_ORDER', '0', 'Sort order of display.', '7', '0', now())");
	// BOF: UPS USPS
			$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Domestic Shipping Methods', 'MODULE_SHIPPING_USPS_TYPES', 'EXPRESS, PRIORITY, FIRST CLASS, PARCEL, MEDIA, BPM, LIBRARY', 'Select the domestic services to be offered:', '6', '14', 'zen_cfg_select_multioption(array(\'EXPRESS\', \'PRIORITY\', \'FIRST CLASS\', \'PARCEL\', \'MEDIA\', \'BPM\', \'LIBRARY\'), ',  now())");
			$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Int\'l Shipping Methods', 'MODULE_SHIPPING_USPS_TYPES_INTL', 'GXG Document, GXG Non-Document, Express, Priority Lg, Priority Sm, Priority Var, Airmail Letter, Airmail Parcel, Surface Letter, Surface Post', 'Select the international services to be offered:', '6', '15', 'zen_cfg_select_multioption(array(\'GXG Document\', \'GXG Non-Document\', \'Express\', \'Priority Lg\', \'Priority Sm\', \'Priority Var\', \'Airmail Letter\', \'Airmail Parcel\', \'Surface Letter\', \'Surface Post\'), ',  now())");
			$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('USPS Options', 'MODULE_SHIPPING_USPS_OPTIONS', 'Display weight, Display transit time', 'Select from the following the USPS options.', '6', '16', 'zen_cfg_select_multioption(array(\'Display weight\', \'Display transit time\'), ',  now())");
	// EOF: UPS USPS
	}

	function remove() {
		global $gBitDb;
		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
		return array('MODULE_SHIPPING_USPS_STATUS', 'MODULE_SHIPPING_USPS_USERID', 'MODULE_SHIPPING_USPS_PASSWORD', 'MODULE_SHIPPING_USPS_SERVER', 'MODULE_SHIPPING_USPS_HANDLING', 'MODULE_SHIPPING_USPS_TAX_CLASS', 'MODULE_SHIPPING_USPS_TAX_BASIS', 'MODULE_SHIPPING_USPS_ZONE', 'MODULE_SHIPPING_USPS_SORT_ORDER', 'MODULE_SHIPPING_USPS_MACHINABLE', 'MODULE_SHIPPING_USPS_OPTIONS', 'MODULE_SHIPPING_USPS_TYPES', 'MODULE_SHIPPING_USPS_TYPES_INTL');
//      return array('MODULE_SHIPPING_USPS_STATUS', 'MODULE_SHIPPING_USPS_USERID', 'MODULE_SHIPPING_USPS_PASSWORD', 'MODULE_SHIPPING_USPS_SERVER', 'MODULE_SHIPPING_USPS_HANDLING', 'MODULE_SHIPPING_USPS_TAX_CLASS', 'MODULE_SHIPPING_USPS_ZONE', 'MODULE_SHIPPING_USPS_SORT_ORDER', 'MODULE_SHIPPING_USPS_OPTIONS', 'MODULE_SHIPPING_USPS_TYPES', 'MODULE_SHIPPING_USPS_TYPES_INTL');
	}

	function _setService($service) {
		$this->service = $service;
	}

	function _setWeight($pounds, $ounces=0) {
		$this->pounds = $pounds;
		$this->ounces = $ounces;
	}

	function _setContainer($container) {
		$this->container = $container;
	}

	function _setSize($size) {
		$this->size = $size;
	}

	function _setMachinable($machinable) {
		$this->machinable = $machinable;
	}

	function _getQuote() {
// BOF: UPS USPS
		global $order, $transittime;
		if(in_array('Display transit time', explode(', ', MODULE_SHIPPING_USPS_OPTIONS))) {
			$transit = TRUE;
		}
// EOF: UPS USPS
		if ($order->delivery['country']['countries_id'] == SHIPPING_ORIGIN_COUNTRY) {
			$request  = '<RateRequest USERID="' . MODULE_SHIPPING_USPS_USERID . '" PASSWORD="' . MODULE_SHIPPING_USPS_PASSWORD . '">';
			$services_count = 0;

			if (isset($this->service)) {
			$this->types = array($this->service => $this->types[$this->service]);
			}

			$dest_zip = str_replace(' ', '', $order->delivery['postcode']);
			if ($order->delivery['country']['countries_iso_code_2'] == 'US') $dest_zip = substr($dest_zip, 0, 5);

			reset($this->types);
// BOF: UPS USPS
			$allowed_types = explode(", ", MODULE_SHIPPING_USPS_TYPES);
			while (list($key, $value) = each($this->types)) {
// BOF: UPS USPS
				if ( !in_array($key, $allowed_types) ) {
					continue;
				}
				$request .= '<Package ID="' . $services_count . '">' .
							'<Service>' . $key . '</Service>' .
							'<ZipOrigination>' . SHIPPING_ORIGIN_ZIP . '</ZipOrigination>' .
							'<ZipDestination>' . $dest_zip . '</ZipDestination>' .
							'<Pounds>'.$this->pounds.'</Pounds>' .
							'<Ounces>'.$this->ounces.'</Ounces>' .
							'<Container>' . $this->container . '</Container>' .
							'<Size>' . $this->size . '</Size>' .
							'<Machinable>' . $this->machinable . '</Machinable>' .
							'</Package>';
// BOF: UPS USPS
				if($transit){
					$transitreq  = 'USERID="' . MODULE_SHIPPING_USPS_USERID .
								'" PASSWORD="' . MODULE_SHIPPING_USPS_PASSWORD . '">' .
								'<OriginZip>' . SHIPPING_ORIGIN_ZIP . '</OriginZip>' .
								'<DestinationZip>' . $dest_zip . '</DestinationZip>';

					switch( strtoupper( $key ) ) {
						case 'EXPRESS':
							$transreq[$key] = 'API=ExpressMail&XML=' . urlencode( '<ExpressMailRequest ' . $transitreq . '</ExpressMailRequest>');
							break;
						case 'PRIORITY':
							$transreq[$key] = 'API=PriorityMail&XML=' . urlencode( '<PriorityMailRequest ' . $transitreq . '</PriorityMailRequest>');
							break;
						case 'BPM':
						case 'LIBRARY':
						case 'MEDIA':
						case 'PARCEL':
							$transreq[$key] = 'API=StandardB&XML=' . urlencode( '<StandardBRequest ' . $transitreq . '</StandardBRequest>');
							break;
						default:
							$transreq[$key] = '';
							break;
					}
				}
// EOF: UPS USPS
				$services_count++;
			}
			$request .= '</RateRequest>';
			$request = 'API=Rate&XML=' . urlencode($request);
		} else {
			$request  = '<IntlRateRequest USERID="' . MODULE_SHIPPING_USPS_USERID . '" PASSWORD="' . MODULE_SHIPPING_USPS_PASSWORD . '">' .
						'<Package ID="0">' .
						'<Pounds>' . $this->pounds . '</Pounds>' .
						'<Ounces>' . $this->ounces . '</Ounces>' .
						'<MailType>Package</MailType>' .
						'<Country>' . $this->countries[$order->delivery['country']['countries_iso_code_2']] . '</Country>' .
						'</Package>' .
						'</IntlRateRequest>';

			$request = 'API=IntlRate&XML=' . urlencode($request);
		}

		switch (MODULE_SHIPPING_USPS_SERVER) {
			case 'production':
				$usps_server = 'production.shippingapis.com';
				$api_dll = 'shippingapi.dll';
				break;
			case 'test':
			default:
				$usps_server = 'testing.shippingapis.com';
				$api_dll = 'ShippingAPITest.dll';
				break;
		}

		$body = '';

		$http = new httpClient();
		if ($http->Connect($usps_server, 80)) {
			$http->addHeader('Host', $usps_server);
			$http->addHeader('User-Agent', 'bitcommerce');
			$http->addHeader('Connection', 'Close');

			if ($http->Get('/' . $api_dll . '?' . $request)) {
				$body = $http->getBody();
			}
	// BOF: UPS USPS
	//  mail('you@yourdomain.com','USPS rate quote response',$body,'From: <you@yourdomain.com>');
			if ($transit && is_array($transreq) && ($order->delivery['country']['countries_id'] == STORE_COUNTRY)) {
				while (list($key, $value) = each($transreq)) {
					if ($http->Get('/' . $api_dll . '?' . $value)) {
						$transresp[$key] = $http->getBody();
					}
				}
			}
	// EOF: UPS USPS

			$http->Disconnect();
		} else {
			return false;
		}

		$response = array();
		while (true) {
			if ($start = strpos($body, '<Package ID=')) {
				$body = substr($body, $start);
				$end = strpos($body, '</Package>');
				$response[] = substr($body, 0, $end+10);
				$body = substr($body, $end+9);
			} else {
				break;
			}
		}

		$rates = array();
		if ($order->delivery['country']['countries_id'] == SHIPPING_ORIGIN_COUNTRY) {
			if (sizeof($response) == '1') {
				if (ereg('<Error>', $response[0])) {
					$number = ereg('<Number>(.*)</Number>', $response[0], $regs);
					$number = $regs[1];
					$description = ereg('<Description>(.*)</Description>', $response[0], $regs);
					$description = $regs[1];

					return array('error' => $number . ' - ' . $description);
				}
			}

			$n = sizeof($response);
			for ($i=0; $i<$n; $i++) {
				if (strpos($response[$i], '<Postage>')) {
					$service = ereg('<Service>(.*)</Service>', $response[$i], $regs);
					$service = $regs[1];
					$postage = ereg('<Postage>(.*)</Postage>', $response[$i], $regs);
					$postage = $regs[1];

					$rates[] = array($service => $postage);

					if ($transit) {
						switch ( strtoupper( $service ) ) {
							case 'EXPRESS':     
								$time = ereg('<MonFriCommitment>(.*)</MonFriCommitment>', $transresp[$service], $tregs);
								$time = $tregs[1];
								if ($time == '' || $time == 'No Data') {
									$time = '1 - 2 ' . tra( 'Days' );
								} else {
									$time = 'Tomorrow by ' . $time;
								}
								break;
							case 'PRIORITY':    
								$time = ereg('<Days>(.*)</Days>', $transresp[$service], $tregs);
								$time = $tregs[1];
								if ($time == '' || $time == 'No Data') {
									$time = '2 - 3 ' . tra( 'Days' );
								} elseif ($time == '1') {
									$time .= ' ' . MODULE_SHIPPING_USPS_TEXT_DAY;
								} else {
									$time .= ' ' . tra( 'Days' );
								}
								break;
							case 'PARCEL':      
								$time = ereg('<Days>(.*)</Days>', $transresp[$service], $tregs);
								$time = $tregs[1];
								if ($time == '' || $time == 'No Data') {
									$time = '4 - 7 ' . tra( 'Days' );
								} elseif ($time == '1') {
									$time .= ' ' . MODULE_SHIPPING_USPS_TEXT_DAY;
								} else {
									$time .= ' ' . tra( 'Days' );
								}
								break;
							case 'FIRST CLASS': 
								$time = '2 - 5 ' . tra( 'Days' );
								break;
							case 'MEDIA':
								$time = '1 - 2 ' . tra( 'Weeks' );
								break;
							default:
					            $time = '';
								break;
						}
						if ($time != '') {
							$transittime[$service] = ' (' . $time . ')';
						}
					}
				}
			}

// This is a hack to force return of something in case USPS servers die
//if( empty( $rates ) ) {
//	$rates[] = array( 'PRIORITY' => 6.25 );
//	return( $rates );
//}

		} else {
			if (ereg('<Error>', $response[0])) {
				$number = ereg('<Number>(.*)</Number>', $response[0], $regs);
				$number = $regs[1];
				$description = ereg('<Description>(.*)</Description>', $response[0], $regs);
				$description = $regs[1];

				return array('error' => $number . ' - ' . $description);
			} else {
			$body = $response[0];
			$services = array();
			while (true) {
				if ($start = strpos($body, '<Service ID=')) {
					$body = substr($body, $start);
					$end = strpos($body, '</Service>');
					$services[] = substr($body, 0, $end+10);
					$body = substr($body, $end+9);
				} else {
					break;
				}
			}

	// BOF: UPS USPS
			$allowed_types = array();
			foreach( explode(", ", MODULE_SHIPPING_USPS_TYPES_INTL) as $value ) $allowed_types[$value] = $this->intl_types[$value];
	// EOF: UPS USPS

			$size = sizeof($services);
			for ($i=0, $n=$size; $i<$n; $i++) {
				if (strpos($services[$i], '<Postage>')) {
					$service = ereg('<SvcDescription>(.*)</SvcDescription>', $services[$i], $regs);
					$service = $regs[1];
					$postage = ereg('<Postage>(.*)</Postage>', $services[$i], $regs);
					$postage = $regs[1];
		// BOF: UPS USPS
					$time = ereg('<SvcCommitments>(.*)</SvcCommitments>', $services[$i], $tregs);
					$time = $tregs[1];
					$time = preg_replace('/Weeks$/', tra( 'Weeks' ), $time);
					$time = preg_replace('/Days$/', tra( 'Days' ), $time);
					$time = preg_replace('/Day$/', MODULE_SHIPPING_USPS_TEXT_DAY, $time);

					if( !in_array($service, $allowed_types) ) continue;
		// EOF: UPS USPS
					if (isset($this->service) && ($service != $this->service) ) {
						continue;
					}

					$rates[] = array($service => $postage);
		// BOF: UPS USPS
				if ($time != '') $transittime[$service] = ' (' . $time . ')';
		// EOF: UPS USPS
					}
				}
			}
		}

		return ((sizeof($rates) > 0) ? $rates : false);
	}

	function country_list() {
	$list = array('AF' => 'Afghanistan',
					'AL' => 'Albania',
					'DZ' => 'Algeria',
					'AD' => 'Andorra',
					'AO' => 'Angola',
					'AI' => 'Anguilla',
					'AG' => 'Antigua and Barbuda',
					'AR' => 'Argentina',
					'AM' => 'Armenia',
					'AW' => 'Aruba',
					'AU' => 'Australia',
					'AT' => 'Austria',
					'AZ' => 'Azerbaijan',
					'BS' => 'Bahamas',
					'BH' => 'Bahrain',
					'BD' => 'Bangladesh',
					'BB' => 'Barbados',
					'BY' => 'Belarus',
					'BE' => 'Belgium',
					'BZ' => 'Belize',
					'BJ' => 'Benin',
					'BM' => 'Bermuda',
					'BT' => 'Bhutan',
					'BO' => 'Bolivia',
					'BA' => 'Bosnia-Herzegovina',
					'BW' => 'Botswana',
					'BR' => 'Brazil',
					'VG' => 'British Virgin Islands',
					'BN' => 'Brunei Darussalam',
					'BG' => 'Bulgaria',
					'BF' => 'Burkina Faso',
					'MM' => 'Burma',
					'BI' => 'Burundi',
					'KH' => 'Cambodia',
					'CM' => 'Cameroon',
					'CA' => 'Canada',
					'CV' => 'Cape Verde',
					'KY' => 'Cayman Islands',
					'CF' => 'Central African Republic',
					'TD' => 'Chad',
					'CL' => 'Chile',
					'CN' => 'China',
					'CX' => 'Christmas Island (Australia)',
					'CC' => 'Cocos Island (Australia)',
					'CO' => 'Colombia',
					'KM' => 'Comoros',
					'CG' => 'Congo (Brazzaville),Republic of the',
					'ZR' => 'Congo, Democratic Republic of the',
					'CK' => 'Cook Islands (New Zealand)',
					'CR' => 'Costa Rica',
					'CI' => 'Cote d\'Ivoire (Ivory Coast)',
					'HR' => 'Croatia',
					'CU' => 'Cuba',
					'CY' => 'Cyprus',
					'CZ' => 'Czech Republic',
					'DK' => 'Denmark',
					'DJ' => 'Djibouti',
					'DM' => 'Dominica',
					'DO' => 'Dominican Republic',
					'TP' => 'East Timor (Indonesia)',
					'EC' => 'Ecuador',
					'EG' => 'Egypt',
					'SV' => 'El Salvador',
					'GQ' => 'Equatorial Guinea',
					'ER' => 'Eritrea',
					'EE' => 'Estonia',
					'ET' => 'Ethiopia',
					'FK' => 'Falkland Islands',
					'FO' => 'Faroe Islands',
					'FJ' => 'Fiji',
					'FI' => 'Finland',
					'FR' => 'France',
					'GF' => 'French Guiana',
					'PF' => 'French Polynesia',
					'GA' => 'Gabon',
					'GM' => 'Gambia',
					'GE' => 'Georgia, Republic of',
					'DE' => 'Germany',
					'GH' => 'Ghana',
					'GI' => 'Gibraltar',
					'GB' => 'Great Britain and Northern Ireland',
					'GR' => 'Greece',
					'GL' => 'Greenland',
					'GD' => 'Grenada',
					'GP' => 'Guadeloupe',
					'GT' => 'Guatemala',
					'GN' => 'Guinea',
					'GW' => 'Guinea-Bissau',
					'GY' => 'Guyana',
					'HT' => 'Haiti',
					'HN' => 'Honduras',
					'HK' => 'Hong Kong',
					'HU' => 'Hungary',
					'IS' => 'Iceland',
					'IN' => 'India',
					'ID' => 'Indonesia',
					'IR' => 'Iran',
					'IQ' => 'Iraq',
					'IE' => 'Ireland',
					'IL' => 'Israel',
					'IT' => 'Italy',
					'JM' => 'Jamaica',
					'JP' => 'Japan',
					'JO' => 'Jordan',
					'KZ' => 'Kazakhstan',
					'KE' => 'Kenya',
					'KI' => 'Kiribati',
					'KW' => 'Kuwait',
					'KG' => 'Kyrgyzstan',
					'LA' => 'Laos',
					'LV' => 'Latvia',
					'LB' => 'Lebanon',
					'LS' => 'Lesotho',
					'LR' => 'Liberia',
					'LY' => 'Libya',
					'LI' => 'Liechtenstein',
					'LT' => 'Lithuania',
					'LU' => 'Luxembourg',
					'MO' => 'Macao',
					'MK' => 'Macedonia, Republic of',
					'MG' => 'Madagascar',
					'MW' => 'Malawi',
					'MY' => 'Malaysia',
					'MV' => 'Maldives',
					'ML' => 'Mali',
					'MT' => 'Malta',
					'MQ' => 'Martinique',
					'MR' => 'Mauritania',
					'MU' => 'Mauritius',
					'YT' => 'Mayotte (France)',
					'MX' => 'Mexico',
					'MD' => 'Moldova',
					'MC' => 'Monaco (France)',
					'MN' => 'Mongolia',
					'MS' => 'Montserrat',
					'MA' => 'Morocco',
					'MZ' => 'Mozambique',
					'NA' => 'Namibia',
					'NR' => 'Nauru',
					'NP' => 'Nepal',
					'NL' => 'Netherlands',
					'AN' => 'Netherlands Antilles',
					'NC' => 'New Caledonia',
					'NZ' => 'New Zealand',
					'NI' => 'Nicaragua',
					'NE' => 'Niger',
					'NG' => 'Nigeria',
					'KP' => 'North Korea (Korea, Democratic People\'s Republic of)',
					'NO' => 'Norway',
					'OM' => 'Oman',
					'PK' => 'Pakistan',
					'PA' => 'Panama',
					'PG' => 'Papua New Guinea',
					'PY' => 'Paraguay',
					'PE' => 'Peru',
					'PH' => 'Philippines',
					'PN' => 'Pitcairn Island',
					'PL' => 'Poland',
					'PT' => 'Portugal',
					'QA' => 'Qatar',
					'RE' => 'Reunion',
					'RO' => 'Romania',
					'RU' => 'Russia',
					'RW' => 'Rwanda',
					'SH' => 'Saint Helena',
					'KN' => 'Saint Kitts (St. Christopher and Nevis)',
					'LC' => 'Saint Lucia',
					'PM' => 'Saint Pierre and Miquelon',
					'VC' => 'Saint Vincent and the Grenadines',
					'SM' => 'San Marino',
					'ST' => 'Sao Tome and Principe',
					'SA' => 'Saudi Arabia',
					'SN' => 'Senegal',
					'YU' => 'Serbia-Montenegro',
					'SC' => 'Seychelles',
					'SL' => 'Sierra Leone',
					'SG' => 'Singapore',
					'SK' => 'Slovak Republic',
					'SI' => 'Slovenia',
					'SB' => 'Solomon Islands',
					'SO' => 'Somalia',
					'ZA' => 'South Africa',
					'GS' => 'South Georgia (Falkland Islands)',
					'KR' => 'South Korea (Korea, Republic of)',
					'ES' => 'Spain',
					'LK' => 'Sri Lanka',
					'SD' => 'Sudan',
					'SR' => 'Suriname',
					'SZ' => 'Swaziland',
					'SE' => 'Sweden',
					'CH' => 'Switzerland',
					'SY' => 'Syrian Arab Republic',
					'TW' => 'Taiwan',
					'TJ' => 'Tajikistan',
					'TZ' => 'Tanzania',
					'TH' => 'Thailand',
					'TG' => 'Togo',
					'TK' => 'Tokelau (Union) Group (Western Samoa)',
					'TO' => 'Tonga',
					'TT' => 'Trinidad and Tobago',
					'TN' => 'Tunisia',
					'TR' => 'Turkey',
					'TM' => 'Turkmenistan',
					'TC' => 'Turks and Caicos Islands',
					'TV' => 'Tuvalu',
					'UG' => 'Uganda',
					'UA' => 'Ukraine',
					'AE' => 'United Arab Emirates',
					'UY' => 'Uruguay',
					'UZ' => 'Uzbekistan',
					'VU' => 'Vanuatu',
					'VA' => 'Vatican City',
					'VE' => 'Venezuela',
					'VN' => 'Vietnam',
					'WF' => 'Wallis and Futuna Islands',
					'WS' => 'Western Samoa',
					'YE' => 'Yemen',
					'ZM' => 'Zambia',
					'ZW' => 'Zimbabwe');

	return $list;
	}
}
?>

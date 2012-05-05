<?php
/**
 * USPS Module for Zen Cart v1.3.x - v1.5
 * RateV3 Updates to: January 22, 2012 Version F
 *
 * @package shippingMethod
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: usps.php 18348F 2012-01-22 01:22:00Z ajeh $
 */

// bof: functions contributed by Marco B
	//Quote sorting functions
	if (!function_exists('usps_sort_Alphanumeric')) {
		function usps_sort_Alphanumeric ($a, $b) {
			return strcmp($a['title'],$b['title']);
		}
	}
	if (!function_exists('usps_sort_Price')) {
		function usps_sort_Price ($a, $b) {
			$c=(float)$a['cost'];
			$d=(float)$b['cost'];
			if ($c==$d) return 0;
			return ($c>$d?1:-1);
		}
	}
// eof: functions contributed by Marco B

/**
 * USPS Shipping Module class
 *
 */
class usps extends BitBase {
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
	 * Shipping module list of supported countries (unique to USPS/UPS)
	 *
	 * @var array
	 */
	var $countries;
	/**
	 * Constructor
	 *
	 * @return usps
	 */

// use USPS translations for US shops
	 var $usps_countries;

	function usps() {
		global $order, $template, $current_page_base;

	parent::__construct();

		$this->code = 'usps';
		$this->title = MODULE_SHIPPING_USPS_TEXT_TITLE;
		$this->enabled = CommerceSystem::isConfigActive( 'MODULE_SHIPPING_USPS_STATUS' );

		if ( $this->enabled && MODULE_SHIPPING_USPS_DEBUG_MODE != 'Off') {
			$this->title .=	'<span class="alert"> (Debug is ON: ' . MODULE_SHIPPING_USPS_DEBUG_MODE . ')</span>';
		}
		if ( $this->enabled && MODULE_SHIPPING_USPS_SERVER != 'production') {
			$this->title .=	'<span class="alert"> (USPS Server set to: ' . MODULE_SHIPPING_USPS_SERVER . ')</span>';
		}
		$this->description = MODULE_SHIPPING_USPS_TEXT_DESCRIPTION;
		$this->icon = 'shipping_usps';

		if ($this->enabled) {
			$this->sort_order = MODULE_SHIPPING_USPS_SORT_ORDER;
			$this->tax_class = MODULE_SHIPPING_USPS_TAX_CLASS;
			$this->tax_basis = MODULE_SHIPPING_USPS_TAX_BASIS;
			if( defined( 'IS_ADMIN_FLAG' ) ) {
				$chk_sql = $this->mDb->Execute("select * from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_SHIPPING\_USPS\_%' ");
				$chk_keys = $this->keys();
				if (sizeof($chk_keys) != $chk_sql->RecordCount()) {
					$this->title = $this->title . '<span class="alert">' . ' - Missing Keys you should reinstall!' . '</span>';
				}
			}
		}

		if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_USPS_ZONE > 0) ) {
			$check_flag = false;
			$check = $this->mDb->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_USPS_ZONE . "' and zone_country_id = '" . $order->delivery['country']['countries_id'] . "' order by zone_id");
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

		$this->types = array(
				'EXPRESS' => 'Express Mail', // ID="0"
				'FIRST CLASS' => 'First-Class Mail', // ID="1"
				'PRIORITY' => 'Priority Mail', // ID="2"
				'PARCEL' => 'Parcel Post', // ID="3"
				'MEDIA' => 'Media Mail', // ID="4"
				'LIBRARY' => 'Library' // ID="5"
				);

// update and add new shipping names 01-02-2011
// 4 6 7 12 1 10 [17] 2 11 9 16 [24] [25] 8 [22] [23] [18] [20] 14 15 13
// update and new shipping names 01-22-2012
// 4 6 7 12 1 [26] 10 17 2 11 9 16 24 25 8 22 23 18 20 [19] [15] 14 13

		$this->codes = array('EXPRESS' => 'USPSEXP',
							'FIRST CLASS' => 'First-Class Mail',
							'PRIORITY' => 'USPSPRI',
							'PARCEL' => 'USPSPAR',
							'MEDIA' => 'USPSREG',
							'Global Express Mail (EMS)' => 'USPSIGEM',
							'Global Express Guaranteed Non-Document Service' => 'USPSIGDX',
							'Airmail Parcel Post' => 'USPSIAPP',
							);


		$this->intl_types = array(
				'Global Express' => 'Global Express Guaranteed (GXG)**', // ID="4"
				'Global Express Non-Doc Rect' => 'Global Express Guaranteed Non-Document Rectangular', // ID="6"
				'Global Express Non-Doc Non-Rect' => 'Global Express Guaranteed Non-Document Non-Rectangular', // ID="7"
				'USPS GXG Envelopes' => 'USPS GXG Envelopes**', // ID="12"
				'Express Mail Int' => 'Express Mail International', // ID="1"
				'Express Mail Int Flat Rate Box' => 'Express Mail International Flat Rate Boxes', // ID="26"
				'Express Mail Int Flat Rate Env' => 'Express Mail International Flat Rate Envelope', // ID="10"
				'Express Mail Int Legal' => 'Express Mail International Legal Flat Rate Envelope', // ID="17"
				'Priority Mail International' => 'Priority Mail International', // ID="2"
				'Priority Mail Int Flat Rate Lrg Box' => 'Priority Mail International Large Flat Rate Box', // ID="11"
				'Priority Mail Int Flat Rate Med Box' => 'Priority Mail International Medium Flat Rate Box', // ID="9"
				'Priority Mail Int Flat Rate Small Box' => 'Priority Mail International Small Flat Rate Box**', // ID="16"
				'Priority Mail Int DVD' => 'Priority Mail International DVD Flat Rate Box**', // ID="24"
				'Priority Mail Int Lrg Video' => 'Priority Mail International Large Video Flat Rate Box**', // ID="25"
				'Priority Mail Int Flat Rate Env' => 'Priority Mail International Flat Rate Envelope**', // ID="8"
				'Priority Mail Int Legal Flat Rate Env' => 'Priority Mail International Legal Flat Rate Envelope**', // ID="22"
				'Priority Mail Int Padded Flat Rate Env' => 'Priority Mail International Padded Flat Rate Envelope**', // ID="23"
				'Priority Mail Int Gift Card Flat Rate Env' => 'Priority Mail International Gift Card Flat Rate Envelope**', // ID=18
				'Priority Mail Int Small Flat Rate Env' => 'Priority Mail International Small Flat Rate Envelope**', // ID="20"
				'Priority Mail Int Window Flat Rate Env' => 'Priority Mail International Window Flat Rate Envelope**', // ID=19
				'First Class Mail Int Parcel' => 'First-Class Mail International Parcel**', // ID="15" Changed Package to Parcel
				'First Class Mail Int Lrg Env' => 'First-Class Mail International Large Envelope**', // ID="14"
				'First Class Mail Int Letter' => 'First-Class Mail International Letter**' // ID="13"
				);

		$this->countries = $this->country_list();

// use USPS translations for US shops
		$this->usps_countries = $this->usps_translation();

	}

	/**
	 * Get quote from shipping provider's API:
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	function quote( $pShipHash = array() ) {
		// BOF: UPS USPS
		global $order, $transittime;


		if ( !empty( $pShipHash['method'] ) && (isset($this->types[$pShipHash['method']]) || in_array($pShipHash['method'], $this->intl_types)) ) {
			$this->_setService( $pShipHash['method'] );
		}

		// usps doesnt accept zero weight
		$shippingWeight = (!empty( $pShipHash['shipping_weight'] ) && $pShipHash['shipping_weight'] > 0.1 ? $pShipHash['shipping_weight'] : 0.1);
		$shippingNumBoxes = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

		// usps doesnt accept zero weight send 1 ounce (0.0625) minimum
		$usps_shipping_weight = ($shippingWeight <= 0.0 ? 0.0625 : $shippingWeight);
		$shipping_pounds = floor ($usps_shipping_weight);
		$shipping_ounces = (16 * ($usps_shipping_weight - floor($usps_shipping_weight)));
		// usps currently cannot handle more than 5 digits on international
		// change to 2 if International rates fail based on Tare Settings
		$shipping_ounces = zen_round($shipping_ounces, MODULE_SHIPPING_USPS_DECIMALS);

		// weight must be less than 35lbs and greater than 6 ounces or it is not machinable
		switch(true) {
			case ($shipping_pounds == 0 and $shipping_ounces < 6):
			// override admin choice too light
			$is_machinable = 'False';
			break;

			case ($usps_shipping_weight > 35):
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
		$this->_setFirstClassType('FLAT');

		$this->_setWeight($shipping_pounds, $shipping_ounces);
		$uspsQuote = $this->_getQuote();

		$options = explode(', ', MODULE_SHIPPING_USPS_OPTIONS);
		if (is_array($uspsQuote)) {
			if (isset($uspsQuote['error'])) {
				$this->quotes = array('module' => $this->title,
															'error' => $uspsQuote['error']);
			} else {
				if (in_array('Display weight', $options)) {
					switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
						case (0):
						$show_box_weight = '';
						break;
						case (1):
						$show_box_weight = $shippingNumBoxes . ' ' . TEXT_SHIPPING_BOXES;
						break;
						case (2):
						$show_box_weight = number_format($usps_shipping_weight * $shippingNumBoxes,2) . TEXT_SHIPPING_WEIGHT;
						break;
						default:
						$show_box_weight = $shippingNumBoxes . ' x ' . number_format($usps_shipping_weight,2) . TEXT_SHIPPING_WEIGHT;
						break;
					}
				}
				$this->quotes = array(
					'id' => $this->code,
					'module' => $this->title,
					'weight' => $show_box_weight, 
				);

				// set handling fee
				if ($order->delivery['country']['countries_id'] == SHIPPING_ORIGIN_COUNTRY	|| (SHIPPING_ORIGIN_COUNTRY == '223' && $this->usps_countries == 'US')) {
					// national
					$usps_handling_fee = MODULE_SHIPPING_USPS_HANDLING;
				} else {
					// international
					$usps_handling_fee = MODULE_SHIPPING_USPS_HANDLING_INT;
				}

				$methods = array();
				$size = sizeof($uspsQuote);
				for ($i=0; $i<$size; $i++) {
					list($type, $cost) = each($uspsQuote[$i]);

					// BOF: UPS USPS
					$title = ((isset($this->types[$type])) ? $this->types[$type] : $type);
					$code = ((isset($this->codes[$type])) ? $this->codes[$type] : '');
					if( in_array( 'Display transit time', $options ) ) {
						$title .= $transittime[$type];
					}
					// strip the ** from the titles
					$title = str_replace('**', '', $title);
					$cost = preg_replace('/[^0-9.]/', '',	$cost);

// add $this->usps_countries to title to test actual country
					$methods[] = array(	'id' => $type,
										'code' => $code,
										'title' => $title,
										'cost' => ($cost * $shippingNumBoxes) + (MODULE_SHIPPING_USPS_HANDLING_METHOD == 'Box' ? $usps_handling_fee * $shippingNumBoxes : $usps_handling_fee) );

					// bof: sort by contributed by Marco B
					// Sort the options
					if (MODULE_SHIPPING_USPS_QUOTE_SORT != 'Unsorted') {
						usort($methods,'usps_sort_'.MODULE_SHIPPING_USPS_QUOTE_SORT);
					}
					// eof: sort by contributed by Marco B
				}

				$this->quotes['methods'] = $methods;

				if ($this->tax_class > 0) {
					$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
				}
			}
		} elseif ($uspsQuote == -1) {
			$this->quotes = array('module' => $this->title,
														'error' => MODULE_SHIPPING_USPS_TEXT_SERVER_ERROR . (MODULE_SHIPPING_USPS_SERVER=='test' ? MODULE_SHIPPING_USPS_TEXT_TEST_MODE_NOTICE : ''));
		} else {
			$this->quotes = array('module' => $this->title,
														'error' => MODULE_SHIPPING_USPS_TEXT_ERROR . (MODULE_SHIPPING_USPS_SERVER=='test' ? MODULE_SHIPPING_USPS_TEXT_TEST_MODE_NOTICE : ''));
		}

		$this->quotes['icon'] = $this->icon;

		return $this->quotes;
	}
	/**
	 * check status of module
	 *
	 * @return boolean
	 */
	function check() {
		if (!isset($this->_check)) {
			$check_query = $this->mDb->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_USPS_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}
	/**
	 * Install this module
	 *
	 */
	function install() {
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable USPS Shipping', 'MODULE_SHIPPING_USPS_STATUS', 'True', 'Do you want to offer USPS shipping?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter the USPS Web Tools User ID', 'MODULE_SHIPPING_USPS_USERID', 'NONE', 'Enter the USPS USERID assigned to you for Rate Quotes/ShippingAPI.', '6', '0', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Which server to use', 'MODULE_SHIPPING_USPS_SERVER', 'production', 'An account at USPS is needed to use the Production server', '6', '0', 'zen_cfg_select_option(array(''test'', ''production''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('All Packages are Machinable', 'MODULE_SHIPPING_USPS_MACHINABLE', 'False', 'Are all products shipped machinable based on C700 Package Services 2.0 Nonmachinable PARCEL POST USPS Rules and Regulations?<br /><br /><strong>Note: Nonmachinable packages will usually result in a higher Parcel Post Rate Charge.<br /><br />Packages 35lbs or more, or less than 6 ounces (.375), will be overridden and set to False</strong>', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee - US', 'MODULE_SHIPPING_USPS_HANDLING', '0', 'National Handling fee for this shipping method.', '6', '0', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee - International', 'MODULE_SHIPPING_USPS_HANDLING_INT', '0', 'International Handling fee for this shipping method.', '6', '0', now())");

		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box', 'MODULE_SHIPPING_USPS_HANDLING_METHOD', 'Box', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(''Order'', ''Box''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Quote Sort Order', 'MODULE_SHIPPING_USPS_QUOTE_SORT', 'Unsorted', 'Sorts the returned quotes using the service name Alphanumerically or by Price. Unsorted will give the order provided by USPS.', '6', '0', 'zen_cfg_select_option(array(''Unsorted'',''Alphanumeric'', ''Price''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Decimal Settings', 'MODULE_SHIPPING_USPS_DECIMALS', '3', 'Decimal Setting can be 1, 2 or 3. Sometimes International requires 2 decimals, based on Tare Rates or Product weights. Do you want to use 1, 2 or 3 decimals?', '6', '0', 'zen_cfg_select_option(array(''1'', ''2'', ''3''), ', now())");

		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_USPS_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_USPS_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br />Shipping - Based on customers Shipping Address<br />Billing Based on customers Billing address<br />Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(''Shipping'', ''Billing'', ''Store''), ', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_USPS_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_USPS_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");

// National
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Domestic Shipping Methods', 'MODULE_SHIPPING_USPS_TYPES', 'EXPRESS, PRIORITY, FIRST CLASS, PARCEL, MEDIA, LIBRARY', 'Select the domestic services to be offered:', '6', '14', 'zen_cfg_select_multioption(array(''EXPRESS'', ''PRIORITY'', ''FIRST CLASS'', ''PARCEL'', ''MEDIA'', ''LIBRARY''), ',	now())");
// International
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('International Shipping Methods', 'MODULE_SHIPPING_USPS_TYPES_INTL',
		'Global Express, Global Express Non-Doc Rect, Global Express Non-Doc Non-Rect, USPS GXG Envelopes, Express Mail Int, Express Mail Int Flat Rate Box, Express Mail Int Flat Rate Env, Express Mail Int Legal, Priority Mail International, Priority Mail Int Flat Rate Env, Priority Mail Int Flat Rate Small Box, Priority Mail Int Flat Rate Med Box, Priority Mail Int Flat Rate Lrg Box, Priority Mail Int DVD, Priority Mail Int Lrg Video, Priority Mail Int Legal Flat Rate Env, Priority Mail Int Padded Flat Rate Env, Priority Mail Int Gift Card Flat Rate Env, Priority Mail Int Small Flat Rate Env, Priority Mail Int Window Flat Rate Env, First Class Mail Int Lrg Env, First Class Mail Int Parcel, First Class Mail Int Letter',
			'Select the international services to be offered:', '6', '15', 'zen_cfg_select_multioption(
			array(''Global Express'', ''Global Express Non-Doc Rect'', ''Global Express Non-Doc Non-Rect'', ''USPS GXG Envelopes'', ''Express Mail Int'', ''Express Mail Int Flat Rate Box'', ''Express Mail Int Flat Rate Env'', ''Express Mail Int Legal'', ''Priority Mail International'', ''Priority Mail Int Flat Rate Env'', ''Priority Mail Int Flat Rate Small Box'', ''Priority Mail Int Flat Rate Med Box'', ''Priority Mail Int Flat Rate Lrg Box'', ''Priority Mail Int DVD'', ''Priority Mail Int Lrg Video'', ''Priority Mail Int Legal Flat Rate Env'', ''Priority Mail Int Padded Flat Rate Env'', ''Priority Mail Int Gift Card Flat Rate Env'', ''Priority Mail Int Small Flat Rate Env'', ''Priority Mail Int Window Flat Rate Env'', ''First Class Mail Int Lrg Env'', ''First Class Mail Int Parcel'', ''First Class Mail Int Letter''), ',	now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('USPS Options', 'MODULE_SHIPPING_USPS_OPTIONS', 'Display weight, Display transit time', 'Select from the following the USPS options.', '6', '16', 'zen_cfg_select_multioption(array(''Display weight'', ''Display transit time'', ''Query transit time''), ',	now())");
		$this->mDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug Mode', 'MODULE_SHIPPING_USPS_DEBUG_MODE', 'Off', 'Would you like to enable debug mode?	A complete detailed log of USPS quote results may be emailed to the store owner, Log results or displayed to Screen.', '6', '0', 'zen_cfg_select_option(array(''Off'', ''Email'', ''Logs'', ''Screen''), ', now())");
	}
	/**
	 * Remove this module
	 *
	 */
	function remove() {
		$this->mDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_SHIPPING\_USPS\_%' ");
	}
	/**
	 * Build array of keys used for installing/managing this module
	 *
	 * @return array
	 */
	function keys() {
		$keys_list = array('MODULE_SHIPPING_USPS_STATUS', 'MODULE_SHIPPING_USPS_USERID', 'MODULE_SHIPPING_USPS_SERVER', 'MODULE_SHIPPING_USPS_QUOTE_SORT', 'MODULE_SHIPPING_USPS_HANDLING', 'MODULE_SHIPPING_USPS_HANDLING_INT', 'MODULE_SHIPPING_USPS_HANDLING_METHOD', 'MODULE_SHIPPING_USPS_DECIMALS', 'MODULE_SHIPPING_USPS_TAX_CLASS', 'MODULE_SHIPPING_USPS_TAX_BASIS', 'MODULE_SHIPPING_USPS_ZONE', 'MODULE_SHIPPING_USPS_SORT_ORDER', 'MODULE_SHIPPING_USPS_MACHINABLE', 'MODULE_SHIPPING_USPS_OPTIONS', 'MODULE_SHIPPING_USPS_TYPES', 'MODULE_SHIPPING_USPS_TYPES_INTL');
		$keys_list[] = 'MODULE_SHIPPING_USPS_DEBUG_MODE';
		return $keys_list;
	}
	/**
	 * Set USPS service mode
	 *
	 * @param string $service
	 */
	function _setService($service) {
		$this->service = $service;
	}
	/**
	 * Set USPS weight for quotation collection
	 *
	 * @param integer $pounds
	 * @param integer $ounces
	 */
	function _setWeight($pounds, $ounces=0) {
		$this->pounds = $pounds;
		$this->ounces = $ounces;
	}
	/**
	 * Set USPS container type
	 *
	 * @param string $container
	 */
	function _setContainer($container) {
		$this->container = $container;
	}

	/**
	 * Set USPS Firs Class type
	 *
	 * @param string $fctype
	 */
	function _setFirstClassType($fctype) {
		$this->fctype = $fctype;
	}

	/**

	 * Set USPS package size
	 *
	 * @param integer $size
	 */
	function _setSize($size) {
		$this->size = $size;
	}
	/**
	 * Set USPS machinable flag
	 *
	 * @param boolean $machinable
	 */
	function _setMachinable($machinable) {
		$this->machinable = $machinable;
	}
	/**
	 * Get actual quote from USPS
	 *
	 * @return array of results or boolean false if no results
	 */
	function _getQuote() {
		// BOF: UPS USPS
		global $order, $transittime;
		$options = explode(', ', MODULE_SHIPPING_USPS_OPTIONS);
		$transit = (in_array('Display transit time', $options) );
		$queryTransit = (in_array('Query transit time', $options) );

		// EOF: UPS USPS

// translate for US Territories
//		if ($order->delivery['country']['countries_id'] == SHIPPING_ORIGIN_COUNTRY) {
		if ($order->delivery['country']['countries_id'] == SHIPPING_ORIGIN_COUNTRY || (SHIPPING_ORIGIN_COUNTRY == '223' && $this->usps_countries == 'US')) {
			$request	= '<RateV3Request USERID="' . MODULE_SHIPPING_USPS_USERID . '">';
			$services_count = 0;

			if (isset($this->service)) {
				$this->types = array($this->service => $this->types[$this->service]);
			}

			$dest_zip = str_replace(' ', '', $order->delivery['postcode']);
// translate for US Territories
			if ($order->delivery['country']['countries_iso_code_2'] == 'US' || (SHIPPING_ORIGIN_COUNTRY == '223' && $this->usps_countries == 'US')) $dest_zip = substr($dest_zip, 0, 5);

			reset($this->types);
			// BOF: UPS USPS
			$allowed_types = explode(", ", MODULE_SHIPPING_USPS_TYPES);
			while (list($key, $value) = each($this->types)) {
				// BOF: UPS USPS
				if ( !in_array($key, $allowed_types) ) continue;
					//For Options list, go to page 6 of document: http://www.usps.com/webtools/_pdf/Rate-Calculators-v1-2.pdf
					//FIRST CLASS MAIL OPTIONS
					if ($key == 'FIRST CLASS') {
						$this->FirstClassMailType = '<FirstClassMailType>LETTER</FirstClassMailType>';
					} else {
						$this->FirstClassMailType = '';
					}
					//PRIORITY MAIL OPTIONS
					if ($key == 'PRIORITY'){
						$this->container = ''; // Blank, Flate Rate Envelope, or Flat Rate Box // Sm Flat Rate Box, Md Flat Rate Box and Lg Flat Rate Box

					}
					//EXPRESS MAIL OPTIONS
					if ($key == 'EXPRESS'){
						$this->container = '';	// Blank, or Flate Rate Envelope
					}
					//PARCEL POST OPTIONS
					if ($key == 'PARCEL'){
						$this->container = 'Regular';
						$this->machinable = 'true';
					}
					//MEDIA MAIL OPTIONS
					//LIBRARY MAIL OPTIONS
				$request .= '<Package ID="' . $services_count . '">' .
				'<Service>' . $key . '</Service>' .
				'<FirstClassMailType>' . $this->fctype . '</FirstClassMailType>' .
				'<ZipOrigination>' . SHIPPING_ORIGIN_ZIP . '</ZipOrigination>' .
				'<ZipDestination>' . $dest_zip . '</ZipDestination>' .
				'<Pounds>' . $this->pounds . '</Pounds>' .
				'<Ounces>' . $this->ounces . '</Ounces>' .
				'<Container>' . $this->container . '</Container>' .
				'<Size>' . $this->size . '</Size>' .
				'<Machinable>' . $this->machinable . '</Machinable>' .
				'</Package>';
				if( $transit ) {
					$transitreq	= 'USERID="' . MODULE_SHIPPING_USPS_USERID . '">' .
					'<OriginZip>' . SHIPPING_ORIGIN_ZIP . '</OriginZip>' .
					'<DestinationZip>' . $dest_zip . '</DestinationZip>';

					switch ($key) {
						case 'EXPRESS':	
							$transreq[$key] = 'API=ExpressMail&XML=' . urlencode( '<ExpressMailRequest ' . $transitreq . '</ExpressMailRequest>');
							break;
						case 'PRIORITY': 
							$transreq[$key] = 'API=PriorityMail&XML=' . urlencode( '<PriorityMailRequest ' . $transitreq . '</PriorityMailRequest>');
							break;
						case 'MEDIA':	 
						case 'LIBRARY':	 
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
			$request .= '</RateV3Request>';

			$request = 'API=RateV3&XML=' . urlencode($request);
		} else {
			// IntlRateRequest
			$request	= '<IntlRateRequest USERID="' . MODULE_SHIPPING_USPS_USERID . '">' .
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
				$usps_server = 'stg-production.shippingapis.com';
				$api_dll = 'ShippingApi.dll';
				break;
		}

		$body = '';

		$http = new httpClient();
		$http->timeout = 5;
		if ($http->Connect($usps_server, 80)) {
			$http->addHeader('Host', $usps_server);
			$http->addHeader('User-Agent', 'Zen Cart');
			$http->addHeader('Connection', 'Close');

			if ($http->Get('/' . $api_dll . '?' . $request)) $body = $http->getBody();
			if (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Email') mail(STORE_OWNER_EMAIL_ADDRESS, 'Debug: USPS rate quote response', '(You can turn off this debug email by editing your USPS module settings in the admin area of your store.) ' . "\n\n" . $body, 'From: <' . EMAIL_FROM . '>');

// translate for US Territories
//			if ($transit && is_array($transreq) && ($order->delivery['country']['countries_id'] == STORE_COUNTRY)) {
			$transresp = array();
			if( $transit && $queryTransit && is_array($transreq) && ( ($order->delivery['country']['countries_id'] == STORE_COUNTRY || (SHIPPING_ORIGIN_COUNTRY == '223' && $this->usps_countries == 'US') )) ) {
				while (list($key, $value) = each($transreq)) {
					if ($http->Get('/' . $api_dll . '?' . $value)) {
						$transresp[$key] = $http->getBody();
					}
				}
			}
			$http->Disconnect();
		} else {
			return -1;
		}

// strip reg and trade out 01-02-2011
$body = str_replace('&amp;lt;sup&amp;gt;&amp;amp;reg;&amp;lt;/sup&amp;gt;', '', $body);
$body = str_replace('&amp;lt;sup&amp;gt;&amp;amp;trade;&amp;lt;/sup&amp;gt;', '', $body);

// USPS debug to Logs
			if (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Logs' || MODULE_SHIPPING_USPS_DEBUG_MODE == 'Screen') {
				$body_display = $body;
				$body_display = str_replace('<Service ID', (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Logs' ? "\n\n" : '<br /><br />') . '<Service ID', $body_display);
				$body_display = str_replace('</Service>', '</Service>' . "\n\n", $body_display);
				$body_display = str_replace('<MaxDimensions>', "\n" . '<MaxDimensions>', $body_display);
				$body_display = str_replace('</MaxDimensions>', '</MaxDimensions>' . "\n", $body_display);

				$body_display = str_replace('<Package ID', (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Logs' ? "\n\n" : '<br /><br />') . '<Package ID', $body_display);
				$body_display = str_replace('</Package>', '</Package>', $body_display);
				$body_display = str_replace('<Postage CLASSID', "\n" . '<Postage CLASSID', $body_display);
				$body_display = str_replace('</Postage>', '</Postage>' . "\n", $body_display);

				global $shipping_weight;
				$body_display_header = '';
				$body_display_header .= "\n" . 'Server: ' . MODULE_SHIPPING_USPS_SERVER . "\n";
				$body_display_header .=	"\n" . 'Weight = ' . $shipping_weight . ' Pounds: ' . $this->pounds . ' Ounces: ' . $this->ounces . "\n";
				$body_display_header .= 'ZipOrigination: ' . SHIPPING_ORIGIN_ZIP . "\n" . 'ZipDestination: ' . $order->delivery['postcode'] . (!empty($this->countries[$order->delivery['country']['countries_iso_code_2']]) ? ' Country: ' . $this->countries[$order->delivery['country']['countries_iso_code_2']] : '') . "\n";
				$body_display_header .= 'Tare Rates - Small/Medium: ' . SHIPPING_BOX_WEIGHT . ' Large: ' . SHIPPING_BOX_PADDING . "\n";
				$body_display_header .= "\n" . 'RESPONSE FROM USPS: ' . "\n";

				if (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Screen') {
					echo '<br />View Source:<br />' . "\n" . $body_display_header . "\n\n" . $body_display . '<br />';
				}
				if (MODULE_SHIPPING_USPS_DEBUG_MODE == 'Logs') {
					$usps_instance_id = date('mdYGis');
					$logfilename = DIR_FS_SQL_CACHE . '/usps_' . $usps_instance_id . '_' . str_replace(' ', '', $order->delivery['postcode']) . '.log';
					$fp = @fopen($logfilename, 'a');
					if ($fp) {
						fwrite($fp, date('M d Y G:i:s') . ' -- ' . $body_display_header . "\n\n" . $body_display . "\n\n");
						fclose($fp);
					}
				}
			}
//			echo 'USPS METHODS: <pre>'; echo print_r($body); echo '</pre>';


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

// translate for US Territories
//		if ($order->delivery['country']['countries_id'] == SHIPPING_ORIGIN_COUNTRY) {
		if ($order->delivery['country']['countries_id'] == SHIPPING_ORIGIN_COUNTRY	|| (SHIPPING_ORIGIN_COUNTRY == '223' && $this->usps_countries == 'US')) {
			if (sizeof($response) == '1') {
				if (preg_match('/<Error>/i', $response[0])) {
					$number = preg_match('/<Number>(.*)<\/Number>/msi', $response[0], $regs);
					$number = $regs[1];
					$description = preg_match('/<Description>(.*)<\/Description>/msi', $response[0], $regs);
					$description = $regs[1];

					return array('error' => $number . ' - ' . $description);
				}
			}

			$n = sizeof($response);
			for ($i=0; $i<$n; $i++) {
				if (strpos($response[$i], '<Rate>')) {
					$service = preg_match('/<MailService>(.*)<\/MailService>/msi', $response[$i], $regs);
					$service = $regs[1];
					if (preg_match('/Express/i', $service)) $service = 'EXPRESS';
					if (preg_match('/Priority/i', $service)) $service = 'PRIORITY';
					if (preg_match('/First-Class Mail/i', $service)) $service = 'FIRST CLASS';
					if (preg_match('/Parcel/i', $service)) $service = 'PARCEL';
					if (preg_match('/Media/i', $service)) $service = 'MEDIA';
					if (preg_match('/Library/i', $service)) $service = 'LIBRARY';
					$postage = preg_match('/<Rate>(.*)<\/Rate>/msi', $response[$i], $regs);
					$postage = $regs[1];

					$rates[] = array($service => $postage);
					// BOF: UPS USPS
					if ($transit) {
						$time = NULL;
						switch ($service) {
							case 'EXPRESS':
								if( !empty( $transresp[$service] ) ) {
									$time = preg_match('/<MonFriCommitment>(.*)<\/MonFriCommitment>/msi', $transresp[$service], $tregs);
									$time = $tregs[1];
								}
								if( empty( $time ) || $time == 'No Data') {
									$time = '1 - 2 ' . tra( 'Days' );
								} else {
									$time = 'Tomorrow by ' . $time;
								}
								break;
							case 'PRIORITY':
								if( !empty( $transresp[$service] ) ) {
									$time = preg_match('/<Days>(.*)<\/Days>/msi', $transresp[$service], $tregs);
									$time = $tregs[1];
								}
								if( empty( $time ) || $time == 'No Data') {
									$time = '2 - 3 ' . tra( 'Days' );
								} elseif ($time == '1') {
									$time .= ' ' . tra( 'Day' );
								} else {
									$time .= ' ' . tra( 'Days' );
								}
								break;
							case 'MEDIA':			
							case 'PARCEL':			
								if( !empty( $transresp[$service] ) ) {
									$time = preg_match('/<Days>(.*)<\/Days>/msi', $transresp[$service], $tregs);
									$time = $tregs[1];
								}
								if( empty( $time ) || $time == 'No Data') {
									$time = '5 - 7 ' . tra( 'Days' );
								} elseif ($time == '1') {
									$time .= ' ' . tra( 'Day' );
								} else {
									$time .= ' ' . tra( 'Days' );
								}
								break;
							case 'FIRST CLASS': 
								$time = '2 - 5 ' . tra( 'Days' );
								break;


							default:						$time = '';
							break;
						}
						if ($time != '') {
							$transittime[$service] = ' (' . $time . ')';
						} else {
							$transittime[$service] = '';
						}
					}
					// EOF: UPS USPS
				}
			}
		} else {
			if (preg_match('/<Error>/i', $response[0])) {
				$number = preg_match('/<Number>(.*)<\/Number>/msi', $response[0], $regs);
				$number = $regs[1];
				$description = preg_match('/<Description>(.*)<\/Description>/msi', $response[0], $regs);
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

				$allowed_types = array();
				foreach( explode(", ", MODULE_SHIPPING_USPS_TYPES_INTL) as $value ) {
					if( isset( $this->intl_types[$value] ) ) {
						$allowed_types[$value] = $this->intl_types[$value];
					}
				}
				$size = sizeof($services);
				for ($i=0, $n=$size; $i<$n; $i++) {
					if (strpos($services[$i], '<Postage>')) {
						$service = preg_match('/<SvcDescription>(.*)<\/SvcDescription>/msi', $services[$i], $regs);
						$service = $regs[1];
						$postage = preg_match('/<Postage>(.*)<\/Postage>/i', $services[$i], $regs);
						$postage = $regs[1];
						// BOF: UPS USPS
						$time = preg_match('/<SvcCommitments>(.*)<\/SvcCommitments>/msi', $services[$i], $tregs);
						$time = $tregs[1];
						$time = preg_replace('/Weeks$/', tra( 'Weeks' ), $time);
						$time = preg_replace('/Days$/', tra( 'Days' ), $time);
						$time = preg_replace('/Day$/', tra( 'Day' ), $time);

						if( !in_array($service, $allowed_types) ) continue;
						if ($_SESSION['cart']->total > 400 && strstr($services[$i], 'Priority Mail International Flat Rate Envelope')) continue; // skip value > $400 Priority Mail International Flat Rate Envelope
						// EOF: UPS USPS
						if (isset($this->service) && ($service != $this->service) ) {
							continue;
						}

						$rates[] = array($service => $postage);
						if ($time != '') {
							$transittime[$service] = ' (' . $time . ')';
						} else {
							$transittime[$service] = '';
						}
					}
				}
			}
		}

		return ((sizeof($rates) > 0) ? $rates : false);
	}
	/**
	 * USPS Country Code List
	 * This list is used to compare the 2-letter ISO code against the order country ISO code, and provide the proper/expected
	 * spelling of the country name to USPS in order to obtain a rate quote
	 *
	 * @return array
	 */
	function country_list() {
		$list = array(
		'AF' => 'Afghanistan',
		'AL' => 'Albania',
		'AX' => 'Aland Island (Finland)',
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
		'CG' => 'Congo, Republic of the',
		'CD' => 'Congo, Democratic Republic of the',
		'CK' => 'Cook Islands (New Zealand)',
		'CR' => 'Costa Rica',
		'CI' => 'Cote d Ivoire (Ivory Coast)',
		'HR' => 'Croatia',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
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
		'FM' => 'Micronesia, Federated States of',
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
		'RS' => 'Serbia',
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
		'TL' => 'East Timor (Indonesia)',
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
		'ZW' => 'Zimbabwe',
		'PS' => 'Palestinian Territory', // usps does not ship
		'ME' => 'Montenegro',
		'GG' => 'Guernsey',
		'IM' => 'Isle of Man',
		'JE' => 'Jersey'
		);

		return $list;
	}

// translate for US Territories
	function usps_translation() {
		global $order;
		global $selected_country, $state_zone_id;
		if (SHIPPING_ORIGIN_COUNTRY == '223') {
			switch($order->delivery['country']['countries_iso_code_2']) {
				case 'AS': // Samoa American
				case 'GU': // Guam
				case 'MP': // Northern Mariana Islands
				case 'PW': // Palau
				case 'PR': // Puerto Rico
				case 'VI': // Virgin Islands US
// which is right
				case 'FM': // Micronesia, Federated States of
					return 'US';
					break;
// stays as original country
//				case 'FM': // Micronesia, Federated States of
				default:
					return $order->delivery['country']['countries_iso_code_2'];
					break;
			}
		} elseif( $order ) {
			return $order->delivery['country']['countries_iso_code_2'];
		}
	}
}

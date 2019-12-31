<?php
/**
 * USPS Module for Zen Cart v1.3.x thru v1.6
 * USPS RateV4 Intl RateV2 - March 28, 2018 Version K10

 * Prices from: Sept 16, 2017
 * Rates Names: Sept 16, 2017
 * Services Names: Sept 16, 2017
 *
 * @package shippingMethod
 * @copyright Copyright 2003-2016 Zen Cart Development Team

 * @copyright Portions Copyright 2003 osCommerce
 * @copyright Portions adapted from 2012 osCbyJetta
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: usps.php 2017-09-16 ajeh - tflmike, 2018-03-28 - bislewl  Version K10 $
 */


require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

/**
 * USPS Shipping Module class
 *
 */
class usps extends CommercePluginShippingBase {
	/**
	 * Shipping module list of supported countries
	 *
	 * @var array
	 */
	var $countries;
	/**
	 * List of services checkboxes, extracted out into an array
	 * @var array
	 */
	 var $typeCheckboxesSelected = array();
	/**
	 * USPS certain methods don't qualify if declared value is greater than $400
	 * @var array
	 */
	 var $typeCheckboxesSelected_to_skip_over_certain_value = array();
	/**
	 * use for debug log of what is sent to usps
	 * @var string
	 */
	 var $request_display;
	/**
	 * Constructor
	 *
	 * @return object
	 */

	function __construct() { // for older php < 5.1.0 change to usps() instead of __construct()
		parent::__construct();
		$this->title = tra( 'United States Postal Service' );
		$this->description = tra( 'United States Postal Service<br /><br />You will need to have a <a href="https://www.usps.com/business/web-tools-apis/">USPS Web Tools User ID</a> to use this module<br /><br />USPS expects you to use pounds as weight measure for your products.' );

		// check if all keys are in configuration table and correct version
		if( $this->isEnabled() ) {
			$this->typeCheckboxesSelected = explode(', ', MODULE_SHIPPING_USPS_TYPES);

			// prepare list of countries which USPS ships to
			$this->countries = $this->country_list();

			// certain methods don't qualify if declared value is greater than $400
			$this->types_to_skip_over_certain_value = array();
			$this->types_to_skip_over_certain_value['Priority Mail InternationalRM Flat Rate Envelope'] = 400; // skip value > $400 Priority Mail International Flat Rate Envelope
			$this->types_to_skip_over_certain_value['Priority Mail InternationalRM Small Flat Rate Envelope'] = 400; // skip value > $400 Priority Mail International Small Flat Rate Envelope
			$this->types_to_skip_over_certain_value['Priority Mail InternationalRM Small Flat Rate Box'] = 400; // skip value > $400 Priority Mail International Small Flat Rate Box
			$this->types_to_skip_over_certain_value['Priority Mail InternationalRM Legal Flat Rate Envelope'] = 400; // skip value > $400 Priority Mail International Legal Flat Rate Envelope
			$this->types_to_skip_over_certain_value['Priority Mail InternationalRM Padded Flat Rate Envelope'] = 400; // skip value > $400 Priority Mail International Padded Flat Rate Envelope
			$this->types_to_skip_over_certain_value['Priority Mail InternationalRM Gift Card Flat Rate Envelope'] = 400; // skip value > $400 Priority Mail International Gift Card Flat Rate Envelope
			$this->types_to_skip_over_certain_value['Priority Mail InternationalRM Window Flat Rate Envelope'] = 400; // skip value > $400 Priority Mail International Window Flat Rate Envelope
			$this->types_to_skip_over_certain_value['First-Class MailRM International Letter'] = 400; // skip value > $400 First-Class Mail International Letter
			$this->types_to_skip_over_certain_value['First-Class MailRM International Large Envelope'] = 400; // skip value > $400 First-Class Mail International Large Envelope
			$this->types_to_skip_over_certain_value['First-Class Package International ServiceTM'] = 400; // skip value > $400 First-Class Package International Service

			$this->getTransitTime = (in_array('Display transit time', explode(', ', MODULE_SHIPPING_USPS_OPTIONS))) ? TRUE : FALSE;

			$this->shipping_cutoff_time = '1400'; // 1400 = 14:00 = 2pm ---- must be HHMM without punctuation
		}
	}

	/**
	 * Prepare request for quotes and process obtained results
	 *
	 * @param string $method
	 * @return array of quotation results
	 */
	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {
			$iInfo = '';
			$methods = array();
			if ( !empty( $pShipHash['method'] ) && isset($this->types[$pShipHash['method']] ) ) {
				$this->_setService( $pShipHash['method'] );
			}

			$this->pounds = (int)$pShipHash['shipping_weight_total'];
			$this->ounces = ceil(round(16 * ($pShipHash['shipping_weight_total'] - $this->pounds), 2));

			// Determine machinable or not
			// weight must be less than 35lbs and greater than 6 ounces or it is not machinable
			$uspsCountryCode = $this->verifyCountryCode( $pShipHash['origin']['countries_iso_code_2'] );
			switch(true) {
// force machinable for $0.49 remove the false && from the first case
				case (false && ($uspsCountryCode == 'US' && ($this->pounds == 0 and $this->ounces <= 1))):
					// override admin choice too light
					$this->machinable = 'True';
					break;

				case ($uspsCountryCode == 'US' && ($this->pounds == 0 and $this->ounces < 6)):
					// override admin choice too light
					$this->machinable = 'False';
					break;

				case ($uspsCountryCode != 'US' && ($this->pounds == 0 and $this->ounces < 3.5)):
					// override admin choice too light
					$this->machinable = 'False';
					break;

				case ($pShipHash['shipping_weight_total'] > 35):
					// override admin choice too heavy
					$this->machinable = 'False';
					break;

				default:
					// admin choice on what to use
					$this->machinable = MODULE_SHIPPING_USPS_MACHINABLE;
			}

			// What method to use for calculating display of transit times
			// Options: 'NEW' = <ShipDate>, 'OLD' = extra API calls, 'CUSTOM' = hard-coded elsewhere in the parseDomesticTransitTimeResults() function.
			$this->transitTimeCalculationMode = MODULE_SHIPPING_USPS_TRANSIT_TIME_CALCULATION_MODE;
			// NOTE: at the present time, with the Test/Staging server, using the new method of adding shipdate adds a lot more time to obtaining quotes

			// request quotes
			$this->request_display = '';
			$this->uspsQuote = $this->_getQuote( $pShipHash );
			$uspsQuote = $this->uspsQuote;

			// were errors encountered?
			if( $uspsQuote === -1 || !is_array($uspsQuote) ) {
				$quotes['error'] = tra( 'The USPS server had a lookup error.' );
			} elseif (isset($uspsQuote['Number']) && !isset($uspsQuote['error'])) {
				$uspsQuote['error'] = $uspsQuote['Number'] . ' - ' . $uspsQuote['Description'];
			} if (isset($uspsQuote['error'])) {
				if ($uspsQuote['Number'] == -2147219085) {
					$quotes['error'] = 'NO OPTIONS INSTALLED: ' . $uspsQuote['error'];
				} else {
					$quotes['error'] = $uspsQuote['error'];
				}
			}

			if( empty( $quotes['error'] ) ) {
				// if we got here, there were no errors, so proceed with evaluating the obtained quotes

				$services_domestic = 'Domestic Services Selected: ' . "\n";
				$services_international = 'International Services Selected: ' . "\n";

				// obtain list of selected services ... so we can evaluate returned quoted services against the services selected by the store administrator (since USPS returns more than we ask for)
				$servicesSelectedDomestic = $this->special_services();
				$servicesSelectedIntl = $this->extra_service();

				// Domestic/US destination:
				if( $uspsCountryCode == 'US' ) {
					$dExtras = array(); // We're going to populate this with a list of "friendly names" of services "checked" to "Y" in our checkboxes
					$dOptions = explode(', ', MODULE_SHIPPING_USPS_DMST_SERVICES); // domestic
					foreach ($dOptions as $key => $val) {
						if (strlen($dOptions[$key]) > 1) {
							if ($dOptions[$key+1] == 'C' || $dOptions[$key+1] == 'S' || $dOptions[$key+1] == 'Y') {
								$services_domestic .= $dOptions[$key] . "\n";
								$dExtras[$dOptions[$key]] = $dOptions[$key+1];
							}
						}
					}
				} else {
				// International destination:
					$iExtras = array();
					$iOptions = explode(', ', MODULE_SHIPPING_USPS_INTL_SERVICES);
					foreach ($iOptions as $key => $val) {
						if(strlen($iOptions[$key]) > 1) {
							if ($iOptions[$key+1] == 'C' || $iOptions[$key+1] == 'S' || $iOptions[$key+1] == 'Y') {
								$services_international .= $iOptions[$key] . "\n";
								$iExtras[$iOptions[$key]] = $iOptions[$key+1];
							}
						}
					}

					if( $this->isConfigActive( 'MODULE_SHIPPING_USPS_REGULATIONS' ) ) {
						$iInfo = '<div id="iInfo">' . "\n" .
										 '  <div id="showInfo" class="ui-state-error" style="cursor:pointer; text-align:center;" onclick="$(\'#showInfo\').hide();$(\'#hideInfo, #Info\').show();">' . MODULE_SHIPPING_USPS_TEXT_INTL_SHOW . '</div>' . "\n" .
										 '  <div id="hideInfo" class="ui-state-error" style="cursor:pointer; text-align:center; display:none;" onclick="$(\'#hideInfo, #Info\').hide();$(\'#showInfo\').show();">' . MODULE_SHIPPING_USPS_TEXT_INTL_HIDE .'</div>' . "\n" .
										 '  <div id="Info" class="ui-state-highlight" style="display:none; padding:10px; max-height:200px; overflow:auto;">' . '<b>Prohibitions:</b><br />' . nl2br($uspsQuote['Package']['Prohibitions']) . '<br /><br /><b>Restrictions:</b><br />' . nl2br($uspsQuote['Package']['Restrictions']) . '<br /><br /><b>Observations:</b><br />' . nl2br($uspsQuote['Package']['Observations']) . '<br /><br /><b>CustomsForms:</b><br />' . nl2br($uspsQuote['Package']['CustomsForms']) . '<br /><br /><b>ExpressMail:</b><br />' . nl2br($uspsQuote['Package']['ExpressMail']) . '<br /><br /><b>AreasServed:</b><br />' . nl2br($uspsQuote['Package']['AreasServed']) . '<br /><br /><b>AdditionalRestrictions:</b><br />' . nl2br($uspsQuote['Package']['AdditionalRestrictions']) .'</div>' . "\n" .
										 '</div>';
					}
				}

				if( $uspsCountryCode== 'US' ) {
					$PackageSize = sizeof($uspsQuote['Package']);
					// if object has no legitimate children, turn it into a firstborn:
					if (isset($uspsQuote['Package']['ZipDestination']) && !isset($uspsQuote['Package'][0]['Postage'])) {
						$uspsQuote['Package'][] = $uspsQuote['Package'];
						$PackageSize = 1;
					}
				} else {
					$PackageSize = sizeof($uspsQuote['Package']['Service']);
				}

				// display 1st occurance of First Class and skip others for the US - start counter
				$cnt_first = 0;

	// *** Customizations once per display ***

	// bof: example to block USPS Priority MailTM Small Flat Rate Box when anything from master_categories_id 12 or 15 are in the cart
	// change false to true to use
	if (false) {
		$chk_cart = 0;
		$chk_cart += $_SESSION['cart']->in_cart_check('master_categories_id','12');
		$chk_cart += $_SESSION['cart']->in_cart_check('master_categories_id','15');
	}
	// see below use of $chk_cart
	// eof: example to block USPS Priority MailTM Small Flat Rate Box when anything from master_categories_id 12 or 15 are in the cart

				for ($i=0; $i<$PackageSize; $i++) {
					if (isset($uspsQuote['Package'][$i]['Error']) && zen_not_null($uspsQuote['Package'][$i]['Error'])) continue;
					$Services = array();
					$hiddenServices = array();
					$hiddenCost = 0;
					$handling = 0;
					$usps_insurance_charge = 0;

					$Package = ($uspsCountryCode == 'US') ? $uspsQuote['Package'][$i]['Postage'] : $uspsQuote['Package']['Service'][$i];

					// Domestic first
					if ($uspsCountryCode == 'US') {
						if (zen_not_null($Package['SpecialServices']['SpecialService'])) {

							// if object has no legitimate children, turn it into a firstborn:
							if (isset($Package['SpecialServices']['SpecialService']['ServiceName']) && !isset($Package['SpecialServices']['SpecialService'][0])) {
								$Package['SpecialServices']['SpecialService'] = array($Package['SpecialServices']['SpecialService']);
							}

							foreach ($Package['SpecialServices']['SpecialService'] as $key => $val) {
								// translate friendly names for Insurance Restricted Delivery 177, 178, 179, since USPS rebranded to remove all sense of explanations
								if ($val['ServiceName'] == 'Insurance Restricted Delivery') {
									if ($val['ServiceID'] == 178) $val['ServiceName'] = 'Insurance Restricted Delivery (Priority Mail Express)';
									if ($val['ServiceID'] == 179) $val['ServiceName'] = 'Insurance Restricted Delivery (Priority Mail)';
								}
								// translate friendly names for insurance 100, 101, 125, since USPS rebranded to remove all sense of explanations
								if ($val['ServiceName'] == 'Insurance') {
									if ($val['ServiceID'] == 125) $val['ServiceName'] = 'Priority Mail Insurance';
									if ($val['ServiceID'] == 101) $val['ServiceName'] = 'Priority Mail Express Insurance';
								}

								$val['ServiceName'] = $this->clean_usps_marks($val['ServiceName']);
								if (isset($dExtras[$val['ServiceName']]) && zen_not_null($dExtras[$val['ServiceName']]) && ((MODULE_SHIPPING_USPS_RATE_TYPE == 'Online' && strtoupper($val['AvailableOnline']) == 'TRUE') || (MODULE_SHIPPING_USPS_RATE_TYPE == 'Retail' && strtoupper($val['Available']) == 'TRUE'))) {
									$val['ServiceAdmin'] = $this->clean_usps_marks($dExtras[$val['ServiceName']]);
									$Services[] = $val;
								}
							}
						}

						$cost = MODULE_SHIPPING_USPS_RATE_TYPE == 'Online' && !empty( $Package['CommercialRate'] ) ? $Package['CommercialRate'] : $Package['Rate'];
						$type = $this->clean_usps_marks($Package['MailService']);
						// methods shipping zone
						$usps_shipping_methods_zone = $uspsQuote['Package'][$i]['Zone'];
					} else {
						// International
	//          if (isset($Package['ExtraServices']) && isset($Package['ExtraServices']['ExtraService'])) foreach ($Package['ExtraServices']['ExtraService'] as $key => $val) {
						if (is_array($Package['ExtraServices']['ExtraService'])) {

							// if object has no legitimate children, turn it into a firstborn:
							if (isset($Package['ExtraServices']['ExtraService']['ServiceName']) && !isset($Package['ExtraServices']['ExtraService'][0])) {
								$Package['ExtraServices']['ExtraService'] = array($Package['ExtraServices']['ExtraService']);
							}

							foreach ($Package['ExtraServices']['ExtraService'] as $key => $val) {
								$val['ServiceName'] = $this->clean_usps_marks($val['ServiceName']);
								if (isset($iExtras[$val['ServiceName']]) && zen_not_null($iExtras[$val['ServiceName']]) && ((MODULE_SHIPPING_USPS_RATE_TYPE == 'Online' && strtoupper($val['OnlineAvailable']) == 'TRUE') || (MODULE_SHIPPING_USPS_RATE_TYPE == 'Retail' && strtoupper($val['Available']) == 'TRUE'))) {
									$val['ServiceAdmin'] = $this->clean_usps_marks($iExtras[$val['ServiceName']]);
									$Services[] = $val;
								}
							}
						}
						$cost = MODULE_SHIPPING_USPS_RATE_TYPE == 'Online' && zen_not_null($Package['CommercialPostage']) ? $Package['CommercialPostage'] : $Package['Postage'];
						$type = $this->clean_usps_marks($Package['SvcDescription']);
					}
					if ($cost == 0) continue;

					// This is used for overriding the matching needed because USPS is inconsistent in how they return the names

	//  // simulate appending the RM
	//  if (preg_match('#First#', $type)) $type .="RM";

					$type_rebuilt = $type;

	// bof: example to block USPS Priority MailTM Small Flat Rate Box when anything from master_categories_id 12 or 15 are in the cart
	// see above for $chk_cart settings
	// change false to true to use
	if (false) {
		if ($chk_cart > 0 && $type == 'Priority MailTM Small Flat Rate Box') {
	//  echo 'USPS $type: ' . $type . ' $chk_cart:' . $chk_cart . '<br>';
			continue;
		}
	}
	// eof: example to block USPS Priority MailTM Small Flat Rate Box when anything from master_categories_id 12 or 15 are in the cart

					// Detect which First-Class type has been quoted, since USPS doesn't consistently return the type in the name of the service
					if (!isset($Package['FirstClassMailType']) || $Package['FirstClassMailType'] == '') {
						if (isset($uspsQuote["Package"][$i]) && isset($uspsQuote["Package"][$i]['FirstClassMailType']) && $uspsQuote["Package"][$i]['FirstClassMailType'] != '') {
							$Package['FirstClassMailType'] = $uspsQuote["Package"][$i]['FirstClassMailType']; // LETTER or FLAT or PARCEL
						}
					}

					// init vars used later
					$minweight = $maxweight = $handling = 0;

					// Build a match pattern for regex compare later against selected allowed services
					$Package['lookupRegex'] = preg_quote($type) . '(?:RM|TM)?$';
					if( !empty( $Package['FirstClassMailType'] ) && $firstClassMailType = strtoupper( $Package['FirstClassMailType'] ) ) {
						if (in_array( $firstClassMailType, array('LETTER'))) { 
							$Package['lookupRegex'] = preg_replace('#Mail(?:RM|TM)?#', 'Mail(?:RM|TM)?(?: Stamped )?.*', preg_quote($type)) . ($uspsCountryCode != 'US' ? '(GXG|International)?.*' : '') . $Package['FirstClassMailType'];
						}
						if (in_array( $firstClassMailType, array('PARCEL'))) { 
							$Package['lookupRegex'] = preg_replace('#Mail(?:RM|TM)?#', 'Mail.*', preg_quote($type)) . ($uspsCountryCode != 'US' ? '(GXG|International)?.*' : '') . $Package['FirstClassMailType'];
						}
						if (in_array( $firstClassMailType, array('FLAT') )) {
							$Package['lookupRegex'] = preg_replace('#Mail(?:RM|TM)?#', 'Mail.*', preg_quote($type)) . ($uspsCountryCode != 'US' ? '(GXG|International)?.*' : '') . 'Envelope';
						}
					}
					$Package['lookupRegex'] = str_replace('Stamped Letter', 'Letter', $Package['lookupRegex']);
					$Package['lookupRegex'] = str_replace('LetterLETTER', 'Letter', $Package['lookupRegex']);
					$Package['lookupRegex'] = str_replace('ParcelEnvelope', 'Envelope', $Package['lookupRegex']);
					$Package['lookupRegex'] = str_replace('EnvelopeEnvelope', 'Envelope', $Package['lookupRegex']);
					$Package['lookupRegex'] = str_replace('ParcelPARCEL', 'Parcel', $Package['lookupRegex']);

					// Certain methods cannot ship if declared value is over $400, so we "continue" which skips the current $type and proceeds with the next one in the loop:
					if (isset($this->types_to_skip_over_certain_value[$type]) && $_SESSION['cart']->total > $this->types_to_skip_over_certain_value[$type]) {
						continue;
					}


					// process weight/handling settings from admin checkboxes
					foreach ($this->typeCheckboxesSelected as $key => $val) {
						if (is_numeric($val) || $val == '') continue;

						if ($val == $type || preg_match('#' . $Package['lookupRegex'] . '#i', $val) ) {
							if (strpos($val, 'International') && !strpos($type, 'International')) continue;
							if (strpos($val, 'GXG') && !strpos($type, 'GXG')) continue;
							$minweight = $this->typeCheckboxesSelected[$key+1];
							$maxweight = $this->typeCheckboxesSelected[$key+2];
							$handling = $this->typeCheckboxesSelected[$key+3];
							if ($val != $type && preg_match('#' . $Package['lookupRegex'] . '#i', $val) ) {
								$type_rebuilt = $val;
							}
							break;
						}

					}

					// process fees for additional services selected
					foreach ($Services as $key => $val) {
						$sDisplay = $Services[$key]['ServiceAdmin'];
						if ($sDisplay == 'Y') $hiddenServices[] = array($Services[$key]['ServiceName'] . ' [' . $Services[$key]['ServiceID'] . ']' => (MODULE_SHIPPING_USPS_RATE_TYPE == 'Online' ? $Services[$key]['PriceOnline'] : $Services[$key]['Price']));
					}
					// prepare costs associated with selected additional services
					$hidden_costs_breakdown = '';
					foreach($hiddenServices as $key => $val) {
						foreach($hiddenServices[$key] as $key1 => $val1) {
							// add the cost to the accumulator
							$hiddenCost += $val1;

							// now check for insurance-specific codes, in order to augment the insurance counter

							// extract the ServiceID, so we can test for specific insurance types
							preg_match('/\[([0-9]{1,3})\]/', $key1, $matches);
							$serviceID = $matches[1];
							$hidden_costs_breakdown .= ($uspsCountryCode == 'US' ? ' SpecialServices: ' : ' ExtraServices: ') . $key1 . ' Amount: ' . number_format($val1, 2) . "\n";
							// Test for Insurance type being returned  100=(General) Insurance, 125=Priority Mail, 101=Priority Mail Express

	// Domestic Insurance 100, 101, 125 International 1
							$insurance_test_flag = false;
							if (preg_match('#Insurance#i', $key1)) {
								// Domestic
								if ($pShipHash['destination']['countries_iso_code_2'] == SHIPPING_ORIGIN_COUNTRY || $uspsCountryCode == 'US') {
									if (strstr($servicesSelectedDomestic, $serviceID)) {
										if (strstr($type, 'Priority Mail')) {
											if (strstr($type, 'Express')) {
												if ($serviceID == 101) $insurance_test_flag = true;
											} else {
												if ($serviceID == 125) $insurance_test_flag = true;
											}
										} else {
											if ($serviceID == 100) $insurance_test_flag = true;
										}
									}
								} else { // international
									if ($serviceID == 1 && strstr($servicesSelectedIntl, $serviceID)) $insurance_test_flag = true;
								}
								if ($insurance_test_flag) {
									$usps_insurance_charge = $val1;
								}
							}

						}
					}

					// set module-specific handling fee
					if ($pShipHash['destination']['countries_iso_code_2'] == SHIPPING_ORIGIN_COUNTRY || $uspsCountryCode == 'US') {
						// domestic/national
						$usps_handling_fee = MODULE_SHIPPING_USPS_HANDLING;
					} else {
						// international
						$usps_handling_fee = MODULE_SHIPPING_USPS_HANDLING_INT;
					}

					// COST
					// clean out invalid characters
					$cost = preg_replace('/[^0-9.]/', '',  $cost);
					// add handling for shipping method costs for extra services applied
					$cost_original = $cost;
					$cost = ($cost + $handling + $hiddenCost) * $pShipHash['shipping_num_boxes'];
					// add handling fee per Box or per Order
					$cost += (MODULE_SHIPPING_USPS_HANDLING_METHOD == 'Box') ? $usps_handling_fee * $pShipHash['shipping_num_boxes'] : $usps_handling_fee;

					// set the output title display name back to correct format
					$title = str_replace(array('RM', 'TM', '**'), array('&reg;', '&trade;', ''), $type_rebuilt);

					$transitTime = '';
					$deliveryDate = '';
					// process customization of transit times in quotes
					if (in_array('Display transit time', explode(', ', MODULE_SHIPPING_USPS_OPTIONS))) {
						list( $transitTime, $deliveryDate ) = $this->parseTransitTimeResults($Package, $type_rebuilt);
					}

					// build USPS output for valid methods based on selected and weight limits
					if( ($pShipHash['shipping_weight_total'] <= $maxweight) && ($pShipHash['shipping_weight_total'] > $minweight) ) {

						if( $pShipHash['method'] != $type && $pShipHash['method'] != $type_rebuilt) {
							if( !empty( $pShipHash['method'] ) ) continue;
							$found = false;
							foreach ($this->typeCheckboxesSelected as $key => $val) {
								if (is_numeric($val) || $val == '') continue;
								if ($val == $type || preg_match('#' . $Package['lookupRegex'] . '#i', $val) ) {
									$found = true;
								}
								if ($found === true) break;
							}
						}
						if ($found === false) continue;


						// display 1st occurance of First Class and skip others for the US
											if (preg_match('#First\-Class.*(?!GXG|International)#i', $type)) {
							$cnt_first ++;
						}
						
	// USPS customize for filtering displayed methods and costs

						if ($uspsCountryCode == 'US' && MODULE_SHIPPING_USPS_FIRST_CLASS_FILTER_US == 'True' && preg_match('#First\-Class#i', $type) && $cnt_first > 1) continue;

						// ADDITIONAL CUSTOMIZED CONDITIONS CAN GO HERE TO MANAGE $type_rebuilt or $title on $methods
							$methods[] = array( 'id' => $type_rebuilt,
												'title' => $title,
												'cost' => $cost,
												'transit_time' => $transitTime,
												'delivery_date' => $deliveryDate,
												'insurance' => $usps_insurance_charge,
												'code' => $type,
												);
					}
				}  // end for $i to $PackageSize

				// sort results
				if (MODULE_SHIPPING_USPS_QUOTE_SORT != 'Unsorted') {
					if (sizeof($methods) > 1)
					{
						if (substr(MODULE_SHIPPING_USPS_QUOTE_SORT, 0, 5) == 'Price') {
							foreach($methods as $c=>$key)
							{
								$sort_cost[] = $key['cost'];
								$sort_id[] = $key['id'];
							}
							array_multisort($sort_cost, (MODULE_SHIPPING_USPS_QUOTE_SORT == 'Price-LowToHigh' ? SORT_ASC : SORT_DESC), $sort_id, SORT_ASC, $methods);
						} else {
							foreach($methods as $c=>$key)
							{
								$sort_key[] = $key['title'];
								$sort_id[] = $key['id'];
							}
							array_multisort($sort_key, (MODULE_SHIPPING_USPS_QUOTE_SORT == 'Alphabetical' ? SORT_ASC : SORT_DESC), $sort_id, SORT_ASC, $methods);
						}
					}
				}

				$quotes['methods'] = $methods;
			}
		}

		return $quotes;
	}


	/**
	 * Get actual quote from USPS
	 *
	 * @return array of results or boolean false if no results
	 */
	function _getQuote( $pShipHash ) {
		$package_id = 'USPS DOMESTIC RETURNED: ' . "\n";
// force GroundOnly results in USPS Retail Ground only being offered
// to use, you must have a field for products_groundonly in the products table
// then uncomment the in_cart_check for the products_groundonly
// when $usps_groundonly is set to true, only USPS Retail Ground will show
		$usps_groundonly = 'false';
//    $usps_groundonly = ($_SESSION['cart']->in_cart_check('products_groundonly','1') ? 'true' : 'false');
		if ($usps_groundonly == 'false') {
			// no GroundOnly products
			$usps_groundonly = '';
		} else {
			// 1+ GroundOnly products force USPS Retail Ground only
			$usps_groundonly = '<Content>' .
													 '<ContentType>HAZMAT</ContentType>' .
												 '</Content>' .
												 '<GroundOnly>' . $usps_groundonly . '</GroundOnly>';
		}

// Force Fragile
// to use, you must have a field for products_fragile in the products table
// then uncomment the in_cart_check for the products_fragile
// when $usps_fragile is set to true, Fragile Rates will be returned
		$usps_fragile = 'false';
//    $usps_fragile = ($_SESSION['cart']->in_cart_check('products_fragile','1') ? 'true' : 'false');
		if ($usps_fragile == 'false') {
			$usps_fragile = '<Content>' .
													 '<ContentType>Fragile</ContentType>' .
												 '</Content>';
		}


		if ((int)SHIPPING_ORIGIN_ZIP == 0) {
			// no quotes obtained no 5 digit zip code origin set
			return array('module' => $this->title,
									 'error' => MODULE_SHIPPING_USPS_TEXT_ERROR . (MODULE_SHIPPING_USPS_SERVER == 'test' ? MODULE_SHIPPING_USPS_TEXT_TEST_MODE_NOTICE : ''));
		}

		$transreq = array();

		$shipment_value = (float)BitBase::getParameter( $pShipHash, 'shipment_value', 0 );
		$insurable_value = $shipment_value; // spiderr - where is this defined? - $uninsurable_value;

		// US Domestic destinations
		if ($pShipHash['destination']['countries_iso_code_2'] == 'US') {

			// build special services for domestic
			// Some Special Services cannot work with others
			$special_services_domestic = $this->special_services(); // original

			$ZipDestination = substr(str_replace(' ', '', $pShipHash['destination']['postcode']), 0, 5);
			if ($ZipDestination == '') return -1;
			$request =  '<RateV4Request USERID="' . MODULE_SHIPPING_USPS_USERID . '">' . '<Revision>2</Revision>';
			$package_count = 0;
			$ship_date = $this->zen_usps_shipdate();

			foreach($this->typeCheckboxesSelected as $requested_type)
			{
				if (is_numeric($requested_type) || preg_match('#(GXG|International)#i' , $requested_type)) continue;
				$FirstClassMailType = '';
				$Container = 'VARIABLE';
				if (preg_match('#First\-Class#i', $requested_type))
				{

// disable request for all First Class at 13oz. - First-Class MailRM Letter, First-Class MailRM Large Envelope, First-Class MailRM Parcel
// disable request for all First Class at 13oz. - First-Class Mail Letter, First-Class Mail Large Envelope, First-Class Package Service - RetailTM
// disable all first class requests if item is over 15oz.
// First-Class Retail and Commercial
					if ($pShipHash['shipping_weight_total'] > 15/16) {
						continue;
					} else {
						// First-Class MailRM Letter\', \'First-Class MailRM Large Envelope\', \'First-Class Package Service - RetailTM
						$service = 'First Class';            
			// disable request for First-Class MailRM Letter at > .21875 and not Retail
						if (($requested_type == 'First-Class Mail Letter') && (MODULE_SHIPPING_USPS_RATE_TYPE == 'Retail') && ($pShipHash['shipping_weight_total'] <= .21875)) {
							$FirstClassMailType = 'LETTER';
			// disable request for First-Class Mail Large Envelope at > 13oz and not Retail  
						} elseif (($requested_type == 'First-Class Mail Large Envelope') && (MODULE_SHIPPING_USPS_RATE_TYPE == 'Retail') && ($pShipHash['shipping_weight_total'] <= 13/16)) {
							$FirstClassMailType = 'FLAT';
						// disable request for First-Class Package Service - RetailTM(new retail parcel designation) at > 13oz and not Retail 			  
						} elseif (($requested_type == 'First-Class Package Service - RetailTM') && (MODULE_SHIPPING_USPS_RATE_TYPE == 'Retail') && ($pShipHash['shipping_weight_total'] <= 13/16)) {			
							 $FirstClassMailType = 'PARCEL';	
			// disable request for First-ClassTM Package Service(existing commercial parcel designation) at > 1 lb and not Online(commercial pricing) 			  
						} elseif (($requested_type == 'First-ClassTM Package Service') && (MODULE_SHIPPING_USPS_RATE_TYPE == 'Online') && ($pShipHash['shipping_weight_total'] <= 15/16)) {
						 $service = 'First Class Commercial';  			
							 $FirstClassMailType = 'PACKAGE SERVICE';	   
						} else {
				continue;
				}
					}
			 }
// continue processing other package types	   
				elseif ($requested_type == 'Media Mail Parcel') {
					$service = 'MEDIA';
				}
				// In the following line, changed Parcel to Standard due to USPS service name change - 01/27/13 a.forever edit
				elseif ($requested_type == 'USPS Retail GroundRM') {
					$service = 'PARCEL';
				}
				elseif (preg_match('#Priority Mail(?! Express)#i', $requested_type))
				{
					$service = 'PRIORITY COMMERCIAL';
					if ($requested_type == 'Priority MailTM Flat Rate Envelope') {
						$Container = 'FLAT RATE ENVELOPE';
					} elseif ($requested_type == 'Priority MailTM Legal Flat Rate Envelope') {
						$Container = 'LEGAL FLAT RATE ENVELOPE';
					} elseif ($requested_type == 'Priority MailTM Padded Flat Rate Envelope') {
						$Container = 'PADDED FLAT RATE ENVELOPE';
					} elseif ($requested_type == 'Priority MailTM Small Flat Rate Box') {
						$Container = 'SM FLAT RATE BOX';
					} elseif ($requested_type == 'Priority MailTM Medium Flat Rate Box') {
						$Container = 'MD FLAT RATE BOX';
					} elseif ($requested_type == 'Priority MailTM Large Flat Rate Box') {
						$Container = 'LG FLAT RATE BOX';
					} elseif ($requested_type == 'Priority MailTM Regional Rate Box A') {
						$Container = 'REGIONALRATEBOXA';
					} elseif ($requested_type == 'Priority MailTM Regional Rate Box B') {
						$Container = 'REGIONALRATEBOXB';
					}
				}
				elseif (preg_match('#Priority Mail Express#i', $requested_type))
				{
					$service = 'EXPRESS COMMERCIAL';
					if ($requested_type == 'Priority Mail ExpressTM Flat Rate Envelope') {
						$Container = 'FLAT RATE ENVELOPE';
					} elseif ($requested_type == 'Priority Mail ExpressTM Legal Flat Rate Envelope') {
						$Container = 'LEGAL FLAT RATE ENVELOPE';
//          } elseif ($requested_type == 'Priority Mail ExpressTM Flat Rate Boxes') {
//            $Container = 'FLAT RATE BOX';
					}
				}
				else
				{
					continue;
				}

// build special services for domestic
$specialservices = $special_services_domestic;

// uncomment to force turn off SpecialServices requests completely
//$specialservices = '';

				$width = MODULE_SHIPPING_USPS_WIDTH;
				$length = MODULE_SHIPPING_USPS_LENGTH;
				$height = MODULE_SHIPPING_USPS_HEIGHT;
				$girth = 108;

// turn on dimensions
$dimensions = '<Width>' . $width . '</Width>' .
							'<Length>' . $length . '</Length>' .
							'<Height>' . $height . '</Height>' .
							'<Girth>' . $girth . '</Girth>';

// uncomment to force turn off dimensions
$dimensions = '';

				$request .=  '<Package ID="' . $package_count . '">' .
//                     '<Service>ALL</Service>' .
//                     '<Service>ONLINE</Service>' .
										 '<Service>' . $service . '</Service>' .
										 ($FirstClassMailType != '' ? '<FirstClassMailType>' . $FirstClassMailType . '</FirstClassMailType>' : '') .
										 '<ZipOrigination>' . SHIPPING_ORIGIN_ZIP . '</ZipOrigination>' .
										 '<ZipDestination>' . $ZipDestination . '</ZipDestination>' .
										 '<Pounds>' . $this->pounds . '</Pounds>' .
										 '<Ounces>' . $this->ounces . '</Ounces>' .
										 '<Container>' . $Container . '</Container>' .
										 '<Size>REGULAR</Size>' .
$dimensions .
								 '<Value>' . number_format($insurable_value, 2, '.', '') . '</Value>' .
$specialservices .
									($usps_groundonly != '' ? $usps_groundonly : '') .
										 '<Machinable>' . ($this->machinable == 'True' ? 'TRUE' : 'FALSE') . '</Machinable>' .
//'<DropOffTime>23:59</DropOffTime>' .
										 ($this->getTransitTime && $this->transitTimeCalculationMode == 'NEW' ? '<ShipDate>' . $ship_date . '</ShipDate>' : '') .
										 '</Package>';

				$package_id .= 'Package ID returned: ' . $package_count . ' $requested_type: ' . $requested_type . ' $service: ' . $service . ' $Container: ' . $Container . "\n";
				$package_count++;

// ask for Domestic transit times using old double-request method to individual USPS API for each shipping service requested
				if ($this->getTransitTime && $this->transitTimeCalculationMode == 'OLD') {
					$transitreq  = 'USERID="' . MODULE_SHIPPING_USPS_USERID . '">' . '<OriginZip>' . SHIPPING_ORIGIN_ZIP . '</OriginZip>' . '<DestinationZip>' . $ZipDestination . '</DestinationZip>';
					switch ($service) {
//             case 'EXPRESS COMMERCIAL':
//             case 'EXPRESS':  $transreq[$requested_type] = 'API=ExpressMail&XML=' . urlencode( '<ExpressMailRequest ' . $transitreq . '</ExpressMailRequest>');
//             break;
						case 'PRIORITY COMMERCIAL':
						case 'PRIORITY': $transreq[$requested_type] = 'API=PriorityMail&XML=' . urlencode( '<PriorityMailRequest ' . $transitreq . '</PriorityMailRequest>');
						break;
						case 'PARCEL':   $transreq[$requested_type] = 'API=StandardB&XML=' . urlencode( '<StandardBRequest ' . $transitreq . '</StandardBRequest>');
						break;
						case 'First-Class Mail':$transreq[$requested_type] = 'API=FirstClassMail&XML=' . urlencode( '<FirstClassMailRequest ' . $transitreq . '</FirstClassMailRequest>');
						break;
						case 'MEDIA':
						default:         $transreq[$requested_type] = '';
						break;
					}
				}

			}

			$request .=  '</RateV4Request>';

			$request = 'API=RateV4&XML=' . urlencode($request);

		} else {
			// INTERNATIONAL destinations

			// build extra services for international
			// Some Extra Services cannot work with others
			$extra_service_international = $this->extra_service(); // original

			$intl_gxg_requested = 0;
			foreach($this->typeCheckboxesSelected as $requested_type)
			{
				if(!is_numeric($requested_type) && preg_match('#(GXG)#i', $requested_type)) {
					$intl_gxg_requested ++;
				}
			}

			// rudimentary dimensions, since they cannot be passed as blanks
			if (true || $intl_gxg_requested) {
				//@@TODO - force International to always use International settings - should be okay to make permanent at a future date
// obtain the most International settings
//        $width = 1.0; // $width = 0.75 for some International Methods to work
//        $length = 9.5;
//        $height = 5.5;
				$width = MODULE_SHIPPING_USPS_WIDTH_INTL;
				$length = MODULE_SHIPPING_USPS_LENGTH_INTL;
				$height = MODULE_SHIPPING_USPS_HEIGHT_INTL;
				$girth = 0;
			} else {
				$width = MODULE_SHIPPING_USPS_WIDTH;
				$length = MODULE_SHIPPING_USPS_LENGTH;
				$height = MODULE_SHIPPING_USPS_HEIGHT;
				$girth = 0;
			}
eb( $pShipHash );
			// adjust <ValueOfContents> to not exceed $2499 per box
			$max_usps_allowed_price = ($pShipHash['shipping_value'] / $pShipHash['shipping_num_boxes']);

// build extra services for international
$extraservices = $extra_service_international;

// uncomment to force turn off ExtraServices
// $extraservices = '';

// $max_usps_allowed_price - adjust <ValueOfContents> to not exceed $2499 per box
			$submission_value = ($insurable_value > $max_usps_allowed_price) ? $max_usps_allowed_price : $insurable_value;

			$request =  '<IntlRateV2Request USERID="' . MODULE_SHIPPING_USPS_USERID . '">' .
									'<Revision>2</Revision>' .
									'<Package ID="0">' .
									'<Pounds>' . $this->pounds . '</Pounds>' .
									'<Ounces>' . $this->ounces . '</Ounces>' .
									'<MailType>All</MailType>' .
									'<GXG>' .
									'  <POBoxFlag>N</POBoxFlag>' .
									'  <GiftFlag>N</GiftFlag>' .
									'</GXG>' .
									'<ValueOfContents>' . number_format($submission_value, 2, '.', '') . '</ValueOfContents>' .
									'<Country>' . $pShipHash['destination']['countries_iso_code_2'] . '</Country>' .
									'<Container>RECTANGULAR</Container>' .
									'<Size>REGULAR</Size>' .
// Small Flat Rate Box - 'maxLength'=>'8.625', 'maxWidth'=>'5.375','maxHeight'=>'1.625'
// Global Express Guaranteed - Minimum 'minLength'=>'9.5', 'minHeight'=>'5.5' ; Maximum - 'maxLength'=>'46', 'maxWidth'=>'35', 'maxHeight'=>'46' and max. length plus girth combined 108"
// NOTE: sizes for Small Flat Rate Box prevent Global Express Guaranteed
// NOTE: sizes for Global Express Guaranteed prevent Small Flat Rate Box
// Not set up:
// Video - 'maxLength'=>'9.25', 'maxWidth'=>'6.25','maxHeight'=>'2'
// DVD - 'maxLength'=>'7.5625', 'maxWidth'=>'5.4375','maxHeight'=>'.625'
// defaults
// MODULE_SHIPPING_USPS_LENGTH 8.625
// MODULE_SHIPPING_USPS_WIDTH  5.375
// MODULE_SHIPPING_USPS_HEIGHT 1.625
									'<Width>' . $width . '</Width>' .
									'<Length>' . $length . '</Length>' .
									'<Height>' . $height . '</Height>' .
									'<Girth>' . $girth . '</Girth>' .

//'<CommercialPlusFlag>N</CommercialPlusFlag>' .
									'<OriginZip>' . SHIPPING_ORIGIN_ZIP . '</OriginZip>' .
									// In the following line, changed N to Y to activate optional commercial base pricing for international services - 01/27/13 a.forever edit
									'<CommercialFlag>Y</CommercialFlag>' .
// '<AcceptanceDateTime>2015-05-30T13:15:00-06:00</AcceptanceDateTime>' .
// '<DestinationPostalCode>' . $DestinationPostalCode . '</DestinationPostalCode>' .
$extraservices .
									'</Package>' .
									'</IntlRateV2Request>';

			if ($this->getTransitTime) {
				$transreq[$requested_type] = '';
			}

			$request = 'API=IntlRateV2&XML=' . urlencode($request);
	}

// Prepare to make quote-request to USPS servers


		switch (MODULE_SHIPPING_USPS_SERVER) {
			case 'production':
			$usps_server = 'http://production.shippingapis.com';
			$api_dll = 'shippingapi.dll';
			break;
			case 'test':
			default:
// 09-7-2014
//Secure APIs: https://stg-secure.shippingapis.com/ShippingApi.dll
//Non-secure APIs: http://stg-production.shippingapis.com/ShippingApi.dll
			$usps_server = 'http://stg-production.shippingapis.com';
			$api_dll = 'ShippingApi.dll';
			break;
		}

		$body = '';
// BOF CURL
		// Send quote request via CURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $usps_server . '/' . $api_dll);
		curl_setopt($ch, CURLOPT_REFERER, HTTPS_SERVER . DIR_WS_HTTPS_CATALOG);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
//    curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Zen Cart');
		if( $this->isConfigActive( 'CURL_PROXY_REQUIRED' ) ) {
			$this->proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $this->proxy_tunnel_flag);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
		}

		// submit request
		$body = curl_exec($ch);
		$this->commError = curl_error($ch);
		$this->commErrNo = curl_errno($ch);
		$this->commInfo = @curl_getinfo($ch);
// error_log($this->commInfo . ' ' . time() . ' - microtime: ' . microtime());

		// done with CURL, so close connection
		curl_close ($ch);

		//if communication error, return -1 because no quotes were found, and user doesn't need to see the actual error message 
		if ($this->commErrNo != 0) return -1;
// EOF CURL

		$body_array = simplexml_load_string($body);
		$body_encoded = json_decode(json_encode($body_array),TRUE);
		return $body_encoded;
	}

	/**
	 * Parse the transit time results data returned by passing the <ShipDate> request parameter
	 * @param array $Package - The package details array to parse, received from USPS and semi-sanitized already
	 * @param string $service - The delivery service being evaluated
	 * ref: <CommitmentDate>2013-07-23</CommitmentDate><CommitmentName>1-Day</CommitmentName>
	 */
	function parseTransitTimeResults($Package, $service) {
		$duration = '';
		$date = '';

		if( preg_match( '#(GXG|International)#i', $service ) ) {
			$duration = isset($Package['SvcCommitments']) ? $Package['SvcCommitments'] : '';
			if ($duration == '' || $this->transitTimeCalculationMode == 'CUSTOM') {
				/********************* CUSTOM START:  IF YOU HAVE CUSTOM TRANSIT TIMES ENTER THEM HERE ***************/
				if( preg_match('#Priority Mail Express#i', $service) ) {
					$duration = '3 - 5 business ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#Priority Mail#i', $service) ) {
					$duration = '6 - 10 business ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#Global Express Guaranteed#i', $service) ) {
					$duration = '1 - 3 business ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#USPS GXG.* Envelopes#i', $service) ) {
					$duration = '1 - 3 business ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#First\-Class#i', $service) ) {
					$duration = 'Varies by destination'; // '' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				}
				/********************* CUSTOM END:  IF YOU HAVE CUSTOM TRANSIT TIMES ENTER THEM HERE ***************/
			} else {
				$duration = preg_replace('/Weeks$/', MODULE_SHIPPING_USPS_TEXT_WEEKS, $duration);
				$duration = preg_replace('/Days$/', MODULE_SHIPPING_USPS_TEXT_DAYS, $duration);
				$duration = preg_replace('/Day$/', MODULE_SHIPPING_USPS_TEXT_DAY, $duration);
			}

		} else {
			$duration = $this->getParameter( $Package, 'CommitmentName' );
			$date = $this->getParameter( $Package, 'CommitmentDate' );
			if( $duration == '' || $this->transitTimeCalculationMode == 'CUSTOM' ) {
				/********************* CUSTOM START:  IF YOU HAVE CUSTOM TRANSIT TIMES ENTER THEM HERE ***************/
				if( preg_match('#Priority Mail Express#i', $service)) {
					$duration = '1 - 2 ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#Priority MailTM#i', $service) ) {
					$duration = '2 - 3 ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#USPS Retail GroundRM#i', $service) ) {
					$duration = '4 - 7 ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#First\-Class#i', $service) ) {
					$duration = '2 - 5 ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				} elseif( preg_match('#Media Mail Parcel#i', $service) ) {
					$duration = '5 - 10 ' . MODULE_SHIPPING_USPS_TEXT_DAYS;
				}
				/********************* CUSTOM END:  IF YOU HAVE CUSTOM TRANSIT TIMES ENTER THEM HERE ***************/
			} else {
				// fix USPS issues with CommitmentName, example: GUAM
				if (is_array($duration)) {
					$duration = '';
				}
			}
		}
		return array( $duration, $date );
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
	'BQ' => 'Bonaire (Curacao)',
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
	'CW' => 'Curacao',
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
		'KN' => 'Saint Kitts (Saint Christopher and Nevis)',
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
	'SX' => 'Sint Maarten (Dutch)',
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
		'TK' => 'Tokelau (Union Group) (Western Samoa)',
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

	// use USPS translations for US shops (USPS treats certain regions as "US States" instead of as different "countries", so we translate here)
	private function verifyCountryCode( $pIsoCode2 ) {
		$ret = $pIsoCode2;

		switch( $pIsoCode2 ) {
			case 'AS':	// Samoa American
			case 'GU':	// Guam
			case 'MP':	// Northern Mariana Islands
			case 'PW':	// Palau
			case 'PR':	// Puerto Rico
			case 'VI':	// Virgin Islands US
			case 'FM':	// Micronesia, Federated States of
				$ret = 'US';
				break;
			default:
				$ret = $pIsoCode2;
				break;
		}

		return $ret;
	}

	// used for shipDate
	function zen_usps_shipdate() {
		// safety calculation for cutoff time
		if ($this->shipping_cutoff_time < 1200 || $this->shipping_cutoff_time > 2300) $this->shipping_cutoff_time = 1400;
		// calculate today vs tomorrow based on time
		if (version_compare(PHP_VERSION, 5.2, '>=')) {
			if (date('Gi') < preg_replace('/[^\d]/', '', $this->shipping_cutoff_time)) { // expects it in the form of HHMM
				$datetime = new DateTime('today');
			} else {
				$datetime = new DateTime('tomorrow');
				//$datetime = new DateTime((date('l') == 'Friday') ? 'Monday next week' : 'tomorrow');
			}
			$usps_date = $datetime->format('Y-m-d');
		} else {
			// old PHP versions use just today's date:
			$usps_date = date('Y-m-d');
		}
		return $usps_date;
	}

	function clean_usps_marks($string) {
		// strip reg and trade symbols
		$string = str_replace(array('&amp;lt;sup&amp;gt;&amp;#174;&amp;lt;/sup&amp;gt;', '&amp;lt;sup&amp;gt;&amp;#8482;&amp;lt;/sup&amp;gt;'), array('RM', 'TM'), htmlspecialchars($string));

		// shipdate info removed from names as it is contained in the shipping methods
		// refers to this field for Domestic: <CommitmentName>  or International: <SvcCommitments>
		$string = str_replace(array('Mail 1-Day', 'Mail 2-Day', 'Mail 3-Day', 'Mail Military', 'Mail DPO'), 'Mail', $string);
		$string = str_replace(array('Express 1-Day', 'Express 2-Day', 'Express 3-Day', 'Express Military', 'Express DPO'), 'Express', $string);

		return $string;
	}

// return SpecialService tags based on checked choices only
	function special_services() {
/*
The Special service definitions are as follows:
USPS Special Service Name ServiceID - Our Special Service Name
	Certified 0 - Certified MailRM
	Insurance 1 - Insurance
Restricted Delivery 3
	Registered without Insurance 4 - Registered without Insurance - REMOVED
	Registered with Insurance 5 - Registered MailTM
	Collect on Delivery 6 - Collect on Delivery
	Return Receipt for Merchandise 7 - Return Receipt for Merchandise
	Return Receipt 8 - Return Receipt
	Certificate of Mailing (Form 3817) (per individual article) 9 - Certificate of Mailing (Form 3817)
Certificate of Mailing (for firm mailing books) 10
	Express Mail Insurance 11 - Priority Mail Express Insurance
	USPS Tracking/Delivery Confirmation 13 - USPS TrackingTM
	USPS TrackingTM Electronic 12 - USPS TrackingTM Electronic
	Signature Confirmation 15 - Signature ConfirmationTM
	Signature ConfirmationTM Electronic 14 - Signature ConfirmationTM Electronic
Return Receipt Electronic 16
	Adult Signature Required 19 - Adult Signature Required
	Adult Signature Restricted Delivery 20 - Adult Signature Restricted Delivery
Priority Mail Express 1030 AM Delivery 200 - Priority Mail Express 1030 AM Delivery

All in order:
$specialservicesdomestic: Certified MailRM USPS TrackingTM Insurance Priority Mail Express Insurance Adult Signature Restricted Delivery Adult Signature Required Registered without Insurance Registered MailTM Collect on Delivery Return Receipt for Merchandise Return Receipt Certificate of Mailing (Form 3817) Signature ConfirmationTM Priority Mail Express 1030 AM Delivery
*/

//@@TODO
// Return Receipt Electronic 110 (16) missing

		$specialservicesdomestic = '';
		$serviceOptions = explode(', ', MODULE_SHIPPING_USPS_DMST_SERVICES); // domestic
		foreach ($serviceOptions as $key => $val) {
			if (strlen($serviceOptions[$key]) > 1) {
				if ($serviceOptions[$key+1] == 'C' || $serviceOptions[$key+1] == 'S' || $serviceOptions[$key+1] == 'Y') {

					if ($serviceOptions[$key] == 'Certified MailRM') {
						$specialservicesdomestic .= '  <SpecialService>105</SpecialService>' . "\n"; // 0
					}
					if ($serviceOptions[$key] == 'Insurance') {
						$specialservicesdomestic .= '  <SpecialService>100</SpecialService>' . "\n"; // 1
					}
					if ($serviceOptions[$key] == 'Registered MailTM') {
						$specialservicesdomestic .= '  <SpecialService>109</SpecialService>' . "\n"; // 5 docs said 4
					}
					if ($serviceOptions[$key] == 'Collect on Delivery') {
						$specialservicesdomestic .= '  <SpecialService>103</SpecialService>' . "\n"; // 6
					}
					if ($serviceOptions[$key] == 'Return Receipt for Merchandise') {
						$specialservicesdomestic .= '  <SpecialService>107</SpecialService>' . "\n"; // 7
					}
					if ($serviceOptions[$key] == 'Return Receipt') {
						$specialservicesdomestic .= '  <SpecialService>102</SpecialService>' . "\n"; // 8
					}
					if ($serviceOptions[$key] == 'Certificate of Mailing (Form 3665)') {
						$specialservicesdomestic .= '  <SpecialService>160</SpecialService>' . "\n"; // 10
					}
					if ($serviceOptions[$key] == 'Certificate of Mailing (Form 3817)') {
						$specialservicesdomestic .= '  <SpecialService>104</SpecialService>' . "\n"; // 9
					}
					if ($serviceOptions[$key] == 'Priority Mail Express Insurance') {
						$specialservicesdomestic .= '  <SpecialService>101</SpecialService>' . "\n"; // 11
					}
					if ($serviceOptions[$key] == 'Priority Mail Insurance') {
						$specialservicesdomestic .= '  <SpecialService>125</SpecialService>' . "\n"; // 1
					}
					if ($serviceOptions[$key] == 'USPS TrackingTM Electronic') {
						$specialservicesdomestic .= '  <SpecialService>155</SpecialService>' . "\n"; // 12 docs said 13
					}
					if ($serviceOptions[$key] == 'USPS TrackingTM') {
						$specialservicesdomestic .= '  <SpecialService>106</SpecialService>' . "\n"; // 13
					}
					if ($serviceOptions[$key] == 'Signature ConfirmationTM Electronic') {
						$specialservicesdomestic .= '  <SpecialService>156</SpecialService>' . "\n"; // 14 docs said 15
					}
					if ($serviceOptions[$key] == 'Signature ConfirmationTM') {
						$specialservicesdomestic .= '  <SpecialService>108</SpecialService>' . "\n"; // 15
					}
					if ($serviceOptions[$key] == 'Adult Signature Required') {
						$specialservicesdomestic .= '  <SpecialService>119</SpecialService>' . "\n"; // 19
					}
					if ($serviceOptions[$key] == 'Adult Signature Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>120</SpecialService>' . "\n"; // 20
					}
					// NOT CURRENTLY WORKING
					if ($serviceOptions[$key] == 'Priority Mail Express 1030 AM Delivery') {
						$specialservicesdomestic .= '  <SpecialService>161</SpecialService>' . "\n"; // 200
					}
					// added 2015_0531
					if ($serviceOptions[$key] == 'Certified MailRM Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>170</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Certified MailRM Adult Signature Required') {
						$specialservicesdomestic .= '  <SpecialService>171</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Certified MailRM Adult Signature Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>172</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Signature ConfirmationTM Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>173</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Signature ConfirmationTM Electronic Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>174</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Collect on Delivery Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>175</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Registered MailTM Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>176</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Insurance Restricted Delivery') {
						$specialservicesdomestic .= '  <SpecialService>177</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Insurance Restricted Delivery (Priority Mail Express)') {
						$specialservicesdomestic .= '  <SpecialService>178</SpecialService>' . "\n";
					}
					if ($serviceOptions[$key] == 'Insurance Restricted Delivery (Priority Mail)') {
						$specialservicesdomestic .= '  <SpecialService>179</SpecialService>' . "\n";
					}

//          $specialservicesdomestic .= $serviceOptions[$key] . "\n";
				}
			}
		}
		if ($specialservicesdomestic) {
			$specialservicesdomestic =
			'<SpecialServices>' .
				$specialservicesdomestic .
			'</SpecialServices>';
		} else {
			$specialservicesdomestic = '';
		}
		return $specialservicesdomestic;
	}

// return ExtraService tags based on checked choices only
	function extra_service() {
/*
The extra service definitions are as follows:
USPS Extra Service Name ServiceID - Our Extra Service Name
 Registered Mail 0 - Registered Mail
 Insurance 1 - Insurance
 Return Receipt 2 - Return Receipt
 Certificate of Mailing 6 - Certificate of Mailing
 Electronic USPS Delivery Confirmation International 9 - Electronic USPS Delivery Confirmation International
*/
		$iserviceOptions = explode(', ', MODULE_SHIPPING_USPS_INTL_SERVICES);
		foreach ($iserviceOptions as $key => $val) {
			if (strlen($iserviceOptions[$key]) > 1) {
				if ($iserviceOptions[$key+1] == 'C' || $iserviceOptions[$key+1] == 'S' || $iserviceOptions[$key+1] == 'Y') {
					if ($iserviceOptions[$key] == 'Registered Mail') {
						$extraserviceinternational .= '  <ExtraService>0</ExtraService>' . "\n";
					}
					if ($iserviceOptions[$key] == 'Insurance') {
						$extraserviceinternational .= '  <ExtraService>1</ExtraService>' . "\n";
					}
					if ($iserviceOptions[$key] == 'Return Receipt') {
						$extraserviceinternational .= '  <ExtraService>2</ExtraService>' . "\n";
					}
					if ($iserviceOptions[$key] == 'Certificate of Mailing') {
						$extraserviceinternational .= '  <ExtraService>6</ExtraService>' . "\n";
					}
					if ($iserviceOptions[$key] == 'Electronic USPS Delivery Confirmation International') {
						$extraserviceinternational .= '  <ExtraService>9</ExtraService>' . "\n";
					}

				}
			}
		}
		if( !empty( $extraserviceinternational ) ) {
			$extraserviceinternational =
			'<ExtraServices>' .
				$extraserviceinternational .
			'</ExtraServices>';
		} else {
			$extraserviceinternational = '';
		}
		return $extraserviceinternational;
	}

	function install() {
		if( !$this->isInstalled() ) {
			parent::install();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('USPS Version Date', 'MODULE_SHIPPING_USPS_VERSION', '2017-09-16', 'You have installed:', '6', '0', 'zen_cfg_select_option(array(''2017-09-16''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Full Name or Short Name', 'MODULE_SHIPPING_USPS_TITLE_SIZE', 'Long', 'Do you want to use a Long or Short name for USPS shipping?', '6', '0', 'zen_cfg_select_option(array(''Long'', ''Short''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Enter the USPS Web Tools User ID', 'MODULE_SHIPPING_USPS_USERID', 'NONE', 'Enter the USPS USERID assigned to you for Rate Quotes/ShippingAPI.', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Which server to use', 'MODULE_SHIPPING_USPS_SERVER', 'production', 'An account at USPS is needed to use the Production server', '6', '0', 'zen_cfg_select_option(array(''test'', ''production''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('All Packages are Machinable?', 'MODULE_SHIPPING_USPS_MACHINABLE', 'False', 'Are all products shipped machinable based on C700 Package Services 2.0 Nonmachinable PARCEL POST USPS Rules and Regulations?<br /><br /><strong>Note: Nonmachinable packages will usually result in a higher Parcel Post Rate Charge.<br /><br />Packages 35lbs or more, or less than 6 ounces (.375), will be overridden and set to False</strong>', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Quote Sort Order', 'MODULE_SHIPPING_USPS_QUOTE_SORT', 'Price-LowToHigh', 'Sorts the returned quotes using the service name Alphanumerically or by Price. Unsorted will give the order provided by USPS.', '6', '0', 'zen_cfg_select_option(array(''Unsorted'',''Alphabetical'', ''Price-LowToHigh'', ''Price-HighToLow''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Decimal Settings', 'MODULE_SHIPPING_USPS_DECIMALS', '3', 'Decimal Setting can be 1, 2 or 3. Sometimes International requires 2 decimals, based on Tare Rates or Product weights. Do you want to use 1, 2 or 3 decimals?', '6', '0', 'zen_cfg_select_option(array(''1'', ''2'', ''3''), ', now())");

			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('USPS Options', 'MODULE_SHIPPING_USPS_OPTIONS', '--none--', 'Select from the following the USPS options.<br />note: this adds a considerable delay in obtaining quotes.', '6', '16', 'zen_cfg_select_multioption(array(''Display weight'', ''Display transit time''), ',  now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('USPS Domestic Transit Time Calculation Mode', 'MODULE_SHIPPING_USPS_TRANSIT_TIME_CALCULATION_MODE', 'NEW', 'Select from the following the USPS options.<br />note: NEW and OLD will add additional time to quotes. CUSTOM allows your custom shipping days.', '6', '16', 'zen_cfg_select_option(array(''CUSTOM'', ''NEW'', ''OLD''), ',  now())");

			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug Mode', 'MODULE_SHIPPING_USPS_DEBUG_MODE', 'Off', 'Would you like to enable debug mode?  A complete detailed log of USPS quote results may be emailed to the store owner, Log results or displayed to Screen.', '6', '0', 'zen_cfg_select_option(array(''Off'', ''Email'', ''Logs'', ''Screen''), ', now())");

			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee - US', 'MODULE_SHIPPING_USPS_HANDLING', '0', 'National Handling fee for this shipping method.', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee - International', 'MODULE_SHIPPING_USPS_HANDLING_INT', '0', 'International Handling fee for this shipping method.', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handling Per Order or Per Box', 'MODULE_SHIPPING_USPS_HANDLING_METHOD', 'Box', 'Do you want to charge Handling Fee Per Order or Per Box?', '6', '0', 'zen_cfg_select_option(array(''Order'', ''Box''), ', now())");

	/*
	Small Flat Rate Box 8-5/8" x 5-3/8" x 1-5/8"
	Global Express Guaranteed - Min. length 9-1/2", height 5-1/2"
	MODULE_SHIPPING_USPS_LENGTH 8.625
	MODULE_SHIPPING_USPS_WIDTH  5.375
	MODULE_SHIPPING_USPS_HEIGHT 1.625
	*/
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('USPS Domestic minimum Length', 'MODULE_SHIPPING_USPS_LENGTH', '8.625', 'The Minimum Length, Width and Height are used to determine shipping methods available for International Shipping.<br />While dimensions are not supported at this time, the Minimums are sent to USPS for obtaining Rate Quotes.<br />In most cases, these Minimums should never have to be changed.<br /><br /><strong>Enter the Domestic</strong><br />Minimum Length - default 8.625', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('USPS minimum Width', 'MODULE_SHIPPING_USPS_WIDTH', '5.375', 'Enter the Minimum Width - default 5.375', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('USPS minimum Height', 'MODULE_SHIPPING_USPS_HEIGHT', '1.625', 'Enter the Minimum Height - default 1.625', '6', '0', now())");

			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('USPS International minimum Length', 'MODULE_SHIPPING_USPS_LENGTH_INTL', '9.50', 'The Minimum Length, Width and Height are used to determine shipping methods available for International Shipping.<br />While dimensions are not supported at this time, the Minimums are sent to USPS for obtaining Rate Quotes.<br />In most cases, these Minimums should never have to be changed.<br /><br /><strong>Enter the International</strong><br />Minimum Length - default 9.50', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('USPS minimum Width', 'MODULE_SHIPPING_USPS_WIDTH_INTL', '1.0', 'Enter the Minimum Width - default 1.0', '6', '0', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('USPS minimum Height', 'MODULE_SHIPPING_USPS_HEIGHT_INTL', '5.50', 'Enter the Minimum Height - default 5.50', '6', '0', now())");

			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable USPS First-Class filter for US shipping', 'MODULE_SHIPPING_USPS_FIRST_CLASS_FILTER_US', 'True', 'Do you want to enable the US First-Class filter to display only 1 First-Class shipping rate?', '6', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
	//    $this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Methods (Domestic and International)',  'MODULE_SHIPPING_USPS_TYPES',  '0, .21875, 0.00, 0, .8125, 0.00, 0, .8125, 0.00, 0, .8125, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 15, 0.00, 0, 20, 0.00, 0, 25, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, .21875, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 66, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 20, 0.00, 0, 20, 0.00, 0, 66, 0.00, 0, 4, 0.00, 0, 20, 0.00, 0, 70, 0.00, 0, 70, 0.00', '<b><u>Checkbox:</u></b> Select the services to be offered<br /><b><u>Minimum Weight (lbs)</u></b>first input field<br /><b><u>Maximum Weight (lbs):</u></b>second input field<br /><br />USPS returns methods based on cart weights.  These settings will allow further control (particularly helpful for flat rate methods) but will not override USPS limits', '6', '0', 'zen_cfg_usps_services(array(''First-Class Mail Letter'', ''First-Class Mail Large Envelope'', ''First-Class Mail Parcel'', ''First-ClassTM Package Service'', ''Media Mail Parcel'', ''Standard PostRM'', ''Priority MailTM'', ''Priority MailTM Flat Rate Envelope'', ''Priority MailTM Legal Flat Rate Envelope'', ''Priority MailTM Padded Flat Rate Envelope'', ''Priority MailTM Small Flat Rate Box'', ''Priority MailTM Medium Flat Rate Box'', ''Priority MailTM Large Flat Rate Box'', ''Priority MailTM Regional Rate Box A'', ''Priority MailTM Regional Rate Box B'', ''Priority MailTM Regional Rate Box C'', ''Priority Mail ExpressTM'', ''Priority Mail ExpressTM Flat Rate Envelope'', ''Priority Mail ExpressTM Legal Flat Rate Envelope'', ''Priority Mail ExpressTM Flat Rate Boxes'', ''First-Class MailRM International Letter'', ''First-Class MailRM International Large Envelope'', ''First-Class Package International ServiceTM'', ''Priority Mail InternationalRM'', ''Priority Mail InternationalRM Flat Rate Envelope'', ''Priority Mail InternationalRM Small Flat Rate Box'', ''Priority Mail InternationalRM Medium Flat Rate Box'', ''Priority Mail InternationalRM Large Flat Rate Box'', ''Priority Mail Express InternationalTM'', ''Priority Mail Express InternationalTM Flat Rate Envelope'', ''Priority Mail Express InternationalTM Flat Rate Boxes'', ''USPS GXGTM Envelopes'', ''Global Express GuaranteedRM (GXG)''), ', now())");
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping Methods (Domestic and International)',  'MODULE_SHIPPING_USPS_TYPES',  '0, .21875, 0.00, 0, .8125, 0.00, 0, .8125, 0.00, 0, .9375, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 15, 0.00, 0, 20, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, .21875, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 66, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 20, 0.00, 0, 20, 0.00, 0, 66, 0.00, 0, 4, 0.00, 0, 70, 0.00, 0, 70, 0.00', '<b><u>Checkbox:</u></b> Select the services to be offered<br /><b><u>Minimum Weight (lbs)</u></b>first input field<br /><b><u>Maximum Weight (lbs):</u></b>second input field<br /><br />USPS returns methods based on cart weights.  These settings will allow further control (particularly helpful for flat rate methods) but will not override USPS limits', '6', '0', 'zen_cfg_usps_services(array(''First-Class Mail Letter'', ''First-Class Mail Large Envelope'', ''First-Class Package Service - RetailTM'', ''First-ClassTM Package Service'', ''Media Mail Parcel'', ''USPS Retail GroundRM'', ''Priority MailTM'', ''Priority MailTM Flat Rate Envelope'', ''Priority MailTM Legal Flat Rate Envelope'', ''Priority MailTM Padded Flat Rate Envelope'', ''Priority MailTM Small Flat Rate Box'', ''Priority MailTM Medium Flat Rate Box'', ''Priority MailTM Large Flat Rate Box'', ''Priority MailTM Regional Rate Box A'', ''Priority MailTM Regional Rate Box B'', ''Priority Mail ExpressTM'', ''Priority Mail ExpressTM Flat Rate Envelope'', ''Priority Mail ExpressTM Legal Flat Rate Envelope'', ''First-Class MailRM International Letter'', ''First-Class MailRM International Large Envelope'', ''First-Class Package International ServiceTM'', ''Priority Mail InternationalRM'', ''Priority Mail InternationalRM Flat Rate Envelope'', ''Priority Mail InternationalRM Small Flat Rate Box'', ''Priority Mail InternationalRM Medium Flat Rate Box'', ''Priority Mail InternationalRM Large Flat Rate Box'', ''Priority Mail Express InternationalTM'', ''Priority Mail Express InternationalTM Flat Rate Envelope'', ''USPS GXGTM Envelopes'', ''Global Express GuaranteedRM (GXG)''), ', now())");

			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Extra Services (Domestic)', 'MODULE_SHIPPING_USPS_DMST_SERVICES', 'Certified MailRM, N, USPS TrackingTM Electronic, N, USPS TrackingTM, N, Insurance, N, Priority Mail Express Insurance, N, Priority Mail Insurance, N, Adult Signature Restricted Delivery, N, Adult Signature Required, N, Registered MailTM, N, Collect on Delivery, N, Return Receipt for Merchandise, N, Return Receipt, N, Certificate of Mailing (Form 3665), N, Certificate of Mailing (Form 3817), N, Signature ConfirmationTM Electronic, N, Signature ConfirmationTM, N, Priority Mail Express 1030 AM Delivery, N, Certified MailRM Restricted Delivery, N, Certified MailRM Adult Signature Required, N, Certified MailRM Adult Signature Restricted Delivery, N, Signature ConfirmationTM Restricted Delivery, N, Signature ConfirmationTM Electronic Restricted Delivery, N, Collect on Delivery Restricted Delivery, N, Registered MailTM Restricted Delivery, N, Insurance Restricted Delivery, N, Insurance Restricted Delivery (Priority Mail Express), N, Insurance Restricted Delivery (Priority Mail), N', 'Included in postage rates.  Not shown to the customer.<br />WARNING: Some services cannot work with other services.', '6', '0', 'zen_cfg_usps_extraservices(array(''Certified MailRM'', ''USPS TrackingTM Electronic'', ''USPS TrackingTM'', ''Insurance'', ''Priority Mail Express Insurance'', ''Priority Mail Insurance'', ''Adult Signature Restricted Delivery'', ''Adult Signature Required'', ''Registered MailTM'', ''Collect on Delivery'', ''Return Receipt for Merchandise'', ''Return Receipt'', ''Certificate of Mailing (Form 3665)'', ''Certificate of Mailing (Form 3817)'', ''Signature ConfirmationTM Electronic'', ''Signature ConfirmationTM'', ''Priority Mail Express 1030 AM Delivery'', ''Certified MailRM Restricted Delivery'', ''Certified MailRM Adult Signature Required'', ''Certified MailRM Adult Signature Restricted Delivery'', ''Signature ConfirmationTM Restricted Delivery'', ''Signature ConfirmationTM Electronic Restricted Delivery'', ''Collect on Delivery Restricted Delivery'', ''Registered MailTM Restricted Delivery'', ''Insurance Restricted Delivery'', ''Insurance Restricted Delivery (Priority Mail Express)'', ''Insurance Restricted Delivery (Priority Mail)''), ', now())");

			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Extra Services (International)', 'MODULE_SHIPPING_USPS_INTL_SERVICES', 'Registered Mail, N, Insurance, N, Return Receipt, N, Electronic USPS Delivery Confirmation International, N, Certificate of Mailing, N', 'Included in postage rates.  Not shown to the customer.<br />WARNING: Some services cannot work with other services.', '6', '0', 'zen_cfg_usps_extraservices(array(''Registered Mail'', ''Insurance'', ''Return Receipt'', ''Electronic USPS Delivery Confirmation International'', ''Certificate of Mailing''), ', now())");

	// Special Services prices and availability will not be returned when Service = ALL or ONLINE
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Retail pricing or Online pricing?', 'MODULE_SHIPPING_USPS_RATE_TYPE', 'Online', 'Rates will be returned ONLY for methods available in this pricing type.  Applies to prices <u>and</u> add on services', '6', '0', 'zen_cfg_select_option(array(''Retail'', ''Online''), ', now())");
		}
	}

	/**
	 * Build array of keys used for installing/managing this module
	 *
	 * @return array
	 */
	function keys() {
		return array_merge( parent::keys(), array(
			'MODULE_SHIPPING_USPS_VERSION',
			'MODULE_SHIPPING_USPS_TITLE_SIZE',
			'MODULE_SHIPPING_USPS_USERID',
			'MODULE_SHIPPING_USPS_SERVER',
			'MODULE_SHIPPING_USPS_QUOTE_SORT',
			'MODULE_SHIPPING_USPS_HANDLING',
			'MODULE_SHIPPING_USPS_HANDLING_INT',
			'MODULE_SHIPPING_USPS_HANDLING_METHOD',
			'MODULE_SHIPPING_USPS_DECIMALS',
			'MODULE_SHIPPING_USPS_MACHINABLE',
			'MODULE_SHIPPING_USPS_OPTIONS',
			'MODULE_SHIPPING_USPS_TRANSIT_TIME_CALCULATION_MODE',
			'MODULE_SHIPPING_USPS_LENGTH',
			'MODULE_SHIPPING_USPS_WIDTH',
			'MODULE_SHIPPING_USPS_HEIGHT',
			'MODULE_SHIPPING_USPS_LENGTH_INTL',
			'MODULE_SHIPPING_USPS_WIDTH_INTL',
			'MODULE_SHIPPING_USPS_HEIGHT_INTL',
			'MODULE_SHIPPING_USPS_FIRST_CLASS_FILTER_US',
			'MODULE_SHIPPING_USPS_TYPES',
			'MODULE_SHIPPING_USPS_DMST_SERVICES',
			'MODULE_SHIPPING_USPS_INTL_SERVICES',
			'MODULE_SHIPPING_USPS_RATE_TYPE'
		) );
	}

}

// admin display functions inspired by osCbyJetta
function zen_cfg_usps_services($select_array, $key_value, $key = '')
{
	$key_values = explode( ", ", $key_value);
	$name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
	$string = '<b><div style="width:20px;float:left;text-align:center;">&nbsp;</div><div style="width:60px;float:left;text-align:center;">Min</div><div style="width:60px;float:left;text-align:center;">Max</div><div style="float:left;"></div><div style="width:60px;float:right;text-align:center;">Handling</div></b><div style="clear:both;"></div>';
	$string_spacing = '<div><br /><br /><b>&nbsp;International Rates:</b><br /></div>' . $string;
	$string_spacing_international = 0;
	$string = '<div><br /><b>&nbsp;Domestic Rates:</b><br /></div>' . $string;
	for ($i=0; $i<sizeof($select_array); $i++)
	{
		if (preg_match("/international/i", $select_array[$i])) {
			$string_spacing_international ++;
		}
		if ($string_spacing_international == 1) {
			$string.= $string_spacing;
		}

		$string .= '<div id="' . $key . $i . '">';
		$string .= '<div style="width:20px;float:left;text-align:center;">' . zen_draw_checkbox_field($name, $select_array[$i], (in_array($select_array[$i], $key_values) ? 'CHECKED' : '')) . '</div>';
		if (in_array($select_array[$i], $key_values)) next($key_values);
		$string .= '<div style="width:60px;float:left;text-align:center;">' . zen_draw_input_field($name, current($key_values), 'size="5"') . '</div>';
		next($key_values);
		$string .= '<div style="width:60px;float:left;text-align:center;">' . zen_draw_input_field($name, current($key_values), 'size="5"') . '</div>';
		next($key_values);
		$string .= '<div style="float:left;">' . preg_replace(array('/RM/', '/TM/', '/International/', '/Envelope/', '/ Mail/', '/Large/', '/Medium/', '/Small/', '/First/', '/Legal/', '/Padded/', '/Flat Rate/', '/Regional Rate/', '/Express Guaranteed /', '/Package\hService\h-\hRetail/', '/Package Service/' ), array('', '', 'Intl', 'Env', '', 'Lg.', 'Md.', 'Sm.', '1st', 'Leg.', 'Pad.', 'F/R', 'R/R', 'Exp Guar', 'Pkgs - Retail', 'Pkgs - Comm'), $select_array[$i]) . '</div>';
		$string .= '<div style="width:60px;float:right;text-align:center;">$' . zen_draw_input_field($name, current($key_values), 'size="4"') . '</div>';
		next($key_values);
		$string .= '<div style="clear:both;"></div></div>';
	}
	return $string;
}
function zen_cfg_usps_extraservices($select_array, $key_value, $key = '')
{
	$key_values = explode( ", ", $key_value);
	$name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
	$string = '<b><div style="width:20px;float:left;text-align:center;">N</div><div style="width:20px;float:left;text-align:center;">Y</div></b><div style="clear:both;"></div>';
	for ($i=0; $i<sizeof($select_array); $i++)
	{
		$string .= zen_draw_hidden_field($name, $select_array[$i]);
		next($key_values);
		$string .= '<div id="' . $key . $i . '">';
		$string .= '<div style="width:20px;float:left;text-align:center;"><input type="checkbox" name="' . $name . '" value="N" ' . (current($key_values) == 'N' || current($key_values) == '' ? 'CHECKED' : '') . ' id="N-'.$key.$i.'" onClick="if(this.checked==1)document.getElementById(\'Y-'.$key.$i.'\').checked=false;else document.getElementById(\'Y-'.$key.$i.'\').checked=true;"></div>';
		$string .= '<div style="width:20px;float:left;text-align:center;"><input type="checkbox" name="' . $name . '" value="Y" ' . (current($key_values) == 'Y' ? 'CHECKED' : '') . ' id="Y-'.$key.$i.'" onClick="if(this.checked==1)document.getElementById(\'N-'.$key.$i.'\').checked=false;else document.getElementById(\'N-'.$key.$i.'\').checked=true;"></div>';
		next($key_values);
		$string .= preg_replace(array('/Signature/', '/without/', '/Merchandise/', '/TM/', '/RM/'), array('Sig', 'w/out', 'Merch.', '', ''), $select_array[$i]) . '<br />';
		$string .= '<div style="clear:both;"></div></div>';
	}
	return $string;
}


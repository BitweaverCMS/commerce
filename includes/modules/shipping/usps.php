<?php
/**
 * USPS Shipping (RESTful) for Zen Cart
 * Version 1.6.2
 *
 * @copyright Portions Copyright 2004-2025 Zen Cart Team
 * @copyright Portions adapted from 2012 osCbyJetta
 * @author Paul Williams (retched)
 * @version $Id: uspsr.php 2025-12-19 retched Version 1.6.2 $
 ****************************************************************************
    USPS Shipping (RESTful) for Zen Cart
    A shipping module for ZenCart, an ecommerce platform
    Copyright (C) 2025  Paul Williams (retched / retched@hotmail.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
****************************************************************************/

class usps extends CommercePluginShippingBase
{

    protected $debug_enabled = FALSE, $typeCheckboxesSelected = [], $debug_filename, $bearerToken, $bearerExpiration = 0, $quote_weight, $_check, $machinable, $shipment_value = 0, $insured_value = 0, $uninsured_value = 0, $orders_tax = 0, $is_us_shipment, $is_apo_dest = FALSE, $usps_countries, $enable_media_mail;
    protected $api_base = 'https://apis.usps.com/';
    protected $ltrQuote, $pkgQuote, $uspsStandards, $uspsLetter, $dimensions = [], $errors = [];

    protected $commError, $commErrNo, $commInfo;

    private const USPSR_CURRENT_VERSION = 'v1.6.2';
    private const ZEN_CART_PLUGIN_ID = 2395;

    /**
     * This holds all of the USPS Zip Codes which are either APO (Air/Army Post Office), FPOs (Fleet Post Office), and
     * DPOs (Diplomatic Post Offices). This should not be removed as it will disable the APO/FPO/DPO Flat Rate.
     * @var array
     */
    private const USPSR_MILITARY_MAIL_ZIP = [
        '09001','09002','09003','09004','09005','09006','09007','09008','09009','09010',
        '09011','09012','09013','09014','09015','09016','09017','09018','09020','09021',
        '09028','09031','09033','09034','09036','09038','09042','09044','09045','09046',
        '09049','09051','09053','09054','09055','09056','09058','09059','09060','09063',
        '09067','09068','09069','09074','09075','09076','09079','09080','09081','09086',
        '09088','09089','09090','09092','09094','09095','09096','09099','09100','09101',
        '09102','09103','09104','09107','09110','09112','09113','09114','09115','09116',
        '09123','09125','09126','09128','09131','09135','09136','09137','09138','09139',
        '09140','09142','09143','09154','09160','09161','09165','09166','09169','09170',
        '09171','09172','09173','09174','09175','09176','09177','09178','09180','09182',
        '09183','09185','09186','09201','09203','09204','09211','09212','09213','09214',
        '09216','09225','09226','09227','09229','09237','09240','09241','09242','09244',
        '09245','09250','09252','09261','09262','09263','09264','09265','09266','09267',
        '09276','09277','09278','09279','09280','09283','09285','09287','09289','09290',
        '09291','09292','09300','09301','09302','09303','09304','09305','09306','09307',
        '09308','09309','09310','09311','09312','09313','09314','09315','09316','09317',
        '09318','09319','09320','09321','09322','09323','09324','09327','09328','09330',
        '09331','09332','09333','09334','09336','09337','09338','09339','09340','09342',
        '09343','09344','09346','09347','09348','09350','09351','09352','09353','09354',
        '09355','09356','09357','09358','09359','09360','09361','09362','09363','09364',
        '09365','09366','09367','09368','09369','09370','09371','09372','09373','09374',
        '09375','09376','09377','09378','09379','09380','09381','09382','09383','09384',
        '09386','09387','09388','09389','09390','09391','09393','09394','09396','09397',
        '09399','09401','09402','09403','09409','09410','09420','09421','09447','09454',
        '09456','09459','09461','09463','09464','09467','09468','09469','09470','09487',
        '09488','09489','09490','09491','09494','09496','09497','09498','09499','09501',
        '09502','09503','09504','09505','09506','09507','09508','09509','09510','09511',
        '09512','09513','09514','09516','09517','09520','09522','09523','09524','09532',
        '09533','09534','09541','09542','09543','09544','09545','09549','09550','09554',
        '09556','09557','09564','09565','09566','09567','09568','09569','09570','09573',
        '09574','09575','09576','09577','09578','09579','09581','09582','09583','09586',
        '09587','09588','09589','09590','09591','09592','09593','09594','09595','09596',
        '09599','09600','09601','09602','09603','09604','09605','09606','09607','09608',
        '09609','09610','09611','09612','09613','09614','09617','09618','09619','09620',
        '09621','09622','09623','09624','09625','09626','09627','09630','09631','09633',
        '09634','09636','09642','09643','09644','09645','09647','09648','09649','09701',
        '09702','09703','09704','09705','09706','09707','09708','09709','09710','09711',
        '09712','09713','09714','09715','09716','09717','09718','09719','09720','09721',
        '09722','09723','09724','09725','09726','09727','09728','09729','09730','09731',
        '09732','09733','09734','09735','09736','09737','09738','09739','09740','09741',
        '09742','09743','09744','09745','09746','09747','09748','09749','09750','09751',
        '09752','09753','09754','09755','09756','09757','09758','09759','09760','09761',
        '09762','09769','09771','09777','09780','09789','09790','09798','09800','09801',
        '09802','09803','09804','09805','09806','09807','09808','09809','09810','09811',
        '09812','09813','09814','09815','09816','09817','09818','09819','09820','09821',
        '09822','09823','09824','09825','09826','09827','09828','09829','09830','09831',
        '09832','09833','09834','09835','09836','09837','09838','09839','09840','09841',
        '09842','09843','09844','09845','09846','09847','09848','09851','09852','09853',
        '09854','09855','09856','09857','09858','09859','09860','09861','09862','09863',
        '09864','09865','09867','09868','09869','09870','09871','09872','09873','09874',
        '09875','09876','09877','09880','09888','09890','09892','09895','09898','09901',
        '09902','09903','09904','09908','09909','09910','09974','09975','09976','09977',
        '09978','34001','34002','34004','34006','34007','34008','34009','34010','34011',
        '34020','34021','34022','34023','34024','34025','34030','34031','34032','34033',
        '34034','34035','34036','34037','34038','34039','34041','34042','34043','34044',
        '34050','34051','34052','34053','34054','34055','34058','34060','34066','34067',
        '34068','34069','34071','34076','34078','34079','34080','34081','34082','34083',
        '34084','34085','34086','34087','34088','34089','34090','34091','34092','34093',
        '34094','34095','34096','34098','34099','96201','96202','96203','96204','96205',
        '96206','96207','96208','96209','96210','96212','96213','96214','96215','96217',
        '96218','96219','96220','96221','96224','96251','96257','96258','96259','96260',
        '96262','96264','96266','96267','96269','96271','96273','96275','96276','96278',
        '96283','96284','96297','96301','96303','96306','96309','96310','96311','96313',
        '96315','96319','96321','96322','96323','96326','96328','96330','96331','96336',
        '96337','96338','96339','96343','96346','96347','96348','96349','96350','96351',
        '96362','96365','96367','96368','96370','96371','96372','96373','96374','96375',
        '96376','96377','96378','96379','96380','96382','96384','96385','96386','96387',
        '96388','96389','96400','96401','96426','96427','96444','96447','96501','96502',
        '96503','96504','96505','96507','96510','96511','96515','96516','96517','96518',
        '96520','96521','96522','96530','96531','96532','96534','96535','96536','96537',
        '96538','96539','96540','96541','96542','96543','96544','96546','96547','96548',
        '96549','96550','96551','96552','96553','96554','96555','96557','96562','96577',
        '96578','96595','96598','96599','96601','96602','96603','96604','96605','96606',
        '96607','96608','96609','96610','96611','96612','96613','96614','96615','96616',
        '96617','96619','96620','96621','96622','96624','96628','96629','96631','96632',
        '96633','96634','96641','96642','96643','96644','96645','96649','96650','96657',
        '96660','96661','96662','96663','96664','96665','96666','96667','96668','96669',
        '96670','96671','96672','96673','96674','96675','96677','96678','96679','96681',
        '96682','96683','96686','96687','96691','96692','96693','96694','96695','96696',
        '96698'
    ];

    /**
     * Main constructor class.
     *
     * A shipping-module’s class constructor performs initialization of its
     * class variables and determines whether the shipping module is
     * enabled for the current order. Upon completion, the class
     * variable enabled identifies whether (true) or not (false) the
     * module is to be enabled for storefront processing.
     */
    public function __construct()
    {
		parent::__construct();
		$this->title = tra( 'United States Postal Service' );
		$this->description = tra( 'United States Postal Service<br /><br />You will need to have a <a href="https://developers.usps.com">USPS Developer User ID</a> to use this module.' );
		$this->booticon = 'fab fa-usps';

		if( $this->isEnabled() ) {
			global $gCommerceSystem;

			// -----
			// Set debug-related variables for use by the uspsrDebug method.
			//
			//$this->debug_filename = DIR_FS_LOGS . '/SHIP_uspsr_Debug_' . (IS_ADMIN_FLAG ? 'adm_' : '') . date('Ymd_His') . '.log';

			$this->typeCheckboxesSelected = explode(', ', $this->getModuleConfigValue( '_TYPES' ));
			
			// Have to leave the defined check, just in case this is an upgrade and it's not yet defined.
			$this->bearerExpiration = (int)$this->getModuleConfigValue( '_BEARER_TOKEN_EXPIRATION' );


			if ($this->checkToken($this->bearerExpiration) ) {
				$this->bearerToken = $this->getModuleConfigValue( '_BEARER_TOKEN' );
			} elseif (!empty($_SESSION['bearer_token'])) {
				$this->bearerToken = $_SESSION['bearer_token'];
				unset($_SESSION['bearer_token']);
			} else {
				$this->getBearerToken();
			}
		}
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
/*
    protected function usps_translation()
    {
        /**
         * use USPS translations for US shops (USPS treats certain regions as
         * "US States" instead of as different "countries", so we translate here)
         * /
        $this->notify('NOTIFY_SHIPPING_USPS_TRANSLATION');
        global $order;
        $delivery_country = 'US';
        if (SHIPPING_ORIGIN_COUNTRY === '223') {
            switch ($pShipHash['destination']['countries_iso_code_2']) {
                case 'AS': // Samoa American
                case 'GU': // Guam
                case 'MP': // Northern Mariana Islands
                case 'PW': // Palau
                case 'PR': // Puerto Rico
                case 'VI': // Virgin Islands US
                // which is right
                case 'FM': // Micronesia, Federated States of
                    break;
                default:
                    $delivery_country = $pShipHash['destination']['countries_iso_code_2'];
                    break;
            }
        } else {
            $delivery_country = $pShipHash['destination']['countries_iso_code_2'];
        }

        // -----
        // If the delivery country is the US, set a multi-use processing flag
        // to simplify the remaining code.
        //
        $this->is_us_shipment = ($delivery_country === 'US');

        return $delivery_country;
    }
*/
	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {

			// What unit are we working with?
			global $gCommerceSystem;
			$shippingUnits = $gCommerceSystem->getConfig( 'SHIPPING_WEIGHT_UNITS', 'lbs' );
			switch ($shippingUnits) {
				case 'kgs':
					// 1 kgs = 2.2046226218487758 lbs
					$this->quote_weight = $pShipHash['shipping_weight_total'] * 2.2046226218487758;
					break;
				case 'lbs':
				default:
					// Since this is in pounds, no conversion is necessary.
					// Additionally, this API doesn't want the weight in ounces and pounds, it only wants pounds and parts there of. So no changing.
					$this->quote_weight = $pShipHash['shipping_weight_total'];
					break;
			}

			/**
			 * Determine if package is machinable or not - Media Mail Only
			 * API will either return both the machinable rate and non-machinable rate or one or the other.
			 *
			 * The store owner will choose a preference. If the preference can be met, show that rate. If it can't be met, but there is only
			 * one rate available... show THAT rate.
			 *
			 * By definition, Media Mail Machinable parcels must weight less than 25lbs with no minimum. Additionally, a package to be machineable
			 * cannot be more than 22 inches long, 18 inches wide, 15 inches high. The USPS considers the longest measurement given to the length, the
			 * 2nd longest measurement is considered it's width, and the third longest it's height. (Actually it considers "length is the measurement of
			 * the longest dimension and girth is the distance around the thickest part".)
			 *
			 * If all else fails, follow the module setting.
			 *
			 * For all other services, this is handled by the API.
			 */

			// Rebuild the dimmensions array
			$pkg_dimensions = array_filter(explode(', ', $this->getModuleConfigValue( '_DIMMENSIONS' )));
			array_walk($pkg_dimensions, function (&$value) {
				$value = floatval(trim($value));
			}); // Quickly remove white space

			$ltr_dimensions = array_filter(explode(', ', $this->getModuleConfigValue( '_LTR_DIMMENSIONS' )));
			array_walk($ltr_dimensions, function (&$value) {
				$value = floatval(trim($value));
			}); // Quickly remove white space

			// Set the weight back 
			if ($this->is_us_shipment) {
				$this->dimensions['pkg_length'] = $pkg_dimensions[0];
				$this->dimensions['pkg_width'] = $pkg_dimensions[2];
				$this->dimensions['pkg_height'] = $pkg_dimensions[4];

				$this->dimensions['ltr_length'] = $ltr_dimensions[0];
				$this->dimensions['ltr_height'] = $ltr_dimensions[2];
				$this->dimensions['ltr_thickness'] = $ltr_dimensions[4];
			} else {
				$this->dimensions['pkg_length'] = $pkg_dimensions[1];
				$this->dimensions['pkg_width'] = $pkg_dimensions[3];
				$this->dimensions['pkg_height'] = $pkg_dimensions[5];

				$this->dimensions['ltr_length'] = $ltr_dimensions[1];
				$this->dimensions['ltr_height'] = $ltr_dimensions[3];
				$this->dimensions['ltr_thickness'] = $ltr_dimensions[5];
			}

			// Notifier: before request quotes
			// -----
			/**
			 * Note for this notifier.
			 * 
			 * $order = Main order details. (Either an actual order in progress or the shipping estimator.)
			 * $this->quote_weight = The main weight of the order (in pounds). If ZenCart is calculating boxes, this is the "box weight".
			 * $pShipHash['shipping_num_boxes'] = The calculated number of boxes
			 * $this->dimensions an array containing the measurements: pkg is for packages, ltr is for letters.
			 * -----------
			 * $this->dimensions['pkg_length']
			 * $this->dimensions['pkg_width']
			 * $this->dimensions['pkg_height']
			 *
			 * $this->dimensions['ltr_length']
			 * $this->dimensions['ltr_height']
			 * $this->dimensions['ltr_thickness']
			 * 
			 */

			// -----
			// Log, if enabled, the base USPS configuration for this quote request.
			//
//			$this->_calcCart( $pShipHash );
//			$this->quoteLogConfiguration();

			// Create the main quotes (both letters and packages)
			$this->_getQuote( $pShipHash );

			// There are two quote fields being used a package

			// Start with package quote
			$uspsQuote = json_decode($this->pkgQuote, TRUE);

			if (!empty($uspsQuote['error'])) {
				// There was an error with the package quote, so prefix the title with "Packages: "
				$this->errors[] = [
					'message' => "Packages: " . $uspsQuote['error']['message'],
					'code' => $uspsQuote['error']['code']
				];
			}

			// Take the Letters Quote and add it to a temp holder
			$_letter = json_decode($this->ltrQuote, TRUE);

			// If there isn't a quote in letters don't bother.
			if (isset($_letter['rates'])) {
				// Force the details of the Letter Request to match the other pieces from packages (adding a Mail Class to match Standards result, productName, and processingCategory)
				$_letter['rates'][0]['mailClass'] .= "_" . strtoupper($this->getModuleConfigValue( '_LTR_PROCESSING' )); // This should yield something: FIRST-CLASS_MAIL_FLATS
				$_letter['rates'][0]['productName'] = ($this->is_us_shipment ? 'First-Class Mail Letter' : 'First-Class Mail International Letter' );
				$_letter['rates'][0]['processingCategory'] = $this->getModuleConfigValue( '_LTR_PROCESSING' );

				# Bug fix for letters since the Domestic metered rate from the API is four cents off. (International seems to come through as normal.)
				# @todo Maybe toggle if First Class Mail is metered or not?
				if ($this->is_us_shipment) {
					$_letter['rates'][0]['price'] += 0.04;
					$_letter['totalBasePrice'] += 0.04;
				}

				$uspsQuote['rateOptions'][] = $_letter;
			} else { // We likely have an error, so add that error to the list of errors.

				$this->errors[] = [
					'message' => "Letters: " . $_letter['error']['message'],
					'code' => $_letter['error']['code']
				];

			}

			if (isset($uspsQuote['rateOptions']) && is_array($uspsQuote['rateOptions'])) {

				// Was a standards call made? If so, load it up.
				if (zen_not_null($this->uspsStandards)) {
					$uspsStandards = $this->uspsStandards;
				} else $uspsStandards = [];

				// ----
				// Selected Methods Builder

				// Go through each of the $this->typeCheckboxesSelected and build a list.
				$selected_methods = [];
				$build_quotes = [];
				for ($i = 0; $i <= count($this->typeCheckboxesSelected) - 1; $i++) {
					if (!is_numeric($this->typeCheckboxesSelected[$i]) && zen_not_null($this->typeCheckboxesSelected[$i])) {
						// Fool proofing the entry of the two values.
						$limits = [(float) $this->typeCheckboxesSelected[$i + 1], (float) $this->typeCheckboxesSelected[$i + 2]];

						// Does this need to be converted into pounds?
						if (defined('SHIPPING_WEIGHT_UNITS') && SHIPPING_WEIGHT_UNITS == 'kgs') {
							$limits[0] *= 2.2046226218487758;
							$limits[1] *= 2.2046226218487758;

							// 1 kgs = 2.2046226218487758 lbs.
						}

						$selected_methods[] = [
							'min_weight' => min($limits),
							'max_weight' => max($limits),
							'method' => $this->typeCheckboxesSelected[$i],
							'handling' => $this->typeCheckboxesSelected[$i + 3]
						];

					}
				}

				$message = '';
				$message .= "\n" . '===============================================' . "\n";
				$message .= 'Reviewing selected method options...' . "\n";
				$message .= print_r($selected_methods, TRUE);
				$this->uspsrDebug($message);

				
				// Order Handling Costs
				if ($pShipHash['destination']['countries_id'] === SHIPPING_ORIGIN_COUNTRY || $this->is_us_shipment === true) {
					// domestic/national
					$usps_handling_fee = (float) $this->getModuleConfigValue( '_HANDLING_DOMESTIC' );
				} else {
					// international
					$usps_handling_fee = (float) $this->getModuleConfigValue( '_HANDLING_INTL' );
				}

				// ----
				// We have the new uni-quote (packages and letters)
				// Now build the mapping array
				$lookup = [];

				// Build lookup from rates
				foreach ($uspsQuote['rateOptions'] as $opt) {
					
					// Base Price of the rate, more in a second.
					$totalBasePrice = $opt['totalBasePrice'] ?? null; // get totalBasePrice if it exists

					// Main rates
					foreach ($opt['rates'] as $rate) {

						// ---------------------------------------------
						// Skip OPEN_AND_DISTRIBUTE rates, we don't use those.
						// ---------------------------------------------
						if ($rate['processingCategory'] === 'OPEN_AND_DISTRIBUTE') continue;

						// ---------------------------------------------
						// Setup the key (if productName is blank, use description instead)
						// ---------------------------------------------
						if (!empty($rate['productName'])) $name = $rate['productName'];
						else {
							$name = $rate['description'];
							$rate['productName'] = $rate['description'];
						}

						// ---------------------------------------------
						// Trim the extra characters off (looking at you 'Connect Local Machinable DDU ')
						// ---------------------------------------------
						$name = trim($name);
						
						// ---------------------------------------------
						// Test to see what the totalBasePrice 
						// For Packages: This will be the base fee plus any special fees. Will not include any services.
						// For Letters: This will automatically add any special fees but will NOT add services
						// ---------------------------------------------
						$rate['totalBasePrice'] = $totalBasePrice ?? $rate['price']; // default to price if null/unset

						// ---------------------------------------------
						// Possible outcomes from the rate listings
						// Possible outcomes:
						// Priority Mail: Machinable + SP or Nonstandard + SP/DR/DN
						// Priority Mail Express: Machinable + SP or Nonstandard + PA/DR/DN
						// Ground Advantage: Machinable + SP or Nonstandard + SP/DR/DN/LO
						// Media Mail: Machinable / Nonstandard, both with SP
						// Priority Mail Cubic: Machinable , CP or Px/Qx
						// Ground Advantage Cubic: Machinable , CP or Px/Qx
						// Connect Local: LC/LF/LL/LS/LO
						//
						// International shipments only have one class, regardless. So in total, this will ignore everything.
						// ---------------------------------------------
						
						// ---------------------------------------------
						// Media Mail
						// ---------------------------------------------
						if (strpos($name, "Media Mail") !== FALSE) {
							// Only allow Single Piece (SP)
							if ($rate['rateIndicator'] === "SP") {
								if (($this->getModuleConfigValue( '_MEDIA_CLASS' ) == 'Nonstandard' && strpos($name, "Nonstandard") !== FALSE) ||
									($this->getModuleConfigValue( '_MEDIA_CLASS' ) == 'Machinable' && strpos($name, "Machinable") !== FALSE)) {
									$name = "Media Mail"; 
									$rate['productName'] = "Media Mail"; // Have to add "Media Mail" as productName is otherwise blank.
								} else {
									continue 2;
								}
							} else {
								continue 2;
							}
						}

						// ---------------------------------------------
						// Cubic Options (Priority Mail Cubic / Ground Advantage Cubic)
						// ---------------------------------------------
						elseif (strpos($name, "Priority Mail Cubic") !== FALSE || strpos($name, "Ground Advantage Cubic") !== FALSE) {
							if (preg_match('/^(CP|[CPQ]\d)$/', $rate['rateIndicator'])) {
								if ($this->getModuleConfigValue( '_CUBIC_CLASS' ) == "Non-Soft" && $rate['rateIndicator'] !== "CP") continue 2;
								if ($this->getModuleConfigValue( '_CUBIC_CLASS' ) == "Soft" && !preg_match('/^([PQ]\d)$/', $rate['rateIndicator'])) continue 2;
							} else {
								continue 2;
							}
						}

						// ---------------------------------------------
						// Nonstandard cases (Priority / Express / Ground / Connect Local)
						// ---------------------------------------------
						elseif ($rate['processingCategory'] === 'NONSTANDARD') {
							// Priority Mail
							if (strpos($name, "Priority Mail") !== FALSE && $rate['rateIndicator'] === "SP") {
								// allow
							}
							// Priority Mail Express
							elseif (strpos($name, "Priority Mail Express") !== FALSE && $rate['rateIndicator'] === "PA") {
								// allow
							}
							// Ground Advantage
							elseif (strpos($name, "Ground Advantage") !== FALSE && $rate['rateIndicator'] === "SP") {
								// allow
							}
							// Ground Advantage OS
							elseif (strpos($name, "Ground Advantage") !== FALSE && $rate['rateIndicator'] === "OS") {
								// allow
							}
							// Connect Local LO
							elseif (strpos($name, "Connect Local") !== FALSE && $rate['rateIndicator'] === "LO") {
								$rate['productName'] = $rate['description'];
								// otherwise allow
							}
							// Dimensional Class fallback (DR / DN)
							elseif ($this->getModuleConfigValue( '_DIMENSIONAL_CLASS' ) == 'Rectangular' && $rate['rateIndicator'] !== 'DR') {
								continue 2;
							} elseif ($this->getModuleConfigValue( '_DIMENSIONAL_CLASS' ) == 'Nonrectangular' && $rate['rateIndicator'] !== 'DN') {
								continue 2;
							}
						}

						// ---------------------------------------------
						// Machinable cases (Priority / Express / Ground)
						// ---------------------------------------------
						elseif ($rate['processingCategory'] === 'Machinable') {
							// Priority Mail, Express, Ground → must be SP
							if (($rate['rateIndicator'] !== "SP") &&
								(strpos($name, "Priority Mail") !== FALSE ||
								strpos($name, "Priority Mail Express") !== FALSE ||
								strpos($name, "Ground Advantage") !== FALSE)) {
								continue 2;
							}
						}

						// ---------------------------------------------
						// Connect Local: the "Product Names" do not appear in the API, force them in to match.
						// ---------------------------------------------
						if (strpos($name, "Connect Local") !== FALSE) $rate['productName'] = $rate['description'];

						// ---------------------------------------------
						// Default: All is OK, add it to the list
						// ---------------------------------------------
						$lookup[$name] = $rate;
					}

					// ---------------------------------------------
					// Extra services: Tack that onto the main roster of returns.
					// ---------------------------------------------
					if (isset($opt['extraServices'])) {
						foreach ($opt['extraServices'] as $svc) {
							$lookup[$name]['extraService'][$svc['extraService']] = $svc;
						}
					}

				} // Done with iterating the returned rates

				$message = "\n";
				$message .= '===============================================' . "\n";
				$message .= 'Lookup lists' . "\n";
				$message .= print_r($lookup, TRUE) . "\n";
				$message .= '===============================================' . "\n";
				// $this->uspsrDebug($message); // Hiding to reduce log file size

				$m = 0; //Index for ZenCart quote builder (ie. "usps0")

				// Extra Services
				if ($this->is_us_shipment) {
					$ltr_services = array_map('intval', explode(',', $this->getModuleConfigValue( '_DMST_LETTER_SERVICES' )));
					$pkg_services = array_map('intval', explode(',', $this->getModuleConfigValue( '_DMST_SERVICES' )));
				} else {
					$ltr_services = array_map('intval', explode(',', $this->getModuleConfigValue( '_INTL_LETTER_SERVICES' )));
					$pkg_services = array_map('intval', explode(',', $this->getModuleConfigValue( '_INTL_SERVICES' )));
				}

				// If either list has the insurance code (930), add the other one.
				if (in_array(930, $ltr_services)) $ltr_services[] = 931;
				if (in_array(930, $pkg_services)) $pkg_services[] = 931;
				
				// Now go through the list of SELECTED services from the configurator and do the work on THOSE
				foreach ($selected_methods as $method_item) {

					// If the $method_item['method'] is it the lookup, continue, otherwise, pass
					if (isset($lookup[$method_item['method']])) {
						
						$quotes = [];
						$match = TRUE;
						$made_weight = FALSE;
						$services_total = 0;

						// If this package is NOT going to an APO/FPO/DPO, skip and continue to the next
						// Currently this is the only rate which has a different rate for APO/FPO/DPO rates.
						if (!$this->is_apo_dest && ($method_item['method'] === 'Priority Mail Large Flat Rate APO/FPO/DPO'))
							continue;

						$price = $lookup[$method_item['method']]['totalBasePrice'];
						
						// Go through and add up the appropriate amount as necessary.
						$services = strpos($method_item['method'], "Letter") !== false ? $ltr_services : $pkg_services;

						// For packages, cycle through and add the services. (For letters, the price is baked into the request and result. Don't do it.)
						$servicesList = '';
						$extraServices = 0;
						if (strpos($method_item['method'], "First-Class") === FALSE) {
							$method_name   = $method_item['method'];
							$method_labels = [];        // tracks names of extra services

							foreach ($services as $s) {
								if (isset($lookup[$method_name]['extraService'][$s]['price'])) {
									$method_price = $lookup[$method_name]['extraService'][$s]['price'];
									$extraServices += $method_price;

									// Add service name if available, otherwise fall back to the code
									$label = $lookup[$method_name]['extraService'][$s]['name'] ?? $s;
									$method_labels[] = $label . " (" . $currencies->format($method_price) . ")";
								}
							}

							// Convert collected labels into a comma-separated string
							$servicesList = implode(", ", $method_labels);
						}

						// Extra Services for method 
						$price += $extraServices;

						// Handling as defined per method
						$price += $method_item['handling'];

						// Multiply the quote times the number of shipping of boxes.
						// ZenCart calculates the number of boxes by "weight" and as such, it divides the quote by weight.
						// So this "restores" the quote to the full weight by multiplying the number of boxes.
						$price *= $pShipHash['shipping_num_boxes'];

						// Handling for using USPS as a whole.
						$price += $usps_handling_fee * ($this->getModuleConfigValue( '_HANDLING_METHOD' ) === 'Box' ? $pShipHash['shipping_num_boxes'] : 1);
						
						// Final Math: Price = ((Method Quote + Method Services (ie. Certified Mail, etc.) + Method Handling (the box on the far right of the method)) * the number of boxes) + (Overall USPS Handling Fee * number of boxes OR 1)
						// Holdover observer from original USPS module. Simple put:
						// -----
						// $method_item['method']  Contains the "Friendly Name" of the desired method, can be used to check
						// $quotes['title'] Output Title, sent to ZenCart
						// $quotes['cost']  Cost. Sent to ZenCart, should be a number. Not a currency.
						
						// If everything passes their checks (match, observer, make weight....) add it.
						
						// If $method is not empty, compare it to the $quotes id. If it matches, add it
						if (!empty($method) && ($method !== $quotes['id'])) $match = FALSE;

						// Did the order make weight?
						if ($this->quote_weight >= $method_item['min_weight'] && $this->quote_weight <= $method_item['max_weight']) $made_weight = TRUE;

						if ($match && $made_weight) {
							global $currencies;
							$transitTime = NULL;
							$deliveryDate = NULL;
							if ($this->is_us_shipment && isset($uspsStandards[$quotes['mailClass']])) { // Only do this for domestic shipments
								// If there is a standards request, add that line:
								switch ($this->getModuleConfigValue( '_DISPLAY_TRANSIT' )) 
								{
									case "Estimate Transit Time":
										$transitTime = (int)$uspsStandards[$quotes['mailClass']]['serviceStandard'] + (int) $this->getModuleConfigValue( '_HANDLING_TIME' );
										break;
									case "Estimate Delivery":
										$est_delivery_raw = new DateTime($uspsStandards[$quotes['mailClass']]['delivery']['scheduledDeliveryDateTime']);
										$deliveryDate = $est_delivery_raw->format(DATE_FORMAT);
										break;
								}
							}
							// Okay, we have the methods, we have the quotes: start building.
							$quote = [
								'id' => $m,
								'title' => uspsr_filter_gibberish($lookup[$method_item['method']]['productName']),
								'cost' => $price,
								'transit_time' => $transitTime,
								'delivery_date' => $deliveryDate,
								'code' => $lookup[$method_item['method']]['mailClass'],
								'servicesAdded' => $servicesList, // For debugging
							];
							$m++;

							// If everything checks out... Add it to the 
							$build_quotes[] = $quote;
						}	
					} 
				}

				// Squash Ground Advantage
				if (strpos($this->getModuleConfigValue( '_SQUASH_OPTIONS' ), "Ground Advantage") !== FALSE) {
					$groundOptions = [];
					$pattern = '/Ground Advantage/'; // There is no flat rate Ground Advantage, so you're dealing with the only two outcomes.

					// Loop through the array to collect priority mail options
					foreach ($build_quotes as $key => $option) {
						if (preg_match($pattern, $option['title'])) {
							$groundOptions[] = [
								'key' => $key,
								'cost' => $option['cost']
							];
						}
					}

					// If both variants exist, remove the more expensive one
					if (count($groundOptions) == 2) {
						//if (isset($groundOptions['Ground Advantage']) && isset($groundOptions['Ground Advantage Cubic'])) {
						$removeKey = ($groundOptions[0]['cost'] > $groundOptions[1]['cost'])
							? $groundOptions[0]['key']
							: $groundOptions[1]['key'];

						$removal_message = '';
						$removal_message .= "\n" . 'SQUASHED option : ' . $build_quotes[$removeKey]['title'] . "\n";

						unset($build_quotes[$removeKey]);
						$this->uspsrDebug($removal_message);
					}

					$build_quotes = array_values($build_quotes);
				}

				// Squash Priority Mail
				if (strpos($this->getModuleConfigValue( '_SQUASH_OPTIONS' ), "Priority Mail") !== FALSE) {
					$priorityOptions = [];
					$pattern = '/^Priority Mail(?: Cubic)*$/';

					// Loop through the array to collect priority mail options
					foreach ($build_quotes as $key => $option) {
						if (preg_match($pattern, $option['title'])) {
							$priorityOptions[] = [
								'key' => $key,
								'cost' => $option['cost']
							];
						}
					}

					// If both variants exist, remove the more expensive one
					if (count($priorityOptions) == 2) {
						//if (isset($priorityOptions['Priority Mail']) && isset($priorityOptions['Priority Mail Cubic'])) {
						$removeKey = ($priorityOptions[0]['cost'] > $priorityOptions[1]['cost'])
							? $priorityOptions[0]['key']
							: $priorityOptions[1]['key'];

						// Removal Message for Debug
						$removal_message = '';
						$removal_message .= "\n" . 'SQUASHED option : ' . $build_quotes[$removeKey]['title'] . "\n";

						unset($build_quotes[$removeKey]);
						$this->uspsrDebug($removal_message);
					}
				}

				// Build Estimates Attachment
				if (!empty($uspsStandards)) {
					switch ($this->getModuleConfigValue( '_DISPLAY_TRANSIT' )) {
						case "Estimate Transit Time":
							foreach ($build_quotes as &$quote) {
								if (isset($uspsStandards[$quote['mailClass']]['serviceStandard'])) $quote['title'] .= " [" . $this->getModuleConfigValue( '_TEXT_ESTIMATED' ) . " " . zen_uspsr_estimate_days($uspsStandards[$quote['mailClass']]['serviceStandard']) . "]";
							}
							break;
						case "Estimate Delivery":
							foreach ($build_quotes as &$quote) {

								if (isset($uspsStandards[$quote['mailClass']]['delivery']['scheduledDeliveryDateTime'])) {
									$est_delivery_raw = new DateTime($uspsStandards[$quote['mailClass']]['delivery']['scheduledDeliveryDateTime']);
									$est_delivery = $est_delivery_raw->format(DATE_FORMAT);

									$quote['title'] .= " [" . $this->getModuleConfigValue( '_TEXT_ESTIMATED_DELIVERY' ) . " " . $est_delivery . "]";
								}
							}
							break;
					}
				}

				// Okay we have our list of Build Quotes, so now... we need to sort pursurant to options
				switch ($this->getModuleConfigValue( '_QUOTE_SORT' )) {
					case 'Alphabetical':
						usort($build_quotes, function ($a, $b) {
							return $a['title'] <=> $b['title'];
						});
						break;
					case 'Price-LowToHigh':
						usort($build_quotes, function ($a, $b) {
							return $a['cost'] <=> $b['cost'];
						});
						break;
					case 'Price-HighToLow':
						usort($build_quotes, function ($a, $b) {
							return $b['cost'] <=> $a['cost'];
						});
						break;
					case 'Unsorted':
						// Do nothing, leave it as is
						break;
				}

				$message = "\n";
				$message .= '===============================================' . "\n";
				$message .= 'Displayed options' . "\n";
				$message .= 'Sorting the returned quotes by: ' . $this->getModuleConfigValue( '_QUOTE_SORT' ) . "\n";
				$message .= print_r($build_quotes, TRUE) . "\n";
				$message .= '===============================================' . "\n";

				$this->uspsrDebug($message);

				if (count($build_quotes) > 0) {
					// Close off and make the final array.
					$quotes = [
						'id' => $this->code,
						'icon' => zen_image($this->icon),
						'module' => $this->title,
						'methods' => $build_quotes,
						'tax' => ($this->tax_class > 0) ? zen_get_tax_rate($this->tax_class, $pShipHash['destination']['countries_id'], $pShipHash['destination']['zone_id']) : null,
					];
					// Should there be a warning that the dates are estimations?

				}  // If we made it this far, there is no point in outputting an error message of any kind.
			} else {
				if ($this->debug_enabled === true && (strpos($this->getModuleConfigValue( '_DEBUG_MODE' ), "Error") !== FALSE) && empty($build_quotes)) {

					// We have an error and error debugging is enabled, so output the error.
					// (Can't show both errors and quotes at the same time.)

					$error_str = '';
					foreach ($this->errors as $error) {
						$error_str .= $error['message'] . " (Code: " . $error['code'] . ")<br>";
					}
					
					$quotes = [
						'id' => $this->code,
						'icon' => zen_image($this->icon),
						'module' => $this->title,
						'methods' => [],
						'error' => '<pre style="white-space: pre-wrap;word-wrap: break-word;">' . $error_str . "</pre>",
					];

				}

			}
		}
        
        return $quotes;
    }


	protected function config() {
		$i = 3;
		$ret = array_merge( parent::config(), array( 
			$this->getModuleKeyTrunk().'_TITLE_SIZE' => array(
				'configuration_title' => 'Full Name or Short Name',
				'configuration_value' => 'Short',
				'configuration_description' => 'Do you want to use the Long (United States Postal Service) or Short name (USPS) for USPS shipping?',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_select_option([\'Long\', \'Short\'], ',
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_API_KEY' => array(
				'configuration_title' => 'Enter the USPS API Consumer Key',
				'configuration_value' => 'NONE',
				'configuration_description' => 'Enter your USPS API Consumer Key assigned to the app dedicated for this website.<br><br><strong>NOTE:</strong> This is NOT the same as the WebTools USERID and is NOT your USPS.com account Username.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_API_SECRET' => array(
				'configuration_title' => 'Enter the USPS API Consumer Secret',
				'configuration_value' => 'NONE',
				'configuration_description' => 'Enter the USPS API Consumer Secret assigned to the app dedicated for this website.<br><br><strong>NOTE:</strong> This is NOT the same as the WebTools PASSWORD and is NOT your USPS.com account Password.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_HANDLING_DOMESTIC' => array(
				'configuration_title' => 'Overall Handling Fee - US',
				'configuration_value' => '0',
				'configuration_description' => 'Domestic Handling fee for this shipping method.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_HANDLING_INTL' => array(
				'configuration_title' => 'Overall Handling Fee - International',
				'configuration_value' => '0',
				'configuration_description' => 'International Handling fee for this shipping method.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_HANDLING_METHOD' => array(
				'configuration_title' => 'Handling Per Order or Per Box',
				'configuration_value' => 'Order',
				'configuration_description' => 'Do you want to charge Handling Fee Per Order or Per Box?<br><br><em>Boxes are defined by ZenCart\'s estimation of what will fit in a box.</em>',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_select_option([\'Order\', \'Box\'], ',
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_ZONE' => array(
				'configuration_title' => 'Shipping Zones',
				'configuration_value' => '0',
				'configuration_description' => 'If a zone is selected, only enable this shipping method for that zone.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_pull_down_zone_classes(',
				'use_function' => 'zen_get_zone_class_title',
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_MEDIA_CLASS' => array(
				'configuration_title' => 'Packaging Class - Media Mail',
				'configuration_value' => 'Machinable',
				'configuration_description' => 'For Media Mail only, are your packages typically machinable?<br><br>"Machinable" means a mail piece designed and sized to be processed by automated postal equipment. Typically this is rigid mail, that fits a certain shape and is within a certain weight (no more than 25 pounds for Media Mail). If your normal packages are within these guidelines, set this flag to "Machinable". Otherwise, set this to "Nonstandard". (If your customer order\'s total weight or package size falls outside this limit, regardless of the setting, the module will set the package to "Nonstandard".) (If your customer order\'s total weight or package size falls outside of this limit, regardless of the setting, the module will set the package to "Nonstandard".) <br><br>This applies only to Media Mail. All other mail services will have their "Machinability" status determined by the weight of the cart and the size of the package entered below.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_select_option([\'Machinable\', \'Nonstandard\'], ',
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_DIMENSIONAL_CLASS' => array(
				'configuration_title' => 'Packaging Class - Dimensional Pricing',
				'configuration_value' => 'Rectangular',
				'configuration_description' => 'Are your packages typically rectangular?<br><br><em>"Rectangular"</em> means a mail piece that is a standard four-corner box shape that is not significantly curved or oddly angled. Something like a typical cardboard shipping box would fit this. If you use any kind of bubble mailer or poly mailer instead of a basic box, you should choose Nonrectangular.<br><br><em>Typically this would only really apply under extreme quotes like extra heavy or big packages.</em>',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_select_option([\'Rectangular\', \'Nonrectangular\'], ',
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_CUBIC_CLASS' => array(
				'configuration_title' => 'Packaging Class - Cubic Pricing',
				'configuration_value' => 'Non-Soft',
				'configuration_description' => 'How would you class the packaging of your items?<br><br><em>"Non-Soft"</em> refers to packaging that is rigid in shape and form, like a box.<br><br><em>"Soft"</em> refers to packaging that is usually cloth, plastic, or vinyl packaging that is flexible enough to adhere closely to the contents being packaged and strong enough to securely contain the contents.<br><br>Choose the style that best fits how you (on average) ship out your packages.<br><em>This selection only applies to Cubic Pricing such as Ground Advantage Cubic, Priority Mail Cubic, Priority Mail Express Cubic</em>',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_select_option([\'Non-Soft\', \'Soft\'], ',
				'date_added' => 'now()'
			),
			$this->getModuleKeyTrunk().'_SQUASH_OPTIONS' => array(
				'configuration_title' => 'Squash Alike Methods Together',
				'configuration_value' => '--none--',
				'configuration_description' => 'If you are offering Priority Mail and Priority Mail Cubic or Ground Advantage and Ground Advantage Cubic in the same quote, do you want to "squash" them together and offer the lower of each pair?<br><br>This will only work if the quote returned from USPS has BOTH options (Cubic and Normal) in it, otherwise it will be ignored.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_select_multioption([\'Squash Ground Advantage\', \'Squash Priority Mail\'], '
			),
			$this->getModuleKeyTrunk().'_DISPLAY_TRANSIT' => array(
				'configuration_title' => 'Display Transit Time',
				'configuration_value' => 'No',
				'configuration_description' => 'Would you like to display an estimated delivery date (ex. "est. delivery: 12/25/2025") or estimate delivery time (ex. "est. 2 days") for the service? This is pulled from the service guarantees listed by the USPS. If the service doesn\'t have a set guideline, no time quote will be displayed.<br><br>Only applies to US based deliveries.',
				'configuration_group_id' => 6,
				'sort_order' => 0,
				'set_function' => 'zen_cfg_select_option([\'No\', \'Estimate Delivery\', \'Estimate Transit Time\'], ',
				'date_added' => 'now()'
			),
/*
        $insert_handling_array = [
            'configuration_title' => 'Handling Time',
            'configuration_value' => '1',
            'configuration_description' => 'In whole numbers, how many days does it take for you to dispatch your packages to the USPS. (Enter as a whole number only. Between 0 and 30. This will be added to the estimated delivery date or time as needed.)',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'use_function' => 'zen_uspsr_estimate_days',
            'date_added' => 'now()'
        ];

        /**
         * Package Dimensions
         * The Small Flat Rate Box is 8-5/8" x 5-3/8" x 1-5/8". That's the minimum.
         * These two rows control the same functionality, but only one will be inserted.
         *
         * @todo Figure out how the new ZenCart uses the product length, width, and height fields.
         */
/*
        if (defined('SHIPPING_DIMENSION_UNITS') && SHIPPING_DIMENSION_UNITS == "centimeters") {
            $this->addConfigurationKey('MODULE_SHIPPING_USPS_DIMMENSIONS', [
                'configuration_title' => 'Typical Package Dimensions (Domestic and International)',
                'configuration_value' => '21.9075, 21.9075, 13.6525, 13.6525, 4.1275, 4.1275',
                'configuration_description' => 'The Minimum Length, Width and Height are used to determine shipping methods available for International Shipping.<br><br>While per-item dimensions are not supported by this module at this time, the minimums listed below are sent to USPS for obtaining Rate Quotes.<br><br>In most cases, these Minimums should never have to be changed.<br><br><em>These measurements will be converted to inches as part of the quoting process as your cart was set to centimeters when it was installed. If you change your cart setting, you will need to reenter these values.<br>',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'set_function' => 'zen_cfg_uspsr_dimmensions(',
                'use_function' => 'zen_cfg_uspsr_showdimmensions',
                'date_added' => 'now()'
            ),
        } else {
*/
            $this->getModuleKeyTrunk().'_DIMMENSIONS' => array(
                'configuration_title' => 'Typical Package Dimensions (Domestic and International)',
                'configuration_value' => '8.625, 8.625, 5.375, 5.375, 1.625, 1.625',
                'configuration_description' => 'The Minimum Length, Width and Height are used to determine shipping methods available for International Shipping.<br><br>While per-item dimensions are not supported at this time, the minimums listed below are sent to USPS for obtaining Rate Quotes.<br><br>In most cases, these Minimums should never have to be changed.<br>These measurements should be in inches.<br>',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'set_function' => 'zen_cfg_uspsr_dimmensions(',
                'use_function' => 'zen_cfg_uspsr_showdimmensions',
                'date_added' => 'now()'
            ),
/*
        }
*/

        /**
         * Letter Dimensions
         * A #10 sized envelope is 4-1/8 x 9-1/2 x 0.007 inches. That's the minimum.
         * These two rows control the same functionality, but only one will be inserted.
         *
         * @todo Figure out how the new ZenCart uses the product length, width, and height fields.
         */
/*
        if (defined('SHIPPING_DIMENSION_UNITS') && SHIPPING_DIMENSION_UNITS == "centimeters") {
            $this->addConfigurationKey('MODULE_SHIPPING_USPS_LTR_DIMMENSIONS', [
                'configuration_title' => 'Typical Letter Dimensions (Domestic and International)',
                'configuration_value' => '21.9075, 21.9075, 13.6525, 13.6525, 4.1275, 4.1275',
                'configuration_description' => 'The Minimum Length, Height, and Thickness are used to determine shipping methods available for sending of letters.<br><br>While per-item dimensions are not supported by this module at this time, the minimums listed below are sent to USPS for obtaining Rate Quotes.<br><br>In most cases, these Minimums should never have to be changed.<br><br><em>These measurements will be converted to inches as part of the quoting process as your cart was set to centimeters when it was installed. If you change your cart setting, you will need to reenter these values.<br>',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'set_function' => 'zen_cfg_uspsr_ltr_dimmensions(',
                'use_function' => 'zen_cfg_uspsr_showdimmensions',
                'date_added' => 'now()'
            ]);
        } else {
*/
            $this->getModuleKeyTrunk().'_LTR_DIMMENSIONS' => array(
                'configuration_title' => 'Typical Letter Dimensions (Domestic and International)',
                'configuration_value' => '4.125, 4.125, 9.5, 9.5, 0.007, 0.007',
                'configuration_description' => 'The Minimum Minimum Length, Height, and Thickness are used to determine shipping methods available for sending of letters.<br><br>While per-item dimensions are not supported at this time, the minimums listed below are sent to USPS for obtaining Rate Quotes.<br><br>In most cases, these Minimums should never have to be changed.<br>These measurements should be in inches.<br>',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'set_function' => 'zen_cfg_uspsr_ltr_dimmensions(',
                'use_function' => 'zen_cfg_uspsr_showdimmensions',
                'date_added' => 'now()'
            ),
  //      }

        $this->getModuleKeyTrunk().'_LTR_PROCESSING' => array(
            'configuration_title' => 'Packaging Class - Letters',
            'configuration_value' => 'Letters',
            'configuration_description' => 'How would you class the packaging of your letters?<br><br><em>"Letters"</em> refers to packaging that is rigid in shape and form, like a plain white envelope (#10). A letter is a rectangular piece no more than 6.125" by 11.5" with a thickness no greater than .25" inches. (Anything greater than this or smaller than the minimums will be treated as non-machineable.<br><br><em>"Flats"</em> typically refer to large envelopes, newsletters, and magazines. Flats must be no greater than 12 inches by 15 inches with a thickness no greater than .75 inches.<br><br><em>"Cards"</em> plainly mean simple postcards with specific measurements.<br><br>Choose the style that best fits how you (on average) ship out your packages.<br><em>This selection only applies to First Class Mail Letters and First Class Mail International Letters.</em><br>',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_select_option([\'Letters\', \'Flats\', \'Cards\'], ',
            'date_added' => 'now()'
        ),


        /**
         * Shipping Methods
         * Since the modules are now including the weights for min/max again, we need to make sure that those fields now have
         * minimum and maximum weights in kilograms where available.
         *
         */
/*
        if (defined('SHIPPING_WEIGHT_UNITS') && SHIPPING_WEIGHT_UNITS === 'kgs') {
            $this->addConfigurationKey('MODULE_SHIPPING_USPS_TYPES', [
                'configuration_title' => 'Shipping Methods (Domestic and International)',
                'configuration_value' => '0, 0.0992233, 0.00, 0, 31.7514, 0.00, 0, 9.0718, 0.00, 0, 31.7514, 0.00, 0, 11.3398, 0.00, 0, 11.3398, 0.00, 0, 11.3398, 0.00, 0, 11.3398, 0.00, 0, 31.7514, 0.00, 0, 9.0718, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 31.7514, 0.00, 0, 0.4534228, 0.00, 0, 1.8143, 0.00, 0, 31.7514, 0.00, 0, 1.8143, 0.00, 0, 1.8143, 0.00, 0, 1.8143, 0.00, 0, 1.8143, 0.00, 0, 9.0718, 0.00, 0, 9.0718, 0.00, 0, 31.7514, 0.00, 0, 1.8143, 0.00, 0, 1.8143, 0.00, 0, 1.8143, 0.00',
                'configuration_description' => 'Choose the services that you want to offer to your customers.<br><br><strong>Checkbox:</strong> Select the services to be offered. (Can also click on the service name in certain browsers.)<br><br><strong>Min/Max</strong> Choose a custom minimum/maximum for the selected service. If the cart as a whole (the items plus any tare settings) fail to make weight, the method will be skipped. Keep in mind that each service also has its own maximums that will be controlled regardless of what was set here. (Example: entering 5 lbs for International First-Class Mail will be ignored since the International First-Class Mail has a hard limit of 4 lbs.)<br><br><strong>Handling:</strong> A handling charge for that particular method (will be added on to the quote plus any services charges that are applicable).<br><br>USPS returns methods based on cart weights. Enter the weights in your site\'s configured standard. (The cart will handle conversions as necessary.)',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'set_function' => 'zen_cfg_uspsr_services([\'First-Class Mail Letter\',\'USPS Ground Advantage\', \'USPS Ground Advantage Cubic\', \'Media Mail\', \'Connect Local Machinable DDU\', \'Connect Local Machinable DDU Flat Rate Box\', \'Connect Local Machinable DDU Small Flat Rate Bag\', \'Connect Local Machinable DDU Large Flat Rate Bag\', \'Priority Mail\', \'Priority Mail Cubic\', \'Priority Mail Flat Rate Envelope\', \'Priority Mail Padded Flat Rate Envelope\', \'Priority Mail Legal Flat Rate Envelope\', \'Priority Mail Small Flat Rate Box\', \'Priority Mail Medium Flat Rate Box\', \'Priority Mail Large Flat Rate Box\', \'Priority Mail Large Flat Rate APO/FPO/DPO\', \'Priority Mail Express\', \'Priority Mail Express Flat Rate Envelope\', \'Priority Mail Express Padded Flat Rate Envelope\', \'Priority Mail Express Legal Flat Rate Envelope\', \'First-Class Mail International Letter\', \'First-Class Package International Service Machinable ISC Single-piece\', \'Priority Mail International ISC Single-piece\', \'Priority Mail International ISC Flat Rate Envelope\', \'Priority Mail International Machinable ISC Padded Flat Rate Envelope\', \'Priority Mail International ISC Legal Flat Rate Envelope\', \'Priority Mail International Machinable ISC Small Flat Rate Box\', \'Priority Mail International Machinable ISC Medium Flat Rate Box\', \'Priority Mail International Machinable ISC Large Flat Rate Box\', \'Priority Mail Express International ISC Single-piece\', \'Priority Mail Express International ISC Flat Rate Envelope\', \'Priority Mail Express International ISC Legal Flat Rate Envelope\', \'Priority Mail Express International ISC Padded Flat Rate Envelope\'], ',
                'use_function' => 'zen_cfg_uspsr_showservices',
                'date_added' => 'now()'
            ]);
        } else {
*/
            $this->getModuleKeyTrunk().'_TYPES' => array(
                'configuration_title' => 'Shipping Methods (Domestic and International)',
                'configuration_value' => '0, 0.21875, 0.00, 0, 70, 0.00, 0, 20, 0.00, 0, 70, 0.00, 0, 25, 0.00, 0, 25, 0.00, 0, 25, 0.00, 0, 25, 0.00, 0, 70, 0.00, 0, 20, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 70, 0.00, 0, 0.999625, 0.00, 0, 4, 0.00, 0, 70, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 20, 0.00, 0, 20, 0.00, 0, 70, 0.00, 0, 4, 0.00, 0, 4, 0.00, 0, 4, 0.00',
                'configuration_description' => 'Choose the services that you want to offer to your customers.<br><br><strong>Checkbox:</strong> Select the services to be offered (Can also click on the service name in certain browsers.)<br><br><strong>Min/Max</strong> Choose a custom minimum/maximum for the selected service. If the cart as a whole (the items plus any tare settings) fail to make weight, the method will be skipped. Keep in mind that each service also has its own maximums that will be controlled regardless of what was set here. (Example: entering 5 lbs for International First-Class Mail will be ignored since the International First-Class Mail has a hard limit of 4 lbs.)<br><br><strong>Handling:</strong> A handling charge for that particular method (will be added on to the quote plus any services charges that are applicable).<br><br>USPS returns methods based on cart weights. Enter the weights in your site\'s configured standard. (The cart will handle conversions as necessary.)',
                'configuration_group_id' => 6,
                'sort_order' => 0,
                'set_function' => 'zen_cfg_uspsr_services([\'First-Class Mail Letter\',\'USPS Ground Advantage\', \'USPS Ground Advantage Cubic\', \'Media Mail\', \'Connect Local Machinable DDU\', \'Connect Local Machinable DDU Flat Rate Box\', \'Connect Local Machinable DDU Small Flat Rate Bag\', \'Connect Local Machinable DDU Large Flat Rate Bag\', \'Priority Mail\', \'Priority Mail Cubic\', \'Priority Mail Flat Rate Envelope\', \'Priority Mail Padded Flat Rate Envelope\', \'Priority Mail Legal Flat Rate Envelope\', \'Priority Mail Small Flat Rate Box\', \'Priority Mail Medium Flat Rate Box\', \'Priority Mail Large Flat Rate Box\', \'Priority Mail Large Flat Rate APO/FPO/DPO\', \'Priority Mail Express\', \'Priority Mail Express Flat Rate Envelope\', \'Priority Mail Express Padded Flat Rate Envelope\', \'Priority Mail Express Legal Flat Rate Envelope\', \'First-Class Mail International Letter\', \'First-Class Package International Service Machinable ISC Single-piece\', \'Priority Mail International ISC Single-piece\', \'Priority Mail International ISC Flat Rate Envelope\', \'Priority Mail International Machinable ISC Padded Flat Rate Envelope\', \'Priority Mail International ISC Legal Flat Rate Envelope\', \'Priority Mail International Machinable ISC Small Flat Rate Box\', \'Priority Mail International Machinable ISC Medium Flat Rate Box\', \'Priority Mail International Machinable ISC Large Flat Rate Box\', \'Priority Mail Express International ISC Single-piece\', \'Priority Mail Express International ISC Flat Rate Envelope\', \'Priority Mail Express International ISC Legal Flat Rate Envelope\', \'Priority Mail Express International ISC Padded Flat Rate Envelope\'], ',
                'use_function' => 'zen_cfg_uspsr_showservices',
                'date_added' => 'now()'
            ),
  //      }

        $this->getModuleKeyTrunk().'_MEDIA_MAIL_EXCLUDE' => array(
            'configuration_title' => 'Categories to Excluded from Media Mail',
            'configuration_value' => '',
            'configuration_description' => 'Enter the Category ID of the categories (separated by commas, white spaces surrounding the comma are OK) that fail Media Mail standards.<br><br>During checkout, if a product matches a category listed here, it will cause that entire order to be disqualified from Media Mail.<br>',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'use_function' => 'uspsr_get_categories',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_CONNECT_LOCAL_ZIP' => array(
            'configuration_title' => 'Zip Codes Allowed for USPS Connect Local',
            'configuration_value' => '',
            'configuration_description' => 'Enter the list of zip codes (only the five digit part, separated by commas) of the zip codes that can be offered any of the USPS Connect Local options.',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'use_function' => 'uspsr_get_connect_zipcodes',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_DMST_SERVICES' => array(
            'configuration_title' => 'Shipping Add-ons (Domestic Packages)',
            'configuration_value' => '',
            'configuration_description' => 'Pick which add-ons you wish to offer as a part of the shipping cost quote for domestic packages. (The USPS API will do the math as necessary.)<br><br><strong>CAUTION:</strong> Not all options apply to all services.<br>',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_uspsr_extraservices(\'domestic\', ',
            'use_function' => 'zen_cfg_uspsr_extraservices_display',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_INTL_SERVICES' => array(
            'configuration_title' => 'Shipping Add-ons (International Packages)',
            'configuration_value' => '',
            'configuration_description' => 'Pick which add-ons you wish to offer as a part of the shipping cost quote for international packages. (The USPS API will do the math as necessary.)<br><br><strong>CAUTION:</strong> Not all options apply to all services.<br>',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_uspsr_extraservices(\'international\', ',
            'use_function' => 'zen_cfg_uspsr_extraservices_display',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_DMST_LETTER_SERVICES' => array(
            'configuration_title' => 'Shipping Add-ons (Domestic Letters)',
            'configuration_value' => '',
            'configuration_description' => 'Pick which add-ons you wish to offer as a part of the shipping cost quote for domestic letters (First Class Mail Letters). (The USPS API will do the math as necessary.)<br>',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_uspsr_extraservices(\'domestic-letters\', ',
            'use_function' => 'zen_cfg_uspsr_extraservices_display',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_INTL_LETTER_SERVICES' => array(
            'configuration_title' => 'Shipping Add-ons (International Letters)',
            'configuration_value' => '',
            'configuration_description' => 'Pick which add-ons you wish to offer as a part of the shipping cost quote for international letters (First Class International Letters). (The USPS API will do the math as necessary.)<br>',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_uspsr_extraservices(\'intl-letters\', ',
            'use_function' => 'zen_cfg_uspsr_extraservices_display',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_LTR_MACHINEABLE_FLAGS' => array(
            'configuration_title' => 'Machineability Flags (First-Class Mail Letter)',
            'configuration_value' => '--none--',
            'configuration_description' => 'When sending items via USPS First-Class Mail, check below if any applies to the typical method of how you send your orders.<br><br>- <em>Polybagged</em>: Is the letter/flat/card polybagged, polywrapped, enclosed in any plastic material, or has an exterior surface made of a material that is not paper. Windows in envelopes made of paper do not make mailpieces nonmachinable. Attachments allowable under applicable eligibility standards do not make mailpieces nonmachinable.<br><br>- <em>ClosureDevices</em>: Does the letter/flat/card have clasps, strings, buttons, or similar closure devices?<br><br>- <em>LooseItems</em>: Does the letter/flat/card contain items such as pens, pencils, keys, or coins that cause the thickness of the mailpiece to be uneven; or loose keys or coins or similar objects not affixed to the contents within the mailpiece. Loose items may cause a letter to be nonmailable when mailed in paper envelopes.<br><br>- <em>Rigid</em>: Is the letter/flat/card too rigid?<br><br>- <em>SelfMailer</em>: Is your item a folded self-mailer?<br><br>- <em>Booklet</em>: Is the letter/flat/card a booklet?',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_select_multioption([\'Polybagged\', \'ClosureDevices\', \'LooseItems\', \'Rigid\', \'SelfMailer\', \'Booklet\'], ',
            'use_function' => '',
            'date_added' => 'now()'
        ),
        $this->getModuleKeyTrunk().'_PRICING' => array(
            'configuration_title' => 'Pricing Levels',
            'configuration_value' => 'Retail',
            'configuration_description' => 'What pricing level do you want to display to the customer?<br><br><em>Retail</em> - This is the price as if you went to the counter at the post office to buy the postage for your package.<br><br><em>Commercial</em> - This is the price you would pay if you\'re buying the label online via an authorized USPS reseller or through USPS Click-N-Ship on a Business account.<br><br><em>Contract</em> - If you have a negotiated service agreement or some other kind of contract with the USPS, select Contract. Then be sure to specify what kind of contract and the contract number you have in the appropriate options below.',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_select_option([\'Retail\', \'Commercial\', \'Contract\'], ',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_CONTRACT_TYPE' => array(
            'configuration_title' => 'NSA Contract Type',
            'configuration_value' => 'None',
            'configuration_description' => 'What kind of payment account do you have with the US Postal Service?<br><br><em>EPS</em> - Enterprise Payment System<br><br><em>Permit</em> - If you have a Mailing Permit whcih would entitle you a special discount on postage pricing, choose this option.<br><br><em>Meter</em> - If you have a licensed postage meter that grants you a special discount with the USPS, choose this option.',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_select_option([\'None\', \'EPS\', \'Permit\', \'Meter\'], ',
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_ACCT_NUMBER' => array(
            'configuration_title' => 'USPS Account Number',
            'configuration_value' => '',
            'configuration_description' => 'What is the associated EPS Account Number or Meter Number you have with the United States Postal Service. (Leave blank if none.)',
            'configuration_group_id' => 6,
            'use_function' => 'zen_cfg_uspsr_account_display',
            'sort_order' => 0,
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_DISPATCH_CART_TOTAL' => array(
            'configuration_title' => 'Send cart total as part of quote?',
            'configuration_value' => 'Yes',
            'configuration_description' => 'As part of the quoting process, you can send the customer\'s order total to the USPS API for it to calculate Insurance and eligibility for international shipping. (The USPS puts a limit on how much merchandise can be sent to certain countries and by certain methods.) If you choose "No", the module will send a cart value of $5 to be processed.<br><br><strong>CAUTION:</strong> If you don\'t send the total, your customer will not receive accurate price details from the USPS and you may end up paying more for the actual postage.',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_select_option([\'Yes\', \'No\'], ',
            'date_added' => 'now()'
        ),
        $this->getModuleKeyTrunk().'_DEBUG_MODE' => array(
            'configuration_title' => 'Debug Mode',
            'configuration_value' => '--none--',
            'configuration_description' => 'Would you like to enable debug modes?<br><br><em>"Generate Logs"</em> - This module will generate log files for each and every call to the USPS API Server (including the admin side viability check).<br><br>"<em>Display errors</em>" - If set, this means that any API errors that are caught will be displayed in the storefront.<br><br><em>CAUTION:</em> Each log file can be as big as 300KB in size.',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'set_function' => 'zen_cfg_select_multioption([\'Generate Logs\', \'Show Errors\'], ',
            'date_added' => 'now()'
        ),
        $this->getModuleKeyTrunk().'_BEARER_TOKEN' => array(
            'configuration_title' => 'USPS Active Bearer Token',
            'configuration_value' => '',
            'configuration_description' => '<strong>FOR INTERNAL USE ONLY:</strong> The active Bearer Token used to authenticate API requests to the USPS API server. (Leave blank to have the module generate a new token as needed.)',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_BEARER_TOKEN_EXPIRATION' => array(
            'configuration_title' => 'USPS Active Bearer Token Expiration',
            'configuration_value' => '',
            'configuration_description' => '<strong>FOR INTERNAL USE ONLY:</strong> The expiration time of the active Bearer Token used to authenticate API requests to the USPS API server.',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_REFRESH_TOKEN' => array(
            'configuration_title' => 'USPS Refresh Token',
            'configuration_value' => '',
            'configuration_description' => '<strong>FOR INTERNAL USE ONLY:</strong> The active Bearer Token used to authenticate API requests to the USPS API server. (Leave blank to have the module generate a new token as needed.)',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'date_added' => 'now()'
        ),

        $this->getModuleKeyTrunk().'_REFRESH_TOKEN_EXPIRATION' => array(
            'configuration_title' => 'USPS Refresh Token Expiration',
            'configuration_value' => '',
            'configuration_description' => '<strong>FOR INTERNAL USE ONLY:</strong> The expiration time of the active Bearer Token used to authenticate API requests to the USPS API server.',
            'configuration_group_id' => 6,
            'sort_order' => 0,
            'date_added' => 'now()'
		)

		) );

		return $ret;
    }

    /**
     * If debug-logging is enabled, write the requested message to the log-file determined in this
     * module's class-constructor.
     */
    protected function uspsrDebug($message)
    {
        if ($this->debug_enabled === true && (strpos(MODULE_SHIPPING_USPS_DEBUG_MODE, "Logs") !== FALSE)) {
            error_log(date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, 3, $this->debug_filename);
        }
    }

    protected function adminInitializationChecks()
    {
        /**
         * Is there an upgrade available?
         *
         * Make a call into the ZenCart Module DB and compare the returned result versus the number.
         * Don't make this call if the current version is v0.0.0. (There will always be a "better" version than v0.0.0)
         */
        $check_for_new_version = plugin_version_check_for_updates(self::ZEN_CART_PLUGIN_ID, MODULE_SHIPPING_USPS_VERSION);

        if (MODULE_SHIPPING_USPS_VERSION !== "v0.0.0" && $check_for_new_version) {
            $messageStack->add_session(MODULE_SHIPPING_USPS_UPGRADE_AVAILABLE, 'caution');
        }

        /**
         * Are you using 0.0.0? Proceed at your own risk.
         * Starting in 1.5.0, -rc version will designate the release as a release candidate... Should be prepared for stuff to not work.
         */
        if (self::USPSR_CURRENT_VERSION === "v0.0.0" || strpos(self::USPSR_CURRENT_VERSION, "-rc") !== FALSE)
            // If this version is v0.0.0 or contains -dev, this is a developmental release. Proceed with caution.
            $messageStack->add_session(MODULE_SHIPPING_USPS_DEVELOPMENTAL, 'warning');
    }

    /**
     * Common storefront/admin configuration checking.  Called from adminInitializationChecks
     * and storefrontInitialization.  Will auto-disable the shipping method if either no services
     * have been selected or the country-of-origin is not the US.
     */
    protected function checkConfiguration()
    {
        global $messageStack;

        // Need to have at least one method enabled
        $usps_shipping_methods_cnt = 0;
        foreach ($this->typeCheckboxesSelected as $requested_type) {
            if (is_numeric($requested_type)) {
                continue;
            }
            $usps_shipping_methods_cnt++;
        }

        if ($usps_shipping_methods_cnt === 0) {
            $this->enabled = false;
            if (IS_ADMIN_FLAG === true) {
                $messageStack->add_session(MODULE_SHIPPING_USPS_ERROR_NO_QUOTES, 'error');
            }
        }

        // If the Origin Zip Code fails validation... stop.
        if (!uspsr_validate_zipcode(SHIPPING_ORIGIN_ZIP)) {
            $this->enabled = false;
            if (IS_ADMIN_FLAG === true) {
                $messageStack->add_session(MODULE_SHIPPING_USPS_ERROR_BAD_ORIGIN_ZIPCODE, 'error');
            }
        }

        // If the origin country isn't the United States, you can't use USPS (APO/DPO/FPO counts as United States)... stop.
        if (SHIPPING_ORIGIN_COUNTRY !== '223') {
            $this->enabled = false;
            if (IS_ADMIN_FLAG === true) {
                $messageStack->add_session(MODULE_SHIPPING_USPS_ERROR_BAD_ORIGIN_COUNTRY, 'error');
            }
        }

        // If either the API Key or Secret are blank, stop, you can't use USPS
        if (!zen_not_null(MODULE_SHIPPING_USPS_API_KEY) || !zen_not_null(MODULE_SHIPPING_USPS_API_SECRET)) {
            $this->enabled = false;
            if (IS_ADMIN_FLAG === true) {
                $messageStack->add_session(MODULE_SHIPPING_USPS_ERROR_BAD_CREDENTIALS, 'error');
            }
        }

        // If either the API Key or Secret are duds, stop, you can't use USPS... you didn't provide proper access credentials.
        if ((strtolower(MODULE_SHIPPING_USPS_API_KEY) == 'none') || strtolower(MODULE_SHIPPING_USPS_API_SECRET) == 'none') {
            $this->enabled = false;
            if (IS_ADMIN_FLAG === true) {
                $messageStack->add_session(MODULE_SHIPPING_USPS_ERROR_NO_CREDENTIALS, 'error');
            }
        }

        // If the Contract option is selected but either the Contract Type is set to None OR the Account Number is blank, stop.
        if (MODULE_SHIPPING_USPS_PRICING == 'Contract' && (MODULE_SHIPPING_USPS_CONTRACT_TYPE == 'None' || !zen_not_null(MODULE_SHIPPING_USPS_ACCT_NUMBER))) {
            $this->enabled = false;
            if (IS_ADMIN_FLAG === true) {
                $messageStack->add_session(MODULE_SHIPPING_USPS_ERROR_NO_CONTRACT, 'error');
            }
        }

        // If the module is NOT able to get a Bearer Token, disable the module. Something is wrong.
        if (((strtolower(MODULE_SHIPPING_USPS_API_KEY) != 'none') && (strtolower(MODULE_SHIPPING_USPS_API_KEY)) != 'none') && !zen_not_null($this->bearerToken)) {
            $this->enabled = false;
            if (IS_ADMIN_FLAG === true) {
                $messageStack->add_session(MODULE_SHIPPING_USPS_ERROR_REJECTED_CREDENTIALS, 'error');
            }

        }

        return $this->enabled;
    }

    protected function _getQuote( $pShipHash )
    {
        /**
         * Build array of shipping values
         */
        
        // Check if the measurement setting exists and if it does, check that it's in inches.
        // If it doesn't or if it is set to inches, do nothing.
        if (defined('SHIPPING_DIMENSION_UNITS') && SHIPPING_DIMENSION_UNITS !== "inches") {
            foreach ($this->dimensions as &$dimmension) {
                $dimmension = (float) $dimmension / 2.54;
            }
        }

        // Sort out each of the dimmensions as necessary.

        /**
         * Build the JSON Call to the server
         */
        // Prepare a Standards Query
        $standards_query = [];

        // Prepare a Packages Query
        $pkg_body = [];

        // Prepare a Letters Query
        $ltr_body = [];

		// US Domestic destinations
		$destCountryCode = $this->verifyCountryCode( $pShipHash['destination']['countries_iso_code_2'] );
		if( $destCountryCode == 'US' ) {

            // There are only three classes needed: Ground Advantage, Priority Mail, Priority Mail Express
            $mailClasses = [
                "USPS_GROUND_ADVANTAGE",
                "PRIORITY_MAIL",
                "PRIORITY_MAIL_EXPRESS"
            ];

            /**
                * Is this package going to a APO/FPO/DPO?
            */
            $this->is_apo_dest = in_array(uspsr_validate_zipcode($pShipHash['destination']['postcode']), self::USPSR_MILITARY_MAIL_ZIP);

            /**
                * Check to see if the products in the cart are ALL eligible for USPS Media Mail.
            */
            if ($this->enable_media_mail) {
                $mailClasses[] = "MEDIA_MAIL";
            }

            // Check to see if the order fits for USPS Connect Local
            if (uspsr_check_connect_local($pShipHash['destination']['postcode']))
                $mailClasses[] = "USPS_CONNECT_LOCAL";

            $destination_zip = uspsr_validate_zipcode($pShipHash['destination']['postcode']);

            // Package Request Body
            $pkg_body = [
                'originZIPCode' => uspsr_validate_zipcode(SHIPPING_ORIGIN_ZIP),
                'destinationZIPCode' => $destination_zip,
                'weight' => $pShipHash['shipping_weight_total'],
                'length' => $this->dimensions['pkg_length'],
                'width' => $this->dimensions['pkg_width'],
                'height' => $this->dimensions['pkg_height'],
                'mailClasses' => $mailClasses,
                'priceType' => strtoupper(MODULE_SHIPPING_USPS_PRICING),
                'itemValue' => (MODULE_SHIPPING_USPS_DISPATCH_CART_TOTAL == "Yes" ? $this->shipment_value : 5),
            ];

            // USPS Letters Services (still need to be attached to the Letter Request, Packages will be processed separately)
            $services_ltr_dmst = array_filter(explode(', ', MODULE_SHIPPING_USPS_DMST_LETTER_SERVICES));
            $services_ltr_intl = array_filter(explode(', ', MODULE_SHIPPING_USPS_INTL_LETTER_SERVICES));
            
            if (in_array(930, $services_ltr_dmst)) {
                $services_ltr_dmst[] = 931;
            }

            if (in_array(930, $services_ltr_intl)) {
                $services_ltr_intl[] = 931;
            }

            $services_ltr_dmst = array_values(array_filter(array_map('intval', $services_ltr_dmst), function ($service) {
                return $service > 0; // Keep only positive integers
            }));

            $services_ltr_intl = array_values(array_filter(array_map('intval', $services_ltr_intl), function ($service) {
                return $service > 0; // Keep only positive integers
            }));

            $services_ltr = $this->is_us_shipment ? $services_ltr_dmst : $services_ltr_intl;


            // Letter Request Body
            $ltr_body = [
                "weight" => $pShipHash['shipping_weight_total'] * 16, // The cart weight is in pounds, the letters API takes the request in ounces
                "length" => $this->dimensions['ltr_length'],
                "height" => $this->dimensions['ltr_height'],
                "thickness" => $this->dimensions['ltr_thickness'],
                "processingCategory" => strtoupper(MODULE_SHIPPING_USPS_LTR_PROCESSING),
                "nonMachinableIndicators" =>
                [
                    "isPolybagged" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "Polybagged") !== false,
                    "hasClosureDevices" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "ClosureDevices") !== false,
                    "hasLooseItems" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "LooseItems") !== false,
                    "isRigid" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "Rigid") !== false,
                    "isSelfMailer" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "SelfMailer") !== false,
                    "isBooklet" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "Booklet") !== false,
                ],
                "extraServices" => $services_ltr,
                "itemValue" => (MODULE_SHIPPING_USPS_DISPATCH_CART_TOTAL == "Yes" ? $this->shipment_value : 5),
            ];

            // Let's make a standards request now.
            $standards_query = [
                'originZIPCode' => $pShipHash['origin']['postcode'],
                'destinationZIPCode' => $destination_zip,
                'mailClass' => 'ALL',
                'weight' => $pShipHash['shipping_weight_total']
            ];

            $todays_date = new DateTime();
            $daystoadd = (int)$this->getModuleConfigValue( '_HANDLING_TIME', 0 );

            $todays_date_plus = $todays_date->modify("+{$daystoadd} days");
            $standards_query['acceptanceDate'] = $todays_date_plus->format('Y-m-d');

            $street_address = trim( $pShipHash['destination']['street_address'] );

            // If the address contains "PO BOX" or "BOX" in the address line 1, that makes it a PO BOX.
            if (preg_match("/^(PO BOX|BOX)/i", $street_address)) {
                $standards_query['destinationType'] = "PO_BOX";
            } else {
                $standards_query['destinationType'] = "STREET";
            }

            // If the Pricing is Contract, add the Contract Type and AccountNumber
            if (MODULE_SHIPPING_USPS_PRICING == 'Contract') {
                $pkg_body['accountType'] = $ltr_body['accountType'] = MODULE_SHIPPING_USPS_CONTRACT_TYPE;
                $pkg_body['accountNumber'] = $ltr_body['accountNumber'] = MODULE_SHIPPING_USPS_ACCT_NUMBER;
            }

            // Send pkg_body to make pkgQuote.
            $this->pkgQuote = $this->_makeQuotesCall($pkg_body, 'package-domestic');
            $this->ltrQuote = $this->_makeQuotesCall($ltr_body, 'letters-domestic');

        } else { // It's not going to the US, so it's international

            $pkg_body = [
                "originZIPCode" => uspsr_validate_zipcode(SHIPPING_ORIGIN_ZIP),
                "foreignPostalCode" => $pShipHash['destination']['postcode'],
                "destinationCountryCode" => $pShipHash['destination']['countries_iso_code_2'],
                "weight" => $shipping_weight,
                'length' => $this->dimensions['pkg_length'],
                'width' => $this->dimensions['pkg_width'],
                'height' => $this->dimensions['pkg_height'],
                "priceType" => strtoupper(MODULE_SHIPPING_USPS_PRICING),
                "mailClass" => "ALL", // Do not change this. There is no "mailClasses" on the International API, so we have to pull all of them.
                'itemValue' => (MODULE_SHIPPING_USPS_DISPATCH_CART_TOTAL == "Yes" ? $this->shipment_value : 5),
            ];

            // Letter Request Body
            $ltr_body = [
                "weight" => $shipping_weight,
                "length" => $this->dimensions['ltr_length'],
                "height" => $this->dimensions['ltr_height'],
                "thickness" => $this->dimensions['ltr_thickness'],
                "processingCategory" => strtoupper(MODULE_SHIPPING_USPS_LTR_PROCESSING),
                "nonMachinableIndicators" =>
                [
                    "isPolybagged" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "Polybagged") !== false,
                    "hasClosureDevices" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "ClosureDevices") !== false,
                    "hasLooseItems" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "LooseItems") !== false,
                    "isRigid" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "Rigid") !== false,
                    "isSelfMailer" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "SelfMailer") !== false,
                    "isBooklet" => strpos(MODULE_SHIPPING_USPS_LTR_MACHINEABLE_FLAGS, "Booklet") !== false,
                ],
                "itemValue" => (MODULE_SHIPPING_USPS_DISPATCH_CART_TOTAL == "Yes" ? $this->shipment_value : 5),
                "destinationCountryCode" => $pShipHash['destination']['countries_iso_code_2'],
            ];

            // If the Pricing is Contract, add the Contract Type and AccountNumber
            if (MODULE_SHIPPING_USPS_PRICING == 'Contract') {
                $pkg_body['accountType'] = $ltr_body['accountType'] = MODULE_SHIPPING_USPS_CONTRACT_TYPE;
                $pkg_body['accountNumber'] = $ltr_body['accountNumber'] = MODULE_SHIPPING_USPS_ACCT_NUMBER;
            }


            // Send pkg_body to make pkgQuote.
            $this->pkgQuote = $this->_makeQuotesCall($pkg_body, 'package-intl');
            $this->ltrQuote = $this->_makeQuotesCall($ltr_body, 'letters-intl');

        }

        // Okay we have our request body ready.

        // Are we looking up the time frames? If not, don't send the request for Standards
        if (defined('MODULE_SHIPPING_USPS_DISPLAY_TRANSIT') && MODULE_SHIPPING_USPS_DISPLAY_TRANSIT !== 'No' && $this->is_us_shipment) {
            $standards_response = json_decode($this->_makeStandardsCall($standards_query), TRUE);
            
            if (is_array($standards_response)) {
                foreach ($standards_response as $item) {
                    if (is_array($item) && isset($item['mailClass'])) {
                        $this->uspsStandards[$item['mailClass']] = $item;
                    }
                }
            }

        }
    }

    protected function _makeStandardsCall($query)
    {
        global $request_type;

        /**
         * cURL Call to USPS server.
         *
         * There is only one server, the production, to reach.
         * We need to figure out are we calling the Domestic (US) or International API
         *
         * That will be handled by the $method parameter
         *
         */

        $paramsBuild = '';
        $paramsBuild = http_build_query($query);

        $ch = curl_init();
        $curl_options = [
            CURLOPT_URL => $this->api_base . 'service-standards/v3/estimates?' . $paramsBuild,
            CURLOPT_REFERER => ($request_type == 'SSL') ? (HTTPS_SERVER . DIR_WS_HTTPS_CATALOG) : (HTTP_SERVER . DIR_WS_CATALOG),
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $this->bearerToken],
            CURLOPT_VERBOSE => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'ZenCart v' . PROJECT_VERSION_MAJOR . "." . PROJECT_VERSION_MINOR . " + USPSr Module " . MODULE_SHIPPING_USPS_VERSION,
        ];

        if (CURL_PROXY_REQUIRED === 'True') {
            $curl_options[CURLOPT_HTTPPROXYTUNNEL] = !defined('CURL_PROXY_TUNNEL_FLAG') || strtoupper(CURL_PROXY_TUNNEL_FLAG) !== 'FALSE';
            $curl_options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
            $curl_options[CURLOPT_PROXY] = CURL_PROXY_SERVER_DETAILS;
        }
        curl_setopt_array($ch, $curl_options);

        // -----
        // Log the starting time of the to-be-sent USPS request.
        //
        $message = '';
        $message .= "\n" . '====================================================' . "\n";
        $message .= 'Sending Standards request to USPS' . "\n";
        $message .= "Standards Build: " . "\n";
        $message .= print_r($query, TRUE) . "\n";

        $this->uspsrDebug($message);

        // -----
        // Submit the request to USPS via CURL.
        //
        $body = curl_exec($ch);
        $this->commError = curl_error($ch);
        $this->commErrNo = curl_errno($ch);
        $this->commInfo = curl_getinfo($ch);

        // done with CURL, so close connection
        // Deprecated in PHP 8.0
        if (PHP_VERSION_ID < 80000) curl_close($ch);
        

        // -----
        // Log the CURL response (will also capture the time the response was received) to capture any
        // CURL-related errors and the shipping-methods being requested.  If a CURL error was returned,
        // no JSON is returned in the response (aka $body).
        //
        //if communication error, return -1 because no quotes were found, and user doesn't need to see the actual error message (set DEBUG mode to get the messages logged instead)
        if ($this->commErrNo != 0) {
            return -1;
        }
        // EOF CURL

        // -----
        // A valid JSON response was received from USPS, log the information to the debug-output file.
        //
        $this->quoteLogJSONResponse($body);

        return $body;
    }

    protected function quoteLogCurlBody($request)
    {
        global $order;

        if ($this->debug_enabled === false) {
            return;
        }

        // The response should be formatted in JSON.... so, should we pretty print that?
        $message =
            "\n" . '==================================' . "\n\n" .
            'REQUEST FROM STORE:' . "\n\n" . uspsr_pretty_json_print($request) . "\n\n";
        $message .= "\n" . '---------------------------------' . "\n";
        $message .= 'CommErr (should be 0): ' . $this->commErrNo . ' - ' . $this->commError . "\n\n";

        $message .= '==================================' . "\n\n" . 'USPS Country - $order->delivery[country][iso_code_2]: ' . $pShipHash['destination']['countries_iso_code_2'] . "\n";

        $this->uspsrDebug($message);
    }

    protected function quoteLogCurlResponse($request)
    {
        global $order;

        if ($this->debug_enabled === false) {
            return;
        }

        // The response should be formatted in JSON.... so, should we pretty print that?
        $message =
            "\n" . '==================================' . "\n\n" .
            'TOKEN RESPONSE FROM USPS:' . "\n\n" . uspsr_pretty_json_print($request) . "\n\n";
        $message .= "\n" . '---------------------------------' . "\n";
        $message .= 'CommErr (should be 0): ' . $this->commErrNo . ' - ' . $this->commError . "\n\n";

        $message .= '==================================' . "\n\n" . (isset($order) ? 'USPS Country - $order->delivery[country][iso_code_2]: ' . $pShipHash['destination']['countries_iso_code_2'] : '') . "\n";


        $this->uspsrDebug($message);
    }

    protected function quoteLogJSONResponse($response)
    {
        if ($this->debug_enabled === false) {
            return;
        }

        $message = "\n" . '==================================' . "\n";
        $message .= "RAW JSON FROM USPS:\n\n" . uspsr_pretty_json_print($response) . "\n\n";

        $this->uspsrDebug($message);
    }

    protected function checkToken($expiration_time)
    {
        // Check to see if the Bearer Token is still valid.

        return time() <= ((int)$expiration_time);
    }
    
    protected function getBearerToken()
    {

        $call_body = [
            'client_id'     => MODULE_SHIPPING_USPS_API_KEY,
            'client_secret' => MODULE_SHIPPING_USPS_API_SECRET,
            'scope'       => 'domestic-prices addresses international-prices service-standards international-service-standard shipments'
        ];
        
        // If there is a refresh token, check to see if it's still valid.
        if (defined('MODULE_SHIPPING_USPS_REFRESH_TOKEN') && zen_not_null(MODULE_SHIPPING_USPS_REFRESH_TOKEN) && $this->checkToken(MODULE_SHIPPING_USPS_REFRESH_TOKEN_EXPIRATION)) {
            
            // It's valid, so use it to get a refreshed bearer token.
            $call_body['grant_type'] = 'refresh_token';
            $call_body['refresh_token'] = MODULE_SHIPPING_USPS_REFRESH_TOKEN;

        } else {
            // Otherwise, there is no refresh token, so get a new bearer token.
            $call_body['grant_type'] = 'client_credentials';
        }

        $call_body = json_encode($call_body);

        $ch = curl_init();
        $curl_options = [
            CURLOPT_URL => $this->api_base . 'oauth2/v3/token',
            CURLOPT_REFERER => BITCOMMERCE_PKG_URI,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_VERBOSE => 0,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $call_body,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Zen Cart',
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json"
            )
        ];

		if( $this->isCommerceConfigActive( 'CURL_PROXY_REQUIRED' ) ) {
            $curl_options[CURLOPT_HTTPPROXYTUNNEL] = !defined('CURL_PROXY_TUNNEL_FLAG') || strtoupper(CURL_PROXY_TUNNEL_FLAG) !== 'FALSE';
            $curl_options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
            $curl_options[CURLOPT_PROXY] = CURL_PROXY_SERVER_DETAILS;
        }
        curl_setopt_array($ch, $curl_options);

        // -----
        // Log the starting time of the to-be-sent USPS request.
        //
        $message = '';
        $message .= "\n" . 'No token detected, requesting session token from USPS' . "\n";
        $message .= 'Token Request' . "\n" . uspsr_pretty_json_print($call_body) . "\n";

        $this->uspsrDebug($message);
        // -----
        // Submit the request to USPS via CURL.
        //
        $body = curl_exec($ch);
        $this->commError = curl_error($ch);
        $this->commErrNo = curl_errno($ch);
        $this->commInfo = curl_getinfo($ch);

        // done with CURL, so close connection
        // Deprecated in PHP 8.0
        if (PHP_VERSION_ID < 80000) curl_close($ch);

        // -----
        // Log the CURL response (will also capture the time the response was received) to capture any
        // CURL-related errors and the shipping-methods being requested.  If a CURL error was returned,
        // no JSON is returned in the response (aka $body).
        //
        $this->quoteLogCurlResponse($body);

        //if communication error, return -1 because no quotes were found, and user doesn't need to see the actual error message (set DEBUG mode to get the messages logged instead)
        if ($this->commErrNo != 0) {
            return -1;
        }
        // EOF CURL

        // Extract the token and expiration times
        $body = json_decode($body, TRUE);

        if (is_array($body) && array_key_exists('access_token', $body)) {
            global $gCommerceSystem;

            $expiration_time = $body['issued_at'] + ($body['expires_in'] * 1000) - 300000; // Subtract 5 minutes to be safe.
            $expiration_time /= 1000; // Convert to seconds

            $gCommerceSystem->storeConfig('MODULE_SHIPPING_USPS_BEARER_TOKEN', $body['access_token'] );

            $gCommerceSystem->storeConfig('MODULE_SHIPPING_USPS_BEARER_TOKEN_EXPIRATION', (int)$expiration_time );
            
            if (isset($body['refresh_token'])) {
                $gCommerceSystem->storeConfig('MODULE_SHIPPING_USPS_REFRESH_TOKEN', $body['refresh_token'] );

                $refresh_expiration = $body['refresh_token_issued_at'] + ($body['refresh_token_expires_in'] * 1000) - 300000; // Subtract 5 minutes to be safe.
                $refresh_expiration /= 1000; // Convert to seconds

                $gCommerceSystem->storeConfig('MODULE_SHIPPING_USPS_REFRESH_TOKEN_EXPIRATION', (int)$refresh_expiration ); // Refresh tokens last 30 days longer than access tokens
            }

            $this->bearerToken = $body['access_token'];
            $_SESSION['bearer_token'] = $this->bearerToken;
            $this->bearerExpiration = (int)$expiration_time;
        }

        return;
    }

    protected function _makeQuotesCall($call_body, $method)
    {
        global $gBitSystem, $request_type;

        $call_body = json_encode($call_body);
        /**
            * cURL Call to USPS server.
            *
            * We need to figure out are we calling the Domestic (US) or International API
            * That will be handled by the $method parameter
            */

        $usps_calls = [
            'package-domestic' => (string)$this->api_base . 'prices/v3/total-rates/search',
            'letters-domestic' => (string)$this->api_base . 'prices/v3/letter-rates/search',
            'package-intl' => (string)$this->api_base . 'international-prices/v3/total-rates/search',
            'letters-intl' => (string)$this->api_base . 'international-prices/v3/letter-rates/search',
        ];

        $ch = curl_init();
        $curl_options = [
            CURLOPT_URL => $usps_calls[$method],
            CURLOPT_REFERER => BITCOMMERCE_PKG_URI,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $this->bearerToken],
            CURLOPT_VERBOSE => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => BITCOMMERCE_PKG_NAME,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $call_body
        ];

		if( $this->isCommerceConfigActive( 'CURL_PROXY_REQUIRED' ) ) {
            $curl_options[CURLOPT_HTTPPROXYTUNNEL] = !defined('CURL_PROXY_TUNNEL_FLAG') || strtoupper(CURL_PROXY_TUNNEL_FLAG) !== 'FALSE';
            $curl_options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
            $curl_options[CURLOPT_PROXY] = CURL_PROXY_SERVER_DETAILS;
        }
        curl_setopt_array($ch, $curl_options);

        // -----
        // Log the starting time of the to-be-sent USPS request.
        //
        $this->uspsrDebug('Sending ' . $method . ' request to USPS');

        // -----
        // Submit the request to USPS via CURL.
        //
        $body = curl_exec($ch);
        $this->commError = curl_error($ch);
        $this->commErrNo = curl_errno($ch);
        $this->commInfo = curl_getinfo($ch);

        // done with CURL, so close connection
        // Deprecated in PHP 8.0
        if (PHP_VERSION_ID < 80000) curl_close($ch);

        // -----
        // Log the CURL response (will also capture the time the response was received) to capture any
        // CURL-related errors and the shipping-methods being requested.  If a CURL error was returned,
        // no JSON is returned in the response (aka $body).
        //
        $this->quoteLogCurlBody($call_body);

        //if communication error, return -1 because no quotes were found, and user doesn't need to see the actual error message (set DEBUG mode to get the messages logged instead)
        if ($this->commErrNo != 0) {
            return -1;
        }
        // EOF CURL

        // -----
        // A valid JSON response was received from USPS, log the information to the debug-output file.
        //
        $this->quoteLogJSONResponse($body);

        return $body;
    }

    /**
     * This order is going to iterate through the customer's cart, build up a total,
     * and remove any form of digital items (products_virtual)
     *
     * @return void
     */
    protected function _calcCart( $pShipHash )
    {
        global $order, $uninsurable_value;

        $this->enable_media_mail = true;


        // From the original USPS Module
        // -----
        // If the order's tax-value isn't set (like when a quote is requested from
        // the shipping-estimator), set that value to 0 to prevent follow-on PHP
        // notices from this module's quote processing.

        $this->orders_tax = (!isset($order->info['tax'])) ? 0 : $order->info['tax'];
        $this->uninsured_value = (float)number_format((isset($uninsurable_value)) ? (float) $uninsurable_value : 0, 2);
        $this->shipment_value = (float)number_format(($order->info['subtotal'] > 0) ? ($order->info['subtotal'] + $this->orders_tax) : $_SESSION['cart']->total, 2);
        $this->insured_value = $this->shipment_value - $this->uninsured_value;

        // Breakout the category of exemptions for Media Mail
        $key_values = preg_split('/[\s+]/', MODULE_SHIPPING_USPS_MEDIA_MAIL_EXCLUDE);

        // Iterate over all the items in the order. If an item is flagged as products_virtual, that means the whole order is excluded.
        // Additionally deduct the value of the non-shipped item from the shipment_value
        foreach ($order->products as $item) {
            if ($item['products_virtual'] === 1) {
                $this->shipment_value -= $item['final_price'];
                $this->uninsured_value += $item['final_price'];
            }

            if (in_array(zen_get_products_category_id($item['id']), $key_values)) {
                $this->enable_media_mail = false;
            }

        }

        if (!$this->is_us_shipment)
            $this->enable_media_mail = false;
    }

    protected function cleanJSON($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanJSON($value);
            }
        } elseif (is_string($data)) {
            $data = trim($data);
        }

        return $data;
    }

}

function zen_cfg_uspsr_dimmensions($key_value, $key = '')
{
    $key_values = array_filter(explode(', ', $key_value));
    array_walk($key_values, function (&$value) {
        $value = trim($value);
    }); // Quickly remove white space

    // Length
    $domm_length = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_DIMMENSIONS][]', $key_values[0], 'size="10" class="form-control" style="text-align: center;"');
    $intl_length = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_DIMMENSIONS][]', $key_values[1], 'size="10" class="form-control" style="text-align: center;"');

    // Width
    $domm_width = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_DIMMENSIONS][]', $key_values[2], 'size="10" class="form-control" style="text-align: center;"');
    $intl_width = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_DIMMENSIONS][]', $key_values[3], 'size="10" class="form-control" style="text-align: center;"');

    // Height
    $domm_height = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_DIMMENSIONS][]', $key_values[4], 'size="10" class="form-control" style="text-align: center;"');
    $intl_height = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_DIMMENSIONS][]', $key_values[5], 'size="10" class="form-control" style="text-align: center;"');


    $table = <<<EOF
    <style>
        .three-column {display: block;border-collapse: collapse;}
        .three-column-row {display:table-row;}
        .three-column-cell {display:table-cell;}
        .border-div {border-right: 1px #000 solid; padding:5px;}
        .align-center {text-align: center;}
    </style>
    <div class="three-column" style="width: 75%; margin: auto;">
        <div class="three-column-row">
            <div class="three-column-cell" style="width: 24%;">&nbsp;</div>
            <div class="three-column-cell border-div align-center" style="width: 38%;font-weight: bold;">Domestic</div>
            <div class="three-column-cell align-center" style="width: 38%;font-weight: bold;">International</div>
        </div>
        <div class="three-column-row">
            <div class="three-column-cell">Length</div>
            <div class="three-column-cell border-div align-center">$domm_length</div>
            <div class="three-column-cell align-center">$intl_length</div>
        </div>
        <div class="three-column-row">
            <div class="three-column-cell">Width</div>
            <div class="three-column-cell border-div align-center">$domm_width</div>
            <div class="three-column-cell align-center">$intl_width</div>
        </div>
        <div class="three-column-row">
            <div class="three-column-cell">Height</div>
            <div class="three-column-cell border-div align-center">$domm_height</div>
            <div class="three-column-cell align-center">$intl_height</div>
        </div>
    </div>
EOF;

    return $table;
}

function zen_cfg_uspsr_ltr_dimmensions($key_value, $key = '')
{
    $key_values = array_filter(explode(', ', $key_value));
    array_walk($key_values, function (&$value) {
        $value = trim($value);
    }); // Quickly remove white space

    // Length
    $domm_length = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_LTR_DIMMENSIONS][]', $key_values[0], 'size="10" class="form-control" style="text-align: center;"');
    $intl_length = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_LTR_DIMMENSIONS][]', $key_values[1], 'size="10" class="form-control" style="text-align: center;"');

    // Height
    $domm_height = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_LTR_DIMMENSIONS][]', $key_values[2], 'size="10" class="form-control" style="text-align: center;"');
    $intl_height = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_LTR_DIMMENSIONS][]', $key_values[3], 'size="10" class="form-control" style="text-align: center;"');

    // Thickness
    $domm_thickness = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_LTR_DIMMENSIONS][]', $key_values[4], 'size="10" class="form-control" style="text-align: center;"');
    $intl_thickness = zen_draw_input_field('configuration[MODULE_SHIPPING_USPS_LTR_DIMMENSIONS][]', $key_values[5], 'size="10" class="form-control" style="text-align: center;"');


    $table = <<<EOF
    <style>
        .three-column {display: block;border-collapse: collapse;}
        .three-column-row {display:table-row;}
        .three-column-cell {display:table-cell;}
        .border-div {border-right: 1px #000 solid; padding:5px;}
        .align-center {text-align: center;}
    </style>
    <div class="three-column" style="width: 75%; margin: auto;">
        <div class="three-column-row">
            <div class="three-column-cell" style="width: 24%;">&nbsp;</div>
            <div class="three-column-cell border-div align-center" style="width: 38%;font-weight: bold;">Domestic</div>
            <div class="three-column-cell align-center" style="width: 38%;font-weight: bold;">International</div>
        </div>
        <div class="three-column-row">
            <div class="three-column-cell">Length</div>
            <div class="three-column-cell border-div align-center">$domm_length</div>
            <div class="three-column-cell align-center">$intl_length</div>
        </div>
        <div class="three-column-row">
            <div class="three-column-cell">Height</div>
            <div class="three-column-cell border-div align-center">$domm_height</div>
            <div class="three-column-cell align-center">$intl_height</div>
        </div>
        <div class="three-column-row">
            <div class="three-column-cell">Thickness</div>
            <div class="three-column-cell border-div align-center">$domm_thickness</div>
            <div class="three-column-cell align-center">$intl_thickness</div>
        </div>
    </div>
EOF;

    return $table;
}

function zen_cfg_uspsr_services($select_array, $key_value, $key = '')
{
    $key_values = explode(', ', $key_value);
    array_walk($key_values, function (&$value) {
        $value = trim($value);
    }); // Quickly remove extra white space

    $name = ($key) ? ('configuration[' . $key . '][]') : 'configuration_value';



    $w20pxl = 'width:20px;float:left;text-align:center;';
    $w60pxl = 'width:60px;float:left;text-align:center;';
    $frc = 'float:right;text-align:center;';

    $string =
        '<b>' .
        '<div style="' . $w20pxl . '">&nbsp;</div>' .
        '<div style="' . $w60pxl . '">Min</div>' .
        '<div style="' . $w60pxl . '">Max</div>' .
        '<div style="float:left;"></div>' .
        '<div style="' . $frc . '">Handling</div>' .
        '</b>' .
        '<div style="clear:both;"></div>';
    $string_spacing = '<div><br><br><b>&nbsp;International Rates:</b><br></div>' . $string;
    $string_spacing_international = 0;
    $string = '<div><br><b>&nbsp;Domestic Rates:</b><br></div>' . $string;
    for ($i = 0, $n = count($select_array); $i < $n; $i++) {
            $servicename =  trim(preg_replace(
                [
                    '/International/',
                    '/Envelope/',
                    '/ Mail/',
                    '/Large/',
                    '/Medium/',
                    '/Small/',
                    '/First/',
                    '/Legal/',
                    '/Padded/',
                    '/Flat Rate/',
                    '/Express Guaranteed /',
                    '/Package\hService\h-\hRetail/',
                    '/Package Service/',
                    '/ISC/',
                    '/Machinable( DDU)?/',
                    '/(Basic|Single-Piece)/i',
                    '/USPS\s+/',
                    '/Non-Soft Pack Tier 1/',
                ],
                [
                    'Intl',
                    'Env',
                    '',
                    'Lg.',
                    'Md.',
                    'Sm.',
                    '1st',
                    'Leg.',
                    'Pad.',
                    'F/R',
                    'Exp Guar',
                    'Pkgs - Retail',
                    'Pkgs - Comm',
                    '',
                    '',
                    '',
                    '',
                    ''
                ],
                $select_array[$i]
            ));

        $stripped_servicename = str_replace(' ', '', $servicename);
        if (stripos($select_array[$i], 'international') !== false) {
            $string_spacing_international++;
        }
        if ($string_spacing_international === 1) {
            $string .= $string_spacing;
        }

        $string .= '<div id="' . $key . $i . '">';
        $string .=
            '<div style="clear:both;">' .
            zen_draw_checkbox_field($name, $select_array[$i], (in_array($select_array[$i], $key_values) ? 'CHECKED' : ''), 'id="'. $stripped_servicename . '"', $servicename) .
            '</div>';
        if (in_array($select_array[$i], $key_values)) {
            next($key_values);
        }

        $string .= '<div class="input-group"> ' .  zen_draw_input_field($name, current($key_values), 'size="5"') .  '<span class="input-group-addon">to</span>';
        next($key_values);

        $string .= zen_draw_input_field($name, current($key_values), 'size="5"');
        next($key_values);

		global $gCommerceSystem;
		$shippingUnits = $gCommerceSystem->getConfig( 'SHIPPING_WEIGHT_UNITS', 'lbs' );
        $string .= '<span class="input-group-addon">'.$shippingUnits.'</span><span class="input-group-addon">$</span>' .  zen_draw_input_field($name, current($key_values), 'size="4" style="text-align: right;"');
        next($key_values);

        $string .= '</div></div>';
    }
    return $string;
}

function zen_cfg_uspsr_extraservices($destination, $key_value, $key = '')
{
    $key_values = array_filter(explode(', ', $key_value));
    array_walk($key_values, function (&$value) {
        $value = trim($value);
    }); // Quickly remove white space

    $name = ($key) ? ('configuration[' . $key . '][]') : 'configuration_value';

    $output_str = '';

    $focus = 0;

    switch ($destination) {
        case "domestic":
            $focus = 1;
            break;
        case "international":
            $focus = 2;
            break;
        case "domestic-letters":
            $focus = 4;
            break;
        case "intl-letters":
            $focus = 8;
            break;
    }

    // Establish a list of codes.
    // Format: (API Code) => ['Name of Service', Bitfield (0 = Nope, 1 = Domestic Pkg, 2 = International Pkg, 4 = Domestic Letters, 8 = International Letters)]
    $options = [
        910 => ['Certified Mail', 1 + 4],
        930 => ['Insurance', 1 + 2 + 4],
        925 => ['Priority Mail Express Merchandise Insurance', 1],
        923 => ['Adult Signature Restricted Delivery', 1],
        922 => ['Adult Signature Required', 1],
        940 => ['Registered Mail', 1 + 4 + 8],
        915 => ['Collect on Delivery', 1],
        955 => ['Return Receipt', 1 + 4 + 8],
        957 => ['Return Receipt Electronic', 1 + 4],
        921 => ['Signature Confirmation', 1],
        911 => ['Certified Mail Restricted Delivery', 1 + 4],
        912 => ['Certified Mail Adult Signature Required', 1],
        913 => ['Certified Mail Adult Signature Restricted Delivery', 1],
        917 => ['Collect on Delivery Restricted Delivery', 1],
        924 => ['Signature Confirmation Restricted Delivery', 1],
        941 => ['Registered Mail Restricted Delivery', 1 + 4],
        984 => ['Parcel Locker Delivery', 1],
        981 => ['Signature Requested (Priority Mail Express only)', 1],
        986 => ['PO to Addressee (Priority Mail Express only)', 1],
        991 => ['Sunday Delivery (Priority Mail + Priority Mail Express)', 1],
        934 => ['Insurance Restricted Delivery', 1 + 4],
        856 => ['Live Animal Transportation Fee', 1],
        857 => ['Hazardous Materials', 1 + 2],
    ];

    foreach ($options as $code => $service) {
        if ($service[1] & $focus) { // Does the service pass the bit check? If so, add it.
            $output_str .= zen_draw_checkbox_field($name, $code, (in_array($code, $key_values) ? TRUE : FALSE), " id=\"$destination-$code\"", $service[0]);
        }
    }

    $output_str .= zen_draw_hidden_field($name, "-1"); // Have to keep this so that fields are kept inline.

    return $output_str;
}

function zen_cfg_uspsr_account_display($key_value)
{
    // The key_value is either something or nothing

    if (zen_not_null($key_value) && !empty($key_value)) {
        return trim($key_value);
    } else {
        return "--none--";
    }

}

function zen_cfg_uspsr_extraservices_display($key_value)
{
    // Display the Values as a Comma-Separated List.

    // Delete the -1 value (It's just a placeholder to keep the array intact)
    $key_values = array_filter(explode(', ', $key_value), function ($value) {
        return !empty($value) && $value != -1;
    });

    array_walk($key_values, function (&$value) {
        $value = (int)trim($value);
    }); // Quickly remove white space


    $output = '';
    $options = [
        -1 => '', // Hidden placeholder, should not be visible.
        910 => 'Certified Mail',
        930 => 'Insurance',
        925 => 'Priority Mail Express Merchandise Insurance',
        923 => 'Adult Signature Restricted Delivery',
        922 => 'Adult Signature Required',
        940 => 'Registered Mail',
        915 => 'Collect on Delivery',
        955 => 'Return Receipt',
        957 => 'Return Receipt Electronic',
        921 => 'Signature Confirmation',
        911 => 'Certified Mail Restricted Delivery',
        912 => 'Certified Mail Adult Signature Required',
        913 => 'Certified Mail Adult Signature Restricted Delivery',
        917 => 'Collect on Delivery Restricted Delivery',
        924 => 'Signature Confirmation Restricted Delivery',
        941 => 'Registered Mail Restricted Delivery',
        984 => 'Parcel Locker Delivery',
        981 => 'Signature Requested (Priority Mail Express only)',
        986 => 'PO to Addressee (Priority Mail Express only)',
        991 => 'Sunday Delivery (Priority Mail + Priority Mail Express)',
        934 => 'Insurance Restricted Delivery',
        856 => 'Live Animal Transportation Fee',
        857 => 'Hazardous Materials',
    ];

    if (!empty($key_values)) {
        $end = end($key_values);
        foreach ($key_values as $code) {
            $output .= $options[$code] . ($code !== $end ? ", " : "");
        }
    }
    if (!zen_not_null($output))
        $output = '--none--';

    return $output;
}

function zen_cfg_uspsr_showservices($key_value)
{
    // Split up Key Value into an array, then go through that array and find the non-numeric values. That should be the name of a method.
    $key_values = array_filter(explode(', ', $key_value));

    $methods_dom = [];
    $methods_intl = [];

    $output_domestic = '';
    $output_intl = '';

    foreach ($key_values as $methods) {
        if (!is_numeric($methods)) {
            // This is a string, not a number. Check to see if the value contains the word International, otherwise, it's a domestic

            if (preg_match('/International/', $methods)) {
                $methods_intl[] = preg_replace(
                    [
                        '/International/',
                        '/Envelope/',
                        '/ Mail/',
                        '/Large/',
                        '/Medium/',
                        '/Small/',
                        '/First/',
                        '/Legal/',
                        '/Padded/',
                        '/Flat Rate/',
                        '/Express Guaranteed /',
                        '/Package\hService\h-\hRetail/',
                        '/Package Service/',
                        '/ISC/',
                        '/Machinable DDU/',
                        '/Machinable\s+/',
                        '/(Basic|Single-Piece)/i',
                        '/USPS\s+/',
                        '/Non-Soft Pack Tier 1/',
                        '/\s{2,}/',
                    ],
                    [
                        'Intl',
                        'Env',
                        '',
                        'Lg.',
                        'Md.',
                        'Sm.',
                        '1st',
                        'Leg.',
                        'Pad.',
                        'F/R',
                        'Exp Guar',
                        'Pkgs - Retail',
                        'Pkgs - Comm',
                        '',
                        '',
                        '',
                        ' ',
                        '',
                        '',
                        ' '
                    ],
                    $methods
                );
            } else {
                $methods_dom[] = preg_replace(
                    [
                        '/International/',
                        '/Envelope/',
                        '/ Mail/',
                        '/Large/',
                        '/Medium/',
                        '/Small/',
                        '/First/',
                        '/Legal/',
                        '/Padded/',
                        '/Flat Rate/',
                        '/Express Guaranteed /',
                        '/Package\hService\h-\hRetail/',
                        '/Package Service/',
                        '/ISC/',
                        '/Machinable DDU\s+/',
                        '/Machinable\s+/',
                        '/(Basic|Single-Piece)/i',
                        '/USPS\s+/',
                        '/Non-Soft Pack Tier 1/',
                    ],
                    [
                        'Intl',
                        'Env',
                        '',
                        'Lg.',
                        'Md.',
                        'Sm.',
                        '1st',
                        'Leg.',
                        'Pad.',
                        'F/R',
                        'Exp Guar',
                        'Pkgs - Retail',
                        'Pkgs - Comm',
                        '',
                        '',
                        '',
                        '',
                        '',
                        ''
                    ],
                    $methods
                );
            }
        }
    }

    foreach ($methods_dom as $method) {
        $output_domestic .= trim($method) . ($method == end($methods_dom) ? '' : ', ');
    }

    foreach ($methods_intl as $method) {
        $output_intl .= trim($method) . ($method == end($methods_intl) ? '' : ', ');
    }

    $output = "<b>Domestic Methods:</b><br> " . (zen_not_null($output_domestic) ? $output_domestic : '--none--') . "<br><br>\n" . "<b>International Methods</b>: <br>" . (zen_not_null($output_intl) ? $output_intl : '--none--');

    return $output . "\n";
}

function zen_cfg_uspsr_showdimmensions($key_value)
{
    $key_values = explode(', ', $key_value);
    $key_values = array_filter($key_values, function ($value) {
        if (zen_not_null($value)) {
            return "--none--";
        }
    });

    // Domestic Measures are 0 x 2 x 4
    // International Measures are 1 x 3 x 5

    // Check if the measurement setting exists and if it does, check that it's in inches.
    // If it doesn't or if it is set to inches, do nothing.
    if (defined('SHIPPING_DIMENSION_UNITS') && SHIPPING_DIMENSION_UNITS !== "inches") {
        foreach ($key_values as &$dimmension) {
            $dimmension = (float) $dimmension / 2.54;
        }
    }

    $output_str = '';
    $output_str .= "<em>Domestic Measurements (LWH):</em> " . $key_values[0] . " × " . $key_values[2] . " × " . $key_values[4] . "<br>\n";
    $output_str .= "<em>International Measurements (LWH):</em> " . $key_values[1] . " × " . $key_values[3] . " × " . "$key_values[5]";

    return $output_str;
}

function uspsr_pretty_json_print($json)
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;

    $encoded_json = json_decode($json, TRUE); // Read the JSON into an array

    unset($encoded_json['client_secret']);

    $sanitized_json = json_encode($encoded_json, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

    $json_length = strlen($sanitized_json);

    for ($i = 0; $i < $json_length; $i++) {
        $char = $sanitized_json[$i];
        $new_line_level = NULL;
        $post = "";
        if ($ends_line_level !== NULL) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ($in_escape) {
            $in_escape = false;
        } else if ($char === '"') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch ($char) {
                case '}':
                case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{':
                case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ":
                case "\t":
                case "\n":
                case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ($char === '\\') {
            $in_escape = true;
        }
        if ($new_line_level !== NULL) {
            $result .= "\n" . str_repeat("    ", $new_line_level);
        }
        $result .= $char . $post;
    }

    return $result;
}

function uspsr_validate_zipcode($entry)
{
    // Don't do anything if $entry is NULL (likely because the page loaded)
    if (zen_not_null($entry)) {
        // Remove any non-digit characters, US Zip codes are only digits.
        $digits = preg_replace('/\D/', '', $entry);

        // Handle 5 digits or 9 digits by returning the first five.
        if ((strlen($digits) === 5) || (strlen($digits) === 9)) {
            return substr($digits, 0, 5); // Only the initial five digits are necessary, filter anything else.
        }
    }

    // Return false if it doesn't have 5 or 9 digits. That generally means it's an invalid zip.
    return false;
}

// Filter out the "gibberish" and make the title pretty
function uspsr_filter_gibberish($entry)
{
    $entry = preg_replace(
        [
            '/ISC/',
            '/Machinable( DDU)?/',
            '/(Basic|Single-Piece)/i',
            '/USPS\s+/',
            '/Non-Soft Pack Tier 1/',
            '/Oversized/',
            '/Nonstandard/',
            '/(Non)?rectangular/i',
            '/Dimmensional/'
        ],
        ''
        ,
        $entry
    );

    return trim(preg_replace('/\s+/', ' ', $entry));
}

function uspsr_get_categories($key_value)
{

    $limit_list = preg_split("/[\s,]/", trim($key_value));
    $limit_list = array_filter($limit_list);

    $output_str = '';

    foreach ($limit_list as $limit) {
        $output_str .= (zen_not_null(zen_get_category_name($limit)) ? zen_get_category_name($limit) : '') . (end($limit_list) && !zen_not_null($output_str) == $limit ? '' : ',');
    }

    if (!zen_not_null($output_str)) {
        $output_str = "--none--";
    }

    return $output_str;
}

function uspsr_check_connect_local($lookup)
{
    $connect_local = FALSE;

    // Disabling the search for CONNECT_LOCAL as you can't just drop your package at any post office.
    // It has to be the one that is closest to the zip code. So if you don't specify the ZIP, the module will turn it off.
    if (!zen_not_null(MODULE_SHIPPING_USPS_CONNECT_LOCAL_ZIP))
        return false;

    $limit_list = preg_split("/[\s,]/", trim(MODULE_SHIPPING_USPS_CONNECT_LOCAL_ZIP));
    $limit_list = array_filter($limit_list);

    if (in_array(uspsr_validate_zipcode($lookup), $limit_list)) {
        $connect_local = TRUE;
    }

    return $connect_local;

}

function uspsr_get_connect_zipcodes($data)
{
    // Split up the incoming data by commas (remove the blanks)

    if (zen_not_null($data)) {
        $output = '';
        $key_values = preg_split('/[\s+]/', $data);
        array_filter($key_values);

        foreach ($key_values as $zipcode) {
            $output .= $zipcode . ($zipcode != end($key_values) ? ", " : "");
        }

        return $output;

    } else {
        return "--none--";
    }
}

function zen_uspsr_estimate_days($data)
{
    $output = '';

    if (!defined('MODULE_SHIPPING_USPS_HANDLING_TIME')) {
        return $data;
    }

    $daystoadd = (int) MODULE_SHIPPING_USPS_HANDLING_TIME;

    // Simply put, put the number before the word.
    if (preg_match("/\d+\-\d+/", $data)) {

        // Split the range of days off and add the handling time to each end.
        $days = explode('-', $data);
        foreach ($days as &$day) {
            $day = (int)$day + $daystoadd;
        }
        $data = implode('-', $days); // Collapse the array back into a - string. (This should still only have two values)
        $output = $data . " " . MODULE_SHIPPING_USPS_TEXT_DAYS;

    } elseif (is_numeric($data) && ($data > 1 || $data == 0)) {
        $output = (int)$data + $daystoadd . " " . MODULE_SHIPPING_USPS_TEXT_DAYS;
    } else {
        $days = (int)$data + $daystoadd;
        $output = "~" . $days . " " . ($days == 1 ? MODULE_SHIPPING_USPS_TEXT_DAY : MODULE_SHIPPING_USPS_TEXT_DAYS);
    }

    return $output;
}

<?php
// Zen Cart Shipping module for shipping via FedEx
// Uses FedEx REST API 
// Replaces use of FedEx XML Web Services API (deprecated).
// Based on work by Numinix, Vinos de Frutas Tropicales, and many others.
// Portions Copyright 2003 osCommerce
// Portions Copyright 2003-2023 Zen Cart Development Team
// Portions Copyright Vinos de Frutas Tropicales
// Copyright 2023 That Software Guy 
// Additional documentation: 
// https://github.com/scottcwilson/zencart_fedexrest

   /*
    * TODO LIST:
    * Street address for send and receive
    */

   class fedexrest
   {
      const BASE_URL = 'https://apis.fedex.com';
      const SAT_SUFFIX = 'SAT'; 

      /**
       * $code determines the internal 'code' name used to designate "this" shipping module
       *
       * @var string
       */
      public $code;
      /**
       * $description is a soft name for this shipping method
       * @var string
       */
      public $description;
      /**
       * $enabled determines whether this module shows or not... during checkout.
       * @var boolean
       */
      public $enabled;
      /**
       * $icon is the file name containing the Shipping method icon
       * @var string
       */
      public $icon;
      /**
       * $quotes is an array containing all the quote information for this shipping module
       * @var array
       */
      public $quotes;
      /**
       * $sort_order is the order priority of this shipping module when displayed
       * @var int
       */
      public $sort_order;
      /**
       * $tax_basis is used to indicate if tax is based on shipping, billing or store address.
       * @var string
       */
      public $tax_basis;
      /**
       * $tax_class is the  Tax class to be applied to the shipping cost
       * @var string
       */
      public $tax_class;
      /**
       * $title is the displayed name for this shipping method
       * @var string
       */
      public $title;
      /**
       * $_check is used to check the configuration key set up
       * @var int
       */
      protected $_check;
      protected $moduleVersion = '1.3.2';

      protected $fedex_act_num,
         $country,
         $debug,
         $logfile,
         $types,
         $fedex_shipping_num_boxes,
         $fedex_shipping_weight;


      function __construct()
      {
         global $order, $db;

         $this->code = "fedexrest";
         $this->title = MODULE_SHIPPING_FEDEX_REST_TEXT_TITLE;

         if (IS_ADMIN_FLAG === true) {
            $this->title = MODULE_SHIPPING_FEDEX_REST_TEXT_TITLE . ' v' . $this->moduleVersion;
         }
         $this->description = MODULE_SHIPPING_FEDEX_REST_TEXT_TITLE;
         $this->sort_order = defined('MODULE_SHIPPING_FEDEX_REST_SORT_ORDER') ? MODULE_SHIPPING_FEDEX_REST_SORT_ORDER : null;
         if (null === $this->sort_order) return false;
         $this->icon = '';
         $this->enabled = (MODULE_SHIPPING_FEDEX_REST_STATUS === 'true');
         $this->tax_class = MODULE_SHIPPING_FEDEX_REST_TAX_CLASS;
         $this->tax_basis = MODULE_SHIPPING_FEDEX_REST_TAX_BASIS;
         $this->fedex_act_num = MODULE_SHIPPING_FEDEX_REST_ACT_NUM;

         if (defined("SHIPPING_ORIGIN_COUNTRY")) {
            if ((int)SHIPPING_ORIGIN_COUNTRY > 0) {
               $countries_array = zen_get_countries((int)SHIPPING_ORIGIN_COUNTRY, true);
               $this->country = $countries_array['countries_iso_code_2'];
               if (!strlen($this->country) > 0) { //when country failed to be retrieved, likely because running from admin.
                  $this->country = $this->country_iso('', (int)SHIPPING_ORIGIN_COUNTRY);
               }
            } else {
               $this->country = SHIPPING_ORIGIN_COUNTRY;
            }
         } else {
            $this->country = STORE_ORIGIN_COUNTRY;
         }

         $this->debug = (MODULE_SHIPPING_FEDEX_REST_DEBUG === 'true');
         $this->logfile = DIR_FS_LOGS . '/fedexrest-' . date('Ymd') . '.log';
         if (($this->enabled == true) && ((int)MODULE_SHIPPING_FEDEX_REST_ZONE > 0) && !IS_ADMIN_FLAG) {

            $check_flag = false;
            if (isset($order->delivery['country']['id'])) {
            $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FEDEX_REST_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
            }

            if ($check_flag == false) {
               $this->enabled = false;
            }
         }
        if ($this->enabled === true && $this->getOAuthToken() === false) {
            $this->enabled = false;
        }
         if ($this->enabled) {
            if (IS_ADMIN_FLAG === false) {
               $this->setTypes();
            }
         }
      }

      function country_iso($country_name = '', $country_id = '')
      {
         global $db;
         $sql = 'SELECT countries_iso_code_2 FROM ' . TABLE_COUNTRIES . ' WHERE ';
         if (strlen($country_name) > 0) {
            $sql .= ' countries_name = \'' . $country_name . '\'';
         } elseif ($country_id > 0) {
            $sql .= ' countries_id = ' . $country_id;
         } else {
            return "";
         }

         $result = $db->Execute($sql);
         return $result->fields['countries_iso_code_2'];

      }

      function setTypes()
      {
         global $order;

         $this->types = [];
         if (MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_PRIORITY == 'true') {
            $this->types['FEDEX_INTERNATIONAL_PRIORITY'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE);
            $this->types['EUROPE_FIRST_INTERNATIONAL_PRIORITY'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE);
         }
         if (MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_ECONOMY == 'true') {
            $this->types['INTERNATIONAL_ECONOMY'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE);
         }
         if (defined('MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_CONNECT_PLUS') && MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_CONNECT_PLUS == 'true') {
            $this->types['FEDEX_INTERNATIONAL_CONNECT_PLUS'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE); 
         }
         if (MODULE_SHIPPING_FEDEX_REST_STANDARD_OVERNIGHT == 'true') {
            $this->types['STANDARD_OVERNIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
         }
         if (MODULE_SHIPPING_FEDEX_REST_FIRST_OVERNIGHT == 'true') {
            $this->types['FIRST_OVERNIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
         }
         if (MODULE_SHIPPING_FEDEX_REST_PRIORITY_OVERNIGHT == 'true') {
            $this->types['PRIORITY_OVERNIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
         }
         if (MODULE_SHIPPING_FEDEX_REST_2DAY == 'true') {
            $this->types['FEDEX_2_DAY'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
         }
         // because FEDEX_GROUND also is returned for Canadian Addresses, we need to check if the country matches the store country and whether international ground is enabled
         if ((MODULE_SHIPPING_FEDEX_REST_GROUND == 'true' && $order->delivery['country']['id'] == STORE_COUNTRY) || (MODULE_SHIPPING_FEDEX_REST_GROUND == 'true' && ($order->delivery['country']['id'] != STORE_COUNTRY) && MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_GROUND == 'true')) {
            $this->types['FEDEX_GROUND'] = array('icon' => '', 'handling_fee' => ($order->delivery['country']['id'] == STORE_COUNTRY ? MODULE_SHIPPING_FEDEX_REST_HANDLING_FEE : MODULE_SHIPPING_FEDEX_REST_INT_HANDLING_FEE));
            $this->types['GROUND_HOME_DELIVERY'] = array('icon' => '', 'handling_fee' => ($order->delivery['country']['id'] == STORE_COUNTRY ? MODULE_SHIPPING_FEDEX_REST_HOME_DELIVERY_HANDLING_FEE : MODULE_SHIPPING_FEDEX_REST_INT_HANDLING_FEE));
         }
         if (MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_GROUND == 'true') {
            $this->types['INTERNATIONAL_GROUND'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_INT_HANDLING_FEE);
         }
         if (MODULE_SHIPPING_FEDEX_REST_EXPRESS_SAVER == 'true') {
            $this->types['FEDEX_EXPRESS_SAVER'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
         }
         if (MODULE_SHIPPING_FEDEX_REST_FREIGHT == 'true') {
            $this->types['FEDEX_FREIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
            $this->types['FEDEX_NATIONAL_FREIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
            $this->types['FEDEX_1_DAY_FREIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
            $this->types['FEDEX_2_DAY_FREIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
            $this->types['FEDEX_3_DAY_FREIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE);
            $this->types['INTERNATIONAL_ECONOMY_FREIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE);
            $this->types['INTERNATIONAL_PRIORITY_FREIGHT'] = array('icon' => '', 'handling_fee' => MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE);
         }
      }

      function quote($method = '')
      {
         if (empty($this->country)) return false;

         $this->quotes = $this->getRates($method);

         return $this->quotes;
      }


      function getOAuthToken()
      {
          if (isset($_SESSION['fedexrest_token_expires']) && $_SESSION['fedexrest_token_expires'] > time()) {
              $this->debugLog('Existing OAuth token is present.');
              return true;
          }
  
         // Get the bearer token
         // https://developer.fedex.com/api/en-us/catalog/authorization/v1/docs.html

         $this->debugLog("Date and Time: " . date('Y-m-d H:i:s') . PHP_EOL . "FEDEX URL: " . self::BASE_URL, true);
         $url = self::BASE_URL . '/oauth/token';
         $timeout = 15;
         $ch = curl_init();
         $input = 'grant_type=' . 'client_credentials' . '&' .
            'client_id=' . MODULE_SHIPPING_FEDEX_REST_API_KEY . '&' .
            'client_secret=' . MODULE_SHIPPING_FEDEX_REST_SECRET_KEY;

         $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => [
               "cache-control: no-cache",
               "content-type: application/x-www-form-urlencoded"
            ],
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $input,
            CURLOPT_TIMEOUT => (int)$timeout,
         ];
         curl_setopt_array($ch, $curl_options);

         $this->debugLog("Auth Request: $input");

         $response = curl_exec($ch);
         if (curl_errno($ch) !== 0) {
            $this->debugLog('Error from cURL: ' . sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch)));
            echo 'Error from cURL: ' . sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch));
            curl_close($ch);
            return false;  
         }
         curl_close($ch);

         $arr_response = json_decode($response, true);
         $this->debugLog("Auth Response: " . print_r($arr_response, true));

         if (!isset($arr_response['access_token'])) {
            // Ruh roh.  How bad is it? 
            if (isset($arr_response['errors'])) {
               // look for bad client creds error 
               foreach ($arr_response['errors'] as $errobj) {
                  if ($errobj['code'] == 'NOT.AUTHORIZED.ERROR') {
                        global $db;
                        $db->Execute("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'false' WHERE configuration_key = 'MODULE_SHIPPING_FEDEX_REST_STATUS'");
                        zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, MODULE_SHIPPING_FEDEXREST_EMAIL_SUBJECT, MODULE_SHIPPING_FEDEXREST_INVALID_CREDENTIALS, STORE_NAME, EMAIL_FROM);
                  }
               }
            }
            return false;
         }

         if (IS_ADMIN_FLAG === false) {
            $_SESSION['fedexrest_token'] = $arr_response['access_token'];
            $_SESSION['fedexrest_token_expires'] = time() + $arr_response['expires_in'] - 3;
         }
         return true; 
      }

      function getRates($method)
      {
         global $order, $db, $shipping_weight, $shipping_num_boxes;

         // Do the rate query
         // https://developer.fedex.com/api/en-us/catalog/rate/v1/docs.html
         $timeout = 15;
         $ch = curl_init();

         $url = self::BASE_URL . '/rate/v1/rates/quotes';
         $rate_hdrs = [
            "Authorization: Bearer " . $_SESSION['fedexrest_token'], 
            "X-locale: " . MODULE_SHIPPING_FEDEX_REST_TEXT_LOCALE,
            "Content-Type: application/json",
         ];

         // customer details
         $street_address = $order->delivery['street_address'] ?? '';
         $street_address2 = $order->delivery['suburb'] ?? '';
         $city = $order->delivery['city'] ?? '';
         if (isset($order->delivery['country']['id'])) {
            $state = zen_get_zone_code($order->delivery['country']['id'], intval($order->delivery['zone_id']), '');
         } else {
            $countryId = $db->Execute("SELECT countries_id FROM " . TABLE_COUNTRIES . " WHERE countries_name = '" . $order->delivery['country'] . "'");
            $state = zen_get_zone_code($countryId->fields['countries_id'], intval($order->delivery['zone_id']), '');
         }
         if ($state == "QC") $state = "PQ";
         if (empty($order->delivery['postcode'])) $order->delivery['postcode'] = '';
         $postcode = str_replace(array(' ', '-'), '', $order->delivery['postcode']);
         if (isset($order->delivery['country']['iso_code_2'])) {
            $country_id = $order->delivery['country']['iso_code_2'];
         } else {
            $country_id = $this->country_iso($order->delivery['country']);
         }

         //Skip the state if the state is over 2 characters, such as New Zealand, Germany and Spain. Otherwise no quote.
         if (strlen(trim($state)) > 2)
         {
            $state = '';
         }

         $this->fedex_shipping_num_boxes = ($shipping_num_boxes > 0 ? $shipping_num_boxes : 1);
         $this->fedex_shipping_weight = $shipping_weight;
         $packages = [];
         // By default, the shipped value is the retail price.
         // You could do some math using your margin if you wanted to 
         // use COGS instead. 
         $shipped_value = $order->info['subtotal']; 
         $box_value = round($shipped_value / $this->fedex_shipping_num_boxes, 2);
         $packageSpecialServices = []; 
         if (!empty(MODULE_SHIPPING_FEDEX_REST_SIGNATURE_OPTION) && MODULE_SHIPPING_FEDEX_REST_SIGNATURE_OPTION > -1 && $shipped_value >= MODULE_SHIPPING_FEDEX_REST_SIGNATURE_OPTION) {
            // Only works in the US
            if ($country_id == 'US') { 
               $packageSpecialServices = [
                  'signatureOptionType' => 'INDIRECT',
               ]; 
            }
         }
//MODULE_SHIPPING_FEDEX_REST_INSURE  
         for ($i = 0; $i < $this->fedex_shipping_num_boxes; $i++) {
            $package = [
               "weight" => [
                  "units" => MODULE_SHIPPING_FEDEX_REST_WEIGHT,
                  "value" => $shipping_weight,
               ],
            ];
            if (MODULE_SHIPPING_FEDEX_REST_INSURE > -1 && $shipped_value > (float)MODULE_SHIPPING_FEDEX_REST_INSURE) {
               $package += [
                  "declaredValue" => [
                     "amount" => $box_value,
                     "currency" => $_SESSION['currency'],
                  ], 
               ]; 
            }
            if (!empty($packageSpecialServices)) { 
               $package += [
                  'packageSpecialServices' => $packageSpecialServices
               ]; 
            }
            $packages[] = $package;
         }

         $requestControlParms = [
            'returnTransitTimes' => (MODULE_SHIPPING_FEDEX_REST_TRANSIT_TIME == 'true' ? 'TRUE' : 'FALSE'),
         ];
         if (MODULE_SHIPPING_FEDEX_REST_SATURDAY == 'true') {
           $requestControlParms += [
              'variableOptions' => 'SATURDAY_DELIVERY', 
           ]; 
         }

         if (MODULE_SHIPPING_FEDEX_REST_SHIP_TO_RESIDENCE == 'true') {
            $ship_to_residential = true; 
         } else if (MODULE_SHIPPING_FEDEX_REST_SHIP_TO_RESIDENCE == 'false') {
            $ship_to_residential = false; 
         } else { 
            $ship_to_residential = (empty($order->delivery['company'])); 
         }

         $shipDate = new DateTime();
         // $shipDate->modify('next thursday');
         $rate_data = [
            "accountNumber" => [
               "value" => MODULE_SHIPPING_FEDEX_REST_ACT_NUM
            ],
            "rateRequestControlParameters" => $requestControlParms,
            "requestedShipment" => [
               "shipper" => [
                  "address" => [
                     "city" => MODULE_SHIPPING_FEDEX_REST_CITY,
                     "stateOrProvinceCode" => MODULE_SHIPPING_FEDEX_REST_STATE,
                     "postalCode" => MODULE_SHIPPING_FEDEX_REST_POSTAL,
                     "countryCode" => MODULE_SHIPPING_FEDEX_REST_COUNTRY,
                     "residential" => (MODULE_SHIPPING_FEDEX_REST_SHIP_FROM_RESIDENCE == 'true' ? true : false),
                  ]
               ],
               "recipient" => [
                  "address" => [
                     "city" => $city,
                     "stateOrProvinceCode" => $state,
                     "postalCode" => $postcode,
                     "countryCode" => $country_id,
                     "residential" => $ship_to_residential, 
                  ]
               ],
               "rateRequestType" => [
                  (MODULE_SHIPPING_FEDEX_REST_RATES == 'LIST' ? 'LIST' : 'ACCOUNT'),
               ],
               "shipDateStamp" => $shipDate->format("Y-m-d"),
               "pickupType" => $this->_setPickup(),
               "requestedPackageLineItems" => $packages,
               "documentShipment" => false,
               "packagingType" => "YOUR_PACKAGING",
               "totalPackageCount" => $this->fedex_shipping_num_boxes, 
               "groupShipment" => true,
               "groundShipment" => true
            ],
            "carrierCodes" => ["FDXG", "FDXE"],
         ];

         if ($country_id != MODULE_SHIPPING_FEDEX_REST_COUNTRY) { 
            $rate_data ["requestedShipment"]["customsClearanceDetail"] = [ 
               "commodities" => [ 
                  [ "description" => "Goods", 
                  "quantity" => "1", 
                  "quantityUnits" => "PCS", 
                  "weight" => [ 
                     "units" => MODULE_SHIPPING_FEDEX_REST_WEIGHT, 
                     "value" => ($_SESSION['cart']->show_weight()), 
                  ], 
                  "customsValue" => [ 
                     "amount" => ($_SESSION['cart']->show_total()), 
                     "currency" => $_SESSION['currency'],
                  ], 
                  ], 
               ], 
            ]; 
         }

         /*
               echo '<pre>';
               print_r($rate_data);
               echo '</pre>';
         */
         $curl_options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $rate_hdrs,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($rate_data),
            CURLOPT_TIMEOUT => (int)$timeout,
            CURLOPT_ENCODING => 'gzip',
         ];
         curl_setopt_array($ch, $curl_options);

         // $this->debugLog("Rate Request: " . print_r($rate_data, true));
         $this->debugLog("JSON Rate Request: " . json_encode($rate_data, JSON_PRETTY_PRINT));

         $response = curl_exec($ch);
         if (curl_errno($ch) !== 0) {
            $this->debugLog('Error from cURL: ' . sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch)));
            echo 'Error from cURL: ' . sprintf('Error [%d]: %s', curl_errno($ch), curl_error($ch));
         }
         curl_close($ch);

         $arr_response = json_decode($response, true);
         $this->debugLog("Rate Response: " . print_r($arr_response, true));
         if (empty($arr_response['output']['rateReplyDetails'])) {
            return false;
         }
         switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
            case (0):
               $show_box_weight = '';
               break;
            case (1):
               $show_box_weight = ' (' . $this->fedex_shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')';
               break;
            case (2):
               $show_box_weight = ' (' . number_format($this->fedex_shipping_weight * $this->fedex_shipping_num_boxes, 2) . TEXT_SHIPPING_WEIGHT . ')';
               break;
            default:
               $show_box_weight = ' (' . $this->fedex_shipping_num_boxes . ' x ' . number_format($this->fedex_shipping_weight, 2) . TEXT_SHIPPING_WEIGHT . ')';
               break;
         }

         $quotes = [
            'id' => $this->code,
            'module' => $this->title . $show_box_weight,
         ];
         $methods = [];
         foreach ($arr_response['output']['rateReplyDetails'] as $rate) {
            $serviceType = $rate['serviceType'];
            // ensure key exists (i.e. service enabled) 
            $check_serviceType = str_replace('_', '', $serviceType); 
            $method_ok = false; 
            if (array_key_exists($serviceType, $this->types)) {
               if ($method == '') {
                  $method_ok = true;
               } else if ($check_serviceType == $method) {
                  $method_ok = true;
               } else if ($check_serviceType . self::SAT_SUFFIX == $method) {
                  $method_ok = true;
               }
            } 
            if (!isset($rate['ratedShipmentDetails'][0])) $method_ok = false;
            if (!$method_ok) continue; 
            // We have to make sure it's not Saturday if not wanted
            if (!empty($method)) {
               if (!empty($rate['commit']['saturdayDelivery']) && $rate['commit']['saturdayDelivery'] == 1) {
                  if (($check_serviceType . self::SAT_SUFFIX) !== $method) {
                     continue; 
                  }
               }
            }

            if ($method_ok) {
               $cost = $rate['ratedShipmentDetails'][0]['totalNetFedExCharge'];
               // add on specified fees - could be % or flat rate
               $fee = 0; 
               if (!empty($this->types[$serviceType]['handling_fee'])) { 
                  $fee = $this->types[$serviceType]['handling_fee']; 
               }
               $cost = $cost + ((strpos($fee, '%') !== FALSE) ? ($cost * (float)$fee / 100) : (float)$fee);
               
               // Show transit time? 
               $transitTime = '';
               if (MODULE_SHIPPING_FEDEX_REST_TRANSIT_TIME == 'true' && in_array($serviceType, array('GROUND_HOME_DELIVERY', 'FEDEX_GROUND', 'INTERNATIONAL_GROUND'))) {
                  $transitTime = ' (' . str_replace(array('_', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen'), array(' business ', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14), strtolower($rate['operationalDetail']['transitTime'] ?? '')) . ')';
               }
               $id_suffix = ''; 
               if (!empty($rate['commit']['saturdayDelivery']) && $rate['commit']['saturdayDelivery'] == 1) {
                  $transitTime .= MODULE_SHIPPING_FEDEXREST_SATURDAY; 
                  $id_suffix = self::SAT_SUFFIX; 
               }

               $methods[] = [
                  'id' => str_replace('_', '', $serviceType) . $id_suffix,
                  'title' => ucwords(strtolower(str_replace('_', ' ', $serviceType))) . $transitTime,
                  'cost' => $cost
               ];
            }
         }

         // Early exit for no rates
         if (sizeof($methods) == 0) {
            return false;
         }

         $quotes['methods'] = $methods;
         if ($this->tax_class > 0) {
            $quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
         }

         return $quotes;
      }

      function _setPickup()
      {
         switch (MODULE_SHIPPING_FEDEX_REST_PICKUP) {
            case '1':
               return 'USE_SCHEDULED_PICKUP';
               break;
            case '2':
               return 'CONTACT_FEDEX_TO_SCHEDULE';
               break;
            case '3':
               return 'DROPOFF_AT_FEDEX_LOCATION';
               break;
         }
      }

      function check()
      {
         global $db;
         if (!isset($this->_check)) {
            $check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SHIPPING_FEDEX_REST_STATUS'");
            $this->_check = $check_query->RecordCount();
            if ($this->_check) {
               // verify config is current
               // ...
            }
         }
         return $this->_check;
      }

      function install()
      {
         global $db;
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable FedEx REST API','MODULE_SHIPPING_FEDEX_REST_STATUS','true','Do you want to offer FedEx shipping?','6','0','zen_cfg_select_option(array(\'true\',\'false\'),',now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Account Number', 'MODULE_SHIPPING_FEDEX_REST_ACT_NUM', '', 'Enter FedEx Account Number', '6', '3', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx API Key', 'MODULE_SHIPPING_FEDEX_REST_API_KEY', '', 'Enter FedEx API Key', '6', '4', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('FedEx Secret Key', 'MODULE_SHIPPING_FEDEX_REST_SECRET_KEY', '', 'Enter FedEx Secret Key', '6', '4', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Weight Units', 'MODULE_SHIPPING_FEDEX_REST_WEIGHT', 'LB', 'Weight Units:', '6', '10', 'zen_cfg_select_option(array(\'LB\', \'KG\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('First line of street address', 'MODULE_SHIPPING_FEDEX_REST_ADDRESS_1', '', 'Enter the first line of your ship-from street address, required', '6', '20', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Second line of street address', 'MODULE_SHIPPING_FEDEX_REST_ADDRESS_2', '', 'Enter the second line of your ship-from street address, leave blank if you do not need to specify a second line', '6', '21', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('City name', 'MODULE_SHIPPING_FEDEX_REST_CITY', '', 'Enter the city name for the ship-from street address, required', '6', '22', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('State or Province name', 'MODULE_SHIPPING_FEDEX_REST_STATE', '', 'Enter the 2 letter state or province name for the ship-from street address, required for Canada and US', '6', '23', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Country Code', 'MODULE_SHIPPING_FEDEX_REST_COUNTRY', 'US', 'Enter the 2 letter country code for the ship-from address', '6', '25', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Postal code', 'MODULE_SHIPPING_FEDEX_REST_POSTAL', '', 'Enter the postal code for the ship-from street address, required', '6', '28', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Phone number', 'MODULE_SHIPPING_FEDEX_REST_PHONE', '', 'Enter a contact phone number for your company, required', '6', '30', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Ship From address is residential', 'MODULE_SHIPPING_FEDEX_REST_SHIP_FROM_RESIDENCE', 'false', 'Is pickup address residential? (Only applies for Pickup type = 1)', '6', '31', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Ship To address is residential', 'MODULE_SHIPPING_FEDEX_REST_SHIP_TO_RESIDENCE', 'false', 'Is ship to address residential?', '6', '31', 'zen_cfg_select_option(array(\'true\', \'false\', \'false if Company set in ship-to address, true otherwise\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Pickup type', 'MODULE_SHIPPING_FEDEX_REST_PICKUP', '1', 'Pickup type (1 = Use scheduled pickup, 2 = Contact FedEx to schedule, 3 = Dropoff at FedEx location)?', '6', '35', 'zen_cfg_select_option(array(\'1\',\'2\',\'3\'),', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Express Saver', 'MODULE_SHIPPING_FEDEX_REST_EXPRESS_SAVER', 'true', 'Enable FedEx Express Saver', '6', '50', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Standard Overnight', 'MODULE_SHIPPING_FEDEX_REST_STANDARD_OVERNIGHT', 'true', 'Enable FedEx Express Standard Overnight', '6', '51', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable First Overnight', 'MODULE_SHIPPING_FEDEX_REST_FIRST_OVERNIGHT', 'true', 'Enable FedEx Express First Overnight', '6', '52', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Priority Overnight', 'MODULE_SHIPPING_FEDEX_REST_PRIORITY_OVERNIGHT', 'true', 'Enable FedEx Express Priority Overnight', '6', '53', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable 2 Day', 'MODULE_SHIPPING_FEDEX_REST_2DAY', 'true', 'Enable FedEx Express 2 Day', '6', '54', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Priority', 'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_PRIORITY', 'true', 'Enable FedEx Express International Priority', '6', '55', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Economy', 'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_ECONOMY', 'true', 'Enable FedEx Express International Economy', '6', '56', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Connect Plus', 'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_CONNECT_PLUS', 'true', 'Enable FedEx Express International Connect Plus', '6', '56', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Ground', 'MODULE_SHIPPING_FEDEX_REST_GROUND', 'true', 'Enable FedEx Ground', '6', '57', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable International Ground', 'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_GROUND', 'true', 'Enable FedEx International Ground', '6', '58', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Freight', 'MODULE_SHIPPING_FEDEX_REST_FREIGHT', 'true', 'Enable FedEx Freight', '6', '59', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Saturday Delivery', 'MODULE_SHIPPING_FEDEX_REST_SATURDAY', 'false', 'Enable Saturday Delivery', '6', '70', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Domestic Ground Handling Fee', 'MODULE_SHIPPING_FEDEX_REST_HANDLING_FEE', '', 'Add a domestic handling fee or leave blank (example: 15 or 15%)', '6', '75', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Domestic Express Handling Fee', 'MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE', '', 'Add a domestic handling fee or leave blank (example: 15 or 15%)', '6', '80', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Home Delivery Handling Fee', 'MODULE_SHIPPING_FEDEX_REST_HOME_DELIVERY_HANDLING_FEE', '', 'Add a home delivery handling fee or leave blank (example: 15 or 15%)', '6', '85', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('International Ground Handling Fee', 'MODULE_SHIPPING_FEDEX_REST_INT_HANDLING_FEE', '', 'Add an international handling fee or leave blank (example: 15 or 15%)', '6', '90', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('International Express Handling Fee', 'MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE', '', 'Add an international handling fee or leave blank (example: 15 or 15%)', '6', '95', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('FedEx Rates','MODULE_SHIPPING_FEDEX_REST_RATES','LIST','FedEx Rates (LIST = FedEx default rates, ACCOUNT = Your discounted rates)','6','100','zen_cfg_select_option(array(\'LIST\',\'ACCOUNT\'),',now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Signature Option', 'MODULE_SHIPPING_FEDEX_REST_SIGNATURE_OPTION', '-1', 'Require a signature when subtotal is greater than or equal to (set to -1 to disable):', '6', '105', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Show Estimated Transit Time', 'MODULE_SHIPPING_FEDEX_REST_TRANSIT_TIME', 'false', 'Display the transit time for ground methods?', '6', '110', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Insurance', 'MODULE_SHIPPING_FEDEX_REST_INSURE', '-1', 'Insure packages when subtotal is greater than or equal to (set to -1 to disable):', '6', '120', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_FEDEX_REST_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '130', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_FEDEX_REST_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '135', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tax Basis', 'MODULE_SHIPPING_FEDEX_REST_TAX_BASIS', 'Shipping', 'On what basis is Shipping Tax calculated. Options are<br>Shipping - Based on customers Shipping Address<br>Billing Based on customers Billing address<br>Store - Based on Store address if Billing/Shipping Zone equals Store zone', '6', '0', 'zen_cfg_select_option(array(\'Shipping\', \'Billing\', \'Store\'), ', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_FEDEX_REST_SORT_ORDER', '0', 'Sort order of display.', '6', '998', now())");
         $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug', 'MODULE_SHIPPING_FEDEX_REST_DEBUG', 'false', 'Turn On Debugging?', '6', '999', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      }

      function remove()
      {
         global $db;
         $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHIPPING_FEDEX_REST_%'");
      }

      function keys()
      {
         return array('MODULE_SHIPPING_FEDEX_REST_STATUS',
            'MODULE_SHIPPING_FEDEX_REST_ACT_NUM',
            'MODULE_SHIPPING_FEDEX_REST_API_KEY',
            'MODULE_SHIPPING_FEDEX_REST_SECRET_KEY',
            'MODULE_SHIPPING_FEDEX_REST_WEIGHT',
            'MODULE_SHIPPING_FEDEX_REST_ADDRESS_1',
            'MODULE_SHIPPING_FEDEX_REST_ADDRESS_2',
            'MODULE_SHIPPING_FEDEX_REST_CITY',
            'MODULE_SHIPPING_FEDEX_REST_STATE',
            'MODULE_SHIPPING_FEDEX_REST_COUNTRY',
            'MODULE_SHIPPING_FEDEX_REST_POSTAL',
            'MODULE_SHIPPING_FEDEX_REST_PHONE',
            'MODULE_SHIPPING_FEDEX_REST_SHIP_FROM_RESIDENCE',
            'MODULE_SHIPPING_FEDEX_REST_SHIP_TO_RESIDENCE',
            'MODULE_SHIPPING_FEDEX_REST_PICKUP',
            'MODULE_SHIPPING_FEDEX_REST_EXPRESS_SAVER',
            'MODULE_SHIPPING_FEDEX_REST_STANDARD_OVERNIGHT',
            'MODULE_SHIPPING_FEDEX_REST_FIRST_OVERNIGHT',
            'MODULE_SHIPPING_FEDEX_REST_PRIORITY_OVERNIGHT',
            'MODULE_SHIPPING_FEDEX_REST_2DAY',
            'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_PRIORITY',
            'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_ECONOMY',
            'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_CONNECT_PLUS',
            'MODULE_SHIPPING_FEDEX_REST_GROUND',
            'MODULE_SHIPPING_FEDEX_REST_FREIGHT',
            'MODULE_SHIPPING_FEDEX_REST_INTERNATIONAL_GROUND',
            'MODULE_SHIPPING_FEDEX_REST_SATURDAY',
            'MODULE_SHIPPING_FEDEX_REST_TAX_CLASS',
            'MODULE_SHIPPING_FEDEX_REST_TAX_BASIS',
            'MODULE_SHIPPING_FEDEX_REST_HANDLING_FEE',
            'MODULE_SHIPPING_FEDEX_REST_HOME_DELIVERY_HANDLING_FEE',
            'MODULE_SHIPPING_FEDEX_REST_EXPRESS_HANDLING_FEE',
            'MODULE_SHIPPING_FEDEX_REST_INT_HANDLING_FEE',
            'MODULE_SHIPPING_FEDEX_REST_INT_EXPRESS_HANDLING_FEE',
            'MODULE_SHIPPING_FEDEX_REST_SIGNATURE_OPTION',
            'MODULE_SHIPPING_FEDEX_REST_INSURE',
            'MODULE_SHIPPING_FEDEX_REST_RATES',
            'MODULE_SHIPPING_FEDEX_REST_TRANSIT_TIME',
            'MODULE_SHIPPING_FEDEX_REST_ZONE',
            'MODULE_SHIPPING_FEDEX_REST_SORT_ORDER',
            'MODULE_SHIPPING_FEDEX_REST_DEBUG'
         );
      }

      protected function debugLog($message, $include_spacer = false)
      {
         if ($this->debug === true) {
            $spacer = ($include_spacer === false) ? '' : "------------------------------------------\n";
            error_log($spacer . date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, 3, $this->logfile);
         }
      }
   }

<?
/*
  $Id: rmamsmallpacket.php,v 1.3 2009/03/20 04:40:21 spiderr Exp $
  based upon
  $Id: rmamsmallpacket.php,v 1.3 2009/03/20 04:40:21 spiderr Exp $
  based upon
  $Id: rmamsmallpacket.php,v 1.3 2009/03/20 04:40:21 spiderr Exp $

  Copyright (c) 2006 Philip Clarke

  Copyright (c) 2004 Merlin Beedell

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

  USAGE
  Part of the big_royalmail shipping module.
  This was originally a conversion of an oscommerce module ported to zencart.
  Various elements have been tidied up for this first release, such as trimming
  the whitespace from the list of countries and rates which has caused this
  module to not function in previous releases.

  All that should be required is the adding of Handling fees.

*/

  class rmamsmallpacket  {
    var $code, $title, $description, $enabled, $num_zones ;


// class constructor
    function rmamsmallpacket () {
      $this->code = 'rmamsmallpacket';
      $this->title = MODULE_SHIPPING_RMAMSMALLPACKET_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_RMAMSMALLPACKET_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_RMAMSMALLPACKET_SORT_ORDER;
      $this->icon = 'shipping_ukam.gif';
      $this->tax_class = MODULE_SHIPPING_RMAMSMALLPACKET_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_RMAMSMALLPACKET_STATUS == 'True') ? true : false);

      // CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
      $this->num_zones = 2;
    }

// class methods
    function quote( $pShipHash = array() ) {
      global $order, $currency;
		// default to 1
		$shippingWeight = (!empty( $pShipHash['shipping_weight'] ) ? $pShipHash['shipping_weight'] : 1);
		$shippingNumBoxes = (!empty( $pShipHash['shipping_num_boxes'] ) ? $pShipHash['shipping_num_boxes'] : 1);

      $currencies = new currencies();

      $dest_country = $order->delivery['country']['countries_iso_code_2'];
      $dest_zone = 0;
      $error = false;

      for ($i=1; $i<=$this->num_zones; $i++) {
        $countries_table = constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_' . $i);

        $country_zones = split("[,]", preg_replace('/\s*/','',$countries_table) );

        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
      }
	   //12 FEB 04 MBeedell	NO specified country (or *) then use this zone for all shipping rates
      if ($dest_zone == 0) {
		for ($i=1; $i<=$this->num_zones; $i++) {
		  $countries_table = constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_' . $i);
		  if ($countries_table == '' or $countries_table == '*') {
		    $dest_zone = $i;
		    break;
		  }
		}
	  }

      if ($dest_zone == 0) {
        $error = true;
      } else {
        $shipping = -1;

	   //12 FEB 04 MBeedell	'glue' together the rates from the 10 cost data entry boxes

        $zones_cost = constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST0_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST1_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST2_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST3_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST4_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST5_' . $dest_zone);

        $zones_table = split("[:,]" , preg_replace('/\s*/','',$zones_cost) );
        $size = sizeof($zones_table);
        for ($i=0; $i<$size; $i+=2) {
          if ($shippingWeight <= $zones_table[$i]) { 
            $shipping = $zones_table[$i+1];
			//12 Feb 04 MBeedell - correctly format the total weight... if the weight exceeds the max
			//  weight, then it is divided down over a number of separate packages - so the weight could end
			//  up being a long fraction.

            $sw_text = number_format($shippingWeight, 3, $currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);


            $shipping_method = MODULE_SHIPPING_RMAMSMALLPACKET_TEXT_WAY . ' ' . $dest_country . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RMAMSMALLPACKET_TEXT_UNITS;
            $shipping_method = MODULE_SHIPPING_RMAMSMALLPACKET_TEXT_WAY . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RMAMSMALLPACKET_TEXT_UNITS;
			//12 Feb 04 MBeedell - if weight is over the max, then show the number of boxes being shipped
            if ($shippingNumBoxes > 1) {
	            $sw_text = number_format($shippingNumBoxes, 0, $currency['decimal_point'], $currency['thousands_point']);
                $sw_text = number_format($shippingWeight, 0, $currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);
				$shipping_method = $shipping_method . ' in ' . $sw_text . ' boxes ';
            }
            break;
          }
        }

        if ($shipping == -1) {
          $shipping_cost = 0;
          $shipping_method = MODULE_SHIPPING_RMAMSMALLPACKET_UNDEFINED_RATE;
          //$shipping_method = $zones_cost; 	   //12 FEB 04 MBeedell	useful for debug-print out the rates list!
        } else {
          $shipping_cost = ($shipping * $shippingNumBoxes) + constant('MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_HANDLING_' . $dest_zone);
        }
      }

      $this->quotes = array('id' => $this->code,
//                            'module' => MODULE_SHIPPING_RMAMSMALLPACKET_TEXT_TITLE ,
                            'module' => 'Royal Mail Airmail <i style="font-weight: normal">&quot;small packet&quot;</i>' ,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $shipping_method,
                                                     'cost' => $shipping_cost)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
      }

		if (zen_not_null($this->icon)) {
			$this->quotes['icon'] = $this->icon;
		}

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_RMAMSMALLPACKET_INVALID_ZONE;

      return $this->quotes;
    }

    function check() {
    global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_RMAMSMALLPACKET_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
    global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Zones Method', 'MODULE_SHIPPING_RMAMSMALLPACKET_STATUS', 'True', 'You must enable Zone shipping for this module to work', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_RMAMSMALLPACKET_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_RMAMSMALLPACKET_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");

// European Rates

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('European Handling Fee', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_HANDLING_1', '0', 'The amount it costs you to package the items for European airmail delivery.', '6', '0', now())");

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Royal Mail defined European Countries', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_1', 'AL, AD, AM, AT, AZ, BA, BE, BG, BY, CY, CZ, DE, DK, EE, ES, FI, FO, FR, GI, GL, GR, HU, HR, IS, IE, IT, KZ, LI, LT, LU, LV, MK, MT, PL, PT, SE, SI, SK, SM, RU, UZ, UA, TM, TR, TJ, CH, MD, MC', 'two character ISO country codes for Europe.', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('European Airmail rates from GB &amp; Northern Ireland', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST0_1', '0.1:1.1, 0.12:1.21, 0.14:1.33, 0.16:1.45, 0.18:1.57, 0.2:1.69, 0.22:1.8, 0.24:2.2, 0.26:2.12, 0.28:2.23, 0.3:2.29, 0.32:2.29, 0.34:2.39, 0.36:2.49, 0.38:2.59, 0.4:2.69', 'Correct on 13<sup>th</sup> September 2006, from information published August 2006. <br />Example: 0.1:1.1 means weights less than or equal to 0.1 Kg would cost &pound;1.10.', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST1_1', '0.42:2.79, 0.44:2.89, 0.46:2.99, 0.48:3.09, 0.5:3.19, 0.52:3.29, 0.54:3.39, 0.56:3.49, 0.58:3.59, 0.6:3.69, 0.62:3.79, 0.64:3.89, 0.66:3.99, 0.68:4.09, 0.7:4.19, 0.72:4.29', 'European Rates cont\'d (2):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST2_1', '0.74:4.39, 0.76:4.49, 0.78:4.59, 0.8:4.69, 0.82:4.79, 0.84:4.89, 0.86:4.99, 0.88:5.09, 0.9:5.19, 0.92:5.29, 0.94:5.39, 0.96:5.49, 0.98:5.59, 1:5.69, 1.02:5.79, 1.04:5.89', 'European Rates cont\'d (3):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST3_1', '1.06:5.99, 1.08:6.09, 1.1:6.19, 1.12:6.29, 1.14:6.39, 1.16:6.49, 1.18:6.59, 1.2:6.69, 1.22:6.79, 1.24:6.89, 1.26:6.99, 1.28:7.09, 1.3:7.19, 1.32:7.29, 1.34:7.39, 1.36:7.49', 'European Rates cont\'d (4):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST4_1', '1.38:7.59, 1.4:7.69, 1.42:7.79, 1.44:7.89, 1.46:7.99, 1.48:8.09, 1.5:8.19, 1.52:8.29, 1.54:8.39, 1.56:8.49, 1.58:8.59, 1.6:8.69, 1.62:8.79, 1.64:8.89, 1.66:8.99, 1.68:9.09', 'European Rates cont\'d (5):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST5_1', '1.7:9.19, 1.72:9.29, 1.74:9.39, 1.76:9.49, 1.78:9.59, 1.8:9.69, 1.82:9.79, 1.84:9.89, 1.86:9.99, 1.88:10.09, 1.9:10.19, 1.92:10.29, 1.94:10.39, 1.96:10.49, 1.98:10.59, 2:10.69', 'European Rates cont\'d (6):', '6', '0', 'zen_cfg_textarea(', now())");

// WORLDWIDE RATES

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Worldwide Handling Fee', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_HANDLING_2', '0', 'The amount it costs you to package the items for Worldwide airmail delivery.', '6', '1', now())");

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Royal Mail defined World Zones 1 &amp; 2 Countries', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_2', 'AX, AF, DZ, AS, AO, AQ,AI, AG, AR, AW, AU, BS, BH, BD, BB, BZ, BJ, BM, BT, BO, BW, BV, BR, IO, BN, BF, BI, KH, CM, CA, CV, KY, CF, TD, CB, CL, CN, CX, CC, CO, KM, CG, CK, CR, CI, CU, DJ, DM, DO, TP, EC, EG, SV, GQ, ER, ET, FK, FJ, FX, GF, PF, TF, GA, GM, GE, GH, GI, GD, GP, GU, GT, GN, GW, GY, HT, HM, HN, HK, IN, ID, IR, IQ, IL, JM, JP, JO, KE, KI, KP, KR, CS, KW, KG, LA, LB, LS, LR, LY, MO, MG, MW, MY, MV, ML, MH, MQ, MR, MU, YT, MX, FM, MN, CS, MS, MA, MZ, MM, NA, NR, NP, NL, AN, NC, NZ, NI, NE, NG, NU, NF, MP, NO, OM, PK, PW, PA, PG, PY, PE, PH, PN, PR, QA, RE, IE, RO, RW, KN, LC, VC, WS, ST, SA, SN, CS, SC, CL, SG, SB, SO, ZA, GS, ES, LK, SH, PM, SD, SR, SJ, SZ, CH, SY, TW, TH, TG, TK, TO, TT, TN, TC, TV, UG, AE, US, UM, UY, VU, VA, VE, VN, VG, VI, WF, EH, YE, YU, ZR, ZM, ZW', 'two character ISO country codes for the Rest of the World.', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Worldwide Airmail rates from GB &amp; Northern Ireland', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST0_2', '0.1:1.46, 0.12:1.66, 0.14:1.87, 0.16:2.07, 0.18:2.27, 0.2:2.48, 0.22:2.67, 0.24:2.86, 0.26:3.05, 0.28:3.24, 0.3:3.43, 0.32:3.62, 0.34:3.81, 0.36:4, 0.38:4.19, 0.4:4.38', 'Correct on 13<sup>th</sup> September 2006, from information published August 2006. <br />Example: 0.1:1.46 means weights less than or equal to 0.1 Kg would cost &pound;1.46.', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST1_2', '0.42:4.57, 0.44:4.76, 0.46:4.95, 0.48:5.14, 0.5:5.33, 0.52:5.52, 0.54:5.71, 0.56:5.9, 0.58:6.09, 0.6:6.28, 0.62:6.47, 0.64:6.66, 0.66:6.85, 0.68:7.04, 0.7:7.23, 0.72:7.42', 'Worldwide Rates cont\'d (2):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST2_2', '0.74:7.61, 0.76:7.8, 0.78:7.99, 0.8:8.18, 0.82:8.37, 0.84:8.56, 0.86:8.75, 0.88:8.94, 0.9:9.13, 0.92:9.32, 0.94:9.51, 0.96:9.7, 0.98:9.89, 1:10.08, 1.02:10.27, 1.04:10.46', 'Worldwide Rates cont\'d (3):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST3_2', '1.06:10.65, 1.08:10.84, 1.1:11.03, 1.12:11.22, 1.14:11.41, 1.16:11.6, 1.18:11.79, 1.2:11.98, 1.22:12.17, 1.24:12.36, 1.26:12.55, 1.28:12.74, 1.3:12.93, 1.32:13.12, 1.34:13.31, 1.36:13.5', 'Worldwide Rates cont\'d (4):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST4_2', '1.38:13.69, 1.4:13.88, 1.42:14.07, 1.44:14.26, 1.46:14.45, 1.48:14.64, 1.5:14.83, 1.52:15.02, 1.54:15.21, 1.56:15.4, 1.58:15.59, 1.6:15.78, 1.62:15.97, 1.64:16.16, 1.66:16.35, 1.68:16.54', 'Worldwide Rates cont\'d (5):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST5_2', '1.7:16.73, 1.72:16.92, 1.74:17.11, 1.76:17.3, 1.78:17.49, 1.8:17.68, 1.82:17.87, 1.84:18.06, 1.86:18.25, 1.88:18.44, 1.9:18.63, 1.92:18.82, 1.94:19.01, 1.96:19.2, 1.98:19.39, 2:19.59', 'Worldwide Rates cont\'d (6):', '6', '1', 'zen_cfg_textarea(', now())");

    }

    function remove() {
    global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
       $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key IN ( 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_1', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_2') ");
    }

    function keys() {
        $keys = array('MODULE_SHIPPING_RMAMSMALLPACKET_STATUS', 'MODULE_SHIPPING_RMAMSMALLPACKET_TAX_CLASS', 'MODULE_SHIPPING_RMAMSMALLPACKET_SORT_ORDER', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_1', 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COUNTRIES_2');

        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_HANDLING_1';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST0_1';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST1_1';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST2_1';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST3_1';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST4_1';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST5_1';

        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_HANDLING_2';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST0_2';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST1_2';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST2_2';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST3_2';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST4_2';
        $keys[] = 'MODULE_SHIPPING_RMAMSMALLPACKET_ZONES_COST5_2';

      return $keys;
    }
  }
?>

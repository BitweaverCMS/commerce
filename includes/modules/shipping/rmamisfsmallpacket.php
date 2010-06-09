<?
/*
  $Id$
  based upon
  $Id$
  based upon
  $Id$

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

  class rmamisfsmallpacket  {
    var $code, $title, $description, $enabled, $num_zones ;


// class constructor
    function rmamisfsmallpacket () {
      $this->code = 'rmamisfsmallpacket';
      $this->icon = 'shipping_ukamisf';
		if( defined( 'MODULE_SHIPPING_RMAMISFSMALLPACKET_STATUS' ) ) {
			$this->title = MODULE_SHIPPING_RMAMISFSMALLPACKET_TEXT_TITLE;
			$this->description = MODULE_SHIPPING_RMAMISFSMALLPACKET_TEXT_DESCRIPTION;
			$this->sort_order = MODULE_SHIPPING_RMAMISFSMALLPACKET_SORT_ORDER;
			$this->tax_class = MODULE_SHIPPING_RMAMISFSMALLPACKET_TAX_CLASS;
			$this->enabled = ((MODULE_SHIPPING_RMAMISFSMALLPACKET_STATUS == 'True') ? true : false);
		}

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
        $countries_table = constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_' . $i);

        $country_zones = split("[,]", preg_replace('/\s*/','',$countries_table) );

        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
      }
	   //12 FEB 04 MBeedell	NO specified country (or *) then use this zone for all shipping rates
      if ($dest_zone == 0) {
		for ($i=1; $i<=$this->num_zones; $i++) {
		  $countries_table = constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_' . $i);
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

        $zones_cost = constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST0_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST1_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST2_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST3_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST4_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST5_' . $dest_zone);

        $zones_table = split("[:,]" , preg_replace('/\s*/','',$zones_cost) );
        $size = sizeof($zones_table);
        for ($i=0; $i<$size; $i+=2) {
          if ($shippingWeight <= $zones_table[$i]) { 
            $shipping = $zones_table[$i+1];
			//12 Feb 04 MBeedell - correctly format the total weight... if the weight exceeds the max
			//  weight, then it is divided down over a number of separate packages - so the weight could end
			//  up being a long fraction.

            $sw_text = number_format($shippingWeight, 3, $currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);


            $shipping_method = MODULE_SHIPPING_RMAMISFSMALLPACKET_TEXT_WAY . ' ' . $dest_country . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RMAMISFSMALLPACKET_TEXT_UNITS;
            $shipping_method = MODULE_SHIPPING_RMAMISFSMALLPACKET_TEXT_WAY . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RMAMISFSMALLPACKET_TEXT_UNITS;
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
          $shipping_method = MODULE_SHIPPING_RMAMISFSMALLPACKET_UNDEFINED_RATE;
          //$shipping_method = $zones_cost; 	   //12 FEB 04 MBeedell	useful for debug-print out the rates list!
        } else {
          $shipping_cost = ($shipping * $shippingNumBoxes) + constant('MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_HANDLING_' . $dest_zone);
        }
      }

      $this->quotes = array('id' => $this->code,
//                            'module' => MODULE_SHIPPING_RMAMISFSMALLPACKET_TEXT_TITLE ,
                            'module' => 'RM <span style="color:red">International</span> <span style="color: #555555">Signed For</span>&reg; <i style="font-weight: normal">&quot;small packet&quot;</i>' ,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $shipping_method,
                                                     'cost' => $shipping_cost)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
      }

		if (zen_not_null($this->icon)) {
			$this->quotes['icon'] = $this->icon;
		}

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_RMAMISFSMALLPACKET_INVALID_ZONE;

      return $this->quotes;
    }

    function check() {
    global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
    global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Zones Method', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_STATUS', 'True', 'You must enable Zone shipping for this module to work', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");

// European Rates

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('European Handling Fee', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_HANDLING_1', '0', 'The amount it costs you to package the items for European &quot;Signed For&reg;&quot; delivery.', '6', '0', now())");

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Royal Mail defined European Countries', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_1', 'AL, AD, AM, AT, AZ, BA, BE, BG, BY, CY, CZ, DE, DK, EE, ES, FI, FO, FR, GI, GL, GR, HU, HR, IS, IE, IT, KZ, LI, LT, LU, LV, MK, MT, PL, PT, SE, SI, SK, SM, RU, UZ, UA, TM, TR, TJ, CH, MD, MC', 'two character ISO country codes for Europe.', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('European &quot;Signed For&reg;&quot; rates from GB &amp; Northern Ireland', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST0_1', '0.1:4.6, 0.12:4.71, 0.14:4.83, 0.16:4.95, 0.18:5.07, 0.2:5.19, 0.22:5.3, 0.24:5.7, 0.26:5.62, 0.28:5.73, 0.3:5.79, 0.32:5.79, 0.34:5.89, 0.36:5.99, 0.38:6.09, 0.4:6.19', 'Correct on 13<sup>th</sup> September 2006, from information published August 2006. <br />Example: 0.1:4.6 means weights less than or equal to 0.1 Kg would cost &pound;4.60.', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST1_1', '0.42:6.29, 0.44:6.39, 0.46:6.49, 0.48:6.59, 0.5:6.69, 0.52:6.79, 0.54:6.89, 0.56:6.99, 0.58:7.09, 0.6:7.19, 0.62:7.29, 0.64:7.39, 0.66:7.49, 0.68:7.59, 0.7:7.69, 0.72:7.79', 'European Rates cont\'d (2):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST2_1', '0.74:7.89, 0.76:7.99, 0.78:8.09, 0.8:8.19, 0.82:8.29, 0.84:8.39, 0.86:8.49, 0.88:8.59, 0.9:8.69, 0.92:8.79, 0.94:8.89, 0.96:8.99, 0.98:9.09, 1:9.19, 1.02:9.29, 1.04:9.39', 'European Rates cont\'d (3):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST3_1', '1.06:9.49, 1.08:9.59, 1.1:9.69, 1.12:9.79, 1.14:9.89, 1.16:9.99, 1.18:10.09, 1.2:10.19, 1.22:10.29, 1.24:10.39, 1.26:10.49, 1.28:10.59, 1.3:10.69, 1.32:10.79, 1.34:10.89, 1.36:10.99', 'European Rates cont\'d (4):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST4_1', '1.38:11.09, 1.4:11.19, 1.42:11.29, 1.44:11.39, 1.46:11.49, 1.48:11.59, 1.5:11.69, 1.52:11.79, 1.54:11.89, 1.56:11.99, 1.58:12.09, 1.6:12.19, 1.62:12.29, 1.64:12.39, 1.66:12.49, 1.68:12.59', 'European Rates cont\'d (5):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST5_1', '1.7:12.69, 1.72:12.79, 1.74:12.89, 1.76:12.99, 1.78:13.09, 1.8:13.19, 1.82:13.29, 1.84:13.39, 1.86:13.49, 1.88:13.59, 1.9:13.69, 1.92:13.79, 1.94:13.89, 1.96:13.99, 1.98:14.09, 2:14.19', 'European Rates cont\'d (6):', '6', '0', 'zen_cfg_textarea(', now())");

// WORLDWIDE RATES

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Worldwide Handling Fee', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_HANDLING_2', '0', 'The amount it costs you to package the items for Worldwide &quot;Signed For&reg;&quot; delivery.', '6', '1', now())");

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Royal Mail defined World Zones 1 &amp; 2 Countries', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_2', 'AX, AF, DZ, AS, AO, AQ,AI, AG, AR, AW, AU, BS, BH, BD, BB, BZ, BJ, BM, BT, BO, BW, BV, BR, IO, BN, BF, BI, KH, CM, CA, CV, KY, CF, TD, CB, CL, CN, CX, CC, CO, KM, CG, CK, CR, CI, CU, DJ, DM, DO, TP, EC, EG, SV, GQ, ER, ET, FK, FJ, FX, GF, PF, TF, GA, GM, GE, GH, GI, GD, GP, GU, GT, GN, GW, GY, HT, HM, HN, HK, IN, ID, IR, IQ, IL, JM, JP, JO, KE, KI, KP, KR, CS, KW, KG, LA, LB, LS, LR, LY, MO, MG, MW, MY, MV, ML, MH, MQ, MR, MU, YT, MX, FM, MN, CS, MS, MA, MZ, MM, NA, NR, NP, NL, AN, NC, NZ, NI, NE, NG, NU, NF, MP, NO, OM, PK, PW, PA, PG, PY, PE, PH, PN, PR, QA, RE, IE, RO, RW, KN, LC, VC, WS, ST, SA, SN, CS, SC, CL, SG, SB, SO, ZA, GS, ES, LK, SH, PM, SD, SR, SJ, SZ, CH, SY, TW, TH, TG, TK, TO, TT, TN, TC, TV, UG, AE, US, UM, UY, VU, VA, VE, VN, VG, VI, WF, EH, YE, YU, ZR, ZM, ZW', 'two character ISO country codes for the Rest of the World.', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Worldwide &quot;Signed For&reg;&quot; rates from GB &amp; Northern Ireland', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST0_2', '0.1:4.96, 0.12:5.16, 0.14:5.37, 0.16:5.57, 0.18:5.77, 0.2:5.98, 0.22:6.17, 0.24:6.36, 0.26:6.55, 0.28:6.74, 0.3:6.93, 0.32:7.12, 0.34:7.31, 0.36:7.5, 0.38:7.69, 0.4:7.88', 'Correct on 13<sup>th</sup> September 2006, from information published August 2006. <br />Example: 0.1:4.96 means weights less than or equal to 0.1 Kg would cost &pound;4.96.', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST1_2', '0.42:8.07, 0.44:8.26, 0.46:8.45, 0.48:8.64, 0.5:8.83, 0.52:9.02, 0.54:9.21, 0.56:9.4, 0.58:9.59, 0.6:9.78, 0.62:9.97, 0.64:10.16, 0.66:10.35, 0.68:10.54, 0.7:10.73, 0.72:10.92', 'Worldwide Rates cont\'d (2):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST2_2', '0.74:11.11, 0.76:11.3, 0.78:11.49, 0.8:11.68, 0.82:11.87, 0.84:12.06, 0.86:12.25, 0.88:12.44, 0.9:12.63, 0.92:12.82, 0.94:13.01, 0.96:13.2, 0.98:13.39, 1:13.58, 1.02:13.77, 1.04:13.96', 'Worldwide Rates cont\'d (3):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST3_2', '1.06:14.15, 1.08:14.34, 1.1:14.53, 1.12:14.72, 1.14:14.91, 1.16:15.1, 1.18:15.29, 1.2:15.48, 1.22:15.67, 1.24:15.86, 1.26:16.05, 1.28:16.24, 1.3:16.43, 1.32:16.62, 1.34:16.81, 1.36:17', 'Worldwide Rates cont\'d (4):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST4_2', '1.38:17.19, 1.4:17.38, 1.42:17.57, 1.44:17.76, 1.46:17.95, 1.48:18.14, 1.5:18.33, 1.52:18.52, 1.54:18.71, 1.56:18.9, 1.58:19.09, 1.6:19.28, 1.62:19.47, 1.64:19.66, 1.66:19.85, 1.68:20.04', 'Worldwide Rates cont\'d (5):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST5_2', '1.7:20.23, 1.72:20.42, 1.74:20.61, 1.76:20.8, 1.78:20.99, 1.8:21.18, 1.82:21.37, 1.84:21.56, 1.86:21.75, 1.88:21.94, 1.9:22.13, 1.92:22.32, 1.94:22.51, 1.96:22.7, 1.98:22.89, 2:23.09', 'Worldwide Rates cont\'d (6):', '6', '1', 'zen_cfg_textarea(', now())");

    }

    function remove() {
    global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
       $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key IN ( 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_1', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_2') ");
    }

    function keys() {
        $keys = array('MODULE_SHIPPING_RMAMISFSMALLPACKET_STATUS', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_TAX_CLASS', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_SORT_ORDER', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_1', 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COUNTRIES_2');

        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_HANDLING_1';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST0_1';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST1_1';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST2_1';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST3_1';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST4_1';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST5_1';

        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_HANDLING_2';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST0_2';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST1_2';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST2_2';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST3_2';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST4_2';
        $keys[] = 'MODULE_SHIPPING_RMAMISFSMALLPACKET_ZONES_COST5_2';

      return $keys;
    }
  }
?>

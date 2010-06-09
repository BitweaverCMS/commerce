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

  class rmamassmallpacket  {
    var $code, $title, $description, $enabled, $num_zones ;


// class constructor
    function rmamassmallpacket () {
      $this->code = 'rmamassmallpacket';
      $this->icon = 'shipping_ukamas';
		if( defined( 'MODULE_SHIPPING_RMAMASSMALLPACKET_STATUS' ) ) {
			$this->title = MODULE_SHIPPING_RMAMASSMALLPACKET_TEXT_TITLE;
			$this->description = MODULE_SHIPPING_RMAMASSMALLPACKET_TEXT_DESCRIPTION;
			$this->sort_order = MODULE_SHIPPING_RMAMASSMALLPACKET_SORT_ORDER;
			$this->tax_class = MODULE_SHIPPING_RMAMASSMALLPACKET_TAX_CLASS;
			$this->enabled = ((MODULE_SHIPPING_RMAMASSMALLPACKET_STATUS == 'True') ? true : false);
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
        $countries_table = constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_' . $i);

        $country_zones = split("[,]", preg_replace('/\s*/','',$countries_table) );

        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
      }
	   //12 FEB 04 MBeedell	NO specified country (or *) then use this zone for all shipping rates
      if ($dest_zone == 0) {
		for ($i=1; $i<=$this->num_zones; $i++) {
		  $countries_table = constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_' . $i);
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

        $zones_cost = constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST0_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST1_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST2_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST3_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST4_' . $dest_zone)
           . ',' . constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST5_' . $dest_zone);

        $zones_table = split("[:,]" , preg_replace('/\s*/','',$zones_cost) );
        $size = sizeof($zones_table);
        for ($i=0; $i<$size; $i+=2) {
          if ($shippingWeight <= $zones_table[$i]) { 
            $shipping = $zones_table[$i+1];
			//12 Feb 04 MBeedell - correctly format the total weight... if the weight exceeds the max
			//  weight, then it is divided down over a number of separate packages - so the weight could end
			//  up being a long fraction.

            $sw_text = number_format($shippingWeight, 3, $currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);


            $shipping_method = MODULE_SHIPPING_RMAMASSMALLPACKET_TEXT_WAY . ' ' . $dest_country . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RMAMASSMALLPACKET_TEXT_UNITS;
            $shipping_method = MODULE_SHIPPING_RMAMASSMALLPACKET_TEXT_WAY . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RMAMASSMALLPACKET_TEXT_UNITS;
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
          $shipping_method = MODULE_SHIPPING_RMAMASSMALLPACKET_UNDEFINED_RATE;
          //$shipping_method = $zones_cost; 	   //12 FEB 04 MBeedell	useful for debug-print out the rates list!
        } else {
          $shipping_cost = ($shipping * $shippingNumBoxes) + constant('MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_HANDLING_' . $dest_zone);
        }
      }

      $this->quotes = array('id' => $this->code,
//                            'module' => MODULE_SHIPPING_RMAMASSMALLPACKET_TEXT_TITLE ,
                            'module' => 'Royal Mail Airsure&reg; <i style="font-weight: normal">&quot;small packet&quot;</i>' ,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $shipping_method,
                                                     'cost' => $shipping_cost)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
      }

		if (zen_not_null($this->icon)) {
			$this->quotes['icon'] = $this->icon;
		}

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_RMAMASSMALLPACKET_INVALID_ZONE;

      return $this->quotes;
    }

    function check() {
    global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_RMAMASSMALLPACKET_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
    global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Zones Method', 'MODULE_SHIPPING_RMAMASSMALLPACKET_STATUS', 'True', 'You must enable Zone shipping for this module to work', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_RMAMASSMALLPACKET_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_RMAMASSMALLPACKET_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");

// European Rates

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('European Handling Fee', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_HANDLING_1', '0', 'The amount it costs you to package the items for European Airsure&reg; delivery.', '6', '0', now())");

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Royal Mail defined European Countries', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_1', 'AD, AT, BE, CH, DE, DK, ES, FI, FO, FR, IE, IS, LI, LU, MC, NL, NO, PT, SE, SK', 'two character ISO country codes for European Airsure&reg; destinations. <i>(note that Airsure&reg; is only for a limited range of 24 countries, some of which are defined as territories such as Corsica being under France !)</i>', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('European Airsure&reg; rates from GB &amp; Northern Ireland', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST0_1', '0.1:5.3, 0.12:5.41, 0.14:5.53, 0.16:5.65, 0.18:5.77, 0.2:5.89, 0.22:6, 0.24:6.4, 0.26:6.32, 0.28:6.43, 0.3:6.49, 0.32:6.49, 0.34:6.59, 0.36:6.69, 0.38:6.79, 0.4:6.89', 'Correct on 13<sup>th</sup> September 2006, from information published August 2006. <br />Example: 0.1:5.3 means weights less than or equal to 0.1 Kg would cost &pound;5.30.', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST1_1', '0.42:6.99, 0.44:7.09, 0.46:7.19, 0.48:7.29, 0.5:7.39, 0.52:7.49, 0.54:7.59, 0.56:7.69, 0.58:7.79, 0.6:7.89, 0.62:7.99, 0.64:8.09, 0.66:8.19, 0.68:8.29, 0.7:8.39, 0.72:8.49', 'European Rates cont\'d (2):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST2_1', '0.74:8.59, 0.76:8.69, 0.78:8.79, 0.8:8.89, 0.82:8.99, 0.84:9.09, 0.86:9.19, 0.88:9.29, 0.9:9.39, 0.92:9.49, 0.94:9.59, 0.96:9.69, 0.98:9.79, 1:9.89, 1.02:9.99, 1.04:10.09', 'European Rates cont\'d (3):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST3_1', '1.06:10.19, 1.08:10.29, 1.1:10.39, 1.12:10.49, 1.14:10.59, 1.16:10.69, 1.18:10.79, 1.2:10.89, 1.22:10.99, 1.24:11.09, 1.26:11.19, 1.28:11.29, 1.3:11.39, 1.32:11.49, 1.34:11.59, 1.36:11.69', 'European Rates cont\'d (4):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST4_1', '1.38:11.79, 1.4:11.89, 1.42:11.99, 1.44:12.09, 1.46:12.19, 1.48:12.29, 1.5:12.39, 1.52:12.49, 1.54:12.59, 1.56:12.69, 1.58:12.79, 1.6:12.89, 1.62:12.99, 1.64:13.09, 1.66:13.19, 1.68:13.29', 'European Rates cont\'d (5):', '6', '0', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST5_1', '1.7:13.39, 1.72:13.49, 1.74:13.59, 1.76:13.69, 1.78:13.79, 1.8:13.89, 1.82:13.99, 1.84:14.09, 1.86:14.19, 1.88:14.29, 1.9:14.39, 1.92:14.49, 1.94:14.59, 1.96:14.69, 1.98:14.79, 2:14.89', 'European Rates cont\'d (6):', '6', '0', 'zen_cfg_textarea(', now())");

// WORLDWIDE RATES

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Worldwide Handling Fee', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_HANDLING_2', '0', 'The amount it costs you to package the items for Worldwide Airsure&reg; delivery.', '6', '1', now())");

      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Royal Mail defined World Zones 1 &amp; 2 Countries', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_2', 'NZ, US', 'two character ISO country codes for the Rest of the World. <i>(note that Airsure&reg; is only for a limited range of 24 countries, some of which are defined as territories such as Corsica being under France !)</i>', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Worldwide Airsure&reg; rates from GB &amp; Northern Ireland', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST0_2', '0.1:5.66, 0.12:5.86, 0.14:6.07, 0.16:6.27, 0.18:6.47, 0.2:6.68, 0.22:6.87, 0.24:7.06, 0.26:7.25, 0.28:7.44, 0.3:7.63, 0.32:7.82, 0.34:8.01, 0.36:8.2, 0.38:8.39, 0.4:8.58', 'Correct on 13<sup>th</sup> September 2006, from information published August 2006. <br />Example: 0.1:5.66 means weights less than or equal to 0.1 Kg would cost &pound;5.66.', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST1_2', '0.42:8.77, 0.44:8.96, 0.46:9.15, 0.48:9.34, 0.5:9.53, 0.52:9.72, 0.54:9.91, 0.56:10.1, 0.58:10.29, 0.6:10.48, 0.62:10.67, 0.64:10.86, 0.66:11.05, 0.68:11.24, 0.7:11.43, 0.72:11.62', 'Worldwide Rates cont\'d (2):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST2_2', '0.74:11.81, 0.76:12, 0.78:12.19, 0.8:12.38, 0.82:12.57, 0.84:12.76, 0.86:12.95, 0.88:13.14, 0.9:13.33, 0.92:13.52, 0.94:13.71, 0.96:13.9, 0.98:14.09, 1:14.28, 1.02:14.47, 1.04:14.66', 'Worldwide Rates cont\'d (3):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST3_2', '1.06:14.85, 1.08:15.04, 1.1:15.23, 1.12:15.42, 1.14:15.61, 1.16:15.8, 1.18:15.99, 1.2:16.18, 1.22:16.37, 1.24:16.56, 1.26:16.75, 1.28:16.94, 1.3:17.13, 1.32:17.32, 1.34:17.51, 1.36:17.7', 'Worldwide Rates cont\'d (4):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST4_2', '1.38:17.89, 1.4:18.08, 1.42:18.27, 1.44:18.46, 1.46:18.65, 1.48:18.84, 1.5:19.03, 1.52:19.22, 1.54:19.41, 1.56:19.6, 1.58:19.79, 1.6:19.98, 1.62:20.17, 1.64:20.36, 1.66:20.55, 1.68:20.74', 'Worldwide Rates cont\'d (5):', '6', '1', 'zen_cfg_textarea(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST5_2', '1.7:20.93, 1.72:21.12, 1.74:21.31, 1.76:21.5, 1.78:21.69, 1.8:21.88, 1.82:22.07, 1.84:22.26, 1.86:22.45, 1.88:22.64, 1.9:22.83, 1.92:23.02, 1.94:23.21, 1.96:23.4, 1.98:23.59, 2:23.79', 'Worldwide Rates cont\'d (6):', '6', '1', 'zen_cfg_textarea(', now())");

    }

    function remove() {
    global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
       $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key IN ( 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_1', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_2') ");
    }

    function keys() {
        $keys = array('MODULE_SHIPPING_RMAMASSMALLPACKET_STATUS', 'MODULE_SHIPPING_RMAMASSMALLPACKET_TAX_CLASS', 'MODULE_SHIPPING_RMAMASSMALLPACKET_SORT_ORDER', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_1', 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COUNTRIES_2');

        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_HANDLING_1';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST0_1';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST1_1';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST2_1';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST3_1';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST4_1';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST5_1';

        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_HANDLING_2';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST0_2';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST1_2';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST2_2';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST3_2';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST4_2';
        $keys[] = 'MODULE_SHIPPING_RMAMASSMALLPACKET_ZONES_COST5_2';

      return $keys;
    }
  }
?>

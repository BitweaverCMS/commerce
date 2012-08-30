<?php
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
  class rm1stpacket {
    var $code, $title, $description, $enabled, $num_zones ;


// class constructor
    function rm1stpacket() {
      $this->code = 'rm1stpacket';
      $this->icon = 'shipping_ukrm';
		if( defined( 'MODULE_SHIPPING_RM1STPACKET_STATUS' ) ) {
			$this->title = MODULE_SHIPPING_RM1STPACKET_TEXT_TITLE;
			$this->description = MODULE_SHIPPING_RM1STPACKET_TEXT_DESCRIPTION;
			$this->sort_order = MODULE_SHIPPING_RM1STPACKET_SORT_ORDER;
			$this->tax_class = MODULE_SHIPPING_RM1STPACKET_TAX_CLASS;
			$this->enabled = ((MODULE_SHIPPING_RM1STPACKET_STATUS == 'True') ? true : false);
		}

      // CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
      $this->num_zones = 1;
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
        $countries_table = constant('MODULE_SHIPPING_RM1STPACKET_ZONES_COUNTRIES_' . $i);
        $country_zones = preg_split("#[,]#", $countries_table);
        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
      }
	   //12 FEB 04 MBeedell	NO specified country (or *) then use this zone for all shipping rates
      if ($dest_zone == 0) {
		for ($i=1; $i<=$this->num_zones; $i++) {
		  $countries_table = constant('MODULE_SHIPPING_RM1STPACKET_ZONES_COUNTRIES_' . $i);
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

        $zones_cost = constant('MODULE_SHIPPING_RM1STPACKET_ZONES_COST0_' . $dest_zone);

        $zones_table = preg_split("#[:,]#" , preg_replace('/\s/','',$zones_cost) );
        $size = sizeof($zones_table);
        for ($i=0; $i<$size; $i+=2) {
          if ($shipping_weight <= $zones_table[$i]) {
            $shipping = $zones_table[$i+1];
			//12 Feb 04 MBeedell - correctly format the total weight... if the weight exceeds the max
			//  weight, then it is divided down over a number of separate packages - so the weight could end
			//  up being a long fraction.

            $sw_text = number_format($shipping_weight, 3, $currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);


            $shipping_method = MODULE_SHIPPING_RM1STPACKET_TEXT_WAY . ' ' . $dest_country . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RM1STPACKET_TEXT_UNITS;
            $shipping_method = MODULE_SHIPPING_RM1STPACKET_TEXT_WAY . ' : ' . $sw_text . ' ' . MODULE_SHIPPING_RM1STPACKET_TEXT_UNITS;
			//12 Feb 04 MBeedell - if weight is over the max, then show the number of boxes being shipped
            if ($shipping_num_boxes > 1) {
	            $sw_text = number_format($shipping_num_boxes, 0, $currency['decimal_point'], $currency['thousands_point']);
                $sw_text = number_format($shipping_weight, 0, $currencies->currencies[DEFAULT_CURRENCY]['decimal_point'], $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']);
				$shipping_method = $shipping_method . ' in ' . $sw_text . ' boxes ';
            }
            break;
          }
        }

        if ($shipping == -1) {
          $shipping_cost = 0;
          $shipping_method = MODULE_SHIPPING_RM1STPACKET_UNDEFINED_RATE;
          //$shipping_method = $zones_cost; 	   //12 FEB 04 MBeedell	useful for debug-print out the rates list!
        } else {
          $shipping_cost = ($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_RM1STPACKET_ZONES_HANDLING_' . $dest_zone);
        }
      }

      $this->quotes = array('id' => $this->code,
//                            'module' => MODULE_SHIPPING_RM1STPACKET_TEXT_TITLE ,
                            'module' => 'Royal Mail 1<sup>st</sup> class <i style="font-weight: normal">&quot;packet&quot;</i>' ,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $shipping_method,
                                                     'cost' => $shipping_cost)));

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
      }

		if (zen_not_null($this->icon)) {
			$this->quotes['icon'] = $this->icon;
		}

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_RM1STPACKET_INVALID_ZONE;

      return $this->quotes;
    }

    function check() {
    global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_RM1STPACKET_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
    global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Zones Method', 'MODULE_SHIPPING_RM1STPACKET_STATUS', 'True', 'You must enable Zone shipping for this module to work', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_RM1STPACKET_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_RM1STPACKET_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_RM1STPACKET_ZONES_HANDLING_1', '0', 'The amount it costs you to package the items for first class delivery.', '6', '0', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 1 Countries', 'MODULE_SHIPPING_RM1STPACKET_ZONES_COUNTRIES_1', 'GB', 'two character ISO country codes for Great Britain and Northern Ireland " . $i . ".', '6', '0', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Shipping rates to GB &amp; Northern Ireland', 'MODULE_SHIPPING_RM1STPACKET_ZONES_COST0_1', '0.1:1.00, 0.25:1.27, 0.5:1.70, 0.75:2.20, 1.0:2.70, 1.25:4.74, 1.5:5.59, 1.75:6.44, 2:7.29, 2.25:8.14, 2.5:8.99, 2.75:9.84, 3:10.69, 3.25:11.54, 3.5:12.39, 3.75:13.24, 4:14.09, 4.25:14.94, 4.5:15.79, 4.75:16.64, 5:17.49', 'Correct on 13<sup>th</sup> September 2006, from information published August 2006. <br />Example: 0.1:1.00 means weights less than or equal to 0.1 Kg would cost &pound;1.00.', '6', '0', 'zen_cfg_textarea(', now())");

    }

    function remove() {
    global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_RM1STPACKET_ZONES_COUNTRIES_1'");
    }

    function keys() {
        $keys = array('MODULE_SHIPPING_RM1STPACKET_STATUS', 'MODULE_SHIPPING_RM1STPACKET_TAX_CLASS', 'MODULE_SHIPPING_RM1STPACKET_SORT_ORDER');

        $keys[] = 'MODULE_SHIPPING_RM1STPACKET_ZONES_HANDLING_1';
        $keys[] = 'MODULE_SHIPPING_RM1STPACKET_ZONES_COST0_1';

      return $keys;
    }
  }
?>

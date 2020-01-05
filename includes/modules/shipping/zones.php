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
// $Id$
//
/*

	USAGE
	By default, the module comes with support for 3 zones.  This can be
	easily changed by editing the line below in the zones constructor
	that defines $this->num_zones.

	Next, you will want to activate the module by going to the Admin screen,
	clicking on Modules, then clicking on Shipping.  A list of all shipping
	modules should appear.  Click on the green dot next to the one labeled
	zones.php.  A list of settings will appear to the right.  Click on the
	Edit button.

	PLEASE NOTE THAT YOU WILL LOSE YOUR CURRENT SHIPPING RATES AND OTHER
	SETTINGS IF YOU TURN OFF THIS SHIPPING METHOD.  Make sure you keep a
	backup of your shipping settings somewhere at all times.

	If you want an additional handling charge applied to orders that use this
	method, set the Handling Fee field.

	Next, you will need to define which countries are in each zone.  Determining
	this might take some time and effort.  You should group a set of countries
	that has similar shipping charges for the same weight.  For instance, when
	shipping from the US, the countries of Japan, Australia, New Zealand, and
	Singapore have similar shipping rates.  As an example, one of my customers
	is using this set of zones:
		1: USA
		2: Canada
		3: Austria, Belgium, Great Britain, France, Germany, Greenland, Iceland,
			 Ireland, Italy, Norway, Holland/Netherlands, Denmark, Poland, Spain,
			 Sweden, Switzerland, Finland, Portugal, Israel, Greece
		4: Japan, Australia, New Zealand, Singapore
		5: Taiwan, China, Hong Kong

	When you enter these country lists, enter them into the Zone X Countries
	fields, where "X" is the number of the zone.  They should be entered as
	two character ISO country codes in all capital letters.  They should be
	separated by commas with no spaces or other punctuation. For example:
		1: US
		2: CA
		3: AT,BE,GB,FR,DE,GL,IS,IE,IT,NO,NL,DK,PL,ES,SE,CH,FI,PT,IL,GR
		4: JP,AU,NZ,SG
		5: TW,CN,HK

	Now you need to set up the shipping rate tables for each zone.  Again,
	some time and effort will go into setting the appropriate rates.  You
	will define a set of weight ranges and the shipping price for each
	range.  For instance, you might want an order than weighs more than 0
	and less than or equal to 3 to cost 5.50 to ship to a certain zone.
	This would be defined by this:  3:5.5

	You should combine a bunch of these rates together in a comma delimited
	list and enter them into the "Zone X Shipping Table" fields where "X"
	is the zone number.  For example, this might be used for Zone 1:
		1:3.5,2:3.95,3:5.2,4:6.45,5:7.7,6:10.4,7:11.85, 8:13.3,9:14.75,10:16.2,11:17.65,
		12:19.1,13:20.55,14:22,15:23.45

	The above example includes weights over 0 and up to 15.  Note that
	units are not specified in this explanation since they should be
	specific to your locale.

	CAVEATS
	At this time, it does not deal with weights that are above the highest amount
	defined.  This will probably be the next area to be improved with the
	module.  For now, you could have one last very high range with a very
	high shipping rate to discourage orders of that magnitude.  For
	instance:  999:1000

	If you want to be able to ship to any country in the world, you will
	need to enter every country code into the Country fields. For most
	shops, you will not want to enter every country.  This is often
	because of too much fraud from certain places. If a country is not
	listed, then the module will add a $0.00 shipping charge and will
	indicate that shipping is not available to that destination.
	PLEASE NOTE THAT THE ORDER CAN STILL BE COMPLETED AND PROCESSED!

	It appears that the osC shipping system automatically rounds the
	shipping weight up to the nearest whole unit.  This makes it more
	difficult to design precise shipping tables.  If you want to, you
	can hack the shipping.php file to get rid of the rounding.

	Lastly, there is a limit of 255 characters on each of the Zone
	Shipping Tables and Zone Countries.

*/

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );

class zones extends CommercePluginShippingBase {

	function __construct() {
		parent::__construct();
		$this->title = tra( 'Zone Rates' );
		$this->description = tra( 'Zone Based Rates' );;
		// CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
		$this->num_zones = 3;
	}

	function quote( $pShipHash ) {
		if( $quotes = $this->isEligibleShipper( $pShipHash ) ) {

			$dest_zone = NULL;
			$error = false;

			for ($i=1; $i<=$this->num_zones; $i++) {
				$countries_table = constant('MODULE_SHIPPING_ZONES_COUNTRIES_' . $i);
				$country_zones = preg_split("#[,]#", $countries_table);
				if( in_array( $pShipHash['destination']['countries_iso_code_2'], $country_zones ) ) {
					$dest_zone = $i;
					break;
				}
			}

			if ($dest_zone == 0) {
				$error = true;
			} else {
				$shipping = NULL;
				$zones_cost = constant('MODULE_SHIPPING_ZONES_COST_' . $dest_zone);

				$zones_table = preg_split("#[:,]#" , $zones_cost);
				$size = sizeof($zones_table);
				for( $b = 0; $b < $pShipHash['shipping_num_boxes']; $b++ ) {
					for ($i=0; $i<$size; $i+=2) {
						if (MODULE_SHIPPING_ZONES_METHOD == 'Weight') {
							if ($shipping_weight <= $zones_table[$i]) {
								$shipping = $zones_table[$i+1];

								switch (SHIPPING_BOX_WEIGHT_DISPLAY) {
									case (0):
										$show_box_weight = '';
										break;
									case (1):
										$show_box_weight = $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES;
										break;
									case (2):
										$show_box_weight = number_format($shipping_weight * $shipping_num_boxes,2) . MODULE_SHIPPING_ZONES_TEXT_UNITS;
										break;
									default:
										$show_box_weight = $shipping_num_boxes . ' x ' . number_format($shipping_weight,2) . MODULE_SHIPPING_ZONES_TEXT_UNITS;
										break;
								}

								$shipping_method = MODULE_SHIPPING_ZONES_TEXT_WAY . ' ' . $pShipHash['destination']['countries_iso_code_2'] . ' (' . $show_box_weight . ')';
								break;
							}
						} else {
							if ( $pShipHash['shipping_value'] <= $zones_table[$i]) {
								$shipping += $zones_table[$i+1];
								$shipping_method = tra( 'Shipping to' ) . ' ' . $pShipHash['destination']['countries_iso_code_2'];
								break;
							}
						}
					}
				}

				if( is_null( $shipping ) ) {
					$shipping_cost = 0;
					$shipping_method = tra( 'The shipping rate cannot be determined at this time' );
				} else {
					if (MODULE_SHIPPING_ZONES_METHOD == 'Weight') {
						$shipping_cost = ($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone);
					} else {
						// don't charge per box when done by Price
						$shipping_cost = ($shipping) + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone);
					}
				}
			}

			$quotes['methods'][] =	array(
										array(
											'id' => $this->code,
											'title' => $shipping_method,
											'cost' => $shipping_cost
										)
									);
		}

		return $quotes;
	}

	function install() {
		if( !$this->isInstalled() ) {
			$this->mDb->StartTrans();
			parent::install();
			$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) VALUES ('Calculation Method', 'MODULE_SHIPPING_ZONES_METHOD', 'Weight', 'Calculate cost based on Weight or Price?', '6', '0', 'zen_cfg_select_option(array('Weight', 'Price'), '', now())");
			for ($i = 1; $i <= $this->num_zones; $i++) {
				$default_countries = '';
				if ($i == 1) {
					$default_countries = 'US,CA';
				}
				$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Zone " . $i ." Countries', 'MODULE_SHIPPING_ZONES_COUNTRIES_" . $i ."', '" . $default_countries . "', 'Comma separated list of two character ISO country codes that are part of Zone " . $i . ".', '6', '0', 'zen_cfg_textarea(', now())");
				$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Zone " . $i ." Shipping Table', 'MODULE_SHIPPING_ZONES_COST_" . $i ."', '3:8.50,7:10.50,99:20.00', 'Shipping rates to Zone " . $i . " destinations based on a group of maximum order weights/prices. Example: 3:8.50,7:10.50,... Weight/Price less than or equal to 3 would cost 8.50 for Zone " . $i . " destinations.', '6', '0', 'zen_cfg_textarea(', now())");
				$this->mDb->query("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Zone " . $i ." Handling Fee', 'MODULE_SHIPPING_ZONES_HANDLING_" . $i."', '0', 'Handling Fee for this shipping zone', '6', '0', now())");
			}
		}
	}

	function keys() {
		$keys = array_merge( parent::keys(), array(
			'MODULE_SHIPPING_ZONES_METHOD',
		) );

		for ($i=1; $i<=$this->num_zones; $i++) {
			$keys[] = 'MODULE_SHIPPING_ZONES_COUNTRIES_' . $i;
			$keys[] = 'MODULE_SHIPPING_ZONES_COST_' . $i;
			$keys[] = 'MODULE_SHIPPING_ZONES_HANDLING_' . $i;
		}

		return $keys;
	}
}

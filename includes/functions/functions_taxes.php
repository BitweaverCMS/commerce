<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
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
// $Id: functions_taxes.php,v 1.4 2005/08/03 15:35:15 spiderr Exp $
//
//
////
// Returns the tax rate for a zone / class
// TABLES: tax_rates, zones_to_geo_zones
  function zen_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    global $db, $gBitUser;
    global $customer_zone_id, $customer_country_id;

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if( !$gBitUser->isRegistered() ) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } elseif( !empty( $_SESSION['customer_country_id'] ) && !empty( $_SESSION['customer_zone_id'] ) ) {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id = $_SESSION['customer_zone_id'];
      } else {
        $country_id = $customer_country_id;
        $zone_id = $customer_zone_id;
	  }
    }


    if (STORE_PRODUCT_TAX_BASIS == 'Store') {
      if ($zone_id != STORE_ZONE) return 0;
    }

	if( !empty( $country_id ) && !empty( $zone_id ) ) {
		$tax_query = "select sum(tax_rate) as tax_rate
					from " . TABLE_TAX_RATES . " tr
					left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
					left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id)
					where (za.zone_country_id is null
					or za.zone_country_id = '0'
					or za.zone_country_id = '" . (int)$country_id . "')
					and (za.zone_id is null
					or za.zone_id = '0'
					or za.zone_id = '" . (int)$zone_id . "')
					and tr.tax_class_id = '" . (int)$class_id . "'
					group by tr.tax_priority";

		$tax = $db->Execute($tax_query);

		if ($tax->RecordCount() > 0) {
		$tax_multiplier = 1.0;
		while (!$tax->EOF) {
			$tax_multiplier *= 1.0 + ($tax->fields['tax_rate'] / 100);
			$tax->MoveNext();
		}
		return ($tax_multiplier - 1.0) * 100;
		} else {
		return 0;
		}
	}
  }

////
// Return the tax description for a zone / class
// TABLES: tax_rates;
  function zen_get_tax_description($class_id, $country_id, $zone_id) {
    global $db;
    $tax_query = "select tax_description
                  from " . TABLE_TAX_RATES . " tr
                  left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id)
                  left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id)
                  where (za.zone_country_id is null or za.zone_country_id = '0'
                  or za.zone_country_id = '" . (int)$country_id . "')
                  and (za.zone_id is null
                  or za.zone_id = '0'
                  or za.zone_id = '" . (int)$zone_id . "')
                  and tr.tax_class_id = '" . (int)$class_id . "'
                  order by tr.tax_priority";

    $tax = $db->Execute($tax_query);

    if ($tax->RecordCount() > 0) {
      $tax_description = '';
      while (!$tax->EOF) {
        $tax_description .= $tax->fields['tax_description'] . ' + ';
        $tax->MoveNext();
      }
      $tax_description = substr($tax_description, 0, -3);

      return $tax_description;
    } else {
      return TEXT_UNKNOWN_TAX_RATE;
    }
  }

////
// Output the tax percentage with optional padded decimals
  function zen_display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
    if (strpos($value, '.')) {
      $loop = true;
      while ($loop) {
        if (substr($value, -1) == '0') {
          $value = substr($value, 0, -1);
        } else {
          $loop = false;
          if (substr($value, -1) == '.') {
            $value = substr($value, 0, -1);
          }
        }
      }
    }

    if ($padding > 0) {
      if ($decimal_pos = strpos($value, '.')) {
        $decimals = strlen(substr($value, ($decimal_pos+1)));
        for ($i=$decimals; $i<$padding; $i++) {
          $value .= '0';
        }
      } else {
        $value .= '.';
        for ($i=0; $i<$padding; $i++) {
          $value .= '0';
        }
      }
    }

    return $value;
  }

////
// Get tax rate from tax description
 function zen_get_tax_rate_from_desc($tax_desc) {
    global $db;
    $tax_rate = 0.00;

    $tax_descriptions = explode(' + ', $tax_desc);
    foreach ($tax_descriptions as $tax_description) {
      $tax_query = "select tax_rate
                  from " . TABLE_TAX_RATES . "
                  where tax_description = '" .
                  $tax_description . "'";

      $tax = $db->Execute($tax_query);

      $tax_rate += $tax->fields['tax_rate'];
    }

    return $tax_rate;
  }

 function zen_get_tax_locations($store_country = -1, $store_zone = -1) {
  global $db;
    switch (STORE_PRODUCT_TAX_BASIS) {

      case 'Shipping':
        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.address_book_id = '" . (int)$_SESSION['sendto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);
      break;
      case 'Billing':

        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.address_book_id = '" . (int)$_SESSION['billto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);
      break;
      case 'Store':
        $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                from " . TABLE_ADDRESS_BOOK . " ab
                                left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                and ab.address_book_id = '" . (int)$_SESSION['billto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);

        if ($tax_address_result ->fields['entry_zone_id'] == STORE_ZONE) {

        } else {
          $tax_address_query = "select ab.entry_country_id, ab.entry_zone_id
                                  from " . TABLE_ADDRESS_BOOK . " ab
                                  left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id)
                                  where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "'
                                  and ab.address_book_id = '" . (int)$_SESSION['sendto'] . "'";
        $tax_address_result = $db->Execute($tax_address_query);
       }
     }
     $tax_address['zone_id'] = $tax_address_result->fields['entry_zone_id'];
     $tax_address['country_id'] = $tax_address_result->fields['entry_country_id'];
     return $tax_address;
 }
?>
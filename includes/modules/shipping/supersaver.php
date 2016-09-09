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

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginShippingBase.php' );


  class supersaver extends CommercePluginShippingBase {
    var $code, $title, $description, $icon, $enabled;

// class constructor
    function supersaver() {
      global $order, $gBitDb;

      $this->code = 'supersaver';
      $this->title = tra( 'SuperSaver Shipping' );
      $this->description = tra( 'Offer fixed rate (or free!) shipping for orders within a specified amount.' );
      $this->sort_order = 1;
      $this->icon = 'shipping_supersaver';
      $this->tax_class = MODULE_SHIPPING_SUPERSAVER_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_SUPERSAVER_STATUS == 'True') ? true : false);
	$this->quotes = array();

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_SUPERSAVER_ZONE > 0) ) {
        $check_flag = false;
        $check = $gBitDb->Execute("select `zone_id` from " . TABLE_ZONES_TO_GEO_ZONES . " where `geo_zone_id` = '" . MODULE_SHIPPING_SUPERSAVER_ZONE . "' and `zone_country_id` = '" . $order->delivery['country']['countries_id'] . "' order by `zone_id`");
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
    }

// class methods
	function quote( $pShipHash = array() ) {
		global $order, $currencies;

		$this->quotes['id'] = $this->code;
		$this->quotes['module'] = tra('SuperSaver Shipping');
		$this->quotes['icon'] = $this->icon;

		$min = MODULE_SHIPPING_SUPERSAVER_MIN;
		$max = MODULE_SHIPPING_SUPERSAVER_MAX;
		if( !empty( $min ) && $order->subtotal < MODULE_SHIPPING_SUPERSAVER_MIN ) {
			$this->quotes['error'] = tra( 'You must spend at least '. $currencies->format( MODULE_SHIPPING_SUPERSAVER_MIN ).' to get SuperSaver Shipping.' ). ' <a href="'.zen_href_link(FILENAME_SHOPPING_CART).'">'.tra( 'Update Cart' ).'</a>';
		} elseif( !empty( $max ) && $order->subtotal > MODULE_SHIPPING_SUPERSAVER_MAX ) {
			// no quote for you!
			$this->quotes['error'] = tra( 'SuperSaver Shipping only applies to orders up to '.$currencies->format( MODULE_SHIPPING_SUPERSAVER_MAX ) ). ' <a href="'.zen_href_link(FILENAME_SHOPPING_CART).'">'.tra( 'Update Cart' ).'</a>';
		} else {
			$this->quotes['note'] = tra( MODULE_SHIPPING_SUPERSAVER_DESC );
			if( SHIPPING_ORIGIN_COUNTRY == $order->delivery['country']['countries_id'] && MODULE_SHIPPING_SUPERSAVER_DOMESTIC == 'True' ) {
				$desc = tra( MODULE_SHIPPING_SUPERSAVER_DESC ).' '.tra( MODULE_SHIPPING_SUPERSAVER_DOMESTIC_DESC );
				$this->quotes['methods'] = array(array('id' => $this->code,
											'title' => trim( $desc ),
											'code' => 'supersaver',
											'cost' => MODULE_SHIPPING_SUPERSAVER_DOMESTIC_COST + MODULE_SHIPPING_SUPERSAVER_HANDLING));
			} elseif( MODULE_SHIPPING_SUPERSAVER_INTL  == 'True' ) {
				$desc = tra( MODULE_SHIPPING_SUPERSAVER_DESC ).' '.tra( MODULE_SHIPPING_SUPERSAVER_INTL_DESC );
				$this->quotes['methods'] = array(array('id' => $this->code,
											'title' => trim( $desc ),
											'code' => 'supersaverintl',
											'cost' => MODULE_SHIPPING_SUPERSAVER_INTL_COST + MODULE_SHIPPING_SUPERSAVER_HANDLING));
			}
			if ($this->tax_class > 0) {
				$this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['countries_id'], $order->delivery['zone_id']);
			}
		}

    	return $this->quotes;
    }

    function check() {
      global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_SHIPPING_SUPERSAVER_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
      global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Enable SuperSaver Shipping', 'MODULE_SHIPPING_SUPERSAVER_STATUS', 'True', 'Do you want to offer SuperSaver shipping?', '7', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Minimum Cart Value', 'MODULE_SHIPPING_SUPERSAVER_MIN', '25.00', 'What is the minimum cart total to get supersaver shipping?', '7', '6', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Maximum Cart Value', 'MODULE_SHIPPING_SUPERSAVER_MAX', '100.00', 'What is the maximum cart total to get supersaver shipping?', '7', '6', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('SuperSaver Shipping Cost', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_COST', '2.99', 'What is the SuperSaver Shipping cost?', '7', '6', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Handling Fee', 'MODULE_SHIPPING_SUPERSAVER_HANDLING', '0', 'Handling fee for this shipping method.', '7', '0', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_SHIPPING_SUPERSAVER_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '7', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('SuperSaver Shipping Description', 'MODULE_SHIPPING_SUPERSAVER_DESC', 'Supersaver Shipping', 'Text to accompany all SuperSaver quotes', '7', '6', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Domestic SuperSaver Shipping', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC', 'True', 'Allow domestic SuperSaver shipping - the same country as the <a href=\"configuration.php?gID=5&cID=123&action=edit\">Default Country</a>.', '7', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Domestic SuperSaver Shipping Description', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_DESC', 'Domestic (1-2 weeks)', 'Text to accompany SuperSaver domestic quote', '7', '6', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('International SuperSaver Shipping', 'MODULE_SHIPPING_SUPERSAVER_INTL', 'True', 'Allow international SuperSaver shipping - countries outside of the <a href=\"configuration.php?gID=5&cID=123&action=edit\">Default Country</a>.', '7', '0', 'zen_cfg_select_option(array(''True'', ''False''), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('International SuperSaver Shipping Description', 'MODULE_SHIPPING_SUPERSAVER_INTL_DESC', 'International (4-8 weeks)', 'Text to accompany SuperSaver international quote', '7', '6', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('SuperSaver Shipping Cost', 'MODULE_SHIPPING_SUPERSAVER_INTL_COST', '9.99', 'What is the SuperSaver Shipping cost?', '7', '6', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Shipping Zone', 'MODULE_SHIPPING_SUPERSAVER_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '7', '0', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_SHIPPING_SUPERSAVER_SORT_ORDER', '0', 'Sort order of display.', '7', '0', now())");
    }

    function remove() {
      global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_SUPERSAVER_STATUS', 'MODULE_SHIPPING_SUPERSAVER_HANDLING', 'MODULE_SHIPPING_SUPERSAVER_TAX_CLASS', 'MODULE_SHIPPING_SUPERSAVER_MIN', 'MODULE_SHIPPING_SUPERSAVER_MAX', 'MODULE_SHIPPING_SUPERSAVER_DESC', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_COST', 'MODULE_SHIPPING_SUPERSAVER_DOMESTIC_DESC', 'MODULE_SHIPPING_SUPERSAVER_INTL', 'MODULE_SHIPPING_SUPERSAVER_INTL_COST', 'MODULE_SHIPPING_SUPERSAVER_INTL_DESC', 'MODULE_SHIPPING_SUPERSAVER_ZONE', 'MODULE_SHIPPING_SUPERSAVER_SORT_ORDER');
    }
  }
?>

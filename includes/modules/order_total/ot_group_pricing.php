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

  class ot_group_pricing {
    var $title, $output;

    function ot_group_pricing() {
      $this->code = 'ot_group_pricing';
      if( defined( 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS' ) ) {
		  $this->title = MODULE_ORDER_TOTAL_GROUP_PRICING_TITLE;
		  $this->description = MODULE_ORDER_TOTAL_GROUP_PRICING_DESCRIPTION;
		  $this->sort_order = MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER;
		  $this->include_shipping = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING;
		  $this->include_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX;
		  $this->calculate_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX;
		  $this->credit_tax = MODULE_ORDER_TOTAL_GROUP_PRICING_CREDIT_TAX;
		  $this->credit_class = true;
      }

      $this->output = array();
    }

    function process() {
      global $gBitDb, $order, $currencies;
      $group_query = $gBitDb->Execute("select customers_group_pricing from " . TABLE_CUSTOMERS . " where `customers_id` = '" . $_SESSION['customer_id'] . "'");
      if ($group_query->fields['customers_group_pricing'] != '0') {
        $group_discount = $gBitDb->Execute("select `group_name`, `group_percentage` from " . TABLE_GROUP_PRICING . " where
                                        `group_id` = '" . $group_query->fields['customers_group_pricing'] . "'");
        $order_total = $this->get_order_total();
        $gift_vouchers = $gBitCustomer->mCart->gv_only();
        $discount = ($order_total - $gift_vouchers) * $group_discount->fields['group_percentage'] / 100;
        $od_amount = zen_round($discount, 2);
        if ($this->calculate_tax != "none") {
          $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax, true);
//          $od_amount = $this->calculate_credit($order_total);
        }
        $this->deduction = $od_amount;
        if ($discount > 0 ) {
          $order->info['total'] -= $this->deduction;
          $this->output[] = array('title' => $this->title . ':',
                                  'text' => '-' . $currencies->format($this->deduction, true, $order->info['currency'], $order->info['currency_value']),
                                  'value' => $this->deduction);
        }
      }
    }

    function get_order_total() {
      global $order;
      $order_total = $order->info['total'];
      if ($this->include_tax == 'false') $order_total = $order_total - $order->info['tax'];
      if ($this->include_shipping == 'false') $order_total = $order_total - $order->info['shipping_cost'];

      return $order_total;
    }

    function calculate_tax_deduction($amount, $od_amount, $method, $finalise = false) {
      global $order;
      $tax_address = zen_get_tax_locations();
      switch ($method) {
        case 'Standard':
        if ($amount == 0) {
          $ratio1 = 0;
        } else {
          $ratio1 = zen_round($od_amount / $amount,2);
        }
        $tod_amount = 0;
        reset($order->info['tax_groups']);
        while (list($key, $value) = each($order->info['tax_groups'])) {
          $tax_rate = zen_get_tax_rate_from_desc($key, $tax_address['country_id'], $tax_address['zone_id']);
          $total_net += $tax_rate * $value;
        }
        if ($od_amount > $total_net) $od_amount = $total_net;
        reset($order->info['tax_groups']);
        while (list($key, $value) = each($order->info['tax_groups'])) {
          $tax_rate = zen_get_tax_rate_from_desc($key, $tax_address['country_id'], $tax_address['zone_id']);
          $net = $tax_rate * $value;
          if ($net > 0) {
            $god_amount = $value * $ratio1;
            $tod_amount += $god_amount;
            if ($finalise) $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount;
          }
        }
        if ($finalise) $order->info['tax'] -= $tod_amount;
        if ($finalise) $order->info['total'] -= $tod_amount;
        break;
        case 'Credit Note':
          $tax_rate = zen_get_tax_rate($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
          $tax_desc = zen_get_tax_description($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
          $tod_amount = $this->deduction / (100 + $tax_rate)* $tax_rate;
          if ($finalise) $order->info['tax_groups'][$tax_desc] -= $tod_amount;
          if ($finalise) $order->info['tax'] -= $tod_amount;
          if ($finalise) $order->info['total'] -= $tod_amount;
        break;
        default:
      }
      return zen_round($tod_amount, 2);
    }

    function pre_confirmation_check($order_total) {
      global $order, $gBitDb;
      if ($this->include_shipping == 'false') $order_total -= $order->info['shipping_cost'];
      if ($this->include_tax == 'false') $order_total -= $order->info['tax'];
      $group_query = $gBitDb->Execute("select customers_group_pricing from " . TABLE_CUSTOMERS . " where `customers_id` = '" . $_SESSION['customer_id'] . "'");
      if ($group_query->fields['customers_group_pricing'] != '0') {
        $group_discount = $gBitDb->Execute("select `group_name`, `group_percentage` from " . TABLE_GROUP_PRICING . " where
                                        `group_id` = '" . $group_query->fields['customers_group_pricing'] . "'");
        $order_total = $this->get_order_total();
        $discount = $order_total * $group_discount->fields['group_percentage'] / 100;
        $od_amount = zen_round($discount, 2);
        if ($this->calculate_tax != "none") {
          $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
        }
      }
      return $od_amount + $tod_amount;
    }

    function credit_selection() {
      return $selection;
    }

    function collect_posts() {
    }

    function update_credit_account($i) {
    }

    function apply_credit() {
    }

    function check() {
      global $gBitDb;
      if (!isset($this->_check)) {
        $check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS'");
        $this->_check = $check_query->RecordCount();
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS');
    }

    function install() {
      global $gBitDb;
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_GROUP_PRICING_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_GROUP_PRICING_SORT_ORDER', '290', 'Sort order of display.', '6', '2', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_SHIPPING', 'false', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_INC_TAX', 'true', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GROUP_PRICING_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
      $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_GROUP_PRICING_TAX_CLASS', '0', 'Use the following tax class when treating Group Discount as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
    }

    function remove() {
      global $gBitDb;
      $gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>

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
// $Id: ot_gv.php,v 1.3 2005/08/24 02:52:59 lsces Exp $
//

  class ot_gv {
    var $title, $output;

    function ot_gv() {
//      echo '#' . $_SESSION['cot_gv'] . '#';
      global $currencies;
      $this->code = 'ot_gv';
      $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
      $this->header = MODULE_ORDER_TOTAL_GV_HEADER;
      $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
      $this->user_prompt = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
      $this->sort_order = MODULE_ORDER_TOTAL_GV_SORT_ORDER;
      $this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
      $this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
      $this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
      $this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
      $this->tax_class  = MODULE_ORDER_TOTAL_GV_TAX_CLASS;
      $this->show_redeem_box = MODULE_ORDER_TOTAL_GV_REDEEM_BOX;
      $this->credit_class = true;
      if (!zen_not_null(ltrim($_SESSION['cot_gv'], ' 0')) || $_SESSION['cot_gv'] == '0') $_SESSION['cot_gv'] = '0.00';
      $this->checkbox = $this->user_prompt . '<input type="textfield" size="6" onChange="submitFunction()" name="cot_gv" value="' . number_format($_SESSION['cot_gv'], 2) . '">' . ($this->user_has_gv_account($_SESSION['customer_id']) > 0 ? '<br />' . MODULE_ORDER_TOTAL_GV_USER_BALANCE . $currencies->format($this->user_has_gv_account($_SESSION['customer_id'])) : '');
      $this->output = array();
    }

    function process() {
      global $order, $currencies;
      if ($_SESSION['cot_gv']) {
        $order_total = $this->get_order_total();
        $od_amount = $this->calculate_credit($order_total);
        if ($this->calculate_tax != "none") {
          $tod_amount = zen_round($this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax, true), 2);
          $od_amount = $this->calculate_credit($order_total);
        }
        $this->deduction = $od_amount + $tod_amount;
        $order->info['total'] = $order->info['total'] - $this->deduction;
        if ($od_amount > 0) {
          $this->output[] = array('title' => $this->title . ':',
                           'text' => '-' . $currencies->format($this->deduction),
                           'value' => $this->deduction);
        }
      }
    }

    function selection_test() {
      if ($this->user_has_gv_account($_SESSION['customer_id'])) {
        return true;
      } else {
        return false;
      }
    }

    function pre_confirmation_check($order_total) {
      global $order;
      // clean out negative values and strip common currency symbols
      $_SESSION['cot_gv'] = str_replace(array('$','%','#','€','£','¥','ƒ'), '', $_SESSION['cot_gv']);
      $_SESSION['cot_gv'] = abs($_SESSION['cot_gv']);

      if ($_SESSION['cot_gv'] > 0) {
      if ($this->include_shipping == 'false') $order_total -= $order->info['shipping_cost'];
      if ($this->include_tax == 'false') $order_total -= $order->info['tax'];
        if (ereg('[^0-9/.]', trim($_SESSION['cot_gv']))) {
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_AMOUNT), 'SSL',true, false));
        }
        if ($_SESSION['cot_gv'] > $this->user_has_gv_account($_SESSION['customer_id'])) {
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_AMOUNT), 'SSL',true, false));
        }
        $od_amount = $this->calculate_credit($order_total);
        if ($this->calculate_tax != "none") {
          $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
          $od_amount = $this->calculate_credit($order_total)+$tod_amount;
        }
        if ($od_amount >= $order->info['total'] && MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID != 0) $order->info['order_status'] = MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID;
      }
      return $od_amount + $tod_amount;
    }

    function use_credit_amount() {
//      $_SESSION['cot_gv'] = false;
      if ($this->selection_test()) {
        $output_string = $this->checkbox;
      }
      return $output_string;
    }

    function update_credit_account($i) {
      global $db, $order, $insert_id;
      if (ereg('^GIFT', addslashes($order->products[$i]['model']))) {
        $gv_order_amount = ($order->products[$i]['final_price'] * $order->products[$i]['quantity']);
        if ($this->credit_tax=='true') $gv_order_amount = $gv_order_amount * (100 + $order->products[$i]['tax']) / 100;
        $gv_order_amount = $gv_order_amount * 100 / 100;
        if (MODULE_ORDER_TOTAL_GV_QUEUE == 'false') {
          // GV_QUEUE is false so release amount to account immediately
          $gv_result = $this->user_has_gv_account($_SESSION['customer_id']);
          $customer_gv = false;
          $total_gv_amount = 0;
          if ($gv_result) {
//            $total_gv_amount = $gv_result->fields['amount'];
            $total_gv_amount = $gv_result;
            $customer_gv = true;
          }
          $total_gv_amount = $total_gv_amount + $gv_order_amount;
          if ($customer_gv) {
            $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $total_gv_amount . "' where customer_id = '" . $_SESSION['customer_id'] . "'");
          } else {
            $db->Execute("insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) values ('" . $_SESSION['customer_id'] . "', '" . $total_gv_amount . "')");
          }
        } else {
         // GV_QUEUE is true - so queue the gv for release by store owner
          $db->Execute("insert into " . TABLE_COUPON_GV_QUEUE . " (customer_id, order_id, amount, date_created, ipaddr) values ('" . $_SESSION['customer_id'] . "', '" . $insert_id . "', '" . $gv_order_amount . "', NOW(), '" . $_SERVER['REMOTE_ADDR'] . "')");
        }
      }
    }

    function credit_selection() {
      global $db, $currencies;
      $gv_query = $db->Execute("select coupon_id from " . TABLE_COUPONS . " where coupon_type = 'G' and coupon_active='Y'");
      if ($gv_query->RecordCount() > 0 || $this->use_credit_amount()) {
        $selection = array('id' => $this->code,
                       'module' => $this->title,
                       'checkbox' => $this->use_credit_amount(),
                       'fields' => array(array('title' => MODULE_ORDER_TOTAL_GV_TEXT_ENTER_CODE,
                                               'field' => zen_draw_input_field('gv_redeem_code'))));

      }
      return $selection;
    }

    function apply_credit() {
      global $db, $order;
      if ($_SESSION['cot_gv'] != 0) {
        $gv_result = $db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $_SESSION['customer_id'] . "'");
        $gv_payment_amount = $this->deduction;
        $gv_amount = $gv_result->fields['amount'] - $gv_payment_amount;
        $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $gv_amount . "' where customer_id = '" . $_SESSION['customer_id'] . "'");
      }
      $_SESSION['cot_gv'] = false;
      return $gv_payment_amount;
    }


    function collect_posts() {
      global $db, $currencies;
      if (!$_POST['cot_gv']) $_SESSION['cot_gv'] = '0.00';
      if ($_POST['gv_redeem_code']) {
        $gv_result = $db->Execute("select coupon_id, coupon_type, coupon_amount from " . TABLE_COUPONS . " where coupon_code = '" . $_POST['gv_redeem_code'] . "'");
        if ($gv_result->RecordCount() > 0) {
          $redeem_query = $db->Execute("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gv_result->fields['coupon_id'] . "'");
          if ( ($redeem_query->RecordCount() > 0) && ($gv_result->fields['coupon_type'] == 'G')  ) {
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_INVALID_REDEEM_GV), 'SSL'));
          }
        } else {
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_INVALID_REDEEM_GV), 'SSL'));
		}
        if ($gv_result->fields['coupon_type'] == 'G') {
          $gv_amount = $gv_result->fields['coupon_amount'];
          // Things to set
          // ip address of claimant
          // customer id of claimant
          // date
          // redemption flag
          // now update customer account with gv_amount
          $gv_amount_result=$db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $_SESSION['customer_id'] . "'");
          $customer_gv = false;
          $total_gv_amount = $gv_amount;;
          if ($gv_amount_result->RecordCount() > 0) {
            $total_gv_amount = $gv_amount_result->fields['amount'] + $gv_amount;
            $customer_gv = true;
          }
          $db->Execute("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = '" . $gv_result->fields['coupon_id'] . "'");
          $db->Execute("insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip) values ('" . $gv_result->fields['coupon_id'] . "', '" . $_SESSION['customer_id'] . "', now(),'" . $_SERVER['REMOTE_ADDR'] . "')");
          if ($customer_gv) {
            // already has gv_amount so update
            $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . " set amount = '" . $total_gv_amount . "' where customer_id = '" . $_SESSION['customer_id'] . "'");
          } else {
            // no gv_amount so insert
            $db->Execute("insert into " . TABLE_COUPON_GV_CUSTOMER . " (customer_id, amount) values ('" . $_SESSION['customer_id'] . "', '" . $total_gv_amount . "')");
          }
          zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_REDEEMED_AMOUNT. $currencies->format($gv_amount)), 'SSL'));
       }
     }
     if ($_POST['submit_redeem_x'] && $gv_result->fields['coupon_type'] == 'G') zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_REDEEM_CODE), 'SSL'));
   }

    function calculate_credit($amount) {
      global $db, $order;
      $gv_payment_amount = $_SESSION['cot_gv'];
      $gv_amount = $gv_payment_amount;
      $save_total_cost = $amount;
      $full_cost = $save_total_cost - $gv_payment_amount;
      if ($full_cost < 0) {
        $full_cost = 0;
        $gv_payment_amount = $save_total_cost;
      }
      return zen_round($gv_payment_amount,2);
    }

    function calculate_tax_deduction($amount, $od_amount, $method, $finalise = false) {
      global $order;
      $tax_address = zen_get_tax_locations();
      switch ($method) {
        case 'Standard':
        $ratio1 = zen_round($od_amount / $amount,2);
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
      return $tod_amount;
    }

    function user_has_gv_account($c_id) {
      global $db;
      $gv_result = $db->Execute("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $c_id . "'");
      if ($gv_result->RecordCount() > 0) {
//        if ($gv_result->fields['amount'] > 0) {
          return $gv_result->fields['amount'];
//        }
      }
//      return false;
        return 0; // was preventing checkout_payment from continuing
    }

    function get_order_total() {
      global $order;
      $order_total = $order->info['total'];
      if ($this->include_tax == 'false') $order_total = $order_total - $order->info['tax'];
      if ($this->include_shipping == 'false') $order_total = $order_total - $order->info['shipping_cost'];

      return $order_total;
    }

    function check() {
      global $db;
      if (!isset($this->check)) {
        $check_query = $db->Execute("select`configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_ORDER_TOTAL_GV_STATUS'");
        $this->check = $check_query->RecordCount();
      }

      return $this->check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_GV_STATUS', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX',  'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID');
    }

    function install() {
      global $db;
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_GV_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', '840', 'Sort order of display.', '6', '2', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Queue Purchases', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'true', 'Do you want to queue purchases of the Gift Voucher?', '6', '3','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Tax', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', 'Use the following tax class when treating Gift Voucher as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Credit including Tax', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', 'Add tax to purchased Gift Voucher when crediting to Account', '6', '8','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID', '0', 'Set the status of orders made where GV covers full payment', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    }

    function remove() {
      global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in ('" . implode("', '", $this->keys()) . "')");
    }
  }
?>
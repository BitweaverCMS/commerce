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
// $Id: order_total.php,v 1.5 2005/11/15 22:01:21 spiderr Exp $
//

  class order_total {
    var $modules;

// class constructor
    function order_total() {
	  global $gBitCustomer;

      if (defined('MODULE_ORDER_TOTAL_INSTALLED') && zen_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
        $this->modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
//          include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/order_total/' . $value);
          include(zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/order_total/', $value, 'false'));
          include(DIR_WS_MODULES . 'order_total/' . $value);

          $class = substr($value, 0, strrpos($value, '.'));
          $GLOBALS[$class] = new $class;
        }
      }
    }

    function process() {
      $order_total_array = array();
      if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          $GLOBALS[$class]->process();

          for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
            if (zen_not_null($GLOBALS[$class]->output[$i]['title']) && zen_not_null($GLOBALS[$class]->output[$i]['text'])) {
              $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                                           'title' => $GLOBALS[$class]->output[$i]['title'],
                                           'text' => $GLOBALS[$class]->output[$i]['text'],
                                           'value' => $GLOBALS[$class]->output[$i]['value'],
                                           'sort_order' => $GLOBALS[$class]->sort_order);
            }
          }
        }
      }

      return $order_total_array;
    }

    function output() {
      $output_string = '';
      if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          $size = sizeof($GLOBALS[$class]->output);
          for ($i=0; $i<$size; $i++) {
            $output_string .= '              <tr>' . "\n" .
                              '                <td align="right" class="' . str_replace('_', '-', $GLOBALS[$class]->code) . '-Text">' . $GLOBALS[$class]->output[$i]['title'] . '</td>' . "\n" .
                              '                <td align="right" class="' . str_replace('_', '-', $GLOBALS[$class]->code) . '-Amount">' . $GLOBALS[$class]->output[$i]['text'] . '</td>' . "\n" .
                              '              </tr>';
          }
        }
      }

      return $output_string;
    }
//
// This function is called in checkout payment after display of payment methods. It actually calls
// two credit class functions.
//
// use_credit_amount() is normally a checkbox used to decide whether the credit amount should be applied to reduce
// the order total. Whether this is a Gift Voucher, or discount coupon or reward points etc.
//
// The second function called is credit_selection(). This in the credit classes already made is usually a redeem box.
// for entering a Gift Voucher number. Note credit classes can decide whether this part is displayed depending on
// E.g. a setting in the admin section.
//
    function credit_selection() {
      $selection_array = array();
      if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ( !empty( $GLOBALS[$class]->credit_class ) ) {
            $selection = $GLOBALS[$class]->credit_selection();
            if (is_array($selection)) $selection_array[] = $selection;
          }
        }
      }
      return $selection_array;
    }


// update_credit_account is called in checkout process on a per product basis. It's purpose
// is to decide whether each product in the cart should add something to a credit account.
// e.g. for the Gift Voucher it checks whether the product is a Gift voucher and then adds the amount
// to the Gift Voucher account.
// Another use would be to check if the product would give reward points and add these to the points/reward account.
//
    function update_credit_account($i) {
      if (MODULE_ORDER_TOTAL_INSTALLED) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ( $GLOBALS[$class]->credit_class ) {
            $GLOBALS[$class]->update_credit_account($i);
          }
        }
      }
    }


// This function is called in checkout confirmation.
// It's main use is for credit classes that use the credit_selection() method. This is usually for
// entering redeem codes(Gift Vouchers/Discount Coupons). This function is used to validate these codes.
// If they are valid then the necessary actions are taken, if not valid we are returned to checkout payment
// with an error

    function collect_posts() {
      if (MODULE_ORDER_TOTAL_INSTALLED) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ( !empty( $GLOBALS[$class]->credit_class ) ) {
            $post_var = 'c' . $GLOBALS[$class]->code;
            if ( !empty( $_POST[$post_var] ) ) {
				$_SESSION[$post_var] = $_POST[$post_var];
			}
            $GLOBALS[$class]->collect_posts();
          }
        }
      }
    }
// pre_confirmation_check is called on checkout confirmation. It's function is to decide whether the
// credits available are greater than the order total. If they are then a variable (credit_covers) is set to
// true. This is used to bypass the payment method. In other words if the Gift Voucher is more than the order
// total, we don't want to go to paypal etc.
//
    function pre_confirmation_check() {
      global $order, $credit_covers;
      if (MODULE_ORDER_TOTAL_INSTALLED) {
        $total_deductions  = 0;
        reset($this->modules);
        $order_total = $order->info['total'];
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          $order_total=$this->get_order_total_main($class,$order_total);
          if ( !empty( $GLOBALS[$class]->credit_class ) ) {
            $total_deductions = $total_deductions + $GLOBALS[$class]->pre_confirmation_check($order_total);
//echo $total_deductions . '#'.$order_total;
            $order_total = $order_total - $GLOBALS[$class]->pre_confirmation_check($order_total);
          }
        }
        $difference = $order->info['total'] - $total_deductions;
//die('here');
        if ( $difference <= 0.009 ) {
          $credit_covers = true;
        }
      }
    }
// this function is called in checkout process. it tests whether a decision was made at checkout payment to use
// the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
// is reduced the order total amount.
//
    function apply_credit() {
      if (MODULE_ORDER_TOTAL_INSTALLED) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ( $GLOBALS[$class]->credit_class) {
            $GLOBALS[$class]->apply_credit();
          }
        }
      }
    }

// Called in checkout process to clear session variables created by each credit class module.
//
    function clear_posts() {
      global $_POST;
      if (MODULE_ORDER_TOTAL_INSTALLED) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ( $GLOBALS[$class]->credit_class ) {
            $_SESSION[$post_var] = 'c_' . $GLOBALS[$class]->code;
          }
        }
      }
    }
// Called at various times. This function calulates the total value of the order that the
// credit will be appled aginst. This varies depending on whether the credit class applies
// to shipping & tax
//
    function get_order_total_main($class, $order_total) {
      global $credit, $order;
//      if ($GLOBALS[$class]->include_tax == 'false') $order_total=$order_total-$order->info['tax'];
//      if ($GLOBALS[$class]->include_shipping == 'false') $order_total=$order_total-$order->info['shipping_cost'];
      return $order_total;
    }
  }
?>

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
// $Id$
//

class CommerceOrderTotalManager extends BitBase {
    var $modules;
	private $mOrder;
	private $mOtClasses;

// class constructor
    function __construct( $pOrder ) {
	  global $gBitCustomer;

      if (defined('MODULE_ORDER_TOTAL_INSTALLED') && zen_not_null(MODULE_ORDER_TOTAL_INSTALLED)) {
        $this->modules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
//          include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/order_total/' . $value);
          $class = substr($value, 0, strrpos($value, '.'));
			if( !class_exists( $class ) ) {
				$langFile = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/order_total/', $value, 'false');
				if( file_exists( $langFile ) ) {
					include( $langFile );
				}
				include(DIR_WS_MODULES . 'order_total/' . $value);
			}
			$this->mOtClasses[$class] = new $class( $pOrder );
        }
      }
    }

    function process() {
      $order_total_array = array();
      if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          $this->mOtClasses[$class]->process( $this->mOrder );

          for ($i=0, $n=sizeof($this->mOtClasses[$class]->output); $i<$n; $i++) {
            if (zen_not_null($this->mOtClasses[$class]->output[$i]['title']) && zen_not_null($this->mOtClasses[$class]->output[$i]['text'])) {
              $order_total_array[] = array('code' => $this->mOtClasses[$class]->code,
                                           'title' => $this->mOtClasses[$class]->output[$i]['title'],
                                           'text' => $this->mOtClasses[$class]->output[$i]['text'],
                                           'value' => $this->mOtClasses[$class]->output[$i]['value'],
                                           'sort_order' => $this->mOtClasses[$class]->sort_order);
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
          $size = sizeof($this->mOtClasses[$class]->output);
          for ($i=0; $i<$size; $i++) {
            $output_string .= '              <tr>' . "\n" .
                              '                <td class="alignright ' . str_replace('_', '-', $this->mOtClasses[$class]->code) . '-Text">' . $this->mOtClasses[$class]->output[$i]['title'] . '</td>' . "\n" .
                              '                <td class="alignright ' . str_replace('_', '-', $this->mOtClasses[$class]->code) . '-Amount">' . $this->mOtClasses[$class]->output[$i]['text'] . '</td>' . "\n" .
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
          if ( !empty( $this->mOtClasses[$class]->credit_class ) ) {
            $selection = $this->mOtClasses[$class]->credit_selection( $this->mOrder );
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
          if ( method_exists( $this->mOtClasses[$class], 'update_credit_account' ) ) {
            $this->mOtClasses[$class]->update_credit_account($i);
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
          if ( method_exists( $this->mOtClasses[$class], 'collect_posts' ) ) {
            $post_var = 'c' . $this->mOtClasses[$class]->code;
            if ( !empty( $_POST[$post_var] ) ) {
				$_SESSION[$post_var] = $_POST[$post_var];
			}
            $this->mOtClasses[$class]->collect_posts( $this->mOrder );
          }
        }
      }
    }

// this function is called in checkout process. it tests whether a decision was made at checkout payment to use
// the credit amount be applied aginst the order. If so some action is taken. E.g. for a Gift voucher the account
// is reduced the order total amount.
//
    function apply_credit() {
		global $order;
      if (MODULE_ORDER_TOTAL_INSTALLED) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ( !empty( $this->mOtClasses[$class]->credit_class ) ) {
            $this->mOtClasses[$class]->apply_credit( $this->mOrder );
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
          if ( !empty( $this->mOtClasses[$class]->credit_class ) ) {
            $post_var = 'c' . $this->mOtClasses[$class]->code;
            $_SESSION[$post_var] = NULL;
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
//      if ($this->mOtClasses[$class]->include_tax == 'false') $order_total=$order_total-$order->info['tax'];
//      if ($this->mOtClasses[$class]->include_shipping == 'false') $order_total=$order_total-$order->info['shipping_cost'];
      return $order_total;
    }
  }
?>

<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginOrderTotalBase.php' );

class ot_gv extends CommercePluginOrderTotalBase {

	function __construct( $pOrder ) {
		$this->code = 'ot_gv';
		$this->mStatusKey = 'MODULE_ORDER_TOTAL_GV_STATUS';

		parent::__construct( $pOrder );

		if( $this->isEnabled() ) {
			global $currencies, $gBitUser;
			$this->title = MODULE_ORDER_TOTAL_GV_TITLE;
			$this->header = MODULE_ORDER_TOTAL_GV_HEADER;
			$this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
			$this->user_prompt = tra( 'Apply Balance' );
			$this->sort_order = MODULE_ORDER_TOTAL_GV_SORT_ORDER;
			$this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
			$this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
			$this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
			$this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
			$this->tax_class	= MODULE_ORDER_TOTAL_GV_TAX_CLASS;
			$this->credit_class = true;
			$this->userGvBalance = $this->getGvBalance( $gBitUser->mUserId );
			$this->checkbox = '';
			if( empty( $this->userGvBalance ) ) {
				if( isset( $_SESSION['cot_gv'] ) ) {
					unset( $_SESSION['cot_gv'] );
				}
			} else {
				if( $this->userGvBalance ) {
					$formDeduction = BitBase::getParameter( $_SESSION, 'cot_gv', ($pOrder->getField( 'total' ) < $this->userGvBalance ? $pOrder->getField( 'total' ) : $this->userGvBalance) );
					$this->checkbox = '
						<div class="form-group">
							<label class="control-label" for="cot_gv">'.tra('Apply Balance').'</label>
							<div class="row">
								<div class="col-xs-6"><div class="input-group">';
					if( $currencies->getLeftSymbol() ) { $this->checkbox .= '<div class="input-group-addon">'.$currencies->getLeftSymbol().'</div>'; }
					$this->checkbox .= '<input type="number" class="form-control" step="'.$currencies->getInputStep().'" name="cot_gv" value="' . number_format( $formDeduction, $currencies->get_decimal_places() ) . '"/>';
					if( $currencies->getRightSymbol() ) { $this->checkbox .= '<div class="input-group-addon">'.$currencies->getRightSymbol().'</div>'; }
					$this->checkbox .= '</div></div><div class="col-xs-6">' . tra( 'of' ) . ' ' . $currencies->format( $this->userGvBalance ) . '</div>
							</div>
						</div>';
				}
			}
		}
	}

	private function getOrderDeduction( $pGvAmount ) {
		global $gBitUser;

		$od_amount = 0.0;
		$tod_amount = 0.0;
		// clean out negative values and strip common currency symbols
		$creditAmount = preg_replace( '/[^\d\.]/', '', $pGvAmount );
		$orderTotal = $this->mOrder->info['total'];

		if ( !empty( $creditAmount ) ) {
			if( $this->include_shipping == 'false') { 
				$orderTotal -= $this->mOrder->info['shipping_cost'];
			}
			if( $this->include_tax == 'false') {
				$orderTotal -= $this->mOrder->info['tax'];
			}

			if( $creditAmount > $orderTotal ) {
				$creditAmount = $orderTotal;
			}

			if( $this->calculate_tax != 'none' ) {
				$tax_address = zen_get_tax_locations();
				switch( $this->calculate_tax ) {
					case 'Standard':
						$ratio1 = zen_round($creditAmount / $orderTotal,2);
						$tod_amount = 0;
						reset($this->mOrder->info['tax_groups']);
						while (list($key, $value) = each($this->mOrder->info['tax_groups'])) {
							$tax_rate = zen_get_tax_rate_from_desc($key, $tax_address['country_id'], $tax_address['zone_id']);
							$total_net += $tax_rate * $value;
						}
						if ($creditAmount > $total_net) $creditAmount = $total_net;
						reset($this->mOrder->info['tax_groups']);
						while (list($key, $value) = each($this->mOrder->info['tax_groups'])) {
							$tax_rate = zen_get_tax_rate_from_desc($key, $tax_address['country_id'], $tax_address['zone_id']);
							$net = $tax_rate * $value;
							if ($net > 0) {
								$god_amount = $value * $ratio1;
								$tod_amount += $god_amount;
							}
						}
						break;
					case 'Credit Note':
						$tax_rate = zen_get_tax_rate($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
						$tax_desc = zen_get_tax_description($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
						$tod_amount = $this->deduction / (100 + $tax_rate)* $tax_rate;
						break;
				}
			}

			$od_amount = $creditAmount + $tod_amount;
			if ($od_amount >= $this->mOrder->info['total'] && defined( 'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID' ) && MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID != 0) {
				 $this->mOrder->info['order_status'] = MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID;
			}
		}

		return $od_amount + $tod_amount;
	}

	function process() {
		parent::process();
		global $currencies;

		if( !empty( $_SESSION['cot_gv'] ) ) {
			if( $deduction = $this->getOrderDeduction( $_SESSION['cot_gv'] ) ) {
				$this->mOrder->info['total'] = round( $this->mOrder->info['total'] - $deduction, $currencies->get_decimal_places() ); // avoid 64 bit math error
				$this->mOrder->info['gv_amount'] = $deduction;

				$this->mProcessingOutput = array( 'code' => $this->code,
													'title' => $this->title . ':',
													'text' => '-' . $currencies->format($deduction),
													'value' => -1 * $deduction);
			}
		}
	}

	function selection_test() {
		return( $this->userGvBalance );
	}

	function pre_confirmation_check() {
		$ret = null;
		if( !empty( $_SESSION['cot_gv'] ) ) {
			if (preg_match('#[^0-9/.]#', trim($_SESSION['cot_gv']))) {
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_AMOUNT), 'SSL',true, false));
			} elseif ($_SESSION['cot_gv'] > $this->userGvBalance ) {
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_AMOUNT), 'SSL',true, false));
			} else {
				$ret = $this->getOrderDeduction( $_SESSION['cot_gv'] );
			}
		}
		return $ret;
	}

	function use_credit_amount() {
//			$_SESSION['cot_gv'] = false;
		if ($this->selection_test()) {
			return $this->checkbox;
		}
	}

	function update_credit_account( $pOpid ) {
		if (preg_match('/^GIFT/', addslashes($this->mOrder->contents[$pOpid]['model']))) {
			$customerId = $this->mOrder->customer['id'];
			$gv_order_amount = ($this->mOrder->contents[$pOpid]['final_price'] * $this->mOrder->contents[$pOpid]['quantity']);
			if ($this->credit_tax=='true') {
				$gv_order_amount = $gv_order_amount * (100 + $this->mOrder->contents[$pOpid]['tax']) / 100;
			}
			$gv_order_amount = $gv_order_amount * 100 / 100;
			if (MODULE_ORDER_TOTAL_GV_QUEUE == 'false') {
				// GV_QUEUE is false so release amount to account immediately
				$gvBalance = $this->getGvBalance( $customerId );
				$customer_gv = false;
				$total_gv_amount = 0;
				if ($gvBalance) {
					$total_gv_amount = $gvBalance;
					$customer_gv = true;
				}
				$total_gv_amount = $total_gv_amount + $gv_order_amount;
				if ($customer_gv) {
					$this->mDb->query("UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET `amount` = ?	WHERE `customer_id` = ?", array( $total_gv_amount, $customerId ) );
				} else {
					$this->mDb->query("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (`customer_id`, `amount`) VALUES (?,?)", array( $customerId, $total_gv_amount ) );
				}
			} else {
				for( $j = 0; $j < $this->mOrder->contents[$pOpid]['quantity']; $j++ ) {
					// GV_QUEUE is true - so queue the gv for release by store owner
					$this->mDb->query("INSERT INTO " . TABLE_COUPON_GV_QUEUE . " (`customer_id`, `order_id`, `amount`, `date_created`, `ipaddr`) VALUES ( ?, ?, ?, NOW(), ? )", array( $customerId, $this->mOrder->mOrdersId, $this->mOrder->contents[$pOpid]['final_price'], $_SERVER['REMOTE_ADDR'] ) );
				}
			}
		}
	}


	function credit_selection() {
		global $gBitUser;
		$selection = array();

		$couponId = $this->mDb->getOne("SELECT coupon_id FROM " . TABLE_COUPONS . " where coupon_type = 'G' and coupon_active='Y'");
		if ($couponId || $this->use_credit_amount()) {
			$selection = array(	'id' => $this->code,
								'module' => $this->title,
								'checkbox' => $this->use_credit_amount(),
								'fields' => array(array('title' => tra( 'Gift Certificate Code' ),
								'field' => zen_draw_input_field('gv_redeem_code'))));
		}
		return $selection;
	}

	function apply_credit() {
		global $gBitUser;
		$ret = FALSE;
		if( $this->mOrder->getField( 'gv_amount' ) ) {
			$this->mDb->query( "UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET `amount` = `amount` - ? WHERE `customer_id` = ?", array( $this->mOrder->getField( 'gv_amount' ), $gBitUser->mUserId ) );
			$ret = TRUE;
			unset( $_SESSION['cot_gv'] );
		}
		return $ret;
	}


	function collect_posts() {
		global $gBitUser, $currencies;
		if ( !empty( $_POST['cot_gv'] ) && is_numeric( $_POST['cot_gv'] ) && $_POST['cot_gv'] > 0 ) {
			$_SESSION['cot_gv'] = $_POST['cot_gv'];
		} elseif( isset( $_SESSION['cot_gv'] ) ) {
			unset( $_SESSION['cot_gv'] );
		}
		if ( !empty( $_POST['gv_redeem_code'] ) ) {
			$gv_result = $this->mDb->Execute("select `coupon_id`, `coupon_type`, `coupon_amount` from " . TABLE_COUPONS . " where `coupon_code` = ?", array( $_POST['gv_redeem_code'] ) );
			if ($gv_result->RecordCount() > 0) {
				$redeem_query = $this->mDb->Execute("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gv_result->fields['coupon_id'] . "'");
				if ( ($redeem_query->RecordCount() > 0) && ($gv_result->fields['coupon_type'] == 'G')	) {
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
				$gv_amount_result=$this->mDb->query("select `amount` from " . TABLE_COUPON_GV_CUSTOMER . " where `customer_id` = ?", array( $gBitUser->mUserId ) );
				$customer_gv = false;
				$total_gv_amount = $gv_amount;;
				if ($gv_amount_result->RecordCount() > 0) {
					$total_gv_amount = $gv_amount_result->fields['amount'] + $gv_amount;
					$customer_gv = true;
				}
				$this->mDb->query("UPDATE " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = ?", array( $gv_result->fields['coupon_id'] ) );
				$this->mDb->query("INSERT INTO	" . TABLE_COUPON_REDEEM_TRACK . " (redeem_date, coupon_id, customer_id, redeem_ip) values ( now(), ?, ?, ?)", array( $gv_result->fields['coupon_id'], $gBitUser->mUserId, $_SERVER['REMOTE_ADDR'] ) );
				if ($customer_gv) {
					// already has gv_amount so update
					$this->mDb->query( "UPDATE " . TABLE_COUPON_GV_CUSTOMER . " set `amount` = ? where `customer_id` = ?", array( $total_gv_amount, $gBitUser->mUserId ) );
				} else {
					// no gv_amount so insert
					$this->mDb->query("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (`customer_id`, `amount`) values (?, ?)", array( $gBitUser->mUserId, $total_gv_amount ) );
				}
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_REDEEMED_AMOUNT. $currencies->format($gv_amount)), 'SSL'));
		 }
	 }
	 if ( isset( $_POST['submit_redeem_x'] ) && $gv_result->fields['coupon_type'] == 'G') zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_REDEEM_CODE), 'SSL'));
 }

	private function getGvBalance( $pCustomersId ) {
		return $this->mDb->getOne( "SELECT `amount` FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE `customer_id` = ?", array( $pCustomersId ) );
	}

	function get_order_total() {
		$orderTotal = $this->mOrder->info['total'];
		if ($this->include_tax == 'false') {
			$orderTotal -= $this->mOrder->info['tax'];
		}
		if ($this->include_shipping == 'false') {
			$orderTotal -= $this->mOrder->info['shipping_cost'];
		}

		return $orderTotal;
	}

	function keys() {
		return array('MODULE_ORDER_TOTAL_GV_STATUS', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX',	'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID');
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_GV_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_GV_SORT_ORDER', '840', 'Sort order of display.', '6', '2', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('Queue Purchases', 'MODULE_ORDER_TOTAL_GV_QUEUE', 'true', 'Do you want to queue purchases of the Gift Voucher?', '6', '3','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Shipping', 'MODULE_ORDER_TOTAL_GV_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Tax', 'MODULE_ORDER_TOTAL_GV_INC_TAX', 'true', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_GV_CALC_TAX', 'None', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_GV_TAX_CLASS', '0', 'Use the following tax class when treating Gift Voucher as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Credit including Tax', 'MODULE_ORDER_TOTAL_GV_CREDIT_TAX', 'false', 'Add tax to purchased Gift Voucher when crediting to Account', '6', '8','zen_cfg_select_option(array(\'true\', \'false\'), ', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `use_function`, `date_added`) values ('Set Order Status', 'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID', '0', 'Set the status of orders made where GV covers full payment', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
	}
}

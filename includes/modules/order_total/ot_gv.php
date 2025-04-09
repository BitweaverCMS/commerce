 <?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginOrderTotalBase.php' );

class ot_gv extends CommercePluginOrderTotalBase {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );

		$this->title = $this->getTitle( 'Gift Certificates' );
		$this->header = tra( 'Gift Certificates' );
		$this->description = tra( 'Gift Certificates' );
		$this->userGvBalance = 0.0;
		if( $this->isEnabled() ) {
			global $gBitUser, $gCommerceSystem;
			$this->user_prompt = tra( 'Apply Balance' );
			$this->include_shipping = $this->getModuleConfigValue( '_INC_SHIPPING' );
			$this->include_tax = $this->getModuleConfigValue( '_INC_TAX' );
			$this->calculate_tax = $this->getModuleConfigValue( '_CALC_TAX' );
			$this->credit_tax = $this->getModuleConfigValue( '_CREDIT_TAX' );
			$this->tax_class = $this->getModuleConfigValue( '_TAX_CLASS' );
			$this->credit_class = true;
			$this->userGvBalance = $this->getGvBalance( $gBitUser->mUserId );
			if( empty( $this->userGvBalance ) ) {
				if( isset( $_SESSION['cot_gv'] ) ) {
					unset( $_SESSION['cot_gv'] );
				}
			}
		}
	}

	private function getDiscount( $pGvAmount, &$pSessionParams ) {
		global $gBitUser;

		$od_amount = 0.0;
		$tod_amount = 0.0;
		// clean out negative values and strip common currency symbols
		$creditAmount = preg_replace( '/[^\d\.]/', '', $pGvAmount );
		//$orderTotal = $this->mOrder->getField( 'total' ); // this is old school, doesn't account for coupons
		$orderTotal = $this->mOrder->getSubtotal( $this->code, $pSessionParams );

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
			if ($od_amount >= $this->mOrder->getField( 'total' ) && defined( 'MODULE_ORDER_TOTAL_GV_ORDERS_STATUS_ID' ) && MODULE_ORDER_TOTAL_GV_ORDERS_STATUS_ID != 0) {
				 $this->mOrder->info['order_status'] = MODULE_ORDER_TOTAL_GV_ORDERS_STATUS_ID;
			}
		}

		return $od_amount + $tod_amount;
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
		global $currencies;

		if( !empty( $pSessionParams['cot_gv'] ) ) {
			if( $deduction = $this->getOrderDeduction( $pSessionParams['cot_gv'], $pSessionParams ) ) {
				$this->mOrder->info['gv_amount'] = $deduction;
				$this->setOrderDeduction( $deduction );
				$this->mProcessingOutput = array( 'code' => $this->code,
													'sort_order' => $this->getSortOrder(),
													'title' => $this->title,
													'text' => '-' . $currencies->format($deduction),
													'value' => -1 * $deduction);
			}
		}
	}

	public function getOrderDeduction( $pOrder, &$pSessionParams ) {
		$ret = null;

		if( !empty( $pSessionParams['cot_gv'] ) ) {
			if (preg_match('#[^0-9/.]#', trim($pSessionParams['cot_gv']))) {
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_AMOUNT), 'SSL',true, false));
			} elseif ($pSessionParams['cot_gv'] > $this->userGvBalance ) {
				$pSessionParams['cot_gv'] = $this->userGvBalance;
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_AMOUNT), 'SSL',true, false));
			} else {
				$ret = $this->getDiscount( $pSessionParams['cot_gv'], $pSessionParams );
			}
		}

		return $ret;
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


	public function credit_selection( $pOrder, &$pSessionParams ) {
		global $currencies, $gBitUser;
		$selection = array();
		$couponId = $this->mDb->getOne("SELECT coupon_id FROM " . TABLE_COUPONS . " where coupon_type = 'G' and coupon_active='Y'");
		if ($couponId || !empty( $this->userGvBalance ) ) {
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
			} else {
				$this->checkbox = '';
			}
			$selection = array(	'id' => $this->code,
								'module' => $this->title,
								'checkbox' => $this->checkbox,
								'fields' => array(
									array( 'title' => tra( 'Gift Certificate Code' ), 'field' => zen_draw_input_field('gv_redeem_code', BitBase::getParameter($pSessionParams, 'gv_redeem_code') )))
								);
		}
		return $selection;
	}

	function apply_credit( &$pSessionParams ) {
		global $gBitUser;
		$ret = FALSE;
		if( $this->mOrder->getField( 'gv_amount' ) ) {
			$this->mDb->query( "UPDATE " . TABLE_COUPON_GV_CUSTOMER . " SET `amount` = `amount` - ? WHERE `customer_id` = ?", array( $this->mOrder->getField( 'gv_amount' ), $gBitUser->mUserId ) );
			$ret = TRUE;
			unset( $pSessionParams['cot_gv'] );
		}
		return $ret;
	}


	function collect_posts( $pRequestParams, &$pSessionParams ) {
		global $gBitUser, $currencies;

		$retError = FALSE;

		if ( !empty( $pRequestParams['cot_gv'] ) && is_numeric( $pRequestParams['cot_gv'] ) && $pRequestParams['cot_gv'] > 0 ) {
			$pSessionParams['cot_gv'] = $pRequestParams['cot_gv'];
			$_SESSION['cot_gv'] = $pRequestParams['cot_gv'];
		} elseif( isset( $pSessionParams['cot_gv'] ) ) {
			unset( $pSessionParams['cot_gv'] );
		}
		if ( !empty( $pRequestParams['gv_redeem_code'] ) ) {
			if( $gvHash = $this->mDb->getRow( "SELECT `coupon_id`, `coupon_type`, `coupon_amount`, `coupon_active` FROM " . TABLE_COUPONS . " WHERE `coupon_type` = 'G' AND `coupon_code` = ?", array( $pRequestParams['gv_redeem_code'] ) ) ) {
				$redemption = $this->mDb->getRow("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = ?", array( $gvHash['coupon_id'] ) );
				if( $gvHash['coupon_active'] != 'Y' || (!empty( $redemption )) ) {
					$retError = ERROR_NO_INVALID_REDEEM_GV;
				} else {
					$gv_amount = $gvHash['coupon_amount'];
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
					$this->mDb->query("UPDATE " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = ?", array( $gvHash['coupon_id'] ) );
					$this->mDb->query("INSERT INTO	" . TABLE_COUPON_REDEEM_TRACK . " (redeem_date, coupon_id, customer_id, redeem_ip) values ( now(), ?, ?, ?)", array( $gvHash['coupon_id'], $gBitUser->mUserId, $_SERVER['REMOTE_ADDR'] ) );
					if ($customer_gv) {
						// already has gv_amount so update
						$this->mDb->query( "UPDATE " . TABLE_COUPON_GV_CUSTOMER . " set `amount` = ? where `customer_id` = ?", array( $total_gv_amount, $gBitUser->mUserId ) );
					} else {
						// no gv_amount so insert
						$this->mDb->query("INSERT INTO " . TABLE_COUPON_GV_CUSTOMER . " (`customer_id`, `amount`) values (?, ?)", array( $gBitUser->mUserId, $total_gv_amount ) );
					}
					$retError = ERROR_REDEEMED_AMOUNT. $currencies->format($gv_amount);
				}
			} else {
				$retError = ERROR_NO_REDEEM_CODE.' '.$pRequestParams['gv_redeem_code'];
			}
	 	}

		return $retError;
 	}

	private function getGvBalance( $pCustomersId ) {
		return $this->mDb->getOne( "SELECT `amount` FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE `customer_id` = ?", array( $pCustomersId ) );
	}

	// {{{	++++++++ config ++++++++
	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$parentConfig = parent::config();
		$i = count( $parentConfig );
		return array_merge( $parentConfig, array( 
			$this->getModuleKeyTrunk().'_QUEUE' => array(
				'configuration_title' => 'Queue Purchases',
				'configuration_description' => 'Do you want to queue purchases of the Gift Voucher?',
				'configuration_value' => 'true',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_INC_SHIPPING' => array(
				'configuration_title' => 'Include Shipping',
				'configuration_description' => 'Include Shipping in calculation',
				'configuration_value' => 'true',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_INC_TAX' => array(
				'configuration_title' => 'Include Tax',
				'configuration_description' => 'Include Tax in calculation.',
				'configuration_value' => 'true',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_CALC_TAX' => array(
				'configuration_title' => 'Re-calculate Tax',
				'configuration_description' => 'Re-Calculate Tax',
				'configuration_value' => 'None',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('None', 'Standard', 'Credit Note'), ",
			),
			$this->getModuleKeyTrunk().'_TAX_CLASS' => array(
				'configuration_title' => 'Tax Class',
				'configuration_description' => 'Use the following tax class when treating Gift Voucher as Credit Note.',
				'configuration_value' => '0',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_pull_down_tax_classes(",
				'use_function' => "zen_get_tax_class_title",
			),
			$this->getModuleKeyTrunk().'_CREDIT_TAX' => array(
				'configuration_title' => 'Credit including Tax',
				'configuration_description' => 'Add tax to purchased Gift Voucher when crediting to Account',
				'configuration_value' => 'false',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_select_option(array('true', 'false'), ",
			),
			$this->getModuleKeyTrunk().'_ORDERS_STATUS_ID' => array(
				'configuration_title' => 'Set Order Status',
				'configuration_description' => 'Set the status of orders made where GV covers full payment',
				'configuration_value' => '0',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_pull_down_order_statuses(",
				'use_function' => "zen_get_order_status_name",
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '840';
		return $ret;
	}
	// }}} ++++ config ++++
}

<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
// $Id: ot_coupon.php,v 1.20 2010/07/21 03:42:31 spiderr Exp $
//

	require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceVoucher.php' );

	class ot_coupon {
		var $title, $output;

		function ot_coupon() {

			$this->code = 'ot_coupon';
			$this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
			$this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
			$this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
			$this->user_prompt = '';
			$this->sort_order = MODULE_ORDER_TOTAL_COUPON_SORT_ORDER;
			$this->include_shipping = MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING;
			$this->include_tax = MODULE_ORDER_TOTAL_COUPON_INC_TAX;
			$this->calculate_tax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
			$this->tax_class	= MODULE_ORDER_TOTAL_COUPON_TAX_CLASS;
			$this->credit_class = true;
			$this->output = array();

		}

	function process() {
		global $order, $currencies, $gBitDb;
		if( $od_amount = $this->calculate_deductions($this->get_order_total()) ) {
		$this->deduction = $od_amount['total'];
		if ($od_amount['total'] > 0) {
			while (list($key, $value) = each($order->info['tax_groups'])) {
				$tax_rate = zen_get_tax_rate_from_desc($key);
				if( !empty( $od_amount[$key] ) ) {
					$order->info['tax_groups'][$key] -= $od_amount[$key];
					$order->info['total'] -=	$od_amount[$key];
				}
			}
			if( !empty( $od_amount['type'] ) && $od_amount['type'] == 'S') $order->info['shipping_cost'] = 0;
				$sql = "select coupon_code from " . TABLE_COUPONS . " where coupon_id = '" . $_SESSION['cc_id'] . "'";
				$zq_coupon_code = $gBitDb->Execute($sql);
				$this->coupon_code = $zq_coupon_code->fields['coupon_code'];
				$order->info['total'] = $order->info['total'] - $od_amount['total'];
				$this->output[] = array('title' => $this->title . ': ' . $this->coupon_code . ' :',
							 'text' => '-' . $currencies->format($od_amount['total']),
							 'value' => $od_amount['total']);
			}
		}
	}

	function selection_test() {
		return false;
	}

	function clear_posts() {
		unset($_SESSION['cc_id']);
	}

	function get_order_total() {
		global $gBitCustomer;
		$gBitCustomer->mCart->calculate();
		if( $this->include_shipping == 'true' ) {
			$ret = $gBitCustomer->mCart->total;
		} else {
			$ret = $gBitCustomer->mCart->subtotal;
		}
		return $ret;
	}

	function pre_confirmation_check($order_total) {
		global $order;
		if ($this->include_shipping == 'false') $order_total -= $order->info['shipping_cost'];
		if ($this->include_tax == 'false') $order_total -= $order->info['tax'];
		$od_amount = $this->calculate_deductions($order_total);
		return $od_amount['total'] + $od_amount['tax'];
//		return $od_amount['total'];
	}

	function use_credit_amount() {
		return false;
	}


	function credit_selection() {
		$selection = array(	'id' => $this->code,
							 'module' => $this->title,
							 'fields' => array(array('title' => 'Coupon Code',
							 'field' => zen_draw_input_field('dc_redeem_code'))));
		return $selection;
	}


	function collect_posts() {
		global $gBitCustomer, $currencies;
		if ( !empty( $_POST['dc_redeem_code'] ) ) {
			$coupon = new CommerceVoucher();
			if ( !$coupon->load( $_POST['dc_redeem_code'] ) ) {
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_COUPON.'-'.$_POST['dc_redeem_code'] ), 'SSL',true, false));
			} elseif ($coupon->getField('coupon_type') != 'G') {
				// JTD - added missing code here to handle coupon product restrictions
				// look through the items in the cart to see if this coupon is valid for any item in the cart
				$foundvalid = FALSE;
				foreach( array_keys( $gBitCustomer->mCart->contents ) as $productKey ) {
					$productHash = $gBitCustomer->mCart->getProductHash( $productKey );
					if ($this->is_product_valid( $productHash ) ) {
						$foundvalid = TRUE;
					}
				}
				if (!$foundvalid) {
					zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_COUPON_PRODUCT.'-'.$_POST['dc_redeem_code']), 'SSL',true, false));
				}
				// JTD - end of additions of missing code to handle coupon product restrictions

				if( !$coupon->isRedeemable() ) {
					zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode( $coupon->mErrors['redeem_error'].'-'.$_POST['dc_redeem_code'] ), 'SSL',true, false));
				}

				if ($coupon->getField('coupon_type')=='S') {
					if( $coupon->getField( 'restrict_to_shipping' ) ) {
						$shippingMethods = explode( ',', $coupon->getField( 'restrict_to_shipping' ) );
						if( in_array( $order->info['shipping_method_code'], $shippingMethods ) ) {
							$coupon_amount = $order->info['shipping_cost'];
						}
					} else {
						$coupon_amount = $order->info['shipping_cost'];
					}
				} else {
					$coupon_amount = $currencies->format($coupon->getField('coupon_amount')) . ' ';
				}
				$_SESSION['cc_id'] = $coupon->mCouponId;
			}
			if( !empty( $_POST['submit_redeem_coupon_x'] ) && !$_POST['gv_redeem_code']) {
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEST_NO_REDEEM_CODE.'-'.$_POST['dc_redeem_code']), 'SSL', true, false));
			}
		}
	}


	function update_credit_account($i) {
		return false;
	}

	function apply_credit() {
		global $gBitDb, $insert_id;
		$cc_id = $_SESSION['cc_id'];
		if ($this->deduction !=0) {
			$gBitDb->Execute("insert into " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, redeem_date, redeem_ip, customer_id, order_id)
						values ('" . $cc_id . "', now(), '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SESSION['customer_id'] . "', '" . $insert_id . "')");
		}
		$_SESSION['cc_id'] = "";
	}

	function calculate_deductions($order_total) {
		global $gBitDb, $gBitCustomer, $order;

		$tax_address = zen_get_tax_locations();
		$od_amount['total'] = 0;
		$od_amount['tax'] = 0;
		if ($_SESSION['cc_id']) {
			$coupon = new CommerceVoucher( $_SESSION['cc_id'] );
			if( $coupon->load() && $coupon->isRedeemable() ) {
				if ($coupon->getField( 'coupon_minimum_order' ) <= $order_total) {
					if ($coupon->getField( 'coupon_type' )=='S') {
						if( $coupon->getField( 'restrict_to_shipping' ) ) {
							$shippingMethods = explode( ',', $coupon->getField( 'restrict_to_shipping' ) );
							if( in_array( $order->info['shipping_method_code'], $shippingMethods ) ) {
								$od_amount['total'] = $order->info['shipping_cost'];
							}
						} else {
							$od_amount['total'] = $order->info['shipping_cost'];
						}
						$od_amount['type'] = 'S';
					} else {
						if ($coupon->getField( 'coupon_type' ) == 'P') {
							// Max discount is a sum of percentages of valid products
							$totalDiscount = 0;
						} else {
							$totalDiscount = $coupon->getField( 'coupon_amount' ) * ($order_total>0);
						}
						$runningDiscount = 0;
						foreach( array_keys( $gBitCustomer->mCart->contents ) as $productKey ) {
							$productHash = $gBitCustomer->mCart->getProductHash( $productKey );
							if( $productHash && $this->is_product_valid( $productHash, $_SESSION['cc_id'] ) ) {
								// _P_ercentage discount
								if ($coupon->getField( 'coupon_type' ) == 'P') {
									$itemDiscount = round( ($productHash['final_price'] * $productHash['products_quantity']) * ($coupon->getField( 'coupon_amount' )/100), 2 );
									$totalDiscount += $itemDiscount;
									if( $runningDiscount < $totalDiscount ) {
										$runningDiscount += $itemDiscount;
									}
									if( $runningDiscount > $totalDiscount ) {
										$runningDiscount = $totalDiscount;
										$itemDiscount = 0;
									}
									switch ($this->calculate_tax) {
										case 'Credit Note':
											$tax_rate = zen_get_tax_rate($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
											$tax_desc = zen_get_tax_description($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
											$od_amount[$tax_desc] = $runningDiscount / 100 * $tax_rate;
											$od_amount['tax'] += $od_amount[$tax_desc];
											break;
										case 'Standard':
											$ratio = $runningDiscount / $this->get_order_total();
											$tax_rate = zen_get_tax_rate($productHash['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
											$tax_desc = zen_get_tax_description($productHash['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
											if ($tax_rate > 0) {
												if( empty( $od_amount[$tax_desc] ) ) { $od_amount[$tax_desc] = 0; }
												$od_amount[$tax_desc] += (($productHash['final_price'] * $productHash['products_quantity']) * $tax_rate)/100 * $ratio;
												$od_amount['tax'] += $od_amount[$tax_desc];
											}
											break;
									}
								// _F_ixed discount
								} elseif ($coupon->getField( 'coupon_type' ) == 'F') {
									switch ($this->calculate_tax) {
										case 'Credit Note':
											$tax_rate = zen_get_tax_rate($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
											$tax_desc = zen_get_tax_description($this->tax_class, $tax_address['country_id'], $tax_address['zone_id']);
											$od_amount[$tax_desc] = $runningDiscount / 100 * $tax_rate;
											$od_amount['tax'] += $od_amount[$tax_desc];
											break;
										case 'Standard':
											$ratio = $runningDiscount/$this->get_order_total();
											$t_prid = zen_get_prid( $productKey );
											$cc_result = $gBitDb->query("select `products_tax_class_id` from " . TABLE_PRODUCTS . " where `products_id` = ?", array( $t_prid ) );

											if ($this->is_product_valid( $productHash, $_SESSION['cc_id'])) {
												if( $runningDiscount < $totalDiscount ) {
													$runningDiscount += ($productHash['final_price'] * $productHash['products_quantity']);
												}
												if( $runningDiscount > $totalDiscount ) {
													$runningDiscount = $totalDiscount;
												}
												$tax_rate = zen_get_tax_rate($cc_result->fields['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
												$tax_desc = zen_get_tax_description($cc_result->fields['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
												if ($tax_rate > 0) {
													if( empty( $od_amount[$tax_desc] ) ) { $od_amount[$tax_desc] = 0; }
													$od_amount[$tax_desc] += (($productHash['final_price'] * $productHash['products_quantity']) * $tax_rate)/100 * $ratio;
													$od_amount['tax'] += $od_amount[$tax_desc];
												}
											}
											break;
									}
								}
							}
						}
						$od_amount['total'] = $runningDiscount;
						if ($od_amount['total']>$order_total) {
							$od_amount['total'] = $order_total;
						}
					}
				}
			}
		}
		return $od_amount;
	}


	function is_product_valid( $pProductHash, $coupon_id) {
		global $gBitDb;
		$ret = false;

		if( is_numeric( $coupon_id ) ) {
			$query = "SELECT * FROM " . TABLE_COUPON_RESTRICT . " WHERE `coupon_id` = ?  ORDER BY ".$gBitDb->convertSortmode( 'coupon_restrict_asc' );
			if( $rs = $gBitDb->query( $query, array( $coupon_id ) ) ) {
				// gifts are not valid, so only check non-gifts
				if( !preg_match( '/^GIFT/', $pProductHash['products_model'] ) ) {
					$ret = TRUE;
					while( $restriction = $rs->fetchRow() ) {

						// specific product_id  - are we exclusive or inclusive?
						if( !empty( $restriction['product_id'] ) ) {
							if( $restriction['product_id'] == $pProductHash['products_id'] ) {
								$ret &= ($restriction['coupon_restrict'] == 'N'); // Exact match
							} else {
								$ret &= ($restriction['coupon_restrict'] != 'N');
							}
						}

						// specific category_id  - are we exclusive or inclusive?
						if( !empty( $restriction['category_id'] ) ) {
							// check master cat quickly, or go deep diving
							if ( ($pProductHash['master_categories_id'] ==  $restriction['category_id']) || zen_product_in_category($pProductHash['products_id'], $restriction['category_id']) ) {
								$ret &= ($restriction['coupon_restrict']=='N'); // Exact match
							} else {
								$ret &= ($restriction['coupon_restrict']!='N');
							}
						}

						// specific product_type_id  - are we exclusive or inclusive?
						if( !empty( $restriction['product_type_id'] ) ) {
							// check master cat quickly, or go deep diving
							if( $restriction['product_type_id'] == $pProductHash['products_type'] ) {
								$ret &= ($restriction['coupon_restrict']=='N'); // Exact match
							} else {
								$ret &= ($restriction['coupon_restrict']!='N');
							}
						}

						// specific products_options_values_id  - are we exclusive or inclusive?
						if( !empty( $restriction['products_options_values_id'] ) ) {
							if( !empty( $pProductHash['attributes'] ) ) {
								if( in_array( $restriction['products_options_values_id'], $pProductHash['attributes'] ) ) {
									$ret &= ($restriction['coupon_restrict']=='N'); // Exact match
								} else {
									$ret &= ($restriction['coupon_restrict']!='N');
								}
							} else {
								$ret = FALSE;
							}
						}
					}

				} else {
					// no restrictions
					$ret = TRUE;
				}
			}
		}
		return $ret;
	}


	function check() {
		global $gBitDb;
		if (!isset($this->check)) {
			$check_query = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'MODULE_ORDER_TOTAL_COUPON_STATUS'");
			$this->check = $check_query->RecordCount();
		}

		return $this->check;
	}

	function keys() {
		return array('MODULE_ORDER_TOTAL_COUPON_STATUS', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS');
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '280', 'Sort order of display.', '6', '2', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Shipping', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(\'true\', \'false\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Tax', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(\'true\', \'false\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', 'Use the following tax class when treating Discount Coupon as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', 'NOW')");
	}

	function remove() {
		global $gBitDb;
		$keys = '';
		$keys_array = $this->keys();
		for ($i=0; $i<sizeof($keys_array); $i++) {
			$keys .= "'" . $keys_array[$i] . "',";
		}
		$keys = substr($keys, 0, -1);

		$gBitDb->Execute("delete from " . TABLE_CONFIGURATION . " where `configuration_key` in (" . $keys . ")");
	}
}
?>

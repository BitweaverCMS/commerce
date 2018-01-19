<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceVoucher.php' );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommercePluginOrderTotalBase.php' );

class ot_coupon extends CommercePluginOrderTotalBase  {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );
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
	}

	protected function getStatusKey() {
		return 'MODULE_ORDER_TOTAL_COUPON_STATUS';
	}

	function process() {
		parent::process();
		global $currencies;

		if( $od_amount = $this->getDiscountHash() ) {
			$this->deduction = $od_amount['total'];
			if ($od_amount['total'] > 0) {
				while (list($key, $value) = each($this->mOrder->info['tax_groups'])) {
					$tax_rate = zen_get_tax_rate_from_desc($key);
					if( !empty( $od_amount[$key] ) ) {
						$this->mOrder->info['tax_groups'][$key] -= $od_amount[$key];
						$this->setOrderDeduction( $od_amount[$key], $key );
					}
				}
				if( !empty( $od_amount['type'] ) && $od_amount['type'] == 'S') {
					$this->mOrder->info['shipping_cost'] = 0;
				}
				$sql = "select coupon_code from " . TABLE_COUPONS . " where coupon_id = ?";
				if( $couponCode = $this->mDb->GetOne($sql, array( $_SESSION['cc_id'] ) ) ) {
					$this->coupon_code = $couponCode;
					$this->setOrderDeduction( $od_amount['total'], $this->coupon_code );
					$this->mProcessingOutput = array( 'code' => $this->code,
														'sort_order' => $this->getSortOrder(),
														'title' => $this->title . ': ' . $this->coupon_code . ' :',
														'text' => '-' . $currencies->format($od_amount['total']),
														'value' => $od_amount['total'] );
				}
			}
		}
	}

	function selection_test() {
		return false;
	}

	function clear_posts() {
		unset($_SESSION['cc_id']);
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


	function collect_posts( $pRequestParams ) {
		global $currencies;
		if ( !empty( $pRequestParams['dc_redeem_code'] ) ) {
			$coupon = new CommerceVoucher();
			if ( !$coupon->load( $pRequestParams['dc_redeem_code'] ) ) {
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_REDEEM_COUPON.'-'.$pRequestParams['dc_redeem_code'] ), 'SSL',true, false));
			} elseif ($coupon->getField('coupon_type') != 'G') {
				// JTD - added missing code here to handle coupon product restrictions
				// look through the items in the cart to see if this coupon is valid for any item in the cart
				$foundvalid = FALSE;
				foreach( array_keys( $this->mOrder->contents ) as $productKey ) {
					$productHash = $this->mOrder->getProductHash( $productKey );
					if ($this->is_product_valid( $productHash, $coupon->mCouponId ) ) {
						$foundvalid = TRUE;
					}
				}
				if (!$foundvalid) {
eb( $pRequestParams );
					zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEXT_INVALID_COUPON_PRODUCT.'-'.$pRequestParams['dc_redeem_code']), 'SSL',true, false));
				}
				// JTD - end of additions of missing code to handle coupon product restrictions

				if( !$coupon->isRedeemable() ) {
					zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode( $coupon->mErrors['redeem_error'].'-'.$pRequestParams['dc_redeem_code'] ), 'SSL',true, false));
				}

				if ($coupon->getField('coupon_type')=='S') {
					if( $coupon->getField( 'restrict_to_shipping' ) ) {
						$shippingMethods = array_map( 'trim', explode( ',', $coupon->getField( 'restrict_to_shipping' ) ) );
						if( in_array( $this->mOrder->info['shipping_method_code'], $shippingMethods ) ) {
							$coupon_amount = $this->mOrder->info['shipping_cost'];
						}
					} else {
						$coupon_amount = $this->mOrder->info['shipping_cost'];
					}
				} else {
					$coupon_amount = $currencies->format($coupon->getField('coupon_amount')) . ' ';
				}
				$_SESSION['cc_id'] = $coupon->mCouponId;
			}
			if( !empty( $pRequestParams['submit_redeem_coupon_x'] ) && !$pRequestParams['gv_redeem_code']) {
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'credit_class_error_code=' . $this->code . '&credit_class_error=' . urlencode(TEST_NO_REDEEM_CODE.'-'.$pRequestParams['dc_redeem_code']), 'SSL', true, false));
			}
		}
	}

	function apply_credit() {
		$cc_id = $_SESSION['cc_id'];
		if( !empty( $this->deduction ) ) {
			$bindVars = array( $cc_id, $_SERVER['REMOTE_ADDR'],  $this->mOrder->customer['customers_id'], $this->mOrder->mOrdersId );
			$this->mDb->query( "INSERT INTO " . TABLE_COUPON_REDEEM_TRACK . " (redeem_date, coupon_id, redeem_ip, customer_id, order_id) VALUES ( now(), ?, ?, ?, ?)", $bindVars );
		}
		$_SESSION['cc_id'] = "";
	}

	function getOrderDeduction( $pOrder ) {

		$od_amount = $this->getDiscountHash();

		return $od_amount['total'] + $od_amount['tax'];
	}

	private function getDiscountHash() {

		$orderTotal = $this->getDiscountTotal();

		$tax_address = zen_get_tax_locations();
		$od_amount['total'] = 0;
		$od_amount['tax'] = 0;
		if ($_SESSION['cc_id']) {
			$coupon = new CommerceVoucher( $_SESSION['cc_id'] );
			if( $coupon->load() && $coupon->isRedeemable() ) {
				if ($coupon->getField( 'coupon_minimum_order' ) <= $orderTotal) {
					if ($coupon->getField( 'coupon_type' )=='S') {
						if( $coupon->getField( 'restrict_to_shipping' ) ) {
							$shippingMethods = array_map( 'trim', explode( ',', $coupon->getField( 'restrict_to_shipping' ) ) );
							if( in_array( $this->mOrder->info['shipping_method_code'], $shippingMethods ) ) {
								$od_amount['total'] = $this->mOrder->info['shipping_cost'];
							}
						} else {
							$od_amount['total'] = $this->mOrder->info['shipping_cost'];
						}
						$od_amount['type'] = 'S';
					} else {
						if ($coupon->getField( 'coupon_type' ) == 'P') {
							// Max discount is a sum of percentages of valid products
							$totalDiscount = 0;
						} else {
							$totalDiscount = $coupon->getField( 'coupon_amount' ) * ($orderTotal>0);
						}
						$runningDiscount = 0;
						$runningDiscountQuantity = 0;
						foreach( array_keys( $this->mOrder->contents ) as $productKey ) {
							$productHash = $this->mOrder->getProductHash( $productKey );
							if( $coupon->getField( 'quantity_max' ) ) {
								if( $discountQuantity = $coupon->getField( 'quantity_max' ) - $runningDiscountQuantity ) {
									if( $discountQuantity > $productHash['products_quantity'] ) {
										$discountQuantity = $productHash['products_quantity'];
									}
								}
							} else {
								$discountQuantity = $productHash['products_quantity'];
							}

							if( $productHash && $discountQuantity && $this->is_product_valid( $productHash, $_SESSION['cc_id'] ) ) {
								// _P_ercentage discount
								if ($coupon->getField( 'coupon_type' ) == 'P') {
									$runningDiscountQuantity += $discountQuantity;
									$itemDiscount = round( ($productHash['final_price'] * $discountQuantity) * ($coupon->getField( 'coupon_amount' )/100), 2 );
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
											$ratio = $runningDiscount / $this->getDiscountTotal();
											$tax_rate = zen_get_tax_rate($productHash['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
											$tax_desc = zen_get_tax_description($productHash['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
											if ($tax_rate > 0) {
												if( empty( $od_amount[$tax_desc] ) ) { $od_amount[$tax_desc] = 0; }
												$od_amount[$tax_desc] += (($productHash['final_price'] * $discountQuantity) * $tax_rate)/100 * $ratio;
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
											$ratio = $runningDiscount / $this->getDiscountTotal();
											$t_prid = zen_get_prid( $productKey );
											$cc_result = $this->mDb->query("select `products_tax_class_id` from " . TABLE_PRODUCTS . " where `products_id` = ?", array( $t_prid ) );

											if ($this->is_product_valid( $productHash, $_SESSION['cc_id'])) {
												if( $runningDiscount < $totalDiscount ) {
													$runningDiscount += ($productHash['final_price'] * $discountQuantity);
												}
												if( $runningDiscount > $totalDiscount ) {
													$runningDiscount = $totalDiscount;
												}
												$tax_rate = zen_get_tax_rate($cc_result->fields['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
												$tax_desc = zen_get_tax_description($cc_result->fields['products_tax_class_id'], $tax_address['country_id'], $tax_address['zone_id']);
												if ($tax_rate > 0) {
													if( empty( $od_amount[$tax_desc] ) ) { $od_amount[$tax_desc] = 0; }
													$od_amount[$tax_desc] += (($productHash['final_price'] * $discountQuantity) * $tax_rate)/100 * $ratio;
													$od_amount['tax'] += $od_amount[$tax_desc];
												}
											}
											break;
									}
								}
							}
						}
						$od_amount['total'] = $runningDiscount;
						if ($od_amount['total']>$orderTotal) {
							$od_amount['total'] = $orderTotal;
						}
					}
				}
			}
		}
		return $od_amount;
	}


	function is_product_valid( $pProductHash, $coupon_id) {
		$ret = FALSE;

		// gifts are not valid, so only check non-gifts
		if( !preg_match( '/^GIFT/', $pProductHash['products_model'] ) ) {
			if( is_numeric( $coupon_id ) ) {
				$ret = TRUE;
				$query = "SELECT * FROM " . TABLE_COUPON_RESTRICT . " WHERE `coupon_id` = ?  ORDER BY ".$this->mDb->convertSortmode( 'coupon_restrict_asc' );
				if( $restrictions = $this->mDb->GetAll( $query, array( $coupon_id ) ) ) {
					$coupAllow = FALSE; // ($rs->RecordCount() == 0); // if there are restictions, assume false
					$coupDeny = FALSE; // DENY is assumed false, and an explicit match will override all other potential matches

					foreach( $restrictions as $restriction ) {
						// specific product_id  - are we exclusive or inclusive?
						if( !empty( $restriction['product_id'] ) ) {
							$prodIsMatch = ($restriction['product_id'] == $pProductHash['products_id']);

							if( $prodIsMatch && $restriction['coupon_restrict'] == 'Y' ) {
								$coupDeny = TRUE; // Product CANNOT be in this type - trumps all else
							} elseif( $prodIsMatch && $restriction['coupon_restrict'] == 'N' ) {
								$coupAllow = TRUE; // Product MUST be in the category
							}
						}

						// specific category_id  - are we exclusive or inclusive?
						if( !empty( $restriction['category_id'] ) ) {
							// check master cat quickly, or go deep diving
							$prodIsMatch = ($pProductHash['master_categories_id'] ==  $restriction['category_id']) || zen_product_in_category( $pProductHash['products_id'], $restriction['category_id'] );
							if( $prodIsMatch && $restriction['coupon_restrict'] == 'Y' ) {
								$coupDeny = TRUE; // Product CANNOT be in this category - trumps all else
							} elseif( $prodIsMatch && $restriction['coupon_restrict'] == 'N' ) {
								$coupAllow = TRUE; // Product MUST be in the category
							}
						}

						// specific product_type_id  - are we exclusive or inclusive?
						if( !empty( $restriction['product_type_id'] ) ) {
							$prodIsMatch = ($restriction['product_type_id'] == $pProductHash['products_type']);

							if( $prodIsMatch && $restriction['coupon_restrict'] == 'Y' ) {
								$coupDeny = TRUE; // Product CANNOT be in this type - trumps all else
							} elseif( $prodIsMatch && $restriction['coupon_restrict'] == 'N' ) {
								$coupAllow = TRUE; // Product MUST be in the category
							}
						}

						// specific products_options_values_id  - are we exclusive or inclusive?
						if( !empty( $restriction['products_options_values_id'] ) ) {
							if( !empty( $pProductHash['attributes'] ) ) {
								foreach( array_keys( $pProductHash['attributes'] ) as $key ) {
									if( $prodIsMatch = ( $restriction['products_options_values_id'] == $pProductHash['attributes'][$key]['options_values_id'] ) ) {
										break;
									}
								}

								if( $prodIsMatch && $restriction['coupon_restrict'] == 'Y' ) {
									$coupDeny = TRUE; // Product CANNOT be in this type - trumps all else
								} elseif( $prodIsMatch && $restriction['coupon_restrict'] == 'N' ) {
									$coupAllow = TRUE; // Product MUST be in the category
								}

							} else {
								$coupDeny = TRUE;
							}
						}
					}
					$ret = ((!$coupDeny) && $coupAllow); //
				}
			} else {
				// no restrictions
				$ret = TRUE;
			}
		} else {
		}
		return $ret;
	}

	private function getDiscountTotal() {

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
		return array('MODULE_ORDER_TOTAL_COUPON_STATUS', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS');
	}

	function install() {
		global $gBitDb;
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function`, `date_added`) values ('This module is installed', 'MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', '', '6', '1','zen_cfg_select_option(array(\'true\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`) values ('Sort Order', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER', '280', 'Sort order of display.', '6', '2', now())");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Shipping', 'MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING', 'true', 'Include Shipping in calculation', '6', '5', 'zen_cfg_select_option(array(''true'', ''false''), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Include Tax', 'MODULE_ORDER_TOTAL_COUPON_INC_TAX', 'false', 'Include Tax in calculation.', '6', '6','zen_cfg_select_option(array(''true'', ''false''), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `set_function` ,`date_added`) values ('Re-calculate Tax', 'MODULE_ORDER_TOTAL_COUPON_CALC_TAX', 'Standard', 'Re-Calculate Tax', '6', '7','zen_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ', 'NOW')");
		$gBitDb->Execute("insert into " . TABLE_CONFIGURATION . " (`configuration_title`, `configuration_key`, `configuration_value`, `configuration_description`, `configuration_group_id`, `sort_order`, `use_function`, `set_function`, `date_added`) values ('Tax Class', 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS', '0', 'Use the following tax class when treating Discount Coupon as Credit Note.', '6', '0', 'zen_get_tax_class_title', 'zen_cfg_pull_down_tax_classes(', 'NOW')");
	}
}

<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2017 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceVoucher.php' );
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommercePluginOrderTotalBase.php' );

class ot_coupon extends CommercePluginOrderTotalBase  {

	function __construct( $pOrder=NULL ) {
		parent::__construct( $pOrder );

		$this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
		$this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
		if( $this->isEnabled() ) {
			$this->user_prompt = '';
			$this->include_shipping = MODULE_ORDER_TOTAL_COUPON_INC_SHIPPING;
			$this->include_tax = MODULE_ORDER_TOTAL_COUPON_INC_TAX;
			$this->calculate_tax = MODULE_ORDER_TOTAL_COUPON_CALC_TAX;
			$this->tax_class	= MODULE_ORDER_TOTAL_COUPON_TAX_CLASS;
			$this->credit_class = true;
		}
	}

	function process( $pPaymentParams, &$pSessionParams ) {
		parent::process( $pPaymentParams, $pSessionParams );
		global $currencies;

		if( $od_amount = $this->getDiscountHash( $pSessionParams ) ) {
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
				if( $couponCode = $this->mDb->GetOne($sql, array( $pSessionParams['cc_id'] ) ) ) {
					$this->coupon_code = $couponCode;
					$this->setOrderDeduction( $od_amount['total'], $this->coupon_code );
					$this->mProcessingOutput = array( 'code' => $this->code,
														'sort_order' => $this->getSortOrder(),
														'title' => $this->title . ': ' . $this->coupon_code,
														'text' => '-' . $currencies->format($od_amount['total']),
														'value' => $od_amount['total'] );
				}
			}
		}
	}

	function selection_test() {
		return false;
	}

	protected function getSessionVars() {
		return array_merge( parent::getSessionVars(), array( 'cc_id', 'dc_redeem_code' ) );
	}

	function use_credit_amount() {
		return false;
	}


	public function credit_selection( $pOrder, &$pSessionParams ) {
		$selection = array(	'id' => $this->code,
							 'module' => $this->title,
							 'fields' => array(
								array('title' => 'Coupon Code', 'field' => zen_draw_input_field('dc_redeem_code', BitBase::getParameter( $pSessionParams, 'dc_redeem_code' )))
							)
						);
		return $selection;
	}


	function collect_posts( $pRequestParams, &$pSessionParams ) {
		global $currencies;
		$error = FALSE;

		if ( $couponCode = trim( BitBase::getParameter( $pRequestParams, 'dc_redeem_code' ) ) ) {
			$coupon = new CommerceVoucher();
			if ( !$coupon->load( $couponCode ) ) {
				$error = TEXT_INVALID_REDEEM_COUPON.'-'.$couponCode;
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
				// JTD - end of additions of missing code to handle coupon product restrictions

				if (!$foundvalid) {
					$error = TEXT_INVALID_COUPON_PRODUCT.' "'.$couponCode.'"';
				} elseif( !$coupon->isRedeemable() ) {
					$error = $coupon->mErrors['redeem_error'].'-'.$couponCode;
				} else {
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
					$pSessionParams['cc_id'] = $coupon->mCouponId;
					$pSessionParams['dc_redeem_code'] = $couponCode;
				}
			}
		}

		return $error;
	}

	function apply_credit( &$pSessionParams ) {
		if( $cc_id = BitBase::getParameter( $pSessionParams, 'cc_id' ) ) {
			if( !empty( $this->deduction ) ) {
				$bindVars = array( $cc_id, $_SERVER['REMOTE_ADDR'],  $this->mOrder->customer['customers_id'], $this->mOrder->mOrdersId );
				$this->mDb->query( "INSERT INTO " . TABLE_COUPON_REDEEM_TRACK . " (redeem_date, coupon_id, redeem_ip, customer_id, order_id) VALUES ( now(), ?, ?, ?, ?)", $bindVars );
			}
			$pSessionParams['cc_id'] = "";
			$pSessionParams['dc_redeem_code'] = "";
		}
	}

	function getOrderDeduction( $pOrder, &$pSessionParams ) {

		$od_amount = $this->getDiscountHash( $pSessionParams );

		return $od_amount['total'] + $od_amount['tax'];
	}

	private function getDiscountHash( &$pSessionParams ) {

		$orderTotal = $this->getDiscountTotal();

		$tax_address = zen_get_tax_locations();
		$od_amount['total'] = 0;
		$od_amount['tax'] = 0;

		if( !empty( $pSessionParams['cc_id'] ) ) {
			$coupon = new CommerceVoucher( $pSessionParams['cc_id'] );
			if( $coupon->load() && $coupon->isRedeemable() ) {
				if ($coupon->getField( 'coupon_minimum_order' ) <= $orderTotal) {

					$runningDiscount = 0;
					$runningDiscountQuantity = 0;
					$totalDiscount = 0;

					if ($coupon->getField( 'coupon_type' ) == 'P') {
						// Max discount is a sum of percentages of valid products
						$totalDiscount = 0;
					} else {
						$totalDiscount += $coupon->getField( 'coupon_amount' ) * ($orderTotal>0);
					}

					// account for any free shipping
					if( $coupon->getField( 'free_ship' ) == 'y' ) {
						if( $coupon->getField( 'restrict_to_shipping' ) ) {
							$shippingMethods = array_map( 'trim', explode( ',', $coupon->getField( 'restrict_to_shipping' ) ) );
							if( in_array( $this->mOrder->info['shipping_method_code'], $shippingMethods ) ) {
								$totalDiscount += $this->mOrder->info['shipping_cost'];
							}
						} else {
							$totalDiscount += $this->mOrder->info['shipping_cost'];
						}
						$runningDiscount += $totalDiscount;
					}

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

						if( $productHash && $discountQuantity && $this->is_product_valid( $productHash, $pSessionParams['cc_id'] ) ) {
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
								if( !empty( $tax_address ) ) {
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
								}
							// _F_ixed discount
							} elseif ($coupon->getField( 'coupon_type' ) == 'F') {
								if( !empty( $tax_address ) ) {
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

											if ($this->is_product_valid( $productHash, $pSessionParams['cc_id'])) {
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
					}
					$od_amount['total'] = $runningDiscount;
					if ($od_amount['total']>$orderTotal) {
						$od_amount['total'] = $orderTotal;
					}
				}
			}
		}

		return $od_amount;
	}


	function is_product_valid( $pProductHash, $coupon_id) {
		$ret = FALSE;

		// Should be a product/class/category config if gifts are not valid, so only check non-gifts
//		if( !preg_match( '/^GIFT/', $pProductHash['products_model'] ) ) {
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
									if( $prodIsMatch = ( $restriction['products_options_values_id'] == $pProductHash['attributes'][$key]['products_options_values_id'] ) ) {
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
//		}
		return $ret;
	}

	private function getDiscountTotal() {

		$orderTotal = $this->mOrder->info['total'];

		if ($this->include_tax == 'false') {
			$orderTotal -= $this->mOrder->info['tax'];
		}

		if ($this->include_shipping == 'false') {
			// do nothing here because this is controlled but the free_ship column
			// $orderTotal -= $this->mOrder->info['shipping_cost'];
		}

		return $orderTotal;
	}

	// {{{	++++++++ config ++++++++
	/*
	* rows for com_configuration table as associative array of column => value
	*/
	protected function config() {
		$parentConfig = parent::config();
		$i = count( $parentConfig );
		return array_merge( $parentConfig, array( 
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
				'set_function' => "zen_cfg_select_option(array(''None'', ''Standard'', ''Credit Note''), ",
			),
			$this->getModuleKeyTrunk().'_TAX_CLASS' => array(
				'configuration_title' => 'Tax Class',
				'configuration_description' => 'Use the following tax class when treating Discount Coupon as Credit Note.',
				'configuration_value' => '0',
				'sort_order' => $i++,
				'set_function' => "zen_cfg_pull_down_tax_classes(",
				'use_function' => "zen_get_tax_class_title",
			),
		) );
		// set some default values
		$ret[$this->getModuleKeyTrunk().'_SORT_ORDER']['configuration_value'] = '280';
		return $ret;
	}
	// }}} ++++ config ++++
}

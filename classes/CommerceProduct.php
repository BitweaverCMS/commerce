<?php
// :vim:tabstop=4:
// +--------------------------------------------------------------------+
// | Copyright (c) 2005-2010 bitcommerce.org							|
// | http://www.bitcommerce.org											|
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
// | Portions Copyright (c) 2003 The zen-cart developers				|
// | Portions Copyright (c) 2003 osCommerce								|
// +--------------------------------------------------------------------+
//
/**
 * Product class for handling all production manipulation
 *
 * @package	bitcommerce
 * @author	 spider <spider@steelsun.com>
 */

/**
 * Initialization
 */
require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );

/**
 * Class for handling the commerce product
 *
 * @package bitcommerce
 */
class CommerceProduct extends LibertyMime {
	public $mProductsId;
	public $mOptions;
	public $mDiscounts;

	function __construct( $pProductsId=NULL, $pContentId=NULL ) {
		$this->mProductsId = $pProductsId;
		parent::__construct();
		$this->registerContentType( BITPRODUCT_CONTENT_TYPE_GUID, array(
						'content_type_guid' => BITPRODUCT_CONTENT_TYPE_GUID,
						'content_name' => 'Product',
						'handler_class' => 'CommerceProduct',
						'handler_package' => 'bitcommerce',
						'handler_file' => 'classes/CommerceProduct.php',
						'maintainer_url' => 'http://www.bitcommerce.org'
				) );
		$this->mContentId = $pContentId;
		$this->mContentTypeGuid = BITPRODUCT_CONTENT_TYPE_GUID;
		$this->mViewContentPerm	= 'p_bitcommerce_product_view';
		$this->mUpdateContentPerm	= 'p_bitcommerce_product_update';
		$this->mCreateContentPerm	= 'p_bitcommerce_product_create';
		$this->mAdminContentPerm = 'p_bitcommerce_admin';
		$this->mOptions = NULL;
		$this->mDiscounts = NULL;
	}

	public function __sleep() {
		return array_merge( parent::__sleep(), array( 'mProductsId', 'mOptions', 'mDiscounts' ) );
	}

	// Override LibertyBase method
	public static function getNewObjectById( $pClass, $pPrimaryId, $pLoadFromCache=TRUE ) {
		return bc_get_commerce_product( array( 'products_id' => $pPrimaryId ) );
	}

	// Override LibertyBase method
	public static function getNewObject( $pClass, $pContentId, $pLoadFromCache=TRUE ) {
		return bc_get_commerce_product( array( 'content_id' => $pContentId ) );
	}

	public function getCacheKey() {
		if( $this->isValid() ) {
			return $this->mProductsId;
		}
	}

	function load( $pContentId=NULL, $pPluginParams = TRUE ) {
		global $gBitUser;
		if( empty( $this->mProductsId ) && !empty( $this->mContentId ) ) {
			$this->mProductsId = $this->mDb->getOne( "SELECT `products_id` FROM ".TABLE_PRODUCTS." WHERE `content_id`=?", array( $this->mContentId ) );
		}
		if( $this->verifyId( $this->mProductsId ) && $this->mInfo = $this->getProduct( $this->mProductsId ) ) {
			$this->mContentId = $this->getField( 'content_id' );
			parent::load();
			if( $this->isDeleted() && !($gBitUser->hasPermission( 'p_bitcommerce_admin' ) || $this->isPurchased()) ) {
				$this->mInfo = array();
				unset( $this->mProductsId );
			} else {
				$this->mContentId = $this->mInfo['content_id'];
			}
		}
		return( count( $this->mInfo ) );
	}

	// LibertyMime override
	function getStorageSubDirName( $pFileHash = NULL ) {
		return 'products';
	}

	function loadByRelatedContent( $pContentId ) {
		if( $this->verifyId( $pContentId ) ) {
			if( $this->mProductsId = $this->mDb->getOne( "SELECT `products_id` FROM " . TABLE_PRODUCTS . " WHERE `related_content_id`=?", array( $pContentId ) ) ) {
				return( $this->load() );
			}
		}
	}

	function getProduct( $pProductsId ) {
		$ret = NULL;
		if( $this->verifyId( $pProductsId ) ) {
			$bindVars = array(); $selectSql = ''; $joinSql = ''; $whereSql = '';
			array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1, $pProductsId );
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );
			$query = "SELECT p.*, pd.*, pt.*, uu.`real_name`, uu.`login` $selectSql , m.*, cat.*, catd.*, lc.*
						FROM " . TABLE_PRODUCTS . " p
							INNER JOIN ".TABLE_PRODUCT_TYPES." pt ON (p.`products_type`=pt.`type_id`)
							INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=p.`content_id`)
							INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=lc.`user_id`) 
							INNER JOIN ".TABLE_CATEGORIES." cat ON ( p.`master_categories_id`=cat.`categories_id` )
						$joinSql
						LEFT OUTER JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON (p.`products_id`=pd.`products_id` AND pd.`language_id`=?)
						LEFT OUTER JOIN ".TABLE_CATEGORIES_DESCRIPTION." catd ON ( cat.`categories_id`=catd.`categories_id` AND catd.`language_id`=pd.`language_id` )
						LEFT OUTER JOIN ".TABLE_MANUFACTURERS." m ON ( p.`manufacturers_id`=m.`manufacturers_id` )
						LEFT OUTER JOIN ".TABLE_SUPPLIERS." s ON ( p.`suppliers_id`=s.`suppliers_id` )						WHERE p.`products_id`=? $whereSql";
// Leave these out for now... and possibly forever. These can produce multiple row returns
//						LEFT OUTER JOIN ".TABLE_TAX_CLASS." txc ON ( p.`products_tax_class_id`=txc.`tax_class_id` )
//						LEFT OUTER JOIN ".TABLE_TAX_RATES." txr ON ( txr.`tax_class_id`=txc.`tax_class_id` )
			if( $ret = $this->mDb->getRow( $query, $bindVars ) ) {
				if( !empty( $ret['products_image'] ) ) {
					$ret['products_image_url'] = $ret['type_class']::getImageUrlFromHash( $ret );
				} else {
					$ret['products_image_url'] = NULL;
				}
				$ret['products_weight_kg'] = $ret['products_weight'] * .45359;
				$ret['info_page'] = $ret['type_handler'].'_info';
			}
		}
		return $ret;
	}

	function getProductsTypeName() {
		return $this->getField( 'type_name', 'Product' );
	}

	function getProductsModel() {
		return $this->getField( 'products_model', $this->getField( 'type_name', 'Product' ) );
	}

	function exportList( $pList ) {
		$ret = parent::exportList( $pList );
		foreach( $pList as $key=>$hash ) {
			$ret[$key]['product_id'] = $hash['products_id'];
			$ret[$key]['product_type'] = $hash['type_class'];
		}
		return $ret;
	}

	function exportHash() {
		$ret = parent::exportHash();
		$ret['product_id'] = $this->mProductsId;
		$ret['product_type'] = $this->getField( 'type_class' );
		return $ret;
	}
	// {{{ =================== Product Pricing Methods ====================

	// User specific commission discount, used for  backing out commissions of an aggregate price, such as that returned by getBasePrice
	function getCommissionUserDiscount() {
		global $gBitUser;
		$ret = 0;
		if( $this->isValid() ) {
			$ret = $this->hasUpdatePermission( FALSE ) ? $this->getField( 'products_commission' ) : 0;
		}
		return $ret;
	}

	// User specific commission charges
	function getCommissionUserCharges() {
		global $gBitUser;
		$ret = 0;
		if( $this->isValid() ) {
			$ret = $this->hasUpdatePermission( FALSE ) ? 0 : $this->getField( 'products_commission' );
		}
		return $ret;
	}

	////
	// computes products_price + option groups lowest attributes price of each group when on
	function getBasePrice() {
		global $gBitDb, $gBitUser;

		// is there a products_price to add to attributes
		$basePrice = $this->getField('products_price') + $this->getField( 'products_commission' );

		$this->loadAttributes();

		if( $this->getField( 'products_priced_by_attribute' ) == '1' ) {
			$the_options_id= 'x';
			$the_base_price= 0;
			foreach( array_keys( $this->mOptions ) as $optionsId ) {
				if( !empty( $this->mOptions[$optionsId]['attributes_required'] ) && $this->mOptions[$optionsId]['attributes_required'] == '1' ) {
					$basePrice += $this->mOptions[$optionsId]['options_values_price'];
				}
			}
		}
		return $basePrice;
	}

	function getLowestPrice() {
		$ret = 0;
		if( $this->isValid() && $this->getField( 'product_is_free' ) == '1' ) {
			$ret = $this->getBasePrice();
			// Check several cases that can change the base price - specials or sales
			$specialPrice = $this->getSpecialPrice();
			if( $specialPrice < $ret ) {
				$ret = $specialPrice;
			}
			$salePrice = $this->getSalePrice();
			if( $salePrice < $ret ) {
				$ret = $salePrice;
			}
			// keep lowest_purchase_price updated
			if( $ret != $this->getField( 'lowest_purchase_price' ) ) {
				$this->mDb->query( "UPDATE " . TABLE_PRODUCTS . " SET `lowest_purchase_price` = ? WHERE `products_id` = ?", array( $ret, $this->mProductsId ) );
			}
		}
		return $ret;
	}

	////
	// computes products_price + option groups lowest attributes price of each group when on
	function getOneTimeCharges( $pQuantity, $pAttributes ) {
		$ret = 0;
		if( !empty( $pAttributes ) ) {
			foreach( $pAttributes as $valueId ) {
				if( $option = $this->getOptionValue( NULL, $valueId ) ) {
					if( $option['product_attribute_is_free'] != '1' && !$this->getField( 'product_is_free' ) ) {
						// calculate additional one time charges
						if( !empty( $option['attributes_price_onetime'] ) ) {
							$ret += $option['attributes_price_onetime'];
						}
						if( !empty( $option['attributes_pf_onetime'] ) ) {
							$ret = zen_get_attributes_price_factor( $this->getBasePrice(), $this->getSalePrice(), $option['attributes_pf_onetime'], $option['attributes_pf_onetime_offset']);
						}
						if( !empty( $option['attributes_qty_prices_onetime'] ) ) {
							$ret = zen_get_attributes_qty_prices_onetime($option['attributes_qty_prices_onetime'], $pQty);
						}
					}
				}
			}
		}
		return $ret;
	}

	function getSpecialPrice() {
		$ret = FALSE;

		if( $this->isValid() ) {
			$ret = $this->getSalePrice( TRUE );
		}
		return $ret;
	}

	// get specials price or sale price
	function getSalePrice( $pSpecialsOnly=false ) {
		$ret = FALSE;

		if( $this->isValid() ) {
			if( !array_key_exists( 'special_price', $this->mInfo ) ) {
				$this->mInfo['special_price'] = $this->mDb->GetOne("select `specials_new_products_price` from " . TABLE_SPECIALS . " where `products_id`=? and `status` ='1'", array( $this->mProductsId ) );
			}

			// return special price only or Never apply a salededuction to Ian Wilson's Giftvouchers
			if( substr($this->getField( 'products_model' ), 0, 4) == 'GIFT' || $pSpecialsOnly ) {
				if( !empty( $this->mInfo['special_price'] ) ) {
					$ret = $this->mInfo['special_price'];
				}
			} else {
				$lowestPrice = $this->getBasePrice();
				// get sale price
				$query ="select `sale_specials_condition`, `sale_deduction_value`, `sale_deduction_type`
						from " . TABLE_SALEMAKER_SALES . "
						where `sale_categories_all` like '%,".$this->getField( 'master_categories_id' ).",%'
							and `sale_status` = '1'
							and (`sale_date_start` <= 'NOW' or `sale_date_start` = '0001-01-01')
							and (`sale_date_end` >= 'NOW' or `sale_date_end` = '0001-01-01')
							and (`sale_pricerange_from` <= ? or `sale_pricerange_from` = '0')
							and (`sale_pricerange_to` >= ? or `sale_pricerange_to` = '0')";
				if( $sale = $this->mDb->getAssoc( $query, array( $lowestPrice, $lowestPrice ) ) ) {
					$tmp_special_price = !empty( $this->mInfo['special_price'] ) ? $this->mInfo['special_price'] : $lowestPrice;
					switch ($sale['sale_deduction_type']) {
						case 0:
							$sale_product_price = $lowestPrice - $sale['sale_deduction_value'];
							$sale_special_price = $tmp_special_price - $sale['sale_deduction_value'];
							break;
						case 1:
							$sale_product_price = $lowestPrice - (($lowestPrice * $sale['sale_deduction_value']) / 100);
							$sale_special_price = $tmp_special_price - (($tmp_special_price * $sale['sale_deduction_value']) / 100);
							break;
						case 2:
							$sale_product_price = $sale['sale_deduction_value'];
							$sale_special_price = $sale['sale_deduction_value'];
							break;
					}
				} else {
					// no sale, just return whatever the most recent special price was
					return $this->mInfo['special_price'];
				}

				if ($sale_product_price < 0) {
					$sale_product_price = 0;
				}

				if ($sale_special_price < 0) {
					$sale_special_price = 0;
				}

				if( empty( $this->mInfo['special_price'] ) ) {
					$ret = number_format($sale_product_price, 4, '.', '');
				} else {
					switch($sale['sale_specials_condition']){
						case 0:
							$ret = number_format($sale_product_price, 4, '.', '');
							break;
						case 1:
							$ret = number_format($this->mInfo['special_price'], 4, '.', '');
							break;
						case 2:
							$ret = number_format($sale_special_price, 4, '.', '');
							break;
						default:
							$ret = number_format($this->mInfo['special_price'], 4, '.', '');
					}
				}
			}
		}
		return $ret;
	}


	////
	// look up discount in sale makers - attributes only can have discounts if set as percentages
	// this gets the discount amount this does not determin when to apply the discount
	function getSaleDiscountType( $categories_id = false, $return_value = false ) {
		global $currencies;
		global $gBitDb;

/*

0 = flat amount off base price with a special
1 = Percentage off base price with a special
2 = New Price with a special

5 = No Sale or Skip Products with Special

special options + option * 10
0 = Ignore special and apply to Price
1 = Skip Products with Specials switch to 5
2 = Apply to Special Price

If a special exist * 10+9

0*100 + 0*10 = flat apply to price = 0 or 9
0*100 + 1*10 = flat skip Specials = 5 or 59
0*100 + 2*10 = flat apply to special = 20 or 209

1*100 + 0*10 = Percentage apply to price = 100 or 1009
1*100 + 1*10 = Percentage skip Specials = 110 or 1109 / 5 or 59
1*100 + 2*10 = Percentage apply to special = 120 or 1209

2*100 + 0*10 = New Price apply to price = 200 or 2009
2*100 + 1*10 = New Price skip Specials = 210 or 2109 / 5 or 59
2*100 + 2*10 = New Price apply to Special = 220 or 2209

*/

		// get products category
		if ($categories_id == true) {
			$check_category = $categories_id;
		} else {
			$check_category = $this->getField( 'master_categories_id' );
		}

		$deduction_type_array = array(array('id' => '0', 'text' => tra( 'Deduct amount' )),
																	array('id' => '1', 'text' => tra( 'Percent' )),
																	array('id' => '2', 'text' => tra( 'New Price' )));

		$sale_exists = 'false';
		$sale_maker_discount = '';
		$sale_maker_special_condition = '';

		static $salemakerSales = NULL;

		if( $salemakerSales === NULL ) {
			$salemakerSales = $this->mDb->getAssoc("select `sale_id`, `sale_status`, `sale_name`, `sale_categories_all`, `sale_deduction_value`, `sale_deduction_type`, `sale_pricerange_from`, `sale_pricerange_to`, `sale_specials_condition`, `sale_categories_selected`, `sale_date_start`, `sale_date_end`, `sale_date_added`, `sale_date_last_modified`, `sale_date_status_change` from " . TABLE_SALEMAKER_SALES . " where `sale_status`='1'", NULL, NULL, $this->cacheQueryTime() );
		}
		foreach( array_keys( $salemakerSales ) as $saleId ) {
			$categories = explode(',', $salemakerSales[$saleId]['sale_categories_all']);
			while (list($key,$value) = each($categories)) {
				if ($value == $check_category) {
					$sale_exists = 'true';
					$sale_maker_discount = $salemakerSales[$saleId]['sale_deduction_value'];
					$sale_maker_special_condition = $salemakerSales[$saleId]['sale_specials_condition'];
					$sale_maker_discount_type = $salemakerSales[$saleId]['sale_deduction_type'];
					break;
				}
			}
		}

		$check_special = $this->getSpecialPrice();

		if ($sale_exists == 'true' and $sale_maker_special_condition != 0) {
			$sale_maker_discount_type = (($sale_maker_discount_type * 100) + ($sale_maker_special_condition * 10));
		} else {
			$sale_maker_discount_type = 5;
		}

		if (!$check_special) {
			// do nothing
		} else {
			$sale_maker_discount_type = ($sale_maker_discount_type * 10) + 9;
		}

		switch (true) {
			case (!$return_value):
				return $sale_maker_discount_type;
				break;
			case ($return_value == 'amount'):
				return $sale_maker_discount;
				break;
			default:
				return 'Unknown Request';
				break;
		}
	}



	function getWeight( $pQuantity=1, $pAttributes=array() ) {
		$ret = 0;
		// only count if not free shipping
		if( $this->getField('product_is_always_free_ship') != 1) {
			$ret = $this->getField( 'products_weight' );
			// account for any additional attributes such as a shopping cart or order
			if( $pAttributes ) {
				foreach( $pAttributes as $optionId => $valueId ) {
					$query = "SELECT `products_attributes_wt_pfix`||`products_attributes_wt` AS `weight` FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa WHERE pa.`products_options_id` = ? AND pa.`products_options_values_id` = ?";
					$ret += (int)$this->mDb->getOne( $query, array( (int)$optionId, (int)$valueId ) );
				}
			} // attributes weight
		}
		return $pQuantity * $ret;
	}


	//
	function getPurchasePrice( $pQuantity=1, $pAttributes=array() ) {
		$ret = NULL;
		if( $this->isValid() ) {
			if( $this->getField( 'product_is_free' ) ) {
			} else {
				// Base price always includes the commission. For purchasing, we might not have to pay it, so back it out
				$ret = $this->getBasePrice() - $this->getCommissionUserDiscount();

				// Product is on sale, that is our new starting point
				if( $salePrice = $this->getSalePrice() ) {
					$ret = $salePrice;
				}

				// discount qty pricing
				if( $this->getField('products_discount_type') ) {
					$ret = $this->getQuantityPrice( $pQuantity, $ret );
				}
			}

			if( $pAttributes ) {
				// loop through passed in attributes and add addition cost to the price
				foreach( $pAttributes as $optionId => $att ) {
					if( is_numeric( $att ) ) {
						// cart has a simple list of $optionId=$valueId
						$valueId = $att;
					} elseif( !empty( $att['options_values_id'] ) ) {
						// order has a hash of $optionId=$attributeHash
						$optionId = $att['options_id'];
						$valueId = $att['options_values_id'];
					}
					$finalPrince = $this->getAttributesPriceFinalRecurring( $valueId, $pQuantity );
					$taxRate = zen_get_tax_rate( $this->getField( 'products_tax_class_id' ) );
					$ret += zen_add_tax( $finalPrince, $taxRate );

					// $ret += zen_add_tax( $this->getAttributesPriceFinalRecurring( $valueId, $pQuantity ), zen_get_tax_rate( $this->getField( 'products_tax_class_id' ) ) );
				}
			}

		}

		return $ret;
	}

	function getCostPrice( $pQuantity=1, $pAttributes=array() ) {
		$cost = $this->getField( 'products_cogs' );
		if( !empty( $pAttributes ) ) {
			foreach( $pAttributes as &$attr ) {
				if( !empty( $attr['options_values_cogs'] ) ) {
					$cost += $pAttributes['options_values_cogs'];
				}
			}
		}
		return $pQuantity * $cost;
	}

	function getWholesalePrice( $pQuantity=1, $pAttributes=array() ) {
		$wholesale = $this->getField( 'products_wholesale' );
		if( !empty( $pAttributes ) ) {
			foreach( $pAttributes as &$attr ) {
				if( !empty( $attr['options_values_wholesale'] ) ) {
					$wholesale += $pAttributes['options_values_wholesale'];
				}
			}
		}
		return $pQuantity * $wholesale;
	}

	// check a given price for a quantity discount. it is the responsibility of the calling function to determine if this method is appropirate, ie. it should check attributes_discounted,  etc...
	function getQuantityPrice( $pQuantity, $pCheckAmount = NULL ) {
		global $gBitDb, $gBitCustomer;
		if( $this->getField( 'products_mixed_discount_qty' ) && is_object( $gBitCustomer->mCart ) ) {
			$mixedQuantity = $gBitCustomer->mCart->in_cart_mixed_discount_quantity( $this->mProductsId );
			// mixed attribute products are all considered in the total quantity discount
			if( $mixedQuantity > $pQuantity) {
				$pQuantity = $mixedQuantity;
			}
		}

		$discountPrice = $this->getDiscount( $pQuantity, 'discount_price' );

		// we might have a 0 check amount, so check for is_null
		if( is_null( $pCheckAmount ) ) {
			$pCheckAmount = $this->getBasePrice();
		}
		$display_specials_price = $this->getSpecialPrice();

		switch( $this->getField('products_discount_type') ) {
			// not discounted, return what was passed in
			case (empty( $discountPrice )):
			case '0':
					$discounted_price = $pCheckAmount;
				break;
			// percentage discount
			case '1':
				if ($this->getField('products_discount_type_from') == '0') {
					// priced by attributes
					$checkPrice = $pCheckAmount;
				} elseif ( $display_specials_price ) {
					$checkPrice = $display_specials_price;
				} else {
					$checkPrice = $pCheckAmount;
				}
				$discounted_price = $checkPrice - ($checkPrice * ($discountPrice/100));

				break;
			// actual price
			case '2':
				$discounted_price = $discountPrice;
				break;
			// amount offprice
			case '3':
				if ($this->getField('products_discount_type_from') == '0') {
					$discounted_price = $pCheckAmount - $discountPrice;
				} else {
					if (!$display_specials_price) {
						$discounted_price = $pCheckAmount - $discountPrice;
					} else {
						$discounted_price = $display_specials_price - $discountPrice;
					}
				}
				break;
		}
		return $discounted_price;
	}


	static function getNotatedPrice( $pPrice, $pTaxClassId ) {
		global $currencies;
		return $currencies->display_price( $pPrice, zen_get_tax_rate( $pTaxClassId ) );
	}

	public function getDisplayPrice() {
		if( $this->isValid() ) {
			static::getDisplayPriceFromHash( $this->mInfo );
		}
	}

	////
	// Display Price Retail
	// Specials and Tax Included
	public static function getDisplayPriceFromHash( $pProductsMixed=NULL ) {
		global $gBitDb, $gBitUser;
		$ret = '';

		if( STORE_STATUS == '1' ) {
			// Showcase no prices
			$ret = '';
		} elseif( CUSTOMERS_APPROVAL == '1' && !$gBitUser->isRegistered() ) {
			// customer must be logged in to browse
			$ret = '';
		} elseif( CUSTOMERS_APPROVAL == '2' && !$gBitUser->isRegistered() ) {
			// customer may browse but no prices
			$ret = TEXT_LOGIN_FOR_PRICE_PRICE;
		} elseif(CUSTOMERS_APPROVAL == '3' and TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM != '') {
			// customer may browse but no prices
			$ret = TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
		} elseif(CUSTOMERS_APPROVAL_AUTHORIZATION != '0' && !$gBitUser->isRegistered()) {
			// customer must be logged in to browse
			$ret = TEXT_AUTHORIZATION_PENDING_PRICE;
		} elseif((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customers_authorization'] > '0') {
			// customer must be logged in to browse
			$ret = TEXT_AUTHORIZATION_PENDING_PRICE;
		} else {
			// proceed normally

			if( is_array( $pProductsMixed ) ) {
				$productHash = $pProductsMixed;
			} elseif( BitBase::verifyId( $pProductsMixed ) ) {
				// $new_fields = ', `product_is_free`, `product_is_call`, `product_is_showroom_only`';
				$productHash = $gBitDb->getRow( "SELECT * FROM " . TABLE_PRODUCTS . " WHERE `products_id` = ? ", array( (int)$pProductsMixed ) );
			}

			if( $productHash ) {
				$show_sale_discount = '';
				$discountAmount = $productHash['products_price'] - $productHash['lowest_purchase_price'];
				// If Free, Show it
				if ($productHash['product_is_free'] == '1') {
					$final_display_price = '<span class="free">'.tra( 'FREE' ).'</span>';
				} elseif( $discountAmount > 0 ) {
					$final_display_price = '<span class="normalprice discounted">' . CommerceProduct::getNotatedPrice( $productHash['products_price'], $productHash['products_tax_class_id'] ) . ' </span>';
					$show_sale_price = '&nbsp;' . '<span class="productSpecialPrice">' . CommerceProduct::getNotatedPrice( $productHash['lowest_purchase_price'], $productHash['products_tax_class_id'] ) . '</span>';
					if( SHOW_SALE_DISCOUNT_STATUS == '1' ) {
						if (SHOW_SALE_DISCOUNT == 1) {
							$show_sale_discount = '<div class="productPriceDiscount">' . tra( 'Save:&nbsp;' ) . number_format(100 - (($productHash['lowest_purchase_price'] / $productHash['products_price']) * 100),SHOW_SALE_DISCOUNT_DECIMALS) . tra( '% off' ) . '</div>';
						} else {
							$show_sale_discount = '<div class="productPriceDiscount">' . tra( 'Save:&nbsp;' ) . CommerceProduct::getNotatedPrice( $discountAmount, $productHash['products_tax_class_id'] ) . PRODUCT_PRICE_DISCOUNT_AMOUNT . '</div>';
						}
					}
					$final_display_price .= $show_sale_price . $show_sale_discount;
				} else {
					$final_display_price = '<span class="normalprice">' . CommerceProduct::getNotatedPrice( $productHash['lowest_purchase_price'], $productHash['products_tax_class_id'] ) . ' </span>';
				}


				// If Call for Price, Show it
				$call_tag = '';
				if ($productHash['product_is_call']) {
					if (PRODUCTS_PRICE_IS_CALL_IMAGE_ON=='0') {
						$call_tag = '<br />' . PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT;
					} else {
						$call_tag = '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_CALL_FOR_PRICE, PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT);
					}
				}
				$ret = $final_display_price  . $call_tag;
			}
		}

		return $ret;
	}


	////
	// attributes final price including any bulk discount, but not include one-time charges
	function getAttributesPriceFinalRecurring( $pOptionsValuesId, $pQuantity = 1 ) {
		$attributes_price_final = 0;
		if( $optionValue = $this->getOptionValue( NULL, $pOptionsValuesId ) ) {
			if( isset( $optionValue['override_price'] ) ) {
				$attributes_price_final = $optionValue['override_price'];
			} else {

				$attributes_price_final = 0;
				// normal attributes price
				if ($optionValue["price_prefix"] == '-') {
					$attributes_price_final -= $optionValue["options_values_price"];
				} else {
					$attributes_price_final += $optionValue["options_values_price"];
				}
				// price factor
				$display_normal_price = $this->getBasePrice();
				$display_special_price = $this->getSpecialPrice();

				$attributes_price_final += zen_get_attributes_price_factor($display_normal_price, $display_special_price, $optionValue["attributes_price_factor"], $optionValue["attributes_pf_offset"]);

			}

			// qty discounts
			$attributes_price_final += zen_get_attributes_qty_prices_onetime($optionValue["attributes_qty_prices"], $pQuantity);

			// per word and letter charges
			if( !empty( $optionValue['products_options_type'] ) && $optionValue['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT ) {
				// calc per word or per letter
			}

			// discount attribute
			if( !empty( $optionValue["attributes_discounted"] ) ) {
				$attributes_price_final = $this->getPriceReduction( $optionValue['products_attributes_id'], $attributes_price_final, $pQuantity );
			}
		}

		return $attributes_price_final;
	}


	////
	// attributes final price onetime
	function getAttributesPriceFinalOnetime( $pOptionsValuesId, $pQuantity= 1 ) {
		global $gBitDb;

		$attributes_price_final_onetime = 0;

		if( $optionValue = $this->getOptionValue( NULL, $pOptionsValuesId ) ) {

			// one time charges
			// onetime charge
			$attributes_price_final_onetime = $optionValue["attributes_price_onetime"];

			// price factor
			$display_normal_price = $this->getBasePrice();
			$display_special_price = $this->getSpecialPrice();

			// price factor one time
			$attributes_price_final_onetime += zen_get_attributes_price_factor($display_normal_price, $display_special_price, $optionValue["attributes_pf_onetime"], $optionValue["attributes_pf_onetime_offset"]);

			// onetime charge qty price
			$attributes_price_final_onetime += zen_get_attributes_qty_prices_onetime($optionValue["attributes_qty_prices_onetime"], 1);
		}

		return $attributes_price_final_onetime;
	}

////
// compute product discount to be applied to attributes or other values
	function getPriceReduction( $attributes_id = false, $attributes_amount = false, $check_qty= false ) {
		global $discount_type_id, $sale_maker_discount;

		// no charge
		if ($attributes_id > 0 and $attributes_amount == 0) {
			return 0;
		}

		$discount_type_id = $this->getSaleDiscountType();

		if( !empty( $this->mInfo['normal_price'] ) ) {
			$special_price_discount = (!empty( $this->mInfo['special_price'] ) ? ($this->mInfo['special_price'] / $this->mInfo['normal_price']) : 1);
		} else {
			$special_price_discount = '';
		}
		$sale_maker_discount = $this->getSaleDiscountType( '', 'amount' );

		// percentage adjustment of discount
		if (($discount_type_id == 120 or $discount_type_id == 1209) or ($discount_type_id == 110 or $discount_type_id == 1109)) {
			$sale_maker_discount = ($sale_maker_discount != 0 ? (100 - $sale_maker_discount)/100 : 1);
		}

		$qty = $check_qty;

		// ==== percentage discount apply to price
		if( $this->getDiscount( $qty, 'discount_price' ) && empty( $attributes_id ) ) {
			// discount quanties exist and this is not an attribute
			$check_discount_qty_price = $this->getQuantityPrice( $qty, $attributes_amount );
			return $check_discount_qty_price;

		} elseif( $this->getDiscount( $qty, 'discount_price' ) && $this->getField( 'products_priced_by_attribute' ) ) {
			// discount quanties exist and this is not an attribute
			$check_discount_qty_price = $this->getQuantityPrice( $qty, $attributes_amount );
			return $check_discount_qty_price;

		} elseif( $discount_type_id == 5 ) {
			// No Sale and No Special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					if ($special_price_discount != 0) {
						$calc = ($attributes_amount * $special_price_discount);
					} else {
						$calc = $attributes_amount;
					}

					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}
		} elseif( $discount_type_id == 59 ) {
			// No Sale and Special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}

		// ==== percentage discounts apply to Sale
		} elseif( $discount_type_id == 120 ) {
			// percentage discount Sale and Special without a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $sale_maker_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}
		} elseif( $discount_type_id == 1209 ) {
			// percentage discount on Sale and Special with a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$calc2 = $calc - ($calc * $sale_maker_discount);
					$sale_maker_discount = $calc - $calc2;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}

		// ==== percentage discounts skip specials
		} elseif( $discount_type_id == 110 ) {
			// percentage discount Sale and Special without a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $sale_maker_discount);
					$sale_maker_discount = $calc;
				} else {
					if ($attributes_amount != 0) {
						$calc = $attributes_amount - ($attributes_amount * $sale_maker_discount);
						$sale_maker_discount = $calc;
					} else {
						$sale_maker_discount = $sale_maker_discount;
					}
				}
			}
		} elseif( $discount_type_id == 1109 ) {
			// percentage discount on Sale and Special with a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}

		// ==== flat amount discounts
		} elseif ( $discount_type_id == 20 ) {
			// flat amount discount Sale and Special without a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount - $sale_maker_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}
		} elseif( $discount_type_id == 209 ) {
			// flat amount discount on Sale and Special with a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$calc2 = ($calc - $sale_maker_discount);
					$sale_maker_discount = $calc2;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}

		// ==== flat amount discounts Skip Special
		} elseif( $discount_type_id == 10 ) {
			// flat amount discount Sale and Special without a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount - $sale_maker_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}
		} elseif( $discount_type_id == 109 ) {
			// flat amount discount on Sale and Special with a special
			if (!$attributes_id) {
				$sale_maker_discount = 1;
			} else {
				// compute attribute amount based on Special
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}

		// ==== New Price amount discounts
		} elseif( $discount_type_id == 220) {
			// New Price amount discount Sale and Special without a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}
		} elseif( $discount_type_id == 2209 ) {
			// New Price amount discount on Sale and Special with a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}

		// ==== New Price amount discounts - Skip Special
		} elseif( $discount_type_id == 210 ) {
			// New Price amount discount Sale and Special without a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}
		} elseif( $discount_type_id == 2109 ) {
			// New Price amount discount on Sale and Special with a special
			if (!$attributes_id) {
				$sale_maker_discount = $sale_maker_discount;
			} else {
				// compute attribute amount
				if ($attributes_amount != 0) {
					$calc = ($attributes_amount * $special_price_discount);
					$sale_maker_discount = $calc;
				} else {
					$sale_maker_discount = $sale_maker_discount;
				}
			}

		} elseif( $discount_type_id == 0 or $discount_type_id == 9 ) {
			// flat discount
			return $sale_maker_discount;

		} else {
			$sale_maker_discount = 7000;
		}

		return $sale_maker_discount;
	}


	// =================== Product Pricing Methods ==================== }}}



	public static function getTitleFromHash( &$pHash, $pDefault=TRUE ) {
		$ret = NULL;
		if( !empty( $pHash ) ) {
			if( !empty( $pHash['products_name'] ) ) {
				$ret = $pHash['products_name'];
			} elseif( !empty( $pHash['title'] ) ) {
				$ret = $pHash['title'];
			}
		} elseif( $this->isValid() ) {
			$ret = $this->mInfo['products_name'];
		}
		return $ret;
	}

	/**
	 * Attempt to create a brief description of this object, most useful for <meta name="description" />
	 *
	 * @return array list of aliases
	 */
	function generateDescription() {
		$ret = NULL;
		if( $this->isValid() ) {
			if( $this->getField( 'metatags_description' ) ) {
				$ret = $this->getField( 'metatags_description' );
			} elseif( $this->getField( 'products_description' ) ) {
				$ret = $this->getField( 'products_description' );
			} else {
				$ret = parent::generateDescription();
			}
			if( $ret ) {
				$ret = $this->getTypeName().': '.$ret;
			}
		}
		return $ret;
	}

	/**
	 * Attempt to create a collection of relevant words about this object, most useful for <meta name="keywords" />
	 *
	 * @return array list of aliases
	 */
	function generateKeywords() {
		$ret = array();
		if( $this->isValid() ) {
			if( $this->getField( 'metatags_keywords' ) ) {
				$ret = $this->getField( 'metatags_keywords' );
			}
			foreach( array( 'products_manufacturers_model', 'categories_name', 'manufacturers_name', 'real_name', 'products_model', 'type_name' ) as $key ) {
				if( $this->getField( $key ) ) {
					$ret[] = $this->getField( $key );
				}
			}
			$ret = array_merge( $ret, parent::generateKeywords() );
		}
		return $ret;
	}

	function getTypeName() {
		if( $this->isValid() ) {
			return( $this->mInfo['type_name'] );
		}
	}

	public static function getDisplayUrlFromId( $pProductsId ) {
		global $gBitSystem;
		$ret = BITCOMMERCE_PKG_URL;
		if( BitBase::verifyId( $pProductsId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret .= $pProductsId;
			} else {
				$ret .= 'index.php?products_id='.$pProductsId;
			}
		}
		return $ret;
	}

	public static function getDisplayUrlFromHash( &$pParamHash ) {
		global $gBitSystem;
		$ret = BITCOMMERCE_PKG_URL;
		if( BitBase::verifyIdParameter( $pParamHash, 'products_id' ) ) {
			$ret = static::getDisplayUrlFromId( $pParamHash['products_id'] );
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) && empty( $pParamHash['short_form'] ) ) {
				if( $pParamHash['products_name'] ) {
					$ret .= '-'.preg_replace( '/[[:^alnum:]]+/', '-', $pParamHash['products_name'] );
				}
				if( $pParamHash['type_name']  && strpos( $ret, $pParamHash['type_name'] ) === FALSE ) {
					$ret .= '-'.preg_replace( '/[[:^alnum:]]+/', '-', $pParamHash['type_name'] );
				}
			}
			if( !empty( $pParamHash['cat_path'] ) ) {
				if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
					$ret .= '/' . $pParamHash['cat_path'];
				} else {
					$ret .= '&cPath=' . $pParamHash['cat_path'];
				}
			}
		}
		return $ret;
	}

	public function getThumbnailFile( $pSize='small' ) {
		if( $this->isValid() ) {
			return static::getThumbnailFileFromHash( $this->mInfo, $pSize );
		}	
	}

	public static function getThumbnailFileFromHash( &$pMixed, $pSize='small' ) {
		$ret = BIT_ROOT_PATH.static::getImageUrlFromHash( $pMixed, $pSize );
		if( !file_exists( dirname( $ret ) ) ) {
			mkdir_p( dirname( $ret ) );
		}
		return $ret;
	}

	public function getThumbnailUrl( $pSize='small', $pInfoHash=NULL, $pSecondary=NULL, $pDefault=TRUE ) {
		if( $this->isValid() ) {
			return( static::getImageUrlFromHash( $this->mProductsId, $pSize ) );
		}
	}

	function getImageUri( $pSize='small' ) {
		if( $this->isValid() ) {
			return BIT_ROOT_URI.static::getImageUrlFromHash( $this->mProductsId, $pSize );
		}
	}

	function getImageUrl( $pSize='small' ) {
		if( !empty( $this ) ) {
			return static::getImageUrlFromHash( $this->mProductsId, $pSize );
		}
	}

	public function getIconUrl( $pSize='sm' ) {
		return BITCOMMERCE_PKG_URL.'images/icons/product-general-'.$pSize.'.png';
	}

	public static function getImageUrlFromHash( $pMixed=NULL, $pSize='small' ) {
		$ret = NULL;

		if( is_array( $pMixed ) && !empty( $pMixed['products_id'] ) ) {
			$productsId = $pMixed['products_id'];
		} elseif( is_numeric( $pMixed ) ) {
			$productsId = $pMixed;
		}

		if( !empty( $productsId ) ) {
			$branch = static::getImageBranchFromId( $productsId );
			$basePath = static::getImageBasePathFromId( $productsId );
			if( is_dir( $basePath.'thumbs/' ) ) {
				$basePath .= 'thumbs/';
				$branch .= 'thumbs/';
			}
			if( file_exists( $basePath.$pSize.'.jpg' ) ) {
				$ret = STORAGE_PKG_URL.$branch.$pSize.'.jpg';
			} elseif( file_exists( $basePath.$pSize.'.png' ) ) {
				$ret = STORAGE_PKG_URL.$branch.$pSize.'.png';
			} else {
//				$ret = BITCOMMERCE_PKG_URL.'images/blank_'.$pSize.'.jpg';
			}
		} else {
			$ret = STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/images/'.$pMixed;
		}
		return $ret;
	}

	protected static function getImageBasePathFromId( $pProductsId ) {
		return STORAGE_PKG_PATH.static::getImageBranchFromId( $pProductsId );
	}

	protected static function getImageBranchFromId( $pProductsId ) {
		return BITCOMMERCE_PKG_NAME.'/'.($pProductsId % 1000).'/'.$pProductsId.'/';
	}

	function getGatekeeperSql( &$pSelectSql, &$pJoinSql, &$pWhereSql ) {
		global $gBitSystem, $gBitUser;
		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
				$pSelectSql .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
				$pJoinSql	 .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security_map` cg ON (p.`content_id`=cg.`content_id`)
								LEFT OUTER JOIN `".BIT_DB_PREFIX."gatekeeper_security` ts ON (ts.`security_id`=cg.`security_id` ) ";
			if( !$this->isOwner() && !$gBitUser->isAdmin() ) {
				// this is an ineleganct solution to mash $gBitUser->mUserId in there, but other things were painful.
				$pWhereSql .= ' AND (cg.`security_id` IS NULL OR ts.`user_id`= \''.$gBitUser->mUserId.'\' )';
			}
		}
	}

	public static function prepGetList(&$pListHash){
		// keep a copy of user_id for later...
		$userId = parent::getParameter( $pListHash, 'user_id' );
		parent::prepGetList($pListHash);
		if(empty($pListHash['query_string'])){
			$pListHash['query_string'] = '';
		}
		$dynamicParams = array ('page', 'max_records','sort_mode');
		foreach ($_GET as $key=>$value){
			if(!in_array($key,$dynamicParams)){
				$pListHash['query_string'].= "&$key=$value";
			}
		}
		if( !empty( $userId ) ) {
			// LibertyContent clobbers user_id for security reasons base on list_content. For Commerce, we want to loosen this up.
			$pListHash['user_id'] = $userId;
		}
	}
	function getList( &$pListHash ) {
		global $gBitSystem, $gBitUser;

		if( empty( $pListHash['sort_mode'] ) ) {
			$pListHash['sort_mode'] = 'created_desc';
		}

		$this->prepGetList( $pListHash );

		$bindVars = array();
		$selectSql = '';
		$joinSql = '';
		$whereSql = '';

		if( @BitBase::verifyId( $pListHash['content_status_id'] ) ) {
			$bindVars[] = $pListHash['content_status_id'];
			$whereSql = ' lc.`content_status_id` = ? ';
		} elseif( $gBitUser->hasPermission( 'p_bitcommerce_admin' ) ) {
			$whereSql = ' lc.`content_status_id` >= ? ';
			$bindVars[] = -999;
		} else {
			$whereSql = ' lc.`content_status_id` >= ? ';
			$bindVars[] = -99;
		}

// 		$selectSql .= ' , s.* ';
		if( !empty( $pListHash['specials'] ) ) {
			$joinSql .= " INNER JOIN " . TABLE_SPECIALS . " s ON ( p.`products_id` = s.`products_id` ) ";
			$whereSql .= " AND s.`status` = '1' ";
// 		} else {
// 			$joinSql .= " LEFT JOIN " . TABLE_SPECIALS . " s ON ( p.`products_id` = s.`products_id` AND s.status = '1' ) ";
		}

		if( empty( $pListHash['thumbnail_size'] ) ) {
			$pListHash['thumbnail_size'] = 'small';
		}

		if( !empty( $pListHash['featured'] ) ) {
			$selectSql .= ' , f.`featured_date_available`, f.`expires_date`, f.`featured_last_modified`, f.`featured_date_added` ';
			$joinSql .= " INNER JOIN " . TABLE_FEATURED . " f ON ( p.`products_id` = f.`products_id` ) ";
			$whereSql .= " AND f.`status` = '1' ";
		}

		if( !empty( $pListHash['best_sellers'] ) ) {
			$whereSql .= " AND p.`products_ordered` > 0 ";
		}

		if( !empty( $pListHash['commissioned'] ) ) {
			$whereSql .= " AND (p.`products_commission` IS NOT NULL AND p.`products_commission` > 0) ";
		}

		if( !empty( $pListHash['user_id'] ) ) {
			$whereSql .= " AND lc.`user_id` = ? ";
			array_push( $bindVars, $pListHash['user_id'] );
		}

		if( !empty( $pListHash['upper_price_limit'] ) ){
			$whereSql .= " AND p.`products_price` <= ? ";
			array_push( $bindVars, $pListHash['upper_price_limit']);
		}
		if( !empty( $pListHash['lower_price_limit'] ) ){
			$whereSql .= " AND p.`products_price` >= ? ";
			array_push( $bindVars, $pListHash['lower_price_limit'] );
		}
		if($gBitSystem->isPackageActive( 'tags' )){
			if( !empty( $pListHash['tag'] ) ){
				$joinSql .= " INNER JOIN tags_content_map tcm ON ( lc.`content_id` = tcm.`content_id`) ";
				$whereSql .= " AND tcm.`tag_id` = ? ";
				array_push( $bindVars, $pListHash['tag'] );
			}
		}

		if( !empty( $pListHash['content_id_list'] ) ) { // you can use an array of titles
			$whereSql .= " AND p.`content_id` IN ( ".implode( ',',array_fill( 0,count( $pListHash['content_id_list'] ),'?' ) ).") ";
			$bindVars = array_merge( $bindVars, $pListHash['content_id_list'] );
		}

		if( !empty( $pListHash['freshness'] ) ) {
			if ( $pListHash['freshness'] == '1' ) {
				$whereSql .= " and ".$this->mDb->SQLDate( 'Ym', 'p.`products_date_added`' )." >= ".$this->mDb->SQLDate( 'Ym' );
			} else {
				$whereSql .= ' and '.$this->mDb->OffsetDate( SHOW_NEW_PRODUCTS_LIMIT, 'p.`products_date_added`' ).' > '. $this->mDb->NOW();
			}
		}

		if( isset( $pListHash['search'] ) ) {
			$whereSql .= " AND (LOWER( pd.`products_name` ) LIKE ? OR LOWER( pd.`products_description` ) LIKE ? OR LOWER( p.`products_model` ) LIKE ?) ";
			$searchTerm = strtolower( $pListHash['search'] );
			$bindVars[] = '%'.$searchTerm.'%';
			$bindVars[] = '%'.$searchTerm.'%';
			$bindVars[] = '%'.$searchTerm.'%';
		}

		if( !empty( $pListHash['reviews'] ) ) {
			$selectSql .= ' , r.`reviews_rating`, rd.`reviews_text` ';
			$joinSql .= " INNER JOIN " . TABLE_REVIEWS . " r	ON ( p.`products_id` = r.`products_id` ) INNER JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON ( r.`reviews_id` = rd.`reviews_id` ) ";
			$whereSql .= " AND r.`status` = '1' AND rd.`languages_id` = ? ";
			array_push( $bindVars, (int)$_SESSION['languages_id'] );
		}

		if ( !empty( $pListHash['category_id'] ) ) {
			if( !is_numeric( $pListHash['category_id'] ) && strpos( $pListHash['category_id'], '_' ) ) {
				$path = explode( '_', $pListHash['category_id'] );
				end( $path );
				$pListHash['category_id'] = current( $path );
			}
			if( $this->verifyId( $pListHash['category_id'] ) ) {
				$joinSql .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON ( p.`products_id` = p2c.`products_id` ) LEFT JOIN " . TABLE_CATEGORIES . " c ON ( p2c.`categories_id` = c.`categories_id` )";
				$whereSql .= " AND c.`categories_id`=? ";
				array_push( $bindVars, (int)$pListHash['category_id'] );
			}
		}

		if( empty( $pListHash['all_status'] ) ) {
			$whereSql .= " AND p.`products_status` = '1' ";
		}

		// This needs to go first since it puts a bindvar in the joinSql
		array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );
		$whereSql .= ' AND pd.`language_id`=?';

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, NULL, $pListHash );

//		$whereSql = preg_replace( '/^\sAND/', ' ', $whereSql );
		$pListHash['total_records'] = 0;

		$query = "SELECT p.`products_id` AS `hash_key`, pd.*, p.*, pd.`products_name`, lc.`created`, lc.`content_status_id`, uu.`user_id`, uu.`real_name`, uu.`login`, pt.* $selectSql
					FROM " . TABLE_PRODUCTS . " p
				 	INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(p.`content_id`=lc.`content_id` )
				 	INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id` )
					INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON(p.`products_id`=pd.`products_id` )
						INNER JOIN `" . BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=lc.`user_id`)
					$joinSql
					WHERE $whereSql ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] );
		if( $rs = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
			// if we returned fewer than the max, use size of our result set
			if( ($rs->RecordCount() < $pListHash['max_records'] || $rs->RecordCount() == 1) && empty($pListHash['offset'])) {
				$pListHash['total_records'] = $rs->RecordCount();
			} else {
				$countQuery = "select COUNT( p.`products_id` )
							from " . TABLE_PRODUCTS . " p
							INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(p.`content_id`=lc.`content_id` )
							INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id` )
							INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON(p.`products_id`=pd.`products_id` )
							$joinSql
							WHERE $whereSql ";
				$pListHash['total_records'] = $this->mDb->getOne( $countQuery, $bindVars );
			}

			$ret = $rs->GetAssoc();
			global $currencies;
			foreach( array_keys( $ret ) as $productId ) {
				$ret[$productId]['info_page'] = $ret[$productId]['type_handler'].'_info';
				if( !empty( $ret[$productId]['type_class_file'] ) && file_exists( BIT_ROOT_PATH.$ret[$productId]['type_class_file'] ) ) {
					require_once( BIT_ROOT_PATH.$ret[$productId]['type_class_file'] );
				}
				if( empty( $ret[$productId]['type_class'] ) ) {
					$ret[$productId]['type_class'] = 'CommerceProduct';
				}
				$ret[$productId]['display_url'] = $ret[$productId]['type_class']::getDisplayUrlFromHash( $ret[$productId] );
				$ret[$productId]['display_uri'] = $ret[$productId]['type_class']::getDisplayUriFromHash( $ret[$productId] );
				if( empty( $ret[$productId]['products_image'] ) ) {
					$ret[$productId]['products_image_url'] = $ret[$productId]['type_class']::getImageUrlFromHash( $ret[$productId], $pListHash['thumbnail_size'] );
				}

				if( empty( $taxRate[$ret[$productId]['products_tax_class_id']] ) ) {
					$taxRate[$ret[$productId]['products_tax_class_id']] = zen_get_tax_rate( $ret[$productId]['products_tax_class_id'] );
				}
				$ret[$productId]['products_weight_kg'] = $ret[$productId]['products_weight'] * .45359;

				$ret[$productId]['regular_price'] = $currencies->display_price( $ret[$productId]['products_price'], $taxRate[$ret[$productId]['products_tax_class_id']] );
				// zen_get_products_display_price is a query hog
				$ret[$productId]['display_price'] = $ret[$productId]['type_class']::getDisplayPriceFromHash( $ret[$productId] );
				$ret[$productId]['title'] = $ret[$productId]['products_name'];
			}
		}

		$pListHash['current_page'] = $this->verifyIdParameter( $pListHash, 'page' ) ? $pListHash['page'] : 1;
		$pListHash['total_pages'] = ceil( $pListHash['total_records'] / $pListHash['max_records'] );
		$pListHash['page_records'] = count( $ret );
//		$pListHash['max_records'] = (count( $ret ) ? count( $ret ) : $pListHash['max_records']);
		$pListHash['offset'] = $pListHash['offset'];
		$pListHash['block_pages'] = 5;
		$pListHash['start_block'] = floor( $pListHash['offset'] / $pListHash['max_records'] ) * $pListHash['max_records'] + 1;

		return( $ret );
	}

	function getInfoPage() {
		$ret = NULL;
		if( !empty( $this->mInfo['info_page'] ) ) {
				$ret = $this->mInfo['info_page'];
		}
		return $ret;
	}

	function isValid() {
		return( !empty( $this->mProductsId ) );
	}

	function isViewable($pContentId = NULL) {
		return( $this->hasUpdatePermission() || $this->isAvailable() );
	}

	function isAvailable() {
		global $gBitUser;
		if( $this->isValid() && !$this->isDeleted() ) {
 			if( !empty( $this->mInfo['products_status'] ) ) {
				$ret = TRUE;
			} else {
				$ret = $this->isOwner() || $this->hasUpdatePermission();
			}
 		} else {
			$ret = FALSE;
		}
		return( $ret );
	}

	function isOwner( $pParamHash = NULL ) {
		global $gBitUser;
		$ret = FALSE;
		if( $this->getField( 'user_id' ) ) {
			$ret = $gBitUser->mUserId == $this->mInfo['user_id'];
		}
		return( $ret );
	}

	function isPurchased( $pUserId=NULL ) {
		$ret = FALSE;
		$joinSql = '';
		$whereSql = '';
		if( $this->isValid() ) {
			$bindVars = array( $this->mProductsId );
			if( is_numeric( $pUserId ) ) {
				$joinSql .= ' INNER JOIN '.TABLE_ORDERS.' co ON(co.`orders_id`=cop.`orders_id`)';
				$whereSql .= ' AND `customers_id`=? ';
				$bindVars[] = $pUserId;
			}
			$ret = $this->mDb->GetOne( "SELECT COUNT(*) FROM " . TABLE_ORDERS_PRODUCTS . " cop $joinSql WHERE `products_id`=? $whereSql", $bindVars );
		}
		return $ret;
	}

	public function isExpeditable( &$pOrder, $pProductKey ) {
		// Default implementation assumes all products are capable of expediting
		return true;
	}

	function verify( &$pParamHash ) {
		$pParamHash['product_store'] = array(
			'products_quantity' => (!empty( $pParamHash['products_quantity'] ) && is_numeric( $pParamHash['products_quantity'] ) ? $pParamHash['products_quantity'] : 0),
			'products_type' => (!empty( $pParamHash['products_type'] ) ? $pParamHash['products_type'] : $this->getProductType()),
			'products_status' => (isset( $pParamHash['products_status'] ) ? (int)!empty( $pParamHash['products_status'] ) : 0),
			'products_qty_box_status' => (int)(!empty( $pParamHash['products_qty_box_status'] )),
			'products_quantity_order_units' => (!empty( $pParamHash['products_quantity_order_units'] ) && is_numeric( $pParamHash['products_quantity_order_units'] ) ? $pParamHash['products_quantity_order_units'] : 1),
			'products_quantity_order_min' => (!empty( $pParamHash['products_quantity_order_min'] ) && is_numeric( $pParamHash['products_quantity_order_min'] ) ? $pParamHash['products_quantity_order_min'] : 1),
			'products_quantity_order_max' => (!empty( $pParamHash['products_quantity_order_max'] ) && is_numeric( $pParamHash['products_quantity_order_max'] ) ? $pParamHash['products_quantity_order_max'] : 0),
			'products_weight' => (!empty( $pParamHash['products_weight'] ) ? $pParamHash['products_weight'] : $this->getWeight()),
			'products_commission' => (!empty( $pParamHash['products_commission'] ) && (double)$pParamHash['products_commission'] > 0 && (double)$pParamHash['products_commission'] < '9999999999999' ? $pParamHash['products_commission'] : NULL),
		);

		// hashed by php type so values can be safely cast when sent into the DB. This is particularly important for real databases
		$checkFields = array(
			// VARCHAR string columns
			'string' => array(
				'products_manufacturers_model',
			), 'id' => array(
				// id's used as foreign keys
				'products_tax_class_id',
				'manufacturers_id',
				'suppliers_id',
				'related_content_id',
				'purchase_group_id',
			), 'int' => array(
				// Integers
				'products_status',
				'products_virtual',
				'products_barcode',
				'products_priced_by_attribute',
				'product_is_free',
				'product_is_call',
				'products_quantity_mixed',
				'product_is_always_free_ship',
				'products_sort_order',
				'products_mixed_discount_qty',
				'products_discount_type',
				'products_discount_type_from',
			), 'double' => array(
				// floating point
				'products_price',
				'products_cogs',
				'products_weight',
				'lowest_purchase_price',
			),
		);

		foreach( $checkFields as $type => $keys ) {
			foreach( $keys as $key ) {
				// We have not previously set this product_store key, as in a derived class, so lets go with default processing...
				if( !isset( $pParamHash['product_store'][$key] ) ) {
					$val = 	(isset( $pParamHash[$key] ) ? $pParamHash[$key] : $this->getField( $key ));
					switch( $type ) {
						case 'id':
							// id's should be non-zero or NULL because of id's
							if( empty( $val ) ) {
								$val = NULL;
							}
							break;
						case 'double':
							$val = (double)$val;
							if( empty( $val ) && $val !== 0 ) {
								// we have an empty string, set to NULL cuz some databases are picky about empty strings in numeric fields
								$val = NULL;
							}
							break;
						default:
							settype( $val, $type );
							break;
					}
					$pParamHash['product_store'][$key] = $val;
				}
			}
		}
		if( ($model = $this->getProductsModel()) != 'Product' ) {
			$pParamHash['product_store']['products_model'] = $model;
		}

		$pParamHash['products_description'][1] = !empty( $pParamHash['products_description'][1] ) ? $pParamHash['products_description'][1] : $this->getField( 'products_description' );

		if( !empty( $pParamHash['reorders_interval_number'] ) && is_numeric( $pParamHash['reorders_interval_number'] ) && !empty( $pParamHash['reorders_pending'] ) ) {
			$pParamHash['product_store']['reorders_interval'] = $pParamHash['reorders_interval_number'].' '.$pParamHash['reorders_interval'];
			$pParamHash['product_store']['reorders_pending'] = $pParamHash['reorders_pending'];
		} else {
			$pParamHash['product_store']['reorders_interval'] = NULL;
			$pParamHash['product_store']['reorders_pending'] = NULL;
		}


		$pParamHash['content_type_guid'] = BITPRODUCT_CONTENT_TYPE_GUID;

		// 'title' trumphs all
		if( !empty( $pParamHash['title'] ) ) {
			$pParamHash['products_name'][1] = substr( preg_replace( '/:space:+/m', ' ', trim( filter_var( $pParamHash['title'], FILTER_SANITIZE_STRING ) ) ), 0, BIT_CONTENT_MAX_TITLE_LEN );
		}

		if( !empty( $pParamHash['products_name'] ) ) {
			if( is_array( $pParamHash['products_name'] ) ) {
				$pParamHash['title'] = current( $pParamHash['products_name'] );
			} elseif( is_string( $pParamHash['products_name'] ) ) {
				$pParamHash['title'] = $pParamHash['products_name'];
			}
		}

		if( empty( $pParamHash['lowest_purchase_price'] ) ) {
			if( $lowestPrice = $this->getLowestPrice() ) {
				$pParamHash['product_store']['lowest_purchase_price'] = $lowestPrice;
			} else {
				// we have an empty string, set to NULL cuz some databases are picky about empty strings in numeric fields
				$pParamHash['product_store']['lowest_purchase_price'] = (double)$pParamHash['products_price'];
			}
		}

		if( empty( $pParamHash['content_id'] ) ) {
			$pParamHash['content_id'] = $this->mContentId;
		}

		if( empty( $pParamHash['content_id'] ) ) {
			$pParamHash['content_id'] = $this->mContentId;
		}

		if( !empty( $pParamHash['products_date_available'] ) ) {
			$pParamHash['product_store']['products_date_available'] = (date('Y-m-d') < $pParamHash['products_date_available']) ? $pParamHash['products_date_available'] : $this->mDb->NOW();
		} else {
			$pParamHash['product_store']['products_date_available'] = NULL;
		}

		$pParamHash['product_store']['products_last_modified'] = (empty( $pParamHash['products_last_modified'] ) ? $this->mDb->NOW() : $pParamHash['products_last_modified']);
		$pParamHash['product_store']['master_categories_id'] = (!empty( $pParamHash['master_categories_id'] ) ? $pParamHash['master_categories_id'] : (!empty( $pParamHash['category_id'] ) ? $pParamHash['category_id'] : NULL));

		if( !$this->isValid() ) {
			$pParamHash['product_store']['products_date_added'] = (empty( $pParamHash['products_date_added'] ) ? $this->mDb->NOW() : $pParamHash['products_date_added']);
		}

		return( TRUE );
	}

	function store( &$pParamHash ) {
		// we have already done all the permission checking needed for this user to upload an image
		$pParamHash['no_perm_check'] = TRUE;
		$this->StartTrans();
		if( CommerceProduct::verify( $pParamHash ) && parent::store( $pParamHash ) ) {
			if (isset($pParamHash['pID'])) {
				$this->mProductsId = zen_db_prepare_input($pParamHash['pID']);
			}

			if( $this->isValid() ) {
				$action = 'update_product';
				$this->mDb->associateUpdate( TABLE_PRODUCTS, $pParamHash['product_store'], array( 'products_id' =>$this->mProductsId ) );
			} else {
				$pParamHash['product_store']['content_id'] = $pParamHash['content_id'];
				$action = 'insert_product';
				$this->mDb->associateInsert( TABLE_PRODUCTS, $pParamHash['product_store'] );
				$this->mProductsId = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );
				$this->mDb->query( "insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " ( `products_id`, `categories_id` ) values (?,?)", array( $this->mProductsId, $pParamHash['product_store']['master_categories_id'] ) );
			}

			$languages = zen_get_languages();
			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$language_id = $languages[$i]['id'];

				$bindVars = array();
				if( !empty( $pParamHash['products_name'][$language_id] ) ) {
					$bindVars['products_name'] = substr( zen_db_prepare_input($pParamHash['products_name'][$language_id]), 0, 64 );
				}
				if( !empty( $pParamHash['products_description'][$language_id] ) ) {
					$bindVars['products_description'] = zen_db_prepare_input($pParamHash['products_description'][$language_id]);
				}
				if( !empty( $pParamHash['products_url'][$language_id] ) ) {
					$bindVars['products_url'] = substr( zen_db_prepare_input($pParamHash['products_url'][$language_id]), 0, 255 );
				}

				if ($action == 'insert_product') {
					$bindVars['products_id'] = $this->mProductsId;
					$bindVars['language_id'] = $language_id;
					$this->mDb->associateInsert( TABLE_PRODUCTS_DESCRIPTION, $bindVars );
				} elseif ($action == 'update_product') {
					if( !empty( $bindVars ) ) {
						$query = "UPDATE " . TABLE_PRODUCTS_DESCRIPTION . " SET `".implode( array_keys( $bindVars ), '`=?, `' ).'`=?' . " WHERE `products_id` =? AND `language_id`=?";
						$bindVars['products_id'] = $this->mProductsId;
						$bindVars['language_id'] = $language_id;
						$this->mDb->query( $query, $bindVars );
					}
				}
			}

			// add meta tags
			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$language_id = $languages[$i]['id'];

				$bindVars = array();
				if( !empty( $pParamHash['metatags_title'][$language_id] ) ) {
					$bindVars['metatags_title'] = zen_db_prepare_input($pParamHash['metatags_title'][$language_id]);
				}
				if( !empty( $pParamHash['metatags_keywords'][$language_id] ) ) {
					$bindVars['metatags_keywords'] = zen_db_prepare_input($pParamHash['metatags_keywords'][$language_id]);
				}
				if( !empty( $pParamHash['metatags_description'][$language_id] ) ) {
					$bindVars['metatags_description'] = zen_db_prepare_input($pParamHash['metatags_description'][$language_id]);
				}

				$this->mDb->query( "DELETE FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE `products_id`=?", array( $this->mProductsId ) );
				if( !empty( $bindVars ) ) {
					if( !empty( $bindVars ) ) {
						$bindVars['products_id'] = $this->mProductsId;
						$bindVars['language_id'] = $language_id;
						$this->mDb->associateInsert(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $bindVars);
					}
				}
				$this->storeProductImage( $pParamHash );
			}
		}

		$this->CompleteTrans();
		$this->load();
		return( count( $this->mErrors ) == 0 );
	}

	public static function storeProductImage( $pParamHash ) {
		if( !empty( $pParamHash['products_id'] ) ) {
			// did we recieve an arbitrary file, or uploaded file as the product image?
			if( !empty( $pParamHash['products_image'] ) && is_readable( $pParamHash['products_image'] ) ) {
				$fileHash['source_file']	= $pParamHash['products_image'];
				$fileHash['name']			= basename( $fileHash['source_file'] );
				$fileHash['source_name']	= $fileHash['source_file'];
			} elseif( !empty( $pParamHash['products_image_upload']['size'] ) ) {
				$fileHash['source_file']	= $pParamHash['products_image_upload']['tmp_name'];
				$fileHash['name']			= basename( $pParamHash['products_image_upload']['name'] );
				$fileHash['source_name']	= $pParamHash['products_image_upload']['name'];
			}

			if( !empty( $fileHash ) ) {
				global $gBitSystem;
				if( !empty( $pParamHash['dest_branch'] ) ) {
					$fileHash['dest_branch']	= $pParamHash['dest_branch'];
				} else {
					$fileHash['dest_branch']	= static::getImageBranchFromId( $pParamHash['products_id'] );
				}
				mkdir_p( STORAGE_PKG_PATH.$fileHash['dest_branch'] );
				$fileHash['dest_base_name']	= 'original';
				$fileHash['max_height']		= 1024;
				$fileHash['max_width']		= 1280;
				$fileHash['type'] = $gBitSystem->verifyMimeType( $fileHash['source_file'] );
				liberty_process_image( $fileHash, empty( $pParamHash['copy_file'] ) );
			}
		}
	}

	function storeStatus( $pContentStatusId ) {
		if( $this->isValid() ) {
			// keep com_products.products_status update to date because it is so pervasive in bitcommerce the code base - one day it will go away...
			$this->mDb->query( "UPDATE " . TABLE_PRODUCTS . " SET `products_status`=? WHERE `products_id`=?", array( (int)($pContentStatusId > 0), $this->mProductsId ) );
			parent::storeStatus( $pContentStatusId );
		}
	}

	function getProductType() {
		global $gCommerceSystem;
		return $gCommerceSystem->getConfig( 'commerce_default_product_type', 1 );
	}

	function update( $pUpdateHash, $pProductsId=NULL ) {
		if( empty( $pProductsId ) && $this && $this->isValid() ) {
			$pProductsId = $this->mProductsId;
		}
		if( $pProductsId ) {
			$this->mDb->associateUpdate( TABLE_PRODUCTS, $pUpdateHash, array( 'products_id' => $pProductsId ) );
		}
	}

	function hasAttributes( $pProductsId=NULL, $not_readonly = 'true' ) {
		$ret = FALSE;
		if( empty( $pProductsId ) ) {
			$this->loadAttributes();
			$ret = !empty( $this->mOptions );
		} else {

			if( PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == 'true' ) {
				// don't include READONLY attributes to determin if attributes must be selected to add to cart
				$query = "select pa.`products_options_values_id`
							from	" . TABLE_PRODUCTS_OPTIONS_MAP . " pom
								INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON(pa.`products_options_values_id`=pom.`products_options_values_id`)
								LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON(pa.`products_options_id`=po.`products_options_id`)
							where pom.`products_id` = ? and po.`products_options_type` != '" . PRODUCTS_OPTIONS_TYPE_READONLY . "'";
			} else {
				// regardless of READONLY attributes no add to cart buttons
				$query = "SELECT pa.`products_attributes_id`
							FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
								INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON(pa.`products_options_values_id`=pom.`products_options_values_id`)
							WHERE pom.`products_id` = ?";
			}

			$ret = $this->mDb->getOne($query, array( $pProductsId) ) > 0;
		}

		return( $ret );
	}


	function getOptionValue( $pOptionId, $pValueId ) {
		$ret = array();
		$this->loadAttributes();
		if( !empty( $pOptionId ) ) {
			if( !empty( $this->mOptions[$pOptionId]['values'][$pValueId] ) ) {
				$ret = $this->mOptions[$pOptionId]['values'][$pValueId];
			}
		} elseif( is_array( $this->mOptions ) ) {
			foreach( array_keys( $this->mOptions ) as $optionId ) {
				if( !empty( $this->mOptions[$optionId]['values'][$pValueId] ) ) {
					$ret = $this->mOptions[$optionId]['values'][$pValueId];
					break;
				}
			}
		}
		return $ret;
	}

	function getProductOptions( $pSelectedId = NULL, $pCart = NULL, &$productSettings = NULL ) {
		global $currencies;
		require_once( BITCOMMERCE_PKG_PATH.'includes/functions/html_output.php' );
		$productOptions = array();

		if ( $this->loadAttributes() ) {
			$productSettings['zv_display_select_option'] = 0;
			$productSettings['show_attributes_qty_prices_description'] = 'false';
			$productSettings['show_onetime_charges_description'] = 'false';
			if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
				$options_order_by= ' ORDER BY popt.`products_options_sort_order`';
			} else {
				$options_order_by= ' ORDER BY popt.`products_options_name`';
			}

			$discount_type = $this->getSaleDiscountType();
			$discount_amount = $this->getPriceReduction();
			$number_of_uploads = 0;
			foreach ( array_keys( $this->mOptions ) as $optionsId ) {
				$products_options_array = array();
				$products_options_value_id = '';
				$products_options_details = '';
				$products_options_details_noname = '';
				$tmp_radio = '';
				$tmp_checkbox = '';
				$tmp_html = '';
				$selected_attribute = false;

				$tmp_attributes_image = '';
				$tmp_attributes_image_row = 0;
				$productSettings['show_attributes_qty_prices_icon'] = 'false';
				$productOptions[$optionsId]['option_values'] = array();
				if( !empty( $this->mOptions[$optionsId]['values'] ) ) {
					foreach ( array_keys( $this->mOptions[$optionsId]['values'] ) as $valId ) {
						$vals = &$this->mOptions[$optionsId]['values'][$valId];
						if( empty( $vals['attributes_html_attrib'] ) ) {
							$vals['attributes_html_attrib'] = '';
						}
						// reset
						$new_value_price= '';
						$price_onetime = '';
						array_push( $products_options_array, array('id' => $vals['products_options_values_id'], 'text' => $vals['products_options_values_name']) );
						if (((CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == '') or (STORE_STATUS == '1')) or (CUSTOMERS_APPROVAL_AUTHORIZATION >= 2 and $_SESSION['customers_authorization'] == '')) {
							$new_options_values_price = 0;
						} else {
							// collect price information if it exists
							$new_value_price = $this->getAttributesPriceFinalRecurring( $vals["products_options_values_id"] );

							$vals['value_price'] = $new_value_price;

							// reverse negative values for display
							if ($new_value_price < 0) {
								$new_value_price = -$new_value_price;
								$vals['price_prefix'] = '-';
							}

							$price_onetime = '';
							if( $vals['attributes_price_onetime'] != 0 || $vals['attributes_pf_onetime'] != 0) {
								$productSettings['show_onetime_charges_description'] = 'true';
								$price_onetime = ' '. $currencies->display_price( $this->getAttributesPriceFinalOnetime( $vals["products_options_values_id"] ), zen_get_tax_rate($this->mInfo['products_tax_class_id']));
							}

							if ( !empty( $vals['attributes_qty_prices'] ) || !empty( $vals['attributes_qty_prices_onetime'] ) ) {
								$productSettings['show_attributes_qty_prices_description'] = 'true';
								$productSettings['show_attributes_qty_prices_icon'] = 'true';
							}

							if ( !empty( $vals['options_values_price'] ) && (empty( $vals['product_attribute_is_free'] ) && !$this->isFree() ) ) {
								// show sale maker discount if a percentage
								$vals['display_price'] = $vals['price_prefix'] . $currencies->display_price($new_value_price, zen_get_tax_rate($this->mInfo['products_tax_class_id']));
							} elseif ( $vals['product_attribute_is_free'] == '1' && !$this->isFree() ) {
								// if product_is_free and product_attribute_is_free
								$vals['display_price'] =	TEXT_ATTRIBUTES_PRICE_WAS . $vals['price_prefix'] . $currencies->display_price($new_value_price, zen_get_tax_rate($this->mInfo['products_tax_class_id'])) . TEXT_ATTRIBUTE_IS_FREE;
							} else {
								// normal price
								if ($new_value_price == 0) {
									$vals['display_price'] = '';
								} else {
									$vals['display_price'] = $vals['price_prefix'] . $currencies->display_price($new_value_price, zen_get_tax_rate( $this->mInfo['products_tax_class_id'] ) );
								}
							}

							if( !empty( $vals['display_price']	) ) {
								$vals['display_price'] = $vals['display_price'].($price_onetime ? ' '.tra('Per Item').', '.$price_onetime.' '.tra( 'One time' ) : '');
							} elseif( $price_onetime ) {
								$vals['display_price'] = $price_onetime;
							}
						} // approve

						if( !empty( $vals['value_price'] ) ) {
							$products_options_array[sizeof($products_options_array)-1]['text'] .= ' ( '.$vals['display_price'].' )';
						}

				// collect weight information if it exists
						if ((SHOW_PRODUCT_INFO_WEIGHT_ATTRIBUTES=='1' && !empty( $vals['products_attributes_wt'] ) )) {
							$products_options_display_weight = ' (' . $vals['products_attributes_wt_pfix'] . round( $vals['products_attributes_wt'], 2 )	. 'lbs / '.round($vals['products_attributes_wt']*0.4536,2).'kg)';
							$products_options_array[sizeof($products_options_array)-1]['text'] .= $products_options_display_weight;
						} else {
							// reset
							$products_options_display_weight='';
						}

						// =-=-=-=-=-=-=-=-=-=-= FILE, TEXT, READONLY
						if ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE or $this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT or $this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_CHECKBOX or $this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_RADIO or count( $this->mOptions[$optionsId] ) == 1 or $this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_READONLY) {
							$products_options_value_id = $vals['products_options_values_id'];
							if ($this->mOptions[$optionsId]['products_options_type'] != PRODUCTS_OPTIONS_TYPE_TEXT and $this->mOptions[$optionsId]['products_options_type'] != PRODUCTS_OPTIONS_TYPE_FILE) {
								$products_options_details = $vals['products_options_values_name'];
							} else {
								// don't show option value name on TEXT or filename
								$products_options_details = '';
							}
							if ($this->mOptions[$optionsId]['products_options_images_style'] >= 3) {
								$products_options_details .= $vals['display_price'] . (!empty( $vals['products_attributes_wt'] ) ? '<br />' . $products_options_display_weight : '');
								$products_options_details_noname = $vals['display_price'] . (!empty( $vals['products_attributes_wt'] ) ? '<br />' . $products_options_display_weight : '');
							} else {
								$products_options_details .= $vals['display_price'] . (!empty( $vals['products_attributes_wt'] ) ? '&nbsp;' . $products_options_display_weight : '');
								$products_options_details_noname = $vals['display_price'] . (!empty( $vals['products_attributes_wt'] ) ? '&nbsp;' . $products_options_display_weight : '');
							}
						}
						// =-=-=-=-=-=-=-=-=-=-= radio buttons
						if ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_RADIO) {
							if( is_object( $pCart ) && $pCart->in_cart($this->mProductsId) && ($pCart->contents[$this->mProductsId]['attributes'][$this->mOptions[$optionsId]['products_options_id']] == $vals['products_options_values_id']) ) {
								$selected_attribute = $pCart->contents[$this->mProductsId]['attributes'][$this->mOptions[$optionsId]['products_options_id']];
							} else {
								$selected_attribute = ($vals['attributes_default']=='1' ? true : false);
								// if an error, set to customer setting
								if( !empty( $pSelectedId ) ) {
									$selected_attribute= false;
									reset($pSelectedId);
									while(list($key,$value) = each($pSelectedId)) {
										if (($key == $this->mOptions[$optionsId]['products_options_id'] and $value == $vals['products_options_values_id'])) {
											// zen_get_products_name($_POST['products_id']) .
											$selected_attribute = true;
											break;
										}
									}
								} else {
									$selected_attribute = $vals['attributes_default'] == '1';
								}
							}
							// ignore products_options_images_style as this should be fully controllable via CSS
							$tmp_radio .= zen_draw_radio_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']', $products_options_value_id, $selected_attribute, NULL, "<span class='title'>$vals[products_options_values_name]</span> <span class='details'>$products_options_details_noname</span>" . (!empty( $vals['attributes_image'] ) ? zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', '') : '') );
						}




						// =-=-=-=-=-=-=-=-=-=-= checkboxes

						if ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_CHECKBOX) {
							$string = $this->mOptions[$optionsId]['products_options_id'].'_chk'.$vals['products_options_values_id'];
							if( is_object( $pCart ) && $pCart->in_cart($this->mProductsId)) {
								if ($pCart->contents[$this->mProductsId]['attributes'][$string] == $vals['products_options_values_id']) {
									$selected_attribute = true;
								} else {
									$selected_attribute = false;
								}
							} else {
								// if an error, set to customer setting
								if( !empty( $pSelectedId ) ) {
									$selected_attribute= false;
									reset($pSelectedId);
									while(list($key,$value) = each($pSelectedId)) {
										if (is_array($value)) {
											while(list($kkey,$vvalue) = each($value)) {
												if (($key == $this->mOptions[$optionsId]['products_options_id'] and $vvalue == $vals['products_options_values_id'])) {
													$selected_attribute = true;
													break;
												}
											}
										} else {
											if (($key == $this->mOptions[$optionsId]['products_options_id'] and $value == $vals['products_options_values_id'])) {
												$selected_attribute = true;
												break;
											}
										}
									}
								} else {
									$selected_attribute = ($vals['attributes_default']=='1' ? true : false);
								}
							}

							switch ($this->mOptions[$optionsId]['products_options_images_style']) {
								case '1':
									$tmp_checkbox .= '<label>'.zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'], (!empty( $vals['attributes_image'] ) ? zen_image(DIR_WS_IMAGES . $vals['attributes_image']).' ' : ' ') . $products_options_details . '</label>' );
									break;
								case '2':
									$tmp_checkbox .= '<label>'.zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'],  $products_options_details .	(!empty( $vals['attributes_image'] ) ? '<br />' . zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', 'hspace="5" vspace="5"') : '') . '</label>' );
									break;
								case '3':
									$tmp_attributes_image_row++;

									if ($tmp_attributes_image_row > $this->mOptions[$optionsId]['products_options_images_per_row']) {
										$tmp_attributes_image .= '</tr><tr>';
										$tmp_attributes_image_row = 1;
									}

									if( !empty( $vals['attributes_image'] ) ) {
										$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'], zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . $products_options_details_noname ) . '</td>';
									} else {
										$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'], $vals['products_options_values_name'] . $products_options_details_noname ) . '</td>';
									}
									break;

								case '4':
									$tmp_attributes_image_row++;

									if ($tmp_attributes_image_row > $this->mOptions[$optionsId]['products_options_images_per_row']) {
										$tmp_attributes_image .= '</tr><tr>';
										$tmp_attributes_image_row = 1;
									}

									if( !empty( $vals['attributes_image'] ) ) {
										$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">'
																	. zen_image(DIR_WS_IMAGES . $vals['attributes_image'])
																	. (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '')
																	. (!empty( $products_options_details_noname )	? '<br />' . $products_options_details_noname : '')
																	. '<br />' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . '</td>';
									} else {
										$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . $vals['products_options_values_name'] . (!empty( $products_options_details_noname ) ? '<br />' . $products_options_details_noname : '') . '<br />' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib']) . '</td>';
									}
									break;

								case '5':
									$tmp_attributes_image_row++;

									if ($tmp_attributes_image_row > $this->mOptions[$optionsId]['products_options_images_per_row']) {
										$tmp_attributes_image .= '</tr><tr>';
										$tmp_attributes_image_row = 1;
									}

									if( !empty( $vals['attributes_image'] ) ) {
										$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'], zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') ) . (!empty( $products_options_details_noname ) ? '<br />' . $products_options_details_noname : '') . '</td>';
									} else {
										$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'], $vals['products_options_values_name'] . ($products_options_details_noname != '' ? $products_options_details_noname : '') ) . '</td>';
									}
									break;
								case '0':
								default:
									$tmp_checkbox .= zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'], $products_options_details );
									break;
							}
						}




						// =-=-=-=-=-=-=-=-=-=-= text
						if (($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT)) {
							if( is_object( $pCart ) ) {
								$tmp_value = $pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']];
								$tmp_html = '<input type="text" name ="id[' . TEXT_PREFIX . $this->mOptions[$optionsId]['products_options_id'] . ']" size="' . $this->mOptions[$optionsId]['products_options_size'] .'" maxlength="' . $this->mOptions[$optionsId]['products_options_length'] . '" value="' . htmlspecialchars($tmp_value) .'" />	';
								$tmp_html .= $products_options_details;
								$tmp_word_cnt_string = '';
					// calculate word charges
								$tmp_word_cnt =0;
								$tmp_word_cnt_string = $pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']];
								$tmp_word_cnt = zen_get_word_count($tmp_word_cnt_string, $vals['attributes_price_words_free']);
								$tmp_word_price = zen_get_word_count_price($tmp_word_cnt_string, $vals['attributes_price_words_free'], $vals['attributes_price_words']);

								if ($vals['attributes_price_words'] != 0) {
									$tmp_html .= TEXT_PER_WORD . $currencies->display_price($vals['attributes_price_words'], zen_get_tax_rate($this->mInfo['products_tax_class_id'])) . ($vals['attributes_price_words_free'] !=0 ? TEXT_WORDS_FREE . $vals['attributes_price_words_free'] : '');
								}
								if ($tmp_word_cnt != 0 and $tmp_word_price != 0) {
									$tmp_word_price = $currencies->display_price($tmp_word_price, zen_get_tax_rate($this->mInfo['products_tax_class_id']));
									$tmp_html = $tmp_html . '<br />' . TEXT_CHARGES_WORD . ' ' . $tmp_word_cnt . ' = ' . $tmp_word_price;
								}
					// calculate letter charges
								$tmp_letters_cnt =0;
								$tmp_letters_cnt_string = $pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']];
								$tmp_letters_cnt = zen_get_letters_count($tmp_letters_cnt_string, $vals['attributes_price_letters_free']);
								$tmp_letters_price = zen_get_letters_count_price($tmp_letters_cnt_string, $vals['attributes_price_letters_free'], $vals['attributes_price_letters']);

								if ($vals['attributes_price_letters'] != 0) {
									$tmp_html .= TEXT_PER_LETTER . $currencies->display_price($vals['attributes_price_letters'], zen_get_tax_rate($this->mInfo['products_tax_class_id'])) . ($vals['attributes_price_letters_free'] !=0 ? TEXT_LETTERS_FREE . $vals['attributes_price_letters_free'] : '');
								}
								if ($tmp_letters_cnt != 0 and $tmp_letters_price != 0) {
									$tmp_letters_price = $currencies->display_price($tmp_letters_price, zen_get_tax_rate($this->mInfo['products_tax_class_id']));
									$tmp_html = $tmp_html . '<br />' . TEXT_CHARGES_LETTERS . ' ' . $tmp_letters_cnt . ' = ' . $tmp_letters_price;
								}

							} else {
								$tmp_html = '<input class="form-control" type="text" name ="id[' . TEXT_PREFIX . $this->mOptions[$optionsId]['products_options_id'] . ']" size="' . $this->mOptions[$optionsId]['products_options_size'] .'" maxlength="' . $this->mOptions[$optionsId]['products_options_length'] . '" />';
								$tmp_html .= $products_options_details;
							}
						}




						// =-=-=-=-=-=-=-=-=-=-= file uploads

						if( is_object( $pCart ) && $this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE) {
							$number_of_uploads++;
							$tmp_html = '<input type="file" name="id[' . TEXT_PREFIX . $this->mOptions[$optionsId]['products_options_id'] . ']" /><br />' .
										$pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']] .
										zen_draw_hidden_field(UPLOAD_PREFIX . $number_of_uploads, $this->mOptions[$optionsId]['products_options_id']) .
										zen_draw_hidden_field(TEXT_PREFIX . UPLOAD_PREFIX . $number_of_uploads, $pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']]);
							$tmp_html	.= $products_options_details;
						}


						// collect attribute image if it exists and to draw in table below
						if ($this->mOptions[$optionsId]['products_options_images_style'] == '0' or ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE or $this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT or $this->mOptions[$optionsId]['products_options_type'] == '0') ) {
							if( !empty( $vals['attributes_image'] ) ) {
							$tmp_attributes_image_row++;

							if ($tmp_attributes_image_row > $this->mOptions[$optionsId]['products_options_images_per_row']) {
								$tmp_attributes_image .= '</tr><tr>';
								$tmp_attributes_image_row = 1;
							}

							$tmp_attributes_image .= '<td class="smallText" align="center">' . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . '</td>';
							}
						}

						// Read Only - just for display purposes
						if ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_READONLY) {
							$tmp_html .= $products_options_details . '<br />';
						} else {
							$productSettings['zv_display_select_option']++;
						}

						$productOptions[$optionsId]['option_values'][$valId]['value_name'] = $vals['products_options_values_name'];
						$productOptions[$optionsId]['option_values'][$valId]['value_price'] = $vals['value_price'];

						// default
						// find default attribute if set to for default dropdown
						if ($vals['attributes_default']=='1') {
							$selected_attribute = $vals['products_options_values_id'];
						}
					}
				}

				$commentPosition = (!empty( $this->mOptions[$optionsId]['products_options_comment_position'] ) && $this->mOptions[$optionsId]['products_options_comment_position'] == '1' ? '1' : '0');
				switch (true) {
					// text
					case ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT):
						if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
							$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $this->mOptions[$optionsId]['products_options_name'];
						} else {
							$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						}
						$productOptions[$optionsId]['menu'] = $tmp_html;
						$productOptions[$optionsId]['comment'] = $this->mOptions[$optionsId]['products_options_comment'];
						$productOptions[$optionsId]['comment_position'] = $commentPosition;
						break;
					// checkbox
					case ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_CHECKBOX):
						if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
							$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $this->mOptions[$optionsId]['products_options_name'];
						} else {
							$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						}
						$productOptions[$optionsId]['menu'] = '<div class="checkbox">'.$tmp_checkbox.'</div>';
						$productOptions[$optionsId]['comment'] = $this->mOptions[$optionsId]['products_options_comment'];
						$productOptions[$optionsId]['comment_position'] = $commentPosition;
						break;
					// radio buttons
					case ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_RADIO):
						if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
							$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $this->mOptions[$optionsId]['products_options_name'];
						} else {
							$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						}
						$productOptions[$optionsId]['menu'] = $tmp_radio;
						$productOptions[$optionsId]['comment'] = $this->mOptions[$optionsId]['products_options_comment'];
						$productOptions[$optionsId]['comment_position'] = $commentPosition;
						break;
					// file upload
					case ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE):
						if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
							$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $this->mOptions[$optionsId]['products_options_name'];
						} else {
							$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						}
						$productOptions[$optionsId]['menu'] = $tmp_html;
						$productOptions[$optionsId]['comment'] = $this->mOptions[$optionsId]['products_options_comment'];
						$productOptions[$optionsId]['comment_position'] = $commentPosition;
						break;
					// READONLY
					case ($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_READONLY):
						$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						$productOptions[$optionsId]['menu'] = $tmp_html;
						$productOptions[$optionsId]['comment'] = $this->mOptions[$optionsId]['products_options_comment'];
						$productOptions[$optionsId]['comment_position'] = $commentPosition;
						break;
					// dropdownmenu auto switch to selected radio button display
					case (!empty( $this->mOptions[$optionsId]['values'] ) && count( $this->mOptions[$optionsId]['values'] ) == 1):
						if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
							$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $this->mOptions[$optionsId]['products_options_name'];
						} else {
							$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						}
						$productOptions[$optionsId]['menu'] = zen_draw_radio_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']', $products_options_value_id, 'selected', NULL, $products_options_details );
						$productOptions[$optionsId]['comment'] = $this->mOptions[$optionsId]['products_options_comment'];
						$productOptions[$optionsId]['comment_position'] = $commentPosition;
						break;
					default:
						// normal dropdown menu display
						if( is_object( $pCart ) && isset($pCart->contents[$this->mProductsId]['attributes'][$this->mOptions[$optionsId]['products_options_id']])) {
							$selected_attribute = $pCart->contents[$this->mProductsId]['attributes'][$this->mOptions[$optionsId]['products_options_id']];
						} else {
						// selected set above
			//				echo 'Type ' . $this->mOptions[$optionsId]['products_options_type'] . '<br />';
						}
						if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
							$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $this->mOptions[$optionsId]['products_options_name'];
						} else {
							$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						}
						$productOptions[$optionsId]['menu'] = zen_draw_pull_down_menu('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']', $products_options_array, $selected_attribute, $this->mOptions[$optionsId]['products_options_html_attrib']);
						$productOptions[$optionsId]['comment'] = $this->mOptions[$optionsId]['products_options_comment'];
						$productOptions[$optionsId]['comment_position'] = $commentPosition;
						break;
				}
				// attributes images table
				$productOptions[$optionsId]['attributes_image'] = $tmp_attributes_image;
			}
		}
		return $productOptions;
	}


	function storeAttributeMap( $pOptionsValuesId, $pOverridePrice=NULL ) {
		if( BitBase::verifyId( $pOptionsValuesId ) && $this->isValid() ) {
			if( !$this->hasOptionValue( $pOptionsValuesId ) ) {
				$this->mDb->associateInsert( TABLE_PRODUCTS_OPTIONS_MAP, array( 'products_id' => $this->mProductsId, 'products_options_values_id' => $pOptionsValuesId, 'override_price' => $pOverridePrice ) );
			}
		}
	}


	function expungeAttributeMap( $pOptionsValuesId ) {
		if( BitBase::verifyId( $pOptionsValuesId ) && $this->isValid() ) {
			// The products_id is redundant for safety purposes
			$this->mDb->query( "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_MAP . " WHERE `products_options_values_id` = ? AND `products_id`=?", array( $pOptionsValuesId, $this->mProductsId ) );
		}
		return( count( $this->mErrors ) == 0 );
	}


	function expungeAllAttributes() {
		if( $this->isValid() ) {
			// The products_id is redundant for safety purposes
			$this->mDb->query( "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_MAP . " WHERE `products_id`=?", array( $this->mProductsId ));
		}
		return( count( $this->mErrors ) == 0 );
	}

	function expungeOptionValue( $pOptionValueId ) {
		if( $this->isValid() && $this->verifyId( $pOptionValueId ) ) {
			// The products_id is redundant for safety purposes
			$this->mDb->query( "DELETE FROM " . TABLE_PRODUCTS_OPTIONS_MAP . " WHERE `products_id`=? AND `products_options_values_id`=?", array( $this->mProductsId, $pOptionValueId ));
		}
		return( count( $this->mErrors ) == 0 );
	}

	function loadDiscounts() {
		$this->mDiscounts = array();
		if( $this->isValid() ) {
			$this->mDiscounts = $this->mDb->getAssoc( "SELECT pdq.`discount_qty` AS `hash_key`, pdq.* FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " pdq WHERE `products_id` = ? ORDER BY `discount_qty`", array( $this->mProductsId ) );
		}
		return( count( $this->mDiscounts ) );
	}

	// retrieve the discount parameter for an exact pQuantity value, this is used during storing of discounts
	function lookupDiscount( $pQuantity, $pDiscount ) {
		$ret = NULL;
		if( is_null( $this->mDiscounts ) ) {
			$this->loadDiscounts();
		}
		// mDiscounts is sorted by DESCending quantity so first one over the quantity is our greatest hit
		foreach( array_keys( $this->mDiscounts ) as $discountQty ) {
			if( $pQuantity == $discountQty ) {
				if( !empty( $this->mDiscounts[$discountQty][$pDiscount] ) ) {
					$ret = $this->mDiscounts[$discountQty][$pDiscount];
				}
			}
		}
		return $ret;
	}

	// retrieve the discount parameter for an arbitrary pQuantity
	function getDiscount( $pQuantity, $pDiscount ) {
		$ret = NULL;
		if( is_null( $this->mDiscounts ) ) {
			$this->loadDiscounts();
		}
		// mDiscounts is sorted by DESCending quantity so first one over the quantity is our greatest hit
		foreach( array_keys( $this->mDiscounts ) as $discountQty ) {
			if( $pQuantity >= $discountQty ) {
				if( !empty( $this->mDiscounts[$discountQty][$pDiscount] ) ) {
					$ret = $this->mDiscounts[$discountQty][$pDiscount];
				}
			}
		}
		return $ret;
	}


	function compareDiscount( &$pParamHash, $pDiscount ) {
		$currentDiscount = $this->lookupDiscount( $pParamHash['discount_qty'], $pDiscount );

		if( (empty( $pParamHash[$pDiscount] ) && !empty( $currentDiscount ))
			|| (!empty( $pParamHash[$pDiscount] ) && ($pParamHash[$pDiscount] != $currentDiscount) ) ) {
			$pParamHash['discounts_store'][$pDiscount] = $pParamHash[$pDiscount];
		}
	}


	function verifyDiscount( &$pParamHash ) {
		if( is_numeric( $pParamHash['discount_qty'] ) ) {
			$this->compareDiscount( $pParamHash, 'discount_qty' );
		}
		if( is_numeric( $pParamHash['discount_price'] ) ) {
			$this->compareDiscount( $pParamHash, 'discount_price' );
		}

		return( !empty( $pParamHash['discounts_store'] ) && count( $pParamHash['discounts_store'] ) );
	}

	function expungeDiscount( $pDiscountId ) {
		if( $this->isValid() && is_numeric( $pDiscountId ) ) {
			$this->mDb->query( "DELETE FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE `products_id` =? AND `discount_id`=?", array( $this->mProductsId, $pDiscountId ) );
		}
	}

	function storeDiscount( $pParamHash ) {
		if( !empty( $pParamHash['discount_id'] ) && empty( $pParamHash['discount_qty'] ) ) {
			$this->expungeDiscount( $pParamHash['discount_id'] );
		} elseif( $this->verifyDiscount( $pParamHash ) ) {
			$pParamHash['discounts_store']['products_id'] = $this->mProductsId;
			$pParamHash['discounts_store']['discount_id'] = !empty( $pParamHash['discount_id'] ) ? $pParamHash['discount_id'] : $this->lookupDiscount( $pParamHash['discount_qty'], 'discount_id' );

			// this is a little funky cause we also to an insert due to oddball updates
			if( $pParamHash['discounts_store']['discount_id'] ) {
				$this->expungeDiscount( $pParamHash['discount_id'] );
			} else {
				$pParamHash['discounts_store']['discount_id'] = (int)$this->mDb->getOne( "SELECT MAX(`discount_id`) FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE `products_id`=?", array( $this->mProductsId ) ) + 1;
			}
			if( !isset( $pParamHash['discounts_store']['discount_qty'] ) ) {
				 $pParamHash['discounts_store']['discount_qty'] = $pParamHash['discount_qty'];
			}
			if( !isset( $pParamHash['discounts_store']['discount_price'] ) ) {
				 $pParamHash['discount_price']['discount_qty'] = $pParamHash['discount_price'];
			}
			$this->mDb->associateInsert( TABLE_PRODUCTS_DISCOUNT_QUANTITY, $pParamHash['discounts_store'] );
		}
		return( !empty( $pParamHash['discount_price'] ) && count( $pParamHash['discount_price'] ) );
	}

	////
	// Display Price Retail
	// Specials and Tax Included
	function expunge() {
		global $gBitSystem;
		if( $this->isValid() ) {
			$this->StartTrans();
/*
Skip deleting of images for now
			if( !empty( $this->mInfo['products_image'] ) ) {
				$duplicate_image = $this->mDb->GetOne("SELECT count(*) as `total`
								 FROM " . TABLE_PRODUCTS . "
								 WHERE `products_image` = ?", array( $this->mInfo['products_image'] ) );
				if ($duplicate_image < 2 ) {
					$products_image = $product_image->fields['products_image'];
					$products_image_extention = substr($products_image, strrpos($products_image, '.'));
					$products_image_base = str_replace($products_image_extention, '', $products_image);

					$filename_medium = 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . $products_image_extention;
							$filename_large = 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . $products_image_extention;

					if (file_exists(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image'])) {
						@unlink(DIR_FS_CATALOG_IMAGES . $product_image->fields['products_image']);
					}
					if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_medium)) {
						@unlink(DIR_FS_CATALOG_IMAGES . $filename_medium);
					}
					if (file_exists(DIR_FS_CATALOG_IMAGES . $filename_large)) {
						@unlink(DIR_FS_CATALOG_IMAGES . $filename_large);
					}
				}
*/
			$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . " WHERE `products_id` = ?", array( $this->mProductsId ) );
			$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE `products_id` = ?", array( $this->mProductsId ) );
			$this->mDb->query("DELETE FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE `products_id` = ?", array( $this->mProductsId ));

			// remove downloads if they exist
			$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE `products_options_values_id` IN (SELECT pa.`products_options_values_id` FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )	WHERE pom.`products_id` = ?)", array( $this->mProductsId ) );
			$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_MAP . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE `customers_basket_id` IN (SELECT `customers_basket_id` FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `products_id` = ?)", array( $this->mProductsId ));
			$this->mDb->query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `products_id` = ?", array( $this->mProductsId ));

			if( $productReviews = $this->mDb->getCol("SELECT `reviews_id` FROM " . TABLE_REVIEWS . " WHERE `products_id` = ?", array( $this->mProductsId )) ) {
				foreach( $productReviews as $reviewId ) {
					$this->mDb->query("DELETE FROM " . TABLE_REVIEWS_DESCRIPTION . " WHERE `reviews_id` = ?", array( $reviewId ) );
				}
			}

			$this->mDb->query("DELETE FROM " . TABLE_REVIEWS . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("DELETE FROM " . TABLE_FEATURED . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("DELETE FROM " . TABLE_SPECIALS . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE `products_id` = ?", array( $this->mProductsId ));
			if( !$this->isPurchased() ) {
				$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE `products_id` = ?", array( $this->mProductsId ));
				$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS . " WHERE `products_id` = ?", array( $this->mProductsId ));
				LibertyMime::expunge();
			} else {
				$this->update( array( 'related_content_id' => NULL ) );
				$this->storeStatus( $gBitSystem->getConfig( 'liberty_status_deleted', -999 ) );
			}


			$this->mInfo = array();
			$this->mProductsId = NULL;

			$this->CompleteTrans();
		}
		return( count( $this->mErrors ) == 0 );
	}

	function quantityInCart( $pProductsId = NULL ) {
		global $gBitCustomer;
		if( empty( $pProductsId ) && !empty( $this->mProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		return $gBitCustomer->mCart->get_quantity( $pProductsId );
	}

	////
	// Return quantity buy now
	function getBuyNowQuantity( $pProductsId = NULL) {
		global $gBitCustomer;
		if( empty( $pProductsId ) && !empty( $this->mProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}

		$check_min = zen_get_products_quantity_order_min( $pProductsId );
		$check_units = zen_get_products_quantity_order_units( $pProductsId );
		$buy_now_qty=1;
		// works on Mixed ON
		switch (true) {
			case ($gBitCustomer->mCart->in_cart_mixed($pProductsId) == 0 ):
				if ($check_min >= $check_units) {
				$buy_now_qty = $check_min;
				} else {
				$buy_now_qty = $check_units;
				}
				break;
			case ($gBitCustomer->mCart->in_cart_mixed($pProductsId) < $check_min):
				$buy_now_qty = $check_min - $gBitCustomer->mCart->in_cart_mixed($pProductsId);
				break;
			case ($gBitCustomer->mCart->in_cart_mixed($pProductsId) > $check_min):
				// set to units or difference in units to balance cart
				$new_units = $check_units - fmod($gBitCustomer->mCart->in_cart_mixed($pProductsId), $check_units);
				//echo 'Cart: ' . $gBitCustomer->mCart->in_cart_mixed($pProductsId) . ' Min: ' . $check_min . ' Units: ' . $check_units . ' fmod: ' . fmod($gBitCustomer->mCart->in_cart_mixed($pProductsId), $check_units) . '<br />';
				$buy_now_qty = ($new_units > 0 ? $new_units : $check_units);
				break;
			default:
				$buy_now_qty = $check_units;
				break;
		}
		if ($buy_now_qty <= 0) {
			$buy_now_qty = 1;
		}
		return $buy_now_qty;
	}


	////
	// Return a products quantity minimum and units display
	function getQuantityMinUnitsDisplay($pProductsId = NULL, $include_break = true, $shopping_cart_msg = false) {
		if( empty( $pProductsId ) && !empty( $this->mProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		$check_min = zen_get_products_quantity_order_min($pProductsId);
		$check_units = zen_get_products_quantity_order_units($pProductsId);

		$the_min_units='';

		if ($check_min != 1 or $check_units != 1) {
			if ($check_min != 1) {
				$the_min_units .= PRODUCTS_QUANTITY_MIN_TEXT_LISTING . '&nbsp;' . $check_min;
			}
			if ($check_units != 1) {
				$the_min_units .= ($the_min_units ? ' ' : '' ) . PRODUCTS_QUANTITY_UNIT_TEXT_LISTING . '&nbsp;' . $check_units;
			}

			if (($check_min > 0 or $check_units > 0) and !zen_get_products_quantity_mixed($pProductsId)) {
				if ($include_break == true) {
					$the_min_units .= '<br />' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_OFF : TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART);
				} else {
					$the_min_units .= '&nbsp;&nbsp;' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_OFF : TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART);
				}
			} else {
				if ($include_break == true) {
					$the_min_units .= '<br />' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_ON : TEXT_PRODUCTS_MIX_ON_SHOPPING_CART);
				} else {
					$the_min_units .= '&nbsp;&nbsp;' . ($shopping_cart_msg == false ? TEXT_PRODUCTS_MIX_ON : TEXT_PRODUCTS_MIX_ON_SHOPPING_CART);
				}
			}
		}

		// quantity max
		$check_max = zen_get_products_quantity_order_max($pProductsId);

		if ($check_max != 0) {
			if ($include_break == true) {
				$the_min_units .= ($the_min_units != '' ? '<br />' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
			} else {
				$the_min_units .= ($the_min_units != '' ? '&nbsp;&nbsp;' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
			}
		}

		return $the_min_units;
	}


	function expungeNotification( $pCustomersId, $pProductsId=NULL ) {
		if( empty( $pProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		if( is_numeric( $pProductsId ) && is_numeric( $pCustomersId ) ) {
			$sql = "DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . " WHERE `products_id` = ? AND `customers_id` = ? ";
			$this->mDb->query( $sql, array( $pProductsId, $pCustomersId ) );
		}
	}

	function storeNotification( $pCustomersId, $pProductsId=NULL ) {
		if( empty( $pProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		if( is_numeric( $pProductsId ) && is_numeric( $pCustomersId ) && !$this->hasNotification( $pCustomersId, $pProductsId ) ) {
			$sql = "INSERT INTO " . TABLE_PRODUCTS_NOTIFICATIONS . " (`products_id`, `customers_id`, `date_added`) values (?, ?, ?)";
			$this->mDb->query( $sql, array( $pProductsId, $pCustomersId, $this->mDb->NOW() ) );
		}
	}

	function loadAttributes( $pRefresh=FALSE ) {
		if( $this->isValid() && (empty( $this->mOptions ) || $pRefresh) ) {
			$this->mOptions = array();
			if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
				$options_order_by= ' ORDER BY popt.`products_options_sort_order`';
			} else {
				$options_order_by= ' ORDER BY popt.`products_options_name`';
			}

			$sql = "SELECT distinct popt.`products_options_id` AS hash_key, popt.*
					FROM " . TABLE_PRODUCTS_OPTIONS . " popt
						INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa ON(pa.`products_options_id` = popt.`products_options_id`)
						INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )
					WHERE pom.`products_id`= ? AND popt.`language_id` = ? " .
					$options_order_by;

			if( $this->mOptions = $this->mDb->GetAssoc($sql, array( $this->mProductsId, (int)$_SESSION['languages_id'] ) ) ) {
				if ( PRODUCTS_OPTIONS_SORT_BY_PRICE =='1' ) {
					$order_by= ' ORDER BY pa.`products_options_sort_order`, pa.`products_options_values_name`';
				} else {
					$order_by= ' ORDER BY pa.`products_options_sort_order`, pa.`options_values_price`';
				}

				foreach( array_keys( $this->mOptions ) as $optionsId ) {
					$sql = "SELECT pa.`products_options_values_id`, pa.`products_options_values_name`, pa.*
							FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
								INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )
							WHERE pom.`products_id`=? AND pa.`products_options_id`=? " .
							$order_by;
					if( $rs = $this->mDb->query( $sql, array( $this->mProductsId, $optionsId ) ) ) {
						$this->mOptions[$optionsId]['values'] = array();
						while( !$rs->EOF ) {
							$this->mOptions[$optionsId]['values'][$rs->fields['products_options_values_id']] = $rs->fields;
							$rs->MoveNext();
						}
					}
				}
			}
		}
		return( count( $this->mOptions ) );
	}

	function hasOptionValue( $pOptionsValuesId ) {
		$ret = FALSE;
		if( !empty( $this->mOptions ) ) {
			// options are loaded, just check the hash
			$ret = !empty( $this->mOptions[$pOptionsValuesId] );
		} elseif( $this->isValid() && $this->verifyId( $pOptionsValuesId ) ) {
			$ret = $this->mDb->getOne( "SELECT `products_id` FROM " . TABLE_PRODUCTS_OPTIONS_MAP . " WHERE `products_id`=? AND `products_options_values_id`=?", array( $this->mProductsId, $pOptionsValuesId ) );
		}
		return( $ret );
	}


	function hasNotification( $pCustomersId, $pProductsId=NULL ) {
		$ret = FALSE;
		if( empty( $pProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		if( $this->isValid() && is_numeric( $pCustomersId ) ) {
			$query = "SELECT count(*) AS `ncount` FROM " . TABLE_PRODUCTS_NOTIFICATIONS . " WHERE `products_id`=? and `customers_id`=?";
			$ret = $this->mDb->getOne($query, array( $pProductsId, $pCustomersId ) );
		}
		return $ret;
	}

	function hasReviews() {
		if( $this->isValid() ) {
			// if review must be approved or disabled do not show review
			$review_status = " AND r.status = '1'";
			$sql = "SELECT COUNT(*) as `rcount`
					FROM " . TABLE_REVIEWS . " r INNER JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON (r.`reviews_id` = rd.`reviews_id`)
					WHERE r.`products_id` = ? AND rd.`languages_id` = ?" . $review_status;

			return( $this->mDb->GetOne( $sql, array( $this->mProductsId, $_SESSION['languages_id'] ) ) );
		}
	}

	public function displayOrderData( $pOrdersProductHash ) {
		// stub function does nothing in base class
	}

	public function isVirtual( $pOptionsHash=NULL ) {
		return $this->getField( 'products_virtual' );
	}

	function isFree() {
		return( !empty( $this->mInfo['product_is_free'] ) );
	}

	function needsCheckoutReview($pItem){
		return false;
	}

	static function getTypes() {
		global $gBitDb;
		return $gBitDb->getAssoc( "SELECT `type_id`, * FROM " . TABLE_PRODUCT_TYPES . " ORDER BY `type_name`" );
	}

	static function getCommerceObject( $pLookupMixed ) {
		global $gBitDb;
		$product = NULL;
		$lookupValue = NULL;
		$lookupKey = NULL;

		if( is_array( $pLookupMixed ) && count( $pLookupMixed ) == 1 ) {
			$currentValue = current( $pLookupMixed );
			if( BitBase::verifyId( $currentValue) ) {
				$lookupKey = key( $pLookupMixed );
				$lookupValue = $currentValue;
			}
		} elseif( BitBase::verifyId( $pLookupMixed ) ) {
			$lookupKey = 'products_id';
			$lookupValue = $pLookupMixed;
		}

		if( !empty( $lookupValue ) ) {
			$sql = "SELECT `type_id` AS `hash_key`, cpt.*
					FROM " . TABLE_PRODUCT_TYPES . " cpt
						LEFT JOIN " . TABLE_PRODUCTS . " cp ON(cpt.`type_id`=cp.`products_type`)
					WHERE `$lookupKey`=?";
			$productTypes = $gBitDb->getRow( $sql, array( $lookupValue ) );

			if( !empty( $productTypes['type_class'] ) && !empty( $productTypes['type_class_file'] ) ) {
				require_once( BIT_ROOT_PATH.$productTypes['type_class_file'] );
				if( class_exists(	$productTypes['type_class'] ) ) {
					$productClass = $productTypes['type_class'];
				}
			}
		}

		if( empty( $productClass ) ) {
			$productClass = get_called_class();
		}

		$productsId = ( $lookupKey == 'products_id' ) ? $lookupValue : NULL;
		$contentId = ( $lookupKey == 'content_id' ) ? $lookupValue : NULL;

		if( !($product = $productClass::loadFromCache( $productsId )) ) {
			$product = new $productClass( $productsId, $contentId );

			if( !$product->load() ) {
				unset( $product->mProductsId );
			}
		}

		return $product;
	}
}


/**
 * return a proper Commerce object based on the product_types.type_class
 *
 * @param mixed If an integer, a product_id is assumed, else a key=>value hash (e.g. content_id=>1234 ) to lookup the product
 * See verify for details of the values required
 */
function bc_get_commerce_product( $pLookupMixed ) {
	return CommerceProduct::getCommerceObject( $pLookupMixed );
}

if( !defined( 'TABLE_PRODUCTS' ) ) {
	// we might be coming in from LibertyBase::getLibertyObject
	// keep bitcommerce_start_inc at the bottom of the file, *after* the class has been declared
	// because bitcommerce_start_inc creates a default gBitProduct of type CommerceProduct
	require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
}

?>

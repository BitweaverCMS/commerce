<?php
/**
 * @version	$Header: /cvsroot/bitweaver/_bit_commerce/classes/CommerceProduct.php,v 1.133 2009/02/10 22:17:25 spiderr Exp $
 *
 * System class for handling the liberty package
 *
 * @package	bitcommerce
 * @author	 spider <spider@steelsun.com>
 */

//
// +----------------------------------------------------------------------+
// | bitcommerce															|
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org									 |
// |																		|
// | http://www.bitcommerce.org											 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +----------------------------------------------------------------------+
//	$Id: CommerceProduct.php,v 1.133 2009/02/10 22:17:25 spiderr Exp $
//

/**
 * Initialization
 */
require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );
if( !defined( 'TABLE_PRODUCTS' ) ) {
	// we might be coming in from LibertyBase::getLibertyObject
	require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
}

/**
 * Class for handling the commerce product
 *
 * @package bitcommerce
 */
class CommerceProduct extends LibertyMime {
	var $mProductsId;
	var $mOptions;
	var $mRelatedContent;

	function CommerceProduct( $pProductsId=NULL, $pContentId=NULL ) {
		LibertyMime::LibertyMime();
		$this->registerContentType( BITPRODUCT_CONTENT_TYPE_GUID, array(
						'content_type_guid' => BITPRODUCT_CONTENT_TYPE_GUID,
						'content_description' => 'Product',
						'handler_class' => 'CommerceProduct',
						'handler_package' => 'bitcommerce',
						'handler_file' => 'classes/CommerceProduct.php',
						'maintainer_url' => 'http://www.bitcommerce.org'
				) );
		$this->mProductsId = $pProductsId;
		$this->mContentId = $pContentId;
		$this->mContentTypeGuid = BITPRODUCT_CONTENT_TYPE_GUID;
		$this->mViewContentPerm  = 'p_commerce_product_view';
		$this->mUpdateContentPerm  = 'p_commerce_product_update';
		$this->mCreateContentPerm  = 'p_commerce_product_create';
		$this->mAdminContentPerm = 'p_commerce_admin';
		$this->mOptions = NULL;
		$this->mRelatedContent = NULL;
	}

	function load( $pFullLoad = TRUE ) {
		global $gBitUser;
		if( empty( $this->mProductsId ) && !empty( $this->mContentId ) ) {
			$this->mProductsId = $this->mDb->getOne( "SELECT `products_id` FROM ".TABLE_PRODUCTS." WHERE `content_id`=?", array( $this->mContentId ) );
		}
		if( is_numeric( $this->mProductsId ) && $this->mInfo = $this->getProduct( $this->mProductsId ) ) {
			$this->mContentId = $this->getField( 'content_id' );
			parent::load();
			if( $this->isDeleted() && !($gBitUser->hasPermission( 'p_commerce_admin' ) || $this->isPurchased()) ) {
				$this->mInfo = array();
				unset( $this->mRelatedContent );
				unset( $this->mProductsId );
			} else {
				$this->mContentId = $this->mInfo['content_id'];
				$this->loadPricing();
			}
			if( $pFullLoad && !empty( $this->mInfo['related_content_id'] ) ) {
				global $gLibertySystem;
				if( $this->mRelatedContent = $gLibertySystem->getLibertyObject( $this->mInfo['related_content_id'] ) ) {
					$this->mInfo['display_link'] = $this->mRelatedContent->getDisplayLink( $this->mRelatedContent->getTitle(), $this->mRelatedContent->mInfo );
				}
			}
		}
		return( count( $this->mInfo ) );
	}

	////
	// Actual Price Retail
	// Specials and Tax Included
	function loadPricing() {
		$ret = 0;
		if( $this->isValid() ) {
			$show_display_price = '';
			$this->mInfo['normal_price']	= zen_get_products_base_price( $this->mProductsId );
			$this->mInfo['special_price']	= zen_get_products_special_price( $this->mProductsId, true );
			$this->mInfo['sale_price']		= zen_get_products_special_price( $this->mProductsId, false );

			$this->mInfo['actual_price']	= $this->mInfo['normal_price'];

			if( $this->mInfo['special_price'] ) {
				$this->mInfo['actual_price'] = $this->mInfo['special_price'];
			}
			if( $this->mInfo['sale_price'] ) {
				$this->mInfo['actual_price'] = $this->mInfo['sale_price'];
			}

			// If Free, Show it
			if( $this->getField( 'product_is_free' ) == '1' ) {
				$this->mInfo['actual_price'] = 0;
			}
		}
	}

	// LibertyMime override
	function getStorageSubDirName() {
		return 'products';
	}

	function loadByRelatedContent( $pContentId ) {
		if( is_numeric( $pContentId ) ) {
			if( $this->mProductsId = $this->mDb->getOne( "SELECT `products_id` FROM " . TABLE_PRODUCTS . " WHERE `related_content_id`=?", array( $pContentId ) ) ) {
				return( $this->load() );
			}
		}
	}

	function getProduct( $pProductsId ) {
		$ret = NULL;
		if( is_numeric( $pProductsId ) ) {
			$bindVars = array(); $selectSql = ''; $joinSql = ''; $whereSql = '';
			array_push( $bindVars, $pProductsId, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );
			$query = "SELECT p.*, pd.*, pt.*, uu.`real_name`, uu.`login` $selectSql , m.*, cat.*, catd.*, lc.*
					  FROM " . TABLE_PRODUCTS . " p
							INNER JOIN ".TABLE_PRODUCT_TYPES." pt ON (p.`products_type`=pt.`type_id`)
							INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=p.`content_id`)
							INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=lc.`user_id`) $joinSql
							INNER JOIN ".TABLE_CATEGORIES." cat ON ( p.`master_categories_id`=cat.`categories_id` )
						LEFT OUTER JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON (p.`products_id`=pd.`products_id`)
						LEFT OUTER JOIN ".TABLE_CATEGORIES_DESCRIPTION." catd ON ( cat.`categories_id`=catd.`categories_id` AND catd.`language_id`=pd.`language_id` )
						LEFT OUTER JOIN ".TABLE_MANUFACTURERS." m ON ( p.`manufacturers_id`=m.`manufacturers_id` )
						LEFT OUTER JOIN ".TABLE_SUPPLIERS." s ON ( p.`suppliers_id`=s.`suppliers_id` )					  WHERE p.`products_id`=? AND pd.`language_id`=? $whereSql";
// Leave these out for now... and possibly forever. These can produce multiple row returns
//						LEFT OUTER JOIN ".TABLE_TAX_CLASS." txc ON ( p.`products_tax_class_id`=txc.`tax_class_id` )
//						LEFT OUTER JOIN ".TABLE_TAX_RATES." txr ON ( txr.`tax_class_id`=txc.`tax_class_id` )
			if( $ret = $this->mDb->getRow( $query, $bindVars ) ) {
				if( !empty( $ret['products_image'] ) ) {
					$ret['products_image_url'] = CommerceProduct::getImageUrl( $ret['products_image'] );
				} else {
					$ret['products_image_url'] = NULL;
				}
				$ret['products_weight_kg'] = $ret['products_weight'] * .45359;
				$ret['info_page'] = $ret['type_handler'].'_info';
			}
		}
		return $ret;
	}

	function getCommissionDiscount() {
		global $gBitUser;
		$ret = 0;
		if( $this->isValid() ) {
			$ret = $this->hasUpdatePermission( FALSE ) ? $this->getField( 'products_commission' ) : 0;
		}
		return $ret;
	}

	function getPurchasePrice( $pQuantity=1 ) {
		$ret = NULL;
		if( $this->isValid() ) {
			$ret = $this->getField( 'actual_price' );
			// adjusted count for free shipping
			if ($this->getField('product_is_always_free_ship') != 1 and $this->getField('products_virtual') != 1) {
			$products_weight = $this->getField('products_weight');
			} else {
			$products_weight = 0;
			}

			$special_price = zen_get_products_special_price( $this->mProductsId );
			if ($special_price and $this->getField('products_priced_by_attribute') == 0) {
			$ret = $special_price;
			} else {
			$special_price = 0;
			}

			if (zen_get_products_price_is_free($this->mProductsId)) {
			// no charge
			$ret = 0;
			}

// adjust price for discounts when priced by attribute
			if ($this->getField('products_priced_by_attribute') == '1' and zen_has_product_attributes($this->getField('products_id'), 'false')) {
			// reset for priced by attributes
//			$products_price = $products->fields['products_price'];
			if ($special_price) {
				$ret = $special_price;
			} else {
				// get the base price quantity discount. attribute discounts will be calculated later
				$ret = $this->getQuantityPrice( $pQuantity, $this->getField('products_price') );
			}
			} else {
// discount qty pricing
			if( $this->getField('products_discount_type') ) {
				$ret = $this->getQuantityPrice( $pQuantity );
			}
			}
		}
		return $ret;
	}

	function getQuantityPrice( $pQuantity, $pCheckAmount=0 ) {
		global $gBitDb, $cart;
		if( is_object( $_SESSION['cart'] ) ) {
			$new_qty = $_SESSION['cart']->in_cart_mixed_discount_quantity( $this->mProductsId );
			// check for discount qty mix
			if ($new_qty > $pQuantity) {
				$pQuantity = $new_qty;
			}
		}

		$discountPrice = $gBitDb->getOne( "SELECT `discount_price` from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where `products_id`=? and `discount_qty` <= ? ORDER BY `discount_qty` DESC", array( $this->mProductsId, $pQuantity ) );

		$display_price = zen_get_products_base_price(  $this->mProductsId );
		$display_specials_price = zen_get_products_special_price( $this->mProductsId, true);

		switch( $this->getField('products_discount_type') ) {
			// none
			case (empty( $discountPrice )):
			case '0':
					$discounted_price = ($pCheckAmount ? $pCheckAmount : zen_get_products_actual_price( $this->mProductsId )) - $this->getCommissionDiscount();
				break;
			// percentage discount
			case '1':
				if ($this->getField('products_discount_type_from') == '0') {
					// priced by attributes
					$checkPrice = (($pCheckAmount != 0) ? $pCheckAmount : $display_price) - $this->getCommissionDiscount();
				} elseif ( $display_specials_price ) {
					$checkPrice = $display_specials_price - $this->getCommissionDiscount();
				} else {
					$checkPrice = (($pCheckAmount != 0) ? $pCheckAmount : $display_price) - $this->getCommissionDiscount();
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
					$discounted_price = $display_price - $discountPrice;
				} else {
					if (!$display_specials_price) {
						$discounted_price = $display_price - $discountPrice;
					} else {
						$discounted_price = $display_specials_price - $discountPrice;
					}
				}
				break;
		}

		return $discounted_price;
	}


	function getPrice( $pType = 'actual' ) {
		$ret = 0;
		if( $this->isValid() ) {
			$ret = $this->getNotatedPrice( $this->getField( $pType.'_price' ), $this->getField( 'products_tax_class_id' ) );
		}
		return $ret;
	}


	function getNotatedPrice( $pPrice, $pTaxClassId ) {
		global $currencies;
		return $currencies->display_price( $pPrice, zen_get_tax_rate( $pTaxClassId ) );
	}


	////
	// Display Price Retail
	// Specials and Tax Included
	function getDisplayPrice( $pProductsId=NULL ) {
		global $gBitDb;
		if( empty( $pProductsId ) && !empty( $this ) && !empty( $this->mProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		// 0 = normal shopping
		// 1 = Login to shop
		// 2 = Can browse but no prices
		// verify display of prices
		switch (true) {
			case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
				// customer must be logged in to browse
				return '';
				break;
			case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
				// customer may browse but no prices
				return TEXT_LOGIN_FOR_PRICE_PRICE;
				break;
			case (CUSTOMERS_APPROVAL == '3' and TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM != ''):
				// customer may browse but no prices
				return TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM;
				break;
			case (CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and $_SESSION['customer_id'] == ''):
				// customer must be logged in to browse
				return TEXT_AUTHORIZATION_PENDING_PRICE;
				break;
			case ((CUSTOMERS_APPROVAL_AUTHORIZATION != '0' and CUSTOMERS_APPROVAL_AUTHORIZATION != '3') and $_SESSION['customers_authorization'] > '0'):
				// customer must be logged in to browse
				return TEXT_AUTHORIZATION_PENDING_PRICE;
				break;
			default:
			// proceed normally
			break;
		}

		// show case only
		if (STORE_STATUS != '0') {
			if (STORE_STATUS == '1') {
				return '';
			}
		}

		// $new_fields = ', `product_is_free`, `product_is_call`, `product_is_showroom_only`';
		$product_check = $gBitDb->getRow("select `products_tax_class_id`, `products_price` , `products_commission`, `products_priced_by_attribute`, `product_is_free`, `product_is_call` from " . TABLE_PRODUCTS . " where `products_id` = ? ", array( (int)$pProductsId ) );

		$show_display_price = '';
		$display_normal_price = zen_get_products_base_price($pProductsId);
		$display_special_price = zen_get_products_special_price($pProductsId, true);
		$display_sale_price = zen_get_products_special_price($pProductsId, false);

		$show_sale_discount = '';
		if (SHOW_SALE_DISCOUNT_STATUS == '1' and ($display_special_price != 0 or $display_sale_price != 0)) {
			if ($display_sale_price) {
				if (SHOW_SALE_DISCOUNT == 1) {
					if ($display_normal_price != 0) {
						$show_discount_amount = number_format(100 - (($display_sale_price / $display_normal_price) * 100),SHOW_SALE_DISCOUNT_DECIMALS);
					} else {
						$show_discount_amount = '';
					}
					$show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . $show_discount_amount . PRODUCT_PRICE_DISCOUNT_PERCENTAGE . '</span>';

				} else {
					$show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . CommerceProduct::getNotatedPrice( ($display_normal_price - $display_sale_price), $product_check['products_tax_class_id'] ) . PRODUCT_PRICE_DISCOUNT_AMOUNT . '</span>';
				}
			} else {
				if (SHOW_SALE_DISCOUNT == 1) {
				$show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . number_format(100 - (($display_special_price / $display_normal_price) * 100),SHOW_SALE_DISCOUNT_DECIMALS) . PRODUCT_PRICE_DISCOUNT_PERCENTAGE . '</span>';
				} else {
				$show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . CommerceProduct::getNotatedPrice( ($display_normal_price - $display_special_price), $product_check['products_tax_class_id'] ) . PRODUCT_PRICE_DISCOUNT_AMOUNT . '</span>';
				}
			}
		}

		if ($display_special_price) {
			$show_normal_price = '<span class="normalprice">' . CommerceProduct::getNotatedPrice( $display_normal_price, $product_check['products_tax_class_id'] ) . ' </span>';
			if ($display_sale_price && $display_sale_price != $display_special_price) {
				$show_special_price = '&nbsp;' . '<span class="productSpecialPriceSale">' . CommerceProduct::getNotatedPrice( $display_special_price, $product_check['products_tax_class_id'] ) . '</span>';
				if ($product_check['product_is_free'] == '1') {
				$show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . '<s>' . CommerceProduct::getNotatedPrice( $display_sale_price, $product_check['products_tax_class_id'] ) . '</s>' . '</span>';
				} else {
				$show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . CommerceProduct::getNotatedPrice( $display_sale_price, $product_check['products_tax_class_id'] ) . '</span>';
				}
			} else {
				if ($product_check['product_is_free'] == '1') {
				$show_special_price = '&nbsp;' . '<span class="productSpecialPrice">' . '<s>' . CommerceProduct::getNotatedPrice( $display_special_price, $product_check['products_tax_class_id'] ) . '</s>' . '</span>';
				} else {
				$show_special_price = '&nbsp;' . '<span class="productSpecialPrice">' . CommerceProduct::getNotatedPrice( $display_special_price, $product_check['products_tax_class_id'] ) . '</span>';
				}
				$show_sale_price = '';
			}
		} else {
			if ($display_sale_price) {
				$show_normal_price = '<span class="normalprice">' . CommerceProduct::getNotatedPrice( $display_normal_price, $product_check['products_tax_class_id'] ) . ' </span>';
				$show_special_price = '';
				$show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . CommerceProduct::getNotatedPrice( $display_sale_price, $product_check['products_tax_class_id'] ) . '</span>';
			} else {
				if ($product_check['product_is_free'] == '1') {
				$show_normal_price = '<s>' . CommerceProduct::getNotatedPrice( $display_normal_price, $product_check['products_tax_class_id'] ) . '</s>';
				} else {
					$show_normal_price = CommerceProduct::getNotatedPrice( $display_normal_price, $product_check['products_tax_class_id'] );
				}
				$show_special_price = '';
				$show_sale_price = '';
			}
		}

		if ($display_normal_price == 0) {
			// don't show the $0.00
			$final_display_price = $show_special_price . $show_sale_price . $show_sale_discount;
		} else {
			$final_display_price = $show_normal_price . $show_special_price . $show_sale_price . $show_sale_discount;
		}

		// If Free, Show it
		$free_tag = '';
		if ($product_check['product_is_free'] == '1') {
			if (OTHER_IMAGE_PRICE_IS_FREE_ON=='0') {
				$free_tag = '<br />' . PRODUCTS_PRICE_IS_FREE_TEXT;
			} else {
				$free_tag = '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_PRICE_IS_FREE, PRODUCTS_PRICE_IS_FREE_TEXT);
			}
		}

		// If Call for Price, Show it
		$call_tag = '';
		if ($product_check['product_is_call']) {
			if (PRODUCTS_PRICE_IS_CALL_IMAGE_ON=='0') {
				$call_tag = '<br />' . PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT;
			} else {
				$call_tag = '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_CALL_FOR_PRICE, PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT);
			}
		}
		return $final_display_price . $free_tag . $call_tag;
	}


	function getTitle() {
		if( $this->isValid() ) {
			return( $this->mInfo['products_name'] );
		}
	}

	function getTypeName() {
		if( $this->isValid() ) {
			return( $this->mInfo['type_name'] );
		}
	}

	function getDisplayUrl( $pProductsId=NULL, $pCatPath=NULL ) {
		global $gBitSystem;
		if( empty( $pProductsId ) && is_object( $this ) && $this->isValid() ) {
			$pProductsId = $this->mProductsId;
		}
		$ret = BITCOMMERCE_PKG_URL;
		if( is_numeric( $pProductsId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret .= $pProductsId;
				if( !empty( $pCatPath ) ) {
					$ret .= '/' . $pCatPath;
				}
			} else {
				$ret .= 'index.php?products_id='.$pProductsId;
				if( !empty( $pCatPath ) ) {
					$ret .= '&cPath=' . $pCatPath;
				}
			}
		}
		return $ret;
	}

	function getThumbnailFile( $pSize='small', $pContentId=NULL, $pProductsId=NULL ) {
		return( BIT_ROOT_PATH.CommerceProduct::getImageUrl( $pProductsId, $pSize ) );
	}

	function getThumbnailUrl( $pSize='small', $pContentId=NULL, $pProductsId=NULL ) {
		return( CommerceProduct::getImageUrl( $pProductsId, $pSize ) );
	}

	function getImageUrl( $pMixed=NULL, $pSize='small' ) {
//		if( empty( $pMixed ) && !empty( $this ) && is_object( $this ) && !empty( $this->mStorage ) ) {
//			$thumbImage = current( $this->mStorage );
//			$ret = $thumbImage['thumbnail_url'][$pSize];
//error_log( $pSize );			

		if( empty( $pMixed ) && !empty( $this ) && is_object( $this ) && !empty( $this->mProductsId ) ) {
			$pMixed = $this->mProductsId;
		}

		if( is_numeric( $pMixed ) ) {
			$path = ($pMixed % 1000).'/'.$pMixed.'/';
			if( is_dir( STORAGE_PKG_PATH.BITCOMMERCE_PKG_NAME.'/'.$path.'thumbs/' ) ) {
				$path .= 'thumbs/';
			}
			$path .= $pSize;
			if( file_exists( STORAGE_PKG_PATH.BITCOMMERCE_PKG_NAME.'/'.$path.'.jpg' ) ) {
				$ret = STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/'.$path.'.jpg';
			} elseif( file_exists( STORAGE_PKG_PATH.BITCOMMERCE_PKG_NAME.'/'.$path.'.png' ) ) {
				$ret = STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/'.$path.'.png';
			} else {
				$ret = BITCOMMERCE_PKG_URL.'images/blank_'.$pSize.'.jpg';
			}
		} else {
			$ret = STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/images/'.$pMixed;
		}
		return $ret;
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

	function getList( &$pListHash ) {
		global $gBitSystem, $gBitUser;
			if( empty( $pListHash['sort_mode'] ) ) {
			$pListHash['sort_mode'] = 'products_date_added_desc';
		}
		BitBase::prepGetList( $pListHash );
		$bindVars = array();
		$selectSql = '';
		$joinSql = '';
		$whereSql = '';

		if( @BitBase::verifyId( $pListHash['content_status_id'] ) ) {
			$bindVars[] = $pListHash['content_status_id'];
			$whereSql = ' lc.`content_status_id` = ? ';
		} elseif( $gBitUser->hasPermission( 'p_commerce_admin' ) ) {
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
			$pListHash['thumbnail_size'] = 'icon';
		}

		if( !empty( $pListHash['featured'] ) ) {
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

		if( !empty( $pListHash['freshness'] ) ) {
			if ( $pListHash['freshness'] == '1' ) {
				$whereSql .= " and ".$this->mDb->SQLDate( 'Ym', 'p.`products_date_added`' )." >= ".$this->mDb->SQLDate( 'Ym' );
			} else {
				$whereSql .= ' and '.$this->mDb->OffsetDate( SHOW_NEW_PRODUCTS_LIMIT, 'p.`products_date_added`' ).' > '. $this->mDb->NOW();
			}
		}

		if( !empty( $pListHash['reviews'] ) ) {
			$selectSql .= ' , r.`reviews_rating`, rd.`reviews_text` ';
			$joinSql .= " INNER JOIN " . TABLE_REVIEWS . " r  ON ( p.`products_id` = r.`products_id` ) INNER JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON ( r.`reviews_id` = rd.`reviews_id` ) ";
			$whereSql .= " AND r.`status` = '1' AND rd.`languages_id` = ? ";
			array_push( $bindVars, (int)$_SESSION['languages_id'] );
		}

		if ( !empty( $pListHash['category_id'] ) ) {
			if( !is_numeric( $pListHash['category_id'] ) && strpos( $pListHash['category_id'], '_' ) ) {
				$path = split( '_', $pListHash['category_id'] );
				end( $path );
				$pListHash['category_id'] = current( $path );
			}
			if( is_numeric( $pListHash['category_id'] ) ) {
				$joinSql .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON ( p.`products_id` = p2c.`products_id` ) LEFT JOIN " . TABLE_CATEGORIES . " c ON ( p2c.`categories_id` = c.`categories_id` )";
				$whereSql .= " AND c.`categories_id`=? ";
				array_push( $bindVars, $pListHash['category_id'] );
			}
		}

		if( empty( $pListHash['all_status'] ) ) {
			$whereSql .= " AND p.`products_status` = '1' ";
		}

		// This needs to go first since it puts a bindvar in the joinSql
		array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );
		$whereSql .= ' AND pd.`language_id`=?';

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$this->getGatekeeperSql( $selectSql, $joinSql, $whereSql, $bindVars );
		}

//		$whereSql = preg_replace( '/^\sAND/', ' ', $whereSql );

		$pListHash['total_count'] = 0;
		$query = "select p.`products_id` AS `hash_key`, p.*, pd.`products_name`, lc.`created`, lc.`content_status_id`, uu.`user_id`, uu.`real_name`, uu.`login`, pt.* $selectSql
				  from " . TABLE_PRODUCTS . " p
				 	INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(p.`content_id`=lc.`content_id` )
				 	INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id` )
					INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON(p.`products_id`=pd.`products_id` )
				  	INNER JOIN `" . BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=lc.`user_id`)
					$joinSql
				  WHERE $whereSql ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] );
		if( $rs = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
			// if we returned fewer than the max, use size of our result set
			if( $rs->RecordCount() < $pListHash['max_records'] || $rs->RecordCount() == 1 ) {
				$pListHash['total_count'] = $rs->RecordCount();
			} else {
				$countQuery = "select COUNT( p.`products_id` )
						  from " . TABLE_PRODUCTS . " p
							INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON(p.`content_id`=lc.`content_id` )
							INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id` )
							INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON(p.`products_id`=pd.`products_id` )
							$joinSql
						  WHERE $whereSql ";
				$pListHash['total_count'] = $this->mDb->getOne( $countQuery, $bindVars );
			}

			$ret = $rs->GetAssoc();
			global $currencies;
			foreach( array_keys( $ret ) as $productId ) {
				$ret[$productId]['info_page'] = $ret[$productId]['type_handler'].'_info';
				$ret[$productId]['display_url'] = CommerceProduct::getDisplayUrl( $ret[$productId]['products_id'] );
				if( empty( $ret[$productId]['products_image'] ) ) {
					$ret[$productId]['products_image_url'] = CommerceProduct::getImageUrl( $ret[$productId]['products_id'], $pListHash['thumbnail_size'] );
				}

				if( empty( $taxRate[$ret[$productId]['products_tax_class_id']] ) ) {
					$taxRate[$ret[$productId]['products_tax_class_id']] = zen_get_tax_rate( $ret[$productId]['products_tax_class_id'] );
				}
				$ret[$productId]['products_weight_kg'] = $ret[$productId]['products_weight'] * .45359;

				$ret[$productId]['regular_price'] = $currencies->display_price( $ret[$productId]['products_price'], $taxRate[$ret[$productId]['products_tax_class_id']] );
				// zen_get_products_display_price is a query hog
				$ret[$productId]['display_price'] = CommerceProduct::getDisplayPrice( $productId );
			}
		}

		$pListHash['page'] = !empty( $pListHash['page'] ) && is_numeric( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
		$pListHash['total_pages'] = ceil( $pListHash['total_count'] / $pListHash['max_records'] );
		$pListHash['max_records'] = (count( $ret ) ? count( $ret ) : $pListHash['max_records']);
		$pListHash['offset'] = $pListHash['offset'] + 1;
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

	function isViewable() {
		return( $this->hasUpdatePermission() || $this->isAvailable() );
	}

	function isAvailable() {
		global $gBitUser;
		if( $this->isValid() ) {
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

	function isOwner() {
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

	function verify( &$pParamHash ) {
		$pParamHash['product_store'] = array(
			'products_quantity' => (!empty( $pParamHash['products_quantity'] ) && is_numeric( $pParamHash['products_quantity'] ) ? $pParamHash['products_quantity'] : 0),
			'products_type' => (!empty( $pParamHash['products_type'] ) ? $pParamHash['products_type'] : $this->getProductType()),
			'products_status' => (isset( $pParamHash['products_status'] ) ? (int)!empty( $pParamHash['products_status'] ) & (int)$this->isValid() : 0),
			'products_qty_box_status' => (int)(!empty( $pParamHash['products_qty_box_status'] )),
			'products_quantity_order_units' => (!empty( $pParamHash['products_quantity_order_units'] ) && is_numeric( $pParamHash['products_quantity_order_units'] ) ? $pParamHash['products_quantity_order_units'] : 1),
			'products_quantity_order_min' => (!empty( $pParamHash['products_quantity_order_min'] ) && is_numeric( $pParamHash['products_quantity_order_min'] ) ? $pParamHash['products_quantity_order_min'] : 1),
			'products_quantity_order_max' => (!empty( $pParamHash['products_quantity_order_max'] ) && is_numeric( $pParamHash['products_quantity_order_max'] ) ? $pParamHash['products_quantity_order_max'] : 0),
			'products_weight' => (!empty( $pParamHash['products_weight'] ) ? $pParamHash['products_weight'] : $this->getWeight()),
		);

		$checkFields = array( 
			'products_model',
			'products_manufacturers_model',
			'products_price',
			'products_commission',
			'products_cogs',
			'products_weight',
			'products_status',
			'products_virtual',
			'products_tax_class_id',
			'manufacturers_id',
			'suppliers_id',
			'products_barcode',
			'products_priced_by_attribute',
			'product_is_free',
			'product_is_call',
			'products_quantity_mixed',
			'product_is_always_free_ship',
			'products_sort_order',
			'products_discount_type',
			'products_discount_type_from',
			'products_price_sorter',
			'related_content_id',
			'purchase_group_id',
		);

		foreach( $checkFields as $key ) {
			if( !isset( $pParamHash['product_store'][$key] ) ) {
				$pParamHash['product_store'][$key] =  (!empty( $pParamHash[$key] ) ? $pParamHash[$key] : $this->getField( $key ));
			}
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
		if( !empty( $pParamHash['products_name'] ) ) {
			if( is_array( $pParamHash['products_name'] ) ) {
				$pParamHash['title'] = current( $pParamHash['products_name'] );
			} elseif( is_string( $pParamHash['products_name'] ) ) {
				$pParamHash['title'] = $pParamHash['products_name'];
			}
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
		$this->mDb->StartTrans();
		if( CommerceProduct::verify( $pParamHash ) && LibertyMime::store( $pParamHash ) ) {
			if (isset($pParamHash['pID'])) {
				$this->mProductsId = zen_db_prepare_input($pParamHash['pID']);
			}

			if( $this->isValid() ) {
				$action = 'update_product';
				$this->mDb->associateUpdate( TABLE_PRODUCTS, $pParamHash['product_store'], array( 'products_id' =>$this->mProductsId ) );
				// reset products_price_sorter for searches etc.
				zen_update_products_price_sorter( (int)$this->mProductsId );
			} else {
				$pParamHash['product_store']['content_id'] = $pParamHash['content_id'];
				$action = 'insert_product';
				$this->mDb->associateInsert( TABLE_PRODUCTS, $pParamHash['product_store'] );
				$this->mProductsId = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );
				// reset products_price_sorter for searches etc.
				zen_update_products_price_sorter( $this->mProductsId );
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
		$this->mDb->CompleteTrans();
		$this->load();
		return( $this->mProductsId );
	}

	function storeProductImage( $pParamHash ) {
		if( $this->isValid() ) {
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
				$fileHash['dest_path']		= str_replace( BIT_ROOT_URL, '', STORAGE_PKG_URL).'/'.BITCOMMERCE_PKG_NAME.'/'.($this->mProductsId % 1000).'/'.$this->mProductsId.'/';
				mkdir_p( BIT_ROOT_PATH.$fileHash['dest_path'] );
				$fileHash['dest_base_name']	= 'original';
				$fileHash['max_height']		= 1024;
				$fileHash['max_width']		= 1280;
				$fileHash['type'] = $gBitSystem->verifyMimeType( $fileHash['source_file'] );
				liberty_process_image( $fileHash );
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

	function getWeight() {
		return $this->getField( 'products_weight' );
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
			$pProductsId = $this->mProductsId;
		}

		if( PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == 'true' ) {
			// don't include READONLY attributes to determin if attributes must be selected to add to cart
			$query = "select pa.`products_options_values_id`
						from  " . TABLE_PRODUCTS_OPTIONS_MAP . " pom 
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

		$attributes = $this->mDb->getOne($query, array( $pProductsId) );

		return( $attributes > 0 );
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

			$discount_type = zen_get_products_sale_discount_type( $this->mProductsId );
			$discount_amount = zen_get_discount_calc( $this->mProductsId );
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
				foreach ( array_keys( $this->mOptions[$optionsId]['values'] ) as $valId ) {
					$vals = &$this->mOptions[$optionsId]['values'][$valId];
					if( empty( $vals['attributes_html_attrib'] ) ) {
						$vals['attributes_html_attrib'] = '';
					}
					// reset
					$new_value_price= '';
					$price_onetime = '';

					$products_options_array[] = array('id' => $vals['products_options_values_id'],
														'text' => $vals['products_options_values_name']);

					if (((CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == '') or (STORE_STATUS == '1')) or (CUSTOMERS_APPROVAL_AUTHORIZATION >= 2 and $_SESSION['customers_authorization'] == '')) {
						$new_options_values_price = 0;
					} else {
						// collect price information if it exists
						if( $vals['attributes_discounted'] == 1 ) {
							// apply product discount to attributes if discount is on
							//			  $new_value_price = $vals['options_values_price'];
							$new_value_price = zen_get_attributes_price_final( $this->mProductsId, $vals["products_options_values_id"], 1, '', 'false' );
							$new_value_price = zen_get_discount_calc( $this->mProductsId, true, $new_value_price);
						} else {
							// discount is off do not apply
							$new_value_price = $vals['options_values_price'];
						}

						// reverse negative values for display
						if ($new_value_price < 0) {
							$new_value_price = -$new_value_price;
							$vals['price_prefix'] = '-';
						}

						$vals['value_price'] = (float)($vals['price_prefix'].$new_value_price);

						$price_onetime = '';
						if( $vals['attributes_price_onetime'] != 0 || $vals['attributes_pf_onetime'] != 0) {
							$productSettings['show_onetime_charges_description'] = 'true';
							$price_onetime = ' '. $currencies->display_price( zen_get_attributes_price_final_onetime( $this->mProductsId, $vals["products_options_values_id"], 1, ''), zen_get_tax_rate($this->mInfo['products_tax_class_id']));
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
							$vals['display_price'] =  TEXT_ATTRIBUTES_PRICE_WAS . $vals['price_prefix'] . $currencies->display_price($new_value_price, zen_get_tax_rate($this->mInfo['products_tax_class_id'])) . TEXT_ATTRIBUTE_IS_FREE;
						} else {
							// normal price
							if ($new_value_price == 0) {
								$vals['display_price'] = '';
							} else {
								$vals['display_price'] = $vals['price_prefix'] . $currencies->display_price($new_value_price, zen_get_tax_rate( $this->mInfo['products_tax_class_id'] ) );
							}
						}

						if( !empty( $vals['display_price']  ) ) {
							$vals['display_price'] = '( '.$vals['display_price'].($price_onetime ? ' '.tra('Per Item').', '.$price_onetime.' '.tra( 'One time' ) : '').' )';
						} elseif( $price_onetime ) {
							$vals['display_price'] = $price_onetime;
						}
					} // approve
					$products_options_array[sizeof($products_options_array)-1]['text'] .= $vals['display_price'];

			// collect weight information if it exists
					if ((SHOW_PRODUCT_INFO_WEIGHT_ATTRIBUTES=='1' && !empty( $vals['products_attributes_wt'] ) )) {
						$products_options_display_weight = ' (' . $vals['products_attributes_wt_pfix'] . round( $vals['products_attributes_wt'], 2 )  . 'lbs / '.round($vals['products_attributes_wt']*0.4536,2).'kg)';
						$products_options_array[sizeof($products_options_array)-1]['text'] .= $products_options_display_weight;
					} else {
						// reset
						$products_options_display_weight='';
					}

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
						$tmp_radio .= '<div class="productoptions">' . 
									  zen_draw_radio_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']', $products_options_value_id, $selected_attribute) . 
									  "<span class='title'>$vals[products_options_values_name]</span> <span class='details'>$products_options_details_noname</span>";
						if( !empty( $vals['attributes_image'] ) ) {
							$tmp_radio .= zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', '');
						}
						$tmp_radio .= '</div>';
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
							$tmp_checkbox .= zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . (!empty( $vals['attributes_image'] ) ? zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', 'hspace="5" vspace="5"') . '&nbsp;' : '') . $products_options_details . '<br />';
							break;
						  case '2':
							$tmp_checkbox .= zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . $products_options_details .  (!empty( $vals['attributes_image'] ) ? '<br />' . zen_image(DIR_WS_IMAGES . $vals['attributes_image'], '', '', '', 'hspace="5" vspace="5"') : '') . '<br />';
							break;
						  case '3':
							$tmp_attributes_image_row++;

							if ($tmp_attributes_image_row > $this->mOptions[$optionsId]['products_options_images_per_row']) {
								$tmp_attributes_image .= '</tr><tr>';
								$tmp_attributes_image_row = 1;
							}

							if( !empty( $vals['attributes_image'] ) ) {
								$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . $products_options_details_noname . '</td>';
							} else {
								$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . '<br />' . $vals['products_options_values_name'] . $products_options_details_noname . '</td>';
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
															. (!empty( $products_options_details_noname )  ? '<br />' . $products_options_details_noname : '')
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
								$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . '<br />' . zen_image(DIR_WS_IMAGES . $vals['attributes_image']) . (PRODUCT_IMAGES_ATTRIBUTES_NAMES == '1' ? '<br />' . $vals['products_options_values_name'] : '') . (!empty( $products_options_details_noname ) ? '<br />' . $products_options_details_noname : '') . '</td>';
							} else {
								$tmp_attributes_image .= '<td class="smallText" align="center" valign="top">' . zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . '<br />' . $vals['products_options_values_name'] . ($products_options_details_noname != '' ? '<br />' . $products_options_details_noname : '') . '</td>';
							}
							break;
						  case '0':
						  default:
							$tmp_checkbox .= zen_draw_checkbox_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']['.$products_options_value_id.']', $products_options_value_id, $selected_attribute, $vals['attributes_html_attrib'] ) . $products_options_details .'<br />';
							break;
						}
					}




					// =-=-=-=-=-=-=-=-=-=-= text

					if (($this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_TEXT)) {
						if ( !empty( $pSelectedId ) ) {
							reset($pSelectedId);
							while(list($key,$value) = each($pSelectedId)) {
			//echo ereg_replace('txt_', '', $key) . '#';
			//print_r($pSelectedId);
			//echo $this->mOptions[$optionsId]['products_options_id'].'|';
			//echo $value.'|';
			//echo $vals['products_options_values_id'].'#';
								if ((ereg_replace('txt_', '', $key) == $this->mOptions[$optionsId]['products_options_id'])) {
									$tmp_html = '<input type="text" name ="id[' . TEXT_PREFIX . $this->mOptions[$optionsId]['products_options_id'] . ']" size="' . $this->mOptions[$optionsId]['products_options_size'] .'" maxlength="' . $this->mOptions[$optionsId]['products_options_length'] . '" value="' . stripslashes($value) .'" />  ';
									$tmp_html .= $products_options_details;
									break;
								}
							}

						} elseif( is_object( $pCart ) ) {
							$tmp_value = $pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']];
							$tmp_html = '<input type="text" name ="id[' . TEXT_PREFIX . $this->mOptions[$optionsId]['products_options_id'] . ']" size="' . $this->mOptions[$optionsId]['products_options_size'] .'" maxlength="' . $this->mOptions[$optionsId]['products_options_length'] . '" value="' . htmlspecialchars($tmp_value) .'" />  ';
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

						}
					}




					// =-=-=-=-=-=-=-=-=-=-= file uploads

					if( is_object( $pCart ) && $this->mOptions[$optionsId]['products_options_type'] == PRODUCTS_OPTIONS_TYPE_FILE) {
						$number_of_uploads++;
						$tmp_html = '<input type="file" name="id[' . TEXT_PREFIX . $this->mOptions[$optionsId]['products_options_id'] . ']" /><br />' .
									$pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']] .
									zen_draw_hidden_field(UPLOAD_PREFIX . $number_of_uploads, $this->mOptions[$optionsId]['products_options_id']) .
									zen_draw_hidden_field(TEXT_PREFIX . UPLOAD_PREFIX . $number_of_uploads, $pCart->contents[$this->mProductsId]['attributes_values'][$this->mOptions[$optionsId]['products_options_id']]);
						$tmp_html  .= $products_options_details;
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
						$productOptions[$optionsId]['menu'] = $tmp_checkbox;
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
					case ( count( $this->mOptions[$optionsId] ) == 1):
						if ($productSettings['show_attributes_qty_prices_icon'] == 'true') {
							$productOptions[$optionsId]['name'] = ATTRIBUTES_QTY_PRICE_SYMBOL . $this->mOptions[$optionsId]['products_options_name'];
						} else {
							$productOptions[$optionsId]['name'] = $this->mOptions[$optionsId]['products_options_name'];
						}
						$productOptions[$optionsId]['menu'] = zen_draw_radio_field('id[' . $this->mOptions[$optionsId]['products_options_id'] . ']', $products_options_value_id, 'selected') . $products_options_details;
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

	function getDiscount( $pQuantity, $pDiscount ) {
		$ret = NULL;
		if( empty( $this->mDiscounts ) ) {
			$this->loadDiscounts();
		}
		if( !empty( $this->mDiscounts[$pQuantity][$pDiscount] ) ) {
			$ret = $this->mDiscounts[$pQuantity][$pDiscount];
		}
		return $ret;
	}


	function compareDiscount( &$pParamHash, $pDiscount ) {
		$currentDiscount = $this->getDiscount( $pParamHash['discount_qty'], $pDiscount );

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
			$pParamHash['discounts_store']['discount_id'] = !empty( $pParamHash['discount_id'] ) ? $pParamHash['discount_id'] : $this->getDiscount( $pParamHash['discount_qty'], 'discount_id' );

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

	function loadDiscounts() {
		$this->mDiscounts = array();
		if( $this->isValid() ) {
			$this->mDiscounts = $this->mDb->getAssoc( "SELECT pdq.`discount_qty` AS `hash_key`, pdq.* FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " pdq WHERE `products_id` = ? ORDER BY `discount_qty`", array( $this->mProductsId ) );
		}
		return( count( $this->mDiscounts ) );
	}

	////
	// Display Price Retail
	// Specials and Tax Included
	function expunge() {
		global $gBitSystem;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
/*
Skip deleting of images for now
			if( !empty( $this->mInfo['products_image'] ) ) {
				$duplicate_image = $this->mDb->GetOne("SELECT count(*) as `total`
								 FROM " . TABLE_PRODUCTS . "
								 WHERE `products_image` = ?", array( $this->mInfo['products_image'] ) );
				if ($duplicate_image < 2 ) {
					$products_image = $product_image->fields['products_image'];
					$products_image_extention = substr($products_image, strrpos($products_image, '.'));
					$products_image_base = ereg_replace($products_image_extention, '', $products_image);

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
			$this->mDb->query("delete FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE `products_id` = ?", array( $this->mProductsId ) );
			$this->mDb->query("delete FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE `products_id` = ?", array( $this->mProductsId ));

			// remove downloads if they exist
			$remove_downloads= $this->mDb->query(  
				"SELECT pa.`products_attributes_id`
				 FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
					INNER JOIN " . TABLE_PRODUCTS_OPTIONS_MAP . " pom ON( pa.`products_options_values_id`=pom.`products_options_values_id` )  
				 WHERE pom.`products_id` = ?", array( $this->mProductsId ) );
			while (!$remove_downloads->EOF) {
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE `products_attributes_id` =?", array( $remove_downloads->fields['products_attributes_id'] ) );
				$remove_downloads->MoveNext();
			}

			$this->mDb->query("delete FROM " . TABLE_PRODUCTS_OPTIONS_MAP . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("delete FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("delete FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE `products_id` = ?", array( $this->mProductsId ));

			$product_reviews = $this->mDb->query("SELECT `reviews_id` FROM " . TABLE_REVIEWS . " WHERE `products_id` = ?", array( $this->mProductsId ));
			while (!$product_reviews->EOF) {
				$this->mDb->query("delete FROM " . TABLE_REVIEWS_DESCRIPTION . "
							WHERE `reviews_id` = ?", array( $product_reviews->fields['reviews_id'] ) );
				$product_reviews->MoveNext();
			}

			$this->mDb->query("delete FROM " . TABLE_REVIEWS . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("delete FROM " . TABLE_FEATURED . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("delete FROM " . TABLE_SPECIALS . " WHERE `products_id` = ?", array( $this->mProductsId ));
			$this->mDb->query("delete FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE `products_id` = ?", array( $this->mProductsId ));
			if( !$this->isPurchased() ) {
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE `products_id` = ?", array( $this->mProductsId ));
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS . " WHERE `products_id` = ?", array( $this->mProductsId ));
				LibertyMime::expunge();
			} else {
				$this->update( array( 'related_content_id' => NULL ) );
				$this->storeStatus( $gBitSystem->getConfig( 'liberty_status_deleted', -999 ) );
			}


			$this->mInfo = array();
			unset( $this->mRelatedContent );
			unset( $this->mProductsId );

			$this->mDb->CompleteTrans();
		}
		return( count( $this->mErrors ) == 0 );
	}

	function quantityInCart( $pProductsId = NULL ) {
		if( empty( $pProductsId ) && !empty( $this->mProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		return $_SESSION['cart']->get_quantity( $pProductsId );
	}

	////
	// Return quantity buy now
	function getBuyNowQuantity( $pProductsId = NULL) {
		global $cart;
		if( empty( $pProductsId ) && !empty( $this->mProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}

		$check_min = zen_get_products_quantity_order_min( $pProductsId );
		$check_units = zen_get_products_quantity_order_units( $pProductsId );
		$buy_now_qty=1;
	// works on Mixed ON
		switch (true) {
		case ($_SESSION['cart']->in_cart_mixed($pProductsId) == 0 ):
			if ($check_min >= $check_units) {
			$buy_now_qty = $check_min;
			} else {
			$buy_now_qty = $check_units;
			}
			break;
		case ($_SESSION['cart']->in_cart_mixed($pProductsId) < $check_min):
			$buy_now_qty = $check_min - $_SESSION['cart']->in_cart_mixed($pProductsId);
			break;
		case ($_SESSION['cart']->in_cart_mixed($pProductsId) > $check_min):
		// set to units or difference in units to balance cart
			$new_units = $check_units - fmod($_SESSION['cart']->in_cart_mixed($pProductsId), $check_units);
	//echo 'Cart: ' . $_SESSION['cart']->in_cart_mixed($pProductsId) . ' Min: ' . $check_min . ' Units: ' . $check_units . ' fmod: ' . fmod($_SESSION['cart']->in_cart_mixed($pProductsId), $check_units) . '<br />';
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
		if( $this->isValid() && $this->verifyId( $pOptionsValuesId ) ) {
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

	function isFree() {
		return( !empty( $this->mInfo['product_is_free'] ) );
	}

}


/**
 * return a proper Commerce object based on the product_types.type_class
 *
 * @param mixed If an integer, a product_id is assumed, else a key=>value hash (e.g. content_id=>1234 ) to lookup the product
 * See verify for details of the values required
 */
function bc_get_commerce_product( $pLookupMixed ) {
	global $gBitDb;
	$product = NULL;

	if( is_array( $pLookupMixed ) && count( $pLookupMixed ) == 1 ) {
		$lookupKey = key( $pLookupMixed );
		$lookupValue = current( $pLookupMixed );
	} elseif( is_numeric( $pLookupMixed ) ) {
		$lookupKey = 'products_id';
		$lookupValue = $pLookupMixed;
	}

	if( !empty( $lookupValue ) ) {

		$sql = "SELECT `type_id` AS `hash_key`, cpt.* 
				FROM " . TABLE_PRODUCT_TYPES . " cpt INNER JOIN " . TABLE_PRODUCTS . " cp ON(cpt.`type_id`=cp.`products_type`)
				WHERE `$lookupKey`=?";
		$productTypes = $gBitDb->getRow( $sql, array( $lookupValue ) );

		if( !empty( $productTypes['type_class'] ) && !empty( $productTypes['type_class_file'] ) ) {
			require_once( BIT_ROOT_PATH.$productTypes['type_class_file'] );
			if( class_exists(  $productTypes['type_class'] ) ) {
				$productClass = $productTypes['type_class']; 
			}
		}
	}

	if( empty( $productClass ) ) {
		$productClass = 'CommerceProduct';
	}

	$productsId = ( $lookupKey == 'products_id' ) ? $lookupValue : NULL;
	$contentId = ( $lookupKey == 'content_id' ) ? $lookupValue : NULL;

	$product = new $productClass( $productsId, $contentId );

	if( !$product->load() ) {	
		unset( $product->mProductsId );
	}

	return $product;
}

?>

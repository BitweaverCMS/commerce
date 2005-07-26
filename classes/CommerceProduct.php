<?php


class CommerceProduct extends BitBase {
	function CommerceProduct( $pProductId=NULL ) {
		BitBase::BitBase();
		$this->mProductsId = $pProductId;
	}

	function load() {
		if( is_numeric( $this->mProductsId ) ) {
			$this->mInfo = $this->getProduct( $this->mProductsId );
		}
		return( count( $this->mInfo ) );
	}

	function loadByRelatedContent( $pContentId ) {
		if( is_numeric( $pContentId ) ) {
			if( $this->mProductsId = $this->getOne( "SELECT `products_id` FROM " . TABLE_PRODUCTS . " WHERE `related_content_id`=?", array( $pContentId ) ) ) {
				$this->load();
			}
		}
		return( $this->isValid() );
	}

	function getProduct( $pProductId ) {
		global $db;
		$ret = NULL;
		if( is_numeric( $pProductId ) ) {
			$bindVars = array( $pProductId );
			array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );
			$query = "SELECT *
					  FROM " . TABLE_PRODUCTS . " p
					  	INNER JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON (p.`products_id`=pd.`products_id`)
					  	INNER JOIN ".TABLE_PRODUCT_TYPES." pt ON (p.`products_type`=pt.`type_id`)
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( p.`related_content_id`=tc.`content_id`)
					  WHERE p.`products_id`=? AND pd.`language_id`=?";
			$ret = $db->getRow( $query, $bindVars );
			if( !empty( $ret['products_image'] ) ) {
				$ret['products_image_url'] = CommerceProduct::getImageUrl( $ret['products_image'] );
			} else {
				$ret['products_image_url'] = NULL;
			}
		}
		return $ret;
	}

	function getTitle() {
		if( $this->isValid() ) {
			return( $this->mInfo['products_name'] );
		}
	}

	function getDisplayUrl() {
		$ret = NULL;
		if( $this->isValid() ) {
			$ret = BITCOMMERCE_PKG_URL.'index.php?main_page='.$this->mInfo['type_handler'].'_info&products_id='.$this->mProductsId;
		}
		return $ret;
	}

	function getImageUrl( $pMixed=NULL, $pSize='medium' ) {
		if( empty( $pMixed ) && !empty( $this->mProductsId ) ) {
			$pMixed = $this->mProductsId;
		}

		if( is_numeric( $pMixed ) ) {
			$path = ($pMixed % 1000).'/'.$pMixed.'/'.$pSize.'.jpg';
			$ret = STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/'.$path;
		} else {
			$ret = STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/images/'.$pMixed;
		}
		return $ret;
	}

	function getList( &$pListHash ) {
		global $db;
		BitBase::prepGetList( $pListHash );
		$bindVars = array();
		if ( !empty( $pListHash['category_id'] ) ) {
			if( !is_numeric( $pListHash['category_id'] ) && strpos( $pListHash['category_id'], '_' ) ) {
				$path = split( '_', $pListHash['category_id'] );
				end( $path );
				$pListHash['category_id'] = current( $path );
			}
			if( is_numeric( $pListHash['category_id'] ) ) {
				$fromSql = " LEFT JOIN " . TABLE_SPECIALS . " s ON p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c ";
				$whereSql = " AND p.products_id = p2c.products_id AND p2c.categories_id = c.categories_id AND c.parent_id=? ";
				array_push( $bindVars, $pListHash['category_id'] );
			}
		}
		$query = "select p.products_id AS hash_key, p.products_id, p.products_image, p.products_tax_class_id, p.products_price, p.products_date_added
				  from " . TABLE_PRODUCTS . " p $fromSql
				  where p.products_status = '1' $whereSql ORDER BY ".$db->convert_sortmode( $pListHash['sort_mode'] );
		if( $ret = $db->getAssoc( $query, $bindVars ) ) {
			foreach( array_keys( $ret ) as $productId ) {
				if( !empty( $ret[$productId]['products_image'] ) ) {
					$ret[$productId]['products_image_url'] = CommerceProduct::getImageUrl( $ret[$productId]['products_image'] );
				}
			}
		}
		return( $ret );
	}

	function isValid() {
		return( !empty( $this->mProductsId ) );
	}

	function verify( &$pParamHash ) {
		$pParamHash['product_store'] = array(
			'products_quantity' => (!empty( $pParamHash['products_quantity'] ) && is_numeric( $pParamHash['products_quantity'] ) ? $pParamHash['products_quantity'] : 0),
			'products_type' => (!empty( $pParamHash['products_type'] ) ? $pParamHash['products_type'] : 1),
			'products_model' => (!empty( $pParamHash['products_model'] ) ? $pParamHash['products_model'] : NULL),
			'products_price' => (!empty( $pParamHash['products_price'] ) ? $pParamHash['products_price'] : NULL),
			'products_weight' => (!empty( $pParamHash['products_weight'] ) ? $pParamHash['products_weight'] : NULL),
			'products_status' => (!empty( $pParamHash['products_status'] ) ? $pParamHash['products_status'] : NULL),
			'products_virtual' => (!empty( $pParamHash['products_virtual'] ) ? $pParamHash['products_virtual'] : NULL),
			'products_tax_class_id' => (!empty( $pParamHash['products_tax_class_id'] ) ? $pParamHash['products_tax_class_id'] : NULL),
			'manufacturers_id' => (!empty( $pParamHash['manufacturers_id'] ) ? $pParamHash['manufacturers_id'] : NULL),
			'products_priced_by_attribute' => (!empty( $pParamHash['products_priced_by_attribute'] ) ? $pParamHash['products_priced_by_attribute'] : NULL),
			'product_is_free' => (!empty( $pParamHash['product_is_free'] ) ? $pParamHash['product_is_free'] : NULL),
			'product_is_call' => (!empty( $pParamHash['product_is_call'] ) ? $pParamHash['product_is_call'] : NULL),
			'products_quantity_mixed' => (!empty( $pParamHash['products_quantity_mixed'] ) ? $pParamHash['products_quantity_mixed'] : NULL),
			'product_is_always_free_ship' => (!empty( $pParamHash['product_is_always_free_ship'] ) ? $pParamHash['product_is_always_free_ship'] : NULL),
			'products_sort_order' => (!empty( $pParamHash['products_sort_order'] ) ? $pParamHash['products_sort_order'] : NULL),
			'products_discount_type' => (!empty( $pParamHash['products_discount_type'] ) ? $pParamHash['products_discount_type'] : NULL),
			'products_discount_type_from' => (!empty( $pParamHash['products_discount_type_from'] ) ? $pParamHash['products_discount_type_from'] : NULL),
			'products_price_sorter' => (!empty( $pParamHash['products_price_sorter'] ) ? $pParamHash['products_price_sorter'] : NULL),
			'related_content_id' => (!empty( $pParamHash['related_content_id'] ) ? $pParamHash['related_content_id'] : NULL),
			'products_qty_box_status' => (!empty( $pParamHash['products_qty_box_status'] ) && is_numeric( $pParamHash['products_qty_box_status'] ) ? $pParamHash['products_qty_box_status'] : 1),
			'products_quantity_order_units' => (!empty( $pParamHash['products_quantity_order_units'] ) && is_numeric( $pParamHash['products_quantity_order_units'] ) ? $pParamHash['products_quantity_order_units'] : 1),
			'products_quantity_order_min' => (!empty( $pParamHash['products_quantity_order_min'] ) && is_numeric( $pParamHash['products_quantity_order_min'] ) ? $pParamHash['products_quantity_order_min'] : 1),
			'products_quantity_order_max' => (!empty( $pParamHash['products_quantity_order_max'] ) && is_numeric( $pParamHash['products_quantity_order_max'] ) ? $pParamHash['products_quantity_order_max'] : 0),
			);

		if (isset($pParamHash['products_image']) && zen_not_null($pParamHash['products_image']) && (!is_numeric(strpos($pParamHash['products_image'],'none'))) ) {
			$pParamHash['product_store']['products_image'] = zen_db_prepare_input($pParamHash['products_image']);
		} else {
			$pParamHash['product_store']['products_image'] = '';
		}

		if( !empty( $pParamHash['products_date_available'] ) ) {
			$pParamHash['products_date_available'] = (date('Y-m-d') < $pParamHash['products_date_available']) ? $pParamHash['products_date_available'] : 'now()';
		} else {
			$pParamHash['products_date_available'] = NULL;
		}

		$pParamHash['product_store']['products_last_modified'] = 'now()';
		$pParamHash['product_store']['master_categories_id'] = (!empty( $pParamHash['category_id'] ) ? $pParamHash['category_id'] : NULL );
		if( $this->isValid() ) {
			$pParamHash['product_store']['master_categories_id'] = ($pParamHash['master_category'] > 0 ? $pParamHash['master_category'] : $pParamHash['master_categories_id'] );
		} else {
			$pParamHash['product_store']['products_date_added'] = 'now()';
		}

		return( TRUE );
	}

	function store( &$pParamHash ) {
		$this->StartTrans();
		if( $this->verify( $pParamHash ) ) {
			if (isset($pParamHash['pID'])) {
				$this->mProductsId = zen_db_prepare_input($pParamHash['pID']);
			}


	// when set to none remove from database
	//          if (isset($pParamHash['products_image']) && zen_not_null($pParamHash['products_image']) && ($pParamHash['products_image'] != 'none')) {
			if( $this->isValid() ) {
				$action = 'update_product';
				$this->associateUpdate( TABLE_PRODUCTS, $pParamHash['product_store'], array( 'name'=>'products_id', 'value'=>$this->mProductsId ) );
				// reset products_price_sorter for searches etc.
				zen_update_products_price_sorter( (int)$this->mProductsId );
			} else {
				$action = 'insert_product';
				$this->associateInsert( TABLE_PRODUCTS, $pParamHash['product_store'] );
				$this->mProductsId = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );
				// reset products_price_sorter for searches etc.
				zen_update_products_price_sorter( $this->mProductsId );
				$this->query( "insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " ( `products_id`, `categories_id` ) values (?,?)", array( $this->mProductsId, (int)$pParamHash['category_id'] ) );
			}

			$languages = zen_get_languages();
			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$language_id = $languages[$i]['id'];

				$sql_data_array = array('products_name' => zen_db_prepare_input($pParamHash['products_name'][$language_id]),
										'products_description' => zen_db_prepare_input($pParamHash['products_description'][$language_id]),
										'products_url' => zen_db_prepare_input($pParamHash['products_url'][$language_id]));

				if ($action == 'insert_product') {
					$insert_sql_data = array('products_id' => $this->mProductsId, 'language_id' => $language_id);
					$sql_data_array = array_merge( $sql_data_array, $insert_sql_data );
					$this->associateInsert( TABLE_PRODUCTS_DESCRIPTION, $sql_data_array );
				} elseif ($action == 'update_product') {
					$this->query( "UPDATE " . TABLE_PRODUCTS_DESCRIPTION . " SET ". implode( ",", $sql_data_array) . " WHERE `products_id` =? AND `language_id`=?",  array( $this->mProductsId, $language_id ) );
				}
			}

		// add meta tags
			$languages = zen_get_languages();
			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$language_id = $languages[$i]['id'];

				$sql_data_array = array('metatags_title' => zen_db_prepare_input($pParamHash['metatags_title'][$language_id]),
										'metatags_keywords' => zen_db_prepare_input($pParamHash['metatags_keywords'][$language_id]),
										'metatags_description' => zen_db_prepare_input($pParamHash['metatags_description'][$language_id]));

				if ($action == 'insert_product_meta_tags') {
					$insert_sql_data = array('products_id' => $this->mProductsId, 'language_id' => $language_id);
					$sql_data_array = array_merge($sql_data_array, $insert_sql_data);
					$this->associateInsert(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array);
				} elseif ($action == 'update_product_meta_tags') {
					$this->query( "UPDATE " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " SET ". implode( ",", $sql_data_array) . " WHERE `products_id` =? AND `language_id`=?",  array( $this->mProductsId, $language_id ) );
				}
			}


		// future image handler code
			if ($new_image == 'true' and IMAGE_MANAGER_HANDLER >= 1) {
		define('IMAGE_MANAGER_HANDLER', 0);
		define('DIR_IMAGEMAGICK', '');
				$src= DIR_FS_CATALOG . DIR_WS_IMAGES . zen_get_products_image($this->mProductsId);
				$filename_small= $src;
				preg_match("/.*\/(.*)\.(\w*)$/", $src, $fname);
				list($oiwidth, $oiheight, $oitype) = getimagesize($src);

				$small_width= SMALL_IMAGE_WIDTH;
				$small_height= SMALL_IMAGE_HEIGHT;
				$medium_width= MEDIUM_IMAGE_WIDTH;
				$medium_height= MEDIUM_IMAGE_HEIGHT;
				$large_width= LARGE_IMAGE_WIDTH;
				$large_height= LARGE_IMAGE_HEIGHT;

				$k = max($oiheight / $small_height, $oiwidth / $small_width); //use smallest size
				$small_width = round($oiwidth / $k);
				$small_height = round($oiheight / $k);

				$k = max($oiheight / $medium_height, $oiwidth / $medium_width); //use smallest size
				$medium_width = round($oiwidth / $k);
				$medium_height = round($oiheight / $k);

				$large_width= $oiwidth;
				$large_height= $oiheight;

				$products_image = zen_get_products_image($this->mProductsId);
				$products_image_extention = substr($products_image, strrpos($products_image, '.'));
				$products_image_base = ereg_replace($products_image_extention, '', $products_image);

				$filename_medium = DIR_FS_CATALOG . DIR_WS_IMAGES . 'medium/' . $products_image_base . IMAGE_SUFFIX_MEDIUM . '.' . $fname[2];
				$filename_large = DIR_FS_CATALOG . DIR_WS_IMAGES . 'large/' . $products_image_base . IMAGE_SUFFIX_LARGE . '.' . $fname[2];

		// ImageMagick
				if (IMAGE_MANAGER_HANDLER == '1') {
					copy($src, $filename_large);
					copy($src, $filename_medium);
					exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $large_width . " " . $filename_large);
					exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $medium_width . " " . $filename_medium);
					exec(DIR_IMAGEMAGICK . "mogrify -geometry " . $small_width . " " . $filename_small);
				}
			}
			$this->CompleteTrans();
		}
		return( $this->mProductsId );
	}

	function quantityInCart( $pProductId = NULL ) {
		if( empty( $pProductId ) && !empty( $this->mProductsId ) ) {
			$pProductId = $this->mProductsId;
		}
		return $_SESSION['cart']->get_quantity( $pProductId );
	}

	////
	// Return quantity buy now
	function getBuyNowQuantity( $pProductId = NULL) {
		global $cart;
		if( empty( $pProductId ) && !empty( $this->mProductsId ) ) {
			$pProductId = $this->mProductsId;
		}
		$check_min = zen_get_products_quantity_order_min( $pProductId );
		$check_units = zen_get_products_quantity_order_units( $pProductId );
		$buy_now_qty=1;
	// works on Mixed ON
		switch (true) {
		case ($_SESSION['cart']->in_cart_mixed($pProductId) == 0 ):
			if ($check_min >= $check_units) {
			$buy_now_qty = $check_min;
			} else {
			$buy_now_qty = $check_units;
			}
			break;
		case ($_SESSION['cart']->in_cart_mixed($pProductId) < $check_min):
			$buy_now_qty = $check_min - $_SESSION['cart']->in_cart_mixed($pProductId);
			break;
		case ($_SESSION['cart']->in_cart_mixed($pProductId) > $check_min):
		// set to units or difference in units to balance cart
			$new_units = $check_units - fmod($_SESSION['cart']->in_cart_mixed($pProductId), $check_units);
	//echo 'Cart: ' . $_SESSION['cart']->in_cart_mixed($pProductId) . ' Min: ' . $check_min . ' Units: ' . $check_units . ' fmod: ' . fmod($_SESSION['cart']->in_cart_mixed($pProductId), $check_units) . '<br />';
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
	function getQuantityMinUnitsDisplay($pProductId = NULL, $include_break = true, $shopping_cart_msg = false) {
		if( empty( $pProductId ) && !empty( $this->mProductsId ) ) {
			$pProductId = $this->mProductsId;
		}
		$check_min = zen_get_products_quantity_order_min($pProductId);
		$check_units = zen_get_products_quantity_order_units($pProductId);

		$the_min_units='';

		if ($check_min != 1 or $check_units != 1) {
			if ($check_min != 1) {
				$the_min_units .= PRODUCTS_QUANTITY_MIN_TEXT_LISTING . '&nbsp;' . $check_min;
			}
			if ($check_units != 1) {
				$the_min_units .= ($the_min_units ? ' ' : '' ) . PRODUCTS_QUANTITY_UNIT_TEXT_LISTING . '&nbsp;' . $check_units;
			}

			if (($check_min > 0 or $check_units > 0) and !zen_get_products_quantity_mixed($pProductId)) {
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
		$check_max = zen_get_products_quantity_order_max($pProductId);

		if ($check_max != 0) {
			if ($include_break == true) {
				$the_min_units .= ($the_min_units != '' ? '<br />' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
			} else {
				$the_min_units .= ($the_min_units != '' ? '&nbsp;&nbsp;' : '') . PRODUCTS_QUANTITY_MAX_TEXT_LISTING . '&nbsp;' . $check_max;
			}
		}

		return $the_min_units;
	}


	////
	// Display Price Retail
	// Specials and Tax Included
	function getDisplayPrice( $pProductId=NULL ) {
		global $db, $currencies;

		if( empty( $pProductId ) && !empty( $this->mProductsId ) ) {
			$pProductId = $this->mProductsId;
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

		// $new_fields = ', product_is_free, product_is_call, product_is_showroom_only';
		$product_check = $db->Execute("select products_tax_class_id, products_price, products_priced_by_attribute, product_is_free, product_is_call from " . TABLE_PRODUCTS . " where products_id = '" . (int)$pProductId . "'" . " limit 1");

		$show_display_price = '';
		$display_normal_price = zen_get_products_base_price($pProductId);
		$display_special_price = zen_get_products_special_price($pProductId, true);
		$display_sale_price = zen_get_products_special_price($pProductId, false);

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
			$show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . $currencies->display_price(($display_normal_price - $display_sale_price), zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . PRODUCT_PRICE_DISCOUNT_AMOUNT . '</span>';
			}
		} else {
			if (SHOW_SALE_DISCOUNT == 1) {
			$show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . number_format(100 - (($display_special_price / $display_normal_price) * 100),SHOW_SALE_DISCOUNT_DECIMALS) . PRODUCT_PRICE_DISCOUNT_PERCENTAGE . '</span>';
			} else {
			$show_sale_discount = '<span class="productPriceDiscount">' . '<br />' . PRODUCT_PRICE_DISCOUNT_PREFIX . $currencies->display_price(($display_normal_price - $display_special_price), zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . PRODUCT_PRICE_DISCOUNT_AMOUNT . '</span>';
			}
		}
		}

		if ($display_special_price) {
		$show_normal_price = '<span class="normalprice">' . $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . ' </span>';
		if ($display_sale_price && $display_sale_price != $display_special_price) {
			$show_special_price = '&nbsp;' . '<span class="productSpecialPriceSale">' . $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
			if ($product_check->fields['product_is_free'] == '1') {
			$show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . '<s>' . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</s>' . '</span>';
			} else {
			$show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
			}
		} else {
			if ($product_check->fields['product_is_free'] == '1') {
			$show_special_price = '&nbsp;' . '<span class="productSpecialPrice">' . '<s>' . $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</s>' . '</span>';
			} else {
			$show_special_price = '&nbsp;' . '<span class="productSpecialPrice">' . $currencies->display_price($display_special_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
			}
			$show_sale_price = '';
		}
		} else {
		if ($display_sale_price) {
			$show_normal_price = '<span class="normalprice">' . $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . ' </span>';
			$show_special_price = '';
			$show_sale_price = '<br />' . '<span class="productSalePrice">' . PRODUCT_PRICE_SALE . $currencies->display_price($display_sale_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</span>';
		} else {
			if ($product_check->fields['product_is_free'] == '1') {
			$show_normal_price = '<s>' . $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id'])) . '</s>';
			} else {
			$show_normal_price = $currencies->display_price($display_normal_price, zen_get_tax_rate($product_check->fields['products_tax_class_id']));
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
		if ($product_check->fields['product_is_free'] == '1') {
		if (OTHER_IMAGE_PRICE_IS_FREE_ON=='0') {
			$free_tag = '<br />' . PRODUCTS_PRICE_IS_FREE_TEXT;
		} else {
			$free_tag = '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_PRICE_IS_FREE, PRODUCTS_PRICE_IS_FREE_TEXT);
		}
		}

		// If Call for Price, Show it
		if ($product_check->fields['product_is_call']) {
		if (PRODUCTS_PRICE_IS_CALL_IMAGE_ON=='0') {
			$call_tag = '<br />' . PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT;
		} else {
			$call_tag = '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_CALL_FOR_PRICE, PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT);
		}
	}

	return $final_display_price . $free_tag . $call_tag;
}




}


?>
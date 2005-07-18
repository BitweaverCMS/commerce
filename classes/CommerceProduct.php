<?php

class CommerceProduct extends BitBase {
	function CommerceProduct() {
		BitBase::BitBase();

	}

	function load( $pProductId ) {
		if( is_numeric( $pProductId ) ) {
			$this->mInfo = $this->getProduct( $pProductId );
			$this->mProductsId = $pProductId;
		}
		return( count( $this->mInfo ) );
	}

	function getProduct( $pProductId ) {
		global $db;
		$ret = NULL;
		if( is_numeric( $pProductId ) ) {
			$bindVars = array( $pProductId );
			array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );
			$query = "SELECT * FROM " . TABLE_PRODUCTS . " p INNER JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON (p.`products_id`=pd.`products_id`)
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

	function getImageUrl( $pFileName ) {
		return STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/images/'.$pFileName;
	}

	function isValid() {
		return( !empty( $this->mProductsId ) );
	}

	function getList( &$pListHash ) {
		global $db;
		BitBase::prepGetList( $pListHash );
		$bindVars = array();
		if ( !empty( $pListHash['category_id'] ) ) {
			$fromSql = " LEFT JOIN " . TABLE_SPECIALS . " s ON p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c ";
			$whereSql = " AND p.products_id = p2c.products_id AND p2c.categories_id = c.categories_id AND c.parent_id=? ";
			array_push( $bindVars, $pListHash['category_id'] );
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

	function store( &$pParamHash ) {
		global $db;
		$db->StartTrans();
		if (isset($pParamHash['pID'])) {
			$this->mProductsId = zen_db_prepare_input($pParamHash['pID']);
		}
		$products_date_available = zen_db_prepare_input($pParamHash['products_date_available']);

		$products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'now()';

		$sql_data_array = array('products_quantity' => (int)zen_db_prepare_input($pParamHash['products_quantity']),
								'products_type' => zen_db_prepare_input($pParamHash['product_type']),
								'products_model' => zen_db_prepare_input($pParamHash['products_model']),
								'products_price' => zen_db_prepare_input($pParamHash['products_price']),
								'products_date_available' => $products_date_available,
								'products_weight' => zen_db_prepare_input($pParamHash['products_weight']),
								'products_status' => zen_db_prepare_input($pParamHash['products_status']),
								'products_virtual' => zen_db_prepare_input($pParamHash['products_virtual']),
								'products_tax_class_id' => zen_db_prepare_input($pParamHash['products_tax_class_id']),
								'manufacturers_id' => zen_db_prepare_input($pParamHash['manufacturers_id']),
								'products_quantity_order_min' => zen_db_prepare_input($pParamHash['products_quantity_order_min']),
								'products_quantity_order_units' => zen_db_prepare_input($pParamHash['products_quantity_order_units']),
								'products_priced_by_attribute' => zen_db_prepare_input($pParamHash['products_priced_by_attribute']),
								'product_is_free' => zen_db_prepare_input($pParamHash['product_is_free']),
								'product_is_call' => zen_db_prepare_input($pParamHash['product_is_call']),
								'products_quantity_mixed' => zen_db_prepare_input($pParamHash['products_quantity_mixed']),
								'product_is_always_free_ship' => zen_db_prepare_input($pParamHash['product_is_always_free_ship']),
								'products_qty_box_status' => (int)zen_db_prepare_input($pParamHash['products_qty_box_status']),
								'products_quantity_order_max' => (int)zen_db_prepare_input($pParamHash['products_quantity_order_max']),
								'products_sort_order' => zen_db_prepare_input($pParamHash['products_sort_order']),
								'products_discount_type' => zen_db_prepare_input($pParamHash['products_discount_type']),
								'products_discount_type_from' => zen_db_prepare_input($pParamHash['products_discount_type_from']),
								'products_price_sorter' => zen_db_prepare_input($pParamHash['products_price_sorter'])
								);

// when set to none remove from database
//          if (isset($pParamHash['products_image']) && zen_not_null($pParamHash['products_image']) && ($pParamHash['products_image'] != 'none')) {
		if (isset($pParamHash['products_image']) && zen_not_null($pParamHash['products_image']) && (!is_numeric(strpos($pParamHash['products_image'],'none'))) ) {
			$sql_data_array['products_image'] = zen_db_prepare_input($pParamHash['products_image']);
			$new_image= 'true';
		} else {
			$sql_data_array['products_image'] = '';
			$new_image= 'false';
		}

		if( $this->isValid() ) {
			$action = 'update_product';
			$update_sql_data = array( 'products_last_modified' => 'now()', 'master_categories_id' => ($pParamHash['master_category'] > 0 ? zen_db_prepare_input($pParamHash['master_category']) : zen_db_prepare_input($pParamHash['master_categories_id'])));
			$sql_data_array = array_merge($sql_data_array, $update_sql_data);
			$db->associateUpdate( TABLE_PRODUCTS, $sql_data_array, array( 'name'=>'products_id', 'value'=>$this->mProductsId ) );
			// reset products_price_sorter for searches etc.
			zen_update_products_price_sorter( (int)$this->mProductsId );
		} else {
			$action = 'insert_product';
			$insert_sql_data = array( 'products_date_added' => 'now()', 'master_categories_id' => $pParamHash['category_id']);
			$sql_data_array = array_merge($sql_data_array, $insert_sql_data);
			$db->associateInsert(TABLE_PRODUCTS, $sql_data_array);
			$this->mProductsId = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );
			// reset products_price_sorter for searches etc.
			zen_update_products_price_sorter( $this->mProductsId );
			$db->query( "insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " ( `products_id`, `categories_id` ) values (?,?)", array( $this->mProductsId, $pParamHash['category_id'] ) );
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
				$db->associateInsert( TABLE_PRODUCTS_DESCRIPTION, $sql_data_array );
			} elseif ($action == 'update_product') {
				$db->query( "UPDATE " . TABLE_PRODUCTS_DESCRIPTION . " SET ". implode( ",", $sql_data_array) . " WHERE `products_id` =? AND `language_id`=?",  array( $this->mProductsId, $language_id ) );
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
				$db->associateInsert(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $sql_data_array);
			} elseif ($action == 'update_product_meta_tags') {
				$db->query( "UPDATE " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " SET ". implode( ",", $sql_data_array) . " WHERE `products_id` =? AND `language_id`=?",  array( $this->mProductsId, $language_id ) );
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
		$db->CompleteTrans();
	}

}


?>
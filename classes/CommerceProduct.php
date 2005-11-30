<?php

require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );

define( 'BITPRODUCT_CONTENT_TYPE_GUID', 'bitproduct' );

class CommerceProduct extends LibertyAttachable {
	var $mProductsId;
	var $mOptions;

	function CommerceProduct( $pProductsId=NULL, $pContentId=NULL ) {
		LibertyAttachable::LibertyAttachable();
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
		$this->mAdminContentPerm = 'bit_p_commerce_admin';
		$this->mOptions = NULL;
	}

	function load() {
		global $gBitUser;
		if( is_numeric( $this->mProductsId ) && $this->mInfo = $this->getProduct( $this->mProductsId ) ) {
			$this->mContentId = $this->mInfo['content_id'];
			if( !$this->isAvailable() && !$gBitUser->hasPermission( 'bit_p_commerce_admin' ) ) {
				$this->mInfo = array();
				unset( $this->mContent );
				unset( $this->mProductsId );
			}
			if( !empty( $this->mInfo['related_content_id'] ) ) {
				global $gLibertySystem;
				if( $this->mContent = $gLibertySystem->getLibertyObject( $this->mInfo['related_content_id'] ) ) {
					$this->mInfo['display_link'] = $this->mContent->getDisplayLink( $this->mContent->getTitle(), $this->mContent->mInfo );
				}
			}
		}
		return( count( $this->mInfo ) );
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
			$this->getServicesSql( 'content_load_function', $selectSql, $joinSql, $whereSql, $bindVars );
			array_push( $bindVars, $pProductsId, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );
			$query = "SELECT p.*, pd.*, pt.*, uu.* $selectSql ,tc.*, m.*
					  FROM " . TABLE_PRODUCTS . " p
					  	INNER JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON (p.`products_id`=pd.`products_id`)
					  	INNER JOIN ".TABLE_PRODUCT_TYPES." pt ON (p.`products_type`=pt.`type_id`)
					  	INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON (tc.`content_id`=p.`content_id`)
					  	INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=tc.`user_id`) $joinSql
						LEFT OUTER JOIN ".TABLE_MANUFACTURERS." m ON ( p.`manufacturers_id`=m.`manufacturers_id` )
						LEFT OUTER JOIN ".TABLE_SUPPLIERS." s ON ( p.`suppliers_id`=s.`suppliers_id` )					  WHERE p.`products_id`=? AND pd.`language_id`=? $whereSql";
// Leave these out for now... and possibly forever. These can produce multiple row returns
//						LEFT OUTER JOIN ".TABLE_TAX_CLASS." txc ON ( p.`products_tax_class_id`=txc.`tax_class_id` )
//						LEFT OUTER JOIN ".TABLE_TAX_RATES." txr ON ( txr.`tax_class_id`=txc.`tax_class_id` )
//						LEFT OUTER JOIN ".TABLE_CATEGORIES." c ON ( p.`master_categories_id`=c.`categories_id` )
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
		$ret = HTTP_SERVER.BITCOMMERCE_PKG_URL;
		if( is_numeric( $pProductsId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) ) {
				$ret .= $pProductsId;
				if( !empty( $pCatPath ) ) {
					$ret .= '/' . $_REQUEST['cPath'];
				}
			} else {
				$ret .= 'index.php?products_id='.$pProductsId;
				if( !empty( $pCatPath ) ) {
					$ret .= '&cPath=' . $_REQUEST['cPath'];
				}
			}
		}
		return $ret;
	}

	function getImageUrl( $pMixed=NULL, $pSize='small' ) {
		if( empty( $pMixed ) && !empty( $this ) && is_object( $this ) && !empty( $this->mProductsId ) ) {
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

	function getGatekeeperSql( &$pSelectSql, &$pJoinSql, &$pWhereSql ) {
		global $gBitSystem, $gBitUser;
		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
				$pSelectSql .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
				$pJoinSql   .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON (p.`content_id`=tcs.`content_id`)
								LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcs.`security_id` ) ";
			if( !$this->isOwner() && !$gBitUser->isAdmin() ) {
				// this is an ineleganct solution to mash $gBitUser->mUserId in there, but other things were painful.
				$pWhereSql .= ' AND (tcs.`security_id` IS NULL OR ts.`user_id`= \''.$gBitUser->mUserId.'\' )';
			}
		}
	}

	function getList( &$pListHash ) {
		global $gBitSystem;
	  	if( empty( $pListHash['sort_mode'] ) ) {
			$pListHash['sort_mode'] = 'products_date_added_desc';
		}
		BitBase::prepGetList( $pListHash );
		$bindVars = array();
		$selectSql = '';
		$joinSql = '';
		$whereSql = '';

		// This needs to go first since it puts a bindvar in the joinSql
		array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );

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

		if( !empty( $pListHash['user_id'] ) ) {
			$whereSql .= " AND tc.`user_id` = ? ";
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
				$whereSql .= " AND c.`parent_id`=? ";
				array_push( $bindVars, $pListHash['category_id'] );
			}
		}

		$joinSql .= ' AND pd.`language_id`=?';

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			$this->getGatekeeperSql( $selectSql, $joinSql, $whereSql, $bindVars );
		}

		$countQuery = "select COUNT( p.`products_id` )
				  from " . TABLE_PRODUCTS . " p
				 	INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON(p.`content_id`=tc.`content_id` )
				 	INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id` )
					INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON(p.`products_id`=pd.`products_id` )
					$joinSql
				  where p.`products_status` = '1' $whereSql ";
		$pListHash['total_count'] = $this->mDb->getOne( $countQuery, $bindVars );

		$query = "select p.`products_id` AS `hash_key`, p.*, pd.`products_name`, tc.`created`, uu.`user_id`, uu.`real_name`, uu.`login`, pt.* $selectSql
				  from " . TABLE_PRODUCTS . " p
				 	INNER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON(p.`content_id`=tc.`content_id` )
				 	INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id` )
					INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON(p.`products_id`=pd.`products_id` )
				  	INNER JOIN `" . BIT_DB_PREFIX."users_users` uu ON (uu.`user_id`=tc.`user_id`)
					$joinSql
				  where p.`products_status` = '1' $whereSql ORDER BY ".$this->mDb->convert_sortmode( $pListHash['sort_mode'] );
		if( $rs = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
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
				$ret[$productId]['display_price'] = zen_get_products_display_price( $productId );
			}
		}

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

	function isAvailable() {
		global $gBitUser;
		if( $this->isValid() ) {
 			if( !empty( $this->mInfo['products_status'] ) ) {
				$ret = TRUE;
			} else {
				$ret = $this->isOwner();
			}
 		} else {
			$ret = TRUE;
		}
		return( $ret );
	}

	function isOwner() {
		global $gBitUser;
		$ret = FALSE;
		if( $this->mInfo['user_id'] ) {
			$ret = $gBitUser->mUserId == $this->mInfo['user_id'];
		}
		return( $ret );
	}

	function isPurchased() {
		$ret = FALSE;
		if( $this->isValid() ) {
			$ret = $this->mDb->GetOne( "SELECT COUNT(*) FROM " . TABLE_ORDERS_PRODUCTS . " WHERE `products_id`=?", array( $this->mProductsId ) );
		}
		return $ret;
	}

	function verify( &$pParamHash ) {
		$pParamHash['product_store'] = array(
			'products_quantity' => (!empty( $pParamHash['products_quantity'] ) && is_numeric( $pParamHash['products_quantity'] ) ? $pParamHash['products_quantity'] : 0),
			'products_type' => (!empty( $pParamHash['products_type'] ) ? $pParamHash['products_type'] : 1),
			'products_model' => (!empty( $pParamHash['products_model'] ) ? $pParamHash['products_model'] : NULL),
			'products_manufacturers_model' => (!empty( $pParamHash['products_manufacturers_model'] ) ? $pParamHash['products_manufacturers_model'] : NULL),			
			'products_price' => (!empty( $pParamHash['products_price'] ) ? $pParamHash['products_price'] : NULL),
			'products_cogs' => (!empty( $pParamHash['products_cogs'] ) ? $pParamHash['products_cogs'] : NULL),					
			'products_weight' => (!empty( $pParamHash['products_weight'] ) ? $pParamHash['products_weight'] : NULL),
			'products_status' => (isset( $pParamHash['products_status'] ) ? (int)$pParamHash['products_status'] : NULL),
			'products_virtual' => (!empty( $pParamHash['products_virtual'] ) ? (int)$pParamHash['products_virtual'] : NULL),
			'products_tax_class_id' => (!empty( $pParamHash['products_tax_class_id'] ) ? $pParamHash['products_tax_class_id'] : NULL),
			'manufacturers_id' => (!empty( $pParamHash['manufacturers_id'] ) ? $pParamHash['manufacturers_id'] : NULL),
			'suppliers_id' => (!empty( $pParamHash['suppliers_id'] ) ? $pParamHash['suppliers_id'] : NULL),	
			'products_barcode' => (!empty( $pParamHash['products_barcode'] ) ? $pParamHash['products_barcode'] : NULL),	
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
			'purchase_group_id' => (!empty( $pParamHash['purchase_group_id'] ) ? $pParamHash['purchase_group_id'] : NULL),
			'products_qty_box_status' => (int)(!empty( $pParamHash['products_qty_box_status'] )),
			'products_quantity_order_units' => (!empty( $pParamHash['products_quantity_order_units'] ) && is_numeric( $pParamHash['products_quantity_order_units'] ) ? $pParamHash['products_quantity_order_units'] : 1),
			'products_quantity_order_min' => (!empty( $pParamHash['products_quantity_order_min'] ) && is_numeric( $pParamHash['products_quantity_order_min'] ) ? $pParamHash['products_quantity_order_min'] : 1),
			'products_quantity_order_max' => (!empty( $pParamHash['products_quantity_order_max'] ) && is_numeric( $pParamHash['products_quantity_order_max'] ) ? $pParamHash['products_quantity_order_max'] : 0),
			);

		$pParamHash['content_type_guid'] = BITPRODUCT_CONTENT_TYPE_GUID;
		if( is_array( $pParamHash['products_name'] ) ) {
			$pParamHash['title'] = current( $pParamHash['products_name'] );
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
		$this->mDb->StartTrans();
		if( $this->verify( $pParamHash ) && LibertyAttachable::store( $pParamHash ) ) {
			if (isset($pParamHash['pID'])) {
				$this->mProductsId = zen_db_prepare_input($pParamHash['pID']);
			}
// $this->debug();
	// when set to none remove from database
	//          if (isset($pParamHash['products_image']) && zen_not_null($pParamHash['products_image']) && ($pParamHash['products_image'] != 'none')) {

			if( $this->isValid() ) {
				$action = 'update_product';
				$this->mDb->associateUpdate( TABLE_PRODUCTS, $pParamHash['product_store'], array( 'name'=>'products_id', 'value'=>$this->mProductsId ) );
				// reset products_price_sorter for searches etc.
				zen_update_products_price_sorter( (int)$this->mProductsId );
			} else {
				$pParamHash['product_store']['content_id'] = $pParamHash['content_id'];
				$action = 'insert_product';
				$this->mDb->associateInsert( TABLE_PRODUCTS, $pParamHash['product_store'] );
				$this->mProductsId = zen_db_insert_id( TABLE_PRODUCTS, 'products_id' );
				// reset products_price_sorter for searches etc.
				zen_update_products_price_sorter( $this->mProductsId );
				$this->mDb->query( "insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " ( `products_id`, `categories_id` ) values (?,?)", array( $this->mProductsId, $pParamHash['master_categories_id'] ) );
			}

			$languages = zen_get_languages();
			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$language_id = $languages[$i]['id'];

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
					$query = "UPDATE " . TABLE_PRODUCTS_DESCRIPTION . " SET `".implode( array_keys( $bindVars ), '`=?, `' ).'`=?' . " WHERE `products_id` =? AND `language_id`=?";
					$bindVars['products_id'] = $this->mProductsId;
					$bindVars['language_id'] = $language_id;
					$this->mDb->query( $query, $bindVars );
				}
			}

		// add meta tags
			$languages = zen_get_languages();
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

				$this->mDb->query( "DELETE FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "WHERE `products_id`=?", array( $this->mProductsId ) );
				if( !empty( $bindVars ) ) {
					$bindVars['products_id'] = $this->mProductsId;
					$bindVars['language_id'] = $language_id;
					$this->mDb->associateInsert(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, $bindVars);
				}
			}

			if( !empty( $pParamHash['products_image'] ) && is_readable( $pParamHash['products_image'] ) ) {
				file_exists( $pParamHash['products_image'] );
				$fileHash['dest_path']		= STORAGE_PKG_URL.BITCOMMERCE_PKG_NAME.'/'.($this->mProductsId % 1000).'/'.$this->mProductsId.'/';
 				mkdir_p( BIT_ROOT_PATH.$fileHash['dest_path'] );
				$fileHash['source_file']	= $pParamHash['products_image'];
				$fileHash['name']			= basename( $fileHash['source_file'] );
				$fileHash['dest_base_name']	= 'original';
				$fileHash['max_height']		= 1024;
				$fileHash['max_width']		= 1280;
				if( class_exists( 'finfo' ) ) {
					// support for pecl Fileinfo - install with: pear install Fileinfo
					// some docs at http://wiki.cc/php/Fileinfo
					$res = finfo_open( FILEINFO_MIME );
					$info = new finfo( FILEINFO_MIME );
					$fileHash['type'] = finfo_file( $res, $pParamHash['products_image'] );
				} else {
					$pathParts = pathinfo( $pParamHash['products_image'] );
					$fileHash['type'] = 'image/'.$pathParts['extension'];
				}
				liberty_process_image( $fileHash );
			}

			$this->mDb->CompleteTrans();
			$this->load();
		}

		return( $this->mProductsId );
	}


	function getAttribute( $pOptionId, $pOptionValueId, $pAttr ) {
		$ret = NULL;
		if( is_null( $this->mOptions ) ) {
			$this->loadAttributes();
		}
		if( !empty( $this->mOptions[$pOptionId]['values'][$pOptionValueId][$pAttr] ) ) {
			$ret = $this->mOptions[$pOptionId]['values'][$pOptionValueId][$pAttr];
		}
		return $ret;
	}

	function compareAttributes( &$pParamHash, $pAttr ) {
		$currentAttr = $this->getAttribute( $pParamHash['options_id'], $pParamHash['options_values_id'], $pAttr );
		if( (!empty( $pParamHash[$pAttr] ) && $pParamHash[$pAttr] != $currentAttr )
			||	(empty( $pParamHash[$pAttr] ) && !empty( $pParamHash[$pAttr] )) ) {
			$pParamHash['attributes_store'][$pAttr] = !empty( $pParamHash[$pAttr] ) ? $pParamHash[$pAttr] : NULL;
		}
	}


	function verifyAttributes( &$pParamHash ) {
		if( !empty( $pParamHash['options_id'] ) ) {
			// iii 030811 added:  For TEXT and FILE option types, ignore option value
			// entered by administrator and use PRODUCTS_OPTIONS_VALUES_TEXT instead.
			$optionType = $this->mDb->GetOne( "select `products_options_type` from " . TABLE_PRODUCTS_OPTIONS . " where `products_options_id` = ?", array( $pParamHash['options_id'] ) );
			$pParamHash['options_values_id'] = (($optionType == PRODUCTS_OPTIONS_TYPE_TEXT) || ($optionType == PRODUCTS_OPTIONS_TYPE_FILE)) ? PRODUCTS_OPTIONS_VALUES_TEXT_ID : $pParamHash['options_values_id'];
			$this->compareAttributes( $pParamHash, 'options_values_id' );
			$this->compareAttributes( $pParamHash, 'options_values_price' );

			$attrList = array(
				'products_options_sort_order',
				'product_attribute_is_free',
				'products_attributes_wt',
				'products_attributes_wt_pfix',
				'attributes_display_only',
				'attributes_default',
				'attributes_discounted',
				'attributes_price_base_inc',
				'attributes_price_onetime',
				'attributes_price_factor',
				'attributes_pf_offset',
				'attributes_pf_onetime',
				'attributes_pf_onetime_offset',
				'attributes_qty_prices',
				'attributes_qty_prices_onetime',
				'attributes_price_words',
				'attributes_price_words_free',
				'attributes_price_letters',
				'attributes_price_letters_free',
				'attributes_required',
				'options_values_price',
				'price_prefix',
			);

			foreach( $attrList as $attr ) {
				$this->compareAttributes( $pParamHash, $attr  );
			}
		}
		return( !empty( $pParamHash['attributes_store'] ) && count( $pParamHash['attributes_store'] ) );
	}

	function storeAttributes( $pParamHash ) {
		if( $this->verifyAttributes( $pParamHash ) ) {
			$prodAttrId = $this->getAttribute( $pParamHash['options_id'], $pParamHash['options_values_id'], 'products_attributes_id' );
			if( $prodAttrId ) {
	            $this->mDb->associateUpdate( TABLE_PRODUCTS_ATTRIBUTES, $pParamHash['attributes_store'], array( 'name' => 'products_attributes_id', 'value' => $prodAttrId ) );
	        } else {
				$pParamHash['attributes_store']['options_id'] = $pParamHash['options_id'];
				$pParamHash['attributes_store']['products_id'] = $this->mProductsId;
	            $this->mDb->associateInsert( TABLE_PRODUCTS_ATTRIBUTES, $pParamHash['attributes_store'] );
	        }
		}
/*
// check for duplicate and block them
$check_duplicate = $db->query("DELETE * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id` = ? and `options_id` = ? and `options_values_id` = ?", array( $_POST['products_id'], $_POST['options_id'], $_POST['values_id'] ));
            $products_id = zen_db_prepare_input($_POST['products_id']);
//            $values_id = zen_db_prepare_input($_POST['values_id']);

        if ($check_duplicate->RecordCount() > 0) {
          // do not add duplicates give a warning
          $messageStack->add_session(ATTRIBUTE_WARNING_DUPLICATE . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
        } else {
          // Validate options_id and options_value_id
          if (!zen_validate_options_to_options_value($_POST['options_id'], $_POST['values_id'])) {
            // do not add invalid match
            $messageStack->add_session(ATTRIBUTE_WARNING_INVALID_MATCH . ' - ' . zen_options_name($_POST['options_id']) . ' : ' . zen_values_name($_POST['values_id']), 'error');
          } else {
// add - update as record exists
// attributes images
// when set to none remove from database
          if (isset($_POST['attributes_image']) && zen_not_null($_POST['attributes_image']) && ($_POST['attributes_image'] != 'none')) {
            $attributes_image = zen_db_prepare_input($_POST['attributes_image']);
          } else {
            $attributes_image = '';
          }

          $attributes_image = new upload('attributes_image');
          $attributes_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
          if ($attributes_image->parse() && $attributes_image->save($_POST['overwrite'])) {
            $attributes_image_name = $_POST['img_dir'] . $attributes_image->filename;
          } else {
            $attributes_image_name = (isset($_POST['attributes_previous_image']) ? $_POST['attributes_previous_image'] : '');
          }
			'attributes_image' => $attributes_image_name,
*/

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
			$this->mDiscounts = $this->mDb->getAssoc( "SELECT `discount_qty` AS `hash_key`, * FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE `products_id` = ? ORDER BY `discount_qty`", array( $this->mProductsId ) );
		}
		return( count( $this->mDiscounts ) );
	}

	////
	// Display Price Retail
	// Specials and Tax Included
	function expunge() {
		if( $this->isValid() ) {
			if( $this->isPurchased() ) {
				$this->mErrors['expunge'] = tra( 'This product cannot be deleted because it has been purchased' );
			} else {
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
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE `products_id` = ?", array( $this->mProductsId ));
				$this->mDb->query("delete FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE `products_id` = ?", array( $this->mProductsId ));

				// remove downloads if they exist
				$remove_downloads= $this->mDb->query( "SELECT `products_attributes_id` FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id` = ?", array( $this->mProductsId ) );
				while (!$remove_downloads->EOF) {
					$this->mDb->query("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " WHERE `products_attributes_id` =?", array( $remove_downloads->fields['products_attributes_id'] ) );
					$remove_downloads->MoveNext();
				}

				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_id` = ?", array( $this->mProductsId ));
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
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS . " WHERE `products_id` = ?", array( $this->mProductsId ));

				LibertyAttachable::expunge();

				$this->mInfo = array();
				unset( $this->mContent );
				unset( $this->mProductsId );

				$this->mDb->CompleteTrans();
			}
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


	////
	// Display Price Retail
	// Specials and Tax Included
	function getDisplayPrice( $pProductsId=NULL ) {
		global $db, $currencies;

		if( empty( $pProductsId ) && !empty( $this->mProductsId ) ) {
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
		$product_check = $db->getOne("select `products_tax_class_id`, `products_price`, `products_priced_by_attribute`, `product_is_free`, `product_is_call` from " . TABLE_PRODUCTS .
			" where `products_id` = '" . (int)$pProductsId . "'");

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

	function loadAttributes() {
		$this->mOptions = array();
		if( $this->isValid() ) {
			if (PRODUCTS_OPTIONS_SORT_ORDER=='0') {
				$options_order_by= ' ORDER BY popt.`products_options_sort_order`';
			} else {
				$options_order_by= ' ORDER BY popt.`products_options_name`';
			}

			$sql = "SELECT distinct popt.`products_options_id` AS hash_key, popt.*
					FROM " . TABLE_PRODUCTS_OPTIONS . " popt
						INNER JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " patrib ON(patrib.`options_id` = popt.`products_options_id`)
					WHERE patrib.`products_id`= ? AND popt.`language_id` = ? " .
					$options_order_by;

			if( $this->mOptions = $this->mDb->GetAssoc($sql, array( $this->mProductsId, (int)$_SESSION['languages_id'] ) ) ) {
				if ( PRODUCTS_OPTIONS_SORT_BY_PRICE =='1' ) {
					$order_by= ' ORDER BY pa.`products_options_sort_order`, pov.`products_options_values_name`';
				} else {
					$order_by= ' ORDER BY pa.`products_options_sort_order`, pa.`options_values_price`';
				}

				foreach( array_keys( $this->mOptions ) as $optionsId ) {
					$sql = "SELECT pov.`products_options_values_id`, pov.`products_options_values_name`, pa.*
							FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
								INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pa.`options_values_id` = pov.`products_options_values_id`)
							WHERE pa.`products_id`=? AND pa.`options_id`=? AND pov.`language_id`=? " .
							$order_by;
			        if( $rs = $this->mDb->query( $sql, array( $this->mProductsId, $optionsId, $_SESSION['languages_id'] ) ) ) {
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


	function hasAttributes( $pProductsId=NULL, $not_readonly = 'true' ) {
		$ret = FALSE;
		if( empty( $pProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}

		if( PRODUCTS_OPTIONS_TYPE_READONLY_IGNORED == '1' and $not_readonly == 'true' ) {
			// don't include READONLY attributes to determin if attributes must be selected to add to cart
			$query = "select pa.`products_attributes_id`
						from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_OPTIONS . " po on pa.`options_id` = po.`products_options_id`
						where pa.`products_id` = ? and po.`products_options_type` != '" . PRODUCTS_OPTIONS_TYPE_READONLY . "'";
		} else {
			// regardless of READONLY attributes no add to cart buttons
			$query = "select pa.`products_attributes_id`
						from " . TABLE_PRODUCTS_ATTRIBUTES . " pa
						where pa.`products_id` = ?";
		}

		$attributes = $this->mDb->getOne($query, array( $pProductsId) );

		return( $attributes->fields['products_attributes_id'] > 0 );
	}


	function hasNotification( $pCustomersId, $pProductsId=NULL ) {
		$ret = FALSE;
		if( empty( $pProductsId ) ) {
			$pProductsId = $this->mProductsId;
		}
		if( $this->isValid() && is_numeric( $pCustomersId ) ) {
			$query = "SELECT count(*) AS `count` FROM " . TABLE_PRODUCTS_NOTIFICATIONS . " WHERE `products_id`=? and `customers_id`=?";
			$ret = $this->mDb->getOne($query, array( $pProductsId, $pCustomersId ) );
		}
		return $ret;
	}

	function hasReviews() {
		if( $this->isValid() ) {
			// if review must be approved or disabled do not show review
			$review_status = " AND r.status = '1'";
			$sql = "SELECT count(*) as count
					FROM " . TABLE_REVIEWS . " r INNER JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON (r.`reviews_id` = rd.`reviews_id`)
					WHERE r.`products_id` = ? AND rd.`languages_id` = ?" . $review_status;

			return( $this->mDb->GetOne( $sql, array( $this->mProductsId, $_SESSION['languages_id'] ) ) );
		}
	}

	function isFree() {
		return( !empty( $this->mInfo['product_is_free'] ) );
	}

}



?>

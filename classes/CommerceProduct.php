<?php


class CommerceProduct extends BitBase {
	var $mInfo;

	function CommerceProduct( $pProductsId=NULL ) {
		BitBase::BitBase();
		$this->mProductsId = $pProductsId;
		$this->mInfo = array();
	}

	function load() {
		if( is_numeric( $this->mProductsId ) ) {
			$this->mInfo = $this->getProduct( $this->mProductsId );
			if( !$this->isAvailable() ) {
				$this->mInfo = array();
				unset( $this->mContent );
				unset( $this->mProductsId );
			}
			if( !empty( $this->mInfo['related_content_id'] ) ) {
				global $gLibertySystem;
				$this->mContent = $gLibertySystem->getLibertyObject( $this->mInfo['related_content_id'], $this->mInfo['content_type_guid'] );
					$this->mInfo['display_link'] = $this->mContent->getDisplayLink( $this->mContent->getTitle(), $this->mContent->mInfo );
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
		global $db;
		$ret = NULL;
		if( is_numeric( $pProductsId ) ) {
			$bindVars = array( $pProductsId );
			array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );
			$query = "SELECT *
					  FROM " . TABLE_PRODUCTS . " p
					  	INNER JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON (p.`products_id`=pd.`products_id`)
					  	INNER JOIN ".TABLE_PRODUCT_TYPES." pt ON (p.`products_type`=pt.`type_id`)
						LEFT OUTER JOIN ".TABLE_MANUFACTURERS." m ON ( p.`manufacturers_id`=m.`manufacturers_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( p.`related_content_id`=tc.`content_id`)
						LEFT OUTER JOIN ".TABLE_TAX_CLASS." txc ON ( p.`products_tax_class_id`=txc.`tax_class_id` )
						LEFT OUTER JOIN ".TABLE_TAX_RATES." txr ON ( txr.`tax_class_id`=txc.`tax_class_id` )
						LEFT OUTER JOIN ".TABLE_CATEGORIES." c ON ( p.`master_categories_id`=c.`categories_id` )
					  WHERE p.`products_id`=? AND pd.`language_id`=?";
			if( $ret = $db->getRow( $query, $bindVars ) ) {
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

	function getDisplayUrl( $pProductsId=NULL, $pTypeHandler=NULL ) {
		if( empty( $pProductsId ) && is_object( $this ) && $this->isValid() ) {
			$pProductsId = $this->mProductsId;
			if( empty( $pTypeHandler ) ) {
				$pTypeHandler = $this->mInfo['type_handler'];
			}
		}
		$ret = NULL;
		if( is_numeric( $pProductsId ) ) {
			$typeHandler = ( !empty( $pTypeHandler ) ? $pTypeHandler : 'product' );
			$ret = BITCOMMERCE_PKG_URL.'index.php?main_page='.$pTypeHandler.'_info&products_id='.$pProductsId;
		}

		if( !empty( $_REQUEST['cPath'] ) ) {
			$ret .= '&cPath=' . $_REQUEST['cPath'];
		}
		return $ret;
	}

	function getImageUrl( $pMixed=NULL, $pSize='small' ) {
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

	function getGatekeeperSql() {
		global $gBitDb, $gBitUser;
		$selectSql = '';
		$whereSql = '';
		$fromSql = '';
		if( $gBitDb->isAdvancedPostgresEnabled() ) {
			$whereSql .= " AND (SELECT ts.`security_id` FROM connectby('tiki_fisheye_gallery_image_map', 'gallery_content_id', 'item_content_id', p.`related_content_id`, 0, '/')  AS t(`cb_gallery_content_id` int, `cb_item_content_id` int, level int, branch text), `".BIT_DB_PREFIX."tiki_content_security_map` tcsm,  `".BIT_DB_PREFIX."tiki_security` ts
						WHERE ts.`security_id`=tcsm.`security_id` AND tcsm.`content_id`=`cb_gallery_content_id` LIMIT 1) IS NULL";
		} else {
			$selectSql .= ' ,ts.`security_id`, ts.`security_description`, ts.`is_private`, ts.`is_hidden`, ts.`access_question`, ts.`access_answer` ';
			$fromSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs ON (p.`related_content_id`=tcs.`content_id`)
						  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( tcs.`content_id`=tc.`content_id`)
						  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts ON (ts.`security_id`=tcs.`security_id` )
						  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_fisheye_gallery_image_map` tfgim ON (tfgim.`item_content_id`=tc.`content_id`)
						  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content_security_map` tcs2 ON (tfgim.`gallery_content_id`=tcs2.`content_id`)
						  LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_security` ts2 ON (ts2.`security_id`=tcs2.`security_id` )";
			$whereSql .= ' AND (tcs2.`security_id` IS NULL OR tc.`user_id`='.$gBitUser->mUserId.') ';
			$bindVars[] = $gBitUser->mUserId;
		}
		return( array( $selectSql, $fromSql, $whereSql ) );
	}

	function getList( &$pListHash ) {
		global $gBitSystem;
		BitBase::prepGetList( $pListHash );
		$bindVars = array();
		$selectSql = '';
		$fromSql = '';
		$whereSql = '';


// 		$selectSql .= ' , s.* ';
		if( !empty( $pListHash['specials'] ) ) {
			$fromSql .= " INNER JOIN " . TABLE_SPECIALS . " s ON ( p.`products_id` = s.`products_id` ) ";
			$whereSql .= " AND s.`status` = '1' ";
// 		} else {
// 			$fromSql .= " LEFT JOIN " . TABLE_SPECIALS . " s ON ( p.products_id = s.products_id AND s.status = '1' ) ";
		}

		if( !empty( $pListHash['featured'] ) ) {
			$fromSql .= " INNER JOIN " . TABLE_FEATURED . " f ON ( p.`products_id` = f.`products_id` ) ";
			$whereSql .= " AND f.`status` = '1' ";
		}

		if( !empty( $pListHash['best_sellers'] ) ) {
			$whereSql .= " AND p.`products_ordered` > 0 ";
		}

		if( !empty( $pListHash['freshness'] ) ) {
			if ( $pListHash['freshness'] == '1' ) {
				$whereSql .= " and ".$this->mDb->SQLDate( 'Ym', 'p.products_date_added' )." >= ".$this->mDb->SQLDate( 'Ym' );
			} else {
				$whereSql .= ' and '.$this->mDb->OffsetDate( SHOW_NEW_PRODUCTS_LIMIT, 'p.products_date_added' ).' > NOW()';
			}
		}

		if( !empty( $pListHash['reviews'] ) ) {
			$selectSql .= ' , r.`reviews_rating`, rd.`reviews_text` ';
			$fromSql .= " INNER JOIN " . TABLE_REVIEWS . " r  ON ( p.`products_id` = r.`products_id` ) INNER JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON ( r.`reviews_id` = rd.`reviews_id` ) ";
			$whereSql .= " AND r.`status` = '1' AND rd.languages_id = ? ";
			array_push( $bindVars, (int)$_SESSION['languages_id'] );
		}

		if ( !empty( $pListHash['category_id'] ) ) {
			if( !is_numeric( $pListHash['category_id'] ) && strpos( $pListHash['category_id'], '_' ) ) {
				$path = split( '_', $pListHash['category_id'] );
				end( $path );
				$pListHash['category_id'] = current( $path );
			}
			if( is_numeric( $pListHash['category_id'] ) ) {
				$fromSql .= " LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON ( p.`products_id` = p2c.`products_id` ) LEFT JOIN " . TABLE_CATEGORIES . " c ON ( p2c.`categories_id` = c.`categories_id` )";
				$whereSql .= " AND c.parent_id=? ";
				array_push( $bindVars, $pListHash['category_id'] );
			}
		}

		$fromSql .= ' AND pd.`language_id`=?';
		array_push( $bindVars, !empty( $_SESSION['languages_id'] ) ? $_SESSION['languages_id'] : 1 );

		if( $gBitSystem->isPackageActive( 'gatekeeper' ) ) {
			list( $gateSelectSql, $gateFromSql, $gateWhereSql ) = $this->getGatekeeperSql();
			$selectSql .= $gateSelectSql;
			$whereSql .= $gateWhereSql;
			$fromSql .= $gateFromSql;
		}

		$query = "select p.products_id AS hash_key, p.*, pd.`products_name`, pt.* $selectSql
				  from " . TABLE_PRODUCTS . " p
				 	INNER JOIN " . TABLE_PRODUCT_TYPES . " pt ON(p.`products_type`=pt.`type_id` )
					INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON(p.`products_id`=pd.`products_id` )
					$fromSql
				  where p.products_status = '1' $whereSql ORDER BY ".$this->mDb->convert_sortmode( $pListHash['sort_mode'] );
		if( $rs = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] ) ) {
			$ret = $rs->GetAssoc();
			foreach( array_keys( $ret ) as $productId ) {
				$ret[$productId]['info_page'] = $ret[$productId]['type_handler'].'_info';
				if( empty( $ret[$productId]['products_image'] ) ) {
					$ret[$productId]['products_image_url'] = CommerceProduct::getImageUrl( $ret[$productId]['products_id'], 'avatar' );
				}
			}
		}

		return( $ret );
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
			'products_price' => (!empty( $pParamHash['products_price'] ) ? $pParamHash['products_price'] : NULL),
			'products_weight' => (!empty( $pParamHash['products_weight'] ) ? $pParamHash['products_weight'] : NULL),
			'products_status' => (isset( $pParamHash['products_status'] ) ? (int)$pParamHash['products_status'] : NULL),
			'products_virtual' => (!empty( $pParamHash['products_virtual'] ) ? (int)$pParamHash['products_virtual'] : NULL),
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
			'related_group_id' => (!empty( $pParamHash['related_group_id'] ) ? $pParamHash['related_group_id'] : NULL),
			'products_qty_box_status' => (int)(!empty( $pParamHash['products_qty_box_status'] )),
			'products_quantity_order_units' => (!empty( $pParamHash['products_quantity_order_units'] ) && is_numeric( $pParamHash['products_quantity_order_units'] ) ? $pParamHash['products_quantity_order_units'] : 1),
			'products_quantity_order_min' => (!empty( $pParamHash['products_quantity_order_min'] ) && is_numeric( $pParamHash['products_quantity_order_min'] ) ? $pParamHash['products_quantity_order_min'] : 1),
			'products_quantity_order_max' => (!empty( $pParamHash['products_quantity_order_max'] ) && is_numeric( $pParamHash['products_quantity_order_max'] ) ? $pParamHash['products_quantity_order_max'] : 0),
			);

		if( !empty( $pParamHash['products_date_available'] ) ) {
			$pParamHash['product_store']['products_date_available'] = (date('Y-m-d') < $pParamHash['products_date_available']) ? $pParamHash['products_date_available'] : 'now()';
		} else {
			$pParamHash['product_store']['products_date_available'] = NULL;
		}

		$pParamHash['product_store']['products_last_modified'] = 'now()';
		$pParamHash['product_store']['master_categories_id'] = (!empty( $pParamHash['master_categories_id'] ) ? $pParamHash['master_categories_id'] : (!empty( $pParamHash['category_id'] ) ? $pParamHash['category_id'] : NULL ));
		if( !$this->isValid() ) {
			$pParamHash['product_store']['products_date_added'] = 'now()';
		}

		return( TRUE );
	}

	function store( &$pParamHash ) {
		$this->mDb->StartTrans();
		if( $this->verify( $pParamHash ) ) {
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

				$bindVars = array('products_name' => zen_db_prepare_input($pParamHash['products_name'][$language_id]),
								  'products_description' => zen_db_prepare_input($pParamHash['products_description'][$language_id]),
								  'products_url' => zen_db_prepare_input($pParamHash['products_url'][$language_id]));

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



		// future image handler code
/*
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
*/
			$this->mDb->CompleteTrans();
			$this->load();
		}
		return( $this->mProductsId );
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
					$duplicate_image = $this->mDb->GetOne("SELECT count(*) as total
                                     FROM " . TABLE_PRODUCTS . "
                                     WHERE products_image = ?", array( $this->mInfo['products_image'] ) );
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
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " WHERE products_id = ?");

				zen_products_attributes_download_delete($product_id);

				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_CUSTOMERS_BASKET . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE products_id = ?");

				$product_reviews = $this->mDb->query("SELECT reviews_id FROM " . TABLE_REVIEWS . " WHERE products_id = ?");
				while (!$product_reviews->EOF) {
					$this->mDb->query("delete FROM " . TABLE_REVIEWS_DESCRIPTION . "
								WHERE reviews_id = '" . (int)$product_reviews->fields['reviews_id'] . "'");
					$product_reviews->MoveNext();
				}

				$this->mDb->query("delete FROM " . TABLE_REVIEWS . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_FEATURED . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_SPECIALS . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " WHERE products_id = ?");
				$this->mDb->query("delete FROM " . TABLE_PRODUCTS . " WHERE products_id = ?");

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

		// $new_fields = ', product_is_free, product_is_call, product_is_showroom_only';
		$product_check = $db->Execute("select products_tax_class_id, products_price, products_priced_by_attribute, product_is_free, product_is_call from " . TABLE_PRODUCTS . " where products_id = '" . (int)$pProductsId . "'" . " limit 1");

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
			$sql = "INSERT INTO " . TABLE_PRODUCTS_NOTIFICATIONS . " (`products_id`, `customers_id`, `date_added`) values (?, ?, now())";
			$this->mDb->query( $sql, array( $pProductsId, $pCustomersId ) );
		}
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

}


?>
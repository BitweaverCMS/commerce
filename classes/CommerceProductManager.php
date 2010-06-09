<?php

//
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id$
//

class CommerceProductManager extends BitBase {

	function CommerceProductManager() {
		parent::BitBase();
	}

	function getOptionsList( $pListHash=NULL ) {
		$ret = array();
		
		$bindVars = array();
		$selectSql = '';
		$joinSql = '';
		$whereSql = '';
		
		if( !empty( $pListHash['products_options_id'] ) ) {
			$whereSql .= ' AND cpo.`products_options_id`=? ';
			$bindVars[] = $pListHash['products_options_id'];
		}
		if( !empty( $pListHash['products_options_values_id'] ) ) {
			$whereSql .= ' AND cpa.`products_options_values_id`=? ';
			$bindVars[] = $pListHash['products_options_values_id'];
		}
		
		$whereSql = preg_replace( '/^[\s]*AND\b/i', 'WHERE ', $whereSql );
		// SElect order is important so LEFT JOIN'ed tables don't NULL out primary keys of INNER JOIN'ed tables
	    $query = "SELECT cpo.`products_options_id` AS `hash_key` $selectSql , cpa.*, cpot.*, cpo.*, cpa.`products_options_sort_order`
				  FROM " . TABLE_PRODUCTS_OPTIONS . " cpo
				  	INNER JOIN " . TABLE_PRODUCTS_OPTIONS_TYPES . " cpot ON(cpo.`products_options_type`=cpot.`products_options_types_id`)
				  	$joinSql
				  	LEFT OUTER JOIN  " . TABLE_PRODUCTS_ATTRIBUTES . " cpa ON (cpa.`products_options_id`=cpo.`products_options_id`)
				  $whereSql
				  ORDER BY cpo.`products_options_name`, cpa.`products_options_sort_order`, cpa.`products_options_values_name`";
		if( $rs = $this->mDb->query( $query, $bindVars ) ) {
			while( $row = $rs->fetchRow() ) {
				if( empty( $ret[$row['hash_key']] ) ) {
					$ret[$row['hash_key']] = $row;
				}
				if( !empty( $row['hash_key'] ) && $rs->RecordCount() > 1 ) {
					unset( $ret[$row['hash_key']]['products_options_values_name'] );
					unset( $ret[$row['hash_key']]['products_ov_sort_order'] );
					if( !empty( $row['products_options_values_id'] ) ) {
						$ret[$row['hash_key']]['values'][$row['products_options_values_id']] = $row;
					}
				}
			}
		}
		return $ret;
	}


	function verifyOption( &$pParamHash ) {
//			$pParamHash['options_values_id'] = (($optionType == PRODUCTS_OPTIONS_TYPE_TEXT) || ($optionType == PRODUCTS_OPTIONS_TYPE_FILE)) ? PRODUCTS_OPTIONS_VALUES_TEXT_ID : $pParamHash['options_values_id'];
		$optList = array(
			'products_options_name',
			'products_options_sort_order',
			'products_options_type',
			'products_options_comment',
			'products_options_images_per_row',
			'products_options_images_style',
			'products_options_html_attrib',
		);
		foreach( $optList as $opt ) {
			$pParamHash['options_store'][$opt] = !empty( $pParamHash[$opt] ) || is_numeric( $pParamHash[$opt] ) ? $pParamHash[$opt] : NULL;
		}

		$pParamHash['options_store']['products_options_length'] = is_numeric( $pParamHash['products_options_length'] ) ? $pParamHash['products_options_length'] : 32;
		$pParamHash['options_store']['products_options_size'] = is_numeric( $pParamHash['products_options_size'] ) ? $pParamHash['products_options_size'] : 32;

		return( count( $this->mErrors ) == 0 && !empty( $pParamHash['options_store'] ) && count( $pParamHash['options_store'] ) );
	}

	function storeOption( $pParamHash ) {
		$ret = FALSE;
		if( $this->verifyOption( $pParamHash ) ) {
			if( !empty( $pParamHash['products_options_id'] ) ) {
				$this->mDb->associateUpdate( TABLE_PRODUCTS_OPTIONS, $pParamHash['options_store'], array( 'products_options_id' => $pParamHash['products_options_id'] ) );
			} else {
				$pParamHash['options_store']['products_options_id'] = $this->genOptionsId();
				$this->mDb->associateInsert( TABLE_PRODUCTS_OPTIONS, $pParamHash['options_store'] );
			}
			$ret = TRUE;
		}
		return $ret;
	}


	function genOptionsId() {
		global $gBitDb;
		$max = (int)$gBitDb->getOne( "SELECT MAX(`products_options_id`) FROM " . TABLE_PRODUCTS_OPTIONS );
		return( $max + 1 );
	}


	function verifyOptionsValue( &$pParamHash ) {
		if( !empty( $pParamHash['products_options_id'] ) ) {
			if( !empty( $pParamHash['products_options_values_id'] ) ) {
				$saveOptionsValue = $this->getOptionsValue( $pParamHash['products_options_values_id'] );
			}
			// iii 030811 added:  For TEXT and FILE option types, ignore option value
			// entered by administrator and use PRODUCTS_OPTIONS_VALUES_TEXT instead.
			$optionType = $this->mDb->GetOne( "select `products_options_type` from " . TABLE_PRODUCTS_OPTIONS . " where `products_options_id` = ?", array( $pParamHash['products_options_id'] ) );
//			$pParamHash['options_values_id'] = (($optionType == PRODUCTS_OPTIONS_TYPE_TEXT) || ($optionType == PRODUCTS_OPTIONS_TYPE_FILE)) ? PRODUCTS_OPTIONS_VALUES_TEXT_ID : $pParamHash['options_values_id'];
			$attrList = array(
				'products_options_id',
				'purchase_group_id',
				'options_values_price',
				'products_options_sort_order',
				'products_attributes_wt',
				'products_attributes_wt_pfix',
				'products_options_values_name',
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
				'options_values_price',
				'price_prefix',
			);
			foreach( $attrList as $attr ) {
				if( (!empty( $pParamHash[$attr] ) && (empty( $saveOptionsValue ) || $pParamHash[$attr] != $saveOptionsValue[$attr]) )
					||	(empty( $pParamHash[$attr] ) && !empty( $saveOptionsValue[$attr] )) ) {
					$pParamHash['options_values_store'][$attr] = !empty( $pParamHash[$attr] ) ? $pParamHash[$attr] : NULL;
				}
			}

			$attrSwitchList = array(
				'product_attribute_is_free',
				'attributes_display_only',
				'attributes_default',
				'attributes_discounted',
				'attributes_price_base_inc',
				'attributes_required',
			);
			foreach( $attrSwitchList as $attr ) {
				$pParamHash['options_values_store'][$attr] = !empty( $pParamHash[$attr] ) ? $pParamHash[$attr] : 0;
			}

		} else {
			$this->mErrors['products_options_id'] = 'Options not specified';
		}
		if( !empty( $pParamHash['products_options_values_id'] ) ) {
			$pParamHash['attibures_map_store']['products_options_values_id'] = $pParamHash['products_options_values_id'];
		}
		
		return( count( $this->mErrors ) == 0 && !empty( $pParamHash['options_values_store'] ) && count( $pParamHash['options_values_store'] ) );
	}

	function storeOptionsValue( $pParamHash ) {
		$this->mDb->StartTrans();
		$ret = FALSE;
		if( $this->verifyOptionsValue( $pParamHash ) ) {
			if( !empty( $pParamHash['products_options_values_id'] ) ) {
				$this->mDb->associateUpdate( TABLE_PRODUCTS_ATTRIBUTES, $pParamHash['options_values_store'], array( 'products_options_values_id' => $pParamHash['products_options_values_id'] ) );
			} else {
				$pParamHash['options_values_store']['products_options_values_id'] = $this->genOptionsValuesId();
				$this->mDb->associateInsert( TABLE_PRODUCTS_ATTRIBUTES, $pParamHash['options_values_store'] );
			}
			$ret = TRUE;
		}
		$this->mDb->CompleteTrans();
		return $ret;
	}


	function genOptionsValuesId() {
		global $gBitDb;
		$max = (int)$gBitDb->getOne( "SELECT MAX(`products_options_values_id`) FROM " . TABLE_PRODUCTS_ATTRIBUTES );
		return( $max + 1 );
	}


	function getOptionsTypes() {
		global $gBitDb;
		return( $gBitDb->getAssoc( "select products_options_types_id, products_options_types_name from " . TABLE_PRODUCTS_OPTIONS_TYPES . " order by products_options_types_id" ) );
	}

	function getOptions() {
		global $gBitDb;
		return( $gBitDb->getAssoc( "select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " order by products_options_name" ) );
	}

	function getOptionsValue( $pAttrId ) {
		$ret = array();
		if( BitBase::verifyId( $pAttrId ) ) {
			$ret = $this->mDb->getRow( "SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_options_values_id`=?", array( $pAttrId ) );
		}
		return $ret;
	}


	function expungeOptionsValue( $pOptionsValuesId ) {
		if( BitBase::verifyId( $pOptionsValuesId ) ) {
			// The products_id is redundant for safety purposes
			$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_OPTIONS_MAP . " WHERE `products_options_values_id` = ?", array( $pOptionsValuesId ));
			$this->mDb->query("DELETE FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE `products_options_values_id` = ?", array( $pOptionsValuesId ));
		}
		return( count( $this->mErrors ) == 0 );		
	}

}

?>

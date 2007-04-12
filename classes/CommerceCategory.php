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
//  $Id: CommerceCategory.php,v 1.2 2007/04/12 23:51:53 spiderr Exp $
//


require_once( KERNEL_PKG_PATH.'BitBase.php' );

class CommerceCategory extends BitBase {
	var $pCategoryId;

	function CommerceCategory( $pCategoryId=NULL, $pContentId=NULL ) {
		BitBase::BitBase();
		if( is_numeric( $pCategoryId ) ) {
			$this->mCategoryId = $pCategoryId;
		}
	}

	function countProductsInCategory( $pCategoryId ) {
		$ret = NULL;
		if( is_numeric( $pCategoryId ) ) {
			$query = "SELECT COUNT(*) as `total` FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE `categories_id` = ?";
			$ret = $this->mDb->getOne( $query, array( $pCategoryId ) );
		}
		return $ret;
	}

	function countParentCategories( $pParentId ) {
		$ret = NULL;
		if( is_numeric( $pParentId ) ) {
			$query = "SELECT COUNT(*) as `total` FROM " . TABLE_CATEGORIES . " WHERE `parent_id` = ?";
			$ret = $this->mDb->getOne( $query, array( $pParentId ) );
		}
		return $ret;
	}
}

?>

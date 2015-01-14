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


require_once( KERNEL_PKG_PATH.'BitBase.php' );

class CommerceCategory extends BitBase {
	var $pCategoryId;

	function __construct( $pCategoryId=NULL, $pContentId=NULL ) {
		parent::__construct();
		if( is_numeric( $pCategoryId ) ) {
			$this->mCategoryId = $pCategoryId;
			$this->load();
		}
	}

	public function load() {
		if( $this->isValid() ) {
			$this->mInfo = $this->mDb->getRow( "SELECT * FROM " . TABLE_CATEGORIES . " WHERE categories_id = ?", array( $this->mCategoryId ) );
		}
		return( count( $this->mInfo ) );
	}

	public function isValid() {
		return self::verifyId( $this->mCategoryId );
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

	static public function getList ( &$pListHash ) {
		global $gBitDb;

		$sql = "SELECT c.`categories_id`, cd.`categories_name`, cd.`categories_description`, c.`categories_image`, c.`parent_id`, c.`sort_order`, c.`date_added`, c.`last_modified`, c.`categories_status`
				FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
				WHERE c.`categories_id` = cd.`categories_id` AND cd.`language_id` = ?";

		$bindVars = array( (int)$_SESSION['languages_id'] );

		if( !empty( $pListHash['search'] ) ) {
			$sql .= "and LOWER( cd.`categories_name` ) LIKE ?";
			$bindVars[] = '%'.strtolower( zen_db_input($pListHash['search']) ).'%';
		}

		if( !empty( $pListHash['parent_id'] ) ) {
			$sql .= " AND c.`parent_id` = ?";
			$bindVars[] = $pListHash['parent_id'];
		}

		$sql .= "ORDER BY c.`sort_order`, cd.`categories_name`";

		if( $ret = $gBitDb->getAssoc( $sql, $bindVars ) ) {
		}

		return $ret;
	}
}

?>

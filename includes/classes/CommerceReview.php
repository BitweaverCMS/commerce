<?php
// :vim:tabstop=4:
// +--------------------------------------------------------------------+
// | Copyright (c) 2022-2023 bitcommerce.org							|
// | http://www.bitcommerce.org											|
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
// | Portions Copyright (c) 2003 The zen-cart developers				|
// | Portions Copyright (c) 2003 osCommerce								|	
// +--------------------------------------------------------------------+
//

require_once( KERNEL_PKG_CLASS_PATH.'BitBase.php' );

class CommerceReview extends CommerceBase {
	public function store( &$pReviewHash ) {
		$ret = FALSE;
		if( $this->verify( $pReviewHash ) ) {
			$pReviewHash['reviews_store']['last_modified'] = $this->mDb->NOW();
			if( empty( $pReviewHash['reviews_id'] ) ) {
				$pReviewHash['reviews_store']['reviews_id'] = $this->mDb->GenID( 'com_reviews_reviews_id_seq' );
				$this->mDb->associateInsert( TABLE_REVIEWS, $pReviewHash['reviews_store'] );
			} else {
				$this->mDb->associateUpdate( TABLE_REVIEWS, $pReviewHash['reviews_store'], array( 'reviews_id' => $pReviewHash['reviews_id'] ) );
			}
		}
		return $ret;
	}

	protected function verify( &$pReviewHash ) {
		$ret = TRUE;
		$cols = array( 'products_id', 'orders_id', 'customers_id', 'reviewers_name', 'reviews_rating', 'date_reviewed', 'last_modified', 'reviews_read', 'status', 'reviews_source', 'reviews_source_url', 'reviews_text', 'lang_code', 'format_guid', 'reviews_admin_note' );
		foreach( $cols as $col ) {
			$pReviewHash['reviews_store'][$col] = (isset( $pReviewHash[$col] ) ? $pReviewHash[$col] : NULL);
		}

		if( empty( $pReviewHash['reviews_store']['format_guid'] ) ) {
			$pReviewHash['reviews_store']['format_guid'] = 'text/plain';
		}

		if( empty( $pReviewHash['reviews_store']['status'] ) ) {
			$pReviewHash['reviews_store']['status'] = 1;
		}

		$pReviewHash['reviews_id'] = $this->mDb->getOne( "SELECT `reviews_id` FROM " . TABLE_REVIEWS . " WHERE `customers_id`=? AND `date_reviewed`=?", array( $pReviewHash['customers_id'], $pReviewHash['date_reviewed'] ) );

		return $ret;
	}

	public static function getList( &$pListHash ) {

		global $gCommerceSystem;
		$ret = array();

		$whereSql = '';
		$bindVars = array();
		$ret = array();

		if( !empty( $pListHash['orders_id'] ) ) {
			$whereSql .= ' AND cr.`orders_id`=? ';
			$bindVars = $pListHash['orders_id'];
		}

		if( !empty( $pListHash['customers_id'] ) ) {
			$whereSql .= ' AND cr.`customers_id`=? ';
			$bindVars = $pListHash['customers_id'];
		}

		$pListHash['cant'] = 0;

		if( empty ( $pListHash['sort_mode'] ) ) {
			$pListHash['sort_mode'] = 'date_reviewed_desc';
		}

		BitBase::prepGetList( $pListHash );

		if( !empty( $whereSql ) ) {
			$whereSql = preg_replace('/^ AND /',' WHERE ', $whereSql);
		}

		$query = "SELECT `reviews_id` AS `hash_key`, * FROM " . TABLE_REVIEWS . " cr
					LEFT JOIN " . TABLE_CUSTOMERS . " cc ON (cc.customers_id=cr.customers_id)
					LEFT JOIN `users_users` uu ON (uu.user_id=cr.customers_id)
					LEFT JOIN `com_orders` co ON (co.orders_id=cr.orders_id)
				$whereSql ";
				
		if( ($rs = $gCommerceSystem->mDb->query( $query." ORDER BY ".$gCommerceSystem->mDb->convertSortmode( $pListHash['sort_mode'] ), $bindVars, $pListHash['max_records'], $pListHash['offset'], BIT_QUERY_CACHE_TIME )) && $rs->RecordCount() ) {
			while (!$rs->EOF) {
				$ret['results'][$rs->fields['reviews_id']] = $rs->fields;

				$pListHash['page_records'] = 0;

				$ratingSum = 0;
				$firstReviewEpoch = PHP_INT_MAX;
				$lastReviewEpoch = 0;
				foreach( $ret['results'] as &$reviewHash ) {
					$reviewEpoch = strtotime( $reviewHash['date_reviewed'] );
					if( $reviewEpoch < $firstReviewEpoch ) {
						$firstReviewEpoch = $reviewEpoch;
					}
					if( $reviewEpoch > $lastReviewEpoch ) {
						$lastReviewEpoch = $reviewEpoch;
					}
					$pListHash['page_records']++;
					$ratingSum += $reviewHash['reviews_rating'];
				}
				$rs->MoveNext();
			}

			$pListHash['first_review_timestamp'] = $firstReviewEpoch;
			$pListHash['last_review_timestamp'] = $lastReviewEpoch;
			$pListHash['rating_avg'] = $ratingSum / $pListHash['page_records'];
			$pListHash['max_records'] = $pListHash['max_records'];
			$pListHash['current_page'] = $pListHash['offset'];
			$countSql = preg_replace( "/SELECT (.*) FROM/", "SELECT count(cr.`reviews_id`) FROM", $query );
			$pListHash['cant'] = $gCommerceSystem->mDb->getOne( $countSql, $bindVars );
			BitBase::postGetList( $pListHash );

		}

		return $ret;
	}
}

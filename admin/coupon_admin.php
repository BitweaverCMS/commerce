<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
//	$Id$
//

	require('includes/application_top.php');
	
	$feedback = array();

	if( !empty( $_REQUEST['selected_box'] ) ) {
		$_REQUEST['action'] ='';
		$_REQUEST['old_action']='';
	}

	$getAction = !empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';

	if( !empty( $_REQUEST['cid'] ) ) {
		$gCoupon = new CommerceVoucher( $_REQUEST['cid'] );
		$gCoupon->load( NULL, FALSE );
	} else {
		$gCoupon = new CommerceVoucher();
	}

	switch ($getAction) {
		case 'delete':
			if( $gCoupon->isValid() ) {
				// confirm first
				if( isset( $_REQUEST["confirm"] ) ) {
					$feedback['success'] = tra( 'Coupon deleted:' ).' '.$gCoupon->getField('coupon_code');
					$gCoupon->expunge();
				} else {
	//				$gBitSystem->setBrowserTitle( tra('Confirm removal of') . ' ' . $gContent->getTitle()); // crossposting from Blog \''.'addblognamehere'.'\'' );		
					$formHash['action'] = 'delete';
					$formHash['cid'] = $_REQUEST['cid'];
					$msgHash = array(
						'label' => 'Delete Coupon',
						'confirm_item' => $gCoupon->getField( 'coupon_code' ).' - '.$gCoupon->getField( 'coupon_description' ),
						'warning' => tra('This cannot be undone!'),
					);
					$gBitSystem->confirmDialog( $formHash, $msgHash );
				}
			}
			break;
		case 'store':
			$_REQUEST['coupon_id'] = $_REQUEST['cid'];
			if( $gCoupon->store( $_REQUEST ) ) {
				$feedback['success'] = tra( 'Coupon Saved:' ).' '.$gCoupon->getField( 'coupon_code' );
			} else {
				$getAction = 'edit';
			}
			break;
	}



	switch ($getAction) {
	case 'report':
		BitBase::prepGetList( $_REQUEST );
		if( empty ( $_REQUEST['listInfo']['sort_mode'] ) ) {
			$_REQUEST['listInfo']['sort_mode'] = 'redeem_date_desc';
		}
		$sql = "SELECT ccrt.`unique_id`, ccrt.*,cot.*,uu.*
				FROM " . TABLE_COUPON_REDEEM_TRACK . " ccrt
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uu ON (ccrt.`customer_id`=uu.`user_id`)
					LEFT OUTER JOIN " . TABLE_COUPONS . " cc ON (ccrt.`coupon_id`=cc.`coupon_id`)
					LEFT OUTER JOIN " . TABLE_ORDERS_TOTAL . " cot ON (ccrt.`order_id`=cot.`orders_id` AND cot.`class`='ot_coupon' AND UPPER(cot.`title`) LIKE '%'||UPPER(cc.`coupon_code`)||'%')
				WHERE ccrt.`coupon_id` = ?
				ORDER BY ".$gBitDb->convertSortmode( $_REQUEST['listInfo']['sort_mode'] );
		$bindVars = array( $_REQUEST['cid'] );

		if( empty( $_REQUEST['page'] ) ) {
			$_REQUEST['page'] = 0;
		}

		$_REQUEST['offset'] = ($_REQUEST['page'] ? (($_REQUEST['page'] -1) * $_REQUEST['max_records']) : 0);
		if( $rs = $gBitDb->query( $sql, $bindVars, $_REQUEST['max_records'], $_REQUEST['offset'] ) ) {
			while( $row = $rs->fetchRow() ) {
				$redeemList[$row['unique_id']] = $row;
			}
			$_REQUEST['listInfo']['page_records'] = $rs->RecordCount();
			$_REQUEST['cant'] = $gBitDb->getOne( "SELECT COUNT(*) FROM " . TABLE_COUPON_REDEEM_TRACK . " ccrt  WHERE ccrt.`coupon_id` = ?", $bindVars ); 
		}

		$_REQUEST['listInfo']['parameters']['action'] = $_REQUEST['action'];
		$_REQUEST['listInfo']['parameters']['cid'] = $_REQUEST['cid'];
		$_REQUEST['listInfo']['query_string'] = 'action=report&cid='.$_REQUEST['cid'];
		BitBase::postGetList( $_REQUEST );
		$_REQUEST['listInfo']['total_pages'] = ceil( $_REQUEST['listInfo']['total_records'] / $_REQUEST['max_records']);

		$gBitSmarty->assign_by_ref( 'redeemList', $redeemList );
		$title = tra( 'Edit Coupon' ).' : '.$coupon['coupon_code'];
		$mid = 'bitpackage:bitcommerce/admin_coupon_report.tpl';
		break;
	case 'edit':
		if( $gCoupon->isValid() ) {
			$_POST = array_merge( $gCoupon->mInfo, $_POST );

			if ($_POST['coupon_type']=='P') {
				$_POST['coupon_amount'] .= '%';
			}
			if ($_POST['coupon_type']=='S') {
				$_POST['coupon_free_ship'] = true;
			} else {
				$_POST['coupon_free_ship'] = false;
			}
			$languages = zen_get_languages();

			$_POST['coupon_name'] = NULL;
			$_POST['coupon_description'] = NULL;
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$languageId = $languages[$i]['id'];
				$editCoupon = $gBitDb->getRow( "SELECT `coupon_name`, `coupon_description` FROM " . TABLE_COUPONS_DESCRIPTION . " WHERE `coupon_id`=? AND `language_id`=?", array( $gCoupon->mCouponId, $languageId ) );
				$_POST['coupon_name'][$languageId] = $editCoupon['coupon_name'];
				$_POST['coupon_description'][$languageId] = $editCoupon['coupon_description'];
			}
		}
	case 'new':
		if( !$gCoupon->isValid() ) {
			$_POST['uses_per_user'] = 1;
		}

		if (!$_POST['coupon_start_date']) {
			$coupon_start_date = explode("-", date('Y-m-d'));
		} else {
			$coupon_start_date = explode("-", $_POST['coupon_start_date']);
		}
		if (!$_POST['coupon_expire_date']) {
			$coupon_expire_date = explode("-", date('Y-m-d'));
			$coupon_expire_date[0] = $coupon_expire_date[0] + 1;
		} else {
			$coupon_expire_date = explode("-", $_POST['coupon_expire_date']);
		}
		$gBitSmarty->assign( 'coupon_start_date', $coupon_start_date );
		$gBitSmarty->assign( 'coupon_expire_date', $coupon_expire_date );
		$gBitSmarty->assign( 'startDateSelect', zen_draw_date_selector('coupon_start_date', mktime(0,0,0, $coupon_start_date[1], (int)$coupon_start_date[2], $coupon_start_date[0] ) ) );
		$gBitSmarty->assign( 'finishDateSelect', zen_draw_date_selector('coupon_expire_date', mktime(0,0,0, $coupon_expire_date[1], (int)$coupon_expire_date[2], $coupon_expire_date[0] ) ) );
		$gBitSmarty->assign( 'languages', zen_get_languages() );
		if( $gCoupon->isValid() ) {
			$title = tra( 'Edit Coupon' ).' : '.$gCoupon->getField( 'coupon_code' );
		} else {
			$title = tra( 'Create Coupon' );
		}

		$mid = 'bitpackage:bitcommerce/admin_coupon_edit.tpl';
		break;
	default:
		$couponList = CommerceVoucher::getList( $_REQUEST );
		$_REQUEST['listInfo']['page_records'] = count( $couponList );
		$gBitSmarty->assign_by_ref( 'couponList', $couponList );
		$title = HEADING_TITLE;
		$mid = 'bitpackage:bitcommerce/admin_coupon_list.tpl';
	}

if( isset( $_REQUEST['listInfo'] ) ) {
	$_REQUEST['listInfo']['block_pages'] = 3;
	$_REQUEST['listInfo']['item_name'] = 'coupons';
	$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );
}
$gBitSmarty->assign_by_ref( 'gCoupon', $gCoupon );
$gBitSmarty->assign_by_ref( 'feedback', $feedback );
$gBitSystem->display( $mid, $title, array( 'display_mode' => 'admin' ));

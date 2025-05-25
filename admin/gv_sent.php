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

if( !empty( $_REQUEST['action'] ) ) {
	if( $_REQUEST['action'] == 'delete' ) {
//		$gBitUser->verifyTicket();
		$coupon = new CommerceVoucher( $_REQUEST['gid'] );
		$coupon->expunge();
		bit_redirect( $_SERVER['SCRIPT_NAME'] );
	}
}

$gBitSmarty->assign( 'feedback', $feedback );

$sql = "SELECT c.`coupon_id` AS `hash_key`, c.coupon_amount, c.coupon_code, c.coupon_id, c.admin_note, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, rt.`customer_id`, rt.`redeem_date`, rt.`redeem_ip`, rt.`order_id`
		FROM " . TABLE_COUPONS . " c
			INNER JOIN " . TABLE_COUPON_EMAIL_TRACK . " et ON(c.coupon_id=et.coupon_id)
			LEFT JOIN " . TABLE_COUPON_REDEEM_TRACK . " rt ON(c.coupon_id=rt.coupon_id)
		ORDER BY date_sent desc";
$couponList = array();
if( $rs = $gBitDb->query( $sql ) ) {
	while( $row = $rs->fetchRow() ) {
		$couponList[$row['hash_key']] = $row;
	}
}
$gBitSmarty->assignByRef( 'couponList', $couponList );

$gBitSystem->display( 'bitpackage:bitcommerce/admin_gv_sent.tpl', 'Gift Vouchers Sent' , array( 'display_mode' => 'admin' ));

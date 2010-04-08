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
//	$Id: gv_sent.php,v 1.10 2010/04/08 04:01:52 spiderr Exp $
//

require('includes/application_top.php');

$sql = "SELECT c.`coupon_id` AS `hash_key`, c.coupon_amount, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, rt.`customer_id`, rt.`redeem_date`, rt.`redeem_ip`, rt.`order_id`
		FROM " . TABLE_COUPONS . " c
			INNER JOIN " . TABLE_COUPON_EMAIL_TRACK . " et ON(c.coupon_id=et.coupon_id)
			LEFT JOIN " . TABLE_COUPON_REDEEM_TRACK . " rt ON(c.coupon_id=rt.coupon_id)
		ORDER BY date_sent desc";
$offset = (!empty( $_REQUEST['page'] ) ? $_REQUEST['page'] * MAX_DISPLAY_SEARCH_RESULTS : 0);
$couponList = array();
if( $rs = $gBitDb->query( $sql, array(), MAX_DISPLAY_SEARCH_RESULTS, $offset ) ) {
	while( $row = $rs->fetchRow() ) {
		$couponList[$row['hash_key']] = $row;
	}
}
$gBitSmarty->assign_by_ref( 'couponList', $couponList );

$gBitSystem->display( 'bitpackage:bitcommerce/admin_gv_sent.tpl', 'Gift Vouchers Sent' , array( 'display_mode' => 'admin' ));

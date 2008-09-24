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
//  $Id: commissions.php,v 1.5 2008/09/24 19:41:14 spiderr Exp $
//

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceCommission.php' );

// $gBitSmarty->assign( 'loadAjax', 'mochikit' );
// $gBitSmarty->assign( 'mochikitLibs', array( 'DOM.js', 'Iter.js', 'Style.js', 'Signal.js', 'Color.js', 'Position.js', 'Visual.js', 'DragAndDrop.js', 'Sortable.js' ) );

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$commissionManager = new CommerceCommission();

$listHash  = array();

$listHash['commissions_delay'] = $gBitSystem->getConfig( 'com_commissions_delay', '60' );
$endEpoch = strtotime( "-".$listHash['commissions_delay']." days" );
$listHash['commissions_due'] = $endEpoch;

$date = getdate( $endEpoch );
$periodEndDate = $date['year'].'-'.str_pad( $date['mon'], 2, '0', STR_PAD_LEFT ).'-'.str_pad( $date['mday'], 2, '0', STR_PAD_LEFT );
$gBitSmarty->assign( 'periodEndDate', $periodEndDate );

if( !empty( $_REQUEST['save_payment'] ) ) {
	$_REQUEST['commission_type'] = 'product sale';
	$commissionManager->storePayment( $_REQUEST );
}

if( $commissionsDue = $commissionManager->getCommissions( $listHash ) ) {
	foreach( array_keys( $commissionsDue ) as $userId ) {
		switch( $commissionsDue[$userId]['payment_method'] ) {
			case 'paypal':
				$commissionsDue[$userId]['commissions_paypal_address'] = LibertyContent::getPreference( 'commissions_paypal_address', NULL, $commissionsDue[$userId]['content_id'] );
				break;
			case 'worldpay':
				$commissionsDue[$userId]['commissions_worldpay_address'] = LibertyContent::getPreference( 'commissions_worldpay_address', NULL, $commissionsDue[$userId]['content_id'] );
				break;
			case 'storecredit':
				break;
			case 'check':
				$commissionsDue[$userId]['commissions_check_address'] = LibertyContent::getPreference( 'commissions_check_address', NULL, $commissionsDue[$userId]['content_id'] );
				break;
		}
	}
	$gBitSmarty->assign_by_ref( 'commissionsDue', $commissionsDue );
}

$gBitSystem->display( 'bitpackage:bitcommerce/admin_commissions.tpl', 'Commissions Report' , array( 'display_mode' => 'admin' ));
?>

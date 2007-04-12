<?php

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceCommission.php' );

// $gBitSmarty->assign( 'loadAjax', 'mochikit' );
// $gBitSmarty->assign( 'mochikitLibs', array( 'DOM.js', 'Iter.js', 'Style.js', 'Signal.js', 'Color.js', 'Position.js', 'Visual.js', 'DragAndDrop.js', 'Sortable.js' ) );

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$commissionManager = new CommerceCommission();

$listHash  = array();

$date = getdate();
$periodEndDate = $date['year'].'-'.str_pad( $date['mon'], 2, '0', STR_PAD_LEFT ).'-01';
$gBitSmarty->assign( 'periodEndDate', $periodEndDate );

$listHash['commissions_due'] = strtotime( $periodEndDate.' 23:59:59' );
$listHash['commissions_delay'] = $gBitSystem->getConfig( 'com_commissions_delay', '30' );

if( !empty( $_REQUEST['save_payment'] ) ) {
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

$gBitSystem->display( 'bitpackage:bitcommerce/admin_commissions.tpl', 'Commissions Report' );
?>

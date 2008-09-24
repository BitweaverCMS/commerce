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
//  $Id: revenue.php,v 1.1 2008/09/24 19:41:14 spiderr Exp $
//

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );

// $gBitSmarty->assign( 'loadAjax', 'mochikit' );
// $gBitSmarty->assign( 'mochikitLibs', array( 'DOM.js', 'Iter.js', 'Style.js', 'Signal.js', 'Color.js', 'Position.js', 'Visual.js', 'DragAndDrop.js', 'Sortable.js' ) );

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

$statsManager = new CommerceStatistics();
$listHash['max_records']  = -1;
if( $_REQUEST['period'] ) {
	$listHash['period'] = $_REQUEST['period'];
}
$gBitSmarty->assign_by_ref( 'stats', $statsManager->getAggregateRevenue( $listHash ) );

$gBitSystem->display( 'bitpackage:bitcommerce/admin_revenue.tpl', 'Revenue' , array( 'display_mode' => 'admin' ));
?>


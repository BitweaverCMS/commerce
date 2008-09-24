<?php

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );
$stats = new CommerceStatistics();

$listHash = array( 'period' => 'YYYY-MM-DD', 'max_records' => 8 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Daily' );
$gBitSmarty->assign( 'statPeriod', 'YYYY-MM-DD' );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' );

$listHash = array( 'period' => 'YYYY-WW', 'max_records' => 13 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Weekly' );
$gBitSmarty->assign( 'statPeriod', 'YYYY-WW' );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' );

$listHash = array( 'period' => 'YYYY-MM', 'max_records' => 13 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Monthly' );
$gBitSmarty->assign( 'statPeriod', 'YYYY-MM' );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' );

$listHash = array( 'period' => 'YYYY-"Q"Q', 'max_records' => 5 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Quarterly' );
$gBitSmarty->assign( 'statPeriod', 'YYYY-"Q"Q' );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' );

$listHash = array( 'period' => 'YYYY', 'max_records' => 10 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Yearly' );
$gBitSmarty->assign( 'statPeriod', 'YYYY' );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' );

?>

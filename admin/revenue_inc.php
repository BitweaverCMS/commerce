<?php

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );
$stats = new CommerceStatistics();

$listHash = array( 'period' => 'Y-m-d', 'max_records' => 8 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Daily' );
$gBitSmarty->assign( 'statPeriod', 'Y-m-d' );
print '<div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div>';

$listHash = array( 'period' => 'Y-\WeekW', 'max_records' => 13 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Weekly' );
$gBitSmarty->assign( 'statPeriod', 'Y-\WeekW' );
print '<div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div>';

$listHash = array( 'period' => 'Y-m', 'max_records' => 13 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Monthly' );
$gBitSmarty->assign( 'statPeriod', 'Y-m' );
print '<div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div>';

$listHash = array( 'period' => 'Y-\QQ', 'max_records' => 5 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Quarterly' );
$gBitSmarty->assign( 'statPeriod', 'Y-\QQ' );
print '<div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div>';

$listHash = array( 'period' => 'Y-', 'max_records' => 10 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Yearly' );
$gBitSmarty->assign( 'statPeriod', 'Y-' );
print '<div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div>';

?>

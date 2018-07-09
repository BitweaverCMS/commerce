<?php

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );
$stats = new CommerceStatistics();

$listHash = array( 'period' => 'Y-m-d', 'order_min'=>0.01, 'max_records' => 8 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Daily' );
$gBitSmarty->assign( 'statPeriod', 'Y-m-d' );
print '<div class="col-md-12 col-sm-6"><div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div></div>';

$listHash = array( 'period' => 'Y-\WeekW', 'order_min'=>0.01, 'max_records' => 13 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Weekly' );
$gBitSmarty->assign( 'statPeriod', 'Y-\WeekW' );
print '<div class="col-md-12 col-sm-6"><div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div></div>';

$listHash = array( 'period' => 'Y-m', 'order_min'=>0.01, 'max_records' => 13 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Monthly' );
$gBitSmarty->assign( 'statPeriod', 'Y-m' );
print '<div class="col-md-12 col-sm-6"><div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div></div>';

$listHash = array( 'period' => 'Y-\QQ', 'order_min'=>0.01, 'max_records' => 5 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Quarterly' );
$gBitSmarty->assign( 'statPeriod', 'Y-\QQ' );
print '<div class="col-md-12 col-sm-6"><div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div></div>';

$listHash = array( 'period' => 'Y-', 'order_min'=>0.01, 'max_records' => 10 );
$statData = $stats->getAggregateRevenue( $listHash );	
$gBitSmarty->assign_by_ref( 'stats', $statData );
$gBitSmarty->assign( 'revenueTitle', 'Yearly' );
$gBitSmarty->assign( 'statPeriod', 'Y-' );
print '<div class="col-md-12 col-sm-6"><div class="well nopadding">'.$gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_revenue_inc.tpl' ).'</div></div>';

?>

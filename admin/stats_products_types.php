<?php
// +--------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org									|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
/**
 * @version	$Header$
 *
 * Product class for handling all production manipulation
 *
 * @package	bitcommerce
 * @author	 spider <spider@steelsun.com>
 */


define('HEADING_TITLE', 'Order'.( (!empty( $_REQUEST['oID'] )) ? ' #'.$_REQUEST['oID'] : 's'));

require('includes/application_top.php');
global $gBitThemes;
$gBitThemes->loadJavascript( UTIL_PKG_URL.'javascript/libs/dynarch/jscalendar/calendar.js' );
$gBitThemes->loadJavascript( UTIL_PKG_URL.'javascript/libs/dynarch/jscalendar/lang/calendar-en.js' );
$gBitThemes->loadJavascript( UTIL_PKG_URL.'javascript/libs/dynarch/jscalendar/calendar-setup.js' );
$gBitThemes->loadCss( UTIL_PKG_URL.'javascript/libs/dynarch/jscalendar/calendar-win2k-cold-1.css' );

$gBitThemes->loadJavascript( UTIL_PKG_URL.'javascript/jquery/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js' );
$gBitThemes->loadCss( UTIL_PKG_URL.'javascript/jquery/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.css' );

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceStatistics.php' );

$stats = new CommerceStatistics();

$gBitSmarty->assign( 'statsByType', $stats->getRevenueByType( $_REQUEST ) );
$statsByOption = $stats->getRevenueByOption( $_REQUEST );
$gBitSmarty->assignByRef( 'statsByOption', $statsByOption );
$statsByOptionTotalUnits = array();
foreach( $statsByOption as $stat ) {
	@$statsByOptionTotalUnits[$stat['products_options_id']] += $stat['total_units'];
}
$gBitSmarty->assignByRef( 'statsByOptionTotalUnits', $statsByOptionTotalUnits );


print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_stats_products_types.tpl' );

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 

?>

<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

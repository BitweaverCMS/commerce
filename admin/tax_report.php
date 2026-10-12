<?php
require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceTaxReport.php' );

if( !defined( 'HEADING_TITLE' ) ) {
	define( 'HEADING_TITLE', tra( 'Sales Tax' ) );
}

$report = new CommerceTaxReport();
$feedback = array();
$action = BitBase::getParameter( $_POST, 'action', '' );
$deleteFiling = trim( (string)BitBase::getParameter( $_POST, 'delete_filing', '' ) );
if( $deleteFiling !== '' ) {
	$_POST['tax_title'] = $deleteFiling;
	$feedback = $report->deleteFiling( $_POST );
	$action = 'delete_filing';
} elseif( $action === 'save_filing' ) {
	$feedback = $report->saveFilings( $_POST );
} elseif( $action === 'add_filing' ) {
	$feedback = $report->addFiling( $_POST );
} elseif( $action === 'record_payment' ) {
	$feedback = $report->recordPayment( $_POST );
} elseif( $action === 'delete_payment' ) {
	$feedback = $report->deletePayment( $_POST );
}
if( $action !== '' ) {
	$gBitSmarty->assign( 'taxDueFeedback', CommerceTaxReport::dueFeedback() );
}

$year = (int)BitBase::getParameter( $_REQUEST, 'year', date( 'Y' ) );
if( $year < 1990 || $year > (int)date( 'Y' ) ) {
	$year = (int)date( 'Y' );
}
$yearChoices = array();
for( $choice = (int)date( 'Y' ); $choice >= 2005; $choice-- ) {
	$yearChoices[$choice] = (string)$choice;
}

$gBitSmarty->assign( 'feedback', $feedback );
$gBitSmarty->assign( 'taxYear', $year );
$gBitSmarty->assign( 'taxYearChoices', $yearChoices );
$gBitSmarty->assign( 'taxReportRows', $report->getReport( $year ) );
$gBitSmarty->assign( 'taxFilings', $report->scheduleRows() );
$gBitSmarty->assign( 'taxUnscheduledTitles', $report->unscheduledTitles() );
$gBitSmarty->assign( 'taxReopenTitle', trim( (string)BitBase::getParameter( $_GET, 'reopen_title', '' ) ) );
$gBitSmarty->assign( 'taxReopenPeriod', trim( (string)BitBase::getParameter( $_GET, 'reopen_period', '' ) ) );
$gBitSmarty->assign( 'taxFrequencies', CommerceTaxReport::frequencyChoices() );
$gBitSmarty->assign( 'taxCycleMonths', CommerceTaxReport::monthChoices() );

require( DIR_FS_ADMIN_INCLUDES.'header.php' );
$gBitSmarty->display( 'bitpackage:bitcommerce/admin_tax_report.tpl' );
require( DIR_FS_ADMIN_INCLUDES.'footer.php' );
require( DIR_FS_ADMIN_INCLUDES.'application_bottom.php' );

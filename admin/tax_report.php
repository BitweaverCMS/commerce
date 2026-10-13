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

// This page shows its own due summary; suppress the generic nav banner.
$gBitSmarty->assign( 'taxDueFeedback', array() );

// Which period's payment form is open on load: an explicit re-open link, or a
// payment that failed validation so the typed values can be corrected.
$reopenTitle = trim( (string)BitBase::getParameter( $_GET, 'reopen_title', '' ) );
$reopenPeriod = trim( (string)BitBase::getParameter( $_GET, 'reopen_period', '' ) );
$postedPayment = array();
if( $action === 'record_payment' && !empty( $feedback['error'] ) ) {
	$reopenTitle = trim( (string)BitBase::getParameter( $_POST, 'tax_title', '' ) );
	$reopenPeriod = trim( (string)BitBase::getParameter( $_POST, 'period_start', '' ) );
	foreach( array( 'amount', 'paid_on', 'reference', 'note' ) as $field ) {
		$postedPayment[$field] = (string)BitBase::getParameter( $_POST, $field, '' );
	}
}

$yearMin = 2005;
$yearMax = (int)date( 'Y' );
$year = (int)BitBase::getParameter( $_REQUEST, 'year', $yearMax );
if( $year < 1990 || $year > $yearMax ) {
	$year = $yearMax;
}
$yearChoices = array();
for( $choice = $yearMax; $choice >= $yearMin; $choice-- ) {
	$yearChoices[$choice] = (string)$choice;
}

$view = CommerceTaxReport::groupReport( $report->getReport( $year ) );
$filings = $report->scheduleRows();
// The schedule editor stays closed unless there is nothing scheduled yet or a
// schedule change was just rejected.
$scheduleActions = array( 'save_filing', 'add_filing', 'delete_filing' );
$schedulesOpen = ( empty( $filings ) || ( in_array( $action, $scheduleActions, TRUE ) && !empty( $feedback['error'] ) ) );

$gBitSmarty->assign( 'feedback', $feedback );
$gBitSmarty->assign( 'taxYear', $year );
$gBitSmarty->assign( 'taxYearMin', $yearMin );
$gBitSmarty->assign( 'taxYearMax', $yearMax );
$gBitSmarty->assign( 'taxYearChoices', $yearChoices );
$gBitSmarty->assign( 'taxReportGroups', $view['groups'] );
$gBitSmarty->assign( 'taxTotals', $view['totals'] );
$gBitSmarty->assign( 'taxDue', $view['due'] );
$gBitSmarty->assign( 'taxFilings', $filings );
$gBitSmarty->assign( 'taxSchedulesOpen', $schedulesOpen );
$gBitSmarty->assign( 'taxUnscheduledTitles', $report->unscheduledTitles() );
$gBitSmarty->assign( 'taxReopenTitle', $reopenTitle );
$gBitSmarty->assign( 'taxReopenPeriod', $reopenPeriod );
$gBitSmarty->assign( 'taxPostedPayment', $postedPayment );
$gBitSmarty->assign( 'taxFrequencies', CommerceTaxReport::frequencyChoices() );
$gBitSmarty->assign( 'taxCycleMonths', CommerceTaxReport::monthChoices() );

require( DIR_FS_ADMIN_INCLUDES.'header.php' );
$gBitSmarty->display( 'bitpackage:bitcommerce/admin_tax_report.tpl' );
require( DIR_FS_ADMIN_INCLUDES.'footer.php' );
require( DIR_FS_ADMIN_INCLUDES.'application_bottom.php' );

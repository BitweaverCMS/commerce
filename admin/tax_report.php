<?php
require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceTaxReport.php' );

$gBitSystem->setRequestConfig( 'layout-body', '-fluid' );

$report = new CommerceTaxReport();
$feedback = array();
$action = BitBase::getParameter( $_POST, 'action', '' );
if( $action === 'save_filing' ) {
	$feedback = $report->saveFilings( $_POST );
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
$gBitSmarty->assign( 'taxFrequencies', CommerceTaxReport::frequencyChoices() );
$gBitSmarty->assign( 'taxCycleMonths', CommerceTaxReport::monthChoices() );

$gBitSystem->display( 'bitpackage:bitcommerce/admin_tax_report.tpl', tra( 'Sales Tax' ), array( 'display_mode' => 'admin' ) );

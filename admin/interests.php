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



require('includes/application_top.php');
define('HEADING_TITLE', tra( 'Customer Interests' ) );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php' );

$mid = 'bitpackage:bitcommerce/admin_interests.tpl';

if( !empty( $_REQUEST['action'] ) ) {
	switch( $_REQUEST['action'] ) {
		case 'deletec2i':
			// Ajax method
			if( $gBitCustomer->expungeCustomerInterest( $_REQUEST ) ) {
				print '<div class="success">'.tra('Removed').'</div>';
			}
die;
			break;
		case 'savec2i':
			// Ajax method
			if( $gBitCustomer->storeCustomerInterest( $_REQUEST ) ) {
				print '<div class="success">'.tra('Saved').'</div>';
			}
die;
			break;
		case 'save':
			$gBitCustomer->storeInterest( $_REQUEST );
			bit_redirect( $_SERVER['SCRIPT_NAME'] );
			break;
		case 'delete':
			$gBitCustomer->expungeInterest( $_REQUEST['interests_id'] );
			bit_redirect( $_SERVER['SCRIPT_NAME'] );
			break;
		case 'edit':
			$gBitSmarty->assign( 'editInterest', $gBitCustomer->getInterest( $_REQUEST['interests_id'] ) );
			break;
	}
} elseif( !empty( $_REQUEST['save_options'] ) ) {
	$gBitSystem->storeConfig( 'commerce_register_interests', !empty( $_REQUEST['commerce_register_interests'] ) ? 'y' : NULL );
	bit_redirect( $_SERVER['SCRIPT_NAME'] );
} elseif( !empty( $_REQUEST['uninterested'] ) ) {
	$gBitSmarty->assign_by_ref( 'uninterestedCustomers', CommerceCustomer::getUninterestedCustomers() );
	$mid = 'bitpackage:bitcommerce/admin_uninterested_customers.tpl';
}

$gBitSmarty->assign( 'interestsList', $gBitCustomer->getInterests() );
print $gBitSmarty->fetch( $mid, tra( 'Customer Interests' ) );

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 
require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); 

<?php
require('includes/application_top.php');
define('HEADING_TITLE', tra( 'Export Orders' ) );

global $gBitDb,$gBitSystem;

$orders = array();

$statuses = array_replace( array( '' => '' ), commerce_get_statuses( TRUE ) );
$gBitSmarty->assign( 'commerceStatuses', $statuses );

// From PirateShip 
$headerHash = array(
				'Order ID'	=> 'orders_id',
				'Email'		=> 'customers_email_address',
				'Full Name'	=> 'delivery_name',
				'Company'	=> 'delivery_company',
				'Address 1'	=> 'delivery_street_address',
				'Address 2'	=> 'delivery_suburb',
				'City'		=> 'delivery_city',
				'State'		=> 'delivery_state',
				'Zipcode'	=> 'delivery_postcode',
				'Country'	=> 'delivery_country',
//				'Order Items',
//				'Pounds',
				'Length'	=> FALSE,
				'Width'		=> FALSE,
				'Height'	=> FALSE 
			);

if( @BitBase::verifyId( $_REQUEST['orders_status_id'] ) ) {
	$listHash['orders_status_id'] = $_REQUEST['orders_status_id'];
	$statusName = '_'.$statuses[$listHash['orders_status_id']];

	if( $orders = order::getList( $listHash ) ) {

		$action = strtolower( BitBase::getParameter( $_REQUEST, 'action' ) );
		if( $action == 'export' && !empty( $_REQUEST['export'] )) {

			$exportList = array_flip( $_REQUEST['export'] );
			header("Content-type: text/csv");
			header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($gBitSystem->getConfig( 'site_title' )."_orders_export_" . date("Y-m-d") . $statusName .'.csv'));

			$out = fopen('php://output', 'w');
			fputcsv( $out, array_keys( $headerHash ) );
			foreach( $orders as $ordersId => $orderHash ) {
				if( isset( $exportList[$ordersId] ) ) {
					$orderRow = array();
					foreach( $headerHash as $columnName => $deliveryKey ) {
						if( $deliveryKey ) {
							$orderRow[] = BitBase::getParameter( $orderHash, $deliveryKey, '' );
						} else {
							$orderRow[] = (!empty( $_REQUEST['order'][$ordersId][$columnName] ) ? $_REQUEST['order'][$ordersId][$columnName] : '');
						}
					}
					fputcsv( $out, $orderRow );
				}
			}
			fclose( $out );
			exit;
		}
	}
}

$gBitSmarty->assign_by_ref( 'headerHash', $headerHash );
$gBitSmarty->assign_by_ref( 'orders', $orders );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_export_orders.tpl' );

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 
require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); 


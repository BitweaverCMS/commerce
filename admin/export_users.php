<?php
	require('includes/application_top.php');
	define('HEADING_TITLE', tra( 'Export Customers' ) );

   
	global $gBitDb,$gBitSystem;

if( !empty( $_REQUEST['export'] ) ) {
	$sql = "SELECT uu.email,real_name,uu.user_id,cab.*,ccou.countries_name,MIN(date_purchased) as first_purchase_date, MAX(date_purchased) as last_purchase_date, COUNT(co.orders_id) AS num_purchases, SUM(co.`order_total`) AS `customer_revenue`, ". $gBitDb->SqlIntToTimestamp( 'uu.`registration_date`' ) ." AS `registration_date`, ". $gBitDb->SqlIntToTimestamp( 'ms.`unsubscribe_date`' ) ." AS `unsubscribe_date`
			FROM users_users uu
			 	 INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON (ugm.user_id = uu.user_id) 
				LEFT JOIN " . TABLE_CUSTOMERS . " cc ON (cc.`customers_id`=uu.`user_id)
				 LEFT JOIN " . TABLE_ADDRESS_BOOK ." cab ON (cc.customers_default_address_id = cab.address_book_id)
				 LEFT JOIN " . TABLE_COUNTRIES . " ccou ON (ccou.countries_id = cab.entry_country_id)
				 LEFT JOIN " . TABLE_ORDERS . " co ON (cab.customers_id = co.customers_id)
				 LEFT JOIN `".BIT_DB_PREFIX."mail_subscriptions` ms ON (ms.user_id = uu.user_id)
	 		WHERE ugm.`group_id` = ? AND uu.`user_id`>0 AND co.`orders_status` > 0
			GROUP BY uu.email,uu.real_name,uu.user_id,uu.registration_date,cab.address_book_id,cab.customers_id,cab.entry_gender,cab.entry_company,cab.entry_firstname,cab.entry_lastname,cab.entry_street_address,cab.entry_suburb,cab.entry_postcode,cab.entry_city,cab.entry_state,cab.entry_country_id,cab.entry_zone_id,cab.entry_telephone,ms.unsubscribe_date,ccou.countries_name
			ORDER BY RANDOM(), uu.`user_id`";

	$max = (!empty( $_REQUEST['num_records'] ) ? $_REQUEST['num_records'] : NULL );
	if( $rs = $gBitDb->query($sql,array( $gBitSystem->getConfig('users_validate_email_group') ), $max ) )  {
		$tempFH = tmpfile();
		$headerSet = FALSE;
		while( $row = $rs->fetchRow() ) {
			$csvOutput = array();

			// Email
			if( !$headerSet ) { $header[] = 'email'; }
			$csvOutput[] = $row['email'];

			// Unsubscribe Date
			if( !$headerSet ) { $header[] = 'unsubscribe_date'; }
			$csvOutput[] = $row['unsubscribe_date'];

			if( !empty( $_REQUEST['customers_id'] ) ) {
				if( !$headerSet ) { $header[] = 'customers_id'; }
				$csvOutput[] = $row['user_id'];
			}
			if( !empty( $_REQUEST['firstname'] ) ) {
				if( !$headerSet ) { $header[] = 'first_name'; }
				$firstName = '';
				if( !empty( $row['entry_firstname'] ) ) {
					$firstName = $row['entry_firstname'];
				} elseif( strpos( $row['real_name'], ' ' ) ) {
					$firstName = substr( $row['real_name'], 0, strpos( $row['real_name'], ' ' ) );
				}
				$csvOutput[] = ucwords( strtolower( trim( $firstName ) ) );
			}
			if( !empty( $_REQUEST['lastname'] ) ) {
				if( !$headerSet ) { $header[] = 'last_name'; }
				$lastName = '';
				if( !empty( $row['entry_lastname'] ) ) {
					$lastName = $row['entry_lastname'];
				} elseif( strpos( $row['real_name'], ' ' ) ) {
					$lastName = substr( $row['real_name'], strpos( $row['real_name'], ' ' ) + 1 );
				}
				$csvOutput[] = ucwords( strtolower( trim( $lastName ) ) );
			}
			if( !empty( $_REQUEST['company'] ) ) {
				if( !$headerSet ) { $header[] = 'company'; }
				$csvOutput[] = ucwords( strtolower( trim( $row['entry_company'] ) ) );
			}
			if( !empty( $_REQUEST['street_address'] ) ) {
				if( !$headerSet ) { $header[] = 'address'; }
				$address = $row['entry_street_address'];
				if( !empty( $row['suburb'] ) ) {
					$address .= '\,'.$row['entry_suburb'];
				}
				$csvOutput[] = $address;
			}
			if( !empty( $_REQUEST['city'] ) ) {
				if( !$headerSet ) { $header[] = 'city'; }
				$csvOutput[] = ucwords( strtolower( trim( $row['entry_city'] ) ) );
			}
			if( !empty( $_REQUEST['state'] ) ) {
				if( !$headerSet ) { $header[] = 'state'; }
				$csvOutput[] = ucwords( strtolower( trim( $row['entry_state'] ) ) );
			}
			if( !empty( $_REQUEST['zip'] ) ) {
				if( !$headerSet ) { $header[] = 'zip'; }
				$csvOutput[] = trim( strtoupper( $row['entry_postcode'] ) );
			}
			if( !empty( $_REQUEST['country'] ) ) {
				if( !$headerSet ) { $header[] = 'country'; }
				$csvOutput[] = ucwords( strtolower( trim( $row['countries_name'] ) ) );
			}
			if( !empty( $_REQUEST['registration_date'] ) ) {
				if( !$headerSet ) { $header[] = 'registration_date'; }
				$csvOutput[] = $row['registration_date'];
			}
			if( !empty( $_REQUEST['first_purchase_date'] ) ) {
				if( !$headerSet ) { $header[] = 'first_purchase_date'; }
				$csvOutput[] = $row['first_purchase_date'];
			}
			if( !empty( $_REQUEST['last_purchase_date'] ) ) {
				if( !$headerSet ) { $header[] = 'last_purchase_date'; }
				$csvOutput[] = $row['last_purchase_date'];
			}
			if( !empty( $_REQUEST['num_purchases'] ) ) {
				if( !$headerSet ) { $header[] = 'num_purchases'; }
				$csvOutput[] = $row['num_purchases'];
			}
			if( !empty( $_REQUEST['interests'] ) ) {
				if( !$headerSet ) { $header[] = 'interests'; }
				if( $interests = $gBitDb->getCol( "SELECT `interests_name` FROM " . TABLE_CUSTOMERS_INTERESTS_MAP . " cim INNER JOIN " . TABLE_CUSTOMERS_INTERESTS . " ci ON(ci.`interests_id`=cim.`interests_id`) WHERE cim.`customers_id`=?", array( $row['customers_id'] ) ) ) {
					$csvOutput[] = implode( '\,', $interests );
				} else {
					$csvOutput[] = "";
				}
			}
			if( !$headerSet ) {
				fputcsv( $tempFH, $header );
				$headerSet = TRUE;
			}
			fputcsv( $tempFH, $csvOutput );
		}
	}

	//Benchmark email format
/*		$csvOutput = "EmailAddress,FirstName,LastName,Address,City,State,Zip,Extra 1,Extra 2\n";//Extra 1 = customer id //Extra 2 = last purchase date
	foreach ($users_export as $user){
		$csvOutput[] = "$user[email],$user[entry_firstname],$user[entry_lastname],$user[entry_street_address],$user[entry_city],$user[entry_state],$user[entry_postcode],$user[customers_id],$user[last_purchase]\n";
	}*/
	//Mad Mimi format
// address	city	company	confirmed	country	unsubscribe_date	email	first_name	last_name	phone	state	title	zip	suppression_reason
//	$csvOutput = "Email,firstname,lastname,address,city,state,zip,country,organizationname,phone,user_id,last_purchase,opt out\n";
//	foreach ($users_export as $user){
//		$csvOutput[] = "$user[email],$user[entry_firstname],$user[entry_lastname],$user[entry_street_address],$user[entry_city],$user[entry_state],$user[entry_postcode],$user[user_id],$user[last_purchase],".(!empty($user['unsubscribe_date'])?'1':'')."\n";
//	}

	header("Content-type: text/csv");
	header("Content-disposition: filename=users_export" . date("Y-m-d") . ".csv");
	fseek( $tempFH, 0 );	
	fpassthru( $tempFH );

	print $csvOutput;
	exit;  
}

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_export_users.tpl' );

require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); 
require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); 

?>

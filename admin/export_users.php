<?php
   require_once '../../bit_setup_inc.php';
   
	global $gBitDb,$gBitSystem;
	
	$query = "SELECT uu.email,real_name,uu.user_id,cab.*,MAX(date_purchased) as last_purchase FROM users_users uu
			  INNER JOIN users_groups_map ugm ON (ugm.user_id = uu.user_id) 
			  LEFT JOIN com_address_book cab ON (uu.user_id = cab.customers_id)
			  LEFT JOIN com_orders co ON (cab.customers_id = co.customers_id)
			  LEFT JOIN mail_subscriptions ms ON (ms.user_id = uu.user_id)
	 		  WHERE ugm.group_id = ? AND ms.unsubscribe_date IS NULL
			  GROUP BY uu.email,uu.real_name,uu.user_id,cab.address_book_id,cab.customers_id,cab.entry_gender,cab.entry_company,cab.entry_firstname,cab.entry_lastname,cab.entry_street_address,cab.entry_suburb,cab.entry_postcode,cab.entry_city,cab.entry_state,cab.entry_country_id,cab.entry_zone_id,cab.entry_telephone";
	$result = $gBitDb->getAll($query,array($gBitSystem->getConfig('users_validate_email_group'))); 

	$users_export = array();
	foreach ($result as $res){
		$users_export[$res['email']] = $res; //done like this to overwrite cases of users with multiple addresses and thus multiple entries	
	}
	//Benchmark email format
/*		$csv_output = "EmailAddress,FirstName,LastName,Address,City,State,Zip,Extra 1,Extra 2\n";//Extra 1 = customer id //Extra 2 = last purchase date
	foreach ($users_export as $user){
		$csv_output .= "$user[email],$user[entry_firstname],$user[entry_lastname],$user[entry_street_address],$user[entry_city],$user[entry_state],$user[entry_postcode],$user[customers_id],$user[last_purchase]\n";
	}*/
	//Mad Mimi etc. format
	$csv_output = "Email\n";
	foreach ($users_export as $user){
		$csv_output .= "$user[email]\n";
	}
   header("Content-type: text/csv");
   header("Content-disposition: filename=users_export" . date("Y-m-d") . ".csv");
   print $csv_output;
   exit;  
?>

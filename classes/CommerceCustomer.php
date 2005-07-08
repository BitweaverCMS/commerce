<?php
	class CommerceCustomer extends BitBase {
		function syncBitUser( $pInfo ) {
			global $db;
			// bitcommerce customers table to bitweaver users_users table
			$syncFields = array( 'customers_id'=>'user_id', 'customers_nick'=>'login', 'customers_email_address'=>'email' );
/*
'customers_firstname'
'customers_lastname'
'customers_gender'
'customers_dob'
'customers_default_address_id'
'customers_telephone'
'customers_fax'
'customers_password'
'customers_newsletter'
'customers_group_pricing'
'customers_email_format'
'customers_authorization'
'customers_referral'
*/
			$rs = $db->query( "SELECT * FROM ".TABLE_CUSTOMERS." WHERE `customers_id`=?", array( $pInfo['user_id'] ) );
			if( $rs && !$rs->EOF ) {
				foreach ( $syncFields AS $custKey=>$userKey ) {
					if( isset( $pInfo[$userKey] ) && ( $pInfo[$userKey] != $rs->fields[$custKey] ) ) {
						$resyncHash[$custKey] = $pInfo[$userKey];
					}
				}
				if( !empty( $resyncHash ) ) {
					$db->associateUpdate( TABLE_CUSTOMERS, $resyncHash, array( 'name'=>'customers_id', 'value'=>$rs->fields['customers_id'] ) );
				}
			} else {
				$custHash = array( 'customers_id' => $pInfo['user_id'], 'customers_nick' => $pInfo['login'], 'customers_email_address' => $pInfo['email'] );
				$db->associateInsert( TABLE_CUSTOMERS, $custHash );
			}
		}
	}
?>
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

		function verifyAddress( &$pParamHash ) {

			if( empty( $pParamHash['firstname'] ) || strlen( $pParamHash['firstname'] ) < ENTRY_FIRST_NAME_MIN_LENGTH ) {
				$this->mErrors['firstname'] = tra( 'Your First Name must contain a minimum of ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['store_address']['entry_firstname'] = $pParamHash['firstname'];
			}

			if( empty( $pParamHash['lastname'] ) || strlen( $pParamHash['lastname'] ) < ENTRY_LAST_NAME_MIN_LENGTH ) {
				$this->mErrors['lastname'] = tra( 'Your Last Name must contain a minimum of ' . ENTRY_LAST_NAME_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['store_address']['entry_lastname'] = $pParamHash['lastname'];
			}

			if( empty( $pParamHash['street_address'] ) || strlen( $pParamHash['street_address'] ) < ENTRY_STREET_ADDRESS_MIN_LENGTH ) {
				$this->mErrors['street_address'] = tra( 'Your Street Address must contain a minimum of ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['store_address']['entry_street_address'] = $pParamHash['street_address'];
			}

			if( empty( $pParamHash['postcode'] ) || strlen( $pParamHash['street_address'] ) < ENTRY_POSTCODE_MIN_LENGTH ) {
				$this->mErrors['postcode'] = tra( 'Your Post Code must contain a minimum of ' . ENTRY_POSTCODE_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['store_address']['entry_postcode'] = $pParamHash['postcode'];
			}

			if( empty( $pParamHash['postcode'] ) || strlen( $pParamHash['postcode'] ) < ENTRY_CITY_MIN_LENGTH ) {
				$this->mErrors['city'] = tra( 'Your City must contain a minimum of ' . ENTRY_CITY_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['store_address']['entry_city'] = $pParamHash['city'];
			}

			if( ACCOUNT_GENDER == 'true' && (empty( $pParamHash['gender'] ) || $pParamHash['gender'] != 'm' || $pParamHash['gender'] != 'f' ) ) {
				$this->mErrors['gender'] = tra( 'Please choose a title.' );
			} else {
				$pParamHash['store_address']['entry_gender'] = $pParamHash['gender'];
			}

			if( ACCOUNT_COMPANY == 'true' && !empty( $pParamHash['company'] ) ) {
				$pParamHash['store_address']['entry_company'] = $pParamHash['company'];
			}

			if( ACCOUNT_SUBURB == 'true' && !empty( $pParamHash['suburb'] ) ) {
				$pParamHash['store_address']['entry_suburb'] = $pParamHash['suburb'];
			}

			if( empty( $pParamHash['country'] ) || !is_numeric( $pParamHash['country'] ) || ($pParamHash['country'] < 1) ) {
				$this->mErrors['country'] = tra( 'You must select a country from the Countries pull down menu.' );
			} else {
				$pParamHash['store_address']['entry_country_id'] = $pParamHash['country'];
				if (ACCOUNT_STATE == 'true') {
					$zone_id = 0;
					$check_query = "select count(*) as total
									from " . TABLE_ZONES . "
									where zone_country_id = ?";

					;

					if( $check = $db->query( $check_query , array( $pParamHash['country'] ) ) ) {
						$zone_query = "select distinct zone_id from " . TABLE_ZONES . "
									   where zone_country_id = ? and (zone_name like ? OR zone_code like ?)";

						if ( $rs = $db->query($zone_query, array( $pParamHash['country'], strtoupper( $pParamHash['state'] ), strtoupper( $pParamHash['state'] ) ) ) ) {
							$pParamHash['store_address']['entry_state'] = $pParamHash['state'];
							$pParamHash['store_address']['entry_zone_id'] = $rs->fields['zone_id'];
						} else {
							$this->mErrors['state'] = tra( 'Please select a state from the States pull down menu.' );
						}
					} elseif( empty( $pParamHash['state'] ) || strlen( $pParamHash['state'] ) < ENTRY_STATE_MIN_LENGTH ) {
						$this->mErrors['state'] = tra( 'Your State must contain a minimum of ' . ENTRY_STATE_MIN_LENGTH . ' characters.' );
					} else {
						$pParamHash['store_address']['entry_state'] = $pParamHash['state'];
					}
				}
			}

			return( count( $this->mErrors ) == 0 );
		}

		// process a new shipping address
		function storeAddress( &$pParamHash ) {
			global $current_page_base, $language_page_directory, $template;

			$directory_array = $template->get_template_part($language_page_directory, '/^'.$current_page_base . '/');
			while(list ($key, $value) = each($directory_array)) {
				require_once($language_page_directory . $value);
			}

			if( $this->verifyAddress( $pParamHash ) ) {
				$process = true;
vd( $pParamHash );
				if( empty( $pParamHash['address'] ) ) {
					$this->mDb->associateInsert(TABLE_ADDRESS_BOOK, $pParamHash['store_address']);
					$pParamHash['address_book_id'] = zen_db_insert_id( TABLE_ADDRESS_BOOK, 'address_book_id' );
				} else {
					$pParamHash['store_address']['customers_id'] = (int)$_SESSION['customer_id'];
					$db->associateUpdate(TABLE_ADDRESS_BOOK, $pParamHash['store_address'], array( 'name'=>'address_book_id' , 'value'=>$pParamHash['address'] ) );
				}
			// process the selected shipping destination
			}
		}


		function getAddresses( $pCustomerId ) {
			global $db;
			$ret = NULL;
			if( is_numeric( $pCustomerId ) ) {
				$query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
									entry_company as company, entry_street_address as street_address,
									entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
									entry_state as state, entry_zone_id as zone_id,
									entry_country_id as country_id, c.*
							from " . TABLE_ADDRESS_BOOK . " ab INNER JOIN " . TABLE_COUNTRIES . " c ON( ab.entry_country_id=c.countries_id )
							where customers_id = ?";

				if( $rs = $db->query( $query, array( $pCustomerId ) ) ) {
					$ret = $rs->GetRows();
				}
			}
			return $ret;
		}

	}
?>
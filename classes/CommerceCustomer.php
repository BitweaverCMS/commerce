<?php
//
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id: CommerceCustomer.php,v 1.26 2007/10/29 03:50:02 spiderr Exp $
//
	class CommerceCustomer extends BitBase {
		var $mCustomerId;

		function CommerceCustomer( $pCustomerId ) {
			BitBase::BitBase();
			if( is_numeric( $pCustomerId ) ) {
				$this->mCustomerId = $pCustomerId;
			}
		}

		function isValid() {
			return( !empty( $this->mCustomerId ) && is_numeric( $this->mCustomerId ) );
		}

		function load() {
			if( $this->isValid() ) {
				$sql = "SELECT * FROM " . TABLE_CUSTOMERS . " WHERE `customers_id`=?";
				if( $rs = $this->mDb->query( $sql, array( $this->mCustomerId ) ) ) {
					$this->mInfo = $rs->fields;
				}
			}
			return( count( $this->mInfo ) );
		}

		function getGiftBalance() {
			$ret = '0.00';
			if( $this->isValid() ) {
				if( $couponAmount = $this->mDb->getOne( "SELECT amount FROM " . TABLE_COUPON_GV_CUSTOMER . " WHERE `customer_id` = ?", array( $this->mCustomerId ) ) ) {
					$ret = $couponAmount;
				}
			}
			return $ret;
		}

		function getCommissionsHistory() {
			$ret = array();
			if( $this->isValid() ) {
				$sql = "SELECT cop.`orders_products_id` AS `hash_key`, co.*,cop.* FROM 
							" . TABLE_ORDERS . " co  
							INNER JOIN	" . TABLE_ORDERS_PRODUCTS . " cop ON (co.`orders_id`=cop.`orders_id`)
							INNER JOIN	" . TABLE_PRODUCTS . " cp ON (cp.`products_id`=cop.`products_id`)
							INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (cp.`content_id`=lc.`content_id`)
						WHERE lc.`user_id`=? AND cop.`products_commission` IS NOT NULL AND cop.`products_commission` > 0
						ORDER BY co.`date_purchased` ASC";
				if( $sales = $this->mDb->getAssoc( $sql, array( $this->mCustomerId ) ) ) {
					foreach( array_keys( $sales ) as $hashKey ) {
						$sales[$hashKey]['purchased_epoch'] = strtotime($sales[$hashKey]['date_purchased'] );
					}
				}

				$sql = "SELECT ccp.`commissions_payments_id` AS `hash_key`, ccp.* FROM " . TABLE_COMMISSIONS_PAYMENTS . " ccp WHERE `payee_user_id`=? ORDER BY ccp.`period_end_date` ASC";
				if( $commissions = $this->mDb->getAssoc( $sql, array( $this->mCustomerId ) ) ) {
					foreach( array_keys( $commissions ) as $commId ) {
						$commissions[$commId]['period_end_epoch'] = strtotime( $commissions[$commId]['period_end_date'] );
					}
				}
				$commission = current( $commissions );
				foreach( $sales AS $sale ) {
					if( !empty( $commission ) && ((int)$commission['period_end_epoch'] < (int)$sale['purchased_epoch']) ) {
						array_push( $ret, $commission );
						$commission = next( $commissions );
					}
					array_push( $ret, $sale );
				}
				// add the last commission if no sales since last payment
				if( !empty( $commission ) ) {
					array_push( $ret, $commission );
				}
				$ret = array_reverse( $ret );
			}
			return( $ret );
		}

		function getGlobalNotifications() {
			if( $this->isValid() ) {
				$global_query = "SELECT `global_product_notifications` from " . TABLE_CUSTOMERS_INFO . " WHERE `customers_info_id` = ?";
				return( $this->mDb->getOne($global_query, array( $this->mCustomerId ) ) );
			}
		}

		function syncBitUser( $pInfo ) {
			global $gBitDb;
			// bitcommerce customers table to bitweaver users_users table
			$syncFields = array( 'customers_id'=>'user_id', 'customers_nick'=>'login', 'customers_email_address'=>'email' );
/* Fields in TABLE_CUSTOMERS:
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
			$rs = $gBitDb->query( "SELECT * FROM ".TABLE_CUSTOMERS." WHERE `customers_id`=?", array( $pInfo['user_id'] ) );
			if( $rs && !$rs->EOF ) {
				foreach ( $syncFields AS $custKey=>$userKey ) {
					if( isset( $pInfo[$userKey] ) && ( $pInfo[$userKey] != $rs->fields[$custKey] ) ) {
						$resyncHash[$custKey] = $pInfo[$userKey];
					}
				}
				if( !empty( $resyncHash ) ) {
					$gBitDb->associateUpdate( TABLE_CUSTOMERS, $resyncHash, array( 'customers_id' =>$rs->fields['customers_id'] ) );
				}
			} else {
				$custHash = array( 'customers_id' => $pInfo['user_id'], 'customers_nick' => $pInfo['login'], 'customers_email_address' => $pInfo['email'] );
				$gBitDb->associateInsert( TABLE_CUSTOMERS, $custHash );
			}
		}

		function register( $pParamHash ) {
			global $gBitUser, $gBitSmarty;
			if( !empty( $_REQUEST['email'] ) && $gBitUser->userExists( array( 'email' => $_REQUEST['email'] ) ) ) {
				if( $gBitUser->login( $_REQUEST['email'], $_REQUEST['password'], FALSE, FALSE ) ) {
					$_REQUEST['customers_id'] = $gBitUser->mUserId;
				} else {
					$gBitSmarty->assign_by_ref( 'userErrors', $newUser->mErrors );
				}
			} else {
				$newUser = new BitPermUser();
				if( $newUser->register( $_REQUEST ) ) {
					$newUser->login( $_REQUEST['email'], $_REQUEST['password'], FALSE, FALSE );
					$newUser->load();
					$_REQUEST['customers_id'] = $gBitUser->mUserId;
					$this->mCustomerId = $gBitUser->mUserId;
					$this->load();
					$this->syncBitUser( $newUser->mInfo );
					$gBitUser = $newUser;
				} else {
					$gBitSmarty->assign_by_ref( 'userErrors', $newUser->mErrors );
				}
			}
			return( count( $gBitUser->mErrors ) == 0 );
		}



//=-=-=-=-=-=-=-=-=-=-= ADDRESS FUNCTIONS



		function verifyAddress( &$pParamHash, &$errorHash ) {
			global $gBitUser;
			if( empty( $pParamHash['customers_id'] ) || !is_numeric( $pParamHash['customers_id'] ) ) {
				if( $this->isValid() ) {
					$pParamHash['address_store']['customers_id'] = $this->mCustomerId;
				} else {
					$errorHash['customers_id'] = tra( 'Your must be registered to save addresses' );
				}
			} else {
				$pParamHash['address_store']['customers_id'] = $pParamHash['customers_id'];
			}

			if( empty( $pParamHash['firstname'] ) || strlen( $pParamHash['firstname'] ) < ENTRY_FIRST_NAME_MIN_LENGTH ) {
				$errorHash['firstname'] = tra( 'Your First Name must contain a minimum of ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['address_store']['entry_firstname'] = $pParamHash['firstname'];
			}

			if( empty( $pParamHash['lastname'] ) || strlen( $pParamHash['lastname'] ) < ENTRY_LAST_NAME_MIN_LENGTH ) {
				$errorHash['lastname'] = tra( 'Your Last Name must contain a minimum of ' . ENTRY_LAST_NAME_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['address_store']['entry_lastname'] = $pParamHash['lastname'];
			}

			if( empty( $pParamHash['street_address'] ) || strlen( $pParamHash['street_address'] ) < ENTRY_STREET_ADDRESS_MIN_LENGTH ) {
				$errorHash['street_address'] = tra( 'Your Street Address must contain a minimum of ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['address_store']['entry_street_address'] = $pParamHash['street_address'];
			}

			if( empty( $pParamHash['postcode'] ) || strlen( $pParamHash['street_address'] ) < ENTRY_POSTCODE_MIN_LENGTH ) {
				$errorHash['postcode'] = tra( 'Your Post Code must contain a minimum of ' . ENTRY_POSTCODE_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['address_store']['entry_postcode'] = $pParamHash['postcode'];
			}

			if( empty( $pParamHash['city'] ) || strlen( $pParamHash['city'] ) < ENTRY_CITY_MIN_LENGTH ) {
				$errorHash['city'] = tra( 'Your City must contain a minimum of ' . ENTRY_CITY_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['address_store']['entry_city'] = $pParamHash['city'];
			}

			if( !empty( $pParamHash['telephone'] ) && strlen( $pParamHash['telephone'] ) < ENTRY_TELEPHONE_MIN_LENGTH ) {
				$errorHash['telephone'] = tra( 'Your City must contain a minimum of ' . ENTRY_TELEPHONE_MIN_LENGTH . ' characters.' );
			} elseif( !empty( $pParamHash['telephone'] ) ) {
				$pParamHash['address_store']['entry_telephone'] = $pParamHash['telephone'];
			} else {
				$pParamHash['address_store']['entry_telephone'] = NULL;
			}

			if( ACCOUNT_GENDER == 'true' && !empty( $pParamHash['gender'] ) ) {
				$pParamHash['address_store']['entry_gender'] = $pParamHash['gender'];
			}

			if( ACCOUNT_COMPANY == 'true' && !empty( $pParamHash['company'] ) ) {
				$pParamHash['address_store']['entry_company'] = $pParamHash['company'];
			}

			if( ACCOUNT_SUBURB == 'true' && !empty( $pParamHash['suburb'] ) ) {
				$pParamHash['address_store']['entry_suburb'] = $pParamHash['suburb'];
			}

			if( empty( $pParamHash['country_id'] ) || !is_numeric( $pParamHash['country_id'] ) || ($pParamHash['country_id'] < 1) ) {
				$errorHash['country_id'] = tra( 'You must select a country from the Countries pull down menu.' );
			} else {
				$pParamHash['address_store']['entry_country_id'] = $pParamHash['country_id'];
				if (ACCOUNT_STATE == 'true') {
					if( $this->getZoneCount( $pParamHash['country_id'] ) ) {
						if( $zoneId = $this->getZoneId( $pParamHash['state'], $pParamHash['country_id'] ) ) {
							$pParamHash['address_store']['entry_state'] = $pParamHash['state'];
							$pParamHash['address_store']['entry_zone_id'] = $zoneId;
						} else {
							$errorHash['state'] = tra( 'Please select a state from the States pull down menu.' );
						}
					} elseif( empty( $pParamHash['state'] ) || strlen( $pParamHash['state'] ) < ENTRY_STATE_MIN_LENGTH ) {
						$errorHash['state'] = tra( 'Your State must contain a minimum of ' . ENTRY_STATE_MIN_LENGTH . ' characters.' );
					} else {
						$pParamHash['address_store']['entry_state'] = $pParamHash['state'];
					}
				}
			}

			return( count( $errorHash ) == 0 );
		}

		// process a new shipping address
		function storeAddress( &$pParamHash ) {
			global $current_page_base, $language_page_directory, $template;

			$directory_array = $template->get_template_part($language_page_directory, '/^'.$current_page_base . '/');
			while(list ($key, $value) = each($directory_array)) {
				require_once($language_page_directory . $value);
			}

			if( $this->verifyAddress( $pParamHash, $this->mErrors ) ) {
				$process = true;
				if( empty( $pParamHash['address'] ) ) {
					$this->mDb->associateInsert(TABLE_ADDRESS_BOOK, $pParamHash['address_store']);
					$pParamHash['address'] = zen_db_insert_id( TABLE_ADDRESS_BOOK, 'address_book_id' );
				} else {
					$this->mDb->associateUpdate(TABLE_ADDRESS_BOOK, $pParamHash['address_store'], array( 'address_book_id' =>$pParamHash['address'] ) );
				}
				if( !$this->getDefaultAddress() || !empty( $pParamHash['primary'] ) ) {
					$this->setDefaultAddress( $pParamHash['address'] );
				}
			// process the selected shipping destination
			}
			return( count( $this->mErrors ) == 0 );
		}

		function getAddress( $pAddressId, $pSecure = TRUE ) {
			$ret = NULL;
			if( is_numeric( $pAddressId ) && (!$pSecure || ($pSecure && $this->isValid())) ) {
				$bindVars = array( $pAddressId );
				$whereSql = '';
				if( $pSecure ) {
					$whereSql = " AND `customers_id`=?";
					array_push( $bindVars, $this->mCustomerId );
				}
				$query = "SELECT * FROM " . TABLE_ADDRESS_BOOK . " WHERE `address_book_id`=? $whereSql";
				if( $rs = $this->mDb->query( $query, $bindVars ) ) {
					$ret = $rs->fields;
				}
			}
			return( $ret );
		}

		function getDefaultAddress() {
			$ret = NULL;
			if( $this->isValid() ) {
				if( empty( $this->mInfo ) ) {
					$this->load();
				}
				if( !empty( $this->mInfo['customers_default_address_id'] ) && $this->addressExists( $this->mInfo['customers_default_address_id'] ) ) {
					$ret = $this->mInfo['customers_default_address_id'];
				} elseif( !empty( $this->mInfo['customers_default_address_id'] ) ) {
					// somehow we lost our default address - let's be sure to clean this up
					$this->setDefaultAddress( NULL );
					unset( $this->mInfo['customers_default_address_id'] );
				}
			}
			return( $ret );
		}

		function setDefaultAddress( $pAddressId ) {
			$ret = NULL;
			if( $this->isValid() && ( is_numeric( $pAddressId ) || is_null( $pAddressId ) ) ) {
				$query = "UPDATE " . TABLE_CUSTOMERS . " SET `customers_default_address_id`=? WHERE `customers_id`=?";
				$this->mDb->query( $query, array( $pAddressId, $this->mCustomerId ) );
				$this->mInfo['customers_default_address_id'] = $pAddressId;
				$ret = TRUE;
			}
			return( $ret );
		}

		function addressExists( $pAddressId ) {
			global $gBitDb;
			$ret = FALSE;
			if( is_numeric( $pAddressId ) ) {
				$query = "SELECT count(*) FROM " . TABLE_ADDRESS_BOOK . " WHERE `address_book_id`=?";
				$ret = $gBitDb->GetOne( $query, array( $pAddressId ) );
			}
			return $ret;
		}

		function isValidAddress( $pAddressId ) {
			$ret = FALSE;
			$errors = array();
			if( !($ret = $this->verifyAddress( $pAddressId, $errors ) ) ) {
				unset( $errors['customers_id'] );
				unset( $errors['gender'] );
				if( !count( $errors ) ) {
					$ret = TRUE;
				}
			}
			return $ret;
		}

		function isAddressOwner( $pAddressId ) {
			$ret = FALSE;
			if( is_numeric( $pAddressId ) ) {
				$query = "select count(*) as `total` from " . TABLE_ADDRESS_BOOK . "
						  where `customers_id` = ? and `address_book_id` = ?";
				$ret = $this->mDb->getOne( $query, array( $this->mCustomerId, $pAddressId ) );
			}
			return $ret;
		}

		function getAddresses( $pCustomerId ) {
			global $gBitDb;
			$ret = NULL;
			if( is_numeric( $pCustomerId ) ) {
				$query = "select `address_book_id`, `entry_firstname` as `firstname`, `entry_lastname` as `lastname`,
									`entry_company` as `company`, `entry_street_address` as `street_address`,
									`entry_suburb` as `suburb`, `entry_city` as `city`, `entry_postcode` as `postcode`,
									`entry_state` as `state`, `entry_zone_id` as `zone_id`,
									`entry_country_id` as `country_id`, c.*
							from " . TABLE_ADDRESS_BOOK . " ab INNER JOIN " . TABLE_COUNTRIES . " c ON( ab.`entry_country_id`=c.`countries_id` )
							where `customers_id` = ?";

				if( $rs = $gBitDb->query( $query, array( $pCustomerId ) ) ) {
					$ret = $rs->GetRows();
				}
			}
			return $ret;
		}

		function getCountryZones( $pCountryId ) {
			global $gBitDb;
			$ret = array();
			if( is_numeric( $pCountryId ) ) {
				$query = "SELECT `zone_name` from " . TABLE_ZONES . " WHERE `zone_country_id` = ? ORDER BY `zone_name`";
				if( $rs = $gBitDb->query($query, array( $pCountryId ) ) ) {
					while (!$rs->EOF) {
						$ret[] = array('id' => $rs->fields['zone_name'], 'text' => $rs->fields['zone_name']);
						$rs->MoveNext();
					}
				}
			}
			return( $ret );
		}

		function getZoneCount( $pCountryId ) {
			$query = "SELECT count(*) as `total` from " . TABLE_ZONES . " WHERE `zone_country_id` = ?";
			return( $this->mDb->getOne( $query, array( $pCountryId ) ) );
		}

		function getZoneId( $pZone, $pCountryId ) {
			$zone_query =  "SELECT distinct `zone_id`
							FROM " . TABLE_ZONES . "
							WHERE `zone_country_id` = ? AND (UPPER(`zone_name`) = ? OR UPPER(`zone_code`) = ?)";
			return( $this->mDb->getOne($zone_query, array( $pCountryId, strtoupper( $pZone ), strtoupper( $pZone ) ) ) );
		}

		function getLanguage() {
			// no multi lang support for now...
			return 'en';
		}

	}
?>

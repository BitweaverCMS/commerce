<?php
// :vim:tabstop=4:
// +--------------------------------------------------------------------+
// | Copyright (c) 2005-2010 bitcommerce.org							|
// | http://www.bitcommerce.org											|
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
// | Portions Copyright (c) 2003 The zen-cart developers				|
// | Portions Copyright (c) 2003 osCommerce								|	
// +--------------------------------------------------------------------+
//

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceShoppingCart.php' );

class CommerceCustomer extends BitBase {
	var $mCustomerId;
	var $mCart;

	function CommerceCustomer( $pCustomerId ) {
		BitBase::BitBase();
		$this->mCart = new CommerceShoppingCart();
		$this->mCart->load();
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

	function expunge() {
		if( $this->isValid() && !($this->getOrdersHistory()) ) {
			$this->mDb->query( "DELETE FROM " . TABLE_ADDRESS_BOOK . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE `customers_basket_id` IN (SELECT `customers_basket_id` FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `customers_id`=?)", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_INTERESTS_MAP . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_WISHLIST . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_FILES_UPLOADED . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_PRODUCTS_NOTIFICATIONS . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_REVIEWS . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_WHOS_ONLINE . " WHERE `customer_id` = ?", array( $this->mCustomerId ) );
		}
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

	function getOrdersHistory( $pCustomerId=NULL ) {
		global $gBitDb;
		if( empty( $pCustomerId ) ) {
			$pCustomerId = $this->mCustomerId;
		}
		$query =   "SELECT o.`orders_id` AS `hash_key`, o.*, ot.`text` as `order_total`, s.`orders_status_name`
					FROM   " . TABLE_ORDERS . " o 
						INNER JOIN " . TABLE_ORDERS_TOTAL . "  ot ON (o.`orders_id` = ot.`orders_id`) 
						INNER JOIN " . TABLE_ORDERS_STATUS . " s ON (o.`orders_status` = s.`orders_status_id`)
					WHERE o.`customers_id` = ? AND ot.`class` = 'ot_total' AND s.`language_id` = ?
					ORDER BY `orders_id` DESC";

		return $gBitDb->getAssoc( $query, array( $pCustomerId, (int)$_SESSION['languages_id'] ) ); 
	}

	function getPurchaseStats( $pCustomerId ) {
		global $gBitDb;
		if( empty( $pCustomerId ) ) {
			$pCustomerId = $this->mCustomerId;
		}
		
		$bindVars[] = $pCustomerId;
		$bindVars[] = DEFAULT_ORDERS_STATUS_ID;

		$sql = "SELECT count( `orders_id` ), sum( `order_total` ) FROM " . TABLE_ORDERS . " WHERE `customers_id`=? AND `orders_status` > ?";
		return $gBitDb->getRow( $sql, $bindVars );
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
			$query = "SELECT * 
					  FROM " . TABLE_ADDRESS_BOOK . " cab
						INNER JOIN " . TABLE_COUNTRIES . " ccou ON (ccou.`countries_id`=cab.`entry_country_id`)
					  WHERE `address_book_id`=? $whereSql";
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

	function getAddresses( $pCustomerId=NULL ) {
		global $gBitDb;
		$ret = NULL;
		if( is_null( $pCustomerId ) && !empty( $this->mCustomerId ) ) {
			$pCustomerId = $this->mCustomerId;
		}
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

	function storeInterest( $pParamHash ) {
		if( !empty( $pParamHash['interests_name'] ) ) {
			$interestName = trim( substr( $pParamHash['interests_name'], 0, 64 ) );
			if( @BitBase::verifyId( $pParamHash['interests_id'] ) ) {
				$this->mDb->associateUpdate( TABLE_CUSTOMERS_INTERESTS, array( 'interests_name' => $interestName ), array( 'interests_id' => $pParamHash['interests_id'] ) );
			} else {
				$interestsId = $this->mDb->GenID( 'com_customers_interests_id_seq' );
				$this->mDb->associateInsert( TABLE_CUSTOMERS_INTERESTS, array( 'interests_name' => $interestName, 'interests_id' => $interestsId ) );
			}
		}
	}

	function expungeInterest( $pInterestsId ) {
		if( BitBase::verifyId( $pInterestsId ) ) {
			$this->mDb->StartTrans();
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_INTERESTS_MAP . " WHERE `interests_id`=?", array( $pInterestsId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_INTERESTS . " WHERE `interests_id`=?", array( $pInterestsId ) );
			$this->mDb->CompleteTrans();
		}
	}

	function getInterest( $pInterestsId ) {
		$ret = array();
		if( BitBase::verifyId( $pInterestsId ) ) {
			$ret = $this->mDb->getRow( "SELECT * FROM " . TABLE_CUSTOMERS_INTERESTS . " WHERE `interests_id`=?", array( $pInterestsId ) );
		}
		return $ret;
	}

	function getCustomerInterests( $pCustomersId ) {
		global $gBitDb;
		$ret = $gBitDb->getAssoc( "SELECT ci.`interests_id`, ci.`interests_name`, cim.`customers_id` AS `is_interested` FROM " . TABLE_CUSTOMERS_INTERESTS . " ci LEFT OUTER JOIN " . TABLE_CUSTOMERS_INTERESTS_MAP . " cim ON(ci.`interests_id`=cim.`interests_id` AND cim.`customers_id`=?) ORDER BY `interests_name`", array( $pCustomersId ) );
		return $ret;
	}

	function getUninterestedCustomers() {
		global $gBitDb;
		$sql = "SELECT uu.`user_id` AS `hash_key`, MAX(co.`orders_id`) AS `most_recent_order`, MAX(co.`date_purchased`) AS `most_recent_date`, COUNT(co.`orders_id`) AS `num_orders`, SUM(co.`order_total`) AS `total_revenue`
				FROM `".BIT_DB_PREFIX."users_users` uu 
					INNER JOIN " . TABLE_ORDERS . " co ON (uu.`user_id`=co.`customers_id`) 
					LEFT JOIN " . TABLE_CUSTOMERS_INTERESTS_MAP . " ccim ON (ccim.`customers_id`=co.`customers_id`) 
				WHERE co.`orders_status` > 0 AND ccim.`interests_id` IS NULL 
				GROUP BY (uu.`user_id`) 
				ORDER BY SUM(co.`order_total`) DESC";
		return $gBitDb->getAssoc( $sql );
	}

	function expungeCustomerInterest( $pParamHash ) {
		$ret = FALSE;
		if( @BitBase::verifyId( $pParamHash['customers_id'] ) && @BitBase::verifyId( $pParamHash['interests_id'] ) ) {
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_INTERESTS_MAP ." WHERE `customers_id`=? AND `interests_id`=?", array( $pParamHash['customers_id'], $pParamHash['interests_id'] ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function storeCustomerInterest( $pParamHash ) {
		$ret = FALSE;
		if( @BitBase::verifyId( $pParamHash['customers_id'] ) && @BitBase::verifyId( $pParamHash['interests_id'] ) ) {
			if( !($this->mDb->getOne( "SELECT `interests_id` FROM " . TABLE_CUSTOMERS_INTERESTS_MAP . " WHERE `customers_id`=? AND `interests_id`=?", array( $pParamHash['customers_id'], $pParamHash['interests_id'] ) ) ) ) {
				$this->mDb->query( "INSERT INTO " . TABLE_CUSTOMERS_INTERESTS_MAP ." (`customers_id`,`interests_id`) VALUES (?,?)", array( $pParamHash['customers_id'], $pParamHash['interests_id'] ) );
				$ret = TRUE;
			}
		}
		return $ret;
	}

	// Can be called statically, and is for user registration
	function getInterests() {
		global $gBitDb;
		return( $gBitDb->getAssoc( "SELECT `interests_id`, `interests_name` FROM `".BITCOMMERCE_DB_PREFIX."com_customers_interests` ORDER BY `interests_name` " ) );
	}
}
?>

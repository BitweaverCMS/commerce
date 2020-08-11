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

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceBase.php' );
require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceShoppingCart.php' );

class CommerceCustomer extends CommerceBase {
	public $mCustomerId;
	public $mCart;

	function __construct( $pCustomerId ) {
		parent::__construct();
		$this->mCart = new CommerceShoppingCart();
		$this->mCart->load();
		if( $this->verifyId( $pCustomerId ) ) {
			$this->mCustomerId = $pCustomerId;
		}
	}

	function isValid() {
		return $this->verifyId( $this->mCustomerId );
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

	function getBatchOrder() {
		$ret = array();
		if( $this->isValid() && ($batchArray = $this->getBatchHash()) ) {
			// first row is the header
			$headerRow = array_shift( $batchArray );

			// Maps spreadsheet header to address fields expected by shopping cart
			$batchKeys = array( 
				'product_id' => 'product_id',
				'quantity' => 'quantity',
				'delivery_first_name' => 'firstname',
				'delivery_last_name' => 'lastname',
				'delivery_company' => 'company',
				'delivery_address_1' => 'street_address',
				'delivery_address_2' => 'suburb',
				'delivery_city' => 'city',
				'delivery_state' => 'state',
				'delivery_postcode' => 'postcode',
				'delivery_country' => 'country',
				'delivery_telephone' => 'telephone',
				'shipping_method' => 'shipping_method',
				'discount_code' => 'discount_code',
				'file_name' => 'file_name',
				'shipping_method' => 'shipping_method',
				'product_id' => 'product_id',
				'option_id' => 'option_id',
			);
			/*
				  'country_id' => string '223' (length=3)
				  'zone_id' => string '1979' (length=4)
				  'countries_id' => string '223' (length=3)
				  'countries_name' => string 'United States' (length=13)
				  'countries_iso_code_2' => string 'US' (length=2)
				  'countries_iso_code_3' => string 'USA' (length=3)
				  'countries_iso_code_num' => string '840' (length=3)
				  'address_format_id' => string '2' (length=1)
			*/

			$batchIndex = array();
			$headerRowIndex = array_flip( $headerRow );
			foreach( array_keys( $batchKeys ) as $sheetKey ) {
				if( isset( $headerRowIndex[$sheetKey] ) ) {
					$batchIndex[$sheetKey] = $headerRowIndex[$sheetKey];
				}
			}
			$i = 1;
			foreach( $batchArray as $batchRow ) {
				$rowHash = array( 'customers_id' => $this->mCustomerId );
				foreach( $batchIndex as $key=>$index ) {
					$rowHash[$batchKeys[$key]] = $batchRow[$index];
				}
				if( ($countryString = trim( BitBase::getParameter( $rowHash, 'country' ) ))  && ($countryHash = zen_get_countries( $countryString ) ) ) {
					if( ($zoneString = trim( BitBase::getParameter( $rowHash, 'state' ) ))  && ($zoneHash = zen_get_zone_by_name( $countryHash['countries_id'], $zoneString ) ) ) {
					} else {
						$rowHash['error'][] = 'State could not be found';
					}
					$rowHash = array_merge( $rowHash, $countryHash, $zoneHash );
					// stupid cruft from com_address_book.country_id inconsistency
					$rowHash['country_id'] = $rowHash['countries_id'];
					// stupid cruft from address_display_inc.tpl
					$rowHash['country'] = $countryHash;
				} else {
					$rowHash['error'][] = 'Country could not be found';
				}
				// validate address has values
				foreach( $batchKeys as $sheetKey=>$addressKey ) {
					switch( $addressKey ) {
						case 'suburb':
						case 'company':
						case 'telephone':
						case 'discount_code':
						case 'file_name':
							// these are all optional
							break;
						default:
							if( empty( $rowHash[$addressKey] ) ) {
								$rowHash['error'][] = ucfirst( str_replace( '_', ' ', $addressKey ) ).' not found';
							}
							break;
					}
				}
				if( !empty( $rowHash['product_id'] ) ) {
					if( !empty( $rowHash['option_id'] ) ) {
						require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceProductManager.php' );
						$productManager = new CommerceProductManager();
						$optionsValuesIds = explode( ',', $rowHash['option_id'] );
						foreach( $optionsValuesIds as $optionsValuesId ) {
							$optionsValuesHash = $productManager->getOptionsValue( $optionsValuesId );
							$rowHash['product_options'][$optionsValuesHash['products_options_id']] = $optionsValuesId;
						}
						
					}
					$productIds = explode( ',', $rowHash['product_id'] );
					foreach( $productIds as $productId ) {
						if( $productObject = CommerceProduct::getCommerceObject( $productId ) ) {
							$rowHash['products'][$productId] = $productObject;
						}
					}
				}
				$rowHash['batch_index'] = $i;
				$ret[$i] = $rowHash;
				$i++;
			}
		}

		return $ret;
	}

	private function getBatchHash () {
		$ret = array();
		$bindVars = array( 'customers_id'=>$this->mCustomerId );
		if( $batchString = $this->mDb->getOne( "SELECT batch_hash FROM " . TABLE_CUSTOMERS_BATCH_ORDERS . " WHERE `customers_id`=?", $bindVars ) ) {
			$ret = unserialize( $batchString );
		}
		return $ret;
	}

	function expungeBatchItem( $pBatchIndex ) {
		if( $batchArray = $this->getBatchHash() ) {
			if( isset( $batchArray[$pBatchIndex] ) ) {
				unset( $batchArray[$pBatchIndex] );
				$this->storeBatchOrder( array_values( $batchArray ) );
			}
		}
	}

	function storeBatchOrder( $pBatchHash ) {
		if( $this->isValid() ) {
			if( !empty( $pBatchHash ) && is_array( $pBatchHash ) ) {
				$bindVars['batch_hash'] = serialize( $pBatchHash );
				if( $hasBatch = $this->mDb->getOne( "SELECT `customers_id` FROM " . TABLE_CUSTOMERS_BATCH_ORDERS . " WHERE `customers_id`=?", array( $this->mCustomerId ) ) ) {
					$bindVars['date_updated'] = $this->mDb->NOW();
					$this->mDb->associateUpdate( TABLE_CUSTOMERS_BATCH_ORDERS, $bindVars, array( 'customers_id' => $this->mCustomerId ) );
				} else {
					$bindVars['customers_id'] = $this->mCustomerId;
					$this->mDb->associateInsert( TABLE_CUSTOMERS_BATCH_ORDERS, $bindVars );
				}
			} else {
				$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_BATCH_ORDERS. " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			}
		}
	}

	function expunge() {
		if( $this->isValid() && !($this->getOrdersHistory()) ) {
			$this->mDb->query( "DELETE FROM " . TABLE_ADDRESS_BOOK . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE `customers_basket_id` IN (SELECT `customers_basket_id` FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `customers_id`=?)", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
			$this->mDb->query( "DELETE FROM " . TABLE_CUSTOMERS_BATCH_ORDERS. " WHERE `customers_id` = ?", array( $this->mCustomerId ) );
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

	function getOrdersHistory() {
		$ret = array();
		if( $this->isValid() ) {
			$ret = static::getOrdersHistoryById( $this->mCustomerId );
		}
		return $ret;
	}

	public static function getOrdersHistoryById( $pCustomerId ) {
		global $gBitDb;
		if( static::verifyId( $pCustomerId ) ) {
			$query =   "SELECT o.`orders_id` AS `hash_key`, o.*, ot.`text` as `order_total`, s.`orders_status_name`
						FROM   " . TABLE_ORDERS . " o 
							INNER JOIN " . TABLE_ORDERS_TOTAL . "  ot ON (o.`orders_id` = ot.`orders_id`) 
							INNER JOIN " . TABLE_ORDERS_STATUS . " s ON (o.`orders_status` = s.`orders_status_id`)
						WHERE o.`customers_id` = ? AND ot.`class` = 'ot_total' AND s.`language_id` = ?
						ORDER BY `orders_id` DESC";

			return $gBitDb->getAssoc( $query, array( $pCustomerId, (int)$_SESSION['languages_id'] ) ); 
		}
	}

	public static function getPurchaseStats( $pCustomerId ) {
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

	public static function syncBitUser( $pInfo ) {
		global $gBitDb;
		// bitcommerce customers table to bitweaver users_users table
		$syncFields = array( 'customers_id'=>'user_id', 'customers_nick'=>'login', 'customers_email_address'=>'email',
								'customers_firstname'=>'customers_firstname',
								'customers_lastname'=>'customers_lastname',
								'customers_gender'=>'customers_gender',
								'customers_dob'=>'customers_dob',
								'customers_telephone'=>'customers_telephone',
								'customers_fax'=>'customers_fax',
/* Fields in TABLE_CUSTOMERS:
'customers_default_address_id'=>'customers_default_address_id',
'customers_password'=>'customers_password',
'customers_newsletter'=>'customers_newsletter',
'customers_group_pricing'=>'customers_group_pricing',
'customers_email_format'=>'customers_email_format',
'customers_authorization'=>'customers_authorization',
'customers_referral' =>'customers_referral',
*/
					);
		if( $customer = $gBitDb->GetRow( "SELECT * FROM ".TABLE_CUSTOMERS." WHERE `customers_id`=?", array( $pInfo['user_id'] ) ) ) {
			foreach ( $syncFields AS $custKey=>$userKey ) {
				if( isset( $pInfo[$userKey] ) && ( $pInfo[$userKey] != $customer[$custKey] ) ) {
					$resyncHash[$custKey] = $pInfo[$userKey];
				}
			}
			if( !empty( $resyncHash ) ) {
				$gBitDb->associateUpdate( TABLE_CUSTOMERS, $resyncHash, array( 'customers_id' =>$customer['customers_id'] ) );
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
			if( $newUser->preRegisterVerify( $_REQUEST ) && $newUser->register( $_REQUEST ) ) {
				$gBitUser->login( $_REQUEST['email'], $_REQUEST['password'], FALSE, FALSE );
				$_REQUEST['customers_id'] = $gBitUser->mUserId;
				$this->mCustomerId = $gBitUser->mUserId;
				$this->syncBitUser( $gBitUser->mInfo );
				$this->load();
			} else {
				$gBitSmarty->assign_by_ref( 'userErrors', $newUser->mErrors );
			}
		}
		return( count( $gBitUser->mErrors ) == 0 );
	}



	//=-=-=-=-=-=-=-=-=-=-= ADDRESS FUNCTIONS

	function verifyAddress( &$pParamHash, &$errorHash ) {
		if( empty( $pParamHash['customers_id'] ) || !is_numeric( $pParamHash['customers_id'] ) ) {
			if( $this->isValid() ) {
				$pParamHash['address_store']['customers_id'] = $this->mCustomerId;
			} else {
				$errorHash['customers_id'] = tra( 'You must be registered to save addresses' );
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

		if( empty( $pParamHash['postcode'] ) || strlen( $pParamHash['postcode'] ) < ENTRY_POSTCODE_MIN_LENGTH ) {
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
			$gender = NULL;
			if( strlen( $pParamHash['gender'] ) == 1 ) {
				$gender = $pParamHash['gender'];
			} else {
				switch( $pParamHash['gender'] ) {
					case 'Mr':
						$gender = 'm';
						break;
					case 'Mrs':
					case 'Ms':
						$gender = 'f';
						break;
				}
			}
			$pParamHash['address_store']['entry_gender'] = $gender;
		}

		if( ACCOUNT_COMPANY == 'true' && !empty( $pParamHash['company'] ) ) {
			$pParamHash['address_store']['entry_company'] = $pParamHash['company'];
		}

		if( ACCOUNT_SUBURB == 'true' && !empty( $pParamHash['suburb'] ) ) {
			$pParamHash['address_store']['entry_suburb'] = $pParamHash['suburb'];
		} else {
			$pParamHash['address_store']['entry_suburb'] = NULL;
		}

		if( empty( $pParamHash['country_id'] ) || !is_numeric( $pParamHash['country_id'] ) || ($pParamHash['country_id'] < 1) ) {
			$errorHash['country_id'] = tra( 'You must select a country from the Countries pull down menu.' );
		} else {
			$pParamHash['address_store']['entry_country_id'] = $pParamHash['country_id'];
			if( $this->getZoneCount( $pParamHash['country_id'] ) ) {
				if( !empty( $pParamHash['state'] ) && is_numeric( $pParamHash['state'] ) && $zoneName = $this->getZoneName( $pParamHash['state'], $pParamHash['country_id'] ) ) {
					$pParamHash['address_store']['entry_zone_id'] = $pParamHash['state'];
					$pParamHash['address_store']['entry_state'] = $zoneName;
				} elseif( !empty( $pParamHash['state'] ) && $zoneId = $this->getZoneId( $pParamHash['state'], $pParamHash['country_id'] ) ) {
					$pParamHash['address_store']['entry_state'] = $pParamHash['state'];
					$pParamHash['address_store']['entry_zone_id'] = $zoneId;
				} else {
					$errorHash['state'] = tra( 'Please select a state from the States pull down menu.' );
				}
			} elseif( strlen( $pParamHash['state'] ) < ENTRY_STATE_MIN_LENGTH ) {
				$errorHash['state'] = tra( 'Your State must contain a minimum of ' . ENTRY_STATE_MIN_LENGTH . ' characters.' );
			} else {
				$pParamHash['address_store']['entry_state'] = $pParamHash['state'];
			}
		}

		return( count( $errorHash ) == 0 );
	}

	// store an address
	function storeAddress( &$pParamHash ) {
		if( $this->verifyAddress( $pParamHash, $this->mErrors ) ) {
			$process = true;
			if( isset( $pParamHash['address_book_id'] ) && self::verifyId( $pParamHash['address_book_id'] ) ) {
				$this->mDb->associateUpdate(TABLE_ADDRESS_BOOK, $pParamHash['address_store'], array( 'address_book_id' =>$pParamHash['address_book_id'] ) );
			} else {
				$this->mDb->associateInsert(TABLE_ADDRESS_BOOK, $pParamHash['address_store']);
				$pParamHash['address'] = zen_db_insert_id( TABLE_ADDRESS_BOOK, 'address_book_id' );
			}
			if( !$this->getDefaultAddressId() || !empty( $pParamHash['primary'] ) ) {
				$this->setDefaultAddress( $pParamHash['address'] );
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	function expungeAddress( $pAddressId, $pSecure = TRUE ) {
		$ret = NULL;
		if( is_numeric( $pAddressId ) && (!$pSecure || ($pSecure && $this->isValid())) ) {
			$bindVars = array( $pAddressId );
			$whereSql = '';
			if( $pSecure ) {
				$whereSql = " AND `customers_id`=?";
				array_push( $bindVars, $this->mCustomerId );
			}
			$query = "DELETE FROM " . TABLE_ADDRESS_BOOK . " cab WHERE `address_book_id`=? $whereSql";
			if( $rs = $this->mDb->query( $query, $bindVars ) ) {
				$ret = $this->mDb->Affected_Rows;
			}
		}
		return( $ret );
	}

	function getAddress( $pAddressId, $pSecure = TRUE ) {
		$ret = NULL;
		if( is_numeric( $pAddressId ) && (!$pSecure || ($pSecure && $this->isValid())) ) {
			$bindVars = array( $pAddressId );
			$whereSql = '';
			if( $pSecure ) {
				$whereSql = " AND cab.`customers_id`=?";
				array_push( $bindVars, $this->mCustomerId );
			}
			$query = "SELECT cab.*,ccou.*, cab.`address_book_id`=cu.`customers_default_address_id` AS `entry_primary`
					  FROM " . TABLE_ADDRESS_BOOK . " cab
						INNER JOIN " . TABLE_COUNTRIES . " ccou ON (ccou.`countries_id`=cab.`entry_country_id`)
						INNER JOIN " . TABLE_CUSTOMERS . " cu ON( cab.`customers_id`=cu.`customers_id` )
					  WHERE `address_book_id`=? $whereSql";
			if( $rs = $this->mDb->query( $query, $bindVars ) ) {
				$ret = $rs->fields;
				$ret['country_id'] = $ret['entry_country_id'];
			}
		}
		return( $ret );
	}

	function getDefaultAddressId() {
		$ret = NULL;
		if( $this->isValid() ) {
			if( empty( $this->mInfo ) ) {
				$this->load();
			}
			if( !empty( $this->mInfo['customers_default_address_id'] ) ) {
				if( $this->addressExists( $this->mInfo['customers_default_address_id'] ) ) {
					$ret = $this->mInfo['customers_default_address_id'];
				} else {
					// somehow we lost our default address - let's be sure to clean this up
					$this->setDefaultAddress( NULL );
					unset( $this->mInfo['customers_default_address_id'] );
				}
			}

			if( empty( $ret ) ) {
				// No default address, let's choose the most recently created
				if( $addresses = $this->getAddresses() ) {
					$newestAddress = current( $addresses );
					$ret = $newestAddress['address_book_id'];
					if( empty( $this->mInfo['customers_default_address_id'] ) ) {
						$this->setDefaultAddress( $ret );
					}
				}
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

	public static function addressExists( $pAddressId ) {
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
			$query = "select count(*) as `total` from " . TABLE_ADDRESS_BOOK . " where `customers_id` = ? and `address_book_id` = ?";
			$ret = $this->mDb->getOne( $query, array( $this->mCustomerId, $pAddressId ) );
		}
		return $ret;
	}

	public function getAddresses() {
		if( $this->isValid() ) {
			return static::getAddressesFromId( $this->mCustomerId );
		}
	}

	public static function getAddressesFromId( $pCustomerId ) {
		global $gBitDb;
		$ret = NULL;
		if( static::verifyId( $pCustomerId ) ) {
			$query = "SELECT `address_book_id`, `entry_firstname` as `firstname`, `entry_lastname` as `lastname`,
								`entry_company` as `company`, `entry_street_address` as `street_address`,
								`entry_suburb` as `suburb`, `entry_city` as `city`, `entry_postcode` as `postcode`,
								`entry_state` as `state`, `entry_zone_id` as `zone_id`,
								`entry_country_id` as `country_id`, co.*, ab.`address_book_id`=cu.`customers_default_address_id` AS `entry_primary`
						FROM " . TABLE_ADDRESS_BOOK . " ab 
							INNER JOIN " . TABLE_COUNTRIES . " co ON( ab.`entry_country_id`=co.`countries_id` )
							INNER JOIN " . TABLE_CUSTOMERS . " cu ON( ab.`customers_id`=cu.`customers_id` )
						WHERE ab.`customers_id` = ?
						ORDER BY `address_book_id` DESC";

			if( $rs = $gBitDb->query( $query, array( $pCustomerId ) ) ) {
				$ret = $rs->GetRows();
			}
		}
		return $ret;
	}

	function getStateInputHtml( $pAddressHash, $pSecure='shipping' ) {

		$stateInput = '';

		if( !($selectedCountry = BitBase::getIdParameter( $pAddressHash, 'country_id' ) ) ) {
			if( !($selectedCountry = BitBase::getIdParameter( $pAddressHash, 'entry_country_id' ) ) ) {
				$selectedCountry = defined( 'STORE_COUNTRY' ) ? STORE_COUNTRY : NULL;
			}
		}

		if( $this->isCommerceConfigActive( 'ACCOUNT_STATE' ) ) {
			if ( !empty( $selectedCountry ) ) {
				if( !($stateInput = zen_get_country_zone_list('state', $selectedCountry, (!empty( $pAddressHash['entry_zone_id'] ) ? $pAddressHash['entry_zone_id'] : ''), 'autocomplete="region"' )) ) { 
					$stateInput = zen_draw_input_field('state', zen_get_zone_name($selectedCountry, $pAddressHash['entry_zone_id'], $pAddressHash['entry_state']));
				}
			} else {
				$stateInput = zen_draw_input_field( 'state', NULL, 'autocomplete="'.$pSection.' region"' );
			}
		}
		return $stateInput;
	}

	function getCountryInputHtml( $pAddressHash, $pSection='shipping' ) {
		if( !($selectedCountry = BitBase::getIdParameter( $pAddressHash, 'country_id' ) ) ) {
			if( !($selectedCountry = BitBase::getIdParameter( $pAddressHash, 'entry_country_id' ) ) ) {
				$selectedCountry = defined( 'STORE_COUNTRY' ) ? STORE_COUNTRY : NULL;
			}
		}

		return zen_get_country_list('country_id', $selectedCountry, ' onchange="updateStates(this.value)" autocomplete="'.$pSection.' country"' );
	}

	public static function getCountryZones( $pCountryId ) {
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

	function getZoneName( $pZoneId, $pCountryId ) {
		$zone_query =  "SELECT distinct `zone_name`
						FROM " . TABLE_ZONES . "
						WHERE `zone_country_id` = ? AND `zone_id` = ?";
		return( $this->mDb->getOne($zone_query, array( $pCountryId, $pZoneId ) ) );
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

	public static function expungeInterest( $pInterestsId ) {
		global $gBitDb;
		if( BitBase::verifyId( $pInterestsId ) ) {
			$gBitDb->StartTrans();
			$gBitDb->query( "DELETE FROM " . TABLE_CUSTOMERS_INTERESTS_MAP . " WHERE `interests_id`=?", array( $pInterestsId ) );
			$gBitDb->query( "DELETE FROM " . TABLE_CUSTOMERS_INTERESTS . " WHERE `interests_id`=?", array( $pInterestsId ) );
			$gBitDb->CompleteTrans();
		}
	}

	public static function getInterest( $pInterestsId ) {
		global $gBitDb;
		$ret = array();
		if( BitBase::verifyId( $pInterestsId ) ) {
			$ret = $gBitDb->getRow( "SELECT * FROM " . TABLE_CUSTOMERS_INTERESTS . " WHERE `interests_id`=?", array( $pInterestsId ) );
		}
		return $ret;
	}

	public static function getCustomerInterests( $pCustomersId ) {
		global $gBitDb;
		$ret = $gBitDb->getAssoc( "SELECT ci.`interests_id`, ci.`interests_name`, cim.`customers_id` AS `is_interested` FROM " . TABLE_CUSTOMERS_INTERESTS . " ci LEFT OUTER JOIN " . TABLE_CUSTOMERS_INTERESTS_MAP . " cim ON(ci.`interests_id`=cim.`interests_id` AND cim.`customers_id`=?) ORDER BY `interests_name`", array( $pCustomersId ) );
		return $ret;
	}

	public static function getUninterestedCustomers() {
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

	public static function expungeCustomerInterest( $pParamHash ) {
		global $gBitDb;
		$ret = FALSE;
		if( @BitBase::verifyId( $pParamHash['customers_id'] ) && @BitBase::verifyId( $pParamHash['interests_id'] ) ) {
			$gBitDb->query( "DELETE FROM " . TABLE_CUSTOMERS_INTERESTS_MAP ." WHERE `customers_id`=? AND `interests_id`=?", array( $pParamHash['customers_id'], $pParamHash['interests_id'] ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function storeCustomerInterest( $pParamHash ) {
		global $gBitDb;
		$ret = FALSE;
		if( @BitBase::verifyId( $pParamHash['customers_id'] ) && @BitBase::verifyId( $pParamHash['interests_id'] ) ) {
			if( !($gBitDb->getOne( "SELECT `interests_id` FROM " . TABLE_CUSTOMERS_INTERESTS_MAP . " WHERE `customers_id`=? AND `interests_id`=?", array( $pParamHash['customers_id'], $pParamHash['interests_id'] ) ) ) ) {
				$gBitDb->query( "INSERT INTO " . TABLE_CUSTOMERS_INTERESTS_MAP ." (`customers_id`,`interests_id`) VALUES (?,?)", array( $pParamHash['customers_id'], $pParamHash['interests_id'] ) );
				$ret = TRUE;
			}
		}
		return $ret;
	}

	// Can be called statically, and is for user registration
	public static function getInterests() {
		global $gBitDb;
		return( $gBitDb->getAssoc( "SELECT `interests_id`, `interests_name` FROM `".BITCOMMERCE_DB_PREFIX."com_customers_interests` ORDER BY `interests_name` " ) );
	}
}
?>

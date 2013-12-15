<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
//	$Id$
//

require('includes/application_top.php');
require( BITCOMMERCE_PKG_PATH.'classes/CommerceStatistics.php');

if( empty( $_GET['list_order'] ) ) {
	$_GET['list_order'] = '';
}

$currencies = new currencies();

$error = false;
$processed = false;

if( BitBase::getParameter( $_GET, 'user_id' ) ) {
	switch ($action) {
		case 'status':
			if ($_GET['current'] == CUSTOMERS_APPROVAL_AUTHORIZATION) {
				$sql = "update " . TABLE_CUSTOMERS . " set `customers_authorization`=0 where `customers_id`='" . $_GET['user_id'] . "'";
			} else {
				$sql = "update " . TABLE_CUSTOMERS . " set `customers_authorization`='" . CUSTOMERS_APPROVAL_AUTHORIZATION . "' where `customers_id`='" . $_GET['user_id'] . "'";
			}
			$gBitDb->Execute($sql);
			$action = '';
			zen_redirect(zen_href_link_admin(FILENAME_CUSTOMERS, 'user_id=' . $_GET['user_id'] . '&page=' . $_GET['page'], 'NONSSL'));
			break;
		case 'update':
			$customers_id = zen_db_prepare_input($_GET['user_id']);
			$customers_firstname = zen_db_prepare_input($_POST['customers_firstname']);
			$customers_lastname = zen_db_prepare_input($_POST['customers_lastname']);
			$customers_email_address = zen_db_prepare_input($_POST['customers_email_address']);
			$customers_telephone = zen_db_prepare_input($_POST['customers_telephone']);
			$customers_fax = zen_db_prepare_input($_POST['customers_fax']);
			$customers_newsletter = zen_db_prepare_input($_POST['customers_newsletter']);
			$customers_group_pricing = zen_db_prepare_input($_POST['customers_group_pricing']);
			$customers_email_format = zen_db_prepare_input($_POST['customers_email_format']);
			$customers_gender = zen_db_prepare_input($_POST['customers_gender']);
			$customers_dob = zen_db_prepare_input($_POST['customers_dob']);

			$customers_authorization = zen_db_prepare_input($_POST['customers_authorization']);
			$customers_referral= zen_db_prepare_input($_POST['customers_referral']);

			if (CUSTOMERS_APPROVAL_AUTHORIZATION == 2 and $customers_authorization == 1) {
				$customers_authorization = 2;
				$messageStack->add_session(ERROR_CUSTOMER_APPROVAL_CORRECTION2, 'caution');
			}

			if (CUSTOMERS_APPROVAL_AUTHORIZATION == 1 and $customers_authorization == 2) {
				$customers_authorization = 1;
				$messageStack->add_session(ERROR_CUSTOMER_APPROVAL_CORRECTION1, 'caution');
			}

			$default_address_id = zen_db_prepare_input($_POST['default_address_id']);
			$entry_street_address = zen_db_prepare_input($_POST['entry_street_address']);
			$entry_suburb = zen_db_prepare_input($_POST['entry_suburb']);
			$entry_postcode = zen_db_prepare_input($_POST['entry_postcode']);
			$entry_city = zen_db_prepare_input($_POST['entry_city']);
			$entry_country_id = zen_db_prepare_input($_POST['entry_country_id']);

			$entry_company = zen_db_prepare_input($_POST['entry_company']);
			$entry_state = zen_db_prepare_input($_POST['entry_state']);
			if (isset($_POST['entry_zone_id'])) $entry_zone_id = zen_db_prepare_input($_POST['entry_zone_id']);

			if (strlen($customers_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
				$error = true;
				$entry_firstname_error = true;
			} else {
				$entry_firstname_error = false;
			}

			if (strlen($customers_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
				$error = true;
				$entry_lastname_error = true;
			} else {
				$entry_lastname_error = false;
			}

			if (ACCOUNT_DOB == 'true') {
				if (checkdate(substr(zen_date_raw($customers_dob), 4, 2), substr(zen_date_raw($customers_dob), 6, 2), substr(zen_date_raw($customers_dob), 0, 4))) {
					$entry_date_of_birth_error = false;
				} else {
					$error = true;
					$entry_date_of_birth_error = true;
				}
			}

			if (strlen($customers_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
				$error = true;
				$entry_email_address_error = true;
			} else {
				$entry_email_address_error = false;
			}

			if (!zen_validate_email($customers_email_address)) {
				$error = true;
				$entry_email_address_check_error = true;
			} else {
				$entry_email_address_check_error = false;
			}

			if (strlen($entry_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
				$error = true;
				$entry_street_address_error = true;
			} else {
				$entry_street_address_error = false;
			}

			if (strlen($entry_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
				$error = true;
				$entry_post_code_error = true;
			} else {
				$entry_post_code_error = false;
			}

			if (strlen($entry_city) < ENTRY_CITY_MIN_LENGTH) {
				$error = true;
				$entry_city_error = true;
			} else {
				$entry_city_error = false;
			}

			if ($entry_country_id == false) {
				$error = true;
				$entry_country_error = true;
			} else {
				$entry_country_error = false;
			}

			if (ACCOUNT_STATE == 'true') {
				if ($entry_country_error == true) {
					$entry_state_error = true;
				} else {
					$zone_id = 0;
					$entry_state_error = false;
					$check_value = $gBitDb->Execute("select count(*) as `total`
																			 from " . TABLE_ZONES . "
																			 where `zone_country_id` = '" . (int)$entry_country_id . "'");

					$entry_state_has_zones = ($check_value->fields['total'] > 0);
					if ($entry_state_has_zones == true) {
						$zone_query = $gBitDb->Execute("select `zone_id
																				from " . TABLE_ZONES . "
																				where `zone_country_id` = '" . (int)$entry_country_id . "'
																				and `zone_name` = '" . zen_db_input($entry_state) . "'");

						if ($zone_query->RecordCount() > 0) {
							$entry_zone_id = $zone_query->fields['zone_id'];
						} else {
							$error = true;
							$entry_state_error = true;
						}
					} else {
						if ($entry_state == false) {
							$error = true;
							$entry_state_error = true;
						}
					}
			 }
		}

		if (strlen($customers_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
			$error = true;
			$entry_telephone_error = true;
		} else {
			$entry_telephone_error = false;
		}

		$check_email = $gBitDb->Execute("select `customers_email_address`
																 from " . TABLE_CUSTOMERS . "
																 where `customers_email_address` = '" . zen_db_input($customers_email_address) . "'
																 and `customers_id` != '" . (int)$customers_id . "'");

		if ($check_email->RecordCount() > 0) {
			$error = true;
			$entry_email_address_exists = true;
		} else {
			$entry_email_address_exists = false;
		}

		if ($error == false) {

			$sql_data_array = array('customers_firstname' => $customers_firstname,
															'customers_lastname' => $customers_lastname,
															'customers_email_address' => $customers_email_address,
															'customers_telephone' => $customers_telephone,
															'customers_fax' => $customers_fax,
															'customers_group_pricing' => $customers_group_pricing,
															'customers_newsletter' => $customers_newsletter,
															'customers_email_format' => $customers_email_format,
															'customers_authorization' => $customers_authorization,
															'customers_referral' => $customers_referral
															);

			if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $customers_gender;
			if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = zen_date_raw($customers_dob);
			$gBitDb->associateInsert(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "'");

			$gBitDb->Execute("update " . TABLE_CUSTOMERS_INFO . "
										set `date_account_last_modified` = " . $gBitDb->mDb->sysTimeStamp . "
										where `customers_info_id` = '" . (int)$customers_id . "'");

			if ($entry_zone_id > 0) $entry_state = '';

			$sql_data_array = array('entry_firstname' => $customers_firstname,
															'entry_lastname' => $customers_lastname,
															'entry_street_address' => $entry_street_address,
															'entry_postcode' => $entry_postcode,
															'entry_city' => $entry_city,
															'entry_country_id' => $entry_country_id);

			if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $entry_company;
			if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $entry_suburb;

			if (ACCOUNT_STATE == 'true') {
				if ($entry_zone_id > 0) {
					$sql_data_array['entry_zone_id'] = $entry_zone_id;
					$sql_data_array['entry_state'] = '';
				} else {
					$sql_data_array['entry_zone_id'] = '0';
					$sql_data_array['entry_state'] = $entry_state;
				}
			}

			$gBitDb->associateInsert(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int)$customers_id . "' and address_book_id = '" . (int)$default_address_id . "'");

			zen_redirect(zen_href_link_admin(FILENAME_CUSTOMERS, zen_get_all_get_params(array('user_id', 'action')) . 'user_id=' . $customers_id, 'NONSSL'));

			} else if ($error == true) {
				$cInfo = new objectInfo($_POST);
				$processed = true;
			}

			break;
		default:
			$customers = $gBitDb->Execute("select c.`customers_id`, c.`customers_gender`, c.`customers_firstname`,
																				c.`customers_lastname`, c.`customers_dob`, c.`customers_email_address`,
																				a.`entry_company`, a.`entry_street_address`, a.`entry_suburb`,
																				a.`entry_postcode`, a.`entry_city`, a.`entry_state`, a.`entry_zone_id`,
																				a.`entry_country_id`, c.`customers_telephone`, c.`customers_fax`,
																				c.`customers_newsletter`, c.`customers_default_address_id`,
																				c.`customers_email_format`, c.`customers_group_pricing`,
																				c.`customers_authorization`, c.`customers_referral`
																from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a
																on c.`customers_default_address_id` = a.`address_book_id`
																and a.`customers_id` = c.`customers_id`
																WHERE c.`customers_id` = '" . (int)$_GET['user_id'] . "'");
							$cust = $customers->FetchRow();
	$cInfo = new objectInfo($cust);
		$gBitSmarty->assign( 'cInfo', $cInfo );
	}
} else {
	$stats = new CommerceStatistics();
	if( empty( $_REQUEST['interval'] ) ) {
		$_REQUEST['interval'] = '3 years';
	}
	$gBitSmarty->assign( 'customers', $stats->getCustomerActivity( $_REQUEST ) );
}
	
if( !empty( $cInfo ) ) {
	$mid = 'bitpackage:bitcommerce/admin_customer_edit.tpl';
	$title = tra('Edit Customer');
	if ($processed == true) {
		if ($cInfo->customers_group_pricing) {
			$group_query = $gBitDb->Execute("select `group_name`, `group_percentage` from " . TABLE_GROUP_PRICING . " where `group_id` = '" . $cInfo->customers_group_pricing . "'");
			echo $group_query->fields['group_name'].'&nbsp;'.$group_query->fields['group_percentage'].'%';
		}
	} else {
		$groupPricing[] = 'None';
		$groupPricing = array_merge( $groupPricing, $gBitDb->getAssoc("select `group_id`, `group_name`|| ' ' || `group_percentage` || '%' from " . TABLE_GROUP_PRICING) );
		$gBitSmarty->assign( 'groupPricing', $groupPricing );
		$gBitSmarty->assign( 'customerAuth', array( CUSTOMERS_AUTHORIZATION_0, CUSTOMERS_AUTHORIZATION_1, CUSTOMERS_AUTHORIZATION_2, CUSTOMERS_AUTHORIZATION_3 ) );
	}
} else {
	$mid = 'bitpackage:bitcommerce/admin_customer_list.tpl';
	$title = tra('Customers');
}
define( 'HEADING_TITLE', $title );

$gBitSystem->display( $mid, $title, array( 'display_mode' => 'admin' ));

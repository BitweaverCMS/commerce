<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers
// |
// | http://www.zen-cart.com/index.php
// |
// | Portions Copyright (c) 2003 osCommerce
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,
// | that is bundled with this package in the file LICENSE, and is
// | available through the world-wide-web at the following url:
// | http://www.zen-cart.com/license/2_0.txt.
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to
// | license@zen-cart.com so we can mail you a copy immediately.
// +----------------------------------------------------------------------+

//
// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());
//	define('DISPLAY_PAGE_PARSE_TIME', 'true');
// set the level of error reporting
if( defined( 'IS_LIVE' ) && IS_LIVE ) {
	error_reporting(E_ALL & ~E_NOTICE);
}

@ini_set("arg_separator.output","&");

if( empty( $_REQUEST['main_page'] ) ) {
	$_REQUEST['main_page'] = NULL;
}

define( 'BITCOMMERCE_ADMIN', FALSE );

require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_user_inc.php' );


// Sanitize get parameters in the url
if( isset($_GET['products_id']) ) $_GET['products_id'] = preg_replace('/[^0-9a-f:]/', '', $_GET['products_id']);
if (isset($_REQUEST['manufacturers_id'])) $_REQUEST['manufacturers_id'] = preg_replace('/[^0-9]/', '', $_REQUEST['manufacturers_id']);
if (isset($_REQUEST['cPath'])) $_REQUEST['cPath'] = preg_replace('/[^0-9_]/', '', $_REQUEST['cPath']);
if (isset($_REQUEST['main_page'])) $_REQUEST['main_page'] = preg_replace('/[^0-9a-zA-Z_]/', '', $_REQUEST['main_page']);

// navigation history
if (!isset($_SESSION['navigation'])) {
	$_SESSION['navigation'] = new navigationHistory;
}
$_SESSION['navigation']->add_current_page();

// Down for maintenance module
if( DOWN_FOR_MAINTENANCE=='true' && !strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])){
	//	if (EXCLUDE_ADMIN_IP_FOR_MAINTENANCE != $_SERVER['REMOTE_ADDR']){
	if( $_REQUEST['main_page'] != DOWN_FOR_MAINTENANCE_FILENAME) {
		zen_redirect(zen_href_link(DOWN_FOR_MAINTENANCE_FILENAME));
	}
}

// do not let people get to down for maintenance page if not turned on
if (DOWN_FOR_MAINTENANCE=='false' and $_REQUEST['main_page'] == DOWN_FOR_MAINTENANCE_FILENAME) {
	zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

// recheck customer status for authorization
if (CUSTOMERS_APPROVAL_AUTHORIZATION > 0 && ($_SESSION['customer_id'] != '' and $_SESSION['customers_authorization'] != '0')) {
	$check_customer_query = "SELECT customers_id, customers_authorization FROM " . TABLE_CUSTOMERS . " WHERE customers_id = ?";
	$check_customer = $gBitDb->Execute($check_customer_query, array( $_SESSION['customer_id'] ) );
	$_SESSION['customers_authorization'] = $check_customer->fields['customers_authorization'];
}

// customer login status
// 0 = normal shopping
// 1 = Login to shop
// 2 = Can browse but no prices
// verify display of prices
switch (true) {
	case (DOWN_FOR_MAINTENANCE == 'true'):
		// if not down for maintenance check login status
		break;
	case ($_REQUEST['main_page'] == FILENAME_LOGOFF):
		break;
	case (CUSTOMERS_APPROVAL == '1' and $_SESSION['customer_id'] == ''):
		// customer must be logged in to browse
		//die('I see ' . $_REQUEST['main_page'] . ' vs ' . FILENAME_LOGIN);
		if ($_REQUEST['main_page'] != FILENAME_LOGIN and $_REQUEST['main_page'] != FILENAME_CREATE_ACCOUNT ) {
			if (!isset($_REQUEST['set_session_login'])) {
				$_REQUEST['set_session_login'] = 'true';
				$_SESSION['navigation']->set_snapshot();
			}
			zen_redirect(FILENAME_LOGIN);
		}
		break;
	case (CUSTOMERS_APPROVAL == '2' and $_SESSION['customer_id'] == ''):
		// customer may browse but no prices
		break;
	default:
		// proceed normally
		break;
	}


// customer authorization status
// 0 = normal shopping
// 1 = customer authorization to shop
// 2 = customer authorization pending can browse but no prices
// verify display of prices
switch (true) {
	case (DOWN_FOR_MAINTENANCE == 'true'):
		// if not down for maintenance check login status
		break;
	case ($_REQUEST['main_page'] == FILENAME_LOGOFF or $_REQUEST['main_page'] == FILENAME_PRIVACY or $_REQUEST['main_page'] == FILENAME_PASSWORD_FORGOTTEN or $_REQUEST['main_page'] == FILENAME_CONTACT_US or $_REQUEST['main_page'] == FILENAME_CONDITIONS or $_REQUEST['main_page'] == FILENAME_SHIPPING or $_REQUEST['main_page'] == FILENAME_UNSUBSCRIBE):
		break;
	case (CUSTOMERS_APPROVAL_AUTHORIZATION == '1' and $_SESSION['customer_id'] == ''):
		// customer must be logged in to browse
		if ($_REQUEST['main_page'] != FILENAME_LOGIN and $_REQUEST['main_page'] != FILENAME_CREATE_ACCOUNT ) {
			if (!isset($_REQUEST['set_session_login'])) {
				$_REQUEST['set_session_login'] = 'true';
				$_SESSION['navigation']->set_snapshot();
			}
			zen_redirect(FILENAME_LOGIN);
		}
		break;
	case (CUSTOMERS_APPROVAL_AUTHORIZATION == '2' and $_SESSION['customer_id'] == ''):
		// customer must be logged in to browse
/*
		if ($_REQUEST['main_page'] != FILENAME_LOGIN and $_REQUEST['main_page'] != FILENAME_CREATE_ACCOUNT ) {
			if (!isset($_REQUEST['set_session_login'])) {
				$_REQUEST['set_session_login'] = 'true';
				$_SESSION['navigation']->set_snapshot();
			}
			zen_redirect(FILENAME_LOGIN);
		}
*/
		break;
	case (CUSTOMERS_APPROVAL_AUTHORIZATION == '1' and $_SESSION['customers_authorization'] != '0'):
		// customer is pending approval
		// customer must be logged in to browse
		if ($_REQUEST['main_page'] != CUSTOMERS_AUTHORIZATION_FILENAME) {
			zen_redirect(zen_href_link(CUSTOMERS_AUTHORIZATION_FILENAME));
		}
		break;
	case (CUSTOMERS_APPROVAL_AUTHORIZATION == '2' and $_SESSION['customers_authorization'] != '0'):
		// customer may browse but no prices
		break;
	default:
		// proceed normally
		break;
}

// infobox
require_once(DIR_FS_CLASSES . 'boxes.php');

// initialize the message stack for output messages
require_once(DIR_FS_CLASSES . 'message_stack.php');
$messageStack = new messageStack;
$gBitSmarty->assign( 'messageStack', $messageStack );

// Shopping cart actions
if (isset($_REQUEST['action'])) {

	if (DISPLAY_CART == 'true') {
		$goto =	FILENAME_SHOPPING_CART;
		$parameters = array('action', 'cPath', 'products_id', 'pid', 'main_page');
	} else {
		$goto = $_REQUEST['main_page'];
		if ($_REQUEST['action'] == 'buy_now') {
			$parameters = array('action');
		} else {
			$parameters = array('action', 'pid', 'main_page');
		}
	}


	switch( $_REQUEST['action'] ) {
		// customer adds a product FROM the products page
		case 'add_product' :
			if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
				// verify attributes and quantity first
				$the_list = '';
				if (isset($_REQUEST['id'])) {
					while(list($key,$value) = each($_REQUEST['id'])) {
						if( is_array( $value ) ) {
							$value = current( $value );
						}
						$check = zen_get_attributes_valid($_POST['products_id'], $key, $value);
						if ($check == false) {
							// zen_get_products_name($_POST['products_id']) .
							$the_list .= '<div class="alert alert-danger">' . TEXT_ERROR_OPTION_FOR . TEXT_INVALID_SELECTION_LABELED . ' : ' . $key .' = '. $value . '</div>';
						}
					}
				}

				if ($the_list != '') {
					$messageStack->add('header', ERROR_CORRECTIONS_HEADING . $the_list, 'error');
				} else {
					// process normally

					// iii 030813 added: File uploading: save uploaded files with unique file names
					$cartAttributes = !empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;
					if( !empty( $_REQUEST['number_of_uploads'] ) ) {
						require_once(DIR_FS_CLASSES . 'upload.php');
						for ($i = 1, $n = $_REQUEST['number_of_uploads']; $i <= $n; $i++) {
							if (zen_not_null($_FILES['id']['tmp_name'][TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]]) and ($_FILES['id']['tmp_name'][TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]] != 'none')) {
								$products_options_file = new upload('id');
								$products_options_file->set_destination(DIR_FS_UPLOADS);
								if ($products_options_file->parse(TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i])) {
									$products_image_extention = substr($products_options_file->filename, strrpos($products_options_file->filename, '.'));
									if ($_SESSION['customer_id']) {
										$gBitDb->Execute("insert into " . TABLE_FILES_UPLOADED . " (sesskey, customers_id, files_uploaded_name) values('" . session_id() . "', '" . $_SESSION['customer_id'] . "', '" . zen_db_input($products_options_file->filename) . "')");
									} else {
										$gBitDb->Execute("insert into " . TABLE_FILES_UPLOADED . " (sesskey, files_uploaded_name) values('" . session_id() . "', '" . zen_db_input($products_options_file->filename) . "')");
									}
									$insert_id = zen_db_insert_id( TABLE_FILES_UPLOADED, 'files_uploaded_id' );
									$cartAttributes[TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]] = $insert_id . ". " . $products_options_file->filename;
									$products_options_file->set_filename("$insert_id" . $products_image_extention);
									if (!($products_options_file->save())) {
										break 2;
									}
								} else {
									break 2;
								}
							} else { // No file uploaded -- use previous value
								$cartAttributes[TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]] = $_REQUEST[TEXT_PREFIX . UPLOAD_PREFIX . $i];
							}
						}
					}
					$gBitCustomer->mCart->addToCart( $_POST['products_id'], $_POST['cart_quantity'], $cartAttributes );
				}
			}

			if ($the_list == '') {
				// no errors
				zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
			} else {
				// errors - display popup message
			}
			break;
		// performed by the 'buy now' button in product listings and review page
		case 'buy_now' :			
			if (isset($_REQUEST['products_id']) && $gBitProduct->isValid() ) {
				if (zen_has_product_attributes($_REQUEST['products_id'])) {
					zen_redirect( $gBitProduct->getDisplayUrl() );
				} else {
					$gBitCustomer->mCart->addToCart($_REQUEST['products_id'], $gBitProduct->getBuyNowQuantity() );
				}
			}
			zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
			break;

		case 'multiple_products_add_product':
			while ( list( $key, $qty ) = each($_REQUEST['products_id']) ) {
				if( !zen_has_product_attributes( $_REQUEST['products_id'] ) ) {
					$gBitCustomer->mCart->addToCart($prodId, $qty );
				}
			}
			zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
			break;

		case 'notify' :
			if( $gBitUser->isRegistered() ) {
				if (isset($_REQUEST['products_id'])) {
					$notify = $_REQUEST['products_id'];
				} elseif (isset($_REQUEST['notify'])) {
					$notify = $_REQUEST['notify'];
				} elseif (isset($_REQUEST['notify'])) {
					$notify = $_REQUEST['notify'];
				} else {
					zen_redirect(zen_href_link($_REQUEST['main_page'], zen_get_all_get_params(array('action', 'notify', 'main_page'))));
				}
				if (!is_array($notify)) {
					$notify = array($notify);
				}
				for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
					$gBitProduct->storeNotification( $gBitUser->mUserId, $notify[$i] );
				}
				zen_redirect(zen_href_link($_REQUEST['main_page'], zen_get_all_get_params(array('action', 'notify', 'main_page'))));
			} else {
				$_SESSION['navigation']->set_snapshot();
				zen_redirect(FILENAME_LOGIN);
			}
			break;
		case 'notify_remove' :
			if( $gBitUser->isRegistered() && $gBitProduct->isValid() ) {
				$gBitProduct->expungeNotification( $gBitUser->mUserId );
				zen_redirect(zen_href_link($_REQUEST['main_page'], zen_get_all_get_params(array('action', 'main_page'))));
			} else {
				$_SESSION['navigation']->set_snapshot();
				zen_redirect(FILENAME_LOGIN);
			}
			break;

		case 'add_wishlist' :	
			 // Add product to the wishlist
			if (ereg('^[0-9]+$', $_REQUEST['products_id'])) {
				if	($_REQUEST['products_id']) {
					$gBitDb->Execute("delete FROM " . TABLE_WISHLIST . " WHERE `products_id` = '" . $_REQUEST['products_id'] . "' and `customers_id` = '" . $_SESSION['customer_id'] . "'");
					$gBitDb->Execute("insert into " . TABLE_WISHLIST . " (`customers_id`, `products_id`, `products_model`, `products_name`, `products_price`) values ('" . $_SESSION['customer_id'] . "', '" . $_REQUEST['products_id'] . "', '" . $products_model . "', '" . $products_name . "', '" . $products_price . "' )");
				}
			}
			zen_redirect(zen_href_link(FILENAME_WISHLIST));
					break;


		case 'wishlist_add_cart':
			// Add wishlist item to the cart
			reset ($lvnr);
			reset ($lvanz);
			while (list($key,$elem) =each ($lvnr)) {
				(list($key1,$elem1) =each ($lvanz));
				$gBitDb->Execute("update " . TABLE_WISHLIST . " SET `products_quantity`=$elem1 WHERE `customers_id`= '" . $_SESSION['customer_id'] . "' AND `products_id`=$elem");
				$gBitDb->Execute("delete FROM " . TABLE_WISHLIST . " WHERE `customers_id`= '" . $_SESSION['customer_id'] . "' AND `products_quantity`='999'");
				$products_in_wishlist = $gBitDb->Execute("SELECT * FROM " . TABLE_WISHLIST . " WHERE `customers_id`= '" . $_SESSION['customer_id'] . "' AND `products_id` = $elem AND `products_quantity` <> '0'");

				while (!$products_in_wishlist->EOF) {
					$cart->addToCart($products_in_wishlist->fields['products_id'], $products_in_wishlist->fields['products_quantity']);
				}
			}
			reset ($lvanz);
			zen_redirect(zen_href_link(FILENAME_WISHLIST));
			break;


		case 'remove_wishlist' :
			// remove item FROM the wishlist
			$gBitDb->Execute("delete FROM " . TABLE_WISHLIST . " WHERE `products_id` = '" . $HTTP_GET_VARS['pid'] . "' and `customers_id` = '" . $_SESSION['customer_id'] . "'");
			zen_redirect(zen_href_link(FILENAME_WISHLIST));
			break;
	}
}

// include the password crypto functions
require_once(DIR_FS_FUNCTIONS . 'password_funcs.php');

// include validation functions (right now only email address)
require_once(DIR_FS_FUNCTIONS . 'validations.php');

// split-page-results
require_once(DIR_FS_CLASSES . 'split_page_results.php');

// only process once per session
// this is processed in the admin for dates that expire as being worked on
if( !empty( $_SESSION['update_expirations'] ) && $_SESSION['update_expirations'] != 'true') {
	// auto expire special products
	require_once(DIR_FS_FUNCTIONS . 'specials.php');
	zen_start_specials();
	zen_expire_specials();

	// auto expire featured products
	require_once(DIR_FS_FUNCTIONS . 'featured.php');
	zen_start_featured();
	zen_expire_featured();

	// auto expire salemaker sales
	require_once(DIR_FS_FUNCTIONS . 'salemaker.php');
	zen_start_salemaker();
	zen_expire_salemaker();

	$_SESSION['update_expirations'] = 'true';
}

// calculate category path
if (isset($_REQUEST['cPath'])) {
	$cPath = $_REQUEST['cPath'];
} elseif (isset($_REQUEST['products_id']) && !zen_check_url_get_terms()) {
	$cPath = zen_get_product_path($_REQUEST['products_id']);
} else {
	if (SHOW_CATEGORIES_ALWAYS == '1' && !zen_check_url_get_terms()) {
		$show_welcome = 'true';
		$cPath = (defined('CATEGORIES_START_MAIN') ? CATEGORIES_START_MAIN : '');
	} else {
		$show_welcome = 'false';
		$cPath = '';
	}
}

if (zen_not_null($cPath)) {
	$cPath_array = zen_parse_category_path($cPath);
	$cPath = implode('_', $cPath_array);
	$current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
} else {
	$current_category_id = 0;
	$cPath_array = array();
}


// set which precautions should be checked
define('WARN_INSTALL_EXISTENCE', 'true');
define('WARN_CONFIG_WRITEABLE', 'true');
define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
define('WARN_SQL_CACHE_DIRECTORY_NOT_WRITEABLE', 'true');
define('WARN_SESSION_AUTO_START', 'true');
define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');
define('WARN_DATABASE_VERSION_PROBLEM','true');

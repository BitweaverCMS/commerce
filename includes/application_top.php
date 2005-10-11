<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: application_top.php,v 1.26 2005/10/11 03:50:10 spiderr Exp $
//
// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
//  define('DISPLAY_PAGE_PARSE_TIME', 'true');
// set the level of error reporting
// if( defined( 'IS_LIVE' ) ) {
  	error_reporting(E_ALL & ~E_NOTICE);
// }

  @ini_set("arg_separator.output","&");

if( empty( $_REQUEST['main_page'] ) ) {
	$_REQUEST['main_page'] = NULL;
}

require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
require_once( BITCOMMERCE_PKG_PATH.'includes/functions/html_output.php');
require_once( BITCOMMERCE_PKG_PATH.'includes/functions/functions_general.php');


// define general functions used application-wide
  require_once(DIR_FS_FUNCTIONS . 'functions_email.php');

// load extra functions
  require_once(DIR_FS_MODULES . 'extra_functions.php');






// set host_address once per session to reduce load on server
  if( empty( $_SESSION['customers_host_address'] ) ) {
    if (SESSION_IP_TO_HOST_ADDRESS == 'true') {
      $_SESSION['customers_host_address']= gethostbyaddr($_SERVER['REMOTE_ADDR']);
    } else {
      $_SESSION['customers_host_address'] = OFFICE_IP_TO_HOST_ADDRESS;
    }
  }

// verify the ssl_session_id if the feature is enabled
  if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == 'true') && ($session_started == true) ) {
    $ssl_session_id = $_SERVER['SSL_SESSION_ID'];
    if (!$_SESSION['SSL_SESSION_ID']) {
      $_SESSION['SESSION_SSL_ID'] = $ssl_session_id;
    }

    if ($_SESSION['SESSION_SSL_ID'] != $ssl_session_id) {
      zen_session_destroy();
      zen_redirect(zen_href_link(FILENAME_SSL_CHECK));
    }
  }

// verify the browser user agent if the feature is enabled
  if (SESSION_CHECK_USER_AGENT == 'True') {
    $http_user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (!$_SESSION['SESSION_USER_AGENT']) {
      $_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
    }

    if ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
      zen_session_destroy();
      zen_redirect(FILENAME_LOGIN);
    }
  }

// verify the IP address if the feature is enabled
  if (SESSION_CHECK_IP_ADDRESS == 'True') {
    $ip_address = zen_get_ip_address();
    if (!$_SESSION['SESSION_IP_ADDRESS']) {
      $_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
    }

    if ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
      zen_session_destroy();
      zen_redirect(FILENAME_LOGIN);
    }
  }

// include the mail classes
  require(DIR_FS_CLASSES . 'mime.php');
  require(DIR_FS_CLASSES . 'email.php');

// Set theme related directories
  $sql = "SELECT `template_dir`
          FROM " . TABLE_TEMPLATE_SELECT .
         " WHERE `template_language` = '0'";

  $template_query = $db->Execute($sql);

  $template_dir = $template_query->fields['template_dir'];

  $sql = "SELECT `template_dir`
          FROM " . TABLE_TEMPLATE_SELECT .
         " WHERE `template_language` = '" . $_SESSION['languages_id'] . "'";

  $template_query = $db->Execute($sql);

  if ($template_query->RecordCount() > 0) {
      $template_dir = $template_query->fields['template_dir'];
  }
//if (template_switcher_available=="YES") $template_dir = templateswitch_custom($current_domain);
  define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES . $template_dir . '/');

  define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE . 'images/');
  define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');

  require(DIR_FS_CLASSES . 'template_func.php');
  $template = new template_func(DIR_WS_TEMPLATE);

// include the language translations
// include template specific language files
  if (file_exists(DIR_WS_LANGUAGES . $template_dir . '/' . $gBitCustomer->getLanguage() . '.php')) {
    $template_dir_SELECT = $template_dir . '/';
//die('Yes ' . DIR_WS_LANGUAGES . $template_dir . '/' . $gBitCustomer->getLanguage() . '.php');
  } else {
//die('NO ' . DIR_WS_LANGUAGES . $template_dir . '/' . $gBitCustomer->getLanguage() . '.php');
    $template_dir_SELECT = '';
  }

$langFile = DIR_WS_LANGUAGES . $template_dir_SELECT . $gBitCustomer->getLanguage() . '.php';
if( !file_exists( $langFile ) ) {
	$langFile = DIR_WS_LANGUAGES . $template_dir_SELECT . 'en.php';
}
require( $langFile );

// include the extra language translations
  include(DIR_FS_MODULES . 'extra_definitions.php');

// currency
  if( empty( $_SESSION['currency'] ) || isset($_REQUEST['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $_SESSION['currency']) ) ) {
    if (isset($_REQUEST['currency'])) {
      if ( zen_currency_exists($_REQUEST['currency'])) {
	   $_SESSION['currency'] = $_REQUEST['currency'];
      } else {
        $_SESSION['currency'] = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
      }
	}
  }

// Sanitize get parameters in the url
  if( isset($_GET['products_id']) ) $_GET['products_id'] = ereg_replace('[^0-9a-f:]', '', $_GET['products_id']);
  if (isset($_REQUEST['manufacturers_id'])) $_REQUEST['manufacturers_id'] = ereg_replace('[^0-9]', '', $_REQUEST['manufacturers_id']);
  if (isset($_REQUEST['cPath'])) $_REQUEST['cPath'] = ereg_replace('[^0-9_]', '', $_REQUEST['cPath']);
  if (isset($_REQUEST['main_page'])) $_REQUEST['main_page'] = ereg_replace('[^0-9a-zA-Z_]', '', $_REQUEST['main_page']);

	clean_input( $_REQUEST );

function clean_input( &$pArray ) {
  while (list($key, $value) = each($pArray)) {
  	if( is_array( $pArray[$key] ) ) {
		clean_input( $pArray );
	} else {
	    $pArray[$key] = ereg_replace('[<>]', '', $value);
	}
  }
}

// validate products_id for search engines and bookmarks, etc.
  if (isset( $_GET['products_id'] ) && is_numeric( $_GET['products_id'] ) && $_SESSION['check_valid'] != 'false') {
    $check_valid = zen_products_id_valid($_GET['products_id']);
    if (!$check_valid) {
      $_GET['main_page'] = zen_get_info_page( $_GET['products_id'] );
      // do not recheck redirect
      $_SESSION['check_valid'] = 'false';
      zen_redirect(zen_href_link($_REQUEST['main_page'], 'products_id=' . $_GET['products_id']));
    }
  } else {
    $_SESSION['check_valid'] = 'true';
  }

// navigation history
  if (!isset($_SESSION['navigation'])) {
    $_SESSION['navigation'] = new navigationHistory;
  }
  $_SESSION['navigation']->add_current_page();
// Down for maintenance module
  if (!strstr(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])){
//  if (EXCLUDE_ADMIN_IP_FOR_MAINTENANCE != $_SERVER['REMOTE_ADDR']){
    if (DOWN_FOR_MAINTENANCE=='true' and $_REQUEST['main_page'] != DOWN_FOR_MAINTENANCE_FILENAME) zen_redirect(zen_href_link(DOWN_FOR_MAINTENANCE_FILENAME));
  }

// do not let people get to down for maintenance page if not turned on
  if (DOWN_FOR_MAINTENANCE=='false' and $_REQUEST['main_page'] == DOWN_FOR_MAINTENANCE_FILENAME) {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
  }

// recheck customer status for authorization
  if (CUSTOMERS_APPROVAL_AUTHORIZATION > 0 && ($_SESSION['customer_id'] != '' and $_SESSION['customers_authorization'] != '0')) {
    $check_customer_query = "SELECT customers_id, customers_authorization
                             FROM " . TABLE_CUSTOMERS . "
                             WHERE customers_id = '" . $_SESSION['customer_id'] . "'";
    $check_customer = $db->Execute($check_customer_query);
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













// Shopping cart actions
  if (isset($_REQUEST['action'])) {
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
    if ($session_started == false) {
      zen_redirect(zen_href_link(FILENAME_COOKIE_USAGE));
    }

    if (DISPLAY_CART == 'true') {
      $goto =  FILENAME_SHOPPING_CART;
      $parameters = array('action', 'cPath', 'products_id', 'pid', 'main_page');
    } else {
      $goto = $_REQUEST['main_page'];
      if ($_REQUEST['action'] == 'buy_now') {
        $parameters = array('action');
      } else {
        $parameters = array('action', 'pid', 'main_page');
      }
    }


    switch ($_REQUEST['action']) {
      // customer wants to update the product quantity in their shopping cart
      // delete checkbox or 0 quantity removes FROM cart
      case 'update_product' : for ($i=0, $n=sizeof($_REQUEST['products_id']); $i<$n; $i++) {
                                $adjust_max= 'false';

//                                if ( in_array($_POST['products_id'][$i], (is_array($_REQUEST['cart_delete']) ? $_REQUEST['cart_delete'] : array())) or $_REQUEST['cart_quantity'][$i]==0) {
                                if ( in_array($_POST['products_id'][$i], (is_array($_REQUEST['cart_delete']) ? $_REQUEST['cart_delete'] : array())) or $_POST['cart_quantity'][$i]==0) {
                                  $_SESSION['cart']->remove($_POST['products_id'][$i]);
                                } else {
                                  $add_max = zen_get_products_quantity_order_max($_POST['products_id'][$i]);
                                  $cart_qty = $_SESSION['cart']->in_cart_mixed($_POST['products_id']);
                                  $new_qty = $_POST['cart_quantity'][$i];
                                  if (($add_max == 1 and $cart_qty == 1)) {
                                    // do not add
                                    $adjust_max= 'true';
                                  } else {
                                    // adjust quantity if needed
                                    if (($new_qty + $cart_qty > $add_max) and $add_max != 0) {
                                      $adjust_max= 'true';
                                      $new_qty = $add_max - $cart_qty;
                                    }
                                    $attributes = ($_REQUEST['id'][$_POST['products_id'][$i]]) ? $_REQUEST['id'][$_POST['products_id'][$i]] : '';
                                    $_SESSION['cart']->add_cart($_POST['products_id'][$i], $new_qty, $attributes, false);
                                  }
                                    if ($adjust_max == 'true') {
                                      $messageStack->add_session('header', ERROR_MAXIMUM_QTY . ' - ' . zen_get_products_name($_POST['products_id'][$i]), 'caution');
                                    }
                                }
                              }


                              zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
                              break;




	// remove individual products FROM cart
      case 'remove_product':
	  	if (isset($_REQUEST['product_id']) && zen_not_null($_REQUEST['product_id'])) $_SESSION['cart']->remove($_REQUEST['product_id']);
		zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
		break;



      // customer adds a product FROM the products page
      case 'add_product' :
                              if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
// verify attributes and quantity first
      $the_list = '';
      $adjust_max= 'false';
    if (isset($_REQUEST['id'])) {
      while(list($key,$value) = each($_REQUEST['id'])) {
        $check = zen_get_attributes_valid($_POST['products_id'], $key, $value);
        if ($check == false) {
          // zen_get_products_name($_POST['products_id']) .
          $the_list .= TEXT_ERROR_OPTION_FOR . '<span class="alertBlack">' . zen_options_name($key) . '</span>' . TEXT_INVALID_SELECTION_LABELED . '<span class="alertBlack">' . (zen_values_name($value) == 'TEXT' ? TEXT_INVALID_USER_INPUT : zen_values_name($value)) . '</span>' . '<br />';
        }
      }
    }

// verify qty to add
    $add_max = zen_get_products_quantity_order_max($_POST['products_id']);
    $cart_qty = $_SESSION['cart']->in_cart_mixed($_POST['products_id']);
    $new_qty = $_POST['cart_quantity'];
    if (($add_max == 1 and $cart_qty == 1)) {
      // do not add
      $new_qty = 0;
      $adjust_max= 'true';
    } else {
      // adjust quantity if needed
      if (($new_qty + $cart_qty > $add_max) and $add_max != 0) {
        $adjust_max= 'true';
        $new_qty = $add_max - $cart_qty;
      }
    }

  if ((zen_get_products_quantity_order_max($_POST['products_id']) == 1 and $_SESSION['cart']->in_cart_mixed($_POST['products_id']) == 1)) {
    // do not add
  } else {
    // process normally
// bof: set error message
      if ($the_list != '') {
        $messageStack->add('header', ERROR_CORRECTIONS_HEADING . $the_list, 'error');
      } else {
      // process normally

// iii 030813 added: File uploading: save uploaded files with unique file names
          $real_ids = !empty( $_REQUEST['id'] ) ? $_REQUEST['id'] : 0;
          if( !empty( $_REQUEST['number_of_uploads'] ) ) {
            require_once(DIR_FS_CLASSES . 'upload.php');
            for ($i = 1, $n = $_REQUEST['number_of_uploads']; $i <= $n; $i++) {
              if (zen_not_null($_FILES['id']['tmp_name'][TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]]) and ($_FILES['id']['tmp_name'][TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]] != 'none')) {
                $products_options_file = new upload('id');
                $products_options_file->set_destination(DIR_FS_UPLOADS);
                if ($products_options_file->parse(TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i])) {
                  $products_image_extention = substr($products_options_file->filename, strrpos($products_options_file->filename, '.'));
                  if ($_SESSION['customer_id']) {
                    $db->Execute("insert into " . TABLE_FILES_UPLOADED . " (sesskey, customers_id, files_uploaded_name) values('" . zen_session_id() . "', '" . $_SESSION['customer_id'] . "', '" . zen_db_input($products_options_file->filename) . "')");
                  } else {
                    $db->Execute("insert into " . TABLE_FILES_UPLOADED . " (sesskey, files_uploaded_name) values('" . zen_session_id() . "', '" . zen_db_input($products_options_file->filename) . "')");
                  }
                  $insert_id = zen_db_insert_id( TABLE_FILES_UPLOADED, 'files_uploaded_id' );
                  $real_ids[TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]] = $insert_id . ". " . $products_options_file->filename;
                  $products_options_file->set_filename("$insert_id" . $products_image_extention);
                  if (!($products_options_file->save())) {
                    break 2;
                  }
                } else {
                  break 2;
                }
              } else { // No file uploaded -- use previous value
                $real_ids[TEXT_PREFIX . $_REQUEST[UPLOAD_PREFIX . $i]] = $_REQUEST[TEXT_PREFIX . UPLOAD_PREFIX . $i];
              }
            }
          }

          $_SESSION['cart']->add_cart($_POST['products_id'], $_SESSION['cart']->get_quantity(zen_get_uprid($_POST['products_id'], $real_ids))+($new_qty), $real_ids);
// iii 030813 end of changes.
        } // eof: set error message
      } // eof: quantity maximum = 1

      if ($adjust_max == 'true') {
        $messageStack->add_session('header', ERROR_MAXIMUM_QTY . ' - ' . zen_get_products_name($_POST['products_id']), 'caution');
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
      case 'buy_now' :        if (isset($_REQUEST['products_id'])) {
                                if (zen_has_product_attributes($_REQUEST['products_id'])) {
                                  zen_redirect( CommerceProduct::getDisplayUrl( $_REQUEST['products_id']) );
                                } else {

                                  $add_max = zen_get_products_quantity_order_max($_REQUEST['products_id']);
                                  $cart_qty = $_SESSION['cart']->in_cart_mixed($_REQUEST['products_id']);
                                  $new_qty = zen_get_buy_now_qty($_REQUEST['products_id']);
                                  if (($add_max == 1 and $cart_qty == 1)) {
                                    // do not add
                                    $new_qty = 0;
                                  } else {
                                    // adjust quantity if needed
                                    if (($new_qty + $cart_qty > $add_max) and $add_max != 0) {
                                      $new_qty = $add_max - $cart_qty;
                                    }
                                  }

                                  if ((zen_get_products_quantity_order_max($_REQUEST['products_id']) == 1 and $_SESSION['cart']->in_cart_mixed($_REQUEST['products_id']) == 1)) {
                                    // do not add
                                  } else {
                                    // check for min/max and add that value or 1
                                    // $add_qty = zen_get_buy_now_qty($_REQUEST['products_id']);
//                                    $_SESSION['cart']->add_cart($_REQUEST['products_id'], $_SESSION['cart']->get_quantity($_REQUEST['products_id'])+$add_qty);
                                    $_SESSION['cart']->add_cart($_REQUEST['products_id'], $_SESSION['cart']->get_quantity($_REQUEST['products_id'])+$new_qty);
                                  }
                                }
                              }
                              zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
                              break;

// multiple products
      case 'multiple_products_add_product':
                              while ( list( $key, $val ) = each($_REQUEST['products_id']) ) {
                                if ($val > 0) {
                                  $prodId = $key;
                                  $qty = $val;

                                  $add_max = zen_get_products_quantity_order_max($prodId);
                                  $cart_qty = $_SESSION['cart']->in_cart_mixed($prodId);
                                  $new_qty = $qty;
                                  if (($add_max == 1 and $cart_qty == 1)) {
                                    // do not add
                                    $adjust_max= 'true';
                                  } else {
                                    // adjust quantity if needed
                                    if (($new_qty + $cart_qty > $add_max) and $add_max != 0) {
                                      $adjust_max= 'true';
                                      $new_qty = $add_max - $cart_qty;
                                    }
                                    $_SESSION['cart']->add_cart($prodId, $_SESSION['cart']->get_quantity($prodId)+($new_qty));
                                  }
                                  if( !empty( $adjust_max ) && $adjust_max == 'true' ) {
                                    $messageStack->add_session('header', ERROR_MAXIMUM_QTY . ' - ' . zen_get_products_name($prodId), 'caution');
                                  }
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
      case 'cust_order' :     if ($_SESSION['customer_id'] && isset($_REQUEST['pid'])) {
                                if (zen_has_product_attributes($_REQUEST['pid'])) {
                                  zen_redirect(zen_href_link(zen_get_info_page($_REQUEST['pid']), 'products_id=' . $_REQUEST['pid']));
                                } else {
                                  $db->Execute("delete FROM " . TABLE_WISHLIST . " WHERE products_id = '" . $_REQUEST['pid'] . "' and customers_id = '" . $_SESSION['customer_id'] . "'");
                                  $_SESSION['cart']->add_cart($_REQUEST['pid'], $_SESSION['cart']->get_quantity($_REQUEST['pid'])+1);
                                }
                              }
                              zen_redirect(zen_href_link($goto, zen_get_all_get_params($parameters)));
                              break;
 // Add product to the wishlist

      case 'add_wishlist' :  if (ereg('^[0-9]+$', $_REQUEST['products_id'])) {
                               if  ($_REQUEST['products_id']) {
                                 $db->Execute("delete FROM " . TABLE_WISHLIST . " WHERE products_id = '" . $_REQUEST['products_id'] . "' and customers_id = '" . $_SESSION['customer_id'] . "'");
                                 $db->Execute("insert into " . TABLE_WISHLIST . " (customers_id, products_id, products_model, products_name, products_price) values ('" . $_SESSION['customer_id'] . "', '" . $_REQUEST['products_id'] . "', '" . $products_model . "', '" . $products_name . "', '" . $products_price . "' )");
                               }
                             }

                             zen_redirect(zen_href_link(FILENAME_WISHLIST));
        break;
     // Add wishlist item to the cart

case 'wishlist_add_cart': reset ($lvnr);
                           reset ($lvanz);
                                 while (list($key,$elem) =each ($lvnr))
                                       {
                                        (list($key1,$elem1) =each ($lvanz));
                                        $db->Execute("update " . TABLE_WISHLIST . " SET `products_quantity`=$elem1 WHERE `customers_id`= '" . $_SESSION['customer_id'] . "' AND `products_id`=$elem");
                                        $db->Execute("delete FROM " . TABLE_WISHLIST . " WHERE `customers_id`= '" . $_SESSION['customer_id'] . "' AND `products_quantity`='999'");
                                        $products_in_wishlist = $db->Execute("SELECT * FROM " . TABLE_WISHLIST . " WHERE `customers_id`= '" . $_SESSION['customer_id'] . "' AND `products_id` = $elem AND `products_quantity` <> '0'");

                                        while (!$products_in_wishlist->EOF)
                                              {
                                               $cart->add_cart($products_in_wishlist->fields['products_id'], $products_in_wishlist->fields['products_quantity']);
                                               }
                                        }
                                  reset ($lvanz);
                              zen_redirect(zen_href_link(FILENAME_WISHLIST));
                              break;


// remove item FROM the wishlist
///// CHANGES TO case 'remove_wishlist' BY DREAMSCAPE /////
      case 'remove_wishlist' :
                             $db->Execute("delete FROM " . TABLE_WISHLIST . " WHERE `products_id` = '" . $HTTP_GET_VARS['pid'] . "' and customers_id = '" . $_SESSION['customer_id'] . "'");
                            zen_redirect(zen_href_link(FILENAME_WISHLIST));
                             break;
    }
  }

// include the who's online functions
  require_once(DIR_FS_FUNCTIONS . 'whos_online.php');
  zen_update_whos_online();

// include the password crypto functions
  require_once(DIR_FS_FUNCTIONS . 'password_funcs.php');

// include validation functions (right now only email address)
  require_once(DIR_FS_FUNCTIONS . 'validations.php');

// split-page-results
  require_once(DIR_FS_CLASSES . 'split_page_results.php');

// auto activate and expire banners
  require_once(DIR_FS_FUNCTIONS . 'banner.php');
  zen_activate_banners();
  zen_expire_banners();

// only process once per session do not include banners as banners expire per click as well as per date
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
//  } elseif (isset($_REQUEST['products_id']) && !isset($_REQUEST['manufacturers_id']) && !isset($_REQUEST['music_genres_id'])) {
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
?>

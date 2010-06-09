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
// $Id$
//

 define( 'HEADING_TITLE', 'Update Address Book Entry' );

  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
    zen_redirect(FILENAME_LOGIN);
  }

if( empty( $country_id ) ) {
	$country_id = STORE_COUNTRY;
}

  require_once(DIR_FS_MODULES . 'require_languages.php');
  if (isset($_GET['action']) && ($_GET['action'] == 'deleteconfirm') && isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $sql = "delete from   " . TABLE_ADDRESS_BOOK . "
                   where  address_book_id = '" . (int)$_GET['delete'] . "'
                   and    customers_id = '" . (int)$_SESSION['customer_id'] . "'";

    $gBitDb->Execute($sql);

    $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');

    zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
  }

// error checking when updating or adding an entry
  $process = false;
  if (isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update'))) {
  	$_REQUEST['address'] = $_REQUEST['edit'];
	if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
      $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'success');
      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
	} else {
		foreach( $gBitCustomer->mErrors as $errString ) {
			$messageStack->add_session( 'addressbook', $errString );
		}
	}
  }

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
	if( !($entry = $gBitCustomer->getAddress( $_GET['edit'] )) ) {
      $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);
      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }

  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if ($_GET['delete'] == $_SESSION['customer_default_address_id']) {
      $messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    } else {
      $check_query = "select count(*) as `total`
                      from " . TABLE_ADDRESS_BOOK . "
                      where `address_book_id` = '" . (int)$_GET['delete'] . "'
                      and `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";

      $check = $gBitDb->Execute($check_query);

      if ($check->fields['total'] < 1) {
        $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

        zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
      }
    }
  } else {
		$entry = array( 'entry_gender' => '', 'entry_firstname' => '', 'entry_lastname' => '', 'entry_company' => '', 'entry_street_address' => '', 'entry_suburb' => '', 'entry_city' => '', 'entry_country_id' => '', 'entry_zone_id' => '', 'entry_state' => '', 'entry_postcode' => '', 'entry_country_id' => STORE_COUNTRY, 'entry_telephone' => '' );
		if( !empty( $_REQUEST['address_store'] ) ) {
    		$entry = array_merge( $entry, $_REQUEST['address_store'] );
		}
  }

  if (!isset($_GET['delete']) && !isset($_GET['edit'])) {
    if (zen_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
      $messageStack->add_session('addressbook', ERROR_ADDRESS_BOOK_FULL);

      zen_redirect(zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

  if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $breadcrumb->add(NAVBAR_TITLE_MODIFY_ENTRY);
  } elseif (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $breadcrumb->add(NAVBAR_TITLE_DELETE_ENTRY);
  } else {
    $breadcrumb->add(NAVBAR_TITLE_ADD_ENTRY);
  }

      if (ACCOUNT_STATE == 'true') {
        $check_query = "select count(*) as `total`
                        from " . TABLE_ZONES . "
						where `zone_country_id` = '" . (int)$country_id . "'";

        $check = $gBitDb->Execute($check_query);           
        $entry_state_has_zones = ($check->fields['total'] > 0);    
      }
  
?>

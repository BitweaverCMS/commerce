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

if (!$gBitUser->isRegistered() ) {
	$_SESSION['navigation']->set_snapshot();
	zen_redirect(FILENAME_LOGIN);
}

require_once(DIR_FS_MODULES . 'require_languages.php');
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

if( $addresses = CommerceCustomer::getAddressesFromId( $_SESSION['customer_id'] ) ) {
	$gBitSmarty->assign( 'addresses', $addresses );
}

$redirectPage = BitBase::getParameter( $_REQUEST, 'return_page', FILENAME_ADDRESS_BOOK );

// error checking when updating or adding an entry
if( isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update')) || isset( $_POST['save_address'] ) ) {
	if( $gBitCustomer->storeAddress( $_REQUEST ) ) {
		$messageStack->add_session('addressbook', 'Your address book has been successfully updated.', 'success');
		zen_redirect(zen_href_link( $redirectPage, '', 'SSL'));
	} else {
		if( BitBase::verifyIdParameter( $_REQUEST, 'address_book_id' ) ) {
			$_REQUEST['address_store']['address_book_id'] =  $_REQUEST['address_book_id'];
		}
		$gBitSmarty->assign( 'editAddress', $_REQUEST['address_store'] );
		$gBitSmarty->assign( 'addressErrors', $gBitCustomer->mErrors );
	}
} elseif (isset($_REQUEST['delete']) && is_numeric( $_REQUEST['delete'] ) ) {
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitCustomer->expungeAddress( $_REQUEST['delete'] ); 
		$messageStack->add_session('addressbook', 'The selected address has been successfully removed from your address book.', 'success');
		zen_redirect( zen_href_link( $redirectPage, '', 'SSL' ) );
	} else {
		$formHash['main_page'] = 'address_book';
		$formHash['delete'] = $_REQUEST['delete'];
		if( !empty( $_REQUEST['return_page'] ) ) {
			$formHash['return_page'] = $_REQUEST['return_page'];
		}
		$msgHash = array(
			'label' => 'Delete Address',
			'confirm_item' => tra( 'Are you sure you would like to delete the selected address from your address book?' ),
			'warning' => tra('This cannot be undone!'),
		);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	}
} elseif (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
	if( !($editAddress = $gBitCustomer->getAddress( $_GET['edit'] )) ) {
		$messageStack->add_session('addressbook', 'This address does not exist');
		zen_redirect(zen_href_link( $redirectPage, '', 'SSL' ) );
	}
	$gBitSmarty->assign( 'editAddress', $editAddress );
} else {
	$entry = array( 'entry_gender' => '', 'entry_firstname' => '', 'entry_lastname' => '', 'entry_company' => '', 'entry_street_address' => '', 'entry_suburb' => '', 'entry_city' => '', 'entry_country_id' => '', 'entry_zone_id' => '', 'entry_state' => '', 'entry_postcode' => '', 'entry_country_id' => STORE_COUNTRY, 'entry_telephone' => '' );
	if( !empty( $_REQUEST['address_store'] ) ) {
		$entry = array_merge( $entry, $_REQUEST['address_store'] );
	}
}

$gBitSmarty->assign( 'formTitle', (isset( $_GET['edit'] ) ? 'Update Address Book Entry' : 'New Address Book Entry') );
$gBitSmarty->assign( 'defaultAddressId', $gBitCustomer->getDefaultAddressId() );

print $gBitSmarty->fetch( 'bitpackage:bitcommerce/page_address_book.tpl' );

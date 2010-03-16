<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
// $Id: header_php.php,v 1.6 2010/03/16 04:18:43 spiderr Exp $
//

  require_once(DIR_FS_MODULES . 'require_languages.php');

//present the option to unsubscribe, with a confirm button/link
  if (isset($_GET['unsubscribe_address'])) {
    $unsubscribe_address = preg_replace('/[^0-9A-Za-z@._-]/', '', $_GET['unsubscribe_address']); 
    if ($unsubscribe_address=='')  zen_redirect(zen_href_link(FILENAME_ACCOUNT_NEWSLETTERS));
  } else {
    $unsubscribe_address = '';
  }

  $breadcrumb->add(NAVBAR_TITLE, zen_href_link(FILENAME_UNSUBSCRIBE, '', 'NONSSL'));


  // if they clicked on the "confirm unsubscribe" then process it:
  if (isset($_GET['action']) && ($_GET['action'] == 'unsubscribe')) {
 	$unsubscribe_address = zen_db_prepare_input($_GET['unsubscribe_address']);
	/// Check and see if the email exists in the database, and is subscribed to the newsletter.
     $unsubscribe_count_query = "select 1 from " . TABLE_CUSTOMERS . " where customers_newsletter = '1' and customers_email_address = '" . $unsubscribe_address . "'";
     $unsubscribe = $gBitDb->Execute($unsubscribe_count_query);
	
	// If we found the customer's email address, and they currently subscribe
	  if ($unsubscribe->RecordCount() >0) {
		  $unsubscribe_query = "UPDATE " . TABLE_CUSTOMERS . " SET customers_newsletter = '0' WHERE customers_email_address = '" . $unsubscribe_address . "'";
		  $unsubscribe = $gBitDb->Execute($unsubscribe_query);
		  $status_display= UNSUBSCRIBE_DONE_TEXT_INFORMATION . $unsubscribe_address;
	  } else {
		// If not found, we want to display an error message (This should never occur, unless they try to unsubscribe twice)
		$status_display = UNSUBSCRIBE_ERROR_INFORMATION . $unsubscribe_address;
	  }
  }

  $_SESSION['navigation']->remove_current_page();

?>

<?php
////////////////////////////////////////////////////////////////////////////
// $Id: Newsletter Unsubscribe, (/catalog/includes/languages/english/unsubscribe.php)v 1.2 2004/04/29
// Programed By: Christopher Bradley (www.wizardsandwars.com)
//  Modified by Jim Keebaugh
//  Modified for Zen-Cart by Chris Brown
//
// Developed for osCommerce, Open Source E-Commerce Solutions
// http://www.oscommerce.com
// Copyright (c) 2003 osCommerce
//
// Released under the GNU General Public License
//
///////////////////////////////////////////////////////////////////////////

define('NAVBAR_TITLE', 'Unsubscribe');
define('HEADING_TITLE', 'Unsubscribe from our Newsletter');

define('UNSUBSCRIBE_TEXT_INFORMATION', '<br />We\'re sorry to hear that you wish to unsubscribe from our newsletter. If you have concerns about your privacy, please see our <a href="' . zen_href_link(FILENAME_PRIVACY,'','NONSSL') . '"><u>privacy notice</u></a>.<br /><br />Subscribers to our newsletter are kept notified of new products, price reductions, and site news.<br /><br />If you still do not wish to receive your newsletter, please click the button below. ');
define('UNSUBSCRIBE_TEXT_NO_ADDRESS_GIVEN', '<br />We\'re sorry to hear that you wish to unsubscribe from our newsletter. If you have concerns about your privacy, please see our <a href="' . zen_href_link(FILENAME_PRIVACY,'','NONSSL') . '"><u>privacy notice</u></a>.<br /><br />Subscribers to our newsletter are kept notified of new products, price reductions, and site news.<br /><br />If you still do not wish to receive your newsletter, please click the button below. You will be taken to your account-preferences page, where you may edit your subscriptions. You may be prompted to log in first.');
define('UNSUBSCRIBE_DONE_TEXT_INFORMATION', '<br />Your email address, listed below, has been removed from our Newsletter Subscription list, as per your request. <br /><br />');
define('UNSUBSCRIBE_ERROR_INFORMATION', '<br />The email address you specified was not found in our newsletter database, or has already been removed from our newletter subscription list. <br /><br />');
?>
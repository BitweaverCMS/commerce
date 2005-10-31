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
// $Id: en.php,v 1.9 2005/10/31 21:18:19 lsces Exp $
//

// bof: removed for meta tags
// page title
//define('TITLE', 'Zen Cart!');

// Site Tagline
//define('SITE_TAGLINE', 'The Art of E-commerce');

// Custom Keywords
//define('CUSTOM_KEYWORDS', 'ecommerce, open source, shop, online shopping');
// eof: removed for meta tags

define('FOOTER_TEXT_BODY', 'Copyright &copy; 2003 <a href="http://www.zen-cart.com" target="_blank">Zen Cart</a>. Powered by <a href="http://www.zen-cart.com" target="_blank">Zen Cart</a>');

// look in your $PATH_LOCALE/locale directory for available locales..
// on RedHat try 'en_US'
// on FreeBSD try 'en_US.ISO_8859-1'
// on Windows try 'en', or 'English'
@setlocale(LC_TIME, 'en_US.ISO_8859-1');
define('DATE_FORMAT_SHORT', '%m/%d/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('DATE_FORMAT', 'm/d/Y'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function zen_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . '.' . substr($date, 0, 2) . '.' . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . '-' . substr($date, 0, 2) . '-' . substr($date, 3, 2);
  }
}


// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency, instead of the applications default currency (used when changing language)
define('LANGUAGE_CURRENCY', 'USD');

// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="en"');

// charset for web pages and emails
define('CHARSET', 'iso-8859-1');

// footer text in includes/footer.php
define('FOOTER_TEXT_REQUESTS_SINCE', 'requests since');

// Define the name of your Gift Certificate as Gift Voucher, Gift Certificate, Zen Cart Dollars, etc. here for use through out the shop
  define('TEXT_GV_NAME','Gift Certificate');
  define('TEXT_GV_NAMES','Gift Certificates');

// used for redeem code, redemption code, or redemption id
  define('TEXT_GV_REDEEM','Redemption Code');

// text for gender
define('MALE', 'Mr.');
define('FEMALE', 'Ms.');
define('MALE_ADDRESS', 'Mr.');
define('FEMALE_ADDRESS', 'Ms.');

// text for date of birth example
define('DOB_FORMAT_STRING', 'mm/dd/yyyy');

//text for sidebox heading links
define('BOX_HEADING_LINKS', '&nbsp;&nbsp;[more]');

// categories box text in sideboxes/categories.php
define('BOX_HEADING_CATEGORIES', 'Categories');

// manufacturers box text in sideboxes/manufacturers.php
define('BOX_HEADING_MANUFACTURERS', 'Manufacturers');

// whats_new box text in sideboxes/whats_new.php
define('BOX_HEADING_WHATS_NEW', 'New Products');
define('CATEGORIES_BOX_HEADING_WHATS_NEW', 'New Products ...');

define('BOX_HEADING_FEATURED_PRODUCTS', 'Featured');
define('CATEGORIES_BOX_HEADING_FEATURED_PRODUCTS', 'Featured Products ...');
define('TEXT_NO_FEATURED_PRODUCTS', 'More featured products will be added soon. Please check back later.');

define('TEXT_NO_ALL_PRODUCTS', 'More products will be added soon. Please check back later.');
define('CATEGORIES_BOX_HEADING_PRODUCTS_ALL', 'All Products ...');

// quick_find box text in sideboxes/quick_find.php
define('BOX_HEADING_SEARCH', 'Search');
define('BOX_SEARCH_ADVANCED_SEARCH', 'Advanced Search');

// specials box text in sideboxes/specials.php
define('BOX_HEADING_SPECIALS', 'Specials');
define('CATEGORIES_BOX_HEADING_SPECIALS','Specials ...');

// reviews box text in sideboxes/reviews.php
define('BOX_HEADING_REVIEWS', 'Reviews');
define('BOX_REVIEWS_WRITE_REVIEW', 'Write a review on this product.');
define('BOX_REVIEWS_NO_REVIEWS', 'There are currently no product reviews.');
define('BOX_REVIEWS_TEXT_OF_5_STARS', '%s of 5 Stars!');

// shopping_cart box text in sideboxes/shopping_cart.php
define('BOX_HEADING_SHOPPING_CART', 'Shopping Cart');
define('BOX_SHOPPING_CART_EMPTY', '0 items');

// order_history box text in sideboxes/order_history.php
define('BOX_HEADING_CUSTOMER_ORDERS', 'Recent Purchases');

// best_sellers box text in sideboxes/best_sellers.php
define('BOX_HEADING_BESTSELLERS', 'Bestsellers');
define('BOX_HEADING_BESTSELLERS_IN', 'Bestsellers in<br />&nbsp;&nbsp;');

// notifications box text in sideboxes/products_notifications.php
define('BOX_HEADING_NOTIFICATIONS', 'Notifications');
define('BOX_NOTIFICATIONS_NOTIFY', 'Notify me of updates to <strong>%s</strong>');
define('BOX_NOTIFICATIONS_NOTIFY_REMOVE', 'Do not notify me of updates to <strong>%s</strong>');

// manufacturer box text
define('BOX_HEADING_MANUFACTURER_INFO', 'Manufacturer Info');
define('BOX_MANUFACTURER_INFO_HOMEPAGE', '%s Homepage');
define('BOX_MANUFACTURER_INFO_OTHER_PRODUCTS', 'Other products');

// languages box text in sideboxes/languages.php
define('BOX_HEADING_LANGUAGES', 'Languages');

// currencies box text in sideboxes/currencies.php
define('BOX_HEADING_CURRENCIES', 'Currencies');

// information box text in sideboxes/information.php
define('BOX_HEADING_INFORMATION', 'Information');
define('BOX_INFORMATION_PRIVACY', 'Privacy Notice');
define('BOX_INFORMATION_CONDITIONS', 'Conditions of Use');
define('BOX_INFORMATION_SHIPPING', 'Shipping &amp; Returns');
define('BOX_INFORMATION_CONTACT', 'Contact Us');
define('BOX_BBINDEX', 'Forum');
define('BOX_INFORMATION_UNSUBSCRIBE', 'Newsletter Unsubscribe');

// information box text in sideboxes/more_information.php - were TUTORIAL_
define('BOX_HEADING_MORE_INFORMATION', 'More Information');
define('BOX_INFORMATION_PAGE_1', 'Page 1');
define('BOX_INFORMATION_PAGE_2', 'Page 2');
define('BOX_INFORMATION_PAGE_3', 'Page 3');
define('BOX_INFORMATION_PAGE_4', 'Page 4');

// tell a friend box text in sideboxes/tell_a_friend.php
define('BOX_HEADING_TELL_A_FRIEND', 'Tell A Friend');
define('BOX_TELL_A_FRIEND_TEXT', 'Tell someone you know about this product.');

// wishlist box text in includes/boxes/wishlist.php
define('BOX_HEADING_CUSTOMER_WISHLIST', 'My Wishlist');
define('BOX_WISHLIST_EMPTY', 'You have no items on your Wishlist');
define('IMAGE_BUTTON_ADD_WISHLIST', 'Add to Wishlist');
define('TEXT_WISHLIST_COUNT', 'Currently %s items are on your Wishlist.');
define('TEXT_DISPLAY_NUMBER_OF_WISHLIST', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> items on your wishlist)');

//New billing address text
define('SET_AS_PRIMARY' , 'Set as Primary Address');
define('NEW_ADDRESS_TITLE', 'Billing Address');

// javascript messages
define('JS_ERROR', 'Errors have occurred during the processing of your form.\n\nPlease make the following corrections:\n\n');

define('JS_REVIEW_TEXT', '* The \'Review Text\' must have at least ' . REVIEW_TEXT_MIN_LENGTH . ' characters.');
define('JS_REVIEW_RATING', '* You must rate the product for your review.');

define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* Please select a payment method for your order.');

define('JS_ERROR_SUBMITTED', 'This form has already been submitted. Please press Ok and wait for this process to be completed.');

define('ERROR_NO_PAYMENT_MODULE_SELECTED', 'Please select a payment method for your order.');
define('ERROR_CONDITIONS_NOT_ACCEPTED', 'Please confirm the terms and conditions bound to this order by ticking the box below.');
define('ERROR_PRIVACY_STATEMENT_NOT_ACCEPTED', 'Please confirm the privacy statement by ticking the box below.');

define('CATEGORY_COMPANY', 'Company Details');
define('CATEGORY_PERSONAL', 'Your Personal Details');
define('CATEGORY_ADDRESS', 'Your Address');
define('CATEGORY_CONTACT', 'Your Contact Information');
define('CATEGORY_OPTIONS', 'Options');
define('CATEGORY_PASSWORD', 'Your Password');
define('CATEGORY_LOGIN', 'Login');
define('PULL_DOWN_DEFAULT', 'Please Choose Your Country');

define('ENTRY_COMPANY', 'Company Name:');
define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_GENDER', 'Salutation:');
define('ENTRY_GENDER_ERROR', 'Please choose a title.');
define('ENTRY_GENDER_TEXT', '*');
define('ENTRY_FIRST_NAME', 'First Name:');
define('ENTRY_FIRST_NAME_ERROR', 'Your First Name must contain a minimum of ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' characters.');
define('ENTRY_FIRST_NAME_TEXT', '*');
define('ENTRY_LAST_NAME', 'Last Name:');
define('ENTRY_LAST_NAME_ERROR', 'Your Last Name must contain a minimum of ' . ENTRY_LAST_NAME_MIN_LENGTH . ' characters.');
define('ENTRY_LAST_NAME_TEXT', '*');
define('ENTRY_DATE_OF_BIRTH', 'Date of Birth:');
define('ENTRY_DATE_OF_BIRTH_ERROR', 'Your Date of Birth must be in this format: MM/DD/YYYY (eg 05/21/1970)');
define('ENTRY_DATE_OF_BIRTH_TEXT', '* (eg. 05/21/1970)');
define('ENTRY_EMAIL_ADDRESS', 'E-Mail Address:');
define('ENTRY_EMAIL_ADDRESS_ERROR', 'Your E-Mail Address must contain a minimum of ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' characters.');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'Your E-Mail Address does not appear to be valid - please make any necessary corrections.');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', 'Your E-Mail Address already exists in our records - please log in with the e-mail address or create an account with a different address.');
define('ENTRY_EMAIL_ADDRESS_TEXT', '*');
define('ENTRY_NICK', 'Forum Nick Name:');
define('ENTRY_NICK_TEXT', ''); // note to display beside nickname input field
define('ENTRY_NICK_DUPLICATE_ERROR', 'This Nick Name already exists.');
define('ENTRY_NICK_LENGTH_ERROR', 'The nick name must contain a minimum of ' . ENTRY_NICK_MIN_LENGTH . ' characters.');
define('ENTRY_STREET_ADDRESS', 'Street Address:');
define('ENTRY_STREET_ADDRESS_ERROR', 'Your Street Address must contain a minimum of ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' characters.');
define('ENTRY_STREET_ADDRESS_TEXT', '*');
define('ENTRY_SUBURB', 'Address Line 2:');
define('ENTRY_SUBURB_ERROR', '');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POST_CODE', 'Post Code:');
define('ENTRY_POST_CODE_ERROR', 'Your Post Code must contain a minimum of ' . ENTRY_POSTCODE_MIN_LENGTH . ' characters.');
define('ENTRY_POST_CODE_TEXT', '*');
define('ENTRY_CITY', 'City:');
define('ENTRY_CUSTOMERS_REFERRAL', 'Referral Code:');

define('ENTRY_CITY_ERROR', 'Your City must contain a minimum of ' . ENTRY_CITY_MIN_LENGTH . ' characters.');
define('ENTRY_CITY_TEXT', '*');
define('ENTRY_STATE', 'State/Province:');
define('ENTRY_STATE_ERROR', 'Your State must contain a minimum of ' . ENTRY_STATE_MIN_LENGTH . ' characters.');
define('ENTRY_STATE_ERROR_SELECT', 'Please select a state from the States pull down menu.');
define('ENTRY_STATE_TEXT', '*');
define('ENTRY_COUNTRY', 'Country:');
define('ENTRY_COUNTRY_ERROR', 'You must select a country from the Countries pull down menu.');
define('ENTRY_COUNTRY_TEXT', '*');
define('ENTRY_TELEPHONE_NUMBER', 'Telephone Number:');
define('ENTRY_TELEPHONE_NUMBER_ERROR', 'Your Telephone Number must contain a minimum of ' . ENTRY_TELEPHONE_MIN_LENGTH . ' characters.');
define('ENTRY_TELEPHONE_NUMBER_TEXT', '*');
define('ENTRY_FAX_NUMBER', 'Fax Number:');
define('ENTRY_FAX_NUMBER_ERROR', '');
define('ENTRY_FAX_NUMBER_TEXT', '');
define('ENTRY_NEWSLETTER', 'Newsletter:');
define('ENTRY_NEWSLETTER_TEXT', '');
define('ENTRY_NEWSLETTER_YES', 'Subscribed');
define('ENTRY_NEWSLETTER_NO', 'Unsubscribed');
define('ENTRY_NEWSLETTER_ERROR', '');
define('ENTRY_PASSWORD', 'Password:');
define('ENTRY_PASSWORD_ERROR', 'Your Password must contain a minimum of ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'The Password Confirmation must match your Password.');
define('ENTRY_PASSWORD_TEXT', '* (at least ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters)');
define('ENTRY_PASSWORD_CONFIRMATION', 'Confirm Password:');
define('ENTRY_PASSWORD_CONFIRMATION_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT', 'Current Password:');
define('ENTRY_PASSWORD_CURRENT_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT_ERROR', 'Your Password must contain a minimum of ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_NEW', 'New Password:');
define('ENTRY_PASSWORD_NEW_TEXT', '*');
define('ENTRY_PASSWORD_NEW_ERROR', 'Your new Password must contain a minimum of ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING', 'The Password Confirmation must match your new Password.');
define('PASSWORD_HIDDEN', '--HIDDEN--');

define('FORM_REQUIRED_INFORMATION', '* Required information');

// constants for use in zen_prev_next_display function
define('TEXT_RESULT_PAGE', '');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> products)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> orders)');
define('TEXT_DISPLAY_NUMBER_OF_REVIEWS', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> reviews)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> new products)');
define('TEXT_DISPLAY_NUMBER_OF_SPECIALS', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> specials)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_FEATURED_PRODUCTS', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> featured products)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_ALL', 'Displaying <strong>%d</strong> to <strong>%d</strong> (of <strong>%d</strong> products)');

define('PREVNEXT_TITLE_FIRST_PAGE', 'First Page');
define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'Previous Page');
define('PREVNEXT_TITLE_NEXT_PAGE', 'Next Page');
define('PREVNEXT_TITLE_LAST_PAGE', 'Last Page');
define('PREVNEXT_TITLE_PAGE_NO', 'Page %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', 'Previous Set of %d Pages');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', 'Next Set of %d Pages');
define('PREVNEXT_BUTTON_FIRST', '&lt;&lt;FIRST');
define('PREVNEXT_BUTTON_PREV', '[&lt;&lt;&nbsp;Prev]');
define('PREVNEXT_BUTTON_NEXT', '[Next&nbsp;&gt;&gt;]');
define('PREVNEXT_BUTTON_LAST', 'LAST&gt;&gt;');

define('TEXT_BASE_PRICE','Starting at: ');

define('TEXT_CLICK_TO_ENLARGE', 'larger image');

define('TEXT_SORT_PRODUCTS', 'Sort products ');
define('TEXT_DESCENDINGLY', 'descendingly');
define('TEXT_ASCENDINGLY', 'ascendingly');
define('TEXT_BY', ' by ');

define('TEXT_REVIEW_BY', 'by %s');
define('TEXT_REVIEW_WORD_COUNT', '%s words');
define('TEXT_REVIEW_RATING', 'Rating: %s [%s]');
define('TEXT_REVIEW_DATE_ADDED', 'Date Added: %s');
define('TEXT_NO_REVIEWS', 'There are currently no product reviews.');

define('TEXT_NO_NEW_PRODUCTS', 'More new products will be added soon. Please check back later.');

define('TEXT_UNKNOWN_TAX_RATE', 'Sales Tax');

define('TEXT_REQUIRED', '<span class="errorText">Required</span>');

define('ERROR_zen_MAIL', '<font face="Verdana, Arial" size="2" color="#ff0000"><strong><small>ZEN ERROR:</small> Cannot send the email through the specified SMTP server. Please check your php.ini setting and correct the SMTP server if necessary.</strong></font>');
define('WARNING_INSTALL_DIRECTORY_EXISTS', 'Warning: Installation directory exists at: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/zc_install. Please remove this directory for security reasons.');
define('WARNING_CONFIG_FILE_WRITEABLE', 'Warning: I am able to write to the configuration file: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/includes/configure.php. This is a potential security risk - please set the right user permissions on this file.');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', 'Warning: The sessions directory does not exist: ' . zen_session_save_path() . '. Sessions will not work until this directory is created.');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', 'Warning: I am not able to write to the sessions directory: ' . zen_session_save_path() . '. Sessions will not work until the right user permissions are set.');
define('WARNING_SESSION_AUTO_START', 'Warning: session.auto_start is enabled - please disable this php feature in php.ini and restart the web server.');
define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', 'Warning: The downloadable products directory does not exist: ' . DIR_FS_DOWNLOAD . '. Downloadable products will not work until this directory is valid.');
define('WARNING_SQL_CACHE_DIRECTORY_NON_EXISTENT', 'Warning: The SQL cache directory does not exist: ' . DIR_FS_SQL_CACHE . '. SQL cacheing will not work until this directory is created.');
define('WARNING_SQL_CACHE_DIRECTORY_NOT_WRITEABLE', 'Warning: I am not able to write to the SQL cache directory: ' . DIR_FS_SQL_CACHE . '. SQL cacheing will not work until the right user permissions are set.');
define('WARNING_DATABASE_VERSION_OUT_OF_DATE','Your database appears to need patching to a higher level. See Admin->Tools->Server Information to review patch levels.');


define('TEXT_CCVAL_ERROR_INVALID_DATE', 'The expiration date entered for the credit card is invalid. Please check the date and try again.');
define('TEXT_CCVAL_ERROR_INVALID_NUMBER', 'The credit card number entered is invalid. Please check the number and try again.');
define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', 'The first four digits of the number entered are: %s. If that number is correct, we do not accept that type of credit card. If it is wrong, please try again.');

define('BOX_INFORMATION_GV', TEXT_GV_NAME . ' FAQ');
define('VOUCHER_BALANCE', TEXT_GV_NAME . ' Balance ');
define('BOX_HEADING_GIFT_VOUCHER', TEXT_GV_NAME . ' Account');
define('GV_FAQ', TEXT_GV_NAME . ' FAQ');
define('ERROR_REDEEMED_AMOUNT', 'Congratulations, you have redeemed ');
define('ERROR_NO_REDEEM_CODE', 'You did not enter a ' . TEXT_GV_REDEEM . '.');
define('ERROR_NO_INVALID_REDEEM_GV', 'Invalid ' . TEXT_GV_NAME . ' ' . TEXT_GV_REDEEM);
define('TABLE_HEADING_CREDIT', 'Credits Available');
define('GV_HAS_VOUCHERA', 'You have funds in your ' . TEXT_GV_NAME . ' Account. If you want <br />
                         you can send those funds by <a class="pageResults" href="');

define('GV_HAS_VOUCHERB', '"><strong>email</strong></a> to someone');
define('ENTRY_AMOUNT_CHECK_ERROR', 'You do not have enough funds to send this amount.');
define('BOX_SEND_TO_FRIEND', 'Send ' . TEXT_GV_NAME . ' ');

define('VOUCHER_REDEEMED',  TEXT_GV_NAME . ' Redeemed');
define('CART_COUPON', 'Coupon :');
define('CART_COUPON_INFO', 'more info');

// payment method is GV/Discount
  define('PAYMENT_METHOD_GV', 'Gift Certificate/Coupon');
  define('PAYMENT_MODULE_GV', 'GV/DC');

define('TABLE_HEADING_CREDIT_PAYMENT', 'Credits Available');

define('TEXT_INVALID_REDEEM_COUPON', 'Invalid Coupon Code');
define('TEXT_INVALID_STARTDATE_COUPON', 'This coupon is not available yet');
define('TEXT_INVALID_FINISDATE_COUPON', 'This coupon has expired');
define('TEXT_INVALID_USES_COUPON', 'This coupon could only be used ');
define('TIMES', ' times.');
define('TIME', ' time.');
define('TEXT_INVALID_USES_USER_COUPON', 'You have used coupon code: %s the maximum number of times allowed per customer. ');
define('REDEEMED_COUPON', 'a coupon worth ');
define('REDEEMED_MIN_ORDER', 'on orders over ');
define('REDEEMED_RESTRICTIONS', ' [Product-Category restrictions apply]');
define('TEXT_ERROR', 'An error has occurred');
//ke-added from forum
define('TEXT_INVALID_COUPON_PRODUCT', 'This coupon code is not valid for any product currently in your cart');

// more info in place of buy now
define('MORE_INFO_TEXT','... more info');

// IP Address
define('TEXT_YOUR_IP_ADDRESS','Your IP Address is: ');

//Generic Address Heading
define('HEADING_ADDRESS_INFORMATION','Address Information');

// cart contents
  define('PRODUCTS_ORDER_QTY_TEXT_IN_CART','Quantity in Cart: ');
  define('PRODUCTS_ORDER_QTY_TEXT','Add to Cart: ');

// Discount Savings
  define('PRODUCT_PRICE_DISCOUNT_PREFIX','Save:&nbsp;');
  define('PRODUCT_PRICE_DISCOUNT_PERCENTAGE','% off');
  define('PRODUCT_PRICE_DISCOUNT_AMOUNT','&nbsp;off');
// Sale Maker Sale Price
  define('PRODUCT_PRICE_SALE','Sale:&nbsp;');

//universal symbols
  define('TEXT_NUMBER_SYMBOL', '# ');

// banner_box
  define('BOX_HEADING_BANNER_BOX','Sponsors');
  define('TEXT_BANNER_BOX','Please Visit Our Sponsors ...');

// banner box 2
  define('BOX_HEADING_BANNER_BOX2','Have you seen ...');
  define('TEXT_BANNER_BOX2','Check this out today!');

// banner_box - all
  define('BOX_HEADING_BANNER_BOX_ALL','Sponsors');
  define('TEXT_BANNER_BOX_ALL','Please Visit Our Sponsors ...');

// boxes defines
  define('PULL_DOWN_ALL','Please Select');
  define('PULL_DOWN_MANUFACTURERS','- Reset -');
// shipping estimator
  define('PULL_DOWN_SHIPPING_ESTIMATOR_SELECT', 'Please Select');

// general Sort By
define('TEXT_INFO_SORT_BY','Sort by: ');

// close window image popups
  define('TEXT_CLOSE_WINDOW',' - Click Image to Close');
// close popups
  define('TEXT_CURRENT_CLOSE_WINDOW','[ Close Window ]');

// iii 031104 added:  File upload error strings
define('ERROR_FILETYPE_NOT_ALLOWED', 'Error:  File type not allowed.');
define('WARNING_NO_FILE_UPLOADED', 'Warning:  no file uploaded.');
define('SUCCESS_FILE_SAVED_SUCCESSFULLY', 'Success:  file saved successfully.');
define('ERROR_FILE_NOT_SAVED', 'Error:  file not saved.');
define('ERROR_DESTINATION_NOT_WRITEABLE', 'Error:  destination not writeable.');
define('ERROR_DESTINATION_DOES_NOT_EXIST', 'Error: destination does not exist.');
// End iii added

define('TEXT_BEFORE_DOWN_FOR_MAINTENANCE', 'NOTICE: This website is scheduled to be down for maintenance on: ');
define('TEXT_ADMIN_DOWN_FOR_MAINTENANCE', 'NOTICE: the website is currently Down For Maintenance to the public');

define('PRODUCTS_PRICE_IS_FREE_TEXT','It\'s Free!');
define('PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT','Call for Price');
define('TEXT_CALL_FOR_PRICE','Call for price');

define('TEXT_INVALID_SELECTION_LABELED',' You picked an Invalid Selection: ');
define('TEXT_ERROR_OPTION_FOR',' On the Option for: ');
define('TEXT_INVALID_USER_INPUT', 'User Input Required');

// product_listing
  define('PRODUCTS_QUANTITY_MIN_TEXT_LISTING','Min:');
  define('PRODUCTS_QUANTITY_UNIT_TEXT_LISTING','Units:');
  define('PRODUCTS_QUANTITY_IN_CART_LISTING','In cart:');
  define('PRODUCTS_QUANTITY_ADD_ADDITIONAL_LISTING','Add Additional:');

  define('PRODUCTS_QUANTITY_MAX_TEXT_LISTING','Max:');

  define('TEXT_PRODUCTS_MIX_OFF','*Mixed OFF');
  define('TEXT_PRODUCTS_MIX_ON','*Mixed ON');

  define('TEXT_PRODUCTS_MIX_OFF_SHOPPING_CART','*Mixed Options Values is OFF');
  define('TEXT_PRODUCTS_MIX_ON_SHOPPING_CART','*Mixed Option Values is ON');

  define('ERROR_MAXIMUM_QTY','Qty Adjusted - Maximum Qty Added to Cart ');
  define('ERROR_CORRECTIONS_HEADING','Please Correct the following: <br />');

// Downloads Controller
  define('DOWNLOADS_CONTROLLER_ON_HOLD_MSG','NOTE: Downloads are not available until payment has been confirmed');
  define('TEXT_FILESIZE_BYTES', ' bytes');
  define('TEXT_FILESIZE_MEGS', ' meg');

// shopping cart errors
  define('ERROR_PRODUCT','Product: ');
  define('ERROR_PRODUCT_QUANTITY_MIN',' ... Minimum Quantity errors - ');
  define('ERROR_PRODUCT_QUANTITY_UNITS',' ... Quantity Units errors - ');
  define('ERROR_PRODUCT_OPTION_SELECTION',' ... Invalid Option Values Selected ');
  define('ERROR_PRODUCT_QUANTITY_ORDERED','You ordered a total of: ');
  define('ERROR_PRODUCT_QUANTITY_MAX',' ... Maximum Quantity errors - ');
  define('ERROR_PRODUCT_QUANTITY_MIN_SHOPPING_CART',' ... Minimum Quantity errors - ');
  define('ERROR_PRODUCT_QUANTITY_UNITS_SHOPPING_CART',' ... Quantity Units errors - ');
  define('ERROR_PRODUCT_QUANTITY_MAX_SHOPPING_CART',' ... Maximum Quantity errors - ');

  define('TEXT_SHIPPING_WEIGHT','lbs');
  define('TEXT_SHIPPING_BOXES', 'Boxes');

define('TABLE_HEADING_FEATURED_PRODUCTS','Featured Products');

define('TABLE_HEADING_NEW_PRODUCTS', 'New Products For %s');
define('TABLE_HEADING_UPCOMING_PRODUCTS', 'Upcoming Products');
define('TABLE_HEADING_DATE_EXPECTED', 'Date Expected');
define('TABLE_HEADING_SPECIALS_INDEX', 'Monthly Specials For %s');

// meta tags special defines
define('META_TAG_PRODUCTS_PRICE_IS_FREE_TEXT','It\'s Free!');

// customer login
define('TEXT_SHOWCASE_ONLY','Contact Us');
// set for login for prices
define('TEXT_LOGIN_FOR_PRICE_PRICE','Price Unavailable');
define('TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE','Login for price');
// set for show room only
define('TEXT_LOGIN_FOR_PRICE_PRICE_SHOWROOM', ''); // blank for prices or enter your own text
define('TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM','Show Room Only');

// authorization pending
define('TEXT_AUTHORIZATION_PENDING_PRICE', 'Price Unavailable');
define('TEXT_AUTHORIZATION_PENDING_BUTTON_REPLACE', 'APPROVAL PENDING');


// text pricing
define('TEXT_CHARGES_WORD','Calculated Charge:');
define('TEXT_PER_WORD','<br />Price per word: ');
define('TEXT_WORDS_FREE',' Word(s) free ');
define('TEXT_CHARGES_LETTERS','Calculated Charge:');
define('TEXT_PER_LETTER','<br />Price per letter: ');
define('TEXT_LETTERS_FREE',' Letter(s) free ');
define('TEXT_ONETIME_CHARGES','*onetime charges = ');
define('TEXT_ONETIME_CHARGES_EMAIL',"\t" . '*onetime charges = ');
define('TEXT_ATTRIBUTES_QTY_PRICES_HELP', 'Option Quantity Discounts');
define('TABLE_ATTRIBUTES_QTY_PRICE_QTY','QTY');
define('TABLE_ATTRIBUTES_QTY_PRICE_PRICE','PRICE');
define('TEXT_ATTRIBUTES_QTY_PRICES_ONETIME_HELP', 'Option Quantity Discounts Onetime Charges');


// Shipping Estimator
  define('CART_SHIPPING_OPTIONS', 'Shipping Estimate:');
  define('CART_SHIPPING_OPTIONS_LOGIN', 'Please <a href="' . FILENAME_LOGIN . '"><u>Log In</u></a>, to display your personal shipping costs.');
  define('CART_SHIPPING_METHOD_TEXT','Shipping Methods:');
  define('CART_SHIPPING_METHOD_RATES','Rates:');
  define('CART_SHIPPING_METHOD_TO','Ship to: ');
  define('CART_SHIPPING_METHOD_TO_NOLOGIN', 'Ship to: <a href="' . FILENAME_LOGIN . '"><u>Log In</u></a>');
  define('CART_SHIPPING_METHOD_FREE_TEXT','Free Shipping');
  define('CART_SHIPPING_METHOD_ALL_DOWNLOADS','- Downloads');
  define('CART_SHIPPING_METHOD_RECALCULATE','Recalculate');
  define('CART_SHIPPING_METHOD_ZIP_REQUIRED','true');
  define('CART_SHIPPING_METHOD_ADDRESS','Address:');
  define('CART_OT','Total Cost Etimate:');
  define('CART_OT_SHOW','true'); // set to false if you don't want order totals
  define('CART_ITEMS','Items in Cart: ');
  define('CART_SELECT','Select');
  define('ERROR_CART_UPDATE', 'Please update your order ...<br />');
  define('IMAGE_BUTTON_UPDATE_CART', 'Update');

// multiple product add to cart
  define('TEXT_PRODUCT_LISTING_MULTIPLE_ADD_TO_CART', 'Add: ');
  define('TEXT_PRODUCT_ALL_LISTING_MULTIPLE_ADD_TO_CART', 'Add: ');
  define('TEXT_PRODUCT_FEATURED_LISTING_MULTIPLE_ADD_TO_CART', 'Add: ');
  define('TEXT_PRODUCT_NEW_LISTING_MULTIPLE_ADD_TO_CART', 'Add: ');
  define('SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART','Add Multiple Products to Cart');

// discount qty table
define('TEXT_HEADER_DISCOUNT_PRICES_PERCENTAGE', 'Qty Discounts off Price');
define('TEXT_HEADER_DISCOUNT_PRICES_ACTUAL_PRICE', 'Qty Discounts New Price');
define('TEXT_HEADER_DISCOUNT_PRICES_AMOUNT_OFF', 'Qty Discounts off Price');
define('TEXT_FOOTER_DISCOUNT_QUANTITIES', '* Discounts may vary based on Options above');
define('TEXT_HEADER_DISCOUNTS_OFF', 'Qty Discounts Unavailable ...');

// sort order titles for dropdowns
define('PULL_DOWN_ALL_RESET','- RESET - ');
define('TEXT_INFO_SORT_BY_PRODUCTS_NAME', 'Product Name');
define('TEXT_INFO_SORT_BY_PRODUCTS_NAME_DESC', 'Product Name - desc');
define('TEXT_INFO_SORT_BY_PRODUCTS_PRICE', 'Price - low to high');
define('TEXT_INFO_SORT_BY_PRODUCTS_PRICE_DESC', 'Price - high to low');
define('TEXT_INFO_SORT_BY_PRODUCTS_MODEL', 'Model');
define('TEXT_INFO_SORT_BY_PRODUCTS_DATE_DESC', 'Date Added - New to Old');
define('TEXT_INFO_SORT_BY_PRODUCTS_DATE', 'Date Added - Old to New');
define('TEXT_INFO_SORT_BY_PRODUCTS_SORT_ORDER', 'Default Display');

//added for Rich Text / Email Mod
 define('ENTRY_EMAIL_PREFERENCE','Email Format Preference:');
 define('ENTRY_EMAIL_HTML_DISPLAY','HTML');
 define('ENTRY_EMAIL_TEXT_DISPLAY','TEXT-Only');
 define('EMAIL_SEND_FAILED','ERROR: Failed sending email to: "%s" <%s> with subject: "%s"');

 define('DB_ERROR_NOT_CONNECTED', 'Error - Could not connect to Database');







///////////////////////////////////////////////////////////
// include email extras
  if (file_exists(DIR_WS_LANGUAGES . 'en/' . $template_dir . '/' . FILENAME_EMAIL_EXTRAS)) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . 'en/' . $template_dir_select . FILENAME_EMAIL_EXTRAS);

// include template specific header defines
  if (file_exists(DIR_WS_LANGUAGES . 'en/' . $template_dir . '/' . FILENAME_HEADER)) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . 'en/' . $template_dir_select . FILENAME_HEADER);

// include template specific button name defines
  if (file_exists(DIR_WS_LANGUAGES . 'en/' . $template_dir . '/' . FILENAME_BUTTON_NAMES)) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . 'en/' . $template_dir_select . FILENAME_BUTTON_NAMES);

// include template specific icon name defines
  if (file_exists(DIR_WS_LANGUAGES . 'en/' . $template_dir . '/' . FILENAME_ICON_NAMES)) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . 'en/' . $template_dir_select . FILENAME_ICON_NAMES);

// include template specific other image name defines
  if (file_exists(DIR_WS_LANGUAGES . 'en/' . $template_dir . '/' . FILENAME_OTHER_IMAGES_NAMES)) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . 'en/' . $template_dir_select . FILENAME_OTHER_IMAGES_NAMES);

// credit cards
  require_once(DIR_WS_LANGUAGES . 'en/' . FILENAME_CREDIT_CARDS);

// include template specific whos_online sidebox defines
  if (file_exists(DIR_WS_LANGUAGES . 'en/' . $template_dir . '/' . FILENAME_WHOS_ONLINE . '.php')) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . 'en/' . $template_dir_select . FILENAME_WHOS_ONLINE . '.php');

// include template specific meta tags defines
  if (file_exists(DIR_WS_LANGUAGES . 'en/' . $template_dir . '/meta_tags.php')) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . 'en/' . $template_dir_select . 'meta_tags.php');

// END OF EXTERNAL LANGUAGE LINKS
?>

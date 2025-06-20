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
//  $Id$
//

// look in your $PATH_LOCALE/locale directory for available locales..
// on RedHat6.0 I used 'en_US'
// on FreeBSD 4.0 I use 'en_US.ISO_8859-1'
// this may not work under win32 environments..
define('DATE_FORMAT_SHORT', '%m/%d/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('DATE_FORMAT', 'm/d/Y'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
define('PHP_DATE_TIME_FORMAT', 'm/d/Y H:i:s'); // this is used for date()
define('DATE_FORMAT_SPIFFYCAL', 'MM/dd/yyyy');  //Use only 'dd', 'MM' and 'yyyy' here in any order

// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="en"');

// charset for web pages and emails
define('CHARSET', 'utf-8');

// include template specific meta tags defines
  if (file_exists(DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/' . $gCommerceSystem->mTemplateDir . '/meta_tags.php')) {
    $template_dir_select = $gCommerceSystem->mTemplateDir . '/';
  } else {
    $template_dir_select = '';
  }
  require(DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/' . $template_dir_select . 'meta_tags.php');
//die(DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/' . $template_dir_select . 'meta_tags.php');

// meta tags
define('ICON_METATAGS_ON', 'Meta Tags Defined');
define('ICON_METATAGS_OFF', 'Meta Tags Undefined');
define('TEXT_LEGEND_META_TAGS', 'Meta Tags Defined:');
define('TEXT_INFO_META_TAGS_USAGE', '<strong>NOTE:</strong> The Site/Tagline is your defined definition for your site in the meta_tags.php file.');

// removed for meta tags
// page title
//define('TITLE', 'Zen Cart');

// header text in includes/header.php
define('HEADER_TITLE_TOP', 'Admin Home');
define('HEADER_TITLE_SUPPORT_SITE', 'Support Site');
define('HEADER_TITLE_ONLINE_CATALOG', 'Online Catalog');
define('HEADER_TITLE_LOGOFF', 'Logoff');
//define('HEADER_TITLE_ADMINISTRATION', 'Administration');

// added defines for header alt and text
define('HEADER_ALT_TEXT', 'Admin Powered by Zen Cart :: The Art of E-Commerce');
define('HEADER_LOGO_WIDTH', '200px');
define('HEADER_LOGO_HEIGHT', '70px');
define('HEADER_LOGO_IMAGE', 'logo.gif');

// Define the name of your Gift Certificate as Gift Voucher, Gift Certificate, Zen Cart Dollars, etc. here for use through out the shop
  define('TEXT_GV_NAME','Gift Certificate');
  define('TEXT_GV_NAMES','Gift Certificates');

// used for redeem code, redemption code, or redemption id
  define('TEXT_GV_REDEEM','Redemption Code');

// text for gender
define('MALE', 'Male');
define('FEMALE', 'Female');

// text for date of birth example
define('DOB_FORMAT_STRING', 'mm/dd/yyyy');

// define pages editor files
define('BOX_TOOLS_DEFINE_PAGES_EDITOR','Define Pages Editor');
define('BOX_TOOLS_DEFINE_MAIN_PAGE', 'Main Page');
define('BOX_TOOLS_DEFINE_CONTACT_US','Contact Us');
define('BOX_TOOLS_DEFINE_PRIVACY','Privacy');
define('BOX_TOOLS_DEFINE_SHIPPINGINFO','Shipping & Returns');
define('BOX_TOOLS_DEFINE_CONDITIONS','Conditions of Use');
define('BOX_TOOLS_DEFINE_CHECKOUT_SUCCESS','Checkout Success');
define('BOX_TOOLS_DEFINE_PAGE_2','Page 2');
define('BOX_TOOLS_DEFINE_PAGE_3','Page 3');
define('BOX_TOOLS_DEFINE_PAGE_4','Page 4');


// localizaion box text in includes/boxes/localization.php
define('BOX_HEADING_LOCALIZATION', 'Localization');
define('BOX_LOCALIZATION_CURRENCIES', 'Currencies');
define('BOX_LOCALIZATION_LANGUAGES', 'Languages');
define('BOX_LOCALIZATION_ORDERS_STATUS', 'Orders Status');

// gift vouchers box text in includes/boxes/gv_admin.php
define('BOX_HEADING_GV_ADMIN', TEXT_GV_NAME . '/Coupons');
define('BOX_GV_ADMIN_QUEUE',  TEXT_GV_NAMES . ' Queue');
define('BOX_GV_ADMIN_MAIL', 'Mail ' . TEXT_GV_NAME);
define('BOX_GV_ADMIN_SENT', TEXT_GV_NAMES . ' sent');
define('BOX_COUPON_ADMIN','Coupon Admin');

define('IMAGE_RELEASE', 'Redeem ' . TEXT_GV_NAME);

// javascript messages
define('JS_ERROR', 'Errors have occurred during the processing of your form!\nPlease make the following corrections:\n\n');

define('JS_OPTIONS_VALUE_PRICE', '* The new product attribute needs a price value\n');
define('JS_OPTIONS_VALUE_PRICE_PREFIX', '* The new product attribute needs a price prefix\n');

define('JS_PRODUCTS_NAME', '* The new product needs a name\n');
define('JS_PRODUCTS_DESCRIPTION', '* The new product needs a description\n');
define('JS_PRODUCTS_PRICE', '* The new product needs a price value\n');
define('JS_PRODUCTS_WEIGHT', '* The new product needs a weight value\n');
define('JS_PRODUCTS_QUANTITY', '* The new product needs a quantity value\n');
define('JS_PRODUCTS_MODEL', '* The new product needs a model value\n');
define('JS_PRODUCTS_IMAGE', '* The new product needs an image value\n');

define('JS_SPECIALS_PRODUCTS_PRICE', '* A new price for this product needs to be set\n');

define('JS_GENDER', '* The \'Gender\' value must be chosen.\n');
define('JS_FIRST_NAME', '* The \'First Name\' entry must have at least ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' characters.\n');
define('JS_LAST_NAME', '* The \'Last Name\' entry must have at least ' . ENTRY_LAST_NAME_MIN_LENGTH . ' characters.\n');
define('JS_DOB', '* The \'Date of Birth\' entry must be in the format: xx/xx/xxxx (month/date/year).\n');
define('JS_EMAIL_ADDRESS', '* The \'E-Mail Address\' entry must have at least ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' characters.\n');
define('JS_ADDRESS', '* The \'Street Address\' entry must have at least ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' characters.\n');
define('JS_POST_CODE', '* The \'Post Code\' entry must have at least ' . ENTRY_POSTCODE_MIN_LENGTH . ' characters.\n');
define('JS_CITY', '* The \'City\' entry must have at least ' . ENTRY_CITY_MIN_LENGTH . ' characters.\n');
define('JS_STATE', '* The \'State\' entry is must be selected.\n');
define('JS_STATE_SELECT', '-- Select Above --');
define('JS_ZONE', '* The \'State\' entry must be selected from the list for this country.');
define('JS_COUNTRY', '* The \'Country\' value must be chosen.\n');
define('JS_TELEPHONE', '* The \'Telephone Number\' entry must have at least ' . ENTRY_TELEPHONE_MIN_LENGTH . ' characters.\n');
define('JS_PASSWORD', '* The \'Password\' and \'Confirmation\' entries must match and have at least ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.\n');

define('JS_ORDER_DOES_NOT_EXIST', 'Order Number %s does not exist!');

define('CATEGORY_PERSONAL', 'Personal');
define('CATEGORY_ADDRESS', 'Address');
define('CATEGORY_CONTACT', 'Contact');
define('CATEGORY_COMPANY', 'Company');
define('CATEGORY_OPTIONS', 'Options');

define('ENTRY_GENDER', 'Gender:');
define('ENTRY_GENDER_ERROR', '&nbsp;<span class="errorText">required</span>');
define('ENTRY_FIRST_NAME', 'First Name:');
define('ENTRY_FIRST_NAME_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' chars</span>');
define('ENTRY_LAST_NAME', 'Last Name:');
define('ENTRY_LAST_NAME_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_LAST_NAME_MIN_LENGTH . ' chars</span>');
define('ENTRY_DATE_OF_BIRTH', 'Date of Birth:');
define('ENTRY_DATE_OF_BIRTH_ERROR', '&nbsp;<span class="errorText">(eg. 05/21/1970)</span>');
define('ENTRY_EMAIL_ADDRESS', 'E-Mail Address:');
define('ENTRY_EMAIL_ADDRESS_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' chars</span>');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', '&nbsp;<span class="errorText">The email address doesn\'t appear to be valid!</span>');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', '&nbsp;<span class="errorText">This email address already exists!</span>');
define('ENTRY_COMPANY', 'Company name:');
define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_PRICING_GROUP', 'Discount Pricing Group');
define('ENTRY_STREET_ADDRESS', 'Street Address:');
define('ENTRY_STREET_ADDRESS_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' chars</span>');
define('ENTRY_SUBURB', 'Suburb:');
define('ENTRY_SUBURB_ERROR', '');
define('ENTRY_POST_CODE', 'Post Code:');
define('ENTRY_POST_CODE_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_POSTCODE_MIN_LENGTH . ' chars</span>');
define('ENTRY_CITY', 'City:');
define('ENTRY_CITY_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_CITY_MIN_LENGTH . ' chars</span>');
define('ENTRY_STATE', 'State:');
define('ENTRY_STATE_ERROR', '&nbsp;<span class="errorText">required</span>');
define('ENTRY_COUNTRY', 'Country:');
define('ENTRY_COUNTRY_ERROR', '');
define('ENTRY_TELEPHONE_NUMBER', 'Telephone Number:');
define('ENTRY_TELEPHONE_NUMBER_ERROR', '&nbsp;<span class="errorText">min ' . ENTRY_TELEPHONE_MIN_LENGTH . ' chars</span>');
define('ENTRY_FAX_NUMBER', 'Fax Number:');
define('ENTRY_FAX_NUMBER_ERROR', '');
define('ENTRY_NEWSLETTER', 'Newsletter:');
define('ENTRY_NEWSLETTER_YES', 'Subscribed');
define('ENTRY_NEWSLETTER_NO', 'Unsubscribed');
define('ENTRY_NEWSLETTER_ERROR', '');

// images
define('IMAGE_ANI_SEND_EMAIL', 'Sending E-Mail');
define('IMAGE_BACK', 'Back');
define('IMAGE_BACKUP', 'Backup');
define('IMAGE_CANCEL', 'Cancel');
define('IMAGE_CONFIRM', 'Confirm');
define('IMAGE_COPY', 'Copy');
define('IMAGE_COPY_TO', 'Copy To');
define('IMAGE_DETAILS', 'Details');
define('IMAGE_DELETE', 'Delete');
define('IMAGE_EDIT', 'Edit');
define('IMAGE_EMAIL', 'Email');
define('IMAGE_FILE_MANAGER', 'File Manager');
define('IMAGE_ICON_STATUS_GREEN', 'Active');
define('IMAGE_ICON_STATUS_GREEN_LIGHT', 'Set Active');
define('IMAGE_ICON_STATUS_RED', 'Inactive');
define('IMAGE_ICON_STATUS_RED_LIGHT', 'Set Inactive');
define('IMAGE_ICON_INFO', 'Info');
define('IMAGE_INSERT', 'Insert');
define('IMAGE_LOCK', 'Lock');
define('IMAGE_MODULE_INSTALL', 'Install Module');
define('IMAGE_MODULE_REMOVE', 'Remove Module');
define('IMAGE_MOVE', 'Move');
define('IMAGE_NEW_BANNER', 'New Banner');
define('IMAGE_NEW_CATEGORY', 'New Category');
define('IMAGE_NEW_COUNTRY', 'New Country');
define('IMAGE_NEW_CURRENCY', 'New Currency');
define('IMAGE_NEW_FILE', 'New File');
define('IMAGE_NEW_FOLDER', 'New Folder');
define('IMAGE_NEW_LANGUAGE', 'New Language');
define('IMAGE_NEW_NEWSLETTER', 'New Newsletter');
define('IMAGE_NEW_PRODUCT', 'New Product');
define('IMAGE_NEW_SALE', 'New Sale');
define('IMAGE_NEW_TAX_CLASS', 'New Tax Class');
define('IMAGE_NEW_TAX_RATE', 'New Tax Rate');
define('IMAGE_NEW_TAX_ZONE', 'New Tax Zone');
define('IMAGE_NEW_ZONE', 'New Zone');
define('IMAGE_OPTION_NAMES', 'Option Names Manager');
define('IMAGE_OPTION_VALUES', 'Option Values Manager');
define('IMAGE_ORDERS', 'Orders');
define('IMAGE_ORDERS_INVOICE', 'Invoice');
define('IMAGE_ORDERS_PACKINGSLIP', 'Packing Slip');
define('IMAGE_PREVIEW', 'Preview');
define('IMAGE_RESTORE', 'Restore');
define('IMAGE_RESET', 'Reset');
define('IMAGE_SAVE', 'Save');
define('IMAGE_SEARCH', 'Search');
define('IMAGE_SELECT', 'Select');
define('IMAGE_SEND', 'Send');
define('IMAGE_SEND_EMAIL', 'Send Email');
define('IMAGE_UNLOCK', 'Unlock');
define('IMAGE_UPDATE', 'Update');
define('IMAGE_UPDATE_CURRENCIES', 'Update Exchange Rate');
define('IMAGE_UPLOAD', 'Upload');
define('IMAGE_TAX_RATES','Tax Rate');
define('IMAGE_DEFINE_ZONES','Define Zones');
define('IMAGE_PRODUCTS_PRICE_MANAGER', 'Products Price Manager');
define('IMAGE_UPDATE_PRICE_CHANGES', 'Update Price Changes');
define('IMAGE_ADD_BLANK_DISCOUNTS','Add ' . DISCOUNT_QTY_ADD . ' Blank Discount Qty');
define('IMAGE_CHECK_VERSION', 'Check for Updates to Zen Cart');
define('IMAGE_PRODUCTS_TO_CATEGORIES', 'Multiple Categories Link Manager');

define('IMAGE_ICON_STATUS_ON', 'Status - Enabled');
define('IMAGE_ICON_STATUS_OFF', 'Status - Disabled');
define('IMAGE_ICON_LINKED', 'Product is Linked');

define('IMAGE_REMOVE_SPECIAL','Remove Special Price Info');
define('IMAGE_REMOVE_FEATURED','Remove Featured Product Info');
define('IMAGE_INSTALL_SPECIAL', 'Add Special Price Info');
define('IMAGE_INSTALL_FEATURED', 'Add Featured Product Info');

define('ICON_PRODUCTS_PRICE_MANAGER','Products Price Manager');
define('ICON_COPY_TO', 'Copy to');
define('ICON_CROSS', 'False');
define('ICON_CURRENT_FOLDER', 'Current Folder');
define('ICON_DELETE', 'Delete');
define('ICON_EDIT', 'Edit');
define('ICON_ERROR', 'Error');
define('ICON_FILE', 'File');
define('ICON_FILE_DOWNLOAD', 'Download');
define('ICON_FOLDER', 'Folder');
define('ICON_LOCKED', 'Locked');
define('ICON_MOVE', 'Move');
define('ICON_PREVIOUS_LEVEL', 'Previous Level');
define('ICON_PREVIEW', 'Preview');
define('ICON_RESET', 'Reset');
define('ICON_STATISTICS', 'Statistics');
define('ICON_SUCCESS', 'Success');
define('ICON_TICK', 'True');
define('ICON_UNLOCKED', 'Unlocked');
define('ICON_WARNING', 'Warning');

// constants for use in zen_prev_next_display function
define('TEXT_RESULT_PAGE', 'Page %s of %d');
define('TEXT_DISPLAY_NUMBER_OF_ADMINS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> admins)');
define('TEXT_DISPLAY_NUMBER_OF_BANNERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> banners)');
define('TEXT_DISPLAY_NUMBER_OF_CATEGORIES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> categories)');
define('TEXT_DISPLAY_NUMBER_OF_COUNTRIES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> countries)');
define('TEXT_DISPLAY_NUMBER_OF_CUSTOMERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> customers)');
define('TEXT_DISPLAY_NUMBER_OF_CURRENCIES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> currencies)');
define('TEXT_DISPLAY_NUMBER_OF_LANGUAGES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> languages)');
define('TEXT_DISPLAY_NUMBER_OF_MANUFACTURERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> manufacturers)');
define('TEXT_DISPLAY_NUMBER_OF_NEWSLETTERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> newsletters)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> orders)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> orders status)');
define('TEXT_DISPLAY_NUMBER_OF_PRICING_GROUPS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> pricing groups)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> products)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCT_TYPES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> product types)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_EXPECTED', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> products expected)');
define('TEXT_DISPLAY_NUMBER_OF_REVIEWS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> product reviews)');
define('TEXT_DISPLAY_NUMBER_OF_SALES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> sales)');
define('TEXT_DISPLAY_NUMBER_OF_SPECIALS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> products on special)');
define('TEXT_DISPLAY_NUMBER_OF_SUPPLIERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> suppliers)');
define('TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> tax classes)');
define('TEXT_DISPLAY_NUMBER_OF_TEMPLATES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> template associations)');
define('TEXT_DISPLAY_NUMBER_OF_TAX_ZONES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> tax zones)');
define('TEXT_DISPLAY_NUMBER_OF_TAX_RATES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> tax rates)');
define('TEXT_DISPLAY_NUMBER_OF_ZONES', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> zones)');

define('PREVNEXT_BUTTON_PREV', '&lt;&lt;');
define('PREVNEXT_BUTTON_NEXT', '&gt;&gt;');


define('TEXT_DEFAULT', 'default');
define('TEXT_SET_DEFAULT', 'Set as default');
define('TEXT_FIELD_REQUIRED', '&nbsp;<span class="fieldRequired">* Required</span>');

define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_STATUS', 'Status');

define('ERROR_NO_DEFAULT_CURRENCY_DEFINED', 'Error: There is currently no default currency set. Please set one at: Administration Tool->Localization->Currencies');

define('TEXT_PRODUCTS_IMAGE_DIR', 'Upload to directory:');
define('TEXT_PRODUCTS', 'Products:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_DATE_ADDED', 'Date Added:');

define('TEXT_CACHE_CATEGORIES', 'Categories Box');
define('TEXT_CACHE_MANUFACTURERS', 'Manufacturers Box');
// define('TEXT_CACHE_MANUFACTURERS', 'Suppliers Box');
define('TEXT_CACHE_ALSO_PURCHASED', 'Also Purchased Module');

define('TEXT_NONE', '--none--');
define('TEXT_TOP', 'Top');

define('ERROR_DIRECTORY_NOT_WRITEABLE', 'Error: I can not write to this directory. Please set the right user permissions on: %s');
define('ERROR_DIRECTORY_DOES_NOT_EXIST', 'Error: Directory does not exist: %s');
define('ERROR_DESTINATION_DOES_NOT_EXIST', 'Error: Destination does not exist %s');
define('ERROR_DESTINATION_NOT_WRITEABLE', 'Error: Destination not writeable %s');
define('ERROR_FILE_NOT_SAVED', 'Error: File upload not saved.');
define('ERROR_FILETYPE_NOT_ALLOWED', 'Error: File upload type not allowed  %s');
define('SUCCESS_FILE_SAVED_SUCCESSFULLY', 'Success: File upload saved successfully %s');
define('WARNING_NO_FILE_UPLOADED', 'Warning: No file uploaded.');
define('WARNING_FILE_UPLOADS_DISABLED', 'Warning: File uploads are disabled in the php.ini configuration file.');
define('ERROR_ADMIN_SECURITY_WARNING', 'Warning: Your Admin login is not secure ... either you still have default login settings for: Admin admin or have not removed or changed: demo demoonly<br />The login(s) should be changed as soon as possible for the Security of your Shop<br />For additional Security for the Admin please see the /docs');
define('WARNING_DATABASE_VERSION_OUT_OF_DATE','Your database appears to need patching to a higher level. See Tools->Server Information to review patch levels.');
define('WARN_DATABASE_VERSION_PROBLEM','true'); //set to false to turn off warnings about database version mismatches

define('_JANUARY', 'January');
define('_FEBRUARY', 'February');
define('_MARCH', 'March');
define('_APRIL', 'April');
define('_MAY', 'May');
define('_JUNE', 'June');
define('_JULY', 'July');
define('_AUGUST', 'August');
define('_SEPTEMBER', 'September');
define('_OCTOBER', 'October');
define('_NOVEMBER', 'November');
define('_DECEMBER', 'December');

define('TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> gift vouchers)');
define('TEXT_DISPLAY_NUMBER_OF_COUPONS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> coupons)');

define('TEXT_VALID_PRODUCTS_LIST', 'Products List');
define('TEXT_VALID_PRODUCTS_ID', 'Products ID');
define('TEXT_VALID_PRODUCTS_NAME', 'Products Name');
define('TEXT_VALID_PRODUCTS_MODEL', 'Products Model');

define('TEXT_VALID_CATEGORIES_LIST', 'Categories List');
define('TEXT_VALID_CATEGORIES_ID', 'Category ID');
define('TEXT_VALID_CATEGORIES_NAME', 'Category Name');

define('DEFINE_LANGUAGE','Define Language:');

define('BOX_ENTRY_COUNTER_DATE','Hit Counter Started:');
define('BOX_ENTRY_COUNTER','Hit Counter:');

// not installed
define('NOT_INSTALLED_TEXT','Not Installed');

// Product Options Values Sort Order - option_values.php
  define('BOX_CATALOG_PRODUCT_OPTIONS_VALUES','Option Value Sorter ');

  define('TEXT_UPDATE_SORT_ORDERS_OPTIONS','<strong>Update Attribute Sort Order from Option Value Defaults</strong> ');
  define('TEXT_INFO_ATTRIBUTES_FEATURES_UPDATES','<strong>Update All Products\' Attribute Sort Orders</strong><br />to match Option Value Default Sort Orders:<br />');

// Product Options Name Sort Order - option_values.php
  define('BOX_CATALOG_PRODUCT_OPTIONS_NAME','Option Name Sorter');

// Attributes only
  define('BOX_CATALOG_CATEGORIES_ATTRIBUTES_CONTROLLER','Attributes Controller');

// generic model
  define('TEXT_MODEL','Model:');

// column controller
  define('BOX_TOOLS_LAYOUT_CONTROLLER','Layout Boxes Controller');

// check GV release queue and alert store owner
  define('SHOW_GV_QUEUE',true);
  define('TEXT_SHOW_GV_QUEUE','%s waiting approval ');
  define('IMAGE_GIFT_QUEUE', TEXT_GV_NAME . ' Queue');
  define('IMAGE_ORDER','Order');

  define('BOX_TOOLS_EMAIL_WELCOME','Email Welcome');

  define('IMAGE_DISPLAY','Display');
  define('IMAGE_UPDATE_SORT','Update Sort Order');
  define('IMAGE_EDIT_PRODUCT','Edit Product');
  define('IMAGE_EDIT_ATTRIBUTES','Edit Attributes');
  define('TEXT_NEW_PRODUCT', 'Product in Category: %s');
  define('IMAGE_OPTIONS_VALUES','Option Names and Option Values');
  define('TEXT_PRODUCTS_PRICE_MANAGER','PRODUCTS PRICE MANAGER');
  define('TEXT_PRODUCT_EDIT','EDIT PRODUCT');
  define('TEXT_ATTRIBUTE_EDIT','EDIT ATTRIBUTES');
  define('TEXT_PRODUCT_DETAILS','VIEW DETAILS');

// sale maker
  define('DEDUCTION_TYPE_DROPDOWN_0', 'Deduct amount');
  define('DEDUCTION_TYPE_DROPDOWN_1', 'Percent');
  define('DEDUCTION_TYPE_DROPDOWN_2', 'New Price');

// Min and Units
  define('PRODUCTS_QUANTITY_MIN_TEXT_LISTING','Min:');
  define('PRODUCTS_QUANTITY_UNIT_TEXT_LISTING','Units:');
  define('PRODUCTS_QUANTITY_IN_CART_LISTING','In cart:');
  define('PRODUCTS_QUANTITY_ADD_ADDITIONAL_LISTING','Add Additional:');

  define('TEXT_PRODUCTS_MIX_OFF','*No Mixed Options');
  define('TEXT_PRODUCTS_MIX_ON','*Yes Mixed Options');

// search filters
  define('TEXT_INFO_SEARCH_DETAIL_FILTER','Search Filter: ');
  define('HEADING_TITLE_SEARCH_DETAIL','Search: ');

  define('PREV_NEXT_PRODUCT', 'Products: ');
  define('TEXT_CATEGORIES_STATUS_INFO_OFF', '<span class="alert alert-warning">*Category is Disabled</span>');
  define('TEXT_PRODUCTS_STATUS_INFO_OFF', '<span class="alert alert-warning">*Product is Disabled</span>');

// admin demo
  define('ADMIN_DEMO_ACTIVE','Admin Demo is currently Active. Some settings are will be disabled');
  define('ADMIN_DEMO_ACTIVE_EXCLUSION','Admin Demo is currently Active. Some settings are will be disabled - <strong>NOTE: Admin Override Enabled</strong>');
  define('ERROR_ADMIN_DEMO','Admin Demo is Active ... the feature(s) you are trying to perform have been disabled');

// Version Check notices
  define('TEXT_VERSION_CHECK_NEW_VER','New Version Available V');
  define('TEXT_VERSION_CHECK_NEW_PATCH','New PATCH Available: V');
  define('TEXT_VERSION_CHECK_PATCH','patch');
  define('TEXT_VERSION_CHECK_DOWNLOAD','Download Here');
  define('TEXT_VERSION_CHECK_CURRENT','Your version of Zen Cart&trade; appears to be current.');

// downloads manager
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_DOWNLOADS_MANAGER', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> downloads)');
define('BOX_CATALOG_CATEGORIES_ATTRIBUTES_DOWNLOADS_MANAGER', 'Downloads Manager');

define('BOX_CATALOG_FEATURED','Featured Products');

define('ERROR_NOTHING_SELECTED', 'Nothing was selected ... No changes have been made');
define('TEXT_STATUS_WARNING','<strong>NOTE:</strong> status is auto enabled/disabled when dates are set');

define('TEXT_LEGEND_LINKED', 'Linked Product');
define('TEXT_MASTER_CATEGORIES_ID','Product Master Category:');
define('TEXT_LEGEND', 'LEGEND: ');
define('TEXT_LEGEND_STATUS_OFF', 'Status OFF ');
define('TEXT_LEGEND_STATUS_ON', 'Status ON ');

define('TEXT_INFO_MASTER_CATEGORIES_ID', '<strong>NOTE: Master Category is used for pricing purposes where the<br />product category affects the pricing on linked products, example: Sales</strong>');
define('TEXT_YES', 'Yes');
define('TEXT_NO', 'No');

// shipping error messages
define('ERROR_SHIPPING_CONFIGURATION', '<strong>Shipping Configuration errors!</strong>');
define('ERROR_SHIPPING_ORIGIN_ZIP', '<strong>Warning:</strong> Store Zip Code is not defined. See <a href="'.BITCOMMERCE_PKG_URL.'admin/configuration.php?gID=7&cID=161&action=edit">Configuration | Shipping/Packaging</a> to set it.');
define('ERROR_ORDER_WEIGHT_ZERO_STATUS', '<strong>Warning:</strong> 0 weight is configured for Free Shipping and Free Shipping Module is not enabled');
define('ERROR_USPS_STATUS', '<strong>Warning:</strong> USPS is either missing the username and/or password, or ... it is set to TEST rather than PRODUCTION and will not work<br />If you still cannot retrieve quotes, contact USPS and activate your live account');

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
define('TEXT_CATEGORIES_PRODUCTS', 'Select a Category with Products ... Or move between the Products');
define('TEXT_PRODUCT_TO_VIEW', 'Select a Product to View and Press Display ...');

define('TEXT_INFO_SET_MASTER_CATEGORIES_ID', 'Invalid Master Category ID');
define('TEXT_INFO_ID', ' ID# ');
define('TEXT_INFO_SET_MASTER_CATEGORIES_ID_WARNING', '<strong>WARNING:</strong> This product is linked to multiple categories but no master category has been set!');

define('PRODUCTS_PRICE_IS_CALL_FOR_PRICE_TEXT', 'Product is Call for Price');
define('PRODUCTS_PRICE_IS_FREE_TEXT','Product is Free');

// min, max, units
define('PRODUCTS_QUANTITY_MAX_TEXT_LISTING', 'Max:');

// Discount Savings
  define('PRODUCT_PRICE_DISCOUNT_PREFIX','Save:&nbsp;');
  define('PRODUCT_PRICE_DISCOUNT_PERCENTAGE','% off');
  define('PRODUCT_PRICE_DISCOUNT_AMOUNT','&nbsp;off');
// Sale Maker Sale Price
  define('PRODUCT_PRICE_SALE','Sale:&nbsp;');

// Rich Text / HTML resources
define('TEXT_HTML_EDITOR_NOT_DEFINED','If you have no HTML editor defined or JavaScript disabled, you may enter raw HTML text here manually.');
define('TEXT_WARNING_HTML_DISABLED','<span class = "main">Note: You are using TEXT only email. If you would like to send HTML you need to enable "use MIME HTML" under Email Options</span>');
define('TEXT_WARNING_CANT_DISPLAY_HTML','<span class = "main">Note: You are using TEXT only email. If you would like to send HTML you need to enable "use MIME HTML" under Email Options</span>');
define('TEXT_EMAIL_CLIENT_CANT_DISPLAY_HTML',"You're seeing this text because we sent you an email in HTML format but your email client cannot display HTML messages.");
define('ENTRY_EMAIL_PREFERENCE','Email Format Pref:');
define('ENTRY_EMAIL_FORMAT_COMMENTS','Choosing "none" or "optout" disables ALL mail, including order details');
define('ENTRY_EMAIL_HTML_DISPLAY','HTML');
define('ENTRY_EMAIL_TEXT_DISPLAY','TEXT-Only');
define('ENTRY_EMAIL_NONE_DISPLAY','Never');
define('ENTRY_EMAIL_OPTOUT_DISPLAY','Opted Out of Newsletters');
define('ENTRY_NOTHING_TO_SEND','You haven\'t entered any content for your message');
 define('EMAIL_SEND_FAILED','ERROR: Failed sending email to: "%s" <%s> with subject: "%s"');

// toggle on/off
  define('TEXT_HTML_AREA', 'HTMLarea');
  define('TEXT_EDITOR_INFO', 'Text Editor');
  define('TEXT_CATEGORIES_PRODUCTS_SORT_ORDER_INFO', 'Product Sort');
  define('TEXT_SORT_PRODUCTS_SORT_ORDER_PRODUCTS_NAME', 'Products Sort Order, Products Name');
  define('TEXT_SORT_PRODUCTS_NAME', 'Products Name');
  define('TEXT_SORT_PRODUCTS_MODEL', 'Products Model');

  define('TABLE_HEADING_YES','Yes');
  define('TABLE_HEADING_NO','No');
  define('TEXT_IMAGES_OVERWRITE', 'Overwrite Existing Image? Use No for manually typed names');
  define('TEXT_IMAGE_OVERWRITE_WARNING','WARNING: FILENAME was updated but not overwritten ');

  define('ERROR_DEFINE_OPTION_NAMES', 'Warning: No Option Names have been defined');
  define('ERROR_DEFINE_OPTION_VALUES', 'Warning: No Option Values have been defined');
  define('ERROR_DEFINE_PRODUCTS', 'Warning: No Products have been defined');

define('BUTTON_ADD_PRODUCT_TYPES_SUBCATEGORIES_ON','Add include SubCategories');
define('BUTTON_ADD_PRODUCT_TYPES_SUBCATEGORIES_OFF','Add without SubCategories');

define('BUTTON_PREVIOUS_ALT','Previous Product');
define('BUTTON_NEXT_ALT','Next Product');

define('BUTTON_PRODUCTS_TO_CATEGORIES', 'Multiple Categories Link Manager');
define('BUTTON_PRODUCTS_TO_CATEGORIES_ALT', 'Copy Product to Multiple Categories');

define('TEXT_INFO_OPTION_NAMES_VALUES_COPIER_STATUS', 'All Global Copy, Add and Delete Features Status is currently OFF');
define('TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_ON', 'Display Global Features - ON');
define('TEXT_SHOW_OPTION_NAMES_VALUES_COPIER_OFF', 'Display Global Features - OFF');


///////////////////////////////////////////////////////////
// include additional files:
  require(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/' . FILENAME_EMAIL_EXTRAS);
  include(zen_get_file_directory(DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/', FILENAME_OTHER_IMAGES_NAMES, 'false'));


?>

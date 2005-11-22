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
//  $Id: suppliers.php,v 1.1 2005/11/22 11:06:25 gilesw Exp $
//

define('HEADING_TITLE', 'Suppliers');

define('TABLE_HEADING_SUPPLIERS', 'Suppliers');
define('TABLE_HEADING_ACTION', 'Action');

define('TEXT_HEADING_NEW_MANUFACTURER', 'New Supplier');
define('TEXT_HEADING_EDIT_MANUFACTURER', 'Edit Supplier');
define('TEXT_HEADING_DELETE_MANUFACTURER', 'Delete Supplier');

define('TEXT_SUPPLIERS', 'Suppliers:');
define('TEXT_DATE_ADDED', 'Date Added:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_PRODUCTS', 'Products:');
define('TEXT_PRODUCTS_IMAGE_DIR', 'Upload to directory:');
define('TEXT_IMAGE_NONEXISTENT', 'IMAGE DOES NOT EXIST');

define('TEXT_NEW_INTRO', 'Please fill out the following information for the new supplier');
define('TEXT_EDIT_INTRO', 'Please make any necessary changes');

define('TEXT_SUPPLIERS_NAME', 'Suppliers Name:');
define('TEXT_SUPPLIERS_IMAGE', 'Suppliers Image:');
define('TEXT_SUPPLIERS_URL', 'Suppliers URL:');

define('TEXT_DELETE_INTRO', 'Are you sure you want to delete this supplier?');
define('TEXT_DELETE_IMAGE', 'Delete suppliers image?');
define('TEXT_DELETE_PRODUCTS', 'Delete products from this supplier? (including product reviews, products on special, upcoming products)');
define('TEXT_DELETE_WARNING_PRODUCTS', '<b>WARNING:</b> There are %s products still linked to this supplier!');

define('ERROR_DIRECTORY_NOT_WRITEABLE', 'Error: I can not write to this directory. Please set the right user permissions on: %s');
define('ERROR_DIRECTORY_DOES_NOT_EXIST', 'Error: Directory does not exist: %s');
?>
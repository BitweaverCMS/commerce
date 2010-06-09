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

/**
* $display_output defines
*/
// file uploads display - output via $display_output
define('EASYPOPULATE_DISPLAY_SPLIT_LOCATION','You can also download your split files from your %s directory<br />');
define('EASYPOPULATE_DISPLAY_HEADING','<br /><b><u>Upload Results</u></b><br />');
define('EASYPOPULATE_DISPLAY_UPLOADED_FILE_SPEC','<p class=smallText>File uploaded.<br />Temporary filename: %s<br><b>User filename: %s</b><br>Size: %s<br>'); // open paragraph
define('EASYPOPULATE_DISPLAY_LOCAL_FILE_SPEC','<p class=smallText><b>Filename: %s</b><br />'); // open paragraph

// upload results display - output via $display_output
define('EASYPOPULATE_DISPLAY_RESULT_DELETED','<br /><font color="fuchsia"><b>DELETED! - Model:</b> %s</font>');
define('EASYPOPULATE_DISPLAY_RESULT_DELETE_NOT_FOUND','<br /><font color="darkviolet"><b>NOT FOUND! - Model:</b> %s - cant delete...</font>');
define('EASYPOPULATE_DISPLAY_RESULT_CATEGORY_NOT_FOUND', '<br /><font color="red"><b>SKIPPED! - Model:</b> %s - No category provided for this%s product</font>');
define('EASYPOPULATE_DISPLAY_RESULT_CATEGORY_NAME_LONG','<br /><font color="red"><b>SKIPPED! - Model:</b> %s - Category name(s) too long (max. %s)</font>');
define('EASYPOPULATE_DISPLAY_RESULT_MODEL_NAME_LONG','<br /><font color="red"><b>SKIPPED! - Model: </b>%s - model name too long</font>');
define('EASYPOPULATE_DISPLAY_RESULT_NEW_PRODUCT', '<br /><font color="green"><b>NEW PRODUCT! - Model:</b> %s</font> | ');
define('EASYPOPULATE_DISPLAY_RESULT_NEW_PRODUCT_FAIL', '<br /><font color="red"><b>ADD NEW PRODUCT FAILED! - Model:</b> %s - SQL error. Check EasyPopulate error log in uploads directory</font>');
define('EASYPOPULATE_DISPLAY_RESULT_UPDATE_PRODUCT', '<br /><font color="mediumblue"><b>UPDATED! - Model:</b> %s</font> | ');
define('EASYPOPULATE_DISPLAY_RESULT_UPDATE_PRODUCT_FAIL', '<br /><font color="red"><b>UPDATE PRODUCT FAILED! - Model:</b> %s - SQL error. Check EasyPopulate error log in uploads directory</font>');
define('EASYPOPULATE_DISPLAY_RESULT_NO_MODEL', '<br /><font color="red"><b>No model field in record. This line was not imported</b></font>');
define('EASYPOPULATE_DISPLAY_RESULT_UPLOAD_COMPLETE','<br /><b>Upload Complete</b></p>'); // close paragraph above


/**
* $messageStack defines
*/
// checks - msg stack alerts - output via $messageStack
define('EASYPOPULATE_MSGSTACK_TEMP_FOLDER_MISSING','<b>EasyPopulate uploads folder not found!</b><br />NIX SERVERS: Your uploads folder is either missing, or you have altered the name and/or directory of your uploads folder without configuring this in EasyPopulate.<br />WINDOWS SERVERS: Please request your web host to assign write permissions to the uploads folder. This is usually granted through Windows server user account IUSR_COMPUTERNAME.<br />Your configuration indicates that your uploads folder is named <b>%s</b>, and is located in <b>%s</b>, however this cannot be found.<br />EasyPopulate cannot upload files until you have provided an uploads folder with read/write/execute permissions for the site owner (chmod 700)');
define('EASYPOPULATE_MSGSTACK_TEMP_FOLDER_PERMISSIONS_SUCCESS','EasyPopulate successfully adjusted the permissions on your uploads folder! You can now upload files using EasyPopulate...');
define('EASYPOPULATE_MSGSTACK_MODELSIZE_DETECT_FAIL','EasyPopulate cannot determine the maximum size permissible for the products_model field in your products table. Please ensure that the length of your model data field does not exceed the Zen Cart default value of 32 characters for any given product. Failure to heed this warning may have unintended consequences for your data.');
define('EASYPOPULATE_MSGSTACK_ERROR_SQL', 'An SQL error has occured. Please check your input data for tabs within fields and delete these. If this error continues, please forward your error log to the EasyPopulate maintainer');
define('EASYPOPULATE_MSGSTACK_DROSS_DELETE_FAIL', '<b>Deleting of product data debris failed!</b> Please see the debug log in your uploads directory for further information.');
define('EASYPOPULATE_MSGSTACK_DROSS_DELETE_SUCCESS', 'Deleting of product data debris succeeded!');
define('EASYPOPULATE_MSGSTACK_DROSS_DETECTED', '<b>%s partially deleted product(s) found!</b> Delete this dross to prevent unwanted zencart behaviour by clicking <a href="%s">here.</a><br />You are seeing this because there are references in tables to a product that no longer exists, which is usually caused by an incomplete product deletion. This can cause Zen Cart to misbehave in certain circumstances.');
define('EASYPOPULATE_MSGSTACK_DATE_FORMAT_FAIL', '%s is not a valid date format. If you upload any date other than raw format (such as from Excel) you will mangle your dates. Please fix this by correcting your date format in the EasyPopulate config.');

// install - msg stack alerts - output via $messageStack
define('EASYPOPULATE_MSGSTACK_INSTALL_DELETE_SUCCESS','Redundant file <b>%s</b> was deleted from <b>YOUR_ADMIN%s</b> directory.');
define('EASYPOPULATE_MSGSTACK_INSTALL_DELETE_FAIL','EasyPopulate was unable to delete redundant file <b>%s</b> from <b>YOUR_ADMIN%s</b> directory. Please delete this file manually.');
define('EASYPOPULATE_MSGSTACK_LANGER','EasyPopulate support & development by <b>langer</b>. Donations are always appreciated to support continuing development: paypal@portability.com.au');
define('EASYPOPULATE_MSGSTACK_INSTALL_CHMOD_FAIL','<b>Please run the EasyPopulate install again after fixing your uploads directory problem.</b>');
define('EASYPOPULATE_MSGSTACK_INSTALL_CHMOD_SUCCESS','<b>Installation Successfull!</b>  A full download of you store has been done and is available in your uploads (temp) directory. You can use this as your 1st template for uploading/updating products.');
define('EASYPOPULATE_MSGSTACK_INSTALL_KEYS_FAIL','<b>Easy Populate Configuration Missing.</b>  Please install your configuration by clicking %shere%s');

// file handling - msg stack alerts - output via $messageStack
define('EASYPOPULATE_MSGSTACK_FILE_EXPORT_SUCCESS', 'File <b>%s.txt</b> successfully exported! The file is ready for download in your /%s directory.');

// html template - bottom of admin/easypopulate.php
// langer - will add after html renovation

/**
* $printsplit defines
*/
// splitting files results text - in $printsplit
define('EASYPOPULATE_FILE_SPLITS_HEADING', '<b><u>Upload split files in turn</u></b><br /><br />');
define('EASYPOPULATE_FILE_SPLIT_COMPLETED', 'Upload done of ');
define('EASYPOPULATE_FILE_SPLITS_DONE', 'All done!<br />');
define('EASYPOPULATE_FILE_SPLIT_PENDING', 'Pending Upload of ');
define('EASYPOPULATE_FILE_SPLIT_ANCHOR_TEXT', 'Upload ');
// misc
define('EASYPOPULATE_FILE_SPLITS_PREFIX', 'Split-');

/**
* $specials_print defines
*/
// results of specials in $specials_print
define('EASYPOPULATE_SPECIALS_HEADING', '<b><u>Specials Summary</u></b><p class=smallText>'); // open paragraph
define('EASYPOPULATE_SPECIALS_PRICE_FAIL', '<font color="red"><b>SKIPPED! - Model:</b> %s - specials price higher than normal price...</font><br />');
define('EASYPOPULATE_SPECIALS_NEW', '<font color="green"><b>NEW! - Model:</b> %s</font> | %s | %s | <font color="green"><b>%s</b></font> |<br />');
define('EASYPOPULATE_SPECIALS_UPDATE', '<font color="mediumblue"><b>UPDATED! - Model:</b> %s</font> | %s | %s | <font color="green"><b>%s</b></font> |<br />');
define('EASYPOPULATE_SPECIALS_DELETE', '<font color="fuchsia"><b>DELETED! - Model:</b> %s</font> | %s |<br />');
define('EASYPOPULATE_SPECIALS_DELETE_FAIL', '<font color="darkviolet"><b>NOT FOUND! - Model:</b> %s - cant delete special...</font><br />');
define('EASYPOPULATE_SPECIALS_FOOTER', '</p>'); // close paragraph

// error log defines - for ep_debug_log.txt
//define('EASYPOPULATE_ERRORLOG_SQL_ERROR', 'MySQL error %s: %s\nWhen executing:\n%sn');
?>
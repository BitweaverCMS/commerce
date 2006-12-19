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
// $Id: header_php.php,v 1.5 2006/12/19 00:11:36 spiderr Exp $
//
  require_once(DIR_FS_MODULES . 'require_languages.php');

  if (!$_SESSION['customer_id']) {
//    die('I DIED WITH NO CUSTOMER ID AND SHOULD BE REDIRECTED RATHER THAN GIVE THIS ERROR 1');
  }

// if the customer is not logged on, redirect them to the time out page
  if (!$_SESSION['customer_id']) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
  }

// Check download.php was called with proper GET parameters
  if ((isset($_GET['order']) && !is_numeric($_GET['order'])) || (isset($_GET['id']) && !is_numeric($_GET['id'])) ) {
// if the paramaters are wrong, redirect them to the time out page
      zen_redirect(zen_href_link(FILENAME_TIME_OUT));
  }

// Check that order_id, customer_id and filename match
  $downloads = $gBitDb->Execute("select date_format(o.`date_purchased`, '%Y-%m-%d') as `date_purchased_day`, opd.`download_maxdays`, opd.`download_count`, opd.`download_maxdays`, opd.`orders_products_filename` from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd where o.`customers_id` = '" . $_SESSION['customer_id'] . "' and o.`orders_id` = '" . (int)$_GET['order'] . "' and o.`orders_id` = op.`orders_id` and op.`orders_products_id` = opd.`orders_products_id` and opd.`orders_products_download_id` = '" . (int)$_GET['id'] . "' and opd.`orders_products_filename` != ''");
  if ($downloads->RecordCount() <= 0 ) die;
// MySQL 3.22 does not have INTERVAL
  list($dt_year, $dt_month, $dt_day) = explode('-', $downloads->fields['date_purchased_day']);
  $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads->fields['download_maxdays'], $dt_year);

// Die if time expired (maxdays = 0 means no time limit)
  if (($downloads->fields['download_maxdays'] != 0) && ($download_timestamp <= time())) {
    zen_redirect(zen_href_link(FILENAME_DOWNLOAD_TIME_OUT));
  };
// Die if remaining count is <=0
  if ($downloads->fields['download_count'] <= 0) {
    zen_redirect(zen_href_link(FILENAME_DOWNLOAD_TIME_OUT));
  }

// FIX HERE AND GIVE ERROR PAGE FOR MISSING FILE
// Die if file is not there
  if (!file_exists(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename'])) die;

// Now decrement counter
  $gBitDb->Execute("update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_count = download_count-1 where orders_products_download_id = '" . (int)$_GET['id'] . "'");

// Returns a random name, 16 to 20 characters long
// There are more than 10^28 combinations
// The directory is "hidden", i.e. starts with '.'
function zen_random_name()
{
  $letters = 'abcdefghijklmnopqrstuvwxyz';
  $dirname = '.';
  $length = floor(zen_rand(16,20));
  for ($i = 1; $i <= $length; $i++) {
   $q = floor(zen_rand(1,26));
   $dirname .= $letters[$q];
  }
  return $dirname;
}

// Unlinks all subdirectories and files in $dir
// Works only on one subdir level, will not recurse
function zen_unlink_temp_dir($dir)
{
  $h1 = opendir($dir);
  while ($subdir = readdir($h1)) {
// Ignore non directories
    if (!is_dir($dir . $subdir)) continue;
// Ignore . and .. and CVS
    if ($subdir == '.' || $subdir == '..' || $subdir == 'CVS') continue;
// Loop and unlink files in subdirectory
    $h2 = opendir($dir . $subdir);
    while ($file = readdir($h2)) {
      if ($file == '.' || $file == '..') continue;
      @unlink($dir . $subdir . '/' . $file);
    }
    closedir($h2);
    @rmdir($dir . $subdir);
  }
  closedir($h1);
}


// Now send the file with header() magic
  header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
  header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  header("Content-Type: Application/octet-stream");
  header("Content-disposition: attachment; filename=" . $downloads->fields['orders_products_filename']);
  $zv_filesize = filesize(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename']);

  if (DOWNLOAD_BY_REDIRECT == 'true') {
// This will work only on Unix/Linux hosts
    zen_unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
    $tempdir = zen_random_name();
    umask(0000);
    mkdir(DIR_FS_DOWNLOAD_PUBLIC . $tempdir, 0777);
    symlink(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename'], DIR_FS_DOWNLOAD_PUBLIC . $tempdir . '/' . $downloads->fields['orders_products_filename']);
    zen_redirect(DIR_WS_DOWNLOAD_PUBLIC . $tempdir . '/' . $downloads->fields['orders_products_filename']);
  } else {
// This will work on all systems, but will need considerable resources
// We could also loop with fread($fp, 4096) to save memory
    header("Content-Length: " . $zv_filesize);
    readfile(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename']);
  }
?>
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
// $Id: downloads.php,v 1.2 2005/07/08 06:12:59 spiderr Exp $
//
   if (!($_GET['main_page']==FILENAME_ACCOUNT_HISTORY_INFO)) {
// Get last order id for checkout_success
    $orders_lookup_query = "select orders_id
                     from " . TABLE_ORDERS . "
                     where customers_id = '" . (int)$_SESSION['customer_id'] . "'
                     order by orders_id desc limit 1";

    $orders_lookup = $db->Execute($orders_lookup_query);
    $last_order = $orders_lookup->fields['orders_id'];
  } else {
    $last_order = $_GET['order_id'];
  }

// Now get all downloadable products in that order
  $downloads_query = "select ".$db->SQLDate('Y-m-d', 'o.date_purchased')." as date_purchased_day,
                             opd.download_maxdays, op.products_name, opd.orders_products_download_id,
                             opd.orders_products_filename, opd.download_count, opd.download_maxdays
                      from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, "
                             . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                      where o.customers_id = ?
	                      and (o.orders_status >= '" . DOWNLOADS_CONTROLLER_ORDERS_STATUS . "'
    	                  and o.orders_status <= '" . DOWNLOADS_CONTROLLER_ORDERS_STATUS_END . "')
        	              and o.orders_id = ?
            	          and o.orders_id = op.orders_id
                	      and op.orders_products_id = opd.orders_products_id
                    	  and opd.orders_products_filename != ''";

  $downloads = $db->query($downloads_query, array( $_SESSION['customer_id'], $last_order) );

  if ($downloads->RecordCount() > 0) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td class="plainBoxHeading">
			<?php echo HEADING_DOWNLOAD; ?>
		</td>
	</tr>
</table>
<table border="0" width="100%" cellspacing="2" cellpadding="2" class="plainBox">
          <tr>
            <td align="left"><strong><?php echo TABLE_HEADING_DOWNLOAD_FILENAME; ?></strong></td>
            <td align="center"><strong><?php echo TABLE_HEADING_DOWNLOAD_DATE; ?></strong></td>
            <td align="center"><strong><?php echo TABLE_HEADING_DOWNLOAD_COUNT; ?></strong></td>
          </tr>
<!-- list of products -->
<?php
    while (!$downloads->EOF) {
// MySQL 3.22 does not have INTERVAL
      list($dt_year, $dt_month, $dt_day) = explode('-', $downloads->fields['date_purchased_day']);
      $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads->fields['download_maxdays'], $dt_year);
      $download_expiry = date('Y-m-d H:i:s', $download_timestamp);
?>
          <tr>
<!-- left box -->
<?php
// The link will appear only if:
// - Download remaining count is > 0, AND
// - The file is present in the DOWNLOAD directory, AND EITHER
// - No expiry date is enforced (maxdays == 0), OR
// - The expiry date is not reached
      if ( ($downloads->fields['download_count'] > 0) && (file_exists(DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename'])) && ( ($downloads->fields['download_maxdays'] == 0) || ($download_timestamp > time())) ) {
        $zv_filesize = filesize (DIR_FS_DOWNLOAD . $downloads->fields['orders_products_filename']);
        if ($zv_filesize >= 1024) {
          $zv_filesize = number_format($zv_filesize/1024/1000,2);
          $zv_filesize_units = TEXT_FILESIZE_MEGS;
        } else {
          $zv_filesize = number_format($zv_filesize);
          $zv_filesize_units = TEXT_FILESIZE_BYTES;
        }
        echo '            <td align="center"><a href="' . zen_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $downloads->fields['orders_products_download_id']) . '">' . zen_image_button(BUTTON_IMAGE_DOWNLOAD, BUTTON_DOWNLOAD_ALT) . '<br />' . $downloads->fields['products_name'] . '<br />' . $zv_filesize . $zv_filesize_units . '</a></td>' . "\n";
      } else {
        echo '            <td>' . $downloads->fields['products_name'] . '</td>' . "\n";
      }
?>
<!-- right box -->
<?php
      echo '            <td align="center">' . zen_date_short($download_expiry) . '</td>' . "\n" .
           '            <td align="center">' . $downloads->fields['download_count'] . '</td>' . "\n" .
           '          </tr>' . "\n";
      $downloads->MoveNext();
    }
?>
  </table>
<?php
// old way
//    if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
// new way
   if (!($_GET['main_page']==FILENAME_ACCOUNT_HISTORY_INFO)) {
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
     <tr>
        <td class="smalltext"><p><?php printf(FOOTER_DOWNLOAD, '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . HEADER_TITLE_MY_ACCOUNT . '</a>'); ?></p></td>
      </tr>
</table>
<?php
    }
  } else {
//    no downloads
  }
?>
<?php
// If there is a download in the order and they cannot get it, tell customer about download rules
  $downloads_check_query = $db->Execute("select o.orders_id, opd.orders_products_download_id
                          from " .
                          TABLE_ORDERS . " o, " .
                          TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
                          where
                          o.orders_id = opd.orders_id
                          and o.orders_id = '" . $last_order . "'
                          and opd.orders_products_filename != ''
                          ");

if ($downloads_check_query->RecordCount() > 0 and $downloads->RecordCount() < 1) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td class="plainBox">
		  <table width="100%">
        <tr>
          <td valign="top" class="alert" height="30"><?php echo DOWNLOADS_CONTROLLER_ON_HOLD_MSG ?></td>
        </tr>
      </table>
		</td>
	</tr>
</table>
<?php
}
?>

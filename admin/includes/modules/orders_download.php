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

global $gBitDb;

// select downloads for current order
$orders_download_query = "select * from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where `orders_id`=?";
$orders_download = $gBitDb->query($orders_download_query, array( $_REQUEST['oID'] ) );

// only display if there are downloads to display
if ($orders_download->RecordCount() > 0) {
?>
<table border="1" cellspacing="0" cellpadding="5">
  <tr>
	<td class="smallText" align="center"><?php echo TEXT_LEGEND; ?></td>
	<td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_AVAILABLE . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT); ?></td>
	<td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_EXPIRED . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED); ?></td>
	<td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_MISSING . '<br />' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_MISSING); ?></td>
  <tr>
	<td colspan="4" class="smallText" align="center"><strong><?php echo TEXT_DOWNLOAD_TITLE; ?></strong></td>
  </tr>
  <tr>
	<td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_STATUS; ?></td>
	<td class="smallText" align="left"><?php echo TEXT_DOWNLOAD_FILENAME; ?></td>
	<td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_MAX_DAYS; ?></td>
	<td class="smallText" align="center"><?php echo TEXT_DOWNLOAD_MAX_COUNT; ?></td>
  </tr>
<?php
// add legend
    while (!$orders_download->EOF) {
      switch (true) {
        case ($orders_download->fields['download_maxdays'] > 0 and $orders_download->fields['download_count'] > 0):
          $zc_file_status = '<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_REQUEST['oID'] . '&download_reset_off=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_CURRENT) . '</a>';
          break;
        case ($orders_download->fields['download_maxdays'] <= 1 or $orders_download->fields['download_count'] <= 1):
          $zc_file_status = '<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_REQUEST['oID'] . '&download_reset_on=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
        default:
          $zc_file_status = '<a href="' . zen_href_link_admin(FILENAME_ORDERS, zen_get_all_get_params(array('oID', 'action')) . 'oID=' . $_REQUEST['oID'] . '&download_reset_on=' . $orders_download->fields['orders_products_download_id'], 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_STATUS_EXPIRED) . '</a>';
          break;
          break;
      }

// if not on server show red
      if (!zen_orders_products_downloads($orders_download->fields['orders_products_filename'])) {
        $zc_file_status = zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF);
      }
?>
  <tr>
	<td class="smallText" align="center"><?php echo $zc_file_status; ?></td>
	<td class="smallText" align="left"><?php echo $orders_download->fields['orders_products_filename']; ?></td>
	<td class="smallText" align="center"><?php echo $orders_download->fields['download_maxdays']; ?></td>
	<td class="smallText" align="center"><?php echo $orders_download->fields['download_count']; ?></td>
  </tr>
<?php
        $orders_download->MoveNext();
    }
?>
</table>
<?php
  } // only display if there are downloads to display
?>

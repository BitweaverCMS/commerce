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
//  $Id: index.php,v 1.19 2006/07/10 03:43:58 spiderr Exp $
//
  $version_check_index=true;
  require('includes/application_top.php');
global $language;
  $languages = zen_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<script language="JavaScript" src="includes/menu.js" type="text/JavaScript"></script>
<link href="includes/stylesheet.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS" />
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
 <?php

  $customers = $db->getOne("SELECT COUNT(*) FROM " . TABLE_CUSTOMERS);

  $products = $db->getOne("SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " WHERE `products_status` = '1'");

  $products_off = $db->getOne("SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " WHERE `products_status` = '0'");

  $reviews = $db->getOne("SELECT COUNT(*) FROM " . TABLE_REVIEWS);
  $reviews_pending = $db->getOne("SELECT COUNT(*) FROM " . TABLE_REVIEWS . " WHERE `status`='0'");

  $newsletters = $db->getOne("SELECT COUNT(*) FROM " . TABLE_CUSTOMERS . " WHERE `customers_newsletter` = '1'");

  $counter_query = "select `startdate`, `counter` from " . TABLE_COUNTER;
  $counter = $db->Execute($counter_query);
  $counter_startdate = $counter->fields['startdate'];
//  $counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
  $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));

  $specials = $db->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '0'");
  $specials_act = $db->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '1'");
  $featured = $db->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '0'");
  $featured_act = $db->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '1'");
  $salemaker = $db->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '0'");
  $salemaker_act = $db->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '1'");


?>
<div id="colone">
<table class="data">
   <tr><th colspan="2"><?php echo BOX_TITLE_ORDERS; ?> </th></tr>
  <?php   $orders_contents = '';
  if( $rs = $db->Execute("SELECT `orders_status_name`, `orders_status_id` FROM " . TABLE_ORDERS_STATUS . " WHERE `language_id` = '" . $_SESSION['languages_id'] . "'") ) {

	  while( $orders_status = $rs->fetchRow() ) {
		$orders_pending = $db->GetOne("SELECT COUNT(*) FROM " . TABLE_ORDERS . " WHERE `orders_status` = '" . $orders_status['orders_status_id'] . "'");
		$orders_contents .= '<tr><td><a href="' . zen_href_link_admin(FILENAME_ORDERS, 'selected_box=customers&status=' . $orders_status['orders_status_id'], 'NONSSL') . '">' . $orders_status['orders_status_name'] . '</a>:</td><td> ' . $orders_pending . '</td></tr>';
		$rs->MoveNext();
	  }
  }

  echo $orders_contents;
?>
</table>
<table class="data">
<tr><th colspan="4"><?php echo BOX_ENTRY_NEW_ORDERS; ?> </th></tr>
  <?php
	require_once( DIR_FS_CLASSES.'order.php' );

	$listHash = array( 'max_records' => '250' );
	$orders = order::getList( $listHash );

	foreach( array_keys( $orders ) as $orderId ) {
		$orderAnchor = '<a href="' . zen_href_link_admin(FILENAME_ORDERS, 'oID=' . $orderId . '&origin=' . FILENAME_DEFAULT, 'NONSSL') . '&action=edit" class="contentlink"> ';
		echo '<tr><td>' . $orderAnchor . $orderId . ' - '. $gBitUser->getDisplayName( FALSE, $orders[$orderId] ) . '</a> ' . '</td><td>' . round( $orders[$orderId]['order_total'], 2) . '</td><td align="right">' . "\n";
		echo zen_date_short( $orders[$orderId]['date_purchased'] );
		echo '</td><td>'.$orders[$orderId]['orders_status_name'].'</td></tr>' . "\n";
	}
?>
</table>
</div>

<?php
/*
data is linked with users_users
<div id="coltwo">
<table class="data">
<tr><th><?php echo BOX_ENTRY_NEW_CUSTOMERS; ?> </th></tr>
  <?php  $customers = $db->Execute("select c.`customers_id`, c.`customers_firstname`, c.`customers_lastname`, a.`date_account_created`, a.`customers_info_id` from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " a on c.`customers_id` = a.`customers_info_id` order by a.`date_account_created` DESC", 5);

  while (!$customers->EOF) {
    echo '<tr><td><a href="' . zen_href_link_admin(FILENAME_CUSTOMERS, 'search=' . $customers->fields['customers_lastname'] . '&origin=' . FILENAME_DEFAULT, 'NONSSL') . '" class="contentlink">'. $customers->fields['customers_firstname'] . ' ' . $customers->fields['customers_lastname'] . '</a>' . "\n";
    echo zen_date_short($customers->fields['date_account_created']);
    echo '</tr></td>' . "\n";
    $customers->MoveNext();
  }
?>
</table>
</div>
*/
?>

<div id="colthree">
<table class="data">
<tr><th colspan="2"><?php echo BOX_TITLE_STATISTICS; ?> </th></tr>
<?php
	echo '<tr><td>' . BOX_ENTRY_COUNTER_DATE . '</td><td> ' . $counter_startdate_formatted . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_COUNTER . '</td><td> ' . $counter->fields['counter'] . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_CUSTOMERS . '</td><td> ' . $customers . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_PRODUCTS . ' </td><td>' . $products . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_PRODUCTS_OFF . ' </td><td>' . $products_off . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_REVIEWS . '</td><td>' . $reviews . '</td></tr>';
    if (REVIEWS_APPROVAL=='1') {
	  echo '<td><a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'status=1', 'NONSSL') . '">' . BOX_ENTRY_REVIEWS_PENDING . '</a></td><td>' . $reviews_pending. '</td></tr>';
    }
	echo '<tr><td>' . BOX_ENTRY_NEWSLETTERS . '</td><td> ' . $newsletters . '</td></tr>';

	echo '<tr><td>' . BOX_ENTRY_SPECIALS_EXPIRED . '</td><td> ' . $specials . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_SPECIALS_ACTIVE . '</td><td> ' . $specials_act . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_FEATURED_EXPIRED . '</td><td> ' . $featured . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_FEATURED_ACTIVE . '</td><td> ' . $featured_act . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_SALEMAKER_EXPIRED . '</td><td> ' . $salemaker . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_SALEMAKER_ACTIVE . '</td><td> ' . $salemaker_act . '</td></tr>';

?>
</table>
</div>

<!-- The following copyright announcement is in compliance
to section 2c of the GNU General Public License, and
thus can not be removed, or can only be modified
appropriately.

Please leave this comment intact together with the
following copyright announcement. //-->

<p class="copyrightrow"><a href="http://www.bitcommerce.org" target="_blank">bitcommerce E-Commerce Engine Copyright &copy; <?=date('Y')?> <a href="http://www.bitcommerce.org" target="_blank">bitcommerce&trade;</a></p><p class="warrantyrow">bitcommerce is derived from: Copyright &copy; 2005 Zen Cart is derived from: Copyright &copy; 2003 osCommerce<br />This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;<br />without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE<br />and is redistributable under the <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GNU General Public License</a></p>
</p>
</body>
</html>

<?php require('includes/application_bottom.php'); ?>
<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

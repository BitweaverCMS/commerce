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
//  $Id: index.php,v 1.9 2005/08/24 15:33:17 spiderr Exp $
//
  $version_check_index=true;
  require('includes/application_top.php');

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
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
 <?php

  $customers = $db->Execute("select count(*) as `count` from " . TABLE_CUSTOMERS);

  $products = $db->Execute("select count(*) as `count` from " . TABLE_PRODUCTS . " where `products_status` = '1'");

  $products_off = $db->Execute("select count(*) as `count` from " . TABLE_PRODUCTS . " where `products_status` = '0'");

  $reviews = $db->Execute("select count(*) as `count` from " . TABLE_REVIEWS);
  $reviews_pending = $db->Execute("select count(*) as `count` from " . TABLE_REVIEWS . " where `status`='0'");

  $newsletters = $db->Execute("select count(*) as `count` from " . TABLE_CUSTOMERS . " where `customers_newsletter` = '1'");

  $counter_query = "select `startdate`, `counter` from " . TABLE_COUNTER;
  $counter = $db->Execute($counter_query);
  $counter_startdate = $counter->fields['startdate'];
//  $counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
  $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));

  $specials = $db->Execute("select count(*) as `count` from " . TABLE_SPECIALS . " where `status`= '0'");
  $specials_act = $db->Execute("select count(*) as `count` from " . TABLE_SPECIALS . " where `status`= '1'");
  $featured = $db->Execute("select count(*) as `count` from " . TABLE_FEATURED . " where `status`= '0'");
  $featured_act = $db->Execute("select count(*) as `count` from " . TABLE_FEATURED . " where `status`= '1'");
  $salemaker = $db->Execute("select count(*) as `count` from " . TABLE_SALEMAKER_SALES . " where `sale_status` = '0'");
  $salemaker_act = $db->Execute("select count(*) as `count` from " . TABLE_SALEMAKER_SALES . " where `sale_status` = '1'");


?>
<div id="colone">
<table class="data">
   <tr><th colspan="2"><?php echo BOX_TITLE_ORDERS; ?> </th></tr>
  <?php   $orders_contents = '';
  $orders_status = $db->Execute("select `orders_status_name`, `orders_status_id` from " . TABLE_ORDERS_STATUS . " where `language_id` = '" . $_SESSION['languages_id'] . "'");

  while (!$orders_status->EOF) {
    $orders_pending = $db->Execute("select count(*) as `count` from " . TABLE_ORDERS . " where `orders_status` = '" . $orders_status->fields['orders_status_id'] . "'");

    $orders_contents .= '<tr><td><a href="' . zen_href_link_admin(FILENAME_ORDERS, 'selected_box=customers&status=' . $orders_status->fields['orders_status_id'], 'NONSSL') . '">' . $orders_status->fields['orders_status_name'] . '</a>:</td><td> ' . $orders_pending->fields['count'] . '</td></tr>';
    $orders_status->MoveNext();
  }

  echo $orders_contents;
?>
</table>
<table class="data">
<tr><th colspan="4"><?php echo BOX_ENTRY_NEW_ORDERS; ?> </th></tr>
  <?php  $orders = $db->Execute("SELECT ot.`text` AS `order_total`, o.*, uu.*, os.* from " . TABLE_ORDERS . " o INNER JOIN " . TABLE_ORDERS_STATUS . " os ON(o.`orders_status`=os.`orders_status_id`) INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON(o.`customers_id`=uu.`user_id`) left join " . TABLE_ORDERS_TOTAL . " ot on (o.`orders_id` = ot.`orders_id`) where `class` = 'ot_total' ORDER BY o.`orders_id` DESC", 50);

  while (!$orders->EOF) {
  	$orderAnchor = '<a href="' . zen_href_link_admin(FILENAME_ORDERS, 'oID=' . $orders->fields['orders_id'] . '&origin=' . FILENAME_DEFAULT, 'NONSSL') . '&action=edit" class="contentlink"> ';
    echo '<tr><td>'. $orderAnchor . $orders->fields['orders_id'] . ' - '. $gBitUser->getDisplayName( FALSE, $orders->fields ) . '</a> ' . '</td><td>' . $orders->fields['order_total'] . '</td><td align="right">' . "\n";
    echo zen_date_short($orders->fields['date_purchased']);
    echo '</td><td>'.$orders->fields['orders_status_name'].'</td></tr>' . "\n";
    $orders->MoveNext();
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
	echo '<tr><td>' . BOX_ENTRY_CUSTOMERS . '</td><td> ' . $customers->fields['count'] . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_PRODUCTS . ' </td><td>' . $products->fields['count'] . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_PRODUCTS_OFF . ' </td><td>' . $products_off->fields['count'] . '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_REVIEWS . '</td><td>' . $reviews->fields['count']. '</td></tr>';
    if (REVIEWS_APPROVAL=='1') {
	  echo '<td><a href="' . zen_href_link_admin(FILENAME_REVIEWS, 'status=1', 'NONSSL') . '">' . BOX_ENTRY_REVIEWS_PENDING . '</a></td><td>' . $reviews_pending->fields['count']. '</td></tr>';
    }
	echo '<tr><td>' . BOX_ENTRY_NEWSLETTERS . '</td><td> ' . $newsletters->fields['count']. '</td></tr>';

	echo '<tr><td>' . BOX_ENTRY_SPECIALS_EXPIRED . '</td><td> ' . $specials->fields['count']. '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_SPECIALS_ACTIVE . '</td><td> ' . $specials_act->fields['count']. '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_FEATURED_EXPIRED . '</td><td> ' . $featured->fields['count']. '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_FEATURED_ACTIVE . '</td><td> ' . $featured_act->fields['count']. '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_SALEMAKER_EXPIRED . '</td><td> ' . $salemaker->fields['count']. '</td></tr>';
	echo '<tr><td>' . BOX_ENTRY_SALEMAKER_ACTIVE . '</td><td> ' . $salemaker_act->fields['count']. '</td></tr>';

?>
</table>
</div>

<!-- The following copyright announcement is in compliance
to section 2c of the GNU General Public License, and
thus can not be removed, or can only be modified
appropriately.

Please leave this comment intact together with the
following copyright announcement. //-->

<div class="copyrightrow"><a href="http://www.zen-cart.com" target="_blank"><img src="images/small_zen_logo.gif" alt="Zen Cart:: the art of e-commerce" border="0" /></a><br /><br />E-Commerce Engine Copyright &copy; 2003 <a href="http://www.zen-cart.com" target="_blank">Zen Cart&trade;</a></div><div class="warrantyrow"><br /><br />Zen Cart is derived from: Copyright &copy; 2003 osCommerce<br />This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;<br />without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE<br />and is redistributable under the <a href="http://www.zen-cart.com/license/2_0.txt" target="_blank">GNU General Public License</a><br />
</div>
</body>
</html>

<?php require('includes/application_bottom.php'); ?>
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

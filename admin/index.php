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
//  $Id: index.php,v 1.1 2005/07/05 05:59:56 bitweaver Exp $
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

  $customers = $db->Execute("select count(*) as count from " . TABLE_CUSTOMERS);

  $products = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = '1'");

  $products_off = $db->Execute("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = '0'");

  $reviews = $db->Execute("select count(*) as count from " . TABLE_REVIEWS);
  $reviews_pending = $db->Execute("select count(*) as count from " . TABLE_REVIEWS . " where status='0'");

  $newsletters = $db->Execute("select count(*) as count from " . TABLE_CUSTOMERS . " where customers_newsletter = '1'");

  $counter_query = "select startdate, counter from " . TABLE_COUNTER;
  $counter = $db->Execute($counter_query);
  $counter_startdate = $counter->fields['startdate'];
//  $counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
  $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));

  $specials = $db->Execute("select count(*) as count from " . TABLE_SPECIALS . " where status= '0'");
  $specials_act = $db->Execute("select count(*) as count from " . TABLE_SPECIALS . " where status= '1'");
  $featured = $db->Execute("select count(*) as count from " . TABLE_FEATURED . " where status= '0'");
  $featured_act = $db->Execute("select count(*) as count from " . TABLE_FEATURED . " where status= '1'");
  $salemaker = $db->Execute("select count(*) as count from " . TABLE_SALEMAKER_SALES . " where sale_status = '0'");
  $salemaker_act = $db->Execute("select count(*) as count from " . TABLE_SALEMAKER_SALES . " where sale_status = '1'");


?>
<div id="colone">
<div class="reportBox">
<div class="header"><?php echo BOX_TITLE_STATISTICS; ?> </div>
<?php
	echo '<div class="row"><span class="left">' . BOX_ENTRY_COUNTER_DATE . '</span><span class="rigth"> ' . $counter_startdate_formatted . '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_COUNTER . '</span><span class="rigth"> ' . $counter->fields['counter'] . '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_CUSTOMERS . '</span><span class="rigth"> ' . $customers->fields['count'] . '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_PRODUCTS . ' </span><span class="rigth">' . $products->fields['count'] . '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_PRODUCTS_OFF . ' </span><span class="rigth">' . $products_off->fields['count'] . '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_REVIEWS . '</span><span class="rigth">' . $reviews->fields['count']. '</span></div>';
    if (REVIEWS_APPROVAL=='1') {
	  echo '<div class="row"><span class="left"><a href="' . zen_href_link(FILENAME_REVIEWS, 'status=1', 'NONSSL') . '">' . BOX_ENTRY_REVIEWS_PENDING . '</a></span><span class="rigth">' . $reviews_pending->fields['count']. '</span></div>';
    }
	echo '<div class="row"><span class="left">' . BOX_ENTRY_NEWSLETTERS . '</span><span class="rigth"> ' . $newsletters->fields['count']. '</span></div>';

	echo '<br /><div class="row"><span class="left">' . BOX_ENTRY_SPECIALS_EXPIRED . '</span><span class="rigth"> ' . $specials->fields['count']. '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_SPECIALS_ACTIVE . '</span><span class="rigth"> ' . $specials_act->fields['count']. '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_FEATURED_EXPIRED . '</span><span class="rigth"> ' . $featured->fields['count']. '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_FEATURED_ACTIVE . '</span><span class="rigth"> ' . $featured_act->fields['count']. '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_SALEMAKER_EXPIRED . '</span><span class="rigth"> ' . $salemaker->fields['count']. '</span></div>';
	echo '<div class="row"><span class="left">' . BOX_ENTRY_SALEMAKER_ACTIVE . '</span><span class="rigth"> ' . $salemaker_act->fields['count']. '</span></div>';

?>
 </div>
 <div class="reportBox">
   <div class="header"><?php echo BOX_TITLE_ORDERS; ?> </div>
  <?php   $orders_contents = '';
  $orders_status = $db->Execute("select orders_status_name, orders_status_id from " . TABLE_ORDERS_STATUS . " where language_id = '" . $_SESSION['languages_id'] . "'");

  while (!$orders_status->EOF) {
    $orders_pending = $db->Execute("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . $orders_status->fields['orders_status_id'] . "'");

    $orders_contents .= '<div class="row"><span class="left"><a href="' . zen_href_link(FILENAME_ORDERS, 'selected_box=customers&status=' . $orders_status->fields['orders_status_id'], 'NONSSL') . '">' . $orders_status->fields['orders_status_name'] . '</a>:</span><span class="rigth"> ' . $orders_pending->fields['count'] . '</span>   </div>';
    $orders_status->MoveNext();
  }

  echo $orders_contents;
?>
  </div>
</div>
<div id="coltwo">
<div class="reportBox">
<div class="header"><?php echo BOX_ENTRY_NEW_CUSTOMERS; ?> </div>
  <?php  $customers = $db->Execute("select c.customers_id as customers_id, c.customers_firstname as customers_firstname, c.customers_lastname as customers_lastname, a.customers_info_date_account_created as customers_info_date_account_created, a.customers_info_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " a on c.customers_id = a.customers_info_id order by a.customers_info_date_account_created DESC limit 5");

  while (!$customers->EOF) {
    echo '              <div class="row"><span class="left"><a href="' . zen_href_link(FILENAME_CUSTOMERS, 'search=' . $customers->fields['customers_lastname'] . '&origin=' . FILENAME_DEFAULT, 'NONSSL') . '" class="contentlink">'. $customers->fields['customers_firstname'] . ' ' . $customers->fields['customers_lastname'] . '</a></span><span class="rigth">' . "\n";
    echo zen_date_short($customers->fields['customers_info_date_account_created']);
    echo '              </span></div>' . "\n";
    $customers->MoveNext();
  }
?>
</div>
</div>
<div id="colthree">
<div class="reportBox">
<div class="header"><?php echo BOX_ENTRY_NEW_ORDERS; ?> </div>
  <?php  $orders = $db->Execute("select o.orders_id as orders_id, o.customers_name as customers_name, o.customers_id, o.date_purchased as date_purchased, o.currency, o.currency_value, ot.class, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where class = 'ot_total' order by orders_id DESC limit 5");

  while (!$orders->EOF) {
    echo '              <div class="row"><span class="left"><a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $orders->fields['orders_id'] . '&origin=' . FILENAME_DEFAULT, 'NONSSL') . '" class="contentlink"> ' . $orders->fields['customers_name'] . '</a></span><span class="center">' . $orders->fields['order_total'] . '</span><span class="rigth">' . "\n";
    echo zen_date_short($orders->fields['date_purchased']);
    echo '              </span></div>' . "\n";
    $orders->MoveNext();
  }
?>
</div>
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
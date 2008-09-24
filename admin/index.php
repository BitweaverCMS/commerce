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
//  $Id: index.php,v 1.25 2008/09/24 19:41:14 spiderr Exp $
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

  $customers = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_CUSTOMERS);

  $products = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " WHERE `products_status` = '1'");

  $products_off = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " WHERE `products_status` = '0'");

  $reviews = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_REVIEWS);
  $reviews_pending = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_REVIEWS . " WHERE `status`='0'");

  $newsletters = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_CUSTOMERS . " WHERE `customers_newsletter` = '1'");

  $counter_query = "select `startdate`, `counter` from " . TABLE_COUNTER;
  $counter = $gBitDb->Execute($counter_query);
  $counter_startdate = $counter->fields['startdate'];
//  $counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
  $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));

  $specials = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '0'");
  $specials_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '1'");
  $featured = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '0'");
  $featured_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '1'");
  $salemaker = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '0'");
  $salemaker_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '1'");


?>
<div id="colone">
<table class="data">
   <tr><th colspan="2"><?php echo BOX_TITLE_ORDERS; ?> </th></tr>
<?php   $orders_contents = '';
	$query = "SELECT `orders_status_name`, `orders_status_id`, COUNT(co.`orders_id`) AS `orders_count`
			  FROM " . TABLE_ORDERS . " co
				INNER JOIN " . TABLE_ORDERS_STATUS . " cos ON(co.`orders_status`=cos.`orders_status_id`)
			  GROUP BY `orders_status_name`, `orders_status_id`
			  ORDER BY `orders_status_id` DESC";
  if( $rs = $gBitDb->query( $query ) ) {
	  while( $orders_status = $rs->fetchRow() ) {
		print '<tr><td><a href="' . BITCOMMERCE_PKG_URL . 'admin/index.php?orders_status_comparison=&orders_status_id=' . $orders_status['orders_status_id'] . '">' . $orders_status['orders_status_name'] . '</a>:</td><td> ' . $orders_status['orders_count'] . '</td></tr>';
	  }
  }
?>
</table>
<?php
	require_once( DIR_FS_CLASSES.'order.php' );

	$listHash = array( 'max_records' => '250', 'recent_comment' => TRUE );
	if( !empty( $_REQUEST['orders_status_comparison'] ) ) {
		$listHash['orders_status_comparison'] = $_REQUEST['orders_status_comparison'];
		$_SESSION['orders_status_comparison'] = $_REQUEST['orders_status_comparison'];
	} elseif( !empty( $_SESSION['orders_status_comparison'] ) && !empty( $_REQUEST['list_filter'] ) ) {
		unset( $_SESSION['orders_status_comparison'] );
	} elseif( !empty( $_SESSION['orders_status_comparison'] ) ) {
		$listHash['orders_status_comparison'] = $_SESSION['orders_status_comparison'];
	} 

	if( !empty( $_REQUEST['search'] ) ) {
		$listHash['search'] = $_REQUEST['search'];
	}

	if( @BitBase::verifyId( $_REQUEST['orders_status_id'] ) ) {
		$listHash['orders_status_id'] = $_REQUEST['orders_status_id'];
		$_SESSION['orders_status_id'] = $_REQUEST['orders_status_id'];
	} elseif( !empty( $_SESSION['orders_status_id'] ) && !empty( $_REQUEST['list_filter'] ) ) {
		unset( $_SESSION['orders_status_id'] );
	} elseif( !empty( $_SESSION['orders_status_id'] ) ) {
		$listHash['orders_status_id'] = $_SESSION['orders_status_id'];
	}

	$orders = order::getList( $listHash );
	$gBitSmarty->assign_by_ref( 'listOrders', $orders );
	$statuses = commerce_get_statuses( TRUE );
	$statuses['all'] = 'All';
	$gBitSmarty->assign( 'commerceStatuses', $statuses );
	$gBitSmarty->display( 'bitpackage:bitcommerce/admin_list_orders_inc.tpl' );
?>
</div>

<div id="coltwo">
<?php
	include( BITCOMMERCE_PKG_PATH.'admin/revenue_inc.php' );
?>
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

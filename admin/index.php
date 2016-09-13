<?php
//
// +------------------------------------------------------------------------+
// |zen-cart Open Source E-commerce											|
// +------------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers								|
// |																		|
// | http://www.zen-cart.com/index.php										|
// |																		|
// | Portions Copyright (c) 2003 osCommerce									|
// +------------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			|
// | that is bundled with this package in the file LICENSE, and is			|
// | available through the world-wide-web at the following url:				|
// | http://www.zen-cart.com/license/2_0.txt.								|
// | If you did not receive a copy of the zen-cart license and are unable 	|
// | to obtain it through the world-wide-web, please send a note to			|
// | license@zen-cart.com so we can mail you a copy immediately.			|
// +------------------------------------------------------------------------+
//	$Id$
//
$version_check_index=true;
require('includes/application_top.php');

if( !empty( $_REQUEST['lookup_order_id'] ) && BitBase::verifyId( $_REQUEST['lookup_order_id'] ) ) {
	bit_redirect( BITCOMMERCE_PKG_URL.'admin/orders.php?oID='.(int)$_REQUEST['lookup_order_id'] );
}

global $language;
$languages = zen_get_languages();
$languages_array = array();
$languages_selected = DEFAULT_LANGUAGE;
for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
	$languages_array[] = array( 'id' => $languages[$i]['code'], 'text' => $languages[$i]['name'] );
	if ($languages[$i]['directory'] == $language) {
		$languages_selected = $languages[$i]['code'];
	}
}

$customers = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_CUSTOMERS);

$products = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " WHERE `products_status` = '1'");

$products_off = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_PRODUCTS . " WHERE `products_status` = '0'");

$reviews = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_REVIEWS);
$reviews_pending = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_REVIEWS . " WHERE `status`='0'");

$newsletters = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_CUSTOMERS . " WHERE `customers_newsletter` = '1'");

$counter_query = "select `startdate`, `counter` from " . TABLE_COUNTER;
$counter = $gBitDb->Execute($counter_query);
$counter_startdate = $counter->fields['startdate'];
//	$counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
$counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));

$specials = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '0'");
$specials_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '1'");
$featured = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '0'");
$featured_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '1'");
$salemaker = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '0'");
$salemaker_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '1'");

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php' );

$listHash = array( 'max_records' => '1000', 'recent_comment' => TRUE );
if( !empty( $_REQUEST['orders_status_comparison'] ) ) {
	$listHash['orders_status_comparison'] = $_REQUEST['orders_status_comparison'];
	$_SESSION['orders_status_comparison'] = $_REQUEST['orders_status_comparison'];
} elseif( !empty( $_SESSION['orders_status_comparison'] ) && !empty( $_REQUEST['list_filter'] ) ) {
	unset( $_SESSION['orders_status_comparison'] );
} elseif( !empty( $_SESSION['orders_status_comparison'] ) ) {
	$listHash['orders_status_comparison'] = $_SESSION['orders_status_comparison'];
} 

if( !empty( $_REQUEST['search_scope'] ) ) {
	$listHash['search_scope'] = $_REQUEST['search_scope'];
	$_SESSION['search_scope'] = $_REQUEST['search_scope'];
}
if( !empty( $_REQUEST['search'] ) ) {
	$listHash['search'] = $_REQUEST['search'];
	$_SESSION['search'] = $_REQUEST['search'];
}
$gBitSmarty->assign( 'searchScopes', array( "all" => 'Search Orders', "history" => 'Search History' ) );

if( @BitBase::verifyId( $_REQUEST['orders_status_id'] ) ) {
	$listHash['orders_status_id'] = $_REQUEST['orders_status_id'];
	$_SESSION['orders_status_id'] = $_REQUEST['orders_status_id'];
} elseif( !empty( $_SESSION['orders_status_id'] ) && !empty( $_REQUEST['list_filter'] ) ) {
	unset( $_SESSION['orders_status_id'] );
} elseif( !empty( $_SESSION['orders_status_id'] ) ) {
	$listHash['orders_status_id'] = $_SESSION['orders_status_id'];
}

?>
<header>
	<h1 class="page-header">Order List</h1>
</header>

<div class="row">
	<div class="col-md-8" id="colone">
<?php

$orders = order::getList( $listHash );
$gBitSmarty->assign_by_ref( 'listOrders', $orders );
$statuses = commerce_get_statuses( TRUE );
$statuses['all'] = 'All';
$gBitSmarty->assign( 'commerceStatuses', $statuses );
print $gBitSmarty->fetch( 'bitpackage:bitcommerce/admin_list_orders_inc.tpl' );

?>
	</div>

	<div class="col-md-4" id="coltwo">

		<div class="row">

<div class="col-md-12 col-sm-6"><div class="well nopadding">
<table class="table data">
<tr><th><?php echo tra( 'Order Summary' ); ?></th><th class="text-right">#</th></tr>
<?php	 $orders_contents = '';
	$query = "SELECT `orders_status_id` AS `key`, `orders_status_name`, `orders_status_id`, COUNT(co.`orders_id`) AS `orders_count`
				FROM " . TABLE_ORDERS . " co
				INNER JOIN " . TABLE_ORDERS_STATUS . " cos ON(co.`orders_status`=cos.`orders_status_id`)
				GROUP BY `orders_status_name`, `orders_status_id`
				ORDER BY `orders_status_id` DESC";
	if( $statusHash = $gBitDb->getAssoc( $query ) ) {
		function cmp($a, $b) {
			$ret = 0;
			if( ($a['orders_status_id'] > 0 && $b['orders_status_id'] > 0 ) ) {
				$ret = ($a['orders_status_id'] < $b['orders_status_id']) ? -1 : 1;
			} elseif( ($a['orders_status_id'] > 0 && $b['orders_status_id'] < 0 ) ) {
				$ret =  -1;
			} elseif( ($a['orders_status_id'] < 0 && $b['orders_status_id'] > 0 ) ) {
				$ret =  1;
			} else {
				$ret = ($a['orders_status_id'] > $b['orders_status_id']) ? -1 : 1;;
			}
			return $ret;
		}

		usort($statusHash, "cmp");
		foreach( $statusHash as $orders_status ) {
			print '<tr class="order-'.($orders_status['orders_status_id'] > 0 ? 'live' : 'dead').' '.strtolower( $orders_status['orders_status_name'] ).'"><td><a href="' . BITCOMMERCE_PKG_URL . 'admin/index.php?orders_status_comparison=&orders_status_id=' . $orders_status['orders_status_id'] . '">' . tra( $orders_status['orders_status_name'] ) . '</a></td><td class="text-right"> ' . $orders_status['orders_count'] . '</td></tr>';
		}
	}
?>
</table>
</div></div>

<?php
	include( BITCOMMERCE_PKG_PATH.'admin/revenue_inc.php' );
?>

<div class="col-md-12 col-sm-6"><div class="well nopadding">
<table class="table data">
<tr><th><?php echo tra( 'Statistics' ); ?></th><th></th></tr>
<?php
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
</div></div>

	</div>
</div>

<!-- The following copyright announcement is in compliance
to section 2c of the GNU General Public License, and
thus can not be removed, or can only be modified
appropriately.

Please leave this comment intact together with the
following copyright announcement. //-->

<p class="copyrightrow"><a href="http://www.bitcommerce.org" target="_blank">bitcommerce E-Commerce Engine Copyright &copy; <?=date('Y')?> <a href="http://www.bitcommerce.org" target="_blank">bitcommerce&trade;</a></p><p class="warrantyrow">bitcommerce is derived from: Copyright &copy; 2005 Zen Cart is derived from: Copyright &copy; 2003 osCommerce<br />This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;<br />without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE<br />and is redistributable under the <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">GNU General Public License</a></p>
</p>

<?php require('includes/application_bottom.php'); ?>
<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>

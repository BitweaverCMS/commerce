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

$specials = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '0'");
$specials_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SPECIALS . " WHERE `status`= '1'");
$featured = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '0'");
$featured_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_FEATURED . " WHERE `status`= '1'");
$salemaker = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '0'");
$salemaker_act = $gBitDb->getOne("SELECT COUNT(*) FROM " . TABLE_SALEMAKER_SALES . " WHERE `sale_status` = '1'");

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceOrder.php' );

$listHash = array( 'max_records' => '1000', 'recent_comment' => TRUE );

// The list form is GET, so a copied URL rebuilds the same list. A submit (list_filter) states every value, so a missing one is cleared rather than filled from the session.
foreach( array( 'orders_status_comparison', 'search_scope', 'search', 'orders_products' ) as $requestKey ) {
	if( !empty( $_REQUEST[$requestKey] ) ) {
		$listHash[$requestKey] = $_REQUEST[$requestKey];
		$_SESSION[$requestKey] = $_REQUEST[$requestKey];
	} elseif( !empty( $_POST ) || !empty( $_REQUEST['list_filter'] ) ) {
		unset( $listHash[$requestKey] );
		unset( $_SESSION[$requestKey] );
	} elseif( !empty( $_SESSION[$requestKey] ) ) {
		$listHash[$requestKey] = $_SESSION[$requestKey];
	}
}

$gBitSmarty->assign( 'statusComparisons', array(
	'eq' => array( 'value' => '', 'label' => tra( 'Exactly' ) ),
	'ge' => array( 'value' => '>=', 'label' => tra( 'At Least' ) ),
	'le' => array( 'value' => '<=', 'label' => tra( 'At Most' ) ),
) );
$gBitSmarty->assign( 'searchScopes', array( "all" => tra( 'Orders' ), "history" => tra( 'History' ) ) );

if( BitBase::verifyId( $_REQUEST['orders_status_id'] ?? null ) ) {
	$listHash['orders_status_id'] = $_REQUEST['orders_status_id'];
	$_SESSION['orders_status_id'] = $_REQUEST['orders_status_id'];
} elseif( !empty( $_SESSION['orders_status_id'] ) && !empty( $_REQUEST['list_filter'] ) ) {
	unset( $_SESSION['orders_status_id'] );
} elseif( !empty( $_SESSION['orders_status_id'] ) ) {
	$listHash['orders_status_id'] = $_SESSION['orders_status_id'];
}

$orders = order::getList( $listHash );

// CSS-only list filters (shown by the filter icon): deadline, shipping method, price band.
$priceLabel = function( $pAmount ) use ( $currencies ) {
	return $currencies->getLeftSymbol( DEFAULT_CURRENCY ).number_format( $pAmount ).$currencies->getRightSymbol( DEFAULT_CURRENCY );
};
$priceBands = array(
	'under100' => array( 'label' => tra( 'Under' ).' '.$priceLabel( 100 ), 'min' => NULL, 'max' => 100 ),
	'100to250' => array( 'label' => $priceLabel( 100 ).' – '.$priceLabel( 250 ), 'min' => 100, 'max' => 250 ),
	'250to500' => array( 'label' => $priceLabel( 250 ).' – '.$priceLabel( 500 ), 'min' => 250, 'max' => 500 ),
	'500plus' => array( 'label' => $priceLabel( 500 ).'+', 'min' => 500, 'max' => NULL ),
);
$orderFilters = array(
	'deadline' => 0,
	'groups' => array(
		// 'icon' is the button face; the title is its tooltip.
		'ship' => array( 'title' => tra( 'Shipping' ), 'icon' => 'fa-truck', 'options' => array() ),
		'price' => array( 'title' => tra( 'Price' ), 'icon' => 'fa-coins', 'options' => array() ),
		'comment' => array( 'title' => tra( 'Comments' ), 'icon' => 'fa-comment', 'options' => array(
			'yes' => array( 'label' => tra( 'With Comments' ), 'count' => 0 ),
			'no' => array( 'label' => tra( 'No Comments' ), 'count' => 0 ),
		) ),
	),
);
foreach( $priceBands as $bandKey => $band ) {
	$orderFilters['groups']['price']['options'][$bandKey] = array( 'label' => $band['label'], 'count' => 0 );
}
// Paid Expedited Processing (ot_expedite total line) counts with deadlines, as does the Expediting status.
$expeditedOrders = array();
if( !empty( $orders ) ) {
	$expeditedOrders = array_flip( $gBitDb->getCol( "SELECT DISTINCT `orders_id` FROM ".TABLE_ORDERS_TOTAL." WHERE `class`='ot_expedite' AND `orders_id` IN (".implode( ',', array_fill( 0, count( $orders ), '?' ) ).")", array_keys( $orders ) ) );
}
foreach( array_keys( $orders ) as $orderId ) {
	$filterClasses = array();
	$isExpediting = strtolower( preg_replace( '/[^\p{L}\p{N}]+/u', '', (string)($orders[$orderId]['orders_status_name'] ?? '') ) ) == 'expediting';
	if( !empty( $orders[$orderId]['deadline_date'] ) || isset( $expeditedOrders[$orderId] ) || $isExpediting ) {
		$filterClasses[] = 'has-deadline';
		$orderFilters['deadline']++;
	}

	$shippingCode = trim( (string)($orders[$orderId]['shipping_method_code'] ?? '') );
	$shippingSlug = trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $shippingCode ) ), '-' );
	if( $shippingSlug === '' ) {
		$shippingSlug = 'none';
		$shippingCode = tra( 'None' );
	}
	if( empty( $orderFilters['groups']['ship']['options'][$shippingSlug] ) ) {
		$orderFilters['groups']['ship']['options'][$shippingSlug] = array( 'label' => $shippingCode, 'count' => 0 );
	}
	$orderFilters['groups']['ship']['options'][$shippingSlug]['count']++;
	$filterClasses[] = 'ship-'.$shippingSlug;

	$orderTotal = (float)($orders[$orderId]['order_total'] ?? 0);
	foreach( $priceBands as $bandKey => $band ) {
		if( (is_null( $band['min'] ) || $orderTotal >= $band['min']) && (is_null( $band['max'] ) || $orderTotal < $band['max']) ) {
			$filterClasses[] = 'price-'.$bandKey;
			$orderFilters['groups']['price']['options'][$bandKey]['count']++;
			break;
		}
	}

	// The same test the list template uses to print the comment row.
	$commentKey = (!empty( $orders[$orderId]['comments'] ) && $orders[$orderId]['comments'] != 'Credit Card processed') ? 'yes' : 'no';
	$filterClasses[] = 'comment-'.$commentKey;
	$orderFilters['groups']['comment']['options'][$commentKey]['count']++;

	$orders[$orderId]['filter_classes'] = implode( ' ', $filterClasses );
	$orders[$orderId]['row_filter_classes'] = $orders[$orderId]['filter_classes'];
}
ksort( $orderFilters['groups']['ship']['options'] );

// Product type menu: an order matches every type among its lines. A listed line row matches only its own type.
if( !empty( $orders ) ) {
	$typeGroup = array( 'title' => tra( 'Product Type' ), 'icon' => 'fa-shapes', 'options' => array() );
	$typeRows = $gBitDb->getAll( "SELECT cop.`orders_id`, cop.`orders_products_id`, pt.`type_id`, pt.`type_name`
		FROM ".TABLE_ORDERS_PRODUCTS." cop
			INNER JOIN ".TABLE_PRODUCTS." cp ON (cp.`products_id`=cop.`products_id`)
			INNER JOIN ".TABLE_PRODUCT_TYPES." pt ON (pt.`type_id`=cp.`products_type`)
		WHERE cop.`orders_id` IN (".implode( ',', array_fill( 0, count( $orders ), '?' ) ).")", array_keys( $orders ) );
	$typeOrders = array();
	foreach( (array)$typeRows as $typeRow ) {
		$orderId = $typeRow['orders_id'];
		$typeId = (int)$typeRow['type_id'];
		$lineId = $typeRow['orders_products_id'];
		$className = 'ptype-'.$typeId;
		if( !isset( $orders[$orderId] ) ) {
			continue;
		}
		if( empty( $typeGroup['options'][$typeId] ) ) {
			$typeGroup['options'][$typeId] = array( 'label' => trim( (string)$typeRow['type_name'] ) !== '' ? $typeRow['type_name'] : (string)$typeId, 'count' => 0 );
		}
		if( empty( $typeOrders[$typeId][$orderId] ) ) {
			$typeOrders[$typeId][$orderId] = TRUE;
			$typeGroup['options'][$typeId]['count']++;
			$orders[$orderId]['filter_classes'] .= ' '.$className;
		}
		if( isset( $orders[$orderId]['products'][$lineId] ) ) {
			$orders[$orderId]['products'][$lineId]['filter_classes'] = trim( ($orders[$orderId]['products'][$lineId]['filter_classes'] ?? '').' '.$className );
		}
	}
	uasort( $typeGroup['options'], function( $a, $b ) { return strnatcasecmp( $a['label'], $b['label'] ); } );
	$orderFilters['groups']['ptype'] = $typeGroup;
}

// Product option filters, one menu per option, when line items are listed. Text and File values are free text, so they are skipped.
if( !empty( $listHash['orders_products'] ) && !empty( $orders ) ) {
	$skipTypes = array( defined( 'PRODUCTS_OPTIONS_TYPE_TEXT' ) ? (int)PRODUCTS_OPTIONS_TYPE_TEXT : 1, defined( 'PRODUCTS_OPTIONS_TYPE_FILE' ) ? (int)PRODUCTS_OPTIONS_TYPE_FILE : 4 );
	$optionTypes = order::optionTypeMap();
	$optionGroups = array();
	$attRows = $gBitDb->getAll( "SELECT * FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE `orders_id` IN (".implode( ',', array_fill( 0, count( $orders ), '?' ) ).")", array_keys( $orders ) );
	foreach( (array)$attRows as $att ) {
		$optionId = (int)$att['products_options_id'];
		$valueId = (int)$att['products_options_values_id'];
		$orderId = $att['orders_id'];
		$lineId = $att['orders_products_id'];
		if( !isset( $orders[$orderId] ) || in_array( (int)($optionTypes[$optionId] ?? -1), $skipTypes ) ) {
			continue;
		}
		$groupKey = 'opt'.$optionId;
		$className = $groupKey.'-'.$valueId;
		if( empty( $optionGroups[$groupKey] ) ) {
			$optionGroups[$groupKey] = array( 'title' => $att['products_options_name'], 'product' => TRUE, 'options' => array(), 'orders' => array() );
		}
		if( empty( $optionGroups[$groupKey]['options'][$valueId] ) ) {
			$optionGroups[$groupKey]['options'][$valueId] = array( 'label' => $att['products_options_values_name'] ?? $att['products_options_values'] ?? $valueId, 'count' => 0, 'orders' => array() );
		}
		// Count orders, not lines, to match the other menus.
		if( empty( $optionGroups[$groupKey]['options'][$valueId]['orders'][$orderId] ) ) {
			$optionGroups[$groupKey]['options'][$valueId]['orders'][$orderId] = TRUE;
			$optionGroups[$groupKey]['options'][$valueId]['count']++;
			$orders[$orderId]['filter_classes'] .= ' '.$className;
		}
		if( isset( $orders[$orderId]['products'][$lineId] ) ) {
			$orders[$orderId]['products'][$lineId]['filter_classes'] = trim( ($orders[$orderId]['products'][$lineId]['filter_classes'] ?? '').' '.$className );
		}
	}
	uasort( $optionGroups, function( $a, $b ) { return strcasecmp( $a['title'], $b['title'] ); } );
	foreach( $optionGroups as $groupKey => $group ) {
		uasort( $group['options'], function( $a, $b ) { return strnatcasecmp( $a['label'], $b['label'] ); } );
		foreach( array_keys( $group['options'] ) as $valueId ) {
			unset( $group['options'][$valueId]['orders'] );
		}
		unset( $group['orders'] );
		$orderFilters['groups'][$groupKey] = $group;
		$orderFilters['products'] = TRUE;
	}
}
foreach( array_keys( $orderFilters['groups'] ) as $groupKey ) {
	$orderFilters['groups'][$groupKey]['options'] = array_filter( $orderFilters['groups'][$groupKey]['options'], function( $pOption ) { return $pOption['count'] > 0; } );
	if( empty( $orderFilters['groups'][$groupKey]['options'] ) ) {
		unset( $orderFilters['groups'][$groupKey] );
	}
}
$gBitSmarty->assign( 'orderFilters', $orderFilters );

$gBitSmarty->assignByRef( 'listOrders', $orders );

?>
<div class="row">
	<div class="col-md-8" id="colone">
<?php

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
	$query = "SELECT co.`orders_status_id` AS `key`, `orders_status_name`, co.`orders_status_id`, COUNT(co.`orders_id`) AS `orders_count`
				FROM " . TABLE_ORDERS . " co
				INNER JOIN " . TABLE_ORDERS_STATUS . " cos ON(co.`orders_status_id`=cos.`orders_status_id`)
				GROUP BY cos.`orders_status_name`, co.`orders_status_id`
				ORDER BY co.`orders_status_id` DESC";
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
		foreach( $statusHash as $ordersStatusHash ) {
			$listProducts = $ordersStatusHash['orders_count'] < 200 ? '&orders_products=1' : '';
			print '<tr class="order-'.($ordersStatusHash['orders_status_id'] > 0 ? 'live' : 'dead').' '.strtolower( preg_replace( '/[^\p{L}\p{N}]+/u', '', $ordersStatusHash['orders_status_name'] ) ).'"><td><a href="' . BITCOMMERCE_PKG_URL . 'admin/index.php?orders_status_comparison='.$listProducts.'&orders_status_id=' . $ordersStatusHash['orders_status_id'] . '">' . tra( $ordersStatusHash['orders_status_name'] ) . '</a></td><td class="text-right"> ' . $ordersStatusHash['orders_count'] . '</td></tr>';
		}
	}
?>
</table>
</div></div>

<?php
	include( BITCOMMERCE_PKG_ADMIN_PATH.'revenue_inc.php' );
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

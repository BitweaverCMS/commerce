<?php 
global $gBitSmarty;
$gBitSmarty->assign( 'currentCategoryId', $current_category_id );
$gBitSmarty->assign( 'catMenu', zen_draw_pull_down_menu( 'cPath', zen_get_category_tree(), $current_category_id, 'onChange="this.form.submit();"') );

$catListHash = $_REQUEST;

if (isset($_GET['search'])) {
	$prodListHash['search'] = $_GET['search'];
	$catListHash['search'] = $_GET['search'];
} else {
	$catListHash['parent_id'] = $current_category_id;
}

$catCount = 0;
if( $catList = CommerceCategory::getList( $catListHash ) ) {
	$catCount = count( $catList );
	if( $gCommerceSystem->isConfigActive( 'SHOW_COUNTS_ADMIN' ) ) {
		foreach( array_keys( $catList ) as $catId ) {
			$catList[$catId]['total_products'] = zen_get_products_to_categories($catId, true);
			$catList[$catId]['total_products_on'] = zen_get_products_to_categories($catId, false);
		}
	}
	$gBitSmarty->assign( 'catList', $catList );
	$gBitSmarty->assign( 'catListHash', $catListHash );
}
$gBitSmarty->assign( 'catCount', $catCount );

// Split Page
// reset page when page is unknown
if ( !empty( $_GET['page'] ) && !empty( $_GET['pID'] ) ) {
	$old_page = $_GET['page'];
	$check_page = $gBitDb->Execute($products_query_raw);
	if ($check_page->RecordCount() > MAX_DISPLAY_RESULTS_CATEGORIES) {
		$check_count=1;
		while (!$check_page->EOF) {
			if ($check_page->fields['products_id'] == $_GET['pID']) {
				break;
			}
			$check_count++;
			$check_page->MoveNext();
		}
		$_GET['page'] = round((($check_count/MAX_DISPLAY_RESULTS_CATEGORIES)+(fmod($check_count,MAX_DISPLAY_RESULTS_CATEGORIES) !=0 ? .5 : 0)),0);
		$page = $_GET['page'];
		if ($old_page != $_GET['page']) {
	//			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
		}
	} else {
		$_GET['page'] = 1;
	}
}

$product = new CommerceProduct();

$offset = new splitPageResults($_GET['page'], MAX_DISPLAY_RESULTS_CATEGORIES, $products_query_raw, $products_query_numrows);

$prodListHash = $_REQUEST;
$prodListHash['all_status'] = 1;
$prodListHash['category_id'] = $current_category_id;
if( $prodList = $product->getList( $prodListHash ) ) {
	$gBitSmarty->assign_by_ref( 'prodList', $prodList );
	$gBitSmarty->assign_by_ref( 'prodListHash', $prodListHash );
	$gBitSmarty->assign( 'prodCount', count( $prodList ) );
}

$sql = "SELECT ptc.`product_type_id`, pt.`type_name` FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc, " . TABLE_PRODUCT_TYPES . " pt where ptc.`category_id`=? and pt.`type_id` = ptc.`product_type_id`";
if( $restrictTypes = $gBitDb->getAssoc($sql, array( $current_category_id ) ) ) {
	$gBitSmarty->assign( 'newProductTypes', $restrictTypes );
} else {
	$gBitSmarty->assign( 'newProductTypes', $product_types_array );
}

if( !empty( $cPath_array ) ) {
	for ($i=0, $n=sizeof($cPath_array)-1; $i<$n; $i++) {
		if (empty($cPath_back)) {
			$cPath_back .= $cPath_array[$i];
		} else {
			$cPath_back .= '_' . $cPath_array[$i];
		}
	}
}

$gBitSmarty->display( 'bitpackage:bitcommerce/admin_product_category_listing_inc.tpl' );


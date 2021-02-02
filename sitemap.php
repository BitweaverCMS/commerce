<?php
require_once( '../kernel/setup_inc.php' );
require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceProduct.php' );
$gSiteMapHash = array();

$listHash['max_records'] = -1;
$listHash['commissioned'] = TRUE;

if( $productList = $gBitProduct->getList($listHash) ) {
	foreach( $productList as $key=>$hash ) {
		if( !empty( $hash['display_url'] ) ) {
			$newHash = array();
			$newHash['loc'] =  BIT_BASE_URI.$hash['display_url'];
			$newHash['priority'] = .8;
			$lastMod = strtotime( $hash['products_last_modified'] );
			$newHash['lastmod'] = date( 'Y-m-d', $lastMod );
			if( (time() - $lastMod) < 86400 ) {
				$freq = 'daily';
			} elseif( (time() - $lastMod) < (86400 * 7) ) {
				$freq = 'weekly';
			} else {
				$freq = 'monthly';
			}
			$newHash['changefreq'] = $freq;
			$gSiteMapHash[$key] = $newHash;
		}
	}
}

$gBitSmarty->assign_by_ref( 'gSiteMapHash', $gSiteMapHash );
$gBitThemes->setFormatHeader( 'xml' );
print $gBitSmarty->display( 'bitpackage:kernel/sitemap.tpl' );

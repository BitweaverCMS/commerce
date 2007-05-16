<?php
global $gBitSystem;

$registerHash = array(
	'package_name' => 'bitcommerce',
	'package_path' => dirname( __FILE__ ).'/',
	'service' => LIBERTY_SERVICE_COMMERCE,
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );
if( $gBitSystem->isPackageActive( 'bitcommerce' ) ) {
	$menuHash = array(
		'package_name'  => BITCOMMERCE_PKG_NAME,
		'index_url'     => BITCOMMERCE_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:bitcommerce/menu_bitcommerce.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );
}

if( !defined( 'BITCOMMERCE_DB_PREFIX' ) ) {
	define( 'BITCOMMERCE_DB_PREFIX', BIT_DB_PREFIX );
}
// include shopping cart class
// 	require_once( BITCOMMERCE_PKG_PATH.'includes/classes/shopping_cart.php' );
if( $gBitSystem->isPackageActive( 'bitcommerce' ) ) {
	define( 'BITPRODUCT_CONTENT_TYPE_GUID', 'bitproduct' );
	$gLibertySystem->registerService( LIBERTY_SERVICE_COMMERCE, BITCOMMERCE_PKG_NAME, array(
		'content_expunge_function' => 'bitcommerce_expunge',
	) );
}

function bitcommerce_expunge ( &$pObject ) {
	require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
	$relProduct = new CommerceProduct();
bt(); die;
	if( $relProduct->loadByRelatedContent( $pObject->mContentId ) ) {
		// do not delete products if related content is getting deleted, but product has been purchased
		if( $relProduct->isPurchased() ) {
			$relProduct->update( array( 'related_content_id' => NULL ) );
		} else {
			$relProduct->expunge();
		}
	}

}



?>

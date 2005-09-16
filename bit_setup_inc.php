<?php
global $gBitSystem;

$gBitSystem->registerPackage( 'bitcommerce', dirname( __FILE__ ).'/' );
if( $gBitSystem->isPackageActive( 'bitcommerce' ) ) {
	$gBitSystem->registerAppMenu( 'bitcommerce', 'Shopping', BITCOMMERCE_PKG_URL.'index.php', 'bitpackage:bitcommerce/menu_bitcommerce.tpl' );
}

if( !defined( 'BITCOMMERCE_DB_PREFIX' ) ) {
	$lastQuote = strrpos( BIT_DB_PREFIX, '`' );
	if( $lastQuote != FALSE ) {
		$lastQuote++;
	}
	$prefix = substr( BIT_DB_PREFIX,  $lastQuote );
	define( 'BITCOMMERCE_DB_PREFIX', $prefix.'com_' );
}

// include shopping cart class
// 	require_once( BITCOMMERCE_PKG_PATH.'includes/classes/shopping_cart.php' );
$gLibertySystem->registerService( LIBERTY_SERVICE_COMMERCE, BITCOMMERCE_PKG_NAME, array(
	'content_expunge_function' => 'bitcommerce_expunge',
) );

function bitcommerce_expunge ( &$pObject ) {
	require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );
	$relProduct = new CommerceProduct();

	if( $relProduct->loadByRelatedContent( $pObject->mContentId ) ) {
		if( !$relProduct->expunge() ) {
			// we couldn't nuke the product because it was purchased
		}
	}

}

?>

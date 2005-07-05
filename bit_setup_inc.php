<?php
global $gBitSystem;

$gBitSystem->registerPackage( 'bitcommerce', dirname( __FILE__ ).'/' );
if( $gBitSystem->isPackageActive( 'bitcommerce' ) ) {
	$gBitSystem->registerAppMenu( 'bitcommerce', 'Shopping', BITCOMMERCE_PKG_URL.'index.php', 'bitpackage:bitcommerce/menu_bitcommerce.tpl' );
}

if( !defined( 'BITCOMMERCE_DB_PREFIX' ) ) {
	define( 'BITCOMMERCE_DB_PREFIX', str_replace( '`', '', BIT_DB_PREFIX.'bit_' ) );
}

?>

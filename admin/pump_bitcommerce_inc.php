<?php
/**
 * @version $Header$
 * @package install
 * @subpackage pumps
 */

/**
 * Required files
 */
	require_once( BITCOMMERCE_PKG_PATH.'includes/common_inc.php' );
	reset_bitcommerce_layout();

	mkdir_p( STORAGE_PKG_PATH.'bitcommerce/images/banners/' );
	copy( BITCOMMERCE_PKG_PATH.'images/banners/125bitcommerce_logo.gif', STORAGE_PKG_PATH.'bitcommerce/images/banners/125bitcommerce_logo.gif' );
	copy( BITCOMMERCE_PKG_PATH.'images/banners/125x125_bitcommerce_logo.gif', STORAGE_PKG_PATH.'bitcommerce/images/banners/125x125_bitcommerce_logo.gif' );

	$pumpedData['Bitcommerce'][] = 'Created Commerce Layout';

?>

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
// $Id: mod_languages.php,v 1.3 2005/08/03 15:35:15 spiderr Exp $
//
	global $db, $gBitProduct, $lng;

	$show_languages= false;
	if (substr(basename($_SERVER['PHP_SELF']), 0, 8) != 'checkout') {
		$show_languages= true;
	}

	if ($show_languages == true) {
		if (!isset($lng) || (isset($lng) && !is_object($lng))) {
		    require_once( BITCOMMERCE_PKG_PATH.'includes/classes/language.php' );
			$lng = new language;
		}
		reset($lng->catalog_languages);
		$gBitSmarty->assign_by_ref( 'sideboxLanguages', $lng->catalog_languages );
		$baseUrl = preg_replace( '/[\?&]?language=[a-z]{2}/', '', $_SERVER['REQUEST_URI'] );
		$baseUrl .= strpos( $baseUrl, '?' ) ? '&' : '?' ;
		$gBitSmarty->assign( 'sideboxLanguagesBaseUrl', $baseUrl );
	}
	if( empty( $moduleTitle ) ) {
		$gBitSmarty->assign( 'moduleTitle', tra( 'Languages' ) );
	}
?>

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
// $Id$
//

  // breaks things
  // pconnect disabled safety switch
  // $gBitDb->close();

	global $gBitSystem;
	if( empty( $title ) ) {
		if( defined( 'META_TAG_TITLE' ) ) {
			$title = META_TAG_TITLE;
		} elseif( empty( $title ) ) {
			$title = ucfirst( BITCOMMERCE_PKG_DIR );
		}
	}
	$gBitSystem->setBrowserTitle( $title );



  if ( (GZIP_LEVEL == '1') && ($ext_zlib_loaded == true) && ($ini_zlib_output_compression < 1) ) {
    if ( (PHP_VERSION < '4.0.4') && (PHP_VERSION >= '4') ) {
      zen_gzip_output(GZIP_LEVEL);
    }
  }
?>

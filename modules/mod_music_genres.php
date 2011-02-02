<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers                           |
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
global $gBitDb, $gBitProduct;
require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );

$music_genres_query = "select music_genre_id, music_genre_name
	from " . TABLE_MUSIC_GENRE . "
	order by `music_genre_name`";

$music_genres = $gBitDb->Execute($music_genres_query);

if ($music_genres->RecordCount()>0) {
	$number_of_rows = $music_genres->RecordCount()+1;

	// Display a list
	$music_genres_array = array();
	if ( !isset($_GET['music_genre_id']) or
		$_GET['music_genre_id'] == '' ) {
		$music_genres_array[] = tra( 'Please Select' );
	} else {
		$music_genres_array[] = tra( '- Reset -' );
	}

	while (!$music_genres->EOF) {
	$music_genre_name = ((strlen($music_genres->fields['music_genre_name']) > MAX_DISPLAY_MUSIC_GENRES_NAME_LEN) ? substr($music_genres->fields['music_genre_name'], 0, MAX_DISPLAY_MUSIC_GENRES_NAME_LEN) . '..' : $music_genres->fields['music_genre_name']);
		$music_genres_array[$music_genres->fields['music_genre_id']] = $music_genre_name;

	$music_genres->MoveNext();
	}
	$gBitSmarty->assign( 'box_genres_array', $music_genres_array );

}
if( empty( $moduleTitle ) ) {
	$gBitSmarty->assign( 'moduleTitle', tra( 'Music Genres' ) );
}
?>

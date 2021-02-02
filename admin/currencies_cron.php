<?php
// :vim:tabstop=4:
// +--------------------------------------------------------------------+
// | Copyright (c) 2005-2010 bitcommerce.org							|
// | http://www.bitcommerce.org											|
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+
//
chdir( dirname( __FILE__ ) );
global $gShellScript;
$gShellScript = TRUE;

if( !empty( $argv ) ) {
	foreach( $argv AS $arg ) {
		switch( $arg ) {
			case '--debug':
				$gDebug = TRUE;
				break;
		}
	}
}

require_once( '../../kernel/setup_inc.php' );

require_once( BITCOMMERCE_PKG_INCLUDE_PATH.'bitcommerce_start_inc.php' );

require_once(DIR_WS_FUNCTIONS . 'localization.php');

$output = currency_update_quotes();
if( !empty( $gDebug ) ) {
	vd( $output );
}

foreach( $output as $result ) {
	if( $result['result'] != 'success' ) {
		print $result['message']."\n";
	}
}

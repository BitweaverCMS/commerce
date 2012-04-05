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
require_once( '../../kernel/setup_inc.php' );

require_once( BITCOMMERCE_PKG_PATH.'includes/bitcommerce_start_inc.php' );

require_once(DIR_WS_FUNCTIONS . 'localization.php');

if( $currencies = $gBitDb->getAssoc("SELECT `currencies_id`, `code`, `title` FROM " . TABLE_CURRENCIES) ) {
	$output = array();
	foreach( $currencies as $curId => $curHash ) {
		$server_used = CURRENCY_SERVER_PRIMARY;
		$quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
		$rate = $quote_function( $curHash['code'] );

		if (empty($rate) && (zen_not_null(CURRENCY_SERVER_BACKUP))) {
            $output[] = sprintf( WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, '', $curHash['code'] );
			$quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
			$rate = $quote_function( $curHash['code'] );
			$server_used = CURRENCY_SERVER_BACKUP;
		}

		if( !empty( $rate ) ) {
			$gBitDb->query( "UPDATE " . TABLE_CURRENCIES . " SET `currency_value`=?, `last_updated` = ".$gBitDb->qtNOW()." WHERE `currencies_id` = ?", array( $rate, $curId ) );
            $output[] = sprintf(TEXT_INFO_CURRENCY_UPDATED, $curHash['title'], $curHash['code'], $server_used);
          } else {
            $output[] = sprintf(ERROR_CURRENCY_INVALID, $curHash['title'], $curHash['code'], $server_used);
		}
	}
}
vd( $output );

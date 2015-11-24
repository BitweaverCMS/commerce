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
//  $Id$
//

// Define how do we update currency exchange rates
// Possible values are 'oanda' 'xe' or ''
define('CURRENCY_SERVER_PRIMARY', 'oanda');
define('CURRENCY_SERVER_BACKUP', 'xe');

require_once( './includes/languages/en/currencies.php' );

$gYahooCurrencies = array();

function currency_update_quotes() {
	global $gBitDb;
	$output = array();
	if( $currencies = $gBitDb->getAssoc("SELECT `code`, `title` FROM " . TABLE_CURRENCIES) ) {
		foreach( $currencies as $curCode => $curTitle ) {
			$server_used = 'yahoo';
			if( !$rate = currency_yahoo_quote( $curCode ) ) {
				$server_used = CURRENCY_SERVER_PRIMARY;
				$quote_function = 'currency_' . CURRENCY_SERVER_PRIMARY . '_quote';
				$rate = $quote_function( $curCode );

				if (empty($rate) && (zen_not_null(CURRENCY_SERVER_BACKUP))) {
					$output[] = sprintf( WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, '', $curCode );
					$quote_function = 'currency_' . CURRENCY_SERVER_BACKUP . '_quote';
					$quote_function = 'currency_yahoo_quote';
					$rate = $quote_function( $curCode );
					$server_used = CURRENCY_SERVER_BACKUP;
				}
			}

			if( !empty( $rate ) ) {
				$gBitDb->query( "UPDATE " . TABLE_CURRENCIES . " SET `currency_value`=?, `last_updated` = ".$gBitDb->qtNOW()." WHERE `code` = ?", array( $rate, $curCode ) );
				$output[$curCode] = array( 'result' => 'success', 'message' => sprintf(TEXT_INFO_CURRENCY_UPDATED, $curTitle, $curCode, $server_used) .' = '.$rate );
			  } else {
				$output[$curCode] = array( 'result' => 'failure', 'message' => sprintf(ERROR_CURRENCY_INVALID, $curTitle, $curCode, $server_used) );
			}
		}
	}
	return $output;
}


function currency_yahoo_load( $base = DEFAULT_CURRENCY ) {
	global $gBitDb, $gYahooCurrencies;
	if( $currencies = $gBitDb->getAssoc("SELECT `code`, `title` FROM " . TABLE_CURRENCIES) ) {
		$searchPairs = '"'.$base.implode( '","'.$base, array_keys( $currencies ) ).'"';
		$yahooSql = 'SELECT * FROM yahoo.finance.xchange WHERE pair IN ('.$searchPairs.')';
		$yahooUrl = 'https://query.yahooapis.com/v1/public/yql?q='.urlencode( $yahooSql ).'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
		if( $jsonQuotes = file_get_contents( $yahooUrl ) ) {
			$yahooQuotes = json_decode( $jsonQuotes, true );
			foreach( $yahooQuotes['query']['results']['rate'] as $quote ) {
				$gYahooCurrencies[$quote['id']] = $quote['Rate'];
			}
		}
	}
}

function currency_yahoo_quote($code, $base = DEFAULT_CURRENCY) {
	global $gYahooCurrencies;
	$ret = FALSE;

	if( empty( $gYahooCurrencies ) ) {
		currency_yahoo_load( $base );
	}

	return BitBase::getParameter( $gYahooCurrencies, strtoupper( $base.$code ) );
}

function currency_oanda_quote($code, $base = DEFAULT_CURRENCY) {
	$page = file('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $code .	'&format=CSV&dest=Get+Table&sel_list=' . $base);

	$match = array();

	preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);

	if (sizeof($match) > 0) {
		return $match[3];
	} else {
		return false;
	}
}

function currency_xe_quote($to, $from = DEFAULT_CURRENCY) {
	$ret = FALSE;

/* dead
	if( $page = file('http://www.xe.net/ucc/convert.cgi?Amount=1&From=' . $from . '&To=' . $to) ) {
		$match = array();

		preg_match('/[0-9.]+\s*' . $from . '\s*=\s*([0-9.]+)\s*' . $to . '/', implode('', $page), $match);

		if (sizeof($match) > 0) {
			$ret = $match[1];
		}
	}
*/
	return $ret;
}
?>

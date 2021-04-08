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
	global $gBitDb, $gCommerceSystem;
	$output = array();
	$server_used = 'exchangeratesapi'; // CURRENCY_SERVER_PRIMARY;
	$quote_function = 'currency_' . $server_used . '_quote';
	if( $currencies = $gBitDb->getAssoc("SELECT `code`, `title` FROM " . TABLE_CURRENCIES . " WHERE `code` != ?", array( DEFAULT_CURRENCY ) ) ) {
		$rates = $quote_function( array_keys( $currencies ), DEFAULT_CURRENCY );
		if( empty( $rates ) ) {
			if( $server_used = $gCommerceSystem->getConfig( 'CURRENCY_SERVER_BACKUP' ) ) {
				$quote_function = 'currency_' . $server_used . '_quote';
				if( function_exists( $quote_function ) ) {
					$rates = $quote_function( array_keys( $currencies ), DEFAULT_CURRENCY );
				}
			}
		}

		if( !empty( $rates ) ) {
			foreach( $rates as $symbol=>$rate ) {
				$gBitDb->query( "UPDATE " . TABLE_CURRENCIES . " SET `currency_value`=?, `last_updated` = ".$gBitDb->qtNOW()." WHERE `code` = ?", array( $rate, $symbol ) );
				$output[$symbol] = array( 'result' => 'success', 'message' => sprintf(TEXT_INFO_CURRENCY_UPDATED, $currencies[$symbol], $symbol, $server_used) .' = '.$rate );
				unset( $currencies[$symbol] );
			}
		}

		if( !empty( $currencies ) ) {
			foreach( $currencies as $symbol=>$curTitle ) {
				$output[$symbol] = array( 'result' => 'failure', 'message' => sprintf(ERROR_CURRENCY_INVALID, $curTitle, $symbol, $server_used) );
			}
			bit_error_email( "Currency Update Failed", '', $output );
		}
	}

	return $output;
}

function currency_exchangeratesapi_quote( $pSymbols, $base = DEFAULT_CURRENCY ) {
	global $gBitDb, $gYahooCurrenciesi, $gCommerceSystem;
	$rates = array();

	$searchPairs = implode( ',', $pSymbols );
	
	$exUrl = 'http://api.exchangeratesapi.io/v1/latest?access_key='.$gCommerceSystem->getConfig('CURRENCY_EXCHANGERATESAPI_KEY').'&symbols='.$searchPairs;
	if( $jsonQuotes = file_get_contents( $exUrl ) ) {
		if( $quoteHash = json_decode( $jsonQuotes, true ) ) {
			if( !empty( $quoteHash['rates'] ) ) {
				foreach( $quoteHash['rates'] as $sym=>$quote ) {
					$rates[$sym] = $quote;
				}
			}
		}
	}
	return $rates;
}


/* dead
function currency_yahoo_load( $base = DEFAULT_CURRENCY ) {
	global $gBitDb, $gYahooCurrencies;
	if( $currencies = $gBitDb->getAssoc("SELECT `code`, `title` FROM " . TABLE_CURRENCIES) ) {
		$searchPairs = '"'.$base.implode( '","'.$base, array_keys( $currencies ) ).'"';
		$yahooSql = 'SELECT * FROM yahoo.finance.xchange WHERE pair IN ('.$searchPairs.')';
		$yahooUrl = 'https://query.yahooapis.com/v1/public/yql?q='.urlencode( $yahooSql ).'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
		if( $jsonQuotes = file_get_contents( $yahooUrl ) ) {
			$yahooQuotes = json_decode( $jsonQuotes, true );
			if( !empty( $yahooQuotes['query']['results']['rate'] ) ) {
				foreach( $yahooQuotes['query']['results']['rate'] as $quote ) {
					$gYahooCurrencies[$quote['id']] = $quote['Rate'];
				}
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

	if( $page = file('http://www.xe.net/ucc/convert.cgi?Amount=1&From=' . $from . '&To=' . $to) ) {
		$match = array();

		preg_match('/[0-9.]+\s*' . $from . '\s*=\s*([0-9.]+)\s*' . $to . '/', implode('', $page), $match);

		if (sizeof($match) > 0) {
			$ret = $match[1];
		}
	}
	return $ret;
}
*/

<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
// $Id$
//

////
// Class to handle currencies
// TABLES: currencies

require_once( KERNEL_PKG_PATH.'BitSingleton.php' );

class currencies extends BitBase {
	var $currencies;

	// class constructor
	function __construct() {
		global $gBitDb;
		parent::__construct();
		$this->currencies = array();
		$currencies_query = "SELECT `code` AS `hash_key`, * FROM " . TABLE_CURRENCIES;

		$this->currencies = $this->mDb->getAssoc( $currencies_query );
	}

	function formatAddTax( $pPrice, $pTax ) {
		$this->format( zen_add_tax( $pPrice, $pTax	) );
	}

	function getRightSymbol( $pCurrency = NULL ) {
		$pCurrency = $this->verifyCurrency( $pCurrency );
		return $this->currencies[$pCurrency]['symbol_right'];
	}

	function getLeftSymbol( $pCurrency = NULL ) {
		$pCurrency = $this->verifyCurrency( $pCurrency );
		return $this->currencies[$pCurrency]['symbol_left'];
	}

	public function getActiveCurrency() {
		return  !empty( $_SESSION['currency'] ) && !empty( $this->currencies[ $_SESSION['currency']] ) ? $_SESSION['currency'] : DEFAULT_CURRENCY;
	}

	public function getActiveCurrencyHash() {
		return $this->currencies[$this->getActiveCurrency()];
	}

	function verifyCurrency( $pCurrency ) {
		if( empty( $pCurrency ) ) {
			$pCurrency = $this->getActiveCurrency();
		}
		if( empty( $this->currencies[$pCurrency] ) ) {
			$pCurrency = DEFAULT_CURRENCY;
		}

		return $pCurrency;
	}

	public function expungeCurrency( $pCurrency ) {

		// Delete the currency as long as it is note the default
		$this->mDb->query( "DELETE FROM " . TABLE_CURRENCIES . " WHERE `code` = ? AND `code` != ?", array( $pCurrency, DEFAULT_CURRENCY ) );
		$this->clearFromCache();
		return $this->mDb->Affected_Rows();
	}

// class methods

	function convert( $pValue, $pToCurrency=NULL, $pFromCurrency=DEFAULT_CURRENCY ) {
		$convertValue = $pValue;
		if( empty( $pToCurrency ) ) {
			$pToCurrency = $this->getActiveCurrency();
		}

		if( !empty( $this->currencies[$pFromCurrency] ) && !empty( $this->currencies[$pToCurrency] ) ) {
			// convert to DEFAULT VALUE
			$convertValue = $pValue / $this->currencies[$pFromCurrency]['currency_value'];
			if( $pToCurrency != DEFAULT_CURRENCY ) {
				$convertValue = $convertValue * $this->currencies[$pToCurrency]['currency_value'];
			}
		}
		return zen_round( $convertValue, $this->currencies[$pToCurrency]['decimal_places']);
	}

	function displayConversion( $pValue, $pToCurrency, $pFromCurrency=DEFAULT_CURRENCY ) {
		$convertValue = $this->convert( $pValue, $pToCurrency, $pFromCurrency );
		return $this->currencies[$pToCurrency]['symbol_left'] . number_format( $convertValue, $this->currencies[$pToCurrency]['decimal_places'], $this->currencies[$pToCurrency]['decimal_point'], $this->currencies[$pToCurrency]['thousands_point']) . $this->currencies[$pToCurrency]['symbol_right'];
	}

	function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {
		$currency_type = $this->verifyCurrency( $currency_type );
		$format_string = '<span class="formatted-price"><sup class="currency-symbol symbol-left">' . $this->currencies[$currency_type]['symbol_left'] . '</sup>';
		if ($calculate_currency_value == true) {
			$rate = (float)(zen_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['currency_value'];
		} else {
			$rate = 1;
		}

		$format_string .= number_format(zen_round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);

		if( $this->currencies[$currency_type]['decimal_places'] ) {
			$format_string = str_replace( $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['decimal_point'].'<span class="fraction">', $format_string).'</span>';
		}

		if( !empty( $this->currencies[$currency_type]['symbol_right'] ) ) {
			$format_string .= '<sup class="currency-symbol symbol-right">' . $this->currencies[$currency_type]['symbol_right'] . '</sup>';
		}

		$format_string .= '</span>';

		return $format_string;
	}

	function value($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {
		if (empty($currency_type)) {
			$currency_type = $this->getActiveCurrency();
		}

		if ($calculate_currency_value == true) {
			if ($currency_type == DEFAULT_CURRENCY) {
				$rate = (zen_not_null($currency_value)) ? $currency_value : 1/$this->currencies[$currency_type]['currency_value'];
			} else {
				$rate = (zen_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['currency_value'];
			}
			$currency_value = zen_round($number * $rate, $this->currencies[$currency_type]['decimal_places']);
		} else {
			$currency_value = zen_round($number, $this->currencies[$currency_type]['decimal_places']);
		}

		return $currency_value;
	}

	function is_set($code) {
		if (isset($this->currencies[$code]) && zen_not_null($this->currencies[$code])) {
			return true;
		} else {
			return false;
		}
	}

	function get_value($code) {
		return $this->currencies[$code]['currency_value'];
	}

	function getInputStep( $pCurrency = DEFAULT_CURRENCY ) {
		return '0.'.str_pad( '1', $this->get_decimal_places( $pCurrency ), '0', STR_PAD_LEFT );
	}

	function get_decimal_places( $pCurrency = DEFAULT_CURRENCY ) {
		return $this->currencies[$pCurrency]['decimal_places'];
	}

	function display_price($products_price, $products_tax, $quantity = 1, $currency_type = '', $currency_value = '') {
		return $this->format( (zen_add_tax($products_price, $products_tax) * $quantity), true, $currency_type, $currency_value );
	}

	function verify( &$pParamHash ) {
		if( empty( $pParamHash['code'] ) ) {
			$this->mErrors = tra( 'A currency code is required' );
		} else {
			$pParamHash['currency_store']['code'] = $pParamHash['code'];
		}
		$pParamHash['currency_store']['decimal_places'] = (int)( isset( $pParamHash['decimal_places'] ) ? $pParamHash['decimal_places'] : 2 );
		$pParamHash['currency_store']['decimal_point'] = ( !empty( $pParamHash['decimal_point'] ) ? $pParamHash['decimal_point'] : '.' );
		$pParamHash['currency_store']['thousands_point'] = ( !empty( $pParamHash['thousands_point'] ) ? $pParamHash['thousands_point'] : ',' );

		if( empty( $pParamHash['symbol_left'] ) && empty( $pParamHash['symbol_right'] ) ) {
			$pParamHash['currency_store']['symbol_right'] = $pParamHash['code'];
		}
		if( !empty( $pParamHash['symbol_left'] ) ) {
			$pParamHash['currency_store']['symbol_left'] = $pParamHash['symbol_left'];
		}
		if( !empty( $pParamHash['symbol_right'] ) ) {
			$pParamHash['currency_store']['symbol_right'] = $pParamHash['symbol_right'];
		}
		if( !empty( $pParamHash['title'] ) ) {
			$pParamHash['currency_store']['title'] = trim( $pParamHash['title'] );
		}
		$pParamHash['currency_store']['currency_value'] = ( !empty( $pParamHash['currency_value'] ) ? $pParamHash['currency_value'] : 1 );
		$pParamHash['currency_store']['last_updated'] = $this->mDb->NOW();

		return( count( $this->mErrors ) == 0 );
	}

	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash ) ) {
			if( $currenciesId = $this->currencyExists( $pParamHash['currency_store']['code'] ) ) {
				$this->mDb->associateUpdate( TABLE_CURRENCIES, $pParamHash['currency_store'], array( 'currencies_id' =>$currenciesId ) );
			} else {
				$this->mDb->associateInsert( TABLE_CURRENCIES, $pParamHash['currency_store'] );
				$currenciesId = zen_db_insert_id( TABLE_CURRENCIES, 'currencies_id' );
			}
			$this->clearFromCache();
		}
	}

	function currencyExists( $code ) {
		return $this->mDb->getOne( "SELECT `currencies_id` FROM " . TABLE_CURRENCIES . " WHERE `code` = ?", array( $code ) );
	}

	function bulkImport( $pBulkString ) {
		$lines = explode( "\n", $pBulkString );
		if( count( $lines ) ) {
			foreach( $lines as $line ) {
				$currValues = array();
				preg_match( '/([A-Z]{3}) ([\w]+[\w ]*) [ ]+([\d\.]+)[ ]+([\d\.]+)/', $line, $currValues );
				if( count( $currValues ) > 1 ) {
					$currHash['code'] = $currValues[1];
					$currHash['title'] = $currValues[2];
					$currHash['currency_value'] = $currValues[4];
					$this->store( $currHash );
				}
			}
		}
		sscanf( $pBulkString, "\n" );
	}
}
?>

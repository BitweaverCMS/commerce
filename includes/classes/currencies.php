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
// $Id: currencies.php,v 1.3 2005/08/03 13:03:20 spiderr Exp $
//

////
// Class to handle currencies
// TABLES: currencies
  class currencies {
    var $currencies;

// class constructor
    function currencies() {
      global $db;
      $this->currencies = array();
      $currencies_query = "select code, title, symbol_left, symbol_right, decimal_point,
                                  thousands_point, decimal_places, value
                          from " . TABLE_CURRENCIES;

      $currencies = $db->Execute($currencies_query);

      while (!$currencies->EOF) {
        $this->currencies[$currencies->fields['code']] = array('title' => $currencies->fields['title'],
                                                       'symbol_left' => $currencies->fields['symbol_left'],
                                                       'symbol_right' => $currencies->fields['symbol_right'],
                                                       'decimal_point' => $currencies->fields['decimal_point'],
                                                       'thousands_point' => $currencies->fields['thousands_point'],
                                                       'decimal_places' => $currencies->fields['decimal_places'],
                                                       'value' => $currencies->fields['value']);

      $currencies->MoveNext();
      }
    }

    function formatAddTax( $pPrice, $pTax ) {
		$this->format( zen_add_tax( $pPrice, $pTax  ) );
	}

// class methods
    function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {

      if (empty($currency_type)) $currency_type = $_SESSION['currency'];

      if ($calculate_currency_value == true) {
        $rate = (zen_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(zen_round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      } else {
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(zen_round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      }

      if (DOWN_FOR_MAINTENANCE=='true' and DOWN_FOR_MAINTENANCE_PRICES_OFF=='true') {
        $format_string= '';
      }

      return ' '.$format_string;
    }

    function value($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {

      if (empty($currency_type)) $currency_type = $_SESSION['currency'];

      if ($calculate_currency_value == true) {
        if ($currency_type == DEFAULT_CURRENCY) {
          $rate = (zen_not_null($currency_value)) ? $currency_value : 1/$this->currencies[$_SESSION['currency']]['value'];
        } else {
          $rate = (zen_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
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
      return $this->currencies[$code]['value'];
    }

    function get_decimal_places($code) {
      return $this->currencies[$code]['decimal_places'];
    }

    function display_price($products_price, $products_tax, $quantity = 1) {
      return $this->format(zen_add_tax($products_price, $products_tax) * $quantity);
    }
  }
?>
<?php
//
// +----------------------------------------------------------------------+
// zen-cart Open Source E-commerce
// +----------------------------------------------------------------------+
// Copyright (c) 2003 The zen-cart developers
//
// http://www.zen-cart.com/index.php
//
// Portions Copyright (c) 2003 osCommerce
// +----------------------------------------------------------------------+
// This source file is subject to version 2.0 of the GPL license,
// that is bundled with this package in the file LICENSE, and is
// available through the world-wide-web at the following url:
// http://www.zen-cart.com/license/2_0.txt.
// If you did not receive a copy of the PHP license and are unable to
// obtain it through the world-wide-web, please send a note to
// license@zen-cart.com so we can mail you a copy immediately.
// +----------------------------------------------------------------------+
// $Id$
//

class CommerceShipping {
	var $modules;

// class constructor
	function CommerceShipping($module = '') {
		global $gBitCustomer;

		if (defined('MODULE_SHIPPING_INSTALLED') && zen_not_null(MODULE_SHIPPING_INSTALLED)) {
			$this->modules = explode(';', MODULE_SHIPPING_INSTALLED);
			$include_modules = array();

			if ( (zen_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($_SERVER['SCRIPT_NAME'], (strrpos($_SERVER['SCRIPT_NAME'], '.')+1)), $this->modules)) ) {
				$include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($_SERVER['SCRIPT_NAME'], (strrpos($_SERVER['SCRIPT_NAME'], '.')+1)));
			} else {
				reset($this->modules);
				while (list(, $value) = each($this->modules)) {
					$base = basename( $value );
					$class = substr( $base, 0, strrpos($base, '.'));
					$include_modules[] = array('class' => $class, 'file' => $value);
				}
			}

			for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
//					include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/shipping/' . $include_modules[$i]['file']);
				$langFile = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/shipping/', $include_modules[$i]['file'], 'false');
				if( file_exists( $langFile ) ) {
					include_once( $langFile );
				}
				include_once( BITCOMMERCE_PKG_PATH . DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file'] );
				$GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class']();
			}
		}
	}

	function quote( $pShippingWeight, $method = '', $module = '') {
		global $currencies;

		$quotes_array = array();

		if( !empty( $this->modules ) ) {
			$shipHash['method'] = $method;
			$shipHash['shipping_num_boxes'] = 1;
			$shipHash['shipping_weight'] = $pShippingWeight;

			$za_tare_array = preg_split("/[:,]/" , SHIPPING_BOX_WEIGHT);
			$zc_tare_percent= $za_tare_array[0];
			$zc_tare_weight= $za_tare_array[1];

			$za_large_array = preg_split("/[:,]/" , SHIPPING_BOX_PADDING);
			$zc_large_percent= $za_large_array[0];
			$zc_large_weight= $za_large_array[1];

			if( SHIPPING_MAX_WEIGHT <= $shipHash['shipping_weight'] ) {
				// large box add padding
				$shipHash['shipping_weight'] = $shipHash['shipping_weight'] + ($shipHash['shipping_weight']*($zc_large_percent/100)) + $zc_large_weight;
			} else {
				// add tare weight < large
				$shipHash['shipping_weight'] = $shipHash['shipping_weight'] + ($shipHash['shipping_weight']*($zc_tare_percent/100)) + $zc_tare_weight;
			}

			if ($shipHash['shipping_weight'] > SHIPPING_MAX_WEIGHT) { // Split into many boxes
				$shipHash['shipping_num_boxes'] = ceil($shipHash['shipping_weight']/SHIPPING_MAX_WEIGHT);
				$shipHash['shipping_weight'] = $shipHash['shipping_weight']/$shipHash['shipping_num_boxes'];
			}

			$include_quotes = array();

			reset($this->modules);
			while (list(, $value) = each($this->modules)) {
				$base = basename( $value );
				$class = substr($base, 0, strrpos($base, '.'));
				if (zen_not_null($module)) {
					if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
						$include_quotes[] = $class;
					}
				} elseif ($GLOBALS[$class]->enabled) {
					$include_quotes[] = $class;
				}
			}

			$size = sizeof($include_quotes);
			for ($i=0; $i<$size; $i++) {
				if( $quotes = $GLOBALS[$include_quotes[$i]]->quote( $shipHash ) ) {
					if( !empty( $quotes['methods'] ) ) {
						foreach( array_keys( $quotes['methods'] ) as $j ) {
							$quotes['methods'][$j]['cost_add_tax'] = zen_add_tax($quotes['methods'][$j]['cost'], (isset($quotes['tax']) ? $quotes['tax'] : 0));
							$quotes['methods'][$j]['format_add_tax'] = $currencies->format( $quotes['methods'][$j]['cost_add_tax'] );
						}
					}
					$quotes_array[] = $quotes;
				}
			}

		}

		return $quotes_array;
	}

	function cheapest() {
		if (is_array($this->modules)) {
			$rates = array();

			reset($this->modules);
			while (list(, $value) = each($this->modules)) {
				$class = substr($value, 0, strrpos($value, '.'));
				if( !empty( $GLOBALS[$class] ) && $GLOBALS[$class]->enabled) {
					$quotes = $GLOBALS[$class]->quotes;
					if( !empty( $quotes['methods'] ) ) {
						$size = sizeof( $quotes['methods'] );
						for ($i=0; $i<$size; $i++) {
							if ($quotes['methods'][$i]['cost']) {
								$rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
																 'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
																 'cost' => $quotes['methods'][$i]['cost'],
																 'module' => $quotes['id']
																 );
							}
						}
					}
				}
			}

			$cheapest = false;
			$size = sizeof($rates);
			for ($i=0; $i<$size; $i++) {
				if (is_array($cheapest)) {
					// never quote storepickup as lowest - needs to be configured in shipping module
					if ($rates[$i]['cost'] < $cheapest['cost'] and $rates[$i]['module'] != 'storepickup') {
						$cheapest = $rates[$i];
					}
				} else {
					if ($rates[$i]['module'] != 'storepickup') {
						$cheapest = $rates[$i];
					}
				}
			}

			return $cheapest;
		}
	}
}
?>

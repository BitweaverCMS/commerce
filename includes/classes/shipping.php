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
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: shipping.php,v 1.5 2005/10/06 21:01:47 spiderr Exp $
//

  class shipping {
    var $modules;

// class constructor
    function shipping($module = '') {
      global $PHP_SELF, $gBitCustomer;

      if (defined('MODULE_SHIPPING_INSTALLED') && zen_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

        $include_modules = array();

        if ( (zen_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
        } else {
          reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class, 'file' => $value);
          }
        }

        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
//          include(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/shipping/' . $include_modules[$i]['file']);
			$langFile = zen_get_file_directory(DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/shipping/', $include_modules[$i]['file'], 'false');
			if( file_exists( $langFile ) ) {
				include( $langFile );
			}
			include(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);

			$GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
        }
      }
    }

    function quote($method = '', $module = '') {
      global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

      $quotes_array = array();

      if (is_array($this->modules)) {
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        $za_tare_array = split("[:,]" , SHIPPING_BOX_WEIGHT);
        $zc_tare_percent= $za_tare_array[0];
        $zc_tare_weight= $za_tare_array[1];

        $za_large_array = split("[:,]" , SHIPPING_BOX_PADDING);
        $zc_large_percent= $za_large_array[0];
        $zc_large_weight= $za_large_array[1];

// SHIPPING_BOX_WEIGHT = tare
// SHIPPING_BOX_PADDING = Large Box % increase
// SHIPPING_MAX_WEIGHT = Largest package

/*
        if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
          $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
        } else {
          $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
        }
*/

        switch (true) {
        // large box add padding
        case(SHIPPING_MAX_WEIGHT <= $shipping_weight):
          $shipping_weight = $shipping_weight + ($shipping_weight*($zc_large_percent/100)) + $zc_large_weight;
          break;
        default:
        // add tare weight < large
          $shipping_weight = $shipping_weight + ($shipping_weight*($zc_tare_percent/100)) + $zc_tare_weight;
          break;
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
          $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        $include_quotes = array();

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
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
          $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          if (is_array($quotes)) $quotes_array[] = $quotes;
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
          if ($GLOBALS[$class]->enabled) {
            $quotes = $GLOBALS[$class]->quotes;
            $size = sizeof($quotes['methods']);
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

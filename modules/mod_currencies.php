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
// $Id: mod_currencies.php,v 1.3 2005/08/02 15:35:45 spiderr Exp $
//
	global $db, $gBitProduct, $currencies;

	// test if box should display
	$show_currencies= false;

	if (substr(basename($_SERVER['PHP_SELF']), 0, 8) != 'checkout') {
		$show_currencies= true;
	}

	if ($show_currencies == true) {
		if (isset($currencies) && is_object($currencies)) {
			reset($currencies->currencies);
			$currencies_array = array();
			while (list($key, $value) = each($currencies->currencies)) {
				$currencies_array[] = array('id' => $key, 'text' => $value['title']);
			}

			$hidden_get_variables = '';
			reset($_GET);
			while (list($key, $value) = each($_GET)) {
				if ( ($key != 'currency') && ($key != zen_session_name()) && ($key != 'x') && ($key != 'y') ) {
					$hidden_get_variables .= zen_draw_hidden_field($key, $value);
				}
			}

			$gBitSmarty->assign( 'sideboxCurrenciesPulldown', zen_draw_pull_down_menu('currency', $currencies_array, $_SESSION['currency'], 'onchange="this.form.submit();" style="width: 100%"') );
			if( empty( $moduleTitle ) ) {
				$gBitSmarty->assign( 'moduleTitle', tra( 'Currencies' ) );
			}
		}
	}
?>

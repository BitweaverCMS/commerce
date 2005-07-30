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
// $Id: specials.php,v 1.3 2005/07/30 03:01:58 spiderr Exp $
//

// test if box should display
	$show_specials= false;

	if( $gBitProduct->isValid() ) {
		$show_specials= true;
	} else {
		$show_specials= false;
	}

	if ($show_specials == true) {
		$listHash['max_records'] = 1;
		$listHash['sort_mode'] = 'random';
		$listHash['specials'] = TRUE;

		if( $specialsList = $gBitProduct->getList( $listHash ) ) {
			$random_product = current( $specialsList );
			$specials_box_price = zen_get_products_display_price($random_product['products_id']);

			require($template->get_template_dir('tpl_specials.php',DIR_WS_TEMPLATE, $current_page_base,'sideboxes'). '/tpl_specials.php');
			$title =  BOX_HEADING_SPECIALS;
			$left_corner = false;
			$right_corner = false;
			$right_arrow = false;
			$title_link = FILENAME_SPECIALS;
			require($template->get_template_dir($column_box_default, DIR_WS_TEMPLATE, $current_page_base,'common') . '/' . $column_box_default);
		}
	}
?>

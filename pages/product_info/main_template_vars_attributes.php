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
// $Id: main_template_vars_attributes.php,v 1.36 2009/08/25 17:17:30 spiderr Exp $
//
//////////////////////////////////////////////////
//// BOF: attributes
//////////////////////////////////////////////////
// limit to 1 for larger tables

$productSettings = array();
if( $productOptions = $gBitProduct->getProductOptions() ) {
	// manage filename uploads
//	$_GET['number_of_uploads'] = $productSettings['number_of_uploads'];
//	zen_draw_hidden_field('number_of_uploads', $productSettings['number_of_uploads']);
//	$gBitSmarty->assign( 'productSettings', $productSettings );
	$gBitSmarty->assign_by_ref( 'productOptions', $productOptions );
}

//////////////////////////////////////////////////
//// EOF: attributes
//////////////////////////////////////////////////

?>

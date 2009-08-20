<?php
// +--------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org									|
// | http://www.bitcommerce.org											|
// +--------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license		|
// +--------------------------------------------------------------------+

global $gBitSmarty;

$gBitSmarty->assign_by_ref( 'gBitCustomer', $gBitCustomer );
$gBitSmarty->display( 'bitpackage:bitcommerce/page_shopping_cart.tpl' );

?>

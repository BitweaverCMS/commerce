<?php
//
// +----------------------------------------------------------------------+
// | bitcommerce                                                          |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 bitcommerce.org                                   |
// |                                                                      |
// | http://www.bitcommerce.org                                           |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license        |
// +----------------------------------------------------------------------+
//  $Id$
//

global $gBitSmarty, $gBitSystem;

if( $gBitSystem->getConfig( 'commerce_register_interests' ) ) {
	require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceCustomer.php' );
	$gBitSmarty->assign('comInterests', CommerceCustomer::getInterests() );
}
?>

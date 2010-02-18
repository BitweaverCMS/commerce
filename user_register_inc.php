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
//  $Id: user_register_inc.php,v 1.1 2010/02/18 17:27:48 spiderr Exp $
//

global $gBitSmarty, $gBitSystem;

if( $gBitSystem->getConfig( 'commerce_register_interests' ) ) {
	require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceCustomer.php' );
	$gBitSmarty->assign('comInterests', CommerceCustomer::getInterests() );
}
?>

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
//  $Id: center_orders_history.php,v 1.1 2009/03/28 20:18:08 spiderr Exp $
//

global $gBitSmarty, $gBitCustomer;
$gBitSmarty->assign_by_ref( 'ordersHistory', $gBitCustomer->getOrdersHistory() );

?>

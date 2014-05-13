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

global $gBitSmarty, $gBitCustomer;
$history = $gBitCustomer->getOrdersHistory(); 
vd( $history );
$_template->tpl_vars['ordersHistory'] = new Smarty_variable( $history );


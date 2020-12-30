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
// $Id$
//

  require_once(DIR_FS_MODULES . 'require_languages.php');
  $breadcrumb->add(NAVBAR_TITLE);

//  $_SESSION['customer_id'] = '';
//  $_SESSION['customer_default_address_id'] = '';
//  $_SESSION['customers_authorization'] = '';
//  $_SESSION['customer_first_name'] = '';
//  $_SESSION['customer_country_id'] = '';
//  $_SESSION['customer_zone_id'] = '';
//  $_SESSION['comments'] = '';
//  $_SESSION['gv_id'] = '';
//  $_SESSION['cc_id'] = '';
//  $_SESSION['cot_gv'] = '0.00';

//  $gBitCustomer->mCart->reset();
 session_destroy();
?>

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

define('NAVBAR_TITLE', 'Redeem ' . TEXT_GV_NAME);
define('HEADING_TITLE', 'Redeem ' . TEXT_GV_NAME);
define('TEXT_INFORMATION', 'For more information regarding ' . TEXT_GV_NAME . ', please see our <a href="' . zen_href_link(FILENAME_GV_FAQ, '', tra( 'NONSSL').'">' . GV_FAQ . '.</a>' ) );
define('TEXT_INVALID_GV', tra( 'The ' . TEXT_GV_NAME . ' number may be invalid or has already been redeemed. Proceed to checkout to view the applied '.TEXT_GV_NAME.'. To contact the shop owner please use the Contact Page' ) );
define('TEXT_VALID_GV', tra( 'Congratulations, you have redeemed a ' . TEXT_GV_NAME . ' worth %s.' ) );
?>

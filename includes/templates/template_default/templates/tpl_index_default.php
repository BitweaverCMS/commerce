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
// $Id: tpl_index_default.php,v 1.3 2005/07/10 17:31:47 spiderr Exp $
//
?>
    <h1><?php echo HEADING_TITLE; ?></h1>
    <p class="greetUser"><?php
	global $gBitUser;
    if( $gBitUser->isRegistered() ) {
      print( sprintf(TEXT_GREETING_PERSONAL, $gBitUser->getDisplayName(), zen_href_link(FILENAME_PRODUCTS_NEW)) );
    } else {
      print( sprintf(TEXT_GREETING_GUEST, USERS_PKG_URL.'login.php', zen_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')) );
    }
 ?></p>
    <p><?php echo TEXT_MAIN; ?></p>
<?php
if (TUTORIAL_STATUS=='1') {
?>
    <p><?php echo TEXT_INFORMATION; ?></p>
<?php } ?>
	<?php include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS); ?>
    <?php include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS); ?>

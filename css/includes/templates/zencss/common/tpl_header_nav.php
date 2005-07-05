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
// $Id: tpl_header_nav.php,v 1.1 2005/07/05 05:59:27 bitweaver Exp $
//
?>
<?php echo '<div class="centervert"><a href="' . zen_href_link(FILENAME_DEFAULT) . '" id="logo" >' . zen_image(DIR_WS_TEMPLATE_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT) . '</a></div>'; ?>
<?php echo '<div id="headernavbar">'; ?>
<?php if ($_SESSION['customer_id']) { ?>
              <a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGOFF; ?></a>&nbsp;|&nbsp;
              <a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></a>
<?php } else { ?>
              <a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGIN; ?></a>
<?php } ?>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
              &nbsp;|&nbsp;<a href="<?php echo zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'); ?>"><?php echo HEADER_TITLE_CART_CONTENTS; ?></a>&nbsp;|&nbsp;<a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><?php echo HEADER_TITLE_CHECKOUT; ?></a>
<?php } ?>
<?php echo '&nbsp;|&nbsp;&nbsp;';?>
<?php require(DIR_WS_MODULES . 'sideboxes/' . 'search_header.php'); ?>
<?php echo '</div>'; ?>

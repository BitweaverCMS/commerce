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
// $Id: tpl_ssl_check_default.php,v 1.1 2005/07/05 05:59:04 bitweaver Exp $
//
?>
<!-- Row 1 Column 1 -->
 
<h1><?php echo HEADING_TITLE; ?></h1>
<?php echo TEXT_INFORMATION; ?>
<h2><?php echo BOX_INFORMATION_HEADING; ?></h2>
<?php echo BOX_INFORMATION; ?> 
<?php echo TEXT_INFORMATION_2; ?>
<?php echo TEXT_INFORMATION_3; ?>
<?php echo TEXT_INFORMATION_4; ?> 
<?php echo TEXT_INFORMATION_5; ?>
<div class="row">
<span class="right"><?php echo '<a href="' . zen_href_link(FILENAME_LOGIN) . '">' . zen_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></span>
</div>
<br class="clear" />

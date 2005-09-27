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
// $Id: tpl_main_page.php,v 1.2 2005/09/27 22:33:53 spiderr Exp $
//
?>

<body onload="resize();">
<table width="96%" border="0" cellpadding="2" cellspacing ="2" align="center" class="popupshippingestimator">
  <tr>
    <td class="main" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CURRENT_CLOSE_WINDOW . '</a>'; ?></td>
  </tr>
  <tr>
    <td>
      <?php require(DIR_FS_MODULES . 'shipping_estimator.php'); ?>
    </td>
  </tr>
  <tr>
    <td class="main" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CURRENT_CLOSE_WINDOW . '</a>'; ?></td>
  </tr>
</table>
</body>
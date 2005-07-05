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
// $Id: tpl_modules_upcoming_products.php,v 1.1 2005/07/05 05:59:04 bitweaver Exp $
//
?>
<h2 class="upcoming"><?php echo TABLE_HEADING_UPCOMING_PRODUCTS; ?></h2>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="tableHeading">&nbsp;<?php echo TABLE_HEADING_UPCOMING_PRODUCTS; ?>&nbsp;</td>
    <td align="right" class="tableHeading">&nbsp;<?php echo TABLE_HEADING_DATE_EXPECTED; ?>&nbsp;</td>
  </tr>
<?php
    $row = 0;
    while (!$expected->EOF) {
      $row++;
      if (($row / 2) == floor($row / 2)) {
        echo '  <tr class="upcomingProducts-even">' . "\n";
      } else {
        echo '  <tr class="upcomingProducts-odd">' . "\n";
      }

      echo '    <td class="smallText">&nbsp;<a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $expected->fields['products_id']) . '">' . $expected->fields['products_name'] . '</a>&nbsp;</td>' . "\n" .
           '    <td align="right" class="smallText">&nbsp;' . zen_date_short($expected->fields['date_expected']) . '&nbsp;</td>' . "\n" .
           '  </tr>' . "\n";
      $expected->MoveNext();
    }
?>
</table>
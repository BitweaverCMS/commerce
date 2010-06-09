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
?>
<!-- bof: upcoming_products -->
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="tableHeading"><?php echo TABLE_HEADING_UPCOMING_PRODUCTS; ?></td>
    <td align="right" nowrap="nowrap" class="tableHeading"><?php echo TABLE_HEADING_DATE_EXPECTED; ?></td>
  </tr>
  <tr>
    <td colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR); ?></td>
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

      echo '    <td class="smallText"><a href="' . zen_href_link(zen_get_info_page($expected->fields['products_id']), 'products_id=' . $expected->fields['products_id']) . '">' . $expected->fields['products_name'] . '</a></td>' . "\n" .
           '    <td align="right" class="smallText">' . zen_date_short($expected->fields['date_expected']) . '</td>' . "\n" .
           '  </tr>' . "\n";
      $expected->MoveNext();
    }
?>
  <tr>
    <td colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR); ?></td>
  </tr>
</table>
<!-- eof: upcoming_products -->

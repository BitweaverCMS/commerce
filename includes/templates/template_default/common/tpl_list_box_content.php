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
// $Id: tpl_list_box_content.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//

//print_r($list_box_contents);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <?php
  for($row=0;$row<sizeof($list_box_contents);$row++) {
    $params = "";
    if ($list_box_contents[$row]['align']) $params = 'align = "' . $list_box_contents[$row]['align']. '"';
    if ($list_box_contents[$row]['params']) $params .= ' ' . $list_box_contents[$row]['params'];
?>
  <tr <?php echo $params; ?>> 
    <?php
    for($col=0;$col<sizeof($list_box_contents[$row]);$col++) {
      $r_params = "";
      if ($list_box_contents[$row][$col]['align']) $r_params = 'align="' . $list_box_contents[$row][$col]['align']. '"';
      if ($list_box_contents[$row][$col]['params']) $r_params .= ' ' . $list_box_contents[$row][$col]['params'];
      if ($list_box_contents[$row][$col]['text']) {
?>
    <td <?php echo $r_params; ?>> 
      <?php
      echo $list_box_contents[$row][$col]['text']
?>
    </td>
    <?php
      }
    }
?>
  </tr>
  <?php
  }
?>
</table>

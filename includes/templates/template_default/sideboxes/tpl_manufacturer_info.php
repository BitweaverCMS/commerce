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
// $Id: tpl_manufacturer_info.php,v 1.1 2005/07/05 05:59:02 bitweaver Exp $
//
  $id = mfrinfo;
  $content = "";
      if (zen_not_null($manufacturer->fields['manufacturers_image'])) $content .= zen_image(DIR_WS_IMAGES . $manufacturer->fields['manufacturers_image'], $manufacturer->fields['manufacturers_name']) . '';
	  if ((zen_not_null($manufacturer->fields['manufacturers_image'])) && (zen_not_null($manufacturer->fields['manufacturers_url']))) $content .= '<br />';
      if (zen_not_null($manufacturer->fields['manufacturers_url'])) $content .= '<a href="' . zen_href_link(FILENAME_REDIRECT, 'action=manufacturer&manufacturers_id=' . $manufacturer->fields['manufacturers_id']) . '" target="_blank">' . sprintf(BOX_MANUFACTURER_INFO_HOMEPAGE, $manufacturer->fields['manufacturers_name']) . '</a>';
?>
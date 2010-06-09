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
//  $Id$
//
  $za_contents = array();
  $za_heading = array();
  $za_heading = array('text' => BOX_HEADING_TOOLS, 'link' => zen_href_link_admin(FILENAME_ALT_NAV, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_TEMPLATE_SELECT, 'link' => zen_href_link_admin(FILENAME_TEMPLATE_SELECT, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_LAYOUT_CONTROLLER, 'link' => KERNEL_PKG_URL.'admin/index.php?page=layout&amp;module_package='.BITCOMMERCE_PKG_NAME, '', 'NONSSL');
  $za_contents[] = array('text' => 'RESET LAYOUT', 'link' => FILENAME_LAYOUT_CONTROLLER.'.php?action=reset_defaults', '', 'NONSSL');
// removed broken
//  $za_contents[] = array('text' => BOX_TOOLS_BACKUP, 'link' => zen_href_link_admin(FILENAME_BACKUP, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_BANNER_MANAGER, 'link' => zen_href_link_admin(FILENAME_BANNER_MANAGER, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_MAIL, 'link' => zen_href_link_admin(FILENAME_MAIL, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_NEWSLETTER_MANAGER, 'link' => zen_href_link_admin(FILENAME_NEWSLETTERS, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_SERVER_INFO, 'link' => zen_href_link_admin(FILENAME_SERVER_INFO, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_WHOS_ONLINE, 'link' => zen_href_link_admin(FILENAME_WHOS_ONLINE, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_ADMIN, 'link' => zen_href_link_admin(FILENAME_ADMIN, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_EMAIL_WELCOME, 'link' => zen_href_link_admin(FILENAME_EMAIL_WELCOME, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_STORE_MANAGER, 'link' => zen_href_link_admin(FILENAME_STORE_MANAGER, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_DEVELOPERS_TOOL_KIT, 'link' => zen_href_link_admin(FILENAME_DEVELOPERS_TOOL_KIT, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TOOLS_DEFINE_PAGES_EDITOR, 'link' => zen_href_link_admin(FILENAME_DEFINE_PAGES_EDITOR, '', 'NONSSL'));

if ($za_dir = @dir(DIR_WS_BOXES . 'extra_boxes')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/tools_dhtml.php$/', $zv_file)) {
      require(DIR_WS_BOXES . 'extra_boxes/' . $zv_file);
    }
  }
}

?>
<!-- tools //-->
<?php
echo zen_draw_admin_box($za_heading, $za_contents);
?>

<li><form method="get" action="<?=BITCOMMERCE_PKG_URL?>admin/index.php"><div style="display:inline">
	<select name="top_search_scope">
		<option value="order_num">Lookup Order #</option>
	</select>
	<input name="orders_search" value="" size="6"/>
	<input type="submit" name="top_search" class="minibutton" value="Go"/>
</div></form></li>
<!-- tools_eof //-->


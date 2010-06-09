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
  $za_heading = array('text' => BOX_HEADING_LOCATION_AND_TAXES, 'link' => zen_href_link_admin(FILENAME_ALT_NAV, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TAXES_COUNTRIES, 'link' => zen_href_link_admin(FILENAME_COUNTRIES, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_LOCALIZATION_CURRENCIES, 'link' => zen_href_link_admin(FILENAME_CURRENCIES, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_LOCALIZATION_LANGUAGES, 'link' => zen_href_link_admin(FILENAME_LANGUAGES, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TAXES_ZONES, 'link' => zen_href_link_admin(FILENAME_ZONES, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TAXES_GEO_ZONES, 'link' => zen_href_link_admin(FILENAME_GEO_ZONES, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TAXES_TAX_CLASSES, 'link' => zen_href_link_admin(FILENAME_TAX_CLASSES, '', 'NONSSL'));
  $za_contents[] = array('text' => BOX_TAXES_TAX_RATES, 'link' => zen_href_link_admin(FILENAME_TAX_RATES, '', 'NONSSL'));
if ($za_dir = @dir(DIR_WS_BOXES . 'extra_boxes')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/taxes_dhtml.php$/', $zv_file)) {
      require(DIR_WS_BOXES . 'extra_boxes/' . $zv_file);
    }
  }
}
?>
<!-- taxes //-->
<?php
echo zen_draw_admin_box($za_heading, $za_contents);
?>
<!-- taxes_eof //-->

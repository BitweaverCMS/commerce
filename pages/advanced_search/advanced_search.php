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
<?php echo zen_draw_form('advanced_search', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get', 'onsubmit="return check_form(this);"') . zen_hide_session_id(); ?>
<?php echo zen_draw_hidden_field('main_page', FILENAME_ADVANCED_SEARCH_RESULT); ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE_1; ?></h1></td>
  </tr>
<?php
  if ($messageStack->size('search') > 0) {
?>
  <tr>
    <td class="main" colspan="2"><?php echo $messageStack->output('search'); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="plainBoxHeading"><?php echo HEADING_SEARCH_CRITERIA; ?></td>
    <td class="smallText" align="right" valign="bottom"><?php echo '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_SEARCH_HELP) . '\')">' . TEXT_SEARCH_HELP_LINK . '</a>'; ?></td>
  </tr>
  <tr>
    <td class="plainBox" colspan="2" align="center"><?php echo zen_draw_input_field('keyword', '', 'style="width: 90%"'); ?><br /><?php echo zen_draw_checkbox_field('search_in_description', '1') . ' ' . TEXT_SEARCH_IN_DESCRIPTION; ?></td>
  </tr>
  <tr>
    <td class="fieldKey"><?php echo ENTRY_CATEGORIES; ?></td>
    <td class="fieldValue"><?php echo zen_draw_pull_down_menu('categories_id', zen_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES)),0 ,'', '1')); ?></td>
  </tr>
  <tr>
    <td class="fieldKey">&nbsp;</td>
    <td class="smallText"><?php echo zen_draw_checkbox_field('inc_subcat', '1', true) . ' ' . ENTRY_INCLUDE_SUBCATEGORIES; ?></td>
  </tr>
  <tr>
    <td class="fieldKey"><?php echo ENTRY_MANUFACTURERS; ?></td>
    <td class="fieldValue"><?php echo zen_draw_pull_down_menu('manufacturers_id', zen_get_manufacturers(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)))); ?></td>
  </tr>
  <tr>
    <td class="fieldKey"><?php echo ENTRY_PRICE_FROM; ?></td>
    <td class="fieldValue"><?php echo zen_draw_input_field('pfrom'); ?></td>
  </tr>
  <tr>
    <td class="fieldKey"><?php echo ENTRY_PRICE_TO; ?></td>
    <td class="fieldValue"><?php echo zen_draw_input_field('pto'); ?></td>
  </tr>
  <tr>
    <td class="fieldKey"><?php echo ENTRY_DATE_FROM; ?></td>
    <td class="fieldValue"><?php echo zen_draw_input_field('dfrom', DOB_FORMAT_STRING, 'onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?></td>
  </tr>
  <tr>
    <td class="fieldKey"><?php echo ENTRY_DATE_TO; ?></td>
    <td class="fieldValue"><?php echo zen_draw_input_field('dto', DOB_FORMAT_STRING, 'onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
    <td class="main" align="right" colspan="2" ><?php echo zen_image_submit(BUTTON_IMAGE_SEARCH, BUTTON_SEARCH_ALT); ?></td>
  </tr>
</table></form>

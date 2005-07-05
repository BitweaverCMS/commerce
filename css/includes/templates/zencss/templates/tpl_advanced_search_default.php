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
// $Id: tpl_advanced_search_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<?php echo zen_draw_form('advanced_search', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get', 'onsubmit="return check_form(this);"') . zen_hide_session_id(); ?> 
<?php echo zen_draw_hidden_field('main_page', FILENAME_ADVANCED_SEARCH_RESULT); ?> 
<h1><?php echo HEADING_TITLE_1; ?></h1>
<?php
  if ($messageStack->size('search') > 0) {
?>
<?php echo $messageStack->output('search'); ?> 
<?php
  }
?>
<fieldset>
<legend><?php echo HEADING_SEARCH_CRITERIA; ?></legend>
<?php echo zen_draw_input_field('keyword', '', 'style="width: 90%"'); ?>
<div class="formrow">
  <label><?php echo TEXT_SEARCH_IN_DESCRIPTION; ?></label>
  <?php echo zen_draw_checkbox_field('search_in_description', '1'); ?>
  </div>
</fieldset>
<?php echo '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_SEARCH_HELP) . '\')">' . TEXT_SEARCH_HELP_LINK . '</a>'; ?> 
<fieldset>
<div class="formrow">
  <label><?php echo ENTRY_CATEGORIES; ?></label>
  <?php echo zen_draw_pull_down_menu('categories_id', zen_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES)))); ?> 
  <?php echo zen_draw_checkbox_field('inc_subcat', '1', true) . ' ' . ENTRY_INCLUDE_SUBCATEGORIES; ?> 
</div>
<div class="formrow">
  <label><?php echo ENTRY_MANUFACTURERS; ?></label>
  <?php echo zen_draw_pull_down_menu('manufacturers_id', zen_get_manufacturers(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)))); ?> 
</div>
<div class="formrow">
  <label><?php echo ENTRY_PRICE_FROM; ?></label>
  <?php echo zen_draw_input_field('pfrom'); ?> </div>
<div class="formrow">
  <label><?php echo ENTRY_PRICE_TO; ?> </label>
  <?php echo zen_draw_input_field('pto'); ?> </div>
<div class="formrow">
  <label><?php echo ENTRY_DATE_FROM; ?></label>
  <?php echo zen_draw_input_field('dfrom', DOB_FORMAT_STRING, 'onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?> 
</div><div class="formrow">
<label><?php echo ENTRY_DATE_TO; ?></label>
<?php echo zen_draw_input_field('dto', DOB_FORMAT_STRING, 'onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?>
</div>
</fieldset>
<?php echo zen_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH); ?> </form> 


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
// $Id: tpl_modules_product_listing.php,v 1.2 2005/11/30 06:28:04 spiderr Exp $
//
?>
<table border="0" width="100%" cellspacing="2" cellpadding="0">
<?php
// only show when there is something to submit and enabled
    if( !empty( $show_top_submit_button ) && $show_top_submit_button == 'true') {
?>
  <tr>
    <td align="right" colspan="2">
    <input type="submit" align="absmiddle" value="<?php echo SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART; ?>" id="submit1" name="submit1" Class="SubmitBtn">
    </td>
  </tr>
<?php
    } // show top submit
?>
<?php if ( ($listing_split->number_of_rows > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>
  <tr>
    <td class="pageresults"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
    <td class="pageresults" align="right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></td>
  </tr>
<?php
}
?>
  <tr>
    <td colspan="2" class="productlisting">
<?php
  require($template->get_template_dir('tpl_list_box_content.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_list_box_content.php');
?>
    </td>
  </tr>
<?php if ( ($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>
  <tr>
    <td class="pageresults"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
    <td class="pageresults" align="right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
  </tr>
<?php
  }
?>
<?php
// only show when there is something to submit and enabled
    if( !empty( $show_bottom_submit_button ) && $show_bottom_submit_button == 'true' ) {
?>
  <tr>
    <td align="right" colspan="2">
    <input type="submit" align="absmiddle" value="<?php echo SUBMIT_BUTTON_ADD_PRODUCTS_TO_CART; ?>" id="submit1" name="submit1" Class="SubmitBtn"></form>
    </td>
  </tr>
<?php
    } // show_bottom_submit_button
?>
</table>

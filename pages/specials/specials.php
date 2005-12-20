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
// $Id: specials.php,v 1.2 2005/12/20 17:13:07 gilesw Exp $
//
?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="3"><h1><?php echo $breadcrumb->last();  ?></h1></td>
  </tr>
<?php
  $specials_query_raw = "select p.`products_id`, pd.`products_name`, p.`products_price`, p.`products_tax_class_id`, p.`products_image`, s.specials_new_products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.`products_status` = '1' and s.`products_id` = p.`products_id` and p.`products_id` = pd.`products_id` and pd.`language_id` = '" . (int)$_SESSION['languages_id'] . "' and s.status = '1' order by s.specials_date_added DESC";
  $specials_split = new splitPageResults($specials_query_raw, MAX_DISPLAY_SPECIAL_PRODUCTS);

  if (($specials_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
  <tr>
    <td colspan="3" class="main"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td colspan="3"><table width="100%"><tr>
      <td colspan="2" class="pageresults"><?php echo $specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></td>
      <td align="right" class="pageresults"><?php echo TEXT_RESULT_PAGE . ' ' . $specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></td>
    </tr></table></td>
  </tr>
<?php
  } // split page
?>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
<?php
    $row = 0;
	$offset = MAX_DISPLAY_SPECIAL_PRODUCTS * (!empty( $_REQUEST['page'] ) ? ($_REQUEST['page'] - 1) : 0);
    $specials = $db->query($specials_split->sql_query, NULL, MAX_DISPLAY_SPECIAL_PRODUCTS, $offset );
    while (!$specials->EOF) {
      $row++;

      echo '            <td valign="bottom" align="center" width="33%" class="smallText"><a href="' . zen_href_link(zen_get_info_page($specials->fields['products_id']), 'products_id=' . $specials->fields['products_id']) . '">' . zen_image( CommerceProduct::getImageUrl( $specials->fields['products_id'], 'avatar' ), $specials->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . zen_href_link(zen_get_info_page($specials->fields['products_id']), 'products_id=' . $specials->fields['products_id']) . '">' . $specials->fields['products_name'] . '</a><br />' . CommerceProduct::getDisplayPrice($specials->fields['products_id']) . '</td>' . "\n";

      if ((($row / 3) == floor($row / 3))) {
?>
  </tr>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
<?php
      }
      $specials->MoveNext();
    }
?>
   </tr>
<?php
  if (($specials_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
  <tr>
    <td colspan="3" class="main"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td colspan="3"><table width="100%"><tr>
      <td colspan="2" class="pageresults"><?php echo $specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></td>
      <td align="right" class="pageresults"><?php echo TEXT_RESULT_PAGE . ' ' . $specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></td>
    </tr></table></td>
  </tr>
  <tr>
    <td colspan="3" class="main"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
  } // split page
?>
  <tr>
    <td class="main"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
  </tr>
</table>

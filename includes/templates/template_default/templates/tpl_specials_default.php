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
// $Id: tpl_specials_default.php,v 1.2 2005/07/05 18:40:23 spiderr Exp $
//
?>
<h1><?php echo $breadcrumb->last();  ?></h1>

<?php
  $specials_query_raw = "select p.products_id, pd.products_name, p.products_price, p.products_tax_class_id, p.products_image, s.specials_new_products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_status = '1' and s.products_id = p.products_id and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and s.status = '1' order by s.specials_date_added DESC";
  $offset = splitPageResults::splitPageResults($specials_query_raw, MAX_DISPLAY_SPECIAL_PRODUCTS);

  if (($specials_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>
<div class="row" id="pageresultstop">
<span class="left"><?php echo TEXT_RESULT_PAGE . ' ' . $specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></span>
<span class="right"><?php echo $specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></span>
</div>
<br class="clear" />
<?php
  }
?>
<table border="0"  width="100%" cellspacing="2" cellpadding="2">
<tr>
<?php
    $row = 0;
    $specials = $db->Execute( $specials_query_raw, NULL, $offset );
    while (!$specials->EOF) {
      $row++;

      echo '            <td valign="bottom" align="center" width="33%" class="smallText"><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $specials->fields['products_id']) . '">' . zen_image(DIR_WS_IMAGES . $specials->fields['products_image'], $specials->fields['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br /><a href="' . zen_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $specials->fields['products_id']) . '">' . $specials->fields['products_name'] . '</a><br /><span class="normalprice">' . $currencies->display_price($specials->fields['products_price'], zen_get_tax_rate($specials->fields['products_tax_class_id'])) . '</span><br /><span class="specialprice">' . $currencies->display_price($specials->fields['specials_new_products_price'], zen_get_tax_rate($specials->fields['products_tax_class_id'])) . '</span></td>' . "\n";

      if ((($row / 3) == floor($row / 3))) {
?>
          </tr>
          <tr>
            <td colspan="3"><?php echo zen_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
          </tr>
          <tr>
<?php
      }
      $specials->MoveNext();
    }
?>
   </tr>
   </table>
<?php
  if (($specials_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
?>

<?php $back = sizeof($_SESSION['navigation']->path)-2; ?>
<br class="clear" />
<div class="row" id="pageresultsbottom">
<span class="left"><?php echo $specials_split->display_count(TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></span>
<span class="right"><?php echo TEXT_RESULT_PAGE . ' ' . $specials_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></span>
</div>
<br class="clear" />
<?php
  }
?>
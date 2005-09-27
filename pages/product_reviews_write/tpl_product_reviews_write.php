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
// $Id: tpl_product_reviews_write.php,v 1.2 2005/09/27 22:33:57 spiderr Exp $
//
?>
<?php echo zen_draw_form('product_reviews_write', zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, 'action=process&products_id=' . $_GET['products_id'], 'SSL'), 'post', 'onsubmit="return checkForm(product_reviews_write);"'); ?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
  </tr>
  <tr>
    <td class="pageHeading" valign="top" colspan="2"><h1><?php echo $products_name . $products_model; ?></h1></td>
  </tr>
<?php
  if ($messageStack->size('review_text') > 0) {
?>
  <tr>
    <td class="main" colspan="2"><?php echo $messageStack->output('review_text'); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td align="left" class="smallText">
    <?php
      echo '<h1>' . $products_price . '</h1>' .
           '<br />' . '<a href="' . zen_href_link(zen_get_info_page($_GET['products_id']), zen_get_all_get_params()) . '">' . TEXT_PRODUCT_INFO . '</a>' .
           '<br /><br />' . SUB_TITLE_FROM, zen_output_string_protected($customer->fields['customers_firstname'] . ' ' . $customer->fields['customers_lastname']) .
           '<br />' . SUB_TITLE_REVIEW;
    ?>
    </td>
    <td align="center" valign="top" class="smallText">
      <?php
        if (zen_not_null($products_image)) {
          require(DIR_FS_MODULES . 'pages/' . $current_page_base . '/main_template_vars_images.php');
        }

/* Turn off price on a Write Page
        // more info in place of buy now
        if (zen_has_product_attributes($review_info->fields['products_id'] )) {
          //   $link = '<p>' . '<a href="' . zen_href_link(zen_get_info_page($review_info->fields['products_id']), 'products_id=' . $review_info->fields['products_id'] ) . '">' . MORE_INFO_TEXT . '</a>' . '</p>';
          $link = '';
        } else {
          $link= '<p><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now') . '">' . zen_image_button(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT) . '</a></p>';
        }

        echo $link;
*/
      ?>
    </td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_textarea_field('review_text', 'soft', 60, 15); ?></td>
  </tr>
  <tr>
    <td class="smallText" colspan="2"><?php echo TEXT_NO_HTML . (REVIEWS_APPROVAL == '1' ? '<br />' . TEXT_APPROVAL_REQUIRED: ''); ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
   </tr>
  <tr>
    <td class="main" colspan="2" align="center" valign="top"><?php echo SUB_TITLE_RATING . '<br />' . TEXT_BAD . ' ' . zen_draw_radio_field('rating', '1') . ' ' . zen_draw_radio_field('rating', '2') . ' ' . zen_draw_radio_field('rating', '3') . ' ' . zen_draw_radio_field('rating', '4') . ' ' . zen_draw_radio_field('rating', '5') . ' ' . TEXT_GOOD; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo zen_draw_separator(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params(array('reviews_id', 'action'))) . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
    <td class="main" align="right"><?php echo zen_image_submit(BUTTON_IMAGE_SUBMIT, BUTTON_SUBMIT_ALT); ?></td>
  </tr>
</table>

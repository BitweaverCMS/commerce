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
// $Id: checkout_payment.php,v 1.4 2006/12/13 18:20:01 spiderr Exp $
//
?>
<?php echo $payment_modules->javascript_validation(); ?>
<?php echo zen_draw_form('checkout_payment', zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onsubmit="return check_form();"'); ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="3"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
  if ($messageStack->size('checkout_payment') > 0) {
?>
  <tr>
    <td colspan="3"><?php echo $messageStack->output('checkout_payment'); ?></td>
  </tr>
<?php
  }
  if (DISPLAY_CONDITIONS_ON_CHECKOUT == 'true') {
?>
  <tr>
    <td class="main" align="center" valign="top"><?php echo TABLE_HEADING_CONDITIONS; ?><br /></td>
    <td colspan="2" class="main" valign="top"><?php echo TEXT_CONDITIONS_DESCRIPTION . '<br /><br />' . zen_draw_checkbox_field('conditions', '1', false, 'id="conditions"') . '<label for="conditions">&nbsp;' . TEXT_CONDITIONS_CONFIRM . '</label>'; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
  }
    $order_total_modules->process();
?>
  <tr>
    <td class="main" align="center" valign="top"><?php echo TITLE_BILLING_ADDRESS; ?><br /><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ARROW_SOUTH_EAST); ?></td>
    <td class="main" style="width:200px;" valign="top"><?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'); ?><p><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_CHANGE_ADDRESS, BUTTON_CHANGE_ADDRESS_ALT) . '</a>'; ?></p></td>
    <td class="main" valign="top"><?php echo TEXT_SELECTED_BILLING_DESTINATION; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="main" align="right" colspan="3">
      <table border="0"  cellspacing="0" cellpadding="2">
        <tr>
          <td align="right" colspan="2"><?php echo $order_total_modules->output(); ?></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="3"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
  if (SHOW_ACCEPTED_CREDIT_CARDS != '0') {
?>
  <tr>
    <td colspan="3">
<?php
    if (SHOW_ACCEPTED_CREDIT_CARDS == '1') {
      echo TEXT_ACCEPTED_CREDIT_CARDS . zen_get_cc_enabled();
    }
    if (SHOW_ACCEPTED_CREDIT_CARDS == '2') {
      echo TEXT_ACCEPTED_CREDIT_CARDS . zen_get_cc_enabled('IMAGE_');
    }
?>
    </td>
  </tr>
<?php } ?>
<?php
  $selection = $payment_modules->selection();

  if (sizeof($selection) > 1) {
?>
  <tr>
    <td class="main" valign="top" colspan="2"><?php echo TEXT_SELECT_PAYMENT_METHOD; ?></td>
    <td class="main" valign="top" align="right"><?php echo TITLE_PLEASE_SELECT; ?><br /><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ARROW_EAST_SOUTH); ?></td>
  </tr>
<?php
  } else {
?>
  <tr>
    <td class="main" colspan="3"><?php echo TEXT_ENTER_PAYMENT_INFORMATION; ?></td>
  </tr>
<?php
  }

  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
?>
<?php
    if ( ($selection[$i]['id'] == $_SESSION['payment']) || ($n == 1) ) {
      echo '  <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    } else {
      echo '  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    }
?>
    <td class="plainBoxHeading" colspan="2"><?php echo $selection[$i]['module']; ?></td>
    <td class="main" align="right">
<?php
    if( defined( 'MODULE_ORDER_TOTAL_COD_STATUS' ) && MODULE_ORDER_TOTAL_COD_STATUS == 'true' and $selection[$i]['id'] == 'cod') {
      echo TEXT_INFO_COD_FEES;
    } else {
      // echo 'WRONG ' . $selection[$i]['id'];
    }
    if (sizeof($selection) > 1) {
      echo zen_draw_radio_field('payment', $selection[$i]['id'], ($i==0?TRUE:FALSE) );
    } else {
      echo zen_draw_hidden_field('payment', $selection[$i]['id']);
    }
?>
    </td>
  </tr>
<?php
    if (isset($selection[$i]['error'])) {
?>
  <tr>
    <td class="main" colspan="4"><?php echo $selection[$i]['error']; ?></td>
  </tr>
<?php
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>
  <tr>
    <td colspan="4">
      <table border="0" cellspacing="0" cellpadding="2">
<?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>
        <tr>
          <td class="main"><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
          <td class="main"><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
        </tr>
<?php
      }
?>
      </table>
    </td>
  </tr>
<?php
    }
    $radio_buttons++;
?>

<?php
  }
?>
<?php
  $selection =  $order_total_modules->credit_selection();//
  if (sizeof($selection)>0) {
?>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="3"><?php echo TABLE_HEADING_CREDIT_PAYMENT; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
    for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
    	if( !empty( $selection[$i] ) ) {
?>
  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
    <td class="main" colspan="2"><?php echo $selection[$i]['module']; ?></td>
    <td class="main" colspan="2"align="right" colspan="2"><?php if( !empty( $selection[$i]['checkbox'] ) ) { echo $selection[$i]['checkbox']; } ?></td>
  </tr>
<?php
			if( !empty($_GET['credit_class_error_code'] ) && $_GET['credit_class_error_code'] == $selection[$i]['id']) {
?>
  <tr>
    <td class="messageStackError" width="100%" valign="top" colspan="3"><?php echo zen_output_string_protected($_GET['credit_class_error']); ?></td>
  </tr>
<?php
			}
			for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>
  <tr>
    <td class="main"colspan="2"><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
    <td class="main" colspan="1"><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
  </tr>
<?php
			}
      	}
    }
  }
?>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="plainBoxHeading" colspan="3"><?php echo TABLE_HEADING_COMMENTS; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_textarea_field('comments', 'soft', '60', '5'); ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></td>
    <td class="main" align="right"><?php echo zen_image_submit(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT, 'onClick="submitFunction('.$gBitCustomer=>getGiftBalance().','.$order->info['total'].')"'); ?></td>
  </tr>
</table></form>

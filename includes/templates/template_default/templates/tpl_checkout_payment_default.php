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
// $Id: tpl_checkout_payment_default.php,v 1.2 2005/07/13 20:24:05 spiderr Exp $
//
bt();
?>
<?php echo zen_draw_form('checkout_payment', zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onsubmit="return check_form();"'); ?> 
<h1><?php echo HEADING_TITLE; ?></h1>
<?php if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) { ?>
<p><?php echo zen_output_string_protected($error['title']); ?></p>
<p class="messageStackError"><?php echo zen_output_string_protected($error['error']); ?></p>
<?php } ?>
<fieldset>
<legend><?php echo TITLE_BILLING_ADDRESS; ?></legend>
<div class="formrow"> 
  <div style="float:left;width:30%;"><?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'); ?></div>
  <div style="float:right;width:65%"><?php echo TEXT_SELECTED_BILLING_DESTINATION; ?></div>
  <br class="clear" />
</div>
<div class="formrow"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . zen_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>'; ?></div>
<br class="clear" />
</fieldset>
<fieldset>
<legend><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></legend>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <?php $selection = $payment_modules->selection();
if (sizeof($selection) > 1) { ?>
  <tr> 
    <td valign="top" colspan="3"><?php echo TEXT_SELECT_PAYMENT_METHOD; ?></td>
  </tr>
  <?php  } else { ?>
  <tr> 
    <td colspan="3"><?php echo TEXT_ENTER_PAYMENT_INFORMATION; ?></td>
  </tr>
  <?php }

  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
?>
  <?php if ( ($selection[$i]['id'] == $_SESSION['payment']) || ($n == 1) ) {
      echo '                  <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    } else {
      echo '                  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    }
?>
  <td colspan="2"><?php echo $selection[$i]['module']; ?></td>
  <td align="right"> 
    <?php
    if (sizeof($selection) > 1) {
      echo zen_draw_radio_field('payment', $selection[$i]['id']);
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
    <td colspan="4"><?php echo $selection[$i]['error']; ?></td>
  </tr>
  <?php
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>
  <tr> 
    <td colspan="4"> <table border="0" cellspacing="0" cellpadding="2">
        <?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>
        <tr> 
          <td><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
          <td><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
        </tr>
        <?php
      }
?>
      </table></td>
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
    <td class="plainBoxHeading" colspan="3"><b><?php echo TABLE_HEADING_CREDIT_PAYMENT; ?></b></td>
  </tr>
  <?php
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
?>
  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)"> 
    <td colspan="2"><b><?php echo $selection[$i]['module']; ?></b></td>
    <td align="right" colspan="2"><?php echo $selection[$i]['checkbox']; ?></td>
  </tr>
  <?php
      if ($_GET['credit_class_error_code'] == $selection[$i]['id']) {
?>
  <tr> 
    <td><?php echo TEXT_ERROR?></td>
    <td colspan="2"><?php echo $_GET['credit_class_error']; ?></td>
  </tr>
  <?php
      }
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>
  <tr> 
    <td><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
    <td colspan="2"><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
  </tr>
  <?php
      }

    }
  }
?>
</table>
</fieldset>
<fieldset>
<legend><?php echo TABLE_HEADING_COMMENTS; ?></legend>
<div class="formrow"><?php echo zen_draw_textarea_field('comments', 'soft', '60', '5'); ?></div>
</fieldset>
<h3><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE; ?></h3> 
<p><?php echo TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?><span class="right"><?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></span></p></form>

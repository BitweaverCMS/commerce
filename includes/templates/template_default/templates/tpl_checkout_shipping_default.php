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
// $Id: tpl_checkout_shipping_default.php,v 1.1 2005/07/05 05:59:04 bitweaver Exp $
//
?>
<?php echo zen_draw_form('checkout_address', zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')) . zen_draw_hidden_field('action', 'process'); ?>
	<h1><?php echo HEADING_TITLE; ?></h1>
	
	<div class="fieldset">
	<div class="legend"><?php echo TITLE_SHIPPING_ADDRESS; ?></div>
		<div class="formrow">
			<div style="float:left;width:30%;"><?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'); ?></div>
			<div style="float:right;width:65%"><?php echo TEXT_CHOOSE_SHIPPING_DESTINATION; ?></div>
		</div>
		<div class="formrow"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . zen_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>'; ?></div>
	</div>

<?php if (zen_count_shipping_modules() > 0) { ?>
	<fieldset>
		<legend><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></legend>
		<table border="0" width="100%" cellspacing="2" cellpadding="2"> 
<?php if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) { ?>
              <tr>
                <td valign="top" colspan="3"><?php echo TEXT_CHOOSE_SHIPPING_METHOD; ?></td>
              </tr>
<?php } elseif ($free_shipping == false) { ?>
              <tr>
                <td width="100%" colspan="3"><?php echo TEXT_ENTER_SHIPPING_INFORMATION; ?></td>
              </tr>
<?php }

    if ($free_shipping == true) { ?>
              <tr>
                <td colspan="3" width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td colspan="3"><?php echo FREE_SHIPPING_TITLE; ?>&nbsp;<?php echo $quotes[$i]['icon']; ?></td>
                  </tr>
                  <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, 0)">
                    <td width="100%"><?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . zen_draw_hidden_field('shipping', 'free_free'); ?></td>
                  </tr>
                </table></td>
              </tr>
<?php } else {
      $radio_buttons = 0;
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) { ?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td colspan="3"><b><?php echo $quotes[$i]['module']; ?></b>&nbsp;<?php if (isset($quotes[$i]['icon']) && zen_not_null($quotes[$i]['icon'])) { echo $quotes[$i]['icon']; } ?></td>
                  </tr>
<?php if (isset($quotes[$i]['error'])) { ?>
                  <tr>
                    <td colspan="3"><?php echo $quotes[$i]['error']; ?></td>
                  </tr>
<?php } else {
          for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
// set the radio button to be checked if it is the method chosen
            $checked = (($quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) ? true : false);

            if ( ($checked == true) || ($n == 1 && $n2 == 1) ) {
              echo '                  <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
            } else {
              echo '                  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
            } ?>
                    <td width="75%"><?php echo $quotes[$i]['methods'][$j]['title']; ?></td>
<?php
            if ( ($n > 1) || ($n2 > 1) ) {
?>
                    <td><?php echo $currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))); ?></td>
                    <td align="center"><?php echo zen_draw_radio_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked); ?></td>
<?php
            } else {
?>
                    <td align="right" colspan="2"><?php echo $currencies->format(zen_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'])) . zen_draw_hidden_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']); ?></td>
<?php
            }
?>
                  </tr>
<?php
            $radio_buttons++;
          }
        }
?>
                </table></td>
              </tr>
<?php
      }
    }
?>
  
<?php
  }
?>
 </table>
 </fieldset>

	<fieldset>
		<legend><?php echo TABLE_HEADING_COMMENTS; ?></legend>
	    <div class="formrow"><?php echo zen_draw_textarea_field('comments', 'soft', '60', '5'); ?></div>
	</fieldset>

	<h3><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE; ?></h3>
	<p><?php echo TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?><span class="right"><?php echo zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></span></p>
 </form>

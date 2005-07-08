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
// $Id: tpl_checkout_confirmation_default.php,v 1.2 2005/07/08 06:13:05 spiderr Exp $
//
?>
<h1><?php echo HEADING_TITLE; ?></h1>

<fieldset style="width:42%;float:left">
	<legend><?php echo HEADING_BILLING_ADDRESS .' (<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">edit</a>)'; ?></legend>
    <p><?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?></p>
</fieldset>


<fieldset style="width:42%;float:right">
    <legend><?php echo HEADING_DELIVERY_ADDRESS .' (<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">edit</a>)'; ?></legend>
<?php if ($_SESSION['sendto'] != false) { ?>
	<p><?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?></p>
<?php } ?>
</fieldset>

<br class="cleargap" />
<br class="cleargap" />

<fieldset>
<?php if (sizeof($order->info['tax_groups']) > 1) { ?>
	<legend><?php echo HEADING_PRODUCTS.' (<a href="' . zen_href_link(FILENAME_SHOPPING_CART, '', 'SSL') . '">edit</a>)'; ?></legend>
	<table border="0"  width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td class="smallText" align="right"><?php echo HEADING_TAX; ?></td>
		<td class="smallText" align="right"><?php echo HEADING_TOTAL; ?></td>
	</tr>
<?php } else { ?>
	<legend><?php echo HEADING_PRODUCTS.' (<a href="' . zen_href_link(FILENAME_SHOPPING_CART, '', 'SSL') . '">edit</a>)'; ?></legend>
	<table border="0"  width="100%" cellspacing="0" cellpadding="2">
<?php }

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td align="right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
         '            <td valign="top">' . $order->products[$i]['name'];

    if (STOCK_CHECK == 'true') {
      echo zen_check_stock($order->products[$i]['id'], $order->products[$i]['qty']);
    }

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '</td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) echo '            <td valign="top" align="right">' . zen_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";

    echo '            <td align="right" valign="top">' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
		</td>
	</tr>
	<tr>
		<td align="right" colspan="3">
<table border="0"  cellspacing="0" cellpadding="2"> <!-- Subtotal + Shipping + Grand Total -->
<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    $order_total_modules->process();
    echo $order_total_modules->output();
  }
?>
</table>
</table>
</fieldset>

<?php if ($order->info['shipping_method']) { ?>
	<fieldset>
		<legend><?php echo HEADING_SHIPPING_METHOD . ' (<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">edit</a>)'; ?></legend>
		<p><?php echo $order->info['shipping_method']; ?></p>
	</fieldset>
<?php } ?>

<fieldset>
	<legend><?php echo HEADING_PAYMENT_METHOD . ' (<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">edit</a>)'; ?></legend>
	<p><?php echo $order->info['payment_method']; ?></p>
</fieldset>

<fieldset>
	<legend><?php echo HEADING_ORDER_COMMENTS .' (<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">edit</a>)'; ?></legend>
	<p><?php echo (empty($order->info['comments']) ? NO_COMMENTS_TEXT : nl2br(zen_output_string_protected($order->info['comments'])) . zen_draw_hidden_field('comments', $order->info['comments'])); ?></p>
</fieldset>


<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
?>

<fieldset>
<table border="0"  cellspacing="0" cellpadding="2">
              <tr>
                <td colspan="3"><?php echo $confirmation['title']; ?></td>
              </tr>
<?php for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) { ?>
              <tr>
                <td class="smallText"><?php echo $confirmation['fields'][$i]['title']; ?></td>
                <td class="smallText"><?php echo $confirmation['fields'][$i]['field']; ?></td>
              </tr>
<?php } ?>
</table>
<?php
    }
  }
?>
</fieldset>

<p><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?>

<?php
  if (isset($$_SESSION['payment']->form_action_url)) {
    $form_action_url = $$_SESSION['payment']->form_action_url;
  } else {
    $form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
  }

  echo zen_draw_form('checkout_confirmation', $form_action_url, 'post');

  if (is_array($payment_modules->modules)) {
    echo $payment_modules->process_button();
  }

  echo zen_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER) . '</form>' . "\n";
?>
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
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="3"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr valign="top">
    <td class="main">
	    <h4><?php echo HEADING_BILLING_ADDRESS; ?></h4>
		<?php echo zen_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?>
		<p><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_CHANGE_ADDRESS, BUTTON_CHANGE_ADDRESS_ALT) . '</a>'; ?></p>
	</td>
    <td class="main">
<?php
  if ($_SESSION['sendto'] != false) {
?>
		<h4><?php echo HEADING_DELIVERY_ADDRESS; ?></h4>
    	<?php echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?>
		<p><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_CHANGE_ADDRESS, BUTTON_CHANGE_ADDRESS_ALT) . '</a>'; ?></p>
<?php
  }
?>

	</td>
    <td></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
// always show comments
//  if ($order->info['comments']) {
?>
  <tr>
    <td colspan="2">
		<h4><?php echo HEADING_ORDER_COMMENTS; ?></h4>
		<?php echo (empty($order->info['comments']) ? NO_COMMENTS_TEXT : nl2br(zen_output_string_protected($order->info['comments'])) . zen_draw_hidden_field('comments', $order->info['comments'])); ?>
	</td>
     <td class="main" align="right"><?php echo  '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
//  }
?>
  <tr>
    <td class="main" colspan="3">
      <table border="0"  width="100%" cellspacing="0" cellpadding="2">
<?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>
        <tr>
          <td class="plainBoxHeading" colspan="2"><?php echo HEADING_PRODUCTS; ?>
          &nbsp;&nbsp;&nbsp;<?php echo '<a href="' . zen_href_link(FILENAME_SHOPPING_CART) . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></td>
          <td class="smallText" align="right"><?php echo HEADING_TAX; ?></td>
          <td class="smallText" align="right"><?php echo HEADING_TOTAL; ?></td>
        </tr>
<?php
  } else {
?>
        <tr>
          <td colspan="2" ><h4><?php echo HEADING_PRODUCTS; ?></h4></td>
          <td align="right"><?php echo '<a href="' . zen_href_link(FILENAME_SHOPPING_CART) . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></td>
        </tr>
<?php
  }

  foreach( array_keys( $order->contents ) as $opid ) {
    echo '        <tr>' . "\n" .
         '          <td class="main" align="right" valign="top" width="30">' . $order->contents[$opid]['products_quantity'] . '&nbsp;x</td>' . "\n" .
         '          <td class="main" valign="top"><a href="' . CommerceProduct::getDisplayUrl( $order->contents[$opid]['products_id'] ) . '">' . $order->contents[$opid]['name']. '</a>';

    if ( !empty( $order->contents[$opid]['attributes'] ) && (sizeof($order->contents[$opid]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->contents[$opid]['attributes']); $j<$n2; $j++) {
        echo '<div style="white-space:nowrap;">&nbsp;<em> - ' . $order->contents[$opid]['attributes'][$j]['option'] . ': ' . $order->contents[$opid]['attributes'][$j]['value'] . '</em></div>';
      }
    }

    echo '        </td>' . "\n";

    if ( !empty( $order->info['tax_groups'] ) && sizeof($order->info['tax_groups']) > 1) {
		echo '            <td class="main" valign="top" align="right">';
		if( !empty( $order->contents[$opid]['tax'] ) ) {
			echo zen_display_tax_value($order->contents[$opid]['tax']) . '%';
		}
		echo '</td>' . "\n";
	}

    echo '        <td class="main" align="right" valign="top">' .
                    $currencies->display_price($order->contents[$opid]['final_price'], $order->contents[$opid]['tax'], $order->contents[$opid]['products_quantity']) .
                    ($order->contents[$opid]['onetime_charges'] != 0 ? '<br /> ' . $currencies->display_price($order->contents[$opid]['onetime_charges'], $order->contents[$opid]['tax'], 1) : '') .
                  '</td>' . "\n" .
         '      </tr>' . "\n";
  }
?>
      </table>
    </td>
  </tr>
  <tr>
    <td class="main" align="right" colspan="3">
      <table border="0"  cellspacing="0" cellpadding="2">
<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    $order_totals = $order_total_modules->process();
    echo $order_total_modules->output();
  }
?>
      </table>
    </td>
  </tr>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
    if( $order->content_type != 'virtual' && $order->info['shipping_method']) {
?>
  <tr>
    <td class="main" colspan="2"><?php echo HEADING_SHIPPING_METHOD; ?>  <?php echo $order->info['shipping_method']; ?></td>
    <td class="main" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
<?php
    }
$class =& $_SESSION['payment'];
?>
  <tr>
    <td colspan="2"><h4><?php echo HEADING_PAYMENT_METHOD; ?></h4> </td>
    <td class="main" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_EDIT_SMALL, BUTTON_EDIT_SMALL_ALT) . '</a>'; ?></td>
  </tr>
  <tr>
    <td colspan="3">
<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
?>
      <table border="0"  cellspacing="0" cellpadding="2">
        <tr>
          <td class="main" colspan="3"><?php echo $confirmation['title']; ?></td>
        </tr>
<?php
      for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
?>
        <tr>
          <td class="smallText"><?php echo $confirmation['fields'][$i]['title']; ?></td>
          <td class="smallText"><?php echo $confirmation['fields'][$i]['field']; ?></td>
        </tr>
<?php
      }
?>
      </table>
<?php
    }
  } else {
  	print $GLOBALS[$class]->title;
  }
?>
    </td>
  </tr>
  <tr>
    <td class="main" colspan="3"><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
  </tr>
  <tr>
    <td class="main" colspan="2"><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></td>
    <td align="right" class="main">
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

  echo zen_image_submit(BUTTON_IMAGE_CONFIRM_ORDER, BUTTON_CONFIRM_ORDER_ALT) . '</form>' . "\n";
?>
    </td>
  </tr>
</table>

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
// $Id: tpl_checkout_payment_address.php,v 1.1 2005/08/04 07:01:26 spiderr Exp $
//
?>
<?php echo zen_draw_form('checkout_address', zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'), 'post', 'onsubmit="return check_form_optional(checkout_address);"'); ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
<tr>
	<td class="pageHeading" colspan="3"><h1><?php echo HEADING_TITLE; ?></h1></td>
</tr>
<tr>
	<td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
</tr>
<?php
if ($messageStack->size('checkout_address') > 0) {
?>
<tr>
	<td class="main" colspan="3"><?php echo $messageStack->output('checkout_address'); ?></td>
</tr>
<?php
}

	if( $gBitUser->isRegistered() ) {
		if( !empty( $_SESSION['billto'] ) ) {
?>
<tr>
	<td class="main" align="center" valign="top"><?php echo TITLE_PAYMENT_ADDRESS . '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ARROW_SOUTH_EAST); ?></td>
	<td class="main" width="35%" valign="top"><?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'); ?></td>
	<td class="main" valign="top"><?php echo TEXT_SELECTED_PAYMENT_DESTINATION; ?></td>
</tr>
<?php
		}
	} else {
		print $gBitSmarty->fetch( 'bitpackage:bitcommerce/register_customer.tpl' );
	}
	if ($addresses_count <= MAX_ADDRESS_BOOK_ENTRIES) {

?>
<tr>
	<td class="plainBox" colspan="3">
<fieldset>
		<?php require( BITCOMMERCE_PKG_PATH.'templates/address_new.php'); ?>
</fieldset>
	</td>
</tr>
<?php
	}
	if ($addresses_count > 1) {
?>
<tr>
	<td class="plainBoxHeading" colspan="3"><?php echo TABLE_HEADING_NEW_PAYMENT_ADDRESS; ?></td>
</tr>
<tr>
	<td class="main" valign="top" colspan="2"><?php echo TEXT_SELECT_OTHER_PAYMENT_DESTINATION; ?></td>
	<td class="main" valign="top" align="right"><?php echo TITLE_PLEASE_SELECT . '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ARROW_EAST_SOUTH); ?></td>
</tr>
<tr>
	<td class="main" colspan="3" ><?php echo zen_draw_separator(OTHER_IMAGE_SILVER_SEPARATOR, '100%', '1'); ?></td>
</tr>
<?php
		require(DIR_WS_BLOCKS . 'blk_checkout_payment_address.php');
	}

?>
<tr>
	<td class="main"><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
	<td class="main" colspan="2">
		<span style="float:left"><?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></span>
		<span style="float:right">
		<?php echo zen_draw_hidden_field('action', 'submit') . zen_image_submit(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT); ?>
		</span>
	</td>
</tr>
</table></form>

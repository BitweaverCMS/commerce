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
<header class="page-header">
	<h1><?php echo tra( 'Enter Your Billing Information' ); ?></h1>
</header>

<div class="row-fluid">
	<div class="span6">
<?php
	if ($addresses_count <= MAX_ADDRESS_BOOK_ENTRIES) {
?>
<fieldset>
	<legend><?=tra('Enter New Address')?></legend>
<?php
	if( !$gBitUser->isRegistered() ) {
		print $gBitSmarty->fetch( 'bitpackage:bitcommerce/register_customer.tpl' );
	}
?>
	<?php require( BITCOMMERCE_PKG_PATH.'pages/address_new/address_new.php'); ?>
</fieldset>
<?php
	}
?>
	</div>
	<div class="span6">

<fieldset>
	<legend><?=tra('Select Existing Address')?></legend>

<?php echo zen_draw_form('checkout_address', zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'), 'post', 'onsubmit="return check_form_optional(checkout_address);"'); ?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
<?php
if ($messageStack->size('checkout_address') > 0) {
?>
<tr>
	<td class="main" colspan="3"><?php echo $messageStack->output('checkout_address'); ?></td>
</tr>
<?php
}

	if ($addresses_count > 1) {
		require(DIR_FS_BLOCKS . 'blk_checkout_payment_address.php');
	}

?>
		</fieldset>
	</div>
</div>

<a class="btn" href="<?=zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL')?>"><?=tra('Back')?></a>
<?php echo zen_draw_hidden_field('action', 'submit') ?>
<input type="submit" class="btn btn-primary" name="Continue" value="<?=tra('Continue')?>">
</form>

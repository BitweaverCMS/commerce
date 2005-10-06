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
// $Id: checkout_shipping_address.php,v 1.1 2005/10/06 19:38:27 spiderr Exp $
//
	if ($messageStack->size('checkout_address') > 0) {
		$gBitSmarty->assign( 'errors', $messageStack->output('checkout_address') );
	}

	if ($process == false) {
//		require(DIR_FS_MODULES . 'checkout_new_address.php');

		if ($addresses_count > 1) {

			$addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
										entry_company as company, entry_street_address as street_address,
										entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
										entry_state as state, entry_zone_id as zone_id,
										entry_country_id as country_id
								from " . TABLE_ADDRESS_BOOK . "
								where `customers_id` = ?";

			if( $rs = $db->query( $addresses_query, array( $_SESSION['customer_id'] ) ) ) {
				$gBitSmarty->assign( 'addresses', $rs->GetRows() );
/*			while (!$rs->EOF) {
				$format_id = zen_get_address_format_id($addresses->fields['country_id']);
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
	if ($addresses->fields['address_book_id'] == $_SESSION['sendto']) {
		echo '                  <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
		} else {
		echo '                  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
		}
?>
<td colspan="2"><?php echo zen_output_string_protected($addresses->fields['firstname'] . ' ' . $addresses->fields['lastname']); ?></td>
<td align="right"><?php echo zen_draw_radio_field('address', $addresses->fields['address_book_id'], ($addresses->fields['address_book_id'] == $_SESSION['sendto'])); ?></td>
</tr>
<tr>
	<td><?php echo zen_address_format($format_id, $addresses->fields, true, ' ', ', '); ?></td>
</tr>
</table>
<?php
			$radio_buttons++;
			$rs->MoveNext();
		}
*/
			}
		}
	}
  print $gBitSmarty->fetch( 'bitpackage:bitcommerce/checkout_shipping.tpl' );

?>
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
// $Id: address_book.php,v 1.1 2005/10/06 19:38:26 spiderr Exp $
//
?>
<table  width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td class="pageHeading" colspan="2"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
<?php
  if ($messageStack->size('addressbook') > 0) {
?>
  <tr>
    <td colspan="2"><?php echo $messageStack->output('addressbook'); ?></td>
  </tr>
<?php
  }

	$defaultAddressId = $gBitCustomer->getDefaultAddress();
	if( !empty( $defaultAddressId ) ) {
?>
  <tr>
    <td class="plainBox" colspan="2">
      <table  width="100%" border="0" cellspacing="5" cellpadding="5">
        <tr>
          <td width="40%" valign="top"><?php echo PRIMARY_ADDRESS_DESCRIPTION; ?></td>
          <td class="main" align="center" valign="top">
            <strong><?php echo PRIMARY_ADDRESS_TITLE; ?></strong><br /><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . OTHER_IMAGE_ARROW_SOUTH_EAST); ?>
          </td>
          <td class="main">
            <?php echo zen_address_label($_SESSION['customer_id'], $defaultAddressId, true, ' ', '<br />'); ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
<?php
	}
?>
  <tr>
    <td class="plainBoxHeading" colspan="2"><?php echo ADDRESS_BOOK_TITLE; ?></td>
  </tr>
    <?php require(DIR_FS_BLOCKS . 'blk_address_book.php'); ?>
  <tr>
   <td class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></td>
<?php
  if (zen_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
?>
   <td class="smallText" align="right"><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL') . '">' . zen_image_button(BUTTON_IMAGE_ADD_ADDRESS, BUTTON_ADD_ADDRESS_ALT) . '</a>'; ?></td>
<?php
  }
?>
  </tr>
  <tr>
    <td class="smallText" colspan="2"><?php echo sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES); ?></td>
  </tr>
</table>

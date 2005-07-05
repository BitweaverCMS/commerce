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
// $Id: tpl_address_book_default.php,v 1.1 2005/07/05 05:59:28 bitweaver Exp $
//
?>
<h1><?php echo HEADING_TITLE; ?></h1>
<?php
  if ($messageStack->size('addressbook') > 0) {
?>
<?php echo $messageStack->output('addressbook'); ?> 
<?php
  }
?>
<?php echo PRIMARY_ADDRESS_DESCRIPTION; ?>
<b><?php echo PRIMARY_ADDRESS_TITLE; ?></b><br />
<?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_south_east.gif'); ?> <?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['customer_default_address_id'], true, ' ', '<br />'); ?> 
<?php echo ADDRESS_BOOK_TITLE; ?> 
<?php
  $addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname, 
                             entry_company as company, entry_street_address as street_address, 
                             entry_suburb as suburb, entry_city as city, entry_postcode as postcode, 
                             entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id 
                      from   " . TABLE_ADDRESS_BOOK . " 
                      where  customers_id = '" . (int)$_SESSION['customer_id'] . "' 
                      order by firstname, lastname";

  $addresses = $db->Execute($addresses_query);

  while (!$addresses->EOF) {
    $format_id = zen_get_address_format_id($addresses->fields['country_id']);
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href='<?php echo zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses->fields['address_book_id'], 'SSL'); ?>'"> 
    <td><b><?php echo zen_output_string_protected($addresses->fields['firstname'] . ' ' . $addresses->fields['lastname']); ?></b>
      <?php if ($addresses->fields['address_book_id'] == $_SESSION['customer_default_address_id']) echo '&nbsp;<small><i>' . PRIMARY_ADDRESS . '</i></small>'; ?>
    </td>
    <td align="right"><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'edit=' . $addresses->fields['address_book_id'], 'SSL') . '">' . zen_image_button('small_edit.gif', SMALL_IMAGE_BUTTON_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, 'delete=' . $addresses->fields['address_book_id'], 'SSL') . '">' . zen_image_button('small_delete.gif', SMALL_IMAGE_BUTTON_DELETE) . '</a>'; ?></td>
  </tr>
  <tr> 
    <td><?php echo zen_address_format($format_id, $addresses->fields, true, ' ', '<br />'); ?></td>
    <td>&nbsp;</td>
  </tr>
</table>
<?php
    $addresses->MoveNext();
  }
?>
<?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?> 
<?php
  if (zen_count_customer_address_book_entries() < MAX_ADDRESS_BOOK_ENTRIES) {
?>
<?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK_PROCESS, '', 'SSL') . '">' . zen_image_button('button_add_address.gif', IMAGE_BUTTON_ADD_ADDRESS) . '</a>'; ?> 
<?php
  }
?>
<?php echo sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES); ?> 
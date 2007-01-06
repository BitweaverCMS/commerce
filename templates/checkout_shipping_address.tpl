<?php echo zen_draw_form('checkout_address', zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), 'post', 'onsubmit="return check_form_optional(checkout_address);"'); ?>
<h1>{tr}Change the Shipping Address{/tr}></h1>
<?php
  if ($messageStack->size('checkout_address') > 0) {
?>
<?php echo $messageStack->output('checkout_address'); ?>
<?php
  }

  if ($process == false) {
?>
<?php echo TITLE_SHIPPING_ADDRESS . '<br />' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'arrow_south_east.gif'); ?></td>
<?php echo zen_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'); ?></td>
<?php echo TEXT_CREATE_NEW_SHIPPING_ADDRESS; ?>
<?php require(DIR_WS_MODULES . 'checkout_new_address.php'); ?>
<?php
    if ($addresses_count > 1) {
?>
<?php echo TABLE_HEADING_ADDRESS_BOOK_ENTRIES; ?> <?php echo TEXT_SELECT_OTHER_SHIPPING_DESTINATION; ?>
<?php
      $radio_buttons = 0;

      $addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
                                 entry_company as company, entry_street_address as street_address,
                                 entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                                 entry_state as state, entry_zone_id as zone_id,
                                 entry_country_id as country_id
                          from " . TABLE_ADDRESS_BOOK . "
                          where customers_id = '" . (int)$_SESSION['customer_id'] . "'";

      $addresses = $gBitDb->Execute($addresses_query);

      while (!$addresses->EOF) {
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
        $addresses->MoveNext();
      }
?>
<?php
    }
  }

  if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
?>
<?php
  }
?>
<?php echo TITLE_CONTINUE_CHECKOUT_PROCEDURE . '<br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?>
<?php echo zen_draw_hidden_field('action', 'submit') . zen_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?>
<?php
  if ($process == true) {
?>
<?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . zen_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?>
<?php
  }
?></form>

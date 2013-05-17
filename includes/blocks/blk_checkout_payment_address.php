<table class="table table-hover">
<?php
      $addresses_query = "select address_book_id, entry_firstname as firstname, entry_lastname as lastname,
                                 entry_company as company, entry_street_address as street_address,
                                 entry_suburb as suburb, entry_city as city, entry_postcode as postcode,
                                 entry_state as state, entry_zone_id as zone_id,
                                 entry_country_id as country_id
                          from " . TABLE_ADDRESS_BOOK . "
                          where `customers_id` = '" . (int)$_SESSION['customer_id'] . "'";

      $addresses = $gBitDb->Execute($addresses_query);

      while (!$addresses->EOF) {
        $format_id = zen_get_address_format_id($addresses->fields['country_id']);
?>
    <tr <?=($addresses->fields['address_book_id'] == $_SESSION['sendto'])?'class="success"':''?>>
        <td >
			<label class="radio">
        		<input type="radio" name="address" value="<?=$addresses->fields['address_book_id']?>" <?=($addresses->fields['address_book_id'] == $_SESSION['sendto'])?'checked="checked"':''?> />
				<a href="<?=BITCOMMERCE_PKG_URL?>?main_page=address_book_process&edit=<?=$addresses->fields['address_book_id']?>"><i class="icon-pencil"></i></a>
        <?php echo zen_address_format($format_id, $addresses->fields, true, ' ', ', '); ?>
			</label>
		</td>
	</tr>

<?
        $addresses->MoveNext();
      }
?>
</table>

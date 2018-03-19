{if count( $addresses )}
	{include file="bitpackage:bitcommerce/checkout_javascript.tpl"}
	{assign var=checked value=$addresses[0].address_book_id}
	<div class="bc-address-list">
	{section name=ix loop=$addresses}
		<div class="radio col-md-12 mt-0 pt-1 ph-1">
		{if $sendToAddressId && $addresses[ix].address_book_id == $sendToAddressId}
			{assign var=checked value=$addresses[ix].address_book_id}
			{assign var=class value="row selected"}
		{else}
			{assign var=class value="row"}
		{/if}
			<label>
				<input type="radio" name="address" value="{$addresses[ix].address_book_id}" {if $checked}checked=checked{/if}/>
				{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$addresses[ix]}
			</label>
		</div>
	{/section}
	</div>
{/if}

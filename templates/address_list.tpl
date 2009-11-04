{if count( $addresses )}
	{include file="bitpackage:bitcommerce/checkout_javascript.tpl"}
	{section name=ix loop=$addresses}
		{if $addresses[ix].address_book_id == $sendToAddressId}
			{assign var=checked value=$addresses[ix].address_book_id}
			{assign var=class value="row selected"}
		{else}
			{assign var=class value="row"}
		{/if}
		<div class="{$class}">
			{html_radios name='address' values=$addresses[ix].address_book_id checked=$checked}
					{assign var=address value=$addresses[ix]}
					{include file="bitpackage:bitcommerce/address_display.tpl"}
		</div>
	{/section}
{/if}

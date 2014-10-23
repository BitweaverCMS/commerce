{if empty($addressLinebreak)}
	{assign var=addressLinebreak value='<br/>'}
{/if}
<div class="bitcommerce-address">
	<h4 class="no-margin">{$address.firstname} {$address.lastname}{if $address.company}, {$address.company}{/if}</h4>
	{$address.street_address}
	{if $address.suburb}
		{$addressLinebreak} {$address.suburb}
	{/if}
	{$addressLinebreak}
	{if $address.address_format_id==1}
		{$address.city} , {$address.postcode}{$addressLinebreak}{$address.state},
	{elseif $address.address_format_id==3}
		{$address.city}{$addressLinebreak}{$address.postcode} - {$address.state},
	{elseif $address.address_format_id==4}
		{$address.city} ({$address.postcode}){$addressLinebreak}
	{elseif $address.address_format_id==5}
		{$address.postcode} {$address.city}{$addressLinebreak}
	{else}
		{$address.city}, {$address.state}    {$address.postcode}{$addressLinebreak}
	{/if}
	{$address.countries_name|default:$address.country.countries_name} {$address.telephone}
</div>

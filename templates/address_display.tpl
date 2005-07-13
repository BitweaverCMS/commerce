<div>
{$address.firstname} {$address.lastname}<br/>
{if $address.company}
	{$address.company}<br/>
{/if}
{$address.street_address}<br/>
{if $address.suburb}
	{$address.suburb}<br/>
{/if}
{$address.city}, {$address.state} {$address.postcode}<br/>
{$address.countries_iso_code_3}
</div>
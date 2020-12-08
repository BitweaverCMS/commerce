{strip}
{if $sideboxManufacturerInfo}
	{bitmodule title=$moduleTitle name="bc_manufaturerinfo"}
		{if $sideboxManufacturerInfo.manufacturers_image_url}
			<img src="{$sideboxManufacturerInfo.manufacturers_image_url}" alt="{$sideboxManufacturerInfo.manufacturers_name|escape:html}" />
			<br />
		{/if}

		{if $sideboxManufacturerInfo.manufacturers_url}
			<a href="{'redirect'|zen_get_page_url:"action=manufacturer&manufacturers_id=`$sideboxManufacturerInfo.manufacturers_url`"}" target="_blank">{$sideboxManufacturerInfo.manufacturers_name}</a>
			<br />
		{/if}
	{/bitmodule}
{/if}
{/strip}

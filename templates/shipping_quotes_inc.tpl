
{section name=ix loop=$quotes}
	{if $quotes[ix].methods || $quotes[ix].error}
		{counter assign=radioButtons start=0}
		<div class="row">
			<div class="formlabel">{if $quotes[ix].icon}{biticon ipackage="bitcommerce" iname=$quotes[ix].icon iexplain=$quotes[ix].title}{else}{$quotes[ix].module for=""}{/if}</div>
			{forminput}
		{formfeedback error=$quotes[ix].error}
				{if $quotes[ix].methods}
					{section name=jx loop=$quotes[ix].methods}
						{* set the radio button to be checked if it is the method chosen *}
						{if ("$quotes[ix].id`_`$quotes[ix].methods[jx].id`" == $sessionShippingId)}
							{assign var=checked value=1}
						{else}
							{assign var=checked value=0}
						{/if}

						{if $smarty.section.ix.total > 1 || $smarty.section.jx.total > 1 }
							<input type="radio" name="shipping" value="{$quotes[ix].id}_{$quotes[ix].methods[jx].id}" {if $checked}checked="checked"{/if}/>{$quotes[ix].methods[jx].name}
						{else}
							{$quotes[ix].methods[jx].format_add_tax} <input type="hidden" name="shipping" value="{"`$quotes[ix].id`_`$quotes[ix].methods[jx].id`"}" />
						{/if}
						{$quotes[ix].methods[jx].title} {$quotes[ix].methods[jx].format_add_tax}
						{if $quotes[ix].methods[jx].note}
							{formhelp note=$quotes[ix].methods[jx].note}
						{/if}
						<br/>
					{/section}
				{/if}
				{if $quotes[ix].note}
					<p class="note">{$quotes[ix].note}</p>
				{/if}
			{/forminput}
		</div>
	{/if}
{/section}

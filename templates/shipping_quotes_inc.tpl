<div id="shippingquotes">
<ul class="unstyled">
{section name=ix loop=$quotes}
	{if $quotes[ix].methods || $quotes[ix].error}
		{counter assign=radioButtons start=0}
		<li class="row">
			<div class="col-md-2 text-right">
				{if $quotes[ix].icon}{biticon ipackage="bitcommerce" iname=$quotes[ix].icon iexplain=$quotes[ix].title}{/if}
			</div>
			<div class="col-md-10">
				{if !$quotes[ix].icon}}<h4>{$quotes[ix].module}</h4>{/if}
				{forminput}
					{formfeedback error=$quotes[ix].error}
					{if $quotes[ix].methods}
						{if $quotes[ix].weight}
							<dt>{$quotes[ix].weight}</dt>
						{/if}
						{section name=jx loop=$quotes[ix].methods}
							<dd>
							{* set the radio button to be checked if it is the method chosen *}
							{if ("$quotes[ix].id`_`$quotes[ix].methods[jx].id`" == $sessionShippingId)}
								{assign var=checked value=1}
							{else}
								{assign var=checked value=0}
							{/if}

							<label class="radio">
							{if empty($noradio) && ($smarty.section.ix.total > 1 || $smarty.section.jx.total > 1)}
								<input type="radio" name="shipping" value="{$quotes[ix].id}_{$quotes[ix].methods[jx].id}" {if $checked}checked="checked"{/if}/> {$quotes[ix].methods[jx].name}
							{else}
								<input type="hidden" name="shipping" value="{"`$quotes[ix].id`_`$quotes[ix].methods[jx].id`"}" />
							{/if}
							<div class="price floatright">{$quotes[ix].methods[jx].format_add_tax}</div>
							{$quotes[ix].methods[jx].title} 
							</label>
							{if $quotes[ix].methods[jx].note}
								{formhelp note=$quotes[ix].methods[jx].note}
							{/if}
							</dd>
						{/section}
					{/if}
					{if $quotes[ix].note}
						<p class="note">{$quotes[ix].note}</p>
					{/if}
				{/forminput}
			</div>
		</li>
	{/if}
{/section}
</ul>
</div>

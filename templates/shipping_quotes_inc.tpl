<div id="shippingquotes">
<ul class="list-unstyled text-left">
{* perhaps embedded somewhere like shipping quote *}
{if $liCss==''}{assign var=liCss value="col-md-6"}{/if}
{section name=ix loop=$quotes}
	{if $quotes[ix].methods || $quotes[ix].error}
		{counter assign=radioButtons start=0}
		<li class="">
			<div class="pull-right text-right">
				{if $quotes[ix].weight}
					<div class="date">{$quotes[ix].weight}</div>
				{/if}
				{if $quotes[ix].origin}
				<div class="">
					{assign var=shipDate value=$quotes[ix].origin.ship_date|strtotime}
					<div class="help-block">{tr}Ships from{/tr} {$quotes[ix].origin.countries_name} {*if $quotes[ix].origin.ship_date}{tr}on{/tr} {$shipDate|bit_long_date}{/if*}</div>
				</div>
				{/if}
				{if $quotes[ix].note}
					<p class="help-block">{$quotes[ix].note}</p>
				{/if}
			</div>
			<h3>{$quotes[ix].module}</h3>
			<div class="">
				{formfeedback error=$quotes[ix].error}
			</div>
			{if $quotes[ix].methods}
				{section name=jx loop=$quotes[ix].methods}
					<div class="row quote">
						<div class="col-xs-9">
							{assign var=quoteMethodId value="`$quotes[ix].id`_`$quotes[ix].methods[jx].id`"}
							{* set the radio button to be checked if it is the method chosen *}
							{if $quoteMethodId == $sessionShippingId}
								{assign var=checked value=1}
							{else}
								{assign var=checked value=0}
							{/if}

								<div class="radio mt-0">
									<label>
									{if $quotes[ix].methods[jx].transit_time}
										<span class="inline-block pull-right">({$quotes[ix].methods[jx].transit_time})</span>
									{/if}
									{if empty($noradio) && ($smarty.section.ix.total > 1 || $smarty.section.jx.total > 1)}
										<input type="radio" name="shipping_quote" value="{$quoteMethodId}" {if $checked}checked="checked"{/if}/>
									{else}
										<input type="hidden" name="shipping_quote" value="{$quoteMethodId}" />
									{/if}
									{if $quotes[ix].booticon}
										{booticon iname=$quotes[ix].booticon class="shipper-icon"}
									{else}
										{biticon ipackage="bitcommerce" iname=$quotes[ix].icon iexplain=$quotes[ix].module class="img-responsive shipper-logo"}
									{/if}
										{$quotes[ix].methods[jx].title} 
									</label>
								</div>
								{if $quotes[ix].methods[jx].note}
									{formhelp note=$quotes[ix].methods[jx].note}
								{/if}
						</div>
						<div class="col-xs-3">
							<div class="price floatright">{$quotes[ix].methods[jx].format_add_tax}</div>
						</div>
					</div>
					<div class="mt-0">
						<div class="help-block">
						{if $quotes[ix].methods[jx].delivery_date}
							{assign var=deliveryDate value=$quotes[ix].methods[jx].delivery_date|strtotime}
							{tr}Estimated Arrival{/tr} <strong>{$deliveryDate|bit_long_date}</strong>
						{else}
							<div>{tr}Exact delivery day cannot be estimated. Do not use if you are on a tight deadline.{/tr}</div>
						{/if}
						</div>
					</div>
				{/section}
			{/if}
		</li>
	{/if}
{/section}
</ul>
</div>

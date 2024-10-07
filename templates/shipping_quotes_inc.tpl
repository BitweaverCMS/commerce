<div id="shippingquotes">
<ul class="list-unstyled text-left row">
{* perhaps embedded somewhere like shipping quote *}
{if $liCss==''}{assign var=liCss value="col-md-4"}{/if}
{section name=ix loop=$quotes}
	{if $quotes[ix].methods || $quotes[ix].error}
		{counter assign=radioButtons start=0}
		<li class="{$liCss}">
			<div class="ph-1">
				<div class="row">
					<div class="col-xs-8">
						{if $quotes[ix].booticon}
						{booticon iname=$quotes[ix].booticon class="shipper-icon"}
						{else}
						{biticon ipackage="bitcommerce" iname=$quotes[ix].icon iexplain=$quotes[ix].module class="img-responsive shipper-logo"}
						{/if}
						{if $quotes[ix].note}
							<p class="help-block">{$quotes[ix].note}</p>
						{/if}
						{formfeedback error=$quotes[ix].error}
					</div>
					<div class="col-xs-4 text-right date">
						{if $quotes[ix].weight}
						<div class="date">{$quotes[ix].weight}</div>
						{/if}
					</div>
				</div>
				{if $quotes[ix].origin}
				<div class="row">
					<div class="col-xs-12">
						{assign var=shipDate value=$quotes[ix].origin.ship_date|strtotime}
						<div class="help-block">{$quotes[ix].module} {tr}Ships from{/tr} {$quotes[ix].origin.countries_name} {*if $quotes[ix].origin.ship_date}{tr}on{/tr} {$shipDate|bit_long_date}{/if*}</div>
					</div>
				</div>
				{/if}
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

								{if empty($noradio) && ($smarty.section.ix.total > 1 || $smarty.section.jx.total > 1)}
									<div class="radio mt-0">
										<label>
											<input type="radio" name="shipping_method" value="{$quoteMethodId}" {if $checked}checked="checked"{/if}/> {$quotes[ix].methods[jx].name} {$quotes[ix].methods[jx].title} 
										</label>
									</div>
								{else}
									<input type="hidden" name="shipping_method" value="{$quoteMethodId}" /> {$quotes[ix].methods[jx].title} 
								{/if}
									{if $quotes[ix].methods[jx].note}
										{formhelp note=$quotes[ix].methods[jx].note}
									{/if}
							</div>
							<div class="col-xs-3">
								<div class="price floatright">{$quotes[ix].methods[jx].format_add_tax}</div>
							</div>
						</div>
						<div class="radio mt-0">
							<label>
								<div class="help-block">
								{if $quotes[ix].methods[jx].delivery_date}
									{assign var=deliveryDate value=$quotes[ix].methods[jx].delivery_date|strtotime}
									<div>{tr}Estimated Arrival{/tr}<br><strong>{$deliveryDate|bit_long_date}</strong></div>
								{else}<div>{tr}Exact delivery day cannot be estimated. Do not use if you are on a tight deadline.{/tr}</div>
								{/if}
								{if $quotes[ix].methods[jx].transit_time}
									({$quotes[ix].methods[jx].transit_time})
								{/if}
								</div>
							</label>
						</div>
					{/section}
				{/if}
			</div>
		</li>
	{/if}
{/section}
</ul>
</div>

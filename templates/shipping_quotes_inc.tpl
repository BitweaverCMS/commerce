<div id="shippingquotes">
<ul class="list-unstyled text-left">
{section name=ix loop=$quotes}
	{if $quotes[ix].methods || $quotes[ix].error}
		{counter assign=radioButtons start=0}
		<li>
			<div class="row">
				<div class="col-xs-3">
					{if $quotes[ix].icon}
						<div>{biticon ipackage="bitcommerce" iname=$quotes[ix].icon iexplain=$quotes[ix].title class="img-responsive"}</div>
					{else}
						<h4>{$quotes[ix].module}</h4>
					{/if}
					{if $quotes[ix].weight}
						<p>{$quotes[ix].weight}</p>
					{/if}
				</div>
				<div class="col-xs-9">
					{formfeedback error=$quotes[ix].error}
					{if $quotes[ix].methods}
						<table class="table table-hover text-left">
						{section name=jx loop=$quotes[ix].methods}
								<tr class="row">
									<td class="col-xs-9">
										{* set the radio button to be checked if it is the method chosen *}
										{if ("`$quotes[ix].id`_`$quotes[ix].methods[jx].id`" == $sessionShippingId.id)}
											{assign var=checked value=1}
										{else}
											{assign var=checked value=0}
										{/if}

										{if empty($noradio) && ($smarty.section.ix.total > 1 || $smarty.section.jx.total > 1)}
											<div class="radio">
												<label>
													<input type="radio" name="shipping" value="{$quotes[ix].id}_{$quotes[ix].methods[jx].id}" {if $checked}checked="checked"{/if}/> {$quotes[ix].methods[jx].name} {$quotes[ix].methods[jx].title} 
												</label>
											</div>
										{else}
											<input type="hidden" name="shipping" value="{"`$quotes[ix].id`_`$quotes[ix].methods[jx].id`"}" /> {$quotes[ix].methods[jx].title} 
										{/if}
											{if $quotes[ix].methods[jx].note}
												{formhelp note=$quotes[ix].methods[jx].note}
											{/if}
									</td>
									<td class="col-xs-3">
										<div class="price floatright">{$quotes[ix].methods[jx].format_add_tax}</div>
									</td>
								</tr>
						{/section}
						</table>
					{/if}
					{if $quotes[ix].note}
						<p class="note">{$quotes[ix].note}</p>
					{/if}
				</div>
			</div>
		</li>
	{/if}
{/section}
</ul>
</div>

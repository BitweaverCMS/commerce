<h2>{tr}Product{/tr} {$gBitProduct->getField('products_id')}: {$gBitProduct->getTitle()|escape:html}</h2>
	<ul class="data">
	{foreach from=$optionsList key=optionId item=option}
		<li class="item">
			
			<strong>{$option.products_options_name}</strong> (ID {$optionId}, {$option.products_options_types_name}) 
			<ul class="data">
			{if $option.values}
					{foreach from=$option.values key=optionValueId item=optionValue}
					<li class="item {cycle values="odd,even"}">
<input type="checkbox" name="products_options[]" value="{$optionValueId}" {if $gBitProduct->hasOptionValue($optionValueId)}checked="checked"{/if} />
					<em><strong>{$optionValue.products_options_sort_order|default:'-'}</strong></em>			
						{$optionValue.products_options_values_name} {if $option.attributes_default}<em>{tr}Default{/tr}</em>{/if}
					
					{if $optionValue.attribute_is_free}
						<strong class="warning">{tr}FREE{/tr}</strong>
					{else}
						
						{if $optionValue.options_values_price}
							{$optionValue.price_prefix}${$optionValue.options_values_price}, 
						{/if}
						{if $optionValue.attributes_price_onetime}
							{$optionValue.price_prefix}${$optionValue.options_values_price} , 
						{/if}
						{if $optionValue.attributes_price_factor}
							{$optionValue.price_prefix}{$optionValue.attributes_price_factor*100}%, 
						{/if}
					{/if}
		
					{if $optionValue.products_attributes_wt}
						{$optionValue.products_attributes_wt_pfix}{$optionValue.products_attributes_wt} lbs., 
					{/if}
		
					{if !$optionValue.attributes_discounted}
						<em>{tr}Will NOT Discount{/tr}</em>
					{/if}
		
					{if $optionValue.attributes_required}
						<em class="warning">{tr}Required{/tr}</em>
					{/if}
		
					</li>
					{/foreach}
			{/if}
			</ul>
	
	<div class="row submit">
			<input type="hidden" name="products_id" value="{$gBitProduct->getField('products_id')}">
			<input type="submit" name="save_attribute_map" value="{tr}Save{/tr}">
			<input type="submit" name="cancel" value="{tr}Cancel{/tr}">
	</div>

		</li>
	{foreachelse}
		<li class="item">{tr}No Product Options.{/tr}</li>
	{/foreach}

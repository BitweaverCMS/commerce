{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}

{include_php file="`$smarty.const.BITCOMMERCE_PKG_ADMIN_PATH`includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="page-header">
		<h1>{tr}Product Options{/tr}</h1>
	</div>
	<div class="body">

{if $editTpl}

<div id="newattrform" {$formStyle}>
{form method="post" enctype="multipart/form-data"}
	{include file=$editTpl}
{/form}
</div>

{else}
<div id="attrlist" {$listStyle} >

<a href="{$smarty.server.SCRIPT_NAME}?products_options_id=new" class="btn btn-default btn-xs">{tr}New Option{/tr}</a> <a href="{$smarty.server.SCRIPT_NAME}?products_options_values_id=new" class="btn btn-default btn-xs">{tr}New Option Value{/tr}</a>


	<ul class="data">
	{foreach from=$optionsList key=optionId item=option}
		<li class="item">
			
			<div class="floaticon">
				<a href="{$smarty.server.SCRIPT_NAME}?products_options_id={$optionId}&amp;action=edit">{booticon iname="fa-pen-to-square" iexplain="Edit Option"}</a>
				<a href="{$smarty.server.SCRIPT_NAME}?products_options_id={$optionId}&amp;delete_attribute=1">{booticon iname="fa-trash" iexplain="Delete Option Attribute"}</a>
			</div>
			<strong>{$option.products_options_name}</strong> (ID {$optionId}, {$option.products_options_types_name}) 
			<ul class="data">
			{if $option.values}
					{foreach from=$option.values key=optionValueId item=optionValue}
					<li class="item {cycle values="odd,even"}">
						<div class="floaticon">
							<span class="small">#{$optionValue.products_options_values_id}</span>
							<a href="{$smarty.server.SCRIPT_NAME}?products_options_values_id={$optionValue.products_options_values_id}&amp;action=edit">{booticon iname="fa-pen-to-square" iexplain="Edit Option Value"}</a>
							<a href="{$smarty.server.SCRIPT_NAME}?products_options_values_id={$optionValue.products_options_values_id}&amp;delete_attribute=1">{booticon iname="fa-trash" iexplain="Delete Option Attribute"}</a>
						</div>
						<span class="badge">{$optionValue.products_options_sort_order|default:'-'}</span> {$optionValue.products_options_values_name} {if $option.attributes_default}<em>{tr}Default{/tr}</em>{/if}
					
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
						{$optionValue.products_attributes_wt} lbs., 
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
	
		</li>
	{foreachelse}
		<li class="item">{tr}No Product Options.{/tr}</li>
	{/foreach}
	</ul>
</div>
{/if}

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

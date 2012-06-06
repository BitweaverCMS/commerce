{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}

{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}Product Options{/tr}</h1>
	</div>
	<div class="body">

{if $editTpl}

<div id="newattrform" {$formStyle}>
{form name="option" method="post"}
	{include file=$editTpl}
{/form}
</div>

{else}
<div id="attrlist" {$listStyle} >

<a href="{$smarty.server.SCRIPT_NAME}?products_options_id=new" class="minibutton">{tr}New Option{/tr}</a> <a href="{$smarty.server.SCRIPT_NAME}?products_options_values_id=new" class="minibutton">{tr}New Option Value{/tr}</a>


	<ul class="data">
	{foreach from=$optionsList key=optionId item=option}
		<li class="item">
			
			<div class="floaticon">
				<a href="{$smarty.server.SCRIPT_NAME}?products_options_id={$optionId}&amp;action=edit">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit Option" iforce="icon"}</a>
				<a href="{$smarty.server.SCRIPT_NAME}?products_options_id={$optionId}&amp;action=delete">{biticon ipackage="icons" iname="edit-delete" iexplain="Delete Option Attribute" iforce="icon"}</a>
			</div>
			<strong>{$option.products_options_name}</strong> (ID {$optionId}, {$option.products_options_types_name}) 
			<ul class="data">
			{if $option.values}
					{foreach from=$option.values key=optionValueId item=optionValue}
					<li class="item {cycle values="odd,even"}">
						<div class="floaticon">
							<a href="{$smarty.server.SCRIPT_NAME}?products_options_values_id={$optionValue.products_options_values_id}&amp;action=edit">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit Option Value" iforce="icon"}</a>
							<a href="{$smarty.server.SCRIPT_NAME}?products_options_values_id={$optionValue.products_options_values_id}&amp;action=delete">{biticon ipackage="icons" iname="edit-delete" iexplain="Delete Option Attribute" iforce="icon"}</a>
						</div>
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
	
		</li>
	{foreachelse}
		<li class="item">{tr}No Product Options.{/tr}</li>
	{/foreach}
	</ul>
</div>
{/if}

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

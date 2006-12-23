{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}Product Options{/tr}</h1>
	</div>
	<div class="body">

{jstabs}

{jstab title="Attribute Sets"}

{if $editAttribute}
	{assign var=listStyle value='style="display:none"'}
	{assign var=formStyle value=''}
{else}
	{assign var=listStyle value=''}
	{assign var=formStyle value='style="display:none"'}
{/if}

<div id="newattrform" {$formStyle}>
	<a onclick="flip('attrlist');flip('newattrform');return 0;">&laquo; {tr}List Product Options{/tr}</a>
	{include file="bitpackage:bitcommerce/admin_products_options_edit_inc.tpl"}
</div>
<div id="attrlist" {$listStyle} >
	<a onclick="flip('attrlist');flip('newattrform');return 0;">{tr}New Attribute{/tr} &raquo;</a>
	<ul class="data">
	{foreach from=$attributesList key=attrId item=attr}
		<li class="item">
			<div class="floaticon">
				<a href="{$smarty.server.PHP_SELF}?attributes_id={$attrId}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="Edit Attribute" iforce="icon"}</a>
				<a href="{$smarty.server.PHP_SELF}?attributes_id={$attrId}&amp;delete_attribute=1">{biticon ipackage="icons" iname="edit-delete" iexplain="Delete Attribute" iforce="icon"}</a>
			</div>
			<h2>{$attr.products_options_name} &raquo; {$attr.products_options_values_name} {if $attr.attributes_default}<em>{tr}Default{/tr}</em>{/if} ( #{$attr.products_attributes_id} ) </h2>
			<em><strong>{$attr.products_attributes_sort_order}</strong></em>
			{if $attr.attribute_is_free}
				<strong class="warning">{tr}FREE{/tr}</strong>
			{else}
				
				{if $attr.options_values_price}
					{$attr.price_prefix}${$attr.options_values_price}, 
				{/if}
				{if $attr.attributes_price_onetime}
					{$attr.price_prefix}${$attr.options_values_price} , 
				{/if}
				{if $attr.attributes_price_factor}
					{$attr.price_prefix}{$attr.attributes_price_factor*100}%, 
				{/if}
			{/if}

			{if $attr.products_attributes_wt}
				{$attr.products_attributes_wt_pfix}{$attr.products_attributes_wt} lbs., 
			{/if}

			{if !$attr.attributes_discounted}
				<em>{tr}Will NOT Discount{/tr}</em>
			{/if}

			{if $attr.attributes_required}
				<em class="warning">{tr}Required{/tr}</em>
			{/if}

		</li>
	{foreachelse}
		<li class="item">{tr}No Product Options.{/tr}</li>
	{/foreach}
	</ul>
</div>

{/jstab}

{/jstabs}

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

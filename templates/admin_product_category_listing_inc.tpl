<div class="">
{*form name="goto" method="get" action="`$smarty.server.SCRIPT_NAME`" class="form-inline inline-block" role="search"}
	{$catMenu}
{/form*}
	<div class="inline-block">
	{$currentCategoryId|zen_output_generated_category_path}
	</div>

	<div class="pull-right">

{form name="search-cat" method="get" action="`$smarty.server.SCRIPT_NAME`" class="form-inline"}
	<div class="input-group">
		<input type="search" class="form-control" placeholder="Search" name="search" value="{$smarty.get.search|escape}" id="cat-search-term">
		<div class="input-group-btn">
			<button class="btn btn-default" type="submit"><i class="icon-search"></i></button>
		</div>
	</div>
{/form}

	</div>
</div>

<table class="table table-hover">
{if $catList}
<tr>
	<th class="text-right">{tr}ID{/tr}</th>
	<th>{tr}Categories{/tr}</th>
	<th class="text-right">{tr}Special{/tr}/{tr}Sale{/tr}</th>
	<th class="text-right">{tr}Quantity{/tr}</th>
	<th class="text-center">{tr}Status{/tr}</th>
	<th class="text-right">{tr}Sort Order{/tr}</th>
	<th class="text-right">
	</th>
</tr>
{foreach from=$catList key=catId item=category}
{assign var=catPath value=$catId|zen_get_path}
{assign var=catLink value=$smarty.const.FILENAME_CATEGORIES|zen_href_link_admin:$catPath}
<tr>
	<td class="text-right">{$catId}</td>
	<td><a href="{$catLink}">{booticon iname="icon-folder-close"} {$category.categories_name}</a></td>
	<td class="text-right">{0|zen_get_products_sale_discount:$catId:TRUE}</td>
	<td class="text-right">
										{$category.total_products_on} {tr}of{/tr} {$category.total_products} {tr}active{/tr}
</td>
	<td class="text-center">
	{if $category.categories_status == '1'}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?action=setflag_categories&amp;flag=0&amp;cID={$catId}&amp;cPath={$smarty.request.cPath}&amp;page={$smarty.request.page}">{booticon iname="icon-ok-sign"}</a>
	{else}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?action=setflag_categories&amp;flag=1&amp;cID={$catId}&amp;cPath={$smarty.request.cPath}&amp;page={$smarty.request.page}">{booticon iname="icon-minus-sign"}</a>
	{/if}
	</td>
	<td class="text-right">{$category.sort_order}</td>
	<td class="text-right">
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?cPath={$smarty.request.cPath}&amp;cID={$catId}&amp;action=edit_category">{booticon iname="icon-edit"}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?cPath={$smarty.request.cPath}&amp;cID={$catId}&amp;action=delete_category">{booticon iname="icon-trash"}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?cPath={$smarty.request.cPath}&amp;cID={$catId}&amp;action=move_category">{booticon iname="icon-mail-forward"}</a>
	</td>
</tr>
{/foreach}
{/if}
</table>
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#newCatModal">{if $smarty.request.cPath}{tr}New Subcategory{/tr}{else}{tr}New Category{/tr}{/if}</button>

{include file="bitpackage:bitcommerce/commerce_pagination.tpl" listInfo=$catListHash}


{if $smarty.request.cPath}
<table class="table table-hover">
{if $prodList}
<tr>
	<th></th>
	<th class="text-right">{tr}ID{/tr}</th>
	<th>{tr}Products{/tr}</th>
	<th class="text-left">{tr}Model{/tr}</th>
	<th class="text-right">{tr}Price{/tr}/{tr}Special{/tr}/{tr}Sale{/tr}</th>
	<th class="text-right">&nbsp;</th>
	<th class="text-right">{tr}Quantity{/tr}</th>
	<th class="text-center">{tr}Status{/tr}</th>
	<th class="text-right">{tr}Sort{/tr}</th>
	<th class="text-center">{tr}Action{/tr}
	</th>
</tr>
{foreach from=$prodList key=prodId item=product}
<tr {if $product.products_status == '1'}class="success"{/if}>
	<td>
<img src="{$product.products_image_url}" class="img-responsive thumbnail" style="max-height:75px"/>
</td>
	<td class="text-right">{$product.products_id}</td>
	<td><a href="{$product.display_url}">{$product.title}</a></td>
	<td>{$product.products_model}</td>
	<td colspan="2" class="text-right">{$product.display_price}</td>
	<td class="text-right">{$product.products_quantity}</td>
	<td class="text-center">
	{if $product.products_status == '1'}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?action=setflag&amp;flag=0&amp;pID={$prodId}&amp;cPath={$smarty.request.cPath}&amp;page={$smarty.request.page}">{booticon iname="icon-ok-sign"}</a>
	{else}
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?action=setflag&amp;flag=1&amp;pID={$prodId}&amp;cPath={$smarty.request.cPath}&amp;page={$smarty.request.page}">{booticon iname="icon-minus-sign"}</a>
	{/if}
	</td>
	<td class="text-right">{$product.products_sort_order}</td>
	<td class="text-right">
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/{$product.type_handler}.php?cPath={$smarty.request.cPath}&amp;product_type={$product.products_type}&amp;pID={$prodId}&amp;action=new_product&amp;action=copy_to&amp;page={$smarty.request.page}">{booticon iname="icon-copy"}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/products_options.php?products_id={$prodId}">{if $prodId|zen_has_product_attributes:'false'}{booticon iname="icon-check-sign"}{else}{booticon iname="icon-check"}{/if}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/products_price_manager.php?products_id={$prodId}">{booticon iname="icon-money"}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/{$product.type_handler}.php?page={$smarty.get.page}&amp;product_type={$product.products_type}&amp;cPath={$smarty.request.cPath}&amp;pID={$product.products_id}&amp;action=new_product_meta_tags">{if zen_get_metatags_keywords($prodId,$smarty.session.languages_id) || zen_get_metatags_description($prodId,$smarty.session.languages_id)}{booticon iname="icon-info"}{else}{booticon iname="icon-info-sign"}{/if}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/{$product.type_handler}.php?cPath={$smarty.request.cPath}&amp;product_type={$product.products_type}&amp;pID={$prodId}&amp;action=new_product&amp;page={$smarty.request.page}">{booticon iname="icon-edit"}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/{$product.type_handler}.php?cPath={$smarty.request.cPath}&amp;product_type={$product.products_type}&amp;pID={$prodId}&amp;action=delete_product&amp;page={$smarty.request.page}">{booticon iname="icon-trash"}</a>
		<a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/{$product.type_handler}.php?cPath={$smarty.request.cPath}&amp;product_type={$product.products_type}&amp;pID={$prodId}&amp;action=move_product&amp;page={$smarty.request.page}">{booticon iname="icon-mail-forward"}</a>
	</td>
</tr>
{/foreach}
{/if}
<tr>
	<th></th>
	<th colspan="4">
		<form name="newproduct" class="form-inline" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php}" method="GET">
			<input type="hidden" name="cPath" value="{$smarty.request.cPath}">
			<input type="hidden" name="action" value="new_product">
			<a class="btn btn-xs btn-primary" href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?cPath={$smarty.request.cPath}&amp;action=new_category">{tr}New Product{/tr}</a>
			{html_options name="product_type" options=$newProductTypes}
		</form>
	</th>
	<th></th>
	<th class="text-right">{$prodCount} {"Product"|plural:$prodCount}</th>
	<th colspan="3"></th>
</tr>
</table>
{include file="bitpackage:bitcommerce/commerce_pagination.tpl" listInfo=$prodListHash}
{/if}


{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}

{strip}
<div class="admin bitcommerce coupons">
	<header>
		<h1>{tr}Product Categories{/tr}</h1>
	</header>
	<div class="body">

	</div>
<!-- Modal -->
{if $smarty.request.cPath}{assign var=dialogTitle value="New Subcategory"}{else}{assign var=dialogTitle value="New Category"}{/if}
<div class="modal fade" id="newCatModal" tabindex="-1" role="dialog" aria-labelledby="newCatModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="newCatModalLabel">{tr}{$dialogTitle}{/tr}</h4>
      </div>
      <div class="modal-body">

<form class="form-horizontal" name="newcategory" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/categories.php?cPath={$smarty.request.cPath}&amp;cID={$catId}&amp;action=insert_category" method="post" enctype="multipart/form-data">
	{foreach from=$languages item=lang}

	<fieldset>
		<legend><img src="{$smarty.const.BITCOMMERCE_PKG_URL}/includes/languages/{$lang.code}/images/icon.gif"/> {tr}{$lang.name}{/tr}</legend>
		<div class="form-group">
			{formlabel class="col-xs-3" label="Name" for="cat-name-`$lang.id`"}
			{forminput class="col-xs-9"}
				<input class="form-control" type="text" id="cat-name-{$lang.id}" name="categories_name[{$lang.id}]" maxlength="32">			
			{/forminput}
		</div>

		<div class="form-group">
			{formlabel class="col-xs-3" label="Description" for="cat-desc-`$lang.id`"}
			{forminput class="col-xs-9"}
				<input class="form-control" type="text" id="cat-desc-{$lang.id}" name="categories_description[{$lang.id}]" maxlength="32">			
			{/forminput}
		</div>
	</fieldset>

	<div class="form-group">
		{formlabel class="col-xs-3" label="Image" for="cat-image-`$lang.id`"}
		{forminput class="col-xs-9"}
			<input class="form-control" id="cat-image-{$lang.id}" type="file" name="categories_image">
		{/forminput}
	</div>

	<div class="form-group">
		{formlabel class="col-xs-3" label="Sort Order" for="cat-sort-`$lang.id`"}
		{forminput class="col-xs-9"}
			<input class="form-control" type="number" name="sort_order">
		{/forminput}
	</div>

	{/foreach}
	
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    	<input type="submit" class="btn btn-primary" name="Save" value="Save"> 
      </div>
    </div>
  </div>
</div>

</div>
{/strip}

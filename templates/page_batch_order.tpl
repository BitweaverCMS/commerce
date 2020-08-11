{strip}
<div class="edit bitcommerce">
	<header class="page-header">
		<h1>{tr}Batch Order{/tr}</h1>
	</header>

	<div class="body shopping-cart">
		{if $batchHash}
			{foreach from=$batchHash item=batchRow}
				{form name='batch_order' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=batch_order"  method="post" enctype="multipart/form-data"}
				<input type="hidden" name="batch_index" value="{$batchRow.batch_index}">
				<fieldset {if $batchRow.error}class="error"{/if}>
					<div class="row">
						<div class="col-xs-12 col-sm-6 col-md-7">
								{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$batchRow}
								<p><strong>{$batchRow.shipping_method}</strong></p>
								{formfeedback error=$batchRow.error}
						</div>
						<div class="col-xs-12 col-sm-6 col-md-5">
							<ul class="data">
							{foreach from=$batchRow.products item=$batchProduct}
								<li class="clear">
									<img class="image-responsive pull-left" style="max-width:150px;padding-right:10px;" src="{$batchProduct->getThumbnailUrl()}">
									<span class="badge"> {$batchRow.quantity} </span> <a href="{$batchProduct->getDisplayUrl()}"><span class="cartproductname">{$batchProduct->getTitle()}</span></a>
								</li>
							{/foreach}
							</ul>
						</div>
					</div>
{if !$batchRow.error}<button class="btn btn-primary btn-xs" name="action" value="checkout">{booticon iname="icon-shopping-cart"} {tr}Checkout{/tr}</button> {/if}<button class="btn btn-default btn-xs" name="action" value="remove">{booticon iname="icon-delete"} {tr}Remove{/tr}</button>
				</fieldset>
	{/form}
			{/foreach}
		{/if}

	{form name='batch_order' action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=batch_order"  method="post" enctype="multipart/form-data"}
		{legend legend="Upload Batch Order"}
			<p>{tr}Choose your batch order spreadsheet below.{/tr}</p>
			{if $batchHash}<p class="alert alert-warning">This will <strong>replace your batch order</strong> above.</span></p>{/if}
			{forminput class="pb-1"}
				{if $isPhpOfficeInstalled}
					{formlabel label="Select a .csv, .xls, or .xlsx file."}
				{else}
					{formlabel label="You must select a .csv file"}
				{/if}
				<input type="file" name="batch_file" class="form-input" accept="{$supportedTypes}" >
			{/forminput}
			<button type="submit" class="btn btn-default" name="action" value="upload">{tr}Upload{/tr}</button>{if count($batchHash)}  <button type="submit" class="btn btn-default pull-right" name="action" value="clear">{tr}Clear Batch{/tr}</button>{/if}
		{/legend}
	{/form}
	</div><!-- end .body -->
</div><!-- end .edit -->
{/strip}

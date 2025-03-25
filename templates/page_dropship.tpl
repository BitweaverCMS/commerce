<header class="page-header">
	<h1>{tr}Drop Ship{/tr}</h1>
</header>

{if $gBitUser->hasPermission('p_bitcommerce_dropship')}
	{form name='dropship' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post' enctype='multipart/form-data'}
		<div class="drop-ship">
			{formlabel label="Drop Shipping" for="drop-ship"}

			<p>If you have would like to have the same cart mailed to multiple recipients, upload a <a href="{$smarty.const.BITCOMMERCE_PKG_URL}pub/dropship.csv" download="{$gBitSystem->getSiteTitle()} Drop Ship Example.csv">drop ship CSV</a> file with your addresses. A unique order with unique shipping charges will be made for each entry in the CSV file.</p>
			<div class="form-inline">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">{booticon iname="fa-address-book"}</div>
						<input type="file" name="drop_csv" class="form-control"></input>
					</div>
					<input class="btn btn-default" type="submit" name="dropship_upload" value="{tr}Upload{/tr}">
				</div>
			</div>
		</div>
	{/form}

	{formfeedback hash=$feedback}

	{if $smarty.session.dropship}
	<h2>{tr}Drop Ship Orders{/tr}</h2>
	{form name='dropship' action="`$smarty.const.BITCOMMERCE_PKG_URL`dropship" method='post' enctype='multipart/form-data'}
	{foreach from=$smarty.session.dropship item=dropShipHash key=dropshipKey}
		<div class="row">
			{assign var=dropShipCart value=$dropShipHash|bc_dropship_cart}
			<div class="col-xs-12 col-sm-4">
				{include file="bitpackage:bitcommerce/address_display_inc.tpl" address=$dropShipCart->delivery selectionHtml="<input type='checkbox' name='dropship_order[]' value='`$dropshipKey`' checked> "}
				{if $dropShipHash.shipping_method_code}
					{assign var=quotes value=$gCommerceShipping->quote($dropShipCart,$dropShipHash.shipping_method_code,$dropShipHash.shipping_method)}
					{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl" sessionShippingId="`$dropShipHash.shipping_method`_`$dropShipHash.shipping_method_code`" liCss="col-xs-12"}
					{*booticon iname="fa-truck-fast"} {$dropShipHash.shipping_method}: {$dropShipHash.shipping_method_code*}
				{else}
					{assign var=quotes value=bc_dropship_quote($dropShipCart)}
					{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl" liCss="col-xs-12"}
				{/if}
			</div>
			<div class="col-xs-12 col-sm-8">

				{include file="bitpackage:bitcommerce/shopping_cart_contents_inc.tpl" cart=$dropShipCart}

			</div>
		</div>
		<hr>
	{/foreach}

	{forminput label="checkbox"}
		<input name="notify" type="checkbox" checked> {booticon iname="fa-envelope" iexplain="Email"} {tr}Email receipt for each order{/tr}
	{/forminput}

	{forminput}
		<input type="submit" name="dropship_process" value="Process Drop Ship" class="btn btn-default">
	{/forminput}

	{/form}
	{/if}
{/if}

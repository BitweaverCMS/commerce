{literal}
<script type="text/javascript">//<![CDATA[
function updateShippingQuote( pForm ) {
	$('#shippingquotes').html('{/literal}{booticon iexplain=Loading iclass="icon-spinner icon-spin"} <em>{tr}Getting Shipping Estimate{/tr}</em>{literal}');
	jQuery.ajax({
		data: $(pForm).serialize(),
		url: "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}shipping_estimator.php{literal}",
		timeout: 60000,
		success: function(r) { 
			$('#shippingestimate').html(r);
		}
	})
}
//]]></script>
{/literal}

<div id="shippingestimate">

{form class="form-horizontal"}
{legend legend="Shipping Estimate"}

<input type="hidden" name="products_id" value="{$gBitProduct->mProductsId}"/>
{foreach from=$smarty.request.id key=attrId item=attrVal}
	<input type="hidden" name="id[{$attrId}]" value="{$attrVal}"/>
{/foreach}

<p>{tr}Shipping prices are an estimate only. The actual amount may vary once a final total is calculated during checkout.{/tr}</p>

{if $addresses}
	{forminput}
		<div class="col-xs-12">
			<select class="form-control" onchange="updateShippingQuote( this.form );" name="address_id" id="addressid">
			{foreach from=$addresses item=addr}
				<option value="{$addr.address_book_id}" {if $smarty.session.cart_address_id == $addr.address_book_id}{assign var=selAddr value=$addr}selected="selected"{/if}>{$addr.address_format_id|zen_address_format:$addr:0:' ':' '}</option>
			{/foreach}
			</select>
		</div>
	{/forminput}
	{forminput}
		{formlabel class="col-xs-3" label="Ship To"}
		<div class="col-xs-9">
			{$selAddr.address_format_id|zen_address_format:$selAddr:1:' ':'<br />'}
		</div>
	{/forminput}
{else}
	{if $gBitCustomer->mCart->get_content_type() != 'virtual'}
		{forminput class="country"}
			{formlabel class="col-xs-3" label="Country"}
			<div class="col-xs-9">
				{$countryMenu}
			</div>
		{/forminput}
		{if $stateMenu}
			{forminput class="state"}
				{formlabel class="col-xs-3" label="State / Province"}
				<div class="col-xs-9">
					{$stateMenu}
				</div>
			{/forminput}
		{/if}
		{forminput class="postalcode"}
			{formlabel class="col-xs-3" label="Postal Code"}
			<div class="col-xs-9">
				<input type="text" class="form-control" name="zip_code" value="{$smarty.session.cart_zip_code|default:$smarty.request.zip_code}"/>		
			</div>
		{/forminput}
	{/if}
{/if}

	{if $gBitProduct->isValid()}
		{forminput}
			{formlabel class="col-xs-3" label="Product"}
			<div class="col-xs-9">
				{$gBitProduct->getTitle()|escape}
			</div>
		{/forminput}
	{elseif !$gBitProduct->isValid() && $gBitCustomer->mCart && $gBitCustomer->mCart->count_contents()}
		{forminput}
			{formlabel class="col-xs-3" label="Items in Cart"}
			<div class="col-xs-9">
				{$gBitCustomer->mCart->count_contents()}
			</div>
		{/forminput}
	{/if}

	{if $smarty.request.cart_quantity}
		{forminput}
			{formlabel class="col-xs-3" label="Quantity"}
			<div class="col-xs-9">
				<input type="text" class="form-control" name="cart_quantity" value="{$smarty.request.cart_quantity}"/>
			</div>
		{/forminput}
	{/if}
{forminput class="submit"}
	<div class="col-xs-offset-3 col-xs-9">
		<input type="button" class="btn btn-default btn-xs" value="Update" onclick="updateShippingQuote( this.form )"/>
	</div>
{/forminput}

{if $gBitCustomer->mCart->get_content_type() == 'virtual'}
	{tr}Free Shipping{/tr} {tr}- Downloads{/tr}
{elseif $freeShipping == 1}
	{tr}Free shipping for orders over{/tr} {$gBitCurrencies->format($smarty.const.MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)}
{else}
	{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl" noradio=1}
{/if}
{/legend}
{/form}

</div>

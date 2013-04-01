{literal}
<script type="text/javascript">//<![CDATA[
function updateShippingQuote( pForm ) {
	$('#shippingquotes').html('{/literal}{biticon ipackage=liberty iname=busy iexplain=Loading style="vertical-align:middle;padding-right:5px"}<em>{tr}Getting Shipping Estimate{/tr}</em>{literal}');
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

{form}
{legend legend="Shipping Estimate"}

<input type="hidden" name="products_id" value="{$gBitProduct->mProductsId}"/>
{foreach from=$smarty.request.id key=attrId item=attrVal}
	<input type="hidden" name="id[{$attrId}]" value="{$attrVal}"/>
{/foreach}

<p>{tr}Shipping prices are an estimate only. The actual amount may vary once a final total is calculated during checkout.{/tr}</p>

{if $addresses}
<div class="control-group">
	<select onchange="updateShippingQuote( this.form );" name="address_id" id="addressid">
	{foreach from=$addresses item=addr}
		<option value="{$addr.address_book_id}" {if $smarty.session.cart_address_id == $addr.address_book_id}{assign var=selAddr value=$addr}selected="selected"{/if}>{$addr.address_format_id|zen_address_format:$addr:0:' ':' '}</option>
	{/foreach}
	</select>
</div>
<div class="control-group">
	{formlabel label="Ship To"}
	{forminput}
		{$selAddr.address_format_id|zen_address_format:$selAddr:1:' ':'<br />'}
	{/forminput}
</div>
{else}
	{if $gBitCustomer->mCart->get_content_type() != 'virtual'}
<div class="control-group country">
	{formlabel label="Country"}
	{forminput}
		{$countryMenu}
	{/forminput}
</div>
{if $stateMenu}
<div class="control-group state">
	{formlabel label="State / Province"}
	{forminput}
		{$stateMenu}
	{/forminput}
</div>
{/if}
<div class="control-group postalcode">
	{formlabel label="Postal Code"}
	{forminput}
		<input type="text" name="zip_code" value="{$smarty.session.cart_zip_code|default:$smarty.request.zip_code}"/>		
	{/forminput}
</div>
	{/if}
{/if}

	{if $gBitProduct->isValid()}
<div class="control-group">
	{formlabel label="Product"}
	{forminput}
		{$gBitProduct->getTitle()|escape}
	{/forminput}
</div>
	{elseif !$gBitProduct->isValid() && $gBitCustomer->mCart && $gBitCustomer->mCart->count_contents()}
<div class="control-group">
	{formlabel label="Items in Cart"}
	{forminput}
		{$gBitCustomer->mCart->count_contents()}
	{/forminput}
</div>
	{/if}

	{if $smarty.request.cart_quantity}
<div class="control-group">
	{formlabel label="Quantity"}
	{forminput}
		<input type="text" name="cart_quantity" value="{$smarty.request.cart_quantity}"/>
	{/forminput}
</div>
	{/if}
<div class="control-group submit">
	<input type="button" class="btn btn-mini" value="Update" onclick="updateShippingQuote( this.form )"/>
</div>
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

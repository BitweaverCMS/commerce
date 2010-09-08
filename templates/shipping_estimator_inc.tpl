{literal}
<script type="text/javascript">//<![CDATA[
function updateShippingQuote( pForm ) {
	BitBase.fade( 'shippingquote', 1, 'up' );
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
<input type="hidden" name="products_id" value="{$gBitProduct->mProductsId}"/>
<input type="hidden" name="cart_quantity" value="{$smarty.request.cart_quantity}"/>
{foreach from=$smarty.request.id key=attrId item=attrVal}
	<input type="hidden" name="id[{$attrId}]" value="{$attrVal}"/>
{/foreach}

{legend legend="Shipping Estimate"}
{if $gBitCustomer->mCart && $gBitCustomer->mCart->count_contents()}
	{tr}Items in Cart:{/tr} {$gBitCustomer->mCart->count_contents()}
{/if}
{assign var=addresses value=$gBitCustomer->getAddresses()}
{if $addresses}
<div class="row">
	<select onchange="updateShippingQuote( this.form );" name="address_id" id="addressid">
	{foreach from=$addresses item=addr}
		<option value="{$addr.address_book_id}" {if $smarty.request.address_id == $addr.address_book_id}{assign var=selAddr value=$addr}selected="selected"{/if}>{$addr.address_format_id|zen_address_format:$addr:0:' ':' '}</option>
	{/foreach}
	</select>
</div>
<div class="row">
	{formlabel label="Ship To:"}
	{forminput}
		{$selAddr.address_format_id|zen_address_format:$selAddr:1:' ':'<br />'}
	{/forminput}
</div>
{else}
	{if $gBitCustomer->mCart->get_content_type() != 'virtual'}
<div class="row country">
	{formlabel label="Country"}
	{forminput}
		{$countryMenu}
	{/forminput}
</div>
{if $stateMenu}
<div class="row state">
	{formlabel label="State / Province"}
	{forminput}
		{$stateMenu}
	{/forminput}
</div>
{/if}
<div class="row postalcode">
	{formlabel label="Postal Code"}
	{forminput}
		<input type="text" name="zip_code" value="{$smarty.session.cart_zip_code|default:$smarty.request.zip_code}"/>		
	{/forminput}
</div>
<div class="row submit">
	<input type="button" class="minibutton" value="Update" onclick="updateShippingQuote( this.form )"/>
</div>
	{/if}
{/if}
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

{literal}
<script type="text/javascript">//<![CDATA[
function shippingQuoteAddressChange( pForm ) {
	if( $('#addressid').val() == 'custom' ) {
		$('#customaddress').show();
	} else {
		$('#customaddress').hide();
		shippingQuoteUpdate( pForm );
	}
}
function shippingQuoteUpdate( pForm ) {
	$('#shippingquotes').html('{/literal}<p class="alert alert-info">{booticon iclass="fa-spinner" class="fa-spin"} {tr}Getting Shipping Estimate{/tr}</p>{literal}');
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

{form class=""}
{legend legend="Shipping Estimate"}

<input type="hidden" name="products_id" value="{$gBitProduct->mProductsId}"/>
{foreach from=$smarty.request.id key=attrId item=attrVal}
	<input type="hidden" name="id[{$attrId}]" value="{$attrVal}"/>
{/foreach}

<p>{tr}Shipping prices are an estimate only. The actual amount may vary once a final total is calculated during checkout.{/tr}</p>

	{if $addresses}
	<div class="row form-group">
		<div class="col-xs-12">
			<select class="form-control" onchange="shippingQuoteAddressChange( this.form );" name="address_id" id="addressid">
			{foreach from=$addresses item=addr}
				<option value="{$addr.address_book_id}" {if $smarty.session.sendto == $addr.address_book_id}{assign var=selAddr value=$addr}selected="selected"{/if}>{$addr.address_format_id|zen_address_format:$addr:0:' ':' '}</option>
			{/foreach}
				<optgroup label="----------"></optgroup>
				<option value="custom" {if $smarty.session.sendto == "custom"}selected="selected"{/if}>{tr}Enter Country and Postal Code...{/tr}</option>
			</select>
		</div>
	</div>
	{else}
	<p><em>{tr}Select Country and Postal Code.{/tr}</em></p>
	{/if}
	{if $gBitCustomer->mCart->get_content_type() != 'virtual'}
		<div id="customaddress" class="row form-group {if $addresses && $smarty.session.sendto != "custom"}display-none{/if}">
			<div class="col-xs-8">
				{$countryMenu}
			</div>
			<div class="col-xs-4">
				<input type="text" placeholder="Enter zip code." class="form-control" name="zip_code" value="{$smarty.session.cart_zip_code|default:$smarty.request.zip_code}"/>		
			</div>
		</div>
	{/if}

	{if $gBitProduct->isValid()}
	{elseif !$gBitProduct->isValid() && $gBitCustomer->mCart && $gBitCustomer->mCart->count_contents()}
		<div class="row form-group">
			{formlabel label="Items in Cart"}
			<div class="col-sm-8">
				{$gBitCustomer->mCart->count_contents()}
			</div>
		</div>
	{/if}

	{if $smarty.request.cart_quantity}
		<div class="row form-group">
			<div class="col-xs-6">
				<div class="input-group">
					<div class="input-group-addon">{tr}Quantity{/tr}</div>
					<input type="number" class="form-control" name="cart_quantity" value="{$smarty.request.cart_quantity}"/>
				</div>
			</div>
		</div>
	{/if}
	<input type="button" class="btn btn-info btn-sm" value="Update" onclick="shippingQuoteUpdate( this.form )"/>

	<hr>

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

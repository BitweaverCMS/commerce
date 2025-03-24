{strip}
{assign var=cartHasContents value=$gBitCustomer->mCart->count_contents()}
<div class="edit bitcommerce">
<header class="page-header">
{if $cartHasContents && $smarty.const.SHOW_TOTALS_IN_CART}
	<div class="smallText pull-right">
	<strong>{$cartHasContents} {tr}Items{/tr}</strong>
	{if $gBitCustomer->mCart->show_weight()}
		, {$gBitCustomer->mCart->show_weight()|round:2} {tr}lbs{/tr} ( {$gBitCustomer->mCart->show_weight('kg')|round:2} {tr}Kg{/tr} )
	{/if}
	, {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}
	</div>
{/if}
	<h1>{tr}Your Shopping Cart Contains:{/tr}</h1>
</header>

<div class="body shopping-cart">
	{form name='cart_quantity' action=shopping_cart|zen_href_link}
	{if $cartHasContents}
		{if $gBitCustomer->mCart->mErrors.checkout}
			{formfeedback error=$gBitCustomer->mCart->mErrors.checkout}
		{/if}

		{include file="bitpackage:bitcommerce/shopping_cart_contents_inc.tpl" cart=$gBitCustomer->mCart}
	{else}
		<div>
			{tr}Your Shopping Cart is empty.{/tr}
		</div>
	{/if}


	<div class="row subtotal">
		<div class="col-sm-6 col-sm-offset-6 currency">
			{if $cartHasContents}{tr}Sub-Total:{/tr} {$gCommerceCurrencies->format($gBitCustomer->mCart->show_total())}{/if}
			<select class="form-control inline-block width-auto ml-1" name="currency" onchange="this.form.submit()">
			{foreach from=$gCommerceCurrencies->currencies item=currencyHash key=currencyCode}
				<option value="{$currencyCode}" {if $smarty.session.currency==$currencyCode || (!$smarty.session.currency && $currencyCode==$smarty.const.DEFAULT_CURRENCY)}selected="selected"{/if}>{$currencyHash.code|escape:html}</option>
			{/foreach}
			</select>
		</div>
		<div class="col-xs-12 text-right">
			{if $gCommerceSystem->getConfig('CONTINUE_SHOPPING_URL')}<a class="pull-left btn btn-default" href="{$gCommerceSystem->getConfig('CONTINUE_SHOPPING_URL')}">{tr}Continue Shopping{/tr}</a>{/if}
			{if $cartHasContents}{if $gCommerceSystem->getConfig('SHOW_SHOPPING_CART_UPDATE')}<input type="submit" class="btn btn-default" name="update_cart" value="{tr}Update Cart{/tr}" />{/if} <input type="submit" class="btn btn-primary" name="checkout" value="{tr}Checkout{/tr}" />{/if} 
		</div>
	</div>

{/form}
</div><!-- end .body -->
</div><!-- end .edit -->
{/strip}

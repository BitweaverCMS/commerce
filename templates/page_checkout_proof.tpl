<h1> Some items in your cart need your attention </h1>
<div class="error"> Please verify the following products : </div>

{foreach from=$pendingItems item = 'item' name='items'} 
	{assign var='itemnum' value=$smarty.foreach.items.index}
	{include file=$pendingTemplates.$itemnum}
{/foreach}
<div class="clear warning"> Click NEXT when you are ready to continue. Note that this is the final warning you will receive prior to ordering your product. </div>

<form action="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_shipping" method="POST">
	<input type="submit" class="btn" name="checkout_proof" value="{tr}Next{/tr} &raquo;" />
</form>				

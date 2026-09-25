{if $ordersProduct.products_id}
{assign var=productUrl value=$gBitProduct->getDisplayUrlFromHash($ordersProduct)}
{assign var=customizeUrl value=$productUrl}
{if !empty($ordersProduct.reorder.query)}
	{if $productUrl|strstr:'?'}
		{assign var=customizeUrl value="`$productUrl`&`$ordersProduct.reorder.query`"}
	{else}
		{assign var=customizeUrl value="`$productUrl`?`$ordersProduct.reorder.query`"}
	{/if}
{/if}
<button type="button" class="btn btn-xs btn-primary" onclick="var panel=document.getElementById('order-again-{$ordersProduct.orders_products_id}'); if(panel) panel.style.display = (panel.style.display=='none' || panel.style.display=='') ? 'block' : 'none';">{tr}Order Again{/tr}</button>
<div id="order-again-{$ordersProduct.orders_products_id}" class="order-again-panel" style="display:none;margin-top:.4em;">
	<form method="post" action="{$smarty.const.BITCOMMERCE_PKG_URL}" class="form-inline">
		<input type="hidden" name="products_id" value="{$ordersProduct.products_id}" />
		{if !empty($ordersProduct.reorder.fields)}
			{foreach from=$ordersProduct.reorder.fields item=reorderField}
				<input type="hidden" name="{$reorderField.name|escape:'html'}" value="{$reorderField.value|escape:'html'}" />
			{/foreach}
		{/if}
		<label class="small" style="margin-right:.4em;">{tr}Qty{/tr}
			<input type="number" class="form-control input-sm" name="cart_quantity" min="1" step="1" value="{$ordersProduct.products_quantity|default:1}" style="width:4.5em;display:inline-block;" />
		</label>
		<button type="submit" class="btn btn-xs btn-primary" name="action" value="add_product">{tr}Add To Cart{/tr}</button>
		<button type="submit" class="btn btn-xs btn-default" formaction="{$customizeUrl|escape:'html'}">{tr}Customize{/tr}</button>
		{if !empty($ordersProduct.reorder.skipped_file)}
			<div class="small">{tr}Uploaded files are not copied.{/tr}</div>
		{/if}
	</form>
</div>
{/if}

<h2>{tr}Previous Orders{/tr}</h2>
<hr>
{foreach from=$ordersHistory item=ordersRow key=ordersId}
<div class="row">
	<div class="col-xs-3 col-sm-2">
		<a href="{'account_history_info'|zen_get_page_url:"order_id=`$ordersRow.orders_id`"}">{tr}#{/tr}{$ordersRow.orders_id}</a>
		<div class="small">{$ordersRow.date_purchased|zen_date_short}</div>
	</div>
	<div class="col-xs-7 col-sm-8">
		<em>
		{if $ordersRow.delivery_name|trim && $ordersRow.billing_name != $ordersRow.delivery_name}{tr}Sent to{/tr} {$ordersRow.delivery_name}{else}{$ordersRow.billing_name}{/if}</em>
		<dt>
			<dd class="item">
			{if $ordersRow.delivery_country}
				{$ordersRow.delivery_street_address}, {$ordersRow.delivery_city}, {$ordersRow.delivery_state} {$ordersRow.delivery_country}
			{else}
				<em>{tr}Online Order{/tr}</em>
			{/if}
			</dd>
		</dt>
	</div>
	<div class="col-xs-2 col-sm-2 text-right">
		<div>{$gCommerceCurrencies->format($ordersRow.order_total, true, $orderRow.currency, $orderRow.currency_value)}</div>
		<div class="small">{$ordersRow.orders_status_name}</div>
	</div>
</div>
{foreach from=$ordersRow.products item=ordersProduct key=opid}
{assign var=product value=$ordersProduct.products_id|bc_get_commerce_product}
<div class="row pb-2">
	<div class="col-xs-4 col-sm-2 text-center"><img src="{if $product|is_object}{$product->getThumbnailUrl('icon')}{else}{$ordersProduct.default_image}{/if}" class="img-responsive" style="max-height:100px;"/></div>
	<div class="col-xs-8 col-sm-10 text-left">
		{$ordersProduct.products_quantity}&nbsp;x <a href="{$gBitProduct->getDisplayUrlFromHash($ordersProduct)}">{$ordersProduct.products_name|default:"Product `$ordersProduct.products_id`"}</a>
		<br/>{$ordersProduct.products_model}{if $ordersProduct.products_version > 1}, v{$ordersProduct.products_version}{/if}
		{if !empty( $ordersProduct.attributes )}
		<ul class="">
		{foreach from=$ordersProduct.attributes item=optionValue key=povid}
			<li class="orders products attributes">
				<small>{$optionValue}</small> 
			</li>
		{/foreach}
		</ul>
		{/if}
		<a class="btn btn-xs btn-primary" href="{$gBitProduct->getDisplayUrlFromHash($ordersProduct)}">Order Again</a>
	</div>
</div>
{/foreach}
<hr>

{foreachelse}
<div class="row">
	<div class="col-xs-6 col-sm-3">
		<em>{tr}No Orders{/tr}</em>
	</div>
</div>
{/foreach}

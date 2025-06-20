{if $order}
<table class="table">
<tr>
{if sizeof($order->info.tax_groups) > 1}
	<th>{tr}Products{/tr}</th>
	<th class="text-right">{tr}Tax{/tr}</th>
{else}
	<th colspan="2">{tr}Products{/tr}</th>
{/if}
	{if $showPricing}
	<th class="text-right">{tr}Total{/tr}</th>
	{/if}
</th>
{foreach from=$order->contents item=ordersProduct key=opid}
{assign var=product value=$order->getProductObject($ordersProduct.products_id)}
<tr>
	<td valign="top" align="right"><img src="{if $product|is_object}{$product->getThumbnailUrl('icon')}{else}{$ordersProduct.default_image}{/if}" style="max-height:100px;"/>
	<td valign="top">
		{$ordersProduct.products_quantity}&nbsp;x <a href="{$gBitProduct->getDisplayUrlFromHash($ordersProduct)}">{$ordersProduct.name|default:"Product `$ordersProduct.products_id`"}</a>
		<br/>{$ordersProduct.model}{if $ordersProduct.products_version}, v{$ordersProduct.products_version}{/if}
		{if !empty( $ordersProduct.attributes )}
		<ul class="">
		{section loop=$ordersProduct.attributes name=a}
			<li class="orders products attributes">
				<small>{$ordersProduct.attributes[a].products_options_name}: {$ordersProduct.attributes[a].products_options_values_name}
					{assign var=sumAttrPrice value=$ordersProduct.attributes[a].final_price*$ordersProduct.products_quantity}
					{if $ordersProduct.attributes[a].price}({$ordersProduct.attributes[a].prefix}{$gCommerceCurrencies->format($sumAttrPrice,true,$order->info.currency,$order->info.currency_value)}){/if}
					{if !empty($ordersProduct.attributes[a].product_attribute_is_free) && $ordersProduct.attributes[a].product_attribute_is_free == '1' and $ordersProduct.product_is_free == '1'}<span class="alert alert-warning">{tr}FREE{/tr}</span>{/if}
				</small> 
			</li>
		{/section}
		</ul>
		{/if}
		<a class="btn btn-xs btn-primary" href="{$gBitProduct->getDisplayUrlFromHash($ordersProduct)}">Order Again</a>
		{$order->displayOrderProductData($opid)}
	</td>

    {if sizeof($order->getField('tax_groups')) > 1}
    <td class="text-right">{$ordersProduct.tax|zen_display_tax_value}%</td>
    {/if}

	{if $showPricing}
    <td class="text-right">
		{$gCommerceCurrencies->display_price( $ordersProduct.final_price, $ordersProduct.tax, $ordersProduct.products_quantity, $order->getField('currency'), $order->getField('currency_value'))} 
		{if $ordersProduct.onetime_charges}<br />{$gCommerceCurrencies->format(zen_add_tax($ordersProduct.onetime_charges, $ordersProduct.tax), true, $order->getField('currency'), $order->getField('currency_value'))}{/if}
	</td>
	{/if}
</tr>
{/foreach}

{if $order->getDownloads()}
<tr><td>
{foreach from=$order->getDownloads() item=download}
<div class="col-sm-3 col-xs-6">
	<div class="well">
        <a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=download&amp;order={$order->mOrdersId}&amp;id={$downloads.orders_products_download_id}"><div>{booticon iname="fa-download" class="fa-3x"}</div>{$downloads.products_name}<div class="small">{"`$smarty.const.DIR_FS_DOWNLOAD``$downloads.orders_products_filename`"|filesize|display_bytes}</a>
	</div>
</div>
{/foreach}
</td></tr>
{/if}

{section loop=$order->totals name=t}
{if $showPricing || $order->totals[t].class == 'ot_shipping'}
<tr>
	<td colspan="2" style="text-align:right;" class="text-right {'ot_'|str_replace:'':$order->totals[t].class}">
		<label>{$order->totals[t].title}</label>
		{if $order->totals[t].class == 'ot_shipping' && !empty($order->info.shipping_tracking_number)}
		<div>{tr}Tracking{/tr}: {$order->info.shipping_tracking_number}<div>
		{/if}
	</td>
	{if $showPricing}
	<td style="text-align:right;" class="text-right {'ot_'|str_replace:'':$order->totals[t].class}">
		{$gCommerceCurrencies->format($order->totals[t].orders_value, 1, $order->getField('currency'), $order->getField('currency_value'))} {if $isForeignCurrency}{$gCommerceCurrencies->format($order->totals[t].orders_value,true,$smarty.const.DEFAULT_CURRENCY)}{/if}
	</td>
	{/if}
</tr>
{/if}
{/section}
</table>
{/if}

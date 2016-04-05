{if $emailVars.order}
{assign var=order value=$emailVars.order}
<table class="table">
<tr>
{if sizeof($order->info.tax_groups) > 1}
	<th>{tr}Products{/tr}</th>
	<th class="text-right">{tr}Tax{/tr}</th>
{else}
	<th colspan="2">{tr}Products{/tr}</th>
{/if}
	<th class="text-right">{tr}Total{/tr}</th>
</th>
{foreach from=$order->contents item=ordersProduct key=opid}
<tr>
	<td class="text-right" valign="top">{$ordersProduct.products_quantity}&nbsp;x</td>
	<td valign="top">
		<a href="{$gBitProduct->getDisplayUrlFromHash($ordersProduct)}">{$ordersProduct.name|default:"Product `$ordersProduct.products_id`"}</a>
		<br/>{$ordersProduct.model}{if $ordersProduct.products_version}, v{$ordersProduct.products_version}{/if}
		{if !empty( $ordersProduct.attributes )}
		<ul class="">
		{section loop=$ordersProduct.attributes name=a}
				<li class="orders products attributes" id="{$ordersProduct.attributes[a].products_attributes_id}att">
					<small>{$ordersProduct.attributes[a].option}: {$ordersProduct.attributes[a].value}
						{assign var=sumAttrPrice value=$ordersProduct.attributes[a].final_price*$ordersProduct.products_quantity}
						{if $ordersProduct.attributes[a].price}({$ordersProduct.attributes[a].prefix}{$gCommerceCurrencies->format($sumAttrPrice,true,$order->info.currency,$order->info.currency_value)}){/if}
						{if !empty($ordersProduct.attributes[a].product_attribute_is_free) && $ordersProduct.attributes[a].product_attribute_is_free == '1' and $ordersProduct.product_is_free == '1'}<span class="alert alert-warning">{tr}FREE{/tr}</span>{/if}
					</small> 
				</li>
		{/section}
		</ul>
		{/if}
		{$order->displayOrderProductData($opid)}
	</td>

    {if sizeof($order->getField('tax_groups')) > 1}
    <td class="text-right">{$ordersProduct.tax|zen_display_tax_value}%</td>
    {/if}

    <td class="text-right">
		{$gCommerceCurrencies->format(zen_add_tax($orderProduct.final_price, $orderProduct.tax) * $orderProduct.products_quantity, true, $order->getField('currency'), $order->getField('currency_value'))} 
		{if $orderProduct.onetime_charges}<br />{$gCommerceCurrencies->format(zen_add_tax($orderProduct.onetime_charges, $ordersProduct.tax), true, $order->getField('currency'), $order->getField('currency_value'))}{/if}
	</td>
</tr>
{/foreach}

<div class="row">
{foreach from=$order->getDownloads() item=download}
<div class="col-sm-3 col-xs-6">
	<div class="well">
        <a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=download&amp;order={$order->mOrdersId}&amp;id={$downloads.orders_products_download_id}"><div>{booticon iname="icon-download" class="icon-3x"}</div>{$downloads.products_name}<div class="small">{"`$smarty.const.DIR_FS_DOWNLOAD``$downloads.orders_products_filename`"|filesize|display_bytes}</a>
	</div>
</div>
{/foreach}
</div>

{section loop=$order->totals name=t}
<tr>
	<td colspan="2" class="text-right {'ot_'|str_replace:'':$order->totals[t].class}">
		<label>{$order->totals[t].title}</label>
	</td>
	<td class="text-right {'ot_'|str_replace:'':$order->totals[t].class}">
		{$gCommerceCurrencies->format($order->totals[t].orders_value)} {if $isForeignCurrency}{$gCommerceCurrencies->format($order->totals[t].orders_value,true,$smarty.const.DEFAULT_CURRENCY)}{/if}
	</td>
</tr>
{/section}
</table>
{/if}

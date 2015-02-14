
<header>
	<div class="row">
		<div class="col-xs-6">
			<div class="panel panel-default height">
				<div class="panel-body">
					<h2>{$gBitSystem->getConfig('site_title')}</h2>
					{$smarty.const.STORE_NAME_ADDRESS|nl2br}
				</div>
			</div>
		</div>
		<div class="col-xs-6 text-right">
			<h1 class="page-heading">{tr}Order{/tr} #{$order->mOrdersId}</h1>
			<div class="date">{tr}Purchased{/tr}: {$order->getField('date_purchased')|bit_long_datetime}</div>
		</div>
	</div>
</header>

<div class="row clear">
	<div class="col-sm-4 col-xs-6">
		<div class="panel panel-default height">
			<div class="panel-heading">{tr}Billing Address{/tr}</div>
			<div class="panel-body">
				{$order->getFormattedAddress('billing')}
			</div>
		</div>
	</div>
	{if $order->delivery}
	<div class="col-sm-4 col-xs-6">
		<div class="panel panel-default height">
			<div class="panel-heading">{tr}Delivery Address{/tr}</div>
			<div class="panel-body">
				{$order->getFormattedAddress('delivery')}
			</div>
		</div>
	</div>
	{/if}

	<div class="col-sm-4 col-xs-12">
		<div class="row">
			<div class="col-sm-12 col-xs-6">
				<div class="panel panel-default height">
					<div class="panel-heading">{tr}Payment{/tr}</div>
					<div class="panel-body">
						{if $order->info.cc_type || $order->info.cc_owner || $order->info.cc_number}
						<div class="clear">
							<div class="pull-left">{$order->info.cc_type}: </div>
							<div class="pull-right">{$order->info.cc_owner}</div>
						</div>
						<div class="clear">
							<div class="pull-left">{tr}Number{/tr}: </div>
							<div class="pull-right">{$order->info.cc_number}</div>
						</div>
						<div class="clear">
							<div class="pull-left">{tr}Expires{/tr}: </div>
							<div class="pull-right">{$order->info.cc_expires} CVV: {$order->getField('cc_cvv')}</div>
						</div>
						<div class="clear">
							<div class="pull-left">{tr}Transaction ID{/tr}: </div>
							<div class="pull-right">{$order->info.cc_ref_id}</div>
						</div>
						{/if}
						<div class="clear">
							<div class="pull-left">{tr}IP{/tr}:</div>
							<div class="pull-right"> {$order->info.ip_address}</div>
						</div>
					</div>
				</div>
			</div>
			{if $order->getField('shipping_method')}
			<div class="col-sm-12 col-xs-6">
				<div class="panel panel-default height">
					<div class="panel-heading">{tr}Shipping{/tr}</div>
					<div class="panel-body">
						{$order->getField('shipping_method')} {$order->getField('shipping_method_code')}
					</div>
				</div>
			</div>
			{/if}
		</div>
	</div>
</div>


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
{if $ordersProductFile|file_exists}
{$ordersProductFile}
{/if}
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

{if $order->mHistory}
<h3>{tr}Status History & Comments{/tr}</h3>
<ul class="list-unstyled">
{foreach from=$order->mHistory|@array_reverse item=history}
	{if $history.customer_notified || $gBitUser->hasPermission( 'p_bitcommerce_admin' )}
	<li class="alert {if $history.customer_notified}alert-warning{else}alert-info{/if}">
		<div class="strong"><strong>{$history.date_added|bit_short_datetime}</strong> <em>{$history.orders_status_name}</em></div>
		{if $history.comments|escape:"html"}
		<p>{$history.comments}</p>
		{/if}
   </li>
   {/if}
{/foreach}
</ul>
{/if}

<a class="btn btn-default" href="mailto:{$smarty.const.STORE_OWNER_EMAIL_ADDRESS}">Contact Us</a>

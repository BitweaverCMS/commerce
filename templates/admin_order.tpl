<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/CalendarPopup.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/PopupWindow.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/AnchorPosition.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/date.js"></script>
<div id="caldiv" style="width:200px;position:absolute;visibility:hidden;background-color:white;layer-background-color:white;"></div>

{literal}
<script type="text/javascript">/* <![CDATA[ */
function editOption( pOrdPrdAttId ) {
	alert( pOrdPrdAttId );
	return false;
}
function deleteOption( pOrdPrdAttId, pTitle ) {
	return confirm( "Are you sure you want to delete the option '"+pTitle+"' from this order?" );
}

function getNewOption( pOrdPrdId ) {
	jQuery.ajax({
		data: 'new_option_id='+document.getElementById('neworderoption'+pOrdPrdId).value+'&orders_products_id='+pOrdPrdId,
		url: "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php{literal}",
		timeout: 60000,
		success: function(r) { 
			$('#neworderattr'+pOrdPrdId).html(r);
		}
	})
}

function saveNewOption( pForm ) {
	pForm.submit();
	window.location.reload();
}

function getShippingQuotes( pOrderId ) {
	jQuery.ajax({
		data: 'action=quote&oID='+pOrderId,
		url: "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/shipping_change.php{literal}",
		timeout: 60000,
		success: function(r) { 
			$('#shippingquote').html(r);
		}
	})

}

/* ]]> */</script>
{/literal}
{strip}


<div class="page-header">
	<div class="floaticon">
		<div class="btn-group floatright">
			<button class="btn"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$smarty.request.oID-1}">&laquo; {tr}Previous{/tr}</a></button>
			<button class="btn"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$smarty.request.oID+1}">{tr}Next{/tr} &raquo;</a></button>
		</div>
	</div>
	<h1>
		{$smarty.const.HEADING_TITLE} 
	</h1>
</div>

{include file="bitpackage:bitcommerce/admin_order_header_inc.tpl"}

<table class="table data">
	<tr class="dataTableHeadingRow">
		<th></th>
		<th class="alignleft">{$smarty.const.TABLE_HEADING_PRODUCTS}, {$smarty.const.TABLE_HEADING_PRODUCTS_MODEL}</th>
		<th class="alignright">{tr}Price{/tr} + {tr}Tax{/tr}</th>
		<th class="alignright">{tr}Total{/tr} + {tr}Tax{/tr}</th>
		<th class="aligncenter">{tr}Income{/tr}</th>
	</tr>
{assign var=wholesaleIncome value=0}
{assign var=wholesaleCost value=0}
{assign var=couponAmount value=0}
{assign var=giftAmount value=0}

{foreach from=$order->contents item=ordersProduct}
<tr class="dataTableRow">
<td class="dataTableContent alignright" valign="top">{$ordersProduct.products_quantity}&nbsp;x
	<div>{booticon href="product_history.php?products_id=`$ordersProduct.products_id`" iname="icon-time" iexplain="Products History"}</div>
</td>
<td class="dataTableContent" valign="top"><a href="{$gBitProduct->getDisplayUrlFromHash($ordersProduct)}">{$ordersProduct.name|default:"Product `$ordersProduct.products_id`"}</a>
	<br/>{$ordersProduct.model}{if $ordersProduct.products_version}, v{$ordersProduct.products_version}{/if}{if $ordersProduct.products_commission}, {$currencies->format($ordersProduct.products_commission)} {tr}Commission{/tr}{/if}</td>
<td class="dataTableContent alignright" valign="top">
	{$currencies->format($ordersProduct.final_price,true,$order->info.currency, $order->info.currency_value)}{if $isForeignCurrency} /{$currencies->format($ordersProduct.final_price,true,$smarty.const.DEFAULT_CURRENCY)}{/if}
	{if $ordersProduct.onetime_charges}<br />{$currencies->format($ordersProduct.onetime_charges, true, $order->info.currency, $order->info.currency_value)}{if $isForeignCurrency} /{$currencies->format($ordersProduct.onetime_charges,true,$smarty.const.DEFAULT_CURRENCY)}{/if}{/if}
	{assign var=finalPlusTax value=$ordersProduct.final_price|zen_add_tax:$ordersProduct.tax}
{if $ordersProduct.tax}
	{$ordersProduct.tax|zen_display_tax_value}%
	( {$currencies->format($finalPlusTax, true, $order->info.currency, $order->info.currency_value)} )
	{if $ordersProduct.onetime_charges}
		{assign var=onetimePlusTax value=$ordersProduct.onetime_charges|zen_add_tax:$ordersProduct.tax}
		{if $ordersProduct.onetime_charges != $onetimePlusTax}
			<br /> {$currencies->format($onetimePlusTax,true,$order->info.currency,$order->info.currency_value)}{if $isForeignCurrency} /{$currencies->format($onetimePlusTax,true,$smarty.const.DEFAULT_CURRENCY)}{/if}
		{/if}
	{/if}
{/if}
</td>
<td class="dataTableContent alignright" valign="top">
	{assign var=finalQty value=$ordersProduct.final_price*$ordersProduct.products_quantity}
	{$currencies->format($finalQty, true, $order->info.currency, $order->info.currency_value)}{if $isForeignCurrency} /{$currencies->format($finalQty,true,$smarty.const.DEFAULT_CURRENCY)}{/if}
	{if $ordersProduct.onetime_charges}<br />{$currencies->format($ordersProduct.onetime_charges, true, $order->info.currency, $order->info.currency_value)}{if $isForeignCurrency} /{$currencies->format($ordersProduct.onetime_charges,true,$smarty.const.DEFAULT_CURRENCY)}{/if}{/if}
	{assign var=finalQtyPlusTax value=$finalPlusTax*$ordersProduct.products_quantity} 
	{if $ordersProduct.tax}
		{$currencies->format($finalQtyPlusTax,true,$order->info.currency,$order->info.currency_value)}
		{if $ordersProduct.onetime_charges}<br />{$currencies->format($onetimePlusTax,true,$order->info.currency,$order->info.currency_value)}{/if}
		{if $isForeignCurrency} ( {$currencies->format($finalQtyPlusTax,true,$smarty.const.DEFAULT_CURRENCY)} ){/if}
	{/if}
</td>
<td class="dataTableContent alignright" rowspan="2">
	{if $ordersProduct.products_wholesale}
		{math equation="f - ((pw+pc)*q)" f=$finalQty pw=$ordersProduct.products_wholesale pc=$ordersProduct.products_commission q=$ordersProduct.products_quantity assign=wholesaleQty}
		{assign var=wholesaleIncome value=$wholesaleIncome+$wholesaleQty}
		{assign var=wholesaleCost value=$wholesaleCost+$ordersProduct.products_wholesale*$ordersProduct.products_quantity}
		<strong>{$currencies->format($wholesaleQty,true,$order->info.currency, $order->info.currency_value)}</strong>&nbsp;
		{if $gBitUser->hasPermission('p_admin') && $ordersProduct.products_cogs!=$ordersProduct.products_wholesale}
			{math equation="(w - c)*q" w=$ordersProduct.products_wholesale c=$ordersProduct.products_cogs q=$ordersProduct.products_quantity assign=cogsQty}
			<br/>[<strong>{$currencies->format($cogsQty,true,$order->info.currency, $order->info.currency_value)}</strong>]
			{assign var=baseCost value=$ordersProduct.products_cogs*$ordersProduct.products_quantity}
			<br/>({$currencies->format($baseCost,true,$order->info.currency, $order->info.currency_value)})
		{/if}
	{/if}
	{*if $ordersProduct.products_wholesale}
		{if $gBitUser->hasPermission('p_admin') && $ordersProduct.products_cogs!=$ordersProduct.products_wholesale}
			{math equation="(pw-pc)*pq" pw=$ordersProduct.products_wholesale pc=$ordersProduct.products_cogs pq=$ordersProduct.products_quantity assign=supplierCost}
			<br/>+ ({$currencies->format($supplierCost,true,$order->info.currency, $order->info.currency_value)})
		{/if}
		<br/>({$currencies->format($wholesaleCost,true,$order->info.currency, $order->info.currency_value)})
		{math equation="fp-pc-pw" fp=$ordersProduct.final_price pc=$ordersProduct.products_commission pw=$ordersProduct.products_wholesale assign=productWholesaleGross}
		{if $gBitUser->hasPermission('p_admin') && $ordersProduct.products_cogs!=$ordersProduct.products_wholesale}
			{math equation="pw-pc" fp=$ordersProduct.final_price pw=$ordersProduct.products_wholesale pc=$ordersProduct.products_cogs assign=productSupplierGross}
			<br/>^{$currencies->format($productSupplierGross,true,$order->info.currency, $order->info.currency_value)}
		{/if}
		<br/>[{$currencies->format($productWholesaleGross,true,$order->info.currency, $order->info.currency_value)}]
		{if $gBitUser->hasPermission('p_admin') && $ordersProduct.products_cogs!=$ordersProduct.products_wholesale}
			^({$currencies->format($ordersProduct.products_cogs,true,$order->info.currency, $order->info.currency_value)})
			{math equation="(pw-pc)" pw=$ordersProduct.products_wholesale pc=$ordersProduct.products_cogs assign=supplierCost}
			<br/>^({$currencies->format($supplierCost,true,$order->info.currency, $order->info.currency_value)})
		{/if}
		{assign var=wholesaleUnitCost value=$wholesaleCost+$ordersProduct.products_wholesale}
		<br/>({$currencies->format($ordersProduct.products_wholesale,true,$order->info.currency, $order->info.currency_value)})
	{/if*}
</td>
</tr>
<tr class="dataTableRow">
	<td></td>
	<td class="dataTableContent" colspan="3">
{if !empty( $ordersProduct.attributes )}
<ul class="unstyled">
{section loop=$ordersProduct.attributes name=a}
		<li class="orders products attributes" id="{$ordersProduct.attributes[a].products_attributes_id}att">
			<small>{$ordersProduct.attributes[a].option}: {$ordersProduct.attributes[a].value}
				{assign var=sumAttrPrice value=$ordersProduct.attributes[a].final_price*$ordersProduct.products_quantity}
				{if $ordersProduct.attributes[a].price}({$ordersProduct.attributes[a].prefix}{$currencies->format($sumAttrPrice,true,$order->info.currency,$order->info.currency_value)}){/if}
				{if !empty($ordersProduct.attributes[a].product_attribute_is_free) && $ordersProduct.attributes[a].product_attribute_is_free == '1' and $ordersProduct.product_is_free == '1'}<span class="alert">{tr}FREE{/tr}</span>{/if}
			</small> <a class="icon" href="{$smarty.server.REQUEST_URI}&amp;del_ord_prod_att_id={$ordersProduct.attributes[a].orders_products_attributes_id}" onclick="return deleteOption({$ordersProduct.attributes[a].orders_products_attributes_id},'{$ordersProduct.attributes[a].option|escape:'quotes'|escape:'htmlall'}: {$ordersProduct.attributes[a].value|escape:'quotes'|escape:'htmlall'}');"><i class="icon-trash"></i></a>
		</li>
{/section}
</ul>
{/if}
		<form class="form-inline condensed" method="post" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php">
			<input type="hidden" name="oID" value="{$smarty.request.oID}"/>
			<input type="hidden" name="action" value="save_new_option"/>
			<input type="hidden" name="orders_products_id" value="{$ordersProduct.orders_products_id}"/>
			{html_options name="newOrderOptionType" options=$optionsList id="neworderoption`$ordersProduct.orders_products_id`" onchange="getNewOption(`$ordersProduct.orders_products_id`);" selected="0"}
			<span id="neworderattr{$ordersProduct.orders_products_id}"></span>
		</form>
	</td>
</tr>

{/foreach}
{section loop=$order->totals name=t}
<tr>
	<td colspan="3" class="alignright {'ot_'|str_replace:'':$order->totals[t].class} text">
		{$order->totals[t].title}
		{if $order->totals[t].class=='ot_shipping'}
			<a class="icon" onclick="getShippingQuotes({$smarty.request.oID});return false;"><i class="icon-edit"></i></a>
				<span id="shippingquote"></span>
		{/if}
	</td>
	<td class="alignright {'ot_'|str_replace:'':$order->totals[t].class} value">
		{$currencies->format($order->totals[t].orders_value)} {if $isForeignCurrency}{$currencies->format($order->totals[t].orders_value,true,$smarty.const.DEFAULT_CURRENCY)}{/if}
	</td>
	<td class="alignright {'ot_'|str_replace:'':$order->totals[t].class} value">
		{if $order->totals[t].class=='ot_subtotal'}
			 {$currencies->format($wholesaleIncome)}
		{elseif $order->totals[t].class=='ot_total'}
			{math equation="i - c - g" i=$wholesaleIncome c=$couponAmount g=$giftAmount assign=wholesaleNet}
			<span class="{if $wholesaleNet>0}success{else}error{/if}">{$currencies->format($wholesaleNet)} {if $isForeignCurrency}({$currencies->format($wholesaleNet,true,$smarty.const.DEFAULT_CURRENCY)}{/if}</span>
		{elseif $order->totals[t].class=='ot_gv'}
			({$currencies->format($order->totals[t].orders_value)}) {if $isForeignCurrency}({$currencies->format($order->totals[t].orders_value,true,$smarty.const.DEFAULT_CURRENCY)}){/if}
			{assign var=giftAmount value=$giftAmount-$order->totals[t].orders_value}
		{elseif $order->totals[t].class=='ot_coupon'}
			({$currencies->format($order->totals[t].orders_value)}) {if $isForeignCurrency}({$currencies->format($order->totals[t].orders_value,true,$smarty.const.DEFAULT_CURRENCY)}){/if}
			{assign var=couponAmount value=$couponAmount+$order->totals[t].orders_value}
		{/if}
	</td>
{/section}

{php}
	// show downloads
	require(DIR_WS_MODULES . 'orders_download.php');
{/php}
</table>

{jstabs}
{foreach from=$fulfillmentFiles item=fulfillmentFile}
	{include_php file=$fulfillmentFile}
{/foreach}
{/jstabs}


<div style="margin:15px 0;">
	<a class="btn" href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}invoice.php?oID={$smarty.request.oID}">{tr}Invoice{/tr}</a>
	<a class="btn" href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}packingslip.php?oID={$smarty.request.oID}">{tr}Packing Slip{/tr}</a>
	<form class="form-inline inline" method="post" action="{$smarty.server.BITCOMMERCE_PKG_ADMIN_URI}gv_mail.php">
		<input type="hidden" name="email_to" value="{$order->customer.email_address}" />
		<input type="hidden" name="oID" value="{$smarty.request.oID}" />
		<input class="btn" type="submit" name="Send" value="Send Gift Certificate" />
	</form>
	<a class="btn" href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}orders.php?oID={$smarty.request.oID}&amp;action=delete">{tr}Delete{/tr}</a>
	{form class="form-inline" method="post" action="`$smarty.const.BITCOMMERCE_PKG_ADMIN_URI`orders.php?oID=`$smarty.request.oID`&amp;action=combine"}
		{tr}Combine with order{/tr}: <input type="text" name="combine_order_id" class="input-small"/>
		<label class="checkbox">
			<input type="checkbox" name="combine_notify" value="on" checked="checked"/>{tr}Notify Customer{/tr}
		</label>
		<input class="btn btn-small" type="submit" name="combine" value="{tr}Combine{/tr}"/>
		<div><small>Both orders must have status {$smarty.const.DEFAULT_ORDERS_STATUS_ID|zen_get_order_status_name}. This order will deleted.</small></div>
	{/form}
</div>

{/strip} 

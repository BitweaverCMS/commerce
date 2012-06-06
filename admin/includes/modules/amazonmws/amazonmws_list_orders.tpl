{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

{formfeedback hash=$feedback}
{strip}
<div class="admin bitcommerce coupons">
	<div class="header">
		<h1>{tr}Amazon Orders{/tr}</h1>
	</div>

	<div class="body">

		<table class="data">
		<tr >
			<th>Amazon Order ID</th>
			<th>Customer</th>
			<th colspan="2">Items / Shipped <div class="floatright">Product + Ship</div></th>
			<th></th>
		</tr>

		{foreach from=$orderList item=azOrder name=orderList}
		<tr class="{if $azOrder->getOrderStatus() != 'Shipped'}active{/if}">
			<td>
				{$azOrder->getAmazonOrderId()}<div class="date">{$azOrder->getPurchaseDate()|strtotime|bit_short_datetime}</div>
				<div>
				{assign var=localOrdersId value=$azOrder->getAmazonOrderId()|amazon_order_is_processed }
				{if $localOrdersId}
					<strong><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$localOrdersId}">{$localOrdersId}</a></strong>
				{else}
					{form}
						<input type="hidden" name="amazon_order_id" value="{$azOrder->getAmazonOrderId()}"/>
						<input type="submit" class="minibutton" value="{tr}Process{/tr}" name="{tr}mws_process_order{/tr}"/>
					{/form}
				{/if}
				</div>
			</td>
			<td>
				{if $azOrder->isSetShippingAddress()}
{assign var=shippingAddress value=$azOrder->getShippingAddress()}
<h3>{$shippingAddress->getName()}</h3>
{$shippingAddress->getAddressLine1()},
{$shippingAddress->getAddressLine2()}
{$shippingAddress->getAddressLine3()} 
{$shippingAddress->getCity()}, {$shippingAddress->getCounty()}  {$shippingAddress->getDistrict()} {$shippingAddress->getStateOrRegion()} {$shippingAddress->getPostalCode()} {$shippingAddress->getCountryCode()}
<div>{$shippingAddress->getPhone()}</div> 
				{/if} 
</td>
			<td class="numeric">{math equation="s+u" s=$azOrder->getNumberOfItemsShipped() u=$azOrder->getNumberOfItemsUnshipped()} / {$azOrder->getNumberOfItemsUnshipped()}</td>
			<td>
				{assign var=azOrderItems value=$azOrder->getAmazonOrderId()|amazon_mws_get_order_items}
				{assign var=azOrderItem value=$azOrderItems->getOrderItem()}
				<ol class="data">
				{foreach from=$azOrderItem item=azi}
					<li class="item">
						{if $azi->getQuantityOrdered()}{$azi->getQuantityOrdered()} x {/if}
						<a href="{$gBitProduct->getDisplayUrlFromHash($azi->getSellerSKU())}">{$azi->getSellerSKU()} {$azi->getTitle()|escape}</a>
						{assign var=lineTotal value=0}
						<div class="floatright">
							{if $azi->getItemPrice()}{assign var=itemPrice value=$azi->getItemPrice()}{$itemPrice->getAmount()}{assign var=lineTotal value=$lineTotal+$itemPrice->getAmount()}{/if} 
							{if $azi->isSetShippingPrice()} + {assign var=shippingPrice value=$azi->getShippingPrice()}{$shippingPrice->getAmount()}{assign var=lineTotal value=$lineTotal+$itemPrice->getAmount()}{/if}
							{if $lineTotal} = {$lineTotal} {$itemPrice->getCurrencyCode()}{/if}
						</div>
					</li>
				{/foreach}
				</ol>
			</td>
		</tr>
		{/foreach}
		</table>

		{include file="bitpackage:bitcommerce/commerce_pagination.tpl"}
	</div>

</div>
{/strip}

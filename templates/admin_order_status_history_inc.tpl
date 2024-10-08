{assign var=hasHistory value=$gBitOrder->loadHistory()}
{assign var=lastStatus value=$gBitOrder->mHistory|count-1}
{formfeedback error=$gBitOrder->mErrors.status}
<form class="status" name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/orders.php?oID={$smarty.request.oID}&amp;action=update_order" method="post"><div style="display:inline">
{legend legend="Order History"}
	<label>{tr}Change Status{/tr}</label> {html_options class="form-control" name='status' options=$orderStatuses selected=$gBitOrder->getStatus()}
	<label>{tr}Comments{/tr}</label>
	<textarea class="form-control" name="comments" wrap="soft">{$smarty.request.comments|escape}</textarea>
	{forminput label="checkbox"}
		<input name="notify" type="checkbox"> {booticon iname="fa-envelope" iexplain="Notified"} {tr}Notify Customer{/tr} 
	{/forminput}
	{forminput label="checkbox"}
		<input type="checkbox" name="adjust_total" value="y" onclick="$('#additional-charge').toggle()" id="adjust_total"/>{booticon iname="fa-money-check-dollar-pen"} {tr}Adjust Order Total{/tr}
	{/forminput}
	<div id="additional-charge" style="display:none">
		{forminput label="checkbox"}
			<input type="checkbox" name="additional_charge" value="y" checked/>{booticon iname="fa-cash-register"} {tr}Make Additional Charge{/tr}
		{/forminput}
		{forminput id="charge-amount"}
			{assign var=leftSymbol value=$gCommerceCurrencies->getLeftSymbol( $gBitOrder->getField('currency') )}
			{assign var=rightSymbol value=$gCommerceCurrencies->getRightSymbol( $gBitOrder->getField('currency') )}
			{if $gBitOrder->getField('currency') && $gBitOrder->getField('currency') != $smarty.const.DEFAULT_CURRENCY}
			<input type="hidden" name="charge_currency" value="{$gBitOrder->getField('currency')}"/>
			<input type="hidden" name="currency_value" value="{$gBitOrder->getField('currency_value')}"/>
			{/if}
<select name="payment_ref_id" class="form-control">
{foreach from=$order->mPayments item=paymentHash}
	<option value="{$paymentHash.payment_ref_id}">{$paymentHash.payment_ref_id}  =  {$paymentHash.payment_amount} {$paymentHash.payment_currency}</option>
{/foreach}
</select>
			<div class="input-group">
				{if $leftSymbol}<span class="input-group-addon">{$leftSymbol}</span>{/if}
				<input class="form-control input-sm text-right" type="text" name="charge_amount" value="{$smarty.request.charge_amount}"/>
				{if $rightSymbol}<span class="input-group-addon">{$rightSymbol}</span>{/if}
				<span class="input-group-addon">
				</span>
			</div>
			{formhelp note="Enter a negative number for a credit"}
		{/forminput}
	</div>
	<input type="hidden" value="{$gBitOrder->mHistory.$lastStatus.orders_status_history_id}" name="last_status_id"/>
			
	<input type="submit" class="btn btn-default" value="{tr}Update{/tr}" name="{tr}Update{/tr}"/>
{/legend}
</div></form>
{if $hasHistory}
<ul class="list-unstyled orderhistory data">
	{section loop=$gBitOrder->mHistory name=ix step=-1}
	<li class="item {if $gBitOrder->mHistory[ix].customer_notified == '1'}alert alert-info{/if}" style="clear:both"> 
		<small class="floatright">
			{if $gBitOrder->mHistory[ix].customer_notified == '1'}
				{booticon iname="fa-envelope" iexplain="Notified"}
			{/if}
			{*if $gBitUser->isAdmin()}
				<a href="{$smarty.server.REQUEST_URI}&delete_status={$gBitOrder->mHistory[ix].orders_status_history_id}">{booticon iname="fa-trash"}</a>
			{/if*}
			{$gBitOrder->mHistory[ix].date_added|date_format:"%m-%d %H:%M"}
		</small>

		<div class="status">
			<strong>{$gBitOrder->mHistory[ix].orders_status_name}</strong> by {displayname hash=$gBitOrder->mHistory[ix]}
		</div>
		{if $gBitOrder->mHistory[ix].comments}
			<p>{$gBitOrder->mHistory[ix].comments|nl2br}</p>
		{/if}
		</li>
	{sectionelse} 
		<li class="item">{tr}No Order History{/tr}</li>
	{/section}
</ul>
{/if}	


<h2>{tr}Customers Interests{/tr}</h2>
{include file="bitpackage:bitcommerce/admin_interests_customer_inc.tpl"}

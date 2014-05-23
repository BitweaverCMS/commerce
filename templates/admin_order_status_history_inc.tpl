<form class="status" name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/orders.php?oID={$smarty.request.oID}&amp;origin=index&amp;action=update_order" method="post"><div style="display:inline">
{legend legend="Order History"}
	<label>{tr}Change Status{/tr}</label> {html_options name='status' options=$orderStatuses selected=$gBitOrder->getStatus()}
	<label>{tr}Comments{/tr}</label>
	<textarea name="comments" wrap="soft">{$smarty.request.comments|escape}</textarea>
	<label class="checkbox">
		<input name="notify" type="checkbox"> {booticon iname="icon-envelope" iexplain="Notified"} {tr}Notify Customer{/tr} 
	</label>
	<label class="checkbox">
		<input type="checkbox" name="update_totals" value="y" onclick="$('#additional-charge').toggle()"/><i class="icon-money"></i> {tr}Make Additional Charge{/tr}
	</label>
		<label class="checkbox hide" id="additional-charge">
			<div class="control-group">
				{forminput}
					{assign var=leftSymbol value=$gCommerceCurrencies->getLeftSymbol()}
					{assign var=rightSymbol value=$gCommerceCurrencies->getRightSymbol()}
					<div class="{if $leftSymbol}input-prepend{/if} {if $rightSymbol}input-append{/if}">
						{if $leftSymbol}<span class="add-on">{$leftSymbol}</span>{/if}
						<input class="input-small text-right" id="appendedPrependedInput" type="text" name="additional_charge" value="{$smarty.request.additional_charge}"/>
						{if $rightSymbol}<span class="add-on">{$rightSymbol}</span>{/if}
					</div>
					{formhelp note="Enter a negative number for a credit"}
				{/forminput}
			</div>
		</label>
			
	<input type="submit" class="btn btn-default" value="{tr}Update{/tr}" name="{tr}Update{/tr}"/>
{/legend}
</div></form>
{if $gBitOrder->loadHistory()}
<ul class="unstyled orderhistory data">
	{section loop=$gBitOrder->mHistory name=ix step=-1}
	<li class="item {if $gBitOrder->mHistory[ix].customer_notified == '1'}alert alert-info{/if}" style="clear:both"> 
		<small class="floatright">
			{if $gBitOrder->mHistory[ix].customer_notified == '1'}
				{booticon iname="icon-envelope" iexplain="Notified"}
			{/if}
			{if $gBitUser->isAdmin()}
				<a href="{$smarty.server.REQUEST_URI}&delete_status={$gBitOrder->mHistory[ix].orders_status_history_id}">{booticon iname="icon-trash"}</a>
			{/if}
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

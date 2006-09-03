<h2>{tr}Order Status History{/tr}</h2>
<form name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/orders.php?oID={$smarty.request.oID}&amp;origin=index&amp;action=update_order" method="post">
	<strong>{tr}Status{/tr}</strong> {html_options name='status' options=$orderStatuses selected=$gBitOrder->getStatus()}
	<br/><strong>{tr}Comments{/tr}</strong>
	<br/><textarea name="comments" wrap="soft" cols="60" rows="5"></textarea>
	<br/><strong>{tr}Notify Customer{/tr}</strong> <input name="notify" type="checkbox">
	<input type="submit" value="{tr}Update{/tr}" name="{tr}Update{/tr}"/>
</form>
{if $gBitOrder->loadHistory()}
<ul class="data">
	{section loop=$gBitOrder->mHistory name=ix step=-1}
	<li class="item" style="clear:both"> 
		{if $gBitOrder->mHistory[ix].customer_notified == '1'}
			<img src="{$smarty.const.BITCOMMERCE_PKG_URL}icons/tick.gif" alt="Notified" />
		{/if}
		<strong>{$gBitOrder->mHistory[ix].orders_status_name}</strong> by {displayname hash=$gBitOrder->mHistory[ix]}
		<div class="date">{$gBitOrder->mHistory[ix].date_added}</div>
		{if $gBitOrder->mHistory[ix].comments}
			{$gBitOrder->mHistory[ix].comments|nl2br}
		{/if}
		</li>
	{sectionelse} 
		<li class="item">{tr}No Order History{/tr}</li>
	{/section}
</ul>
{/if}	

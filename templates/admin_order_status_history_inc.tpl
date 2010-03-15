<h2>{tr}Order Status History{/tr}</h2>
<form class="status" name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/orders.php?oID={$smarty.request.oID}&amp;origin=index&amp;action=update_order" method="post"><div style="display:inline">
	<strong>{tr}Status{/tr}</strong> {html_options name='status' options=$orderStatuses selected=$gBitOrder->getStatus()}
	<br/><strong>{tr}Comments{/tr}</strong>
	<br/><textarea name="comments" wrap="soft"></textarea>
	<br/> {biticon iname="internet-mail" iexplain="Notified"} <strong>{tr}Notify Customer{/tr}</strong> <input name="notify" type="checkbox">
	<input type="submit" class="minibutton" value="{tr}Update{/tr}" name="{tr}Update{/tr}"/>
</div></form>
{if $gBitOrder->loadHistory()}
<ul class="orderhistory data">
	{section loop=$gBitOrder->mHistory name=ix step=-1}
	<li class="item {if $gBitOrder->mHistory[ix].customer_notified == '1'}notified{/if}" style="clear:both"> 
		<div class="date">
			{if $gBitOrder->mHistory[ix].customer_notified == '1'}
				{biticon iname="internet-mail" iexplain="Notified"}
			{/if}
			{if $gBitUser->isAdmin()}
				<a href="{$smarty.server.REQUEST_URI}&delete_status={$gBitOrder->mHistory[ix].orders_status_history_id}">{biticon iname="edit-delete"}</a>
			{/if}
			{$gBitOrder->mHistory[ix].date_added|date_format:"%m-%d %H:%M"}
		</div>

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

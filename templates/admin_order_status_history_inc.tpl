<h2>{tr}Order Status History{/tr}</h2>
<form class="status" name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}/admin/orders.php?oID={$smarty.request.oID}&amp;origin=index&amp;action=update_order" method="post"><div style="display:inline">
	<strong>{tr}Status{/tr}</strong> {html_options name='status' options=$orderStatuses selected=$gBitOrder->getStatus()}
	<br/><strong>{tr}Comments{/tr}</strong>
	<br/><textarea name="comments" wrap="soft"></textarea>
	<br/> {biticon iname="internet-mail" iexplain="Notified"} <strong>{tr}Notify Customer{/tr}</strong> <input name="notify" type="checkbox">
	<input type="submit" value="{tr}Update{/tr}" name="{tr}Update{/tr}"/>
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
<ul class="data">
{foreach from=$customersInterests key=interestsId item=interest}
	<li class="item"><input type="checkbox" name="interersts_id" onchange="storeCustomerInterest(this.value,this.checked)" value="{$interestsId}" {if $interest.is_interested}checked="checked"{/if}/>{$interest.interests_name}</li>
{/foreach}
</ul>
<div id="interestsfeedback"></div>
{literal}
<script type="text/javascript">/* <![CDATA[ */
function storeCustomerInterest( pInterestsId, pChecked ) {
console.log( pChecked );
	var action = pChecked ? 'savec2i' : 'deletec2i';
	jQuery.ajax({
		data: 'action='+action+'&interests_id='+pInterestsId+'&customers_id='+{/literal}{$order->customer.id}{literal},
		url: "{/literal}{$smarty.const.BITCOMMERCE_PKG_URL}admin/interests.php{literal}",
		timeout: 60000,
		success: function(r) { 
			$('#interestsfeedback').html(r);
		}
	})
}
/* ]]> */</script>
{/literal}

       <h3>{tr}Status History & Comments{/tr}</h3>
       <ul class="data">
{foreach from=$orderHistory key=i item=history}
 		{if $history.customer_notified || $gBitUser->hasPermission( 'bit_p_commerce_admin' )}
 		<li class="item">
 			{if $history.customer_notified}<span class="warning">{/if}
			{$history.date_added|bit_short_datetime} - {$history.orders_status_name}
			{if $history.comments}
			<br/><strong>{tr}NOTE{/tr}:</strong> {$history.comments}
			{/if}
	 		{if $history.customer_notified}</span>{/if}
       </li>
       {/if}
{/foreach}

{if $gBitUser->hasPermission( 'bit_p_commerce_admin' )}
	* <em class="warning">{tr}Customer cannot see these comments{/tr}</em>
{/if}
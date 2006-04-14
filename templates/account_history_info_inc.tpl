       <h3>{tr}Status History & Comments{/tr}</h3>
       <ul class="data">
{section loop=$orderHistory name=ix }
 		{if $orderHistory[ix].customer_notified || $gBitUser->hasPermission( 'p_commerce_admin' )}
 		<li class="item">{$orderHistory[ix].customer_notified}
 			{if !$orderHistory[ix].customer_notified}<span class="warning">{/if}
			{$orderHistory[ix].date_added|bit_short_datetime} - {$orderHistory[ix].orders_status_name}
			{if $orderHistory[ix].comments|escape:"html"}
			<br/><strong>{tr}NOTE{/tr}:</strong> {$orderHistory[ix].comments}
			{/if}
	 		{if !$orderHistory[ix].customer_notified}</span>{/if}
       </li>
       {/if}
{/section}

{if $gBitUser->hasPermission( 'p_commerce_admin' )}
	* <em class="warning">{tr}Customer cannot see these comments{/tr}</em>
{/if}

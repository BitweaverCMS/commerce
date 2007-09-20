
<table>
<tr>
	<td valign="top">
		{$order->info.date_purchased|bit_long_datetime}<br/>
		{displayname hash=$order->customer} (ID: {$order->customer.user_id} <a href="list_orders.php?user_id={$order->customer.user_id}&amp;orders_status_id=all&amp;list_filter=all">orders</a> <a href="product_history.php?user_id={$order->customer.user_id}"><img src="/themes/icon_styles/tango/small/appointment-new.png" title="Users Products History" alt="H" /></a>)<br/>
{if $order->customer.telephone}
	{$order->customer.telephone}<br/>
{/if}
		<a href="mailto:{$order->customer.email_address}">{$order->customer.email_address}</a><br/>
		IP: {$order->info.ip_address}<br/>
		{$order->info.payment_method}
		</td>
	</td>
	<td>


		<table style="width:auto;">
		{if $order->info.cc_type || $order->info.cc_owner || $order->info.cc_number}
			  <tr>
				<td colspan="2"><strong>Credit Card Info</strong></td>
			  </tr>
			  <tr>
				<td class="main">Type:</td>
				<td class="main">{$order->info.cc_type}</td>
			  </tr>
			  <tr>
				<td class="main">Owner:</td>
				<td class="main">{$order->info.cc_owner}</td>
			  </tr>
			  <tr>
				<td class="main">Number:</td>
				<td class="main">{$order->info.cc_number}</td>
			  </tr>
			  <tr>
				<td class="main">CVV:</td>
				<td class="main">{$order->getField('cc_cvv')}</td>
			  </tr>
			  <tr>
				<td class="main">Expires:</td>
				<td class="main">{$order->info.cc_expires}</td>
			  </tr>
		{/if}
		</table>
	</td>
	</tr>
	<tr>
		<td valign="top">
			<strong>{tr}Shipping Address{/tr}</strong><br/>
{php}
global $order;
echo zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />');
{/php}

		</td>
		<td valign="top"><table>
			<strong>{tr}Billing Address{/tr}</strong><br/>
{php}
global $order;
echo zen_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />');
{/php}
		</td>
	  </tr>
	</table>

		</td>
	</tr>
	{if $notificationBlock}
	<tr>
		<td>
			{$notificationBlock}
		</td>
	</tr>
	{/if}
</table>

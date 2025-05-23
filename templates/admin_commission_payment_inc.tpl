{if !$commission.payment_method}
	<div class="form-group">
		{formlabel label="Payment Method"}
		{forminput}
			{$commission.payment_method|default:"Not Set"}
			<p>The customer has not chosen a method to receive commission payments.</p>
		{/forminput}
	</div>
{else}
	{if $commission.payment_method == 'paypal'}
		<div class="form-group">
			{formlabel label="Make Payment"}
			{forminput}
				{if $commission.commissions_paypal_address}
<!--				<a target="_new" href="http://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business={$commission.commission_paypal_address}&amp;amount={$commission.commission_sum}&amp;item_name=">Send PayPal payment</a> -->

				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_new">
					<input type="hidden" name="cmd" value="_xclick" />
					<input type="hidden" name="business" value="{$commission.commissions_paypal_address}" />
					<input type="hidden" name="item_name" value="{$commission.commission_type|ucwords} Payments Through {$commission.period_end_date} ( {$commission.user_id} )" />
					<input type="hidden" name="item_number" value="{$commission.user_id}" />
					<input type="hidden" name="amount" value="{$commission.commission_sum|round:2}" />
					<input type="hidden" name="no_shipping" value="1" />
					<input type="hidden" name="no_note" value="1" />
					<input type="hidden" name="currency_code" value="USD" />
					<input type="hidden" name="lc" value="US" />
					<input type="hidden" name="bn" value="PP-BuyNowBF" />
					<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but02.gif" border="0" name="submit" alt="Send Commission Payment" />
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
				</form>
				{else}
					<span class="error">{tr}No PayPal Email.{/tr}</span>
				{/if}
				<p class="warning">{tr}You must click the PayPal icon above to send the commission payment.{/tr}</p>
			{/forminput}
		</div>
	{elseif $commission.payment_method == 'check'}
		<div class="form-group">
			{formlabel label="Check Number"}
			{forminput}
				<input type="text" name="payment_reference_number" value="" />
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Check Address"}
			{forminput}
			{$commission.user_id|@zen_address_label:$commission.commissions_check_address:true}
			{/forminput}
		</div>
	{else}
		<div class="form-group">
			{forminput}
				{$commission.payment_method}
			{/forminput}
		</div>
	{/if}
	{form name="paymentform`$userId`" class="form-horizontal" action=$smarty.server.REQUEST_URI}
	<input type="hidden" name="payment_method" value="{$commission.payment_method}" />
	<input type="hidden" name="user_id" value="{$userId}" />


		<div class="form-group">
			{formlabel label="Commission Amount"}
			{forminput}
				<input type="text" class="form-control" name="payment_amount" value="{$commission.commission_sum|string_format:"%.2f"}" />
			{/forminput}
		</div>
<div></div>
		<div class="form-group clear">
			{formlabel label="From"}
			{forminput}
				<input type="date" class="form-control" name="period_start_date" id="periodstart{$userId}" value="{$commission.last_period_end_date}">
			{/forminput}
		</div>
		<div class="form-group clear">
			{formlabel label="Through"}
			{forminput}
				<input type="date" class="form-control" name="period_end_date" id="periodend{$userId}" value="{$commission.period_end_date}"/>
			{/forminput}
		</div>

		<div class="form-group">
			{formlabel label="Note"}
			{forminput}
				<input type="text" class="form-control" name="payment_note" value="" />
				{formhelp note="For administrative purposes only. This note will NOT be visible to the payee."}
			{/forminput}
		</div>

		<div class="form-group submit">
			{formlabel label=""}
			{forminput}
				<input type="submit" class="btn btn-default" name="save_payment" value="Save Payment" />
			{/forminput}
		</div>
	{/form}
{/if}

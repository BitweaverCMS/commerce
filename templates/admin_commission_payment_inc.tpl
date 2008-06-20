
		{if !$commission.payment_method}
			<div class="row">
				{formlabel label="Payment Method"}
				{forminput}
					{$commission.payment_method|default:"Not Set"}
					<p>The customer has not chosen a method to receive commission payments.</p>
				{/forminput}
			</div>
		{else}
			{if $commission.payment_method == 'paypal'}
				<div class="row">
					{formlabel label="Make Payment"}
					{forminput}
						{if $commission.commissions_paypal_address}
		<!--				<a target="_new" href="http://www.paypal.com/cgi-bin/webscr?cmd=_xclick&amp;business={$commission.commission_paypal_address}&amp;amount={$commission.commission_sum}&amp;item_name=">Send PayPal payment</a> -->

						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_new">
							<input type="hidden" name="cmd" value="_xclick" />
							<input type="hidden" name="business" value="{$commission.commissions_paypal_address}" />
							<input type="hidden" name="item_name" value="Payments Through {$periodEndDate} ( {$commission.user_id} )" />
							<input type="hidden" name="item_number" value="{$commission.user_id}" />
							<input type="hidden" name="amount" value="{$commission.commission_sum}" />
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
				<div class="row">
					{formlabel label="Check Number"}
					{forminput}
						<input type="text" name="payment_reference_number" value="" />
					{/forminput}
				</div>
			{else}
				<div class="row">
					{forminput}
						{$commission.payment_method}
					{/forminput}
				</div>
			{/if}
			{form name="paymentform`$userId`" action=$smarty.server.REQUEST_URI}
			<input type="hidden" name="payment_method" value="{$commission.payment_method}" />
			<input type="hidden" name="user_id" value="{$userId}" />


				<div class="row">
					{formlabel label="Commission Amount"}
					{forminput}
						<input type="text" name="payment_amount" value="{$commission.commission_sum|string_format:"%.2f"}" />
					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Payment Dates"}
					{forminput}
						{tr}From{/tr} <input type="text" name="period_start_date"  id="periodstart{$userId}" value="{$commission.last_period_end_date}" style="width:80px"/> 
	<script language="JavaScript">
	calPeriodStart{$userId}= new CalendarPopup("caldiv");
	</script>
						<a href="#" onclick="calPeriodStart{$userId}.select($('periodstart{$userId}'),'anchorperiodstart{$userId}','yyyy-MM-dd'); return false;" title="calPeriodStart{$userId}.select(document.paymentform{$userId}.period_start_date,'anchorperiodstart{$userId}','yyyy-MM-dd'); return false;" name="anchorperiodstart{$userId}" id="anchorperiodstart{$userId}"><img src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/calendar_mini.png" alt="Choose Date"></img></a>

	{tr}Through{/tr} <input type="text" name="period_end_date" id="periodend{$userId}" value="{$periodEndDate}" style="width:80px"/>
	<script language="JavaScript">
	calPeriodEnd{$userId}= new CalendarPopup("caldiv");
	</script>
						<a href="#" onclick="calPeriodEnd{$userId}.select($('periodend{$userId}'),'anchorperiodend{$userId}','yyyy-MM-dd'); return false;" title="calPeriodEnd{$userId}.select(document.paymentform{$userId}.period_end_date,'anchorperiodend{$userId}','yyyy-MM-dd'); return false;" name="anchorperiodend{$userId}" id="anchorperiodend{$userId}"><img src="{$smarty.const.UTIL_PKG_URL}javascript/kruse/calendar_mini.png" alt="Choose Date"></img></a>


					{/forminput}
				</div>

				<div class="row">
					{formlabel label="Note"}
					{forminput}
						<input type="text" name="payment_note" value="" />
						{formhelp note="For administrative purposes only. This note will NOT be visible to the payee."}
					{/forminput}
				</div>

				<div class="row submit">
					<input type="submit" name="save_payment" value="Save Payment" />
				</div>
				{/form}
			{/if}

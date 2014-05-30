{form method="post" enctype="multipart/form-data" action=$smarty.server.REQUEST_URI}
	<input type="hidden" name="tk" value="{$gBitUser->mTicket}" />
	<div class="form-group">
		{formlabel label="Payment Method"}
		{forminput}
			{html_options id="commissions_payment_method" name="commissions_payment_method" options=$paymentOptions selected=$gBitUser->getPreference('commissions_payment_method')  onchange="updatePaymentMethod()" }
			{formhelp note="Select the way in which you would like to receive payments. <a href='`$smarty.const.WIKI_PKG_URL`Selling+Products'>Fees</a> may be required for some payment methods."}
		{/forminput}
	</div>

	<div class="form-group" id="commissionstorecredit"> 
		{formlabel label=""}
		{forminput}
		{/forminput}
	</div>
	<div class="form-group" id="commissionpaypal"> 
		{formlabel label="PayPal Email"}
		{forminput}
				<input type="text" name="commissions_paypal_address" value="{$gBitUser->getPreference('commissions_paypal_address',$gBitUser->getField('email'))}" />
		{/forminput}
	</div>
	<div class="form-group" id="commissionworldpay"> 
		{formlabel label="WorldPay Email"}
		{forminput}
				<input type="text" name="commissions_worldpay_address" value="{$gBitUser->getPreference('commissions_worldpay_address',$gBitUser->getField('email'))}" />
		{/forminput}
	</div>
	<div class="form-group" id="commissioncheck"> 
		{formlabel label="Mailing Address"}
		{forminput}
			{html_options name="commissions_check_address" options=$addressList selected=$defaultAddressId}
		<div style="padding:10px"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=address_book">{tr}Add Address{/tr}</a></div>
		{/forminput}
	</div>


	<div class="form-group submit">
		<input type="submit" class="btn btn-default" name="save_commission_settings" value="Save" />
	</div>
{/form}

<div class="clear"></div>

{literal}
<script type="text/javascript">
function updatePaymentMethod() {
	BitBase.hideById('commissionstorecredit');
	BitBase.hideById('commissionpaypal');
	BitBase.hideById('commissionworldpay');
	BitBase.hideById('commissioncheck');
	methodValue = 'commission'+document.getElementById('commissions_payment_method').value;
	BitBase.showById(methodValue);
	return true;
}

updatePaymentMethod();
</script>

{/literal}

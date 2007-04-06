	<div class="row" id="commissionstorecredit"> 
		{formlabel label=""}
		{forminput}
		{/forminput}
	</div>
	<div class="row" id="commissionpaypal"> 
		{formlabel label="PayPal Email"}
		{forminput}
				<input type="text" name="commissions_paypal_address" value="{$gBitUser->getPreference('commissions_paypal_address',$gBitUser->getField('email'))}" />
		{/forminput}
	</div>
	<div class="row" id="commissionworldpay"> 
		{formlabel label="WorldPay Email"}
		{forminput}
				<input type="text" name="commissions_worldpay_address" value="{$gBitUser->getPreference('commissions_worldpay_address',$gBitUser->getField('email'))}" />
		{/forminput}
	</div>
	<div class="row" id="commissioncheck"> 
		{formlabel label="Mailing Address"}
		{forminput}
			{html_options name="commissions_check_address" options=$addressList selected=$defaultAddressId}
		<div style="padding:10px"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=address_book">{tr}Add Address{/tr}</a></div>
		{/forminput}
	</div>


{form action="shipping_change.php?oID=`$smarty.request.oID`"}	
<input type="hidden" name="tk" value="{$gBitUser->mTicket}">
{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl"}
<div class="form-group">
	{formlabel label="Comments"}
	{forminput}
		<textarea name="comment"></textarea>
		{formhelp note="A summary of changes will automatically be added to the order history. Customer will NOT be notified of changes nor can they see any comment entered below."}
	{/forminput}
</div>
<div class="form-group">
	{forminput}
		{forminput label="checkbox"}
			<input type="checkbox" name="update_totals" value="y" checked="checked"/> {tr}Update Order Totals{/tr}<br/>
		{/forminput}
	{/forminput}
</div>
<div class="form-group submit">
	{forminput}
		<input class="btn btn-default" type="submit" name="change_shipping" value="{tr}Change Shipping{/tr}">
	{/forminput}
</div>
{/form}

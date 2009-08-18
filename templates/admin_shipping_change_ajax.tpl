{form action="shipping_change.php?oID=`$smarty.request.oID`"}	
<input type="hidden" name="tk" value="{$gBitUser->mTicket}" />
{include file="bitpackage:bitcommerce/shipping_quotes_inc.tpl"}
<div class="row">
	{formlabel label="Comments"}
	{forminput}
		{formhelp note="A summary of changes will automatically be added to the order history. Customer will NOT be notified of changes nor can they see any comment entered below."}
		<textarea name="comment"></textarea>
	{/forminput}
</div>
<div class="row">
	{forminput}
		<input type="checkbox" name="update_totals" value="y" checked="checked"/> {tr}Update Order Totals{/tr}<br/>
	{/forminput}
</div>
<div class="row submit">
	{forminput}
		<input type="submit" name="change_shipping" value="{tr}Change Shipping{/tr}" />
	{/forminput}
</div>
{/form}

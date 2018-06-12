{strip}
<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	<div class="heading">
		<h1>{tr}Order Complete!{/tr} {tr}Your Order Number is{/tr}:<a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=account_history_info&amp;order_id={$newOrdersId}">{$newOrdersId}</a></h1>
	</div>
	
	{include file="bitpackage:bitcommerce/order_success.tpl"}
		
	<form name="order" action="{$smarty.server.SCRIPT_NAME}?main_page=checkout_success&amp;action=update" method="post">
		<p>You can view your order history by going to the <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=account">My Account</a> page and by clicking on view all orders.</p>
{if $gCommerceSystem->getConfig('CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS') == '1' && $notifyProducts}
		<fieldset>
		{forminput}
			<p>{tr}Please notify me of updates to the products I have selected below:{/tr}</p>
			<div class="checkbox">{html_checkboxes name='notify' options=$notifyProducts seperator='br/>'}</div>
		{/forminput}
		</fieldset>
{/if}
		<p>{tr}Please direct any questions you have to <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=contact_us">customer service</a>.{/tr}</p>
		<p class="bold">{tr}Thank you for shopping with us!{/tr}</p>
		{if $gvAmount}
			You have funds in your {$smarty.const.TEXT_GV_NAME} Account. If you want you can send those funds by <a class="pageResults" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send"><strong>{tr}email{/tr}</strong></a> {tr}to someone{/tr}.
		{/if}
		<input class="btn btn-sm" name="Continue" value="{tr}Continue{/tr}" type="submit">
	</form>
	{include file="bitpackage:bitcommerce/page_checkout_success_inc.tpl"}
</div>
{/strip}

{strip}
<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	{if $newOrdersId}
	<div class="heading">
		<h1>{tr}Order Complete!{/tr} {tr}Your Order Number is{/tr}:<a href="{'account_history_info'|zen_get_page_url}?order_id={$newOrdersId}">{$newOrdersId}</a></h1>
	</div>
	
	{include file="bitpackage:bitcommerce/order_success.tpl"}
		
	<p>You can view your order history by going to the <a href="{'account'|zen_get_page_url}">My Account</a> page and by clicking on view all orders.</p>

	<form name="order" action="{'checkout_success'|zen_get_page_url:'action=update'}" method="post">
{if $gCommerceSystem->getConfig('CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS') == '1' && $notifyProducts}
		<fieldset>
		{forminput}
			<p>{tr}Please notify me of updates to the products I have selected below:{/tr}</p>
			<div class="checkbox">{html_checkboxes name='notify' options=$notifyProducts seperator='br/>'}</div>
		{/forminput}
		</fieldset>
		<input class="btn btn-sm" name="Continue" value="{tr}Notify{/tr}" type="submit">
{/if}
		<p>{tr}Please direct any questions you have to <a href="{'contact_us'|zen_get_page_url}">customer service</a>.{/tr}</p>
		<p class="bold">{tr}Thank you for shopping with us!{/tr}</p>
		{if $gvAmount}
			You have funds in your {$smarty.const.TEXT_GV_NAME} Account. If you want you can send those funds by <a class="pageResults" href="{'gv_send'|zen_get_page_url}"><strong>{tr}email{/tr}</strong></a> {tr}to someone{/tr}.
		{/if}
	</form>
	{include file="bitpackage:bitcommerce/page_checkout_success_inc.tpl"}
	{include file="bitpackage:bitcommerce/order_invoice_contents_inc.tpl" order=$newOrder}
	{/if}
</div>
{/strip}

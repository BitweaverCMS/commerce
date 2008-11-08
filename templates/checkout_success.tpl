{strip}
<div class="floaticon">{bithelp}</div>
<div class="edit bitcommerce">
	<div class="heading">
		<h1>{tr}Thank You! We Appreciate your Business!{/tr}</h1>
	</div>
	
	{formfeedback success="{tr}Your order has been successfully accepted.{/tr}"}

	{include file="bitpackage:bitcommerce/order_success.tpl"}
		
{if $gCommerceSystem->getConfig('CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS') == '1' && $notifyProducts}
	{tr}Please notify me of updates to the products I have selected below:{/tr}
	<p class="productsNotifications">
		{html_checkboxes name='notify' options=$notifyProducts seperator='br/>'}
	</p>
{/if}

	<div><strong>{tr}Your Order Number is{/tr}:</strong><a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=account_history_info&amp;order_id={$newOrdersId}">{$newOrdersId}</a></div>
	<p>You can view your order history by going to the <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=account">My Account</a> page and by clicking on view all orders.</p>
	<p>{tr}Please direct any questions you have to <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=contact_us">customer service</a>.{/tr}</p>
	
	<h3>{tr}Thanks for shopping with us online!{/tr}</h3>
	{if $gvAmount}
		You have funds in your {$smarty.const.TEXT_GV_NAME} Account. If you want you can send those funds by <a class="pageResults" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send"><strong>{tr}email{/tr}</strong></a> {tr}to someone{/tr}.
	{/if}
	
	<form name="order" action="{$smarty.server.PHP_SELF}?main_page=checkout_success&amp;action=update" method="post">

	{if $gCommerceSystem->getConfig('DOWNLOAD_ENABLED') == 'true'}
		{include_php file="`$smarty.const.DIR_WS_MODULES`downloads.php"}
	{/if}

	<input class="button" name="Continue" value="{tr}Continue{/tr}" type="submit">

	</form>
</div>
{/strip}

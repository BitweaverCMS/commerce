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
	
	<form name="order" action="{$smarty.server.SCRIPT_NAME}?main_page=checkout_success&amp;action=update" method="post">

	{if $gCommerceSystem->getConfig('DOWNLOAD_ENABLED') == 'true'}
		{include_php file="`$smarty.const.DIR_WS_MODULES`downloads.php"}
	{/if}

	<input class="btn btn-small" name="Continue" value="{tr}Continue{/tr}" type="submit">

	</form>

	{if $gBitSystem->isLive() && $smarty.const.IS_LIVE && $gBitSystem->getConfig('google_analytics_ua')}
		{php}
		global $newOrdersId, $gBitUser, $gBitSystem, $newOrder;
		require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceOrder.php' );
		$newOrder = new order( $newOrdersId );
		{/php}

		<script src="https://ssl.google-analytics.com/urchin.js" type="text/javascript"></script>
		<script type="text/javascript">
			_uacct = "{$gBitSystem->getConfig('google_analytics_ua')}";
			urchinTracker();
		</script>

		<form style="display:none;" name="utmform">
			<textarea style="display:none;" id="utmtrans">{php}
			global $newOrder, $newOrdersId;

			// UTM:T|[orders-id]|[affiliation]|[total]|[tax]|[shipping]|[city]|[state]|[country]
			// UTM:I|[orders-id]|[sku/code]|[productname]|[category]|[price]|[quantity]

			print "UTM:T|$newOrdersId|".$gBitUser->getPreference('affiliate_code',$gBitSystem->getConfig('site_title'))."|".$newOrder->getField('total')."|".$newOrder->getField('tax')."|".$newOrder->getField('shipping_total')."|".$newOrder->delivery['city']."|".$newOrder->delivery['state']."|".$newOrder->delivery['country']['countries_name'];
			foreach( $newOrder->contents AS $product ) {
				print "\nUTM:I|$newOrdersId|".$product['id']."|".str_replace( '|', ' ', $product['name'])."|".$product['model']."|".$product['price']."|".$product['products_quantity'];
			}
			{/php}</textarea>
		</form>

		<script type="text/javascript"> 
		__utmSetTrans(); 
		</script>
	{/if}

</div>
{/strip}

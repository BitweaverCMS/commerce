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
		{html_checkboxes name='notify' options=$notifyProducts seperator='br/>'}
	{/forminput}
	</fieldset>
{/if}

	<p>{tr}Please direct any questions you have to <a href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=contact_us">customer service</a>.{/tr}</p>

	<p class="bold">{tr}Thank you for shopping with us!{/tr}</p>
	{if $gvAmount}
		You have funds in your {$smarty.const.TEXT_GV_NAME} Account. If you want you can send those funds by <a class="pageResults" href="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=gv_send"><strong>{tr}email{/tr}</strong></a> {tr}to someone{/tr}.
	{/if}
	
	{if $gCommerceSystem->getConfig('DOWNLOAD_ENABLED') == 'true'}
		{include_php file="`$smarty.const.DIR_WS_MODULES`downloads.php"}
	{/if}

	<input class="btn btn-small" name="Continue" value="{tr}Continue{/tr}" type="submit">

	</form>

	{if $gBitSystem->isLive() && $smarty.const.IS_LIVE}
		{if $gBitSystem->getConfig('google_analytics_ua')}
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
		{if $gBitSystem->getConfig('boostsuite_site_id')}
			{literal}
			<script type="text/javascript">
			var _bsc = _bsc || {"suffix":"pageId={/literal}{$gBitSystem->getConfig('boostsuite_tracking_checkout_id')}{literal}&siteId={/literal}{$gBitSystem->getConfig('boostsuite_site_id')}{literal}"}; 
			(function() {
				var bs = document.createElement('script');
				bs.type = 'text/javascript';
				bs.async = true;
				bs.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://d2so4705rl485y.cloudfront.net/widgets/tracker/tracker.js';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(bs, s); 
			})();
			</script>
			{/literal}
		{/if}
		{if $gBitSystem->getConfig('shopperapproved_site_id')}
			<script type="text/javascript"> var randomnumber = Math.floor(Math.random()*1000); sa_draw = window.document.createElement('script'); sa_draw.setAttribute('src', 'https://shopperapproved.com/thankyou/sv-draw_js.php?site={$gBitSystem->getConfig('shopperapproved_site_id')}&loadoptin=1&rnd'+randomnumber); sa_draw.setAttribute('type', 'text/javascript'); document.getElementsByTagName("head")[0].appendChild(sa_draw); </script>
		{/if}
	{/if}

</div>
{/strip}

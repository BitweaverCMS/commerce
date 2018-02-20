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

{/strip}
{if $gBitSystem->isLive() && !$gBitUser->hasPermission( 'p_users_admin' )}
	{if $gBitSystem->getConfig('google_analytics_ua')}
	<!-- START Google Analytics -->
	<script src="https://ssl.google-analytics.com/urchin.js" type="text/javascript"></script>
	<script type="text/javascript">
		_uacct = "{$gBitSystem->getConfig('google_analytics_ua')}";
		urchinTracker();
	</script>
	<form style="display:none;" name="utmform">
<textarea style="display:none;" id="utmtrans">
UTM:T|{$newOrder->mOrdersId}|{$gBitUser->getPreference('affiliate_code',$gBitSystem->getConfig('site_title'))}|{$newOrder->getField('total')}|{$newOrder->getField('tax')}|{$newOrder->getField('shipping_cost')}|{$newOrder->delivery.city}|{$newOrder->delivery.state}|{$newOrder->delivery.country.countries_name}
{foreach from=$newOrder->contents item=product}UTM:I|{$newOrder->mOrdersId}|{$product.id}|{$product.name|replace:'|':' '}|{$product.model|replace:'|':' '}|{$product.price}|{$product.products_quantity}
{/foreach}</textarea>
	</form>
	<script type="text/javascript"> 
	__utmSetTrans(); 
	</script>
	<!-- END Google Analytics -->
	{/if}
	{if $gBitSystem->getConfig('google_conversion_id')}
	<!-- START Google Code for Checkout Success Conversion Page -->
	<script type="text/javascript">
	/* <![CDATA[ */
	var google_conversion_id = {$gBitSystem->getConfig('google_conversion_id')};
	var google_conversion_language = "en";
	var google_conversion_format = "1";
	var google_conversion_color = "ffffff";
	var google_conversion_label = "QhXYCIrwsAEQzKj8_QM";
	var google_remarketing_only = false;
	{if $newOrder->getField('total')}
		var google_conversion_value = {$newOrder->getField('total')}; 
		var google_conversion_currency = "{$newOrder->getField('currency',$smarty.const.DEFAULT_CURRENCY)}";
	{/if}
	/* ]]> */
	</script>
	<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
	<noscript>
	<div style="display:inline;">
	<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/{$gBitSystem->getConfig('google_conversion_id')}/?value={$newOrder->getField('total')}&conversion_currency={$newOrder->getField('currency',$smarty.const.DEFAULT_CURRENCY)}&amp;label=QhXYCIrwsAEQzKj8_QM&amp;guid=ON&amp;script=0"/>
	</div>
	</noscript>
	<!-- END Google Code for Checkout Success Conversion Page -->
	{/if}
	{if $gBitSystem->getConfig('google_trusted_store')}
	<!-- START Google Trusted Stores Order -->
	<div id="gts-order" style="display:none;" translate="no">
		{* start order and merchant information *}
		<span id="gts-o-id">{$gCommerceSystem->getConfig('GOOGLE_TRUSTED_STORE')}</span>
		<span id="gts-o-domain">{$smarty.server.HTTP_HOST}</span>
		<span id="gts-o-email">{$gBitUser->getField('email')}</span>
		<span id="gts-o-country">{$newOrder->delivery.country.countries_name}</span>
		<span id="gts-o-currency">{$newOrder->getField('currency',$smarty.const.DEFAULT_CURRENCY)}</span>
		<span id="gts-o-total">{$newOrder->info.total}</span>
		<span id="gts-o-discounts">{$newOrder->getModuleTotal('ot_gv')+$newOrder->getModuleTotal('ot_coupon')}</span>
		<span id="gts-o-shipping-total">{$newOrder->getModuleTotal('ot_shipping')}</span>
		<span id="gts-o-tax-total">{$newOrder->getModuleTotal('ot_tax')}</span>
		{assign var=shipEpoch value=$smarty.now+(86400 * 7)}<span id="gts-o-est-ship-date">{$shipEpoch|date_format:'Y-m-d'}</span>
		{assign var=deliveryEpoch value=$smarty.now+(86400 * 14)}<span id="gts-o-est-delivery-date">{$deliveryEpoch|date_format:'Y-m-d'}</span>
		<span id="gts-o-has-preorder">N</span>
		<span id="gts-o-has-digital">N</span>
		{* end order and merchant information *}
		{foreach from=$newOrder->contents item=product}<span class="gts-item">
			<span class="gts-i-name">{$product.name|htmlspecialchars:$smarty.const.ENT_NOQUOTES}</span>
			<span class="gts-i-price">{$product.price}</span>
			<span class="gts-i-quantity">{$product.products_quantity}</span>
		</span>{/foreach}
	</div>
	<!-- END Google Trusted Stores Order -->
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

	<script type="text/javascript">{literal}
		var randomnumber = Math.floor(Math.random()*1000); 
		/* Include all products in the following object using the key value pairs: 'product id':'Product Name' */ 
		var sa_products = { {/literal}{foreach from=$newOrder->contents item=product}{foreach from=$product.attributes item=attr}{if $attr.options_id==1} '{$attr.options_values_id}':'{$attr.value}', {/if}{/foreach}{/foreach}{literal} };
		sa_draw = window.document.createElement('script'); 
		sa_draw.setAttribute('src', 'https://shopperapproved.com/thankyou/sv-draw_js.php?site={$gBitSystem->getConfig('shopperapproved_site_id')}&loadoptin=1&rnd'+randomnumber); 
		sa_draw.setAttribute('type', 'text/javascript'); 
		document.getElementsByTagName("head")[0].appendChild(sa_draw); 
	{/literal}</script>
	{/if}
{/if}
</div>

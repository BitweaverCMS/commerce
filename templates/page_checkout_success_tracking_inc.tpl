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
		{assign var=shipEpoch value=$smarty.now+(86400 * 10)}
	{literal}
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>

<script>
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          // REQUIRED FIELDS
          "merchant_id": {$gCommerceSystem->getConfig('GOOGLE_MERCHANT_ID')},
          "order_id": "{$newOrder->mOrdersId}",
          "email": "{$gBitUser->getField('email')}",
          "delivery_country": "{$newOrder->delivery.country.countries_iso_code_2}",
          "estimated_delivery_date": "{$shipEpoch|date_format:'Y-m-d'}",

          // OPTIONAL FIELDS
          "products": [ {/literal}{foreach from=$newOrder->contents item=product}{foreach from=$product.attributes item=attr}{if $attr.options_id==1} {ldelim}"mpn":"{$attr.options_values_id}"{rdelim}, {/if}{/foreach}{/foreach}{literal} ],
        });
    });
  }
</script>
	{/literal}
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
	/* Include all products in the following object using the key value pairs: 'product id':'Product Name' */ 
	var sa_products = { {/literal}{foreach from=$newOrder->contents item=product}{foreach from=$product.attributes item=attr}{if $attr.options_id==1} '{$attr.options_values_id}':'{$attr.value}', {/if}{/foreach}{/foreach}{literal} };
	var sa_values = { {/literal}
		"site":{$gBitSystem->getConfig('shopperapproved_site_id')}, 
		"token":"{$gBitSystem->getConfig('shopperapproved_token')}", 
		'orderid':'{$newOrder->mOrdersId}', 
		'name':'{$newOrder->billing.name|replace:"'":"\'"}', 
		'email':'{$gBitUser->getField('email')}', 
		'country':'{$newOrder->delivery.country.countries_name}', 
		'state':'{$newOrder->delivery.state}' 
	{literal} }; 
	function saLoadScript(src) { 
		var js = window.document.createElement("script"); 
		js.src = src; 
		js.type = "text/javascript"; 
		document.getElementsByTagName("head")[0].appendChild(js); 
	} 
	var d = new Date(); 
	{/literal}
	if (d.getTime() - 172800000 > 1477399567000) 
		saLoadScript("//www.shopperapproved.com/thankyou/rate/{$gBitSystem->getConfig('shopperapproved_site_id')}.js"); 
	else 
		saLoadScript("//direct.shopperapproved.com/thankyou/rate/{$gBitSystem->getConfig('shopperapproved_site_id')}.js?d=" + d.getTime()); 
</script>
	{/if}
{/if}

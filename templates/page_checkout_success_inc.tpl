{if $newOrder}
	{if $gBitSystem->getConfig('google_merchant_id') && $gBitSystem->getConfig('google_merchant_reviews')}
	<!-- START Google Trusted Stores Order -->
		{assign var=shipEpoch value=$smarty.now+(86400 * 10)}
	{literal}
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>

<script>
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          "merchant_id": {$gCommerceSystem->getConfig('GOOGLE_MERCHANT_ID')},
          "order_id": "{$newOrder->mOrdersId}",
          "email": "{$gBitUser->getField('email')}",
          "delivery_country": "{$newOrder->delivery.country.countries_iso_code_2}",
          "estimated_delivery_date": "{$shipEpoch|date_format:'Y-m-d'}",
          "products": [ {/literal}{foreach from=$newOrder->contents item=product}{foreach from=$product.attributes item=attr}{if $attr.products_options_id==1} {ldelim}"id":"{$attr.products_options_values_id}"{rdelim}, {/if}{/foreach}{/foreach}{literal} ],
        });
    });
  }
</script>
	{/literal}
	{/if}
	{if $gBitSystem->getConfig('shopperapproved_site_id')}
<script type="text/javascript">{literal}
	/* Include all products in the following object using the key value pairs: 'product id':'Product Name' */ 
	var sa_products = { {/literal}{foreach from=$newOrder->contents item=product}{foreach from=$product.attributes item=attr}{if $attr.products_options_id==1} '{$attr.products_options_values_id}':'{$attr.products_options_values}', {/if}{/foreach}{/foreach}{literal} };
	var sa_values = { {/literal}
		"site":{$gBitSystem->getConfig('shopperapproved_site_id')}, 
		"token":"{$gBitSystem->getConfig('shopperapproved_token')}", 
		'orderid':'{$newOrder->mOrdersId}', 
		'name':'{$newOrder->billing.name|replace:"'":"\'"}', 
		'email':'{$gBitUser->getField('email')}', 
		'country':'{$newOrder->delivery.countries_name}', 
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

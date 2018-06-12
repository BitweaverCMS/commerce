{strip}
{* Google analytics setup *}
{if $gBitSystem->isTracking()}
	{* **** GOOGLE ANALYTICS **** *}
	{if $gBitSystem->getConfig('google_analytics_ua')}
		{if $smarty.request.main_page == 'shopping_cart'}
			{assign var=eecStep value=1}
			{assign var=eecEvent value='begin_checkout'}
		{elseif $smarty.request.main_page == 'checkout_shipping'}
			{assign var=eecStep value=2}
			{assign var=eecEvent value='checkout_progress'}
		{elseif $smarty.request.main_page == 'checkout_payment'}
			{assign var=eecStep value=3}
			{assign var=eecEvent value='checkout_progress'}
		{elseif $smarty.request.main_page == 'checkout_confirmation'}
			{assign var=eecStep value=4}
			{assign var=eecEvent value='checkout_progress'}
		{elseif $smarty.request.main_page == 'checkout_success'}
			{assign var=eecStep value=5}
			{assign var=eecEvent value='purchase'}
		{/if}
		{if $eecStep}
<script type="text/javascript">
gtag('event', '{$eecEvent}', {ldelim}
	{if $eecStep} "checkout_step": {$eecStep},{/if}
{if $newOrder}
	{if $gBitAffiliate}
		{assign var=affiliate value=$gBitAffiliate->getRegistration($gBitUser->mUserId)}
		{if $affiliate}
			{assign var=affiiateString value="'affiliation': '`$affiliate.program_name`', "}
		{/if}
	{/if}
	{section loop=$newOrder->totals name=t}
		{if $newOrder->totals[t].class=='ot_coupon'}
			{assign var=couponName value=$newOrder->totals[t].title|regex_replace:"/.*: /":''|escape:quotes}
			{assign var=couponString value=", 'coupon':'$couponName'"}
		{elseif $newOrder->totals[t].class=='ot_gv' && !$couponString}
			{assign var=couponString value=', "coupon":"Gift Certificate"'}
		{/if}
	{/section}
	{if $eecEvent=='purchase'}
		'transaction_id': '{$newOrder->mOrdersId}',
		'value': '{$newOrder->getField('total')}',
		'currency': '{$smarty.const.DEFAULT_CURRENCY}',
		'shipping': '{$newOrder->getField('shipping_cost')}',
		'tax': '{$newOrder->getField('tax')}',
		{$affiiateString}
	{/if}
	"items": [
		{foreach from=$newOrder->contents item=product}{foreach from=$product.attributes item=attr}{if $attr.options_id==1}{ldelim}
			"id": '{$attr.options_values_id}',
			'name': '{$attr.value}',
			"brand": "{$gBitSystem->getConfig('site_title')|escape:quotes}",
			'category': '{$product.model|replace:"'":''}',
			'quantity': '{$product.products_quantity}',
			'price': '{$product.price}',
		{rdelim},{/if}{/foreach}{/foreach}
	]
{else}
	{if $smarty.session.dc_redeem_code} {assign var=couponString value=' "coupon":"`$smarty.session.dc_redeem_code`"'} 
	{elseif $smarty.session.cot_gv} {assign var=couponString value=' "coupon":"Gift Certificate"'} {/if}
{/if}
{$couponString}
{rdelim});
</script>
		{/if}
{*$eecStep}{$eecEvent}{$smarty.request|vd}{$smarty.session|vd}{$gBitCustomer->mCart|vd}{$newOrder|vd*}
	{/if}
{/if}
{/strip}


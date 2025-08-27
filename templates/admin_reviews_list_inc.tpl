

		{if $reviewsList}
		<ol start={$smarty.request.offset + 1}>
		{foreach from=$reviewsList item=review key=reviewId name=reviewList}
		<li class="pb-1 review review-{if $review.reviews_status!='Y'}inactive{elseif $review.coupon_start_date|strtotime > time()}pending{elseif $review.date_reviewed|strtotime > time()}active{else}expired{/if}">
			<span class="badge" onclick="BitBase.toggleElementDisplay('review-content-{$reviewId}','block')">{booticon iname="fa-star"} {$review.reviews_rating}</span> {booticon iname='fa-pen-to-square'}
<span class="pl-2"><a href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URL}reviews.php?customers_id={$review.customers_id}">{$review.real_name|default:$review.login}</a> <span class="date">"{$review.reviewers_name}" {$review.email} </span></span>
<div class="date pull-right inline-block">

{$review.delivery_city}, {$review.delivery_state} {$review.delivery_country} {if $review.orders_id}<a href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URL}orders.php?oID={$review.orders_id}">{$review.orders_id}</a></span>{/if} <span class="date pr-1"><a href="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URL}list_orders.php?user_id={$review.customers_id}">{booticon iname="fa-clock"}</a> <span class="inline-block" style="min-width:10em;font-family:monospace;">{$review.date_reviewed|substr:0:10}</span>
</div>
<div>
</div>
			<div style="display:none" id="review-content-{$reviewId}" class="box">
				{$review.reviews_text}
			</div>
		</li>
		{/foreach}
		</ol>
		{/if}

		{pagination listInfo=$reviewsList.listInfo}

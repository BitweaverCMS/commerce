{if $sideboxReview || $writeReview}
	{bitmodule title=$moduleTitle name="bc_reviewsrandom"}
		{if $sideboxReview}
			<div style="text-align:center;">
				<a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_reviews_info&products_id={$sideboxReview.products_id}&reviews_id={$sideboxReview.reviews_id}"><img src="{$sideboxReview.products_image_url}" alt="{$sideboxReview.products_name|escape:html}" /></a>
			</div>
			<img src="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}icons/stars_{$sideboxReview.reviews_rating}.png" alt="{$sideboxReview.reviews_rating}{tr}of 5 stars.{/tr}" /><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_reviews_info&products_id={$sideboxReview.products_id}&reviews_id={$sideboxReview.reviews_id}">{$sideboxReview.reviews_text|truncate:75:"..."}</a>
		{elseif $writeReview}
			<a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=product_reviews_write&products_id={$reviewProductsId}">{tr}Write a review on this product.{/tr}</a><br />
			<a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=reviews">{tr}See more...{/tr}</a><br />
		{else}
			{tr}There are currently no product reviews.{/tr}
		{/if}
	{/bitmodule}
{/if}

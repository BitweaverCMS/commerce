{strip}
<header class="page-header">
	<h1>Some items in your cart need your review:</h1>
</header>

<section class="body">
	<div class="error">{tr}Before proceeding, please verify each of items in your cart below:{/tr}</div>

	{foreach from=$proofProducts item=proof} 
		{include file=$proof.template productProof=$proof}
	{/foreach}
	<div class="clear warning">{tr}If you are satisfied with the items above, click NEXT to proceed.{/tr}</div>

	<form action="{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_shipping" method="POST">
		<input type="submit" class="btn btn-default" name="checkout_proof" value="{tr}Next{/tr} &raquo;" />
	</form>
</section>

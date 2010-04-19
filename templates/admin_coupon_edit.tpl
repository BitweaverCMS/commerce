<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{tr}{$smarty.const.HEADING_TITLE}{/tr}</h1>
	</div>
	<div class="body">

	{form name="coupon" method="post" action="`$smarty.const.PHP_SELF`?action=update&oldaction=`$smarty.get.action`&amp;cid=`$smarty.get.cid`"}
		<div class="row">
			{formlabel label=$smarty.const.COUPON_NAME}
			{forminput}
				{foreach from=$languages key=i item=lang}
					{assign var=langId value=$lang.id}
					<div><input type="text" name="coupon_name[{$langId}]" value="{$smarty.post.coupon_name.$langId|escape}"/> {$lang.name|escape}</div>
				{/foreach}
				{formhelp note=$smarty.const.COUPON_NAME_HELP}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label=$smarty.const.COUPON_DESC}
			{forminput}
				{foreach from=$languages key=i item=lang}
					{assign var=langId value=$lang.id}
					<div><input type="text" name="coupon_desc[{$langId}]" value="{$smarty.post.coupon_desc.$langId|escape}"/> {$lang.name|escape}</div>
				{/foreach}
				{formhelp note=$smarty.const.COUPON_NAME_HELP}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label=$smarty.const.COUPON_AMOUNT}
			{forminput}
				<input type="text" name="coupon_amount" value="{$smarty.post.coupon_amount|escape}"/>
				{formhelp note=$smarty.const.COUPON_AMOUNT_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_MIN_ORDER}
			{forminput}
				<input type="text" name="coupon_min_order" value="{$smarty.post.coupon_min_order|escape}"/>
				{formhelp note=$smarty.const.COUPON_MIN_ORDER_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_FREE_SHIP}
			{forminput}
				<input type="checkbox" name="coupon_free_ship" {if $smarty.post.coupon_free_ship}checked='CHECKED'{/if}/>
				{formhelp note=$smarty.const.COUPON_FREE_SHIP_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_CODE}
			{forminput}
				<input type="text" name="coupon_code" value="{$smarty.post.coupon_code|escape}"/>
				{formhelp note=$smarty.const.COUPON_CODE_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label="Product Restrictions"}
			{forminput}
				<input type="text" name="restrict_to_products" value="{$smarty.post.restrict_to_products|escape}"/>
				{formhelp note="Comma seperated list of product ID's"}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label="Category Restrictions"}
			{forminput}
				<input type="text" name="restrict_to_categories" value="{$smarty.post.restrict_to_categories|escape}"/>
				{formhelp note="Comma seperated list of category ID's"}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_USES_COUPON}
			{forminput}
				<input type="text" name="coupon_uses_coupon" value="{$smarty.post.coupon_uses_coupon|escape}"/>
				{formhelp note=$smarty.const.COUPON_USES_COUPON_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_USES_USER}
			{forminput}
				<input type="text" name="coupon_uses_user" value="{$smarty.post.coupon_uses_user|escape}"/>
				{formhelp note=$smarty.const.COUPON_USES_USER_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_STARTDATE}
			{forminput}
				{$startDateSelect}
				{formhelp note=$smarty.const.COUPON_STARTDATE_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_FINISHDATE}
			{forminput}
				{$finishDateSelect}
				{formhelp note=$smarty.const.COUPON_FINISHDATE_HELP}
			{/forminput}
		</div>
		<div class="row submit">
			<input type="submit" class="button" name="Preview" value="Preview"/>
			<a href="{$smarty.server.PHP_SELF}?cid={$smarty.request.cid}" class="button">{tr}Cancel{/tr}</a>
		</div>
	{/form}
		
	</div>
</div>


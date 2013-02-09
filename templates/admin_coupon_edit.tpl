<div class="admin bitcommerce">
	<div class="header">
		<h1 class="header">{if $gCoupon->isValid()}{tr}Edit Coupon:{/tr} {$gCoupon->getField('coupon_code')|escape}{else}{tr}Create New Coupon{/tr}{/if}</h1>
	</div>
	<div class="body">

	{formfeedback hash=$gCoupon->mFeedback}

	{form name="coupon" method="post" action="`$smarty.server.SCRIPT_NAME`"}
		<input type="hidden" name="cid" value="{$smarty.get.cid}">
		<input type="hidden" name="action" value="store">
		<div class="row">
			{formlabel label=$smarty.const.COUPON_CODE}
			{forminput}
				<input type="text" name="coupon_code" value="{$smarty.post.coupon_code|escape}"/>
				{formhelp note=$smarty.const.COUPON_CODE_HELP}
			{/forminput}
		</div>
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
					<div><input type="text" name="coupon_description[{$langId}]" value="{$smarty.post.coupon_description.$langId|escape}"/> {$lang.name|escape}</div>
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
				<input type="text" name="coupon_minimum_order" value="{$smarty.post.coupon_minimum_order|escape}"/>
				{formhelp note=$smarty.const.COUPON_MIN_ORDER_HELP}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label="Quantity Limit"}
			{forminput}
				<input type="text" name="quantity_max" value="{$smarty.post.quantity_max|escape}"/>
				{formhelp note="The maximum cart quantity that will be deducted. Additional quantity over the max will be priced at full price."}
			{/forminput}
		</div>

		<div class="row">
			{formlabel label=$smarty.const.COUPON_FREE_SHIP}
			{forminput}
				<input type="checkbox" name="coupon_free_ship" {if $smarty.post.coupon_free_ship}checked='CHECKED'{/if}/>
				{formhelp note=$smarty.const.COUPON_FREE_SHIP_HELP}
			{/forminput}
		</div>
{*		<div class="row">
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
			{formlabel label="Minimum Quantity"}
			{forminput}
				<input type="text" name="restrict_to_quantity" value="{$smarty.post.restrict_to_quantity|escape}"/>
				{formhelp note="Comma seperated list of category ID's"}
			{/forminput}
		</div>
*}
		<div class="row">
			{formlabel label="Shipping Restrictions"}
			{forminput}
				<input type="text" name="restrict_to_shipping" value="{$smarty.post.restrict_to_shipping|escape}"/>
				{formhelp note="Comma seperated list of shipping_code's (e.g. 'USPSPRI') will only allow enumerated shipping methods if Free Shipping is selected."}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_USES_COUPON}
			{forminput}
				<input type="text" name="uses_per_coupon" value="{$smarty.post.uses_per_coupon|escape}"/>
				{formhelp note=$smarty.const.COUPON_USES_COUPON_HELP}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label=$smarty.const.COUPON_USES_USER}
			{forminput}
				<input type="text" name="uses_per_user" value="{$smarty.post.uses_per_user|escape}"/>
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
		<div class="row">
			{formlabel label="Administration Note"}
			{forminput}
				<textarea name="admin_note">{$smarty.post.admin_note|escape}</textarea>
				{formhelp note="Administrator's private note, not visible to customers."}
			{/forminput}
		</div>

		<div class="row submit">
			<input type="submit" class="button" name="Save" value="Save"/>
			<a href="{$smarty.server.SCRIPT_NAME}?cid={$smarty.request.cid}" class="button">{tr}Cancel{/tr}</a>
		</div>
	{/form}
		
	</div>
</div>


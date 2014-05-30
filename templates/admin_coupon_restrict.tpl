
{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

{formfeedback hash=$feedback}
{strip}
<div class="admin bitcommerce coupons">
	<div class="page-header">
		<h1>{tr}Discount Coupons{/tr}: {tr}Restrictions for {/tr} {$gCoupon->getField('coupon_name')} ({$gCoupon->getField('coupon_code')})</h1>
	</div>
	<div class="body">
		<ul class="data width50p">
	{foreach from=$gCoupon->mRestrictions item=r}
		<li class="item {if $r.coupon_restrict=='Y'}restricted{else}permitted{/if}">
			<div class="floaticon">
				<a class="" href="{$smarty.server.SCRIPT_NAME}?cid={$gCoupon->mCouponId}&amp;info={$r.restrict_id}&amp;action=switch_status">{if $r.coupon_restrict=='Y'}{biticon ipackage="bitcommerce" iname="icon_status_red" iexplain="Restricted"}{elseif $r.coupon_restrict=='O'}{biticon ipackage="bitcommerce" iname="icon_status_yellow" iexplain="Restricted"}{else}{biticon ipackage="bitcommerce" iname="icon_status_green" iexplain="Permitted"}{/if}</a>
				<a class="" href="{$smarty.server.SCRIPT_NAME}?cid={$gCoupon->mCouponId}&amp;info={$r.restrict_id}&amp;action=remove">{booticon iname="icon-trash" iexplain="Remove"}</a>
			</div>
			{if $r.category_id}<strong>{tr}Category{/tr}:</strong> {$r.categories_name}<br/>{/if}
			{if $r.product_id}<strong>{tr}Product{/tr}:</strong> <a href="{$gBitProduct->getDisplayUrlFromHash($r)}">{$r.products_name|escape} #{$r.product_id}</a><br/>{/if}
			{if $r.product_type_id}<strong>{tr}Product Type{/tr}:</strong> {$r.type_name}<br/>{/if}
			{if $r.products_options_values_id}<strong>{tr}Option{/tr}:</strong> {$r.products_options_values_name}<br/>{/if}
		</li>
	{foreachelse}
		<li><em>{tr}Unrestricted Coupon{/tr}</em></li>
	{/foreach}
		</ul>

{form name="restrict_category" method="post" action="`$smarty.server.SCRIPT_NAME`?cid=`$gCoupon->mCouponId`"}
{legend legend="Add Restriction"}
<div class="form-group">
	{formlabel label="Category"}
	{forminput}
		{$categorySelect}
	{/forminput}
</div>
<div class="form-group">
	{formlabel label="Options"}
	{forminput}
		<select name="products_options_values_id">
		<option value="">Any</option>
		{foreach from=$optionsList item=optionGroup}
		<optgroup label="{$optionGroup.products_options_name|escape}">
			{foreach from=$optionGroup.values item=optionValue}
				<option value="{$optionValue.products_options_values_id}">{$optionValue.products_options_values_name|escape}</option>
			{/foreach}
		</optgroup>
		{/foreach}
		</select>
	{/forminput}
</div>
<div class="form-group">
	{formlabel label="Product Type"}
	{forminput}
		<select name="product_type_id">
		<option value="">Any</option>
		{foreach from=$productTypes item=type}
			<option value="{$type.type_id}">{$type.type_name|escape}{if $type.type_class} - {$type.type_class|escape}{/if}</option>
		{/foreach}
		</select>
	{/forminput}
</div>
<div class="form-group">
	{formlabel label="Specific Product"}
	{forminput}
		{$productCategorySelect}
		<select name="product_id">
		<option value="">Any</option>
		{foreach from=$productsList item=prod key=prodId}
			<option value="{$prodId}">{$prod|escape}</option>
		{/foreach}
		</select>
	{/forminput}
</div>
<div class="form-group">
	{formlabel label="Restriction"}
	{forminput}
		<select name="restrict_status">
			<option value="Deny" selected="selected">{tr}Deny{/tr}</option>
			<option value="Maybe" selected="selected">{tr}Allow - Optional{/tr}</option>
			<option value="Allow" selected="selected">{tr}Allow - Mandatory{/tr}</option>
		</select>
	{/forminput}
</div>
<div class="form-group submit">
	{forminput}
		<input type="submit" class="btn btn-default" name="action" value="Add">
	{/forminput}
</div>
{/legend}
{/form}
	</div>
</div>
{/strip}

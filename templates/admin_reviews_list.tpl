<div class="page-header">
		{*<a href="{$smarty.server.SCRIPT_NAME}?action=new" class="pull-right btn btn-primary btn-xs">{tr}Create Review{/tr}</a>*}
		<form class="form form-inline pull-right" name="status" action="{$smarty.const.BITCOMMERCE_PKG_URL}admin/reviews.php" method="get">
			{*<select class="form-control" name="status" onchange="this.form.submit();">
				<option value="" selected="selected">{tr}All{/tr}</option>
				<option value="Y" {if $smarty.request.status=='Y'}selected="selected"{/if}>{tr}Active Reviews{/tr}</option>
				<option value="N" {if $smarty.request.status=='N'}selected="selected"{/if}>{tr}Inactive Reviews{/tr}</option>
			</select>*}
			<select class="form-control" name="sort_mode" onchange="this.form.submit();">
				<option value="date_reviewed_desc" {if $smarty.request.sort_mode=='date_reviewed_desc'}selected="selected"{/if}>{tr}Date - Newest First{/tr}</option>
				<option value="date_reviewed_asc" {if $smarty.request.sort_mode=='date_reviewed_asc'}selected="selected"{/if}>{tr}Date - Oldest First{/tr}</option>
				<option value="reviews_rating_desc" {if $smarty.request.sort_mode=='reviews_rating_desc'}selected="selected"{/if}>{tr}Rating - High to Low{/tr}</option>
				<option value="reviews_rating_asc" {if $smarty.request.sort_mode=='reviews_rating_asc'}selected="selected"{/if}>{tr}Rating - Low to High{/tr}</option>
			</select>							
		</form>
	<h1>{tr}Customer Reviews{/tr}</h1>
</div>

{formfeedback hash=$feedback}
{strip}
<div class="admin bitcommerce reviews">
	<header>
	<div class="body">
		{include file="bitpackage:bitcommerce/admin_reviews_list_inc.tpl"}
	</div>
</div>
{/strip}

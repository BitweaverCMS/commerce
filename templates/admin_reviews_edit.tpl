<header class="page-header">
	<h1>{tr}Customer Reviews{/tr}</h1>
</header>

{formfeedback hash=$feedback}

{strip}
<div class="admin bitcommerce reviews">
	<div class="body">
		{form name="review" action="`$smarty.const.BITCOMMERCE_PKG_ADMIN_URI`reviews?action=edit" method="post"}
			<input type="hidden" name="reviews_id" value="{$review.reviewers_id}">

			<div class="form-group {if $reviewErrors.reviewers_name}error{/if}">
				{formlabel label="Reviewers name" for="reviewers_name"}
				{forminput}
					<input class="form-control"type="text" name="reviewers_name" id="reviewers_name" value="{$review.reviewers_name|default:$smarty.request.reviewers_name}"/>
					{formhelp note=$reviewErrors.validate}
				{/forminput}
			</div>

			<div class="form-group {if $reviewErrors.reviews_rating}error{/if}">
				{formlabel label="Rating" for="reviews_rating"}
				{forminput}
					<input class="form-control"type="text" name="reviews_rating" id="reviews_rating" value="{$review.reviews_rating|default:$smarty.request.reviews_rating}"/>
					{formhelp note=$reviewErrors.validate}
				{/forminput}
			</div>

			<div class="form-group {if $reviewErrors.reviewers_date}error{/if}">
				{formlabel label="Review Date" for="reviewers_date"}
				<div class="input-group date" id="date-reviewed">
					<input type="date" class="form-control" name="date_reviewed" value="{$deadline|date_format:'Y-m-d'}"><span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
				</div>
				{formhelp note=""}
			</div>

			<div class="form-group {if $reviewErrors.reviews_admin_note}error{/if}">
				{formlabel label="Admin Note" for="reviews_admin_note"}
				{forminput}
					<input class="form-control" type="text" name="reviews_admin_note" id="reviews_admin_note" value="{$review.reviews_admin_note|default:$smarty.request.reviews_admin_note}"/>
					{formhelp note="Not visible to reviewer."}
				{/forminput}
			</div>

			<div class="form-group {if $reviewErrors.customers_id}error{/if}">
				{formlabel label="Reviewers name" for="customers_id"}
				{forminput}
					<input class="form-control" type="text" name="customers_id" id="customers_id" value="{$review.customers_id|default:$smarty.request.customers_id}"/>
					{formhelp note=$reviewErrors.validate}
				{/forminput}
			</div>

			<div class="form-group {if $reviewErrors.orders_id}error{/if}">
				{formlabel label="User ID" for="orders_id"}
				{forminput}
					<input class="form-control" type="text" name="orders_id" id="orders_id" value="{$review.orders_id|default:$smarty.request.orders_id}"/>
					{formhelp note=$reviewErrors.validate}
				{/forminput}
			</div>

			<div class="form-group {if $reviewErrors.orders_id}error{/if}">
				{formlabel label="Review Text"}
				{forminput}
					<textarea rows="5" class="form-control" name="reviews_text">{$review.reviews_text|escape}</textarea>
					{formhelp note=$reviewErrors.validate}
				{/forminput}
			</div>

products_id
status
reviews_source
reviews_source_url
lang_code
format_guid
reviews_text

		{/form}
	</div>
</div>
{/strip}


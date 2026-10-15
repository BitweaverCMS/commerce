{* Saved purchase options for one customer, grouped by option. Replaced in place by the add and remove requests. *}
<div id="purchase-options" class="purchase-options attrlist">
	{if $purchaseOptionError}
	<div class="alert alert-danger purchase-options-error">{$purchaseOptionError|escape}</div>
	{/if}
	{if $purchaseOptions}
	<ul class="data purchase-options-list">
	{foreach from=$purchaseOptions key=optionId item=option}
		<li class="item">
			<a class="option-toggle" role="button" data-toggle="collapse" href="#purchase-option-values-{$optionId}" aria-expanded="true" aria-controls="purchase-option-values-{$optionId}"><i class="fa fal fa-caret-right option-caret" title="{tr}Show option values{/tr}"></i></a>
			<strong>{$option.products_options_name|escape}</strong> <span class="purchase-option-id">{tr}ID{/tr} {$optionId}</span>
			<span class="option-value-count text-muted">{$option.values|@count}</span>
			<ul class="data option-values collapse in" id="purchase-option-values-{$optionId}">
			{foreach from=$option.values key=valueId item=value}
				<li class="item purchase-option-value">
					<span class="purchase-option-id">#{$valueId}</span> {$value.products_options_values_name|escape}
					<button type="button" class="btn btn-link btn-xs purchase-option-remove" data-option-id="{$optionId}" data-value-id="{$valueId}" title="{tr}Remove{/tr}"><i class="fa fal fa-xmark"></i><span class="sr-only">{tr}Remove{/tr}</span></button>
				</li>
			{/foreach}
			</ul>
		</li>
	{/foreach}
	</ul>
	{else}
	<p class="purchase-options-empty">{tr}Nothing saved yet. Choose an option and a value this customer wants.{/tr}</p>
	{/if}
	<div class="form-inline purchase-option-add collapse" id="purchase-option-add-form">
		<div class="form-group">
			<label for="purchase-option-id">{tr}Option{/tr}</label>
			<select id="purchase-option-id" class="form-control input-sm">
				<option value="">{tr}Choose{/tr}</option>
				{html_options options=$purchaseOptionChoices}
			</select>
		</div>
		<div class="form-group">
			<label for="purchase-option-value">{tr}Value{/tr}</label>
			<select id="purchase-option-value" class="form-control input-sm" disabled></select>
		</div>
		<div class="form-group purchase-option-submit">
			<button type="button" class="btn btn-primary btn-sm" id="purchase-option-add" disabled>{tr}Save option{/tr}</button>
		</div>
	</div>
</div>

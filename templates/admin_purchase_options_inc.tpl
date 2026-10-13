<div id="purchase-options">
	<h4>{tr}Purchase options{/tr}</h4>
	{if $purchaseOptionError}
		{formfeedback error=$purchaseOptionError}
	{/if}
	{if $purchaseOptions}
	<ul class="list-unstyled">
		{foreach from=$purchaseOptions item=pref}
		<li>
			{$pref.products_options_name|escape} ({$pref.products_options_id}): {$pref.products_options_values_name|escape} ({$pref.products_options_values_id})
			<button type="button" class="btn btn-xs btn-link purchase-option-remove" data-option-id="{$pref.products_options_id}">{tr}remove{/tr}</button>
		</li>
		{/foreach}
	</ul>
	{/if}
	<div>
		<select id="purchase-option-id" class="form-control input-sm">
			<option value="">{tr}Option{/tr}</option>
			{html_options options=$purchaseOptionChoices}
		</select>
		<select id="purchase-option-value" class="form-control input-sm"></select>
		<button type="button" class="btn btn-xs btn-default" id="purchase-option-add">{tr}Add{/tr}</button>
	</div>
</div>

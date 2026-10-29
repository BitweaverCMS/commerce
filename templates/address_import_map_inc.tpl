{* Column mapping step for an uploaded sheet. The page wraps this in its own form and adds the submit buttons.
   Expects $mapping from bc_address_mapping_view() and $mapSubmitName, the button name the controller reads
   ("sheet" shows another sheet of the workbook). *}
<p class="help-block">{tr}Each field below needs the column of your file that holds it. Name (or First and Last name) and Street are required; leave the rest on "not in the file" when your file does not have them. The third column shows the first address in the file with those choices.{/tr}</p>
{if count($mapping.sheets) > 1}
	<div class="form-inline address-map-sheets">
		<div class="form-group">
			<label>{tr}Sheet{/tr}
				<select name="map_sheet" class="form-control input-sm">
				{foreach from=$mapping.sheets item=mapSheet}
					<option value="{$mapSheet.index}" {if $mapSheet.index == $mapping.sheet}selected{/if}>{$mapSheet.name|escape} ({$mapSheet.rows} {tr}rows{/tr})</option>
				{/foreach}
				</select>
			</label>
			<button type="submit" class="btn btn-default btn-sm" name="{$mapSubmitName}" value="sheet">{tr}Show This Sheet{/tr}</button>
		</div>
	</div>
{else}
	<input type="hidden" name="map_sheet" value="{$mapping.sheet}">
{/if}
<table class="table table-condensed address-map">
	<thead>
		<tr>
			<th>{tr}We need{/tr}</th>
			<th>{tr}Column in{/tr} {$mapping.file|escape}</th>
			<th>{tr}First address{/tr}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$mapping.fields item=mapField}
		<tr>
			<td class="address-map-field">{$mapField.label}{if $mapField.need} <span class="text-danger" title="{tr}required{/tr}">*</span>{/if}{if $mapField.help}<br><small class="text-muted">{$mapField.help}</small>{/if}</td>
			<td>
				<select name="map[{$mapField.key}]" class="form-control input-sm address-map-select" aria-label="{$mapField.label}">
					<option value="">{tr}not in the file{/tr}</option>
				{foreach from=$mapping.columns item=mapColumn}
					<option value="{$mapColumn.index}" data-sample="{$mapColumn.sample|escape}" {if $mapField.selected !== '' && $mapField.selected == $mapColumn.index}selected{/if}>{$mapColumn.label|escape}</option>
				{/foreach}
				</select>
			</td>
			<td class="address-map-sample text-muted">{$mapField.sample|escape}</td>
		</tr>
	{/foreach}
	</tbody>
</table>
{literal}
<script>
$(document).ready(function() {
	$('.address-map-select').on('change', function() {
		$(this).closest('tr').find('.address-map-sample').text( $(this).find('option:selected').data('sample') || '' );
	});
});
</script>
{/literal}

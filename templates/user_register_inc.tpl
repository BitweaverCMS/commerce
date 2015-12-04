{if $comInterests}
<div class="form-group">
	{formlabel label="Interests"}
	{forminput class="checkbox"}
		{foreach from=$comInterests key=interestId item=interestName}
			<div><label>
			<input type="checkbox" name="com_interests[]" value="{$interestId}"/>{$interestName}
			</label></div>
		{/foreach}
		{formhelp note="Let us know your interests so we can best serve you."}
	{/forminput}
</div>
{/if}

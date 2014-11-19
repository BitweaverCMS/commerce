{if $comInterests}
<div class="form-group">
	{formlabel label="Interests"}
	{forminput}
		{foreach from=$comInterests key=interestId item=interestName}
		{forminput label="checkbox"}
			<input type="checkbox" name="com_interests[]" value="{$interestId}"/>{$interestName}<br/>
		{/forminput}
		{/foreach}
		{formhelp note="Let us know your interests so we can best serve you."}
	{/forminput}
</div>
{/if}

<div class="row">
	{formlabel label="Interests"}
	{forminput}
		{foreach from=$comInterests key=interestId item=interestName}
			<input type="checkbox" name="interests[]" value="{$interestId}"/>{$interestName}<br/>
		{/foreach}
		{formhelp note="Let us know your interests so we can best serve you."}
	{/forminput}
</div>


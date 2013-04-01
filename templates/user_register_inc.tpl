{if $comInterests}
<div class="control-group">
	{formlabel label="Interests"}
	{forminput}
		<ul class="data">
		{foreach from=$comInterests key=interestId item=interestName}
			<li class="item"><input type="checkbox" name="com_interests[]" value="{$interestId}"/>{$interestName}</li>
		{/foreach}
		</ul>
		{formhelp note="Let us know your interests so we can best serve you."}
	{/forminput}
</div>
{/if}

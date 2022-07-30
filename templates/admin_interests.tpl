<div class="page-header">
	<h1>
		{tr}Customer Interests{/tr}
	</h1>
</div>

<div class="body">

{form}
<div class="form-group">
	{forminput label="checkbox"}
		<input type="checkbox" name="commerce_register_interests" value="y" {if $gBitSystem->isFeatureActive('commerce_register_interests')}checked="checked"{/if} />Registration Interests
		{formhelp note="Ask new users to choose their interests during registration."}
	{/forminput}
</div>
<div class="form-group submit">
	{forminput}
		<input type="submit" class="btn btn-default" value="{tr}Save{/tr}" name="save_options"/>
	{/forminput}
</div>
{/form}

<ul class="data span-12">
{foreach from=$interestsList key=interestsId item=interestsName}
	<li class="item">
		<div class="floaticon">
			<a href="{$smarty.server.php_self}?action=edit&amp;interests_id={$interestsId}">{booticon iname="fa-pen-to-square"}</a>
			<a href="{$smarty.server.php_self}?action=delete&amp;interests_id={$interestsId}">{booticon iname="fa-trash"}</a>
		</div>
		{$interestsName}
	</li>
{/foreach}
</ul>
{if $editInterest || $smarty.request.new}
	{form}
		{if $editInterest}
			{formlabel label="Edit Interest"}
		{else}
			{formlabel label="Create New Interest"}
		{/if}
		{forminput}
			<input type="hidden" name="interests_id" value="{$editInterest.interests_id}"/>
			<input type="text" name="interests_name" value="{$editInterest.interests_name}"/>
			<input type="hidden" name="action" value="save" />
			<input type="submit" class="btn btn-default" name="save" value="{tr}Save{/tr}" />
		{/forminput}
	{/form}
{else}
	<a href="{$smarty.server.php_self}?new=1" class="btn btn-primary">New Interest</a>
{/if}

<a href="{$smarty.server.SCRIPT_NAME}?uninterested=1" class="btn btn-default">{tr}Uninterested Customers{/tr}</a>
</div>

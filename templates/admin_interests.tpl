<h1 class="header">
	{tr}Customer Interests{/tr}
</h1>

<div class="body">

{form}
<div class="row">
	{formlabel label="Registration Interests"}
	{forminput}
		<input type="checkbox" name="commerce_register_interests" value="y" {if $gBitSystem->isFeatureActive('commerce_register_interests')}checked="checked"{/if} />
		{formhelp note="Ask new users to choose their interests during registration."}
	{/forminput}
</div>
<div class="row submit">
	{forminput}
		<input type="submit" value="{tr}Save{/tr}" name="save_options"/>
	{/forminput}
</div>
{/form}

<ul class="data span-12">
{foreach from=$interestsList key=interestsId item=interestsName}
	<li class="item">
		<div class="floaticon">
			<a href="{$smarty.server.php_self}?action=edit&amp;interests_id={$interestsId}">{biticon iname="accessories-text-editor"}</a>
			<a href="{$smarty.server.php_self}?action=delete&amp;interests_id={$interestsId}">{biticon iname="edit-delete"}</a>
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
			<input type="submit" name="save" value="{tr}Save{/tr}" />
		{/forminput}
	{/form}
{else}
	<a href="{$smarty.server.php_self}?new=1">New Interest</a>
{/if}

<a href="{$smarty.server.SCRIPT_NAME}?uninterested=1" class="btn">{tr}Uninterested Customers{/tr}</a>
</div>

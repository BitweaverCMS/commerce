<div class="display bitcommerce">

<div class="page-header">
	<h1>{tr}Product not available{/tr}</h1>
</div>
<div class="body" >
	{formfeedback error="{tr}This product is not available{/tr}"}

	{if !$gBitUser->isRegistered()}
		{include file="bitpackage:users/login_inc.tpl"}
	{/if}
</div>

</div>

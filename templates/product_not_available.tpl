<div class="display bitcommerce">

<div class="body" >
	<div class="clear">
		{formfeedback error="{tr}This product is not available{/tr}"}
	</div>

	{if !$gBitUser->isRegistered()}
		{include file="bitpackage:users/login_inc.tpl"}
	{/if}
</div>

</div>

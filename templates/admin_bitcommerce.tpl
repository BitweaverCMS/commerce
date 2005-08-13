{* $Header: /cvsroot/bitweaver/_bit_commerce/templates/admin_bitcommerce.tpl,v 1.1 2005/08/13 16:36:11 spiderr Exp $ *}
{strip}

<div id="navbar">
<div class="floaticon">
	{bithelp}
</div>
<ul class="nde-menu-system">
	{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}
</ul>
</div>

<div class="admin bitcommerce">
	<div class="body">
		{$bitcommerceAdmin}
	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

{/strip}

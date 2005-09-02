{* $Header: /cvsroot/bitweaver/_bit_commerce/templates/admin_bitcommerce.tpl,v 1.2 2005/09/02 08:39:32 squareing Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>
<div id="navbar">
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

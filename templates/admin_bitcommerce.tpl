{* $Header: /cvsroot/bitweaver/_bit_commerce/templates/admin_bitcommerce.tpl,v 1.5 2007/10/31 12:44:01 spiderr Exp $ *}
{strip}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="body">
		{* put back when all of admin is smartified $messageStack->output() *}
		{$bitcommerceAdmin}
	</div><!-- end .body -->
</div><!-- end .bitcommerce -->
{/strip}

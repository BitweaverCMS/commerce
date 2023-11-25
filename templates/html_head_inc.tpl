{if $gBitSystem->isPackageActive( $smarty.const.BITCOMMERCE_PKG_NAME ) && $gBitSystem->getActivePackage()==$smarty.const.BITCOMMERCE_PKG_NAME}
	{if $smarty.const.BITCOMMERCE_ADMIN}
<link rel="stylesheet" type="text/css" href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/includes/stylesheet.css"/>
	{else}
<script>
	function clearText( thefield ) {ldelim}
		if( thefield.defaultValue==thefield.value )
		thefield.value = ""
	{rdelim}
</script>
	{/if}
{/if}

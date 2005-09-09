{if $gBitSystem->isPackageActive( 'commerce' )}
	<meta name="keywords" content="{$smarty.const.META_TAG_KEYWORDS}" />
	<meta name="description" content="{$smarty.const.META_TAG_DESCRIPTION}" />
	<link rel="stylesheet" type="text/css" href="{$smarty.const.BITCOMMERCE_PKG_URL}includes/templates/template_default/css/stylesheet.css" />
	<script type="text/javascript">//<![CDATA[
		function clearText( thefield ) {ldelim}
			if( thefield.defaultValue==thefield.value )
			thefield.value = ""
		{rdelim}
	//]]></script>
{/if}

{if $gBitSystem->isPackageActive( $smarty.const.BITCOMMERCE_PKG_NAME ) && $smarty.const.ACTIVE_PACKAGE==$smarty.const.BITCOMMERCE_PKG_NAME}
	{if $smarty.const.BITCOMMERCE_ADMIN}
<link rel="stylesheet" type="text/css" href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/includes/stylesheet.css"/>
<link rel="stylesheet" type="text/css" href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/includes/cssjsmenuhover.css" media="all" id="hoverJS"/>
<script type="text/javascript" src="{$smarty.const.BITCOMMERCE_PKG_URL}admin/includes/menu.js"></script>
<script type="text/javascript" src="{$smarty.const.BITCOMMERCE_PKG_URL}admin/includes/general.js"></script>
<script type="text/javascript">
{literal}
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
{/literal}
</script>
	{else}
{if !empty($gBitProduct) && $gBitProduct->isValid()}
	{if $gBitProduct->getField('summary')}
<meta name="description" content="{$gBitProduct->getField('summary')|escape}">
	{elseif $gBitProduct->getField('products_description')}
<meta name="description" content="{$gBitProduct->getTypeName()|escape}: {$gBitProduct->getField('products_description')|strip_tags|truncate:250|escape}"/>
	{/if}
{/if}
{if defined($smarty.const.META_TAG_KEYWORDS)}
<meta name="keywords" content="{$smarty.const.META_TAG_KEYWORDS}" />
{/if}
{if defined($smarty.const.META_TAG_DESCRIPTION)}
<meta name="description" content="{$smarty.const.META_TAG_DESCRIPTION}" />
{/if}
<script type="text/javascript">//<![CDATA[
		function clearText( thefield ) {ldelim}
		if( thefield.defaultValue==thefield.value )
		thefield.value = ""
	{rdelim}
//]]></script>
	{/if}
{/if}

{include file="bitpackage:themes/popup_header_inc.tpl"}

<body onload="resize();">
<table width="96%" border="0" cellpadding="2" cellspacing ="2" align="center" class="popupshippingestimator">
  <tr>
    <td class="main" align="right"><a href="javascript:window.close()">[X] {tr}Close Window{/tr}</a></td>
  </tr>
  <tr>
    <td>
		{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`includes/modules/shipping_estimator.php"}
    </td>
  </tr>
  <tr>
    <td class="main" align="right"><a href="javascript:window.close()">[X] {tr}Close Window{/tr}</a></td>
  </tr>
</table>
</body>

{include file="bitpackage:themes/popup_footer_inc.tpl"}

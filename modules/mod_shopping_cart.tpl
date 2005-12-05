{if $sideboxShoppingCartContent}
	{bitmodule title=$moduleTitle name="bc_shoppingcart"}
		<script type="text/javascript">//<![CDATA[
		function couponpopupWindow(url) {ldelim}
			window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
		{rdelim}
		//]]></script>
		{$sideboxShoppingCartContent}
		<em><a href="{$smarty.const.HTTPS_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}?main_page=checkout_shipping">{tr}Checkout{/tr}&nbsp;&raquo;</a></em>
	{/bitmodule}
{/if}

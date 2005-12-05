{strip}
{bitmodule title=$smarty.const.BOX_HEADING_INFORMATION name="bc_information"}
	<ul>
		<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=shippinginfo">{tr}Shipping &amp; Returns{/tr}</a></li>
		<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=privacy">{tr}Privacy Notice{/tr}</a></li>
		<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=conditions">{tr}Conditions of Use{/tr}</a></li>
		<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=contact_us">{tr}Contact Us{/tr}</a></li>

		{if $smarty.const.MODULE_ORDER_TOTAL_GV_STATUS}
			<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=gv_faq">{tr}Gift Certificate FAQ{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_NEWSLETTER_UNSUBSCRIBE_LINK}
			<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=unsubscribe">{tr}Newsletter Unsubscribe{/tr}</a></li>
		{/if}
	</ul>
{/bitmodule}
{/strip}

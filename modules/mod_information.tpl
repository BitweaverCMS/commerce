{strip}
{bitmodule title=$smarty.const.BOX_HEADING_INFORMATION name="bc_information"}
	<ul class="list-unstyled">
		<li><a href="{'shippinginfo'|zen_get_page_url}">{tr}Shipping &amp; Returns{/tr}</a></li>
		<li><a href="{'privacy'|zen_get_page_url}">{tr}Privacy Notice{/tr}</a></li>
		<li><a href="{'conditions'|zen_get_page_url}">{tr}Conditions of Use{/tr}</a></li>
		<li><a href="{'contact_us'|zen_get_page_url}">{tr}Contact Us{/tr}</a></li>

		{if $smarty.const.MODULE_ORDER_TOTAL_GV_STATUS}
			<li><a href="{'gv_faq'|zen_get_page_url}">{tr}Gift Certificate FAQ{/tr}</a></li>
		{/if}

		{if $smarty.const.SHOW_NEWSLETTER_UNSUBSCRIBE_LINK}
			<li><a href="{'unsubscribe'|zen_get_page_url}">{tr}Newsletter Unsubscribe{/tr}</a></li>
		{/if}
	</ul>
{/bitmodule}
{/strip}

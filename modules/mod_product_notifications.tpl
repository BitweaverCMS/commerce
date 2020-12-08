{strip}
{if $gBitProduct->isValid() && $gBitUser->isRegistered()}
	{bitmodule title=$moduleTitle name="bc_notifications"}
		{if $notificationExists}
			<a href="{$smarty.request.main_page|zen_get_page_url:"action=notify_remove&products_id=`$gBitProduct->mProductsId`}">{booticon iname="icon-envelope"  ipackage="icons"  iexplain="Send Mail"}{tr}Do not notify me of updates to{/tr} {$gBitProduct->getTitle()}</a>
		{else}
			<a href="{$smarty.request.main_page|zen_get_page_url:"action=notify&products_id=`$gBitProduct->mProductsId`}">{booticon iname="icon-envelope"  ipackage="icons"  iexplain="Send Mail"}{tr}Notify me of updates to{/tr} {$gBitProduct->getTitle()}</a>
		{/if}
	{/bitmodule}
{/if}
{/strip}

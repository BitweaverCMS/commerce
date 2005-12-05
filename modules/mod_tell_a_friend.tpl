{if $sideboxTellFriend}
	{bitmodule title=$moduleTitle name="bc_tellafriend"}
		{form ipackage=bitcommerce iname="quick_find" action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=tell_a_friend"}
			<input type="hidden" name="products_id" value="{$gBitProduct->mProductsId}" />
			<input type="text" name="to_email_address" /><input type="image" src="{$smarty.const.LIBERTY_PKG_URL}icons/mail_send.png" alt="{tr}Tell a friend{/tr}" />
			<p>{tr}Tell someone you know about this product.{/tr}</p>
		{/form}
	{/bitmodule}
{/if}

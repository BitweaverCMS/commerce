{if $reorderOrdersId}
<form method="post" action="{$smarty.const.BITCOMMERCE_PKG_URL}" class="form-inline">
	<input type="hidden" name="orders_id" value="{$reorderOrdersId}" />
	<button type="submit" class="btn btn-xs btn-primary" name="action" value="reorder_order">{tr}Reorder All Items{/tr}</button>
</form>
{/if}

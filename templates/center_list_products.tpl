{if $listBoxContents}
<div class="listproducts" id="module{$moduleParams.module_id}">
<h2 class="centerboxheading">{$productListTitle|default:"Products"}</h2>

<table border="0" cellspacing="0" cellpadding="5">
{section name=ix loop=$listBoxContents}
<tr>
    {section name=jx loop=$listBoxContents[ix]}
    <td style="width:{$listColWidth}%">
		<span class="productimage"><a href="{CommerceProduct::getDisplayUrlFromHash($listBoxContents[ix][jx])}" /><img class="thumb" src="{$listBoxContents[ix][jx].products_image_url}"/></a></span>
		<div class="productinfo">
		<h3><a href="{CommerceProduct::getDisplayUrlFromHash($listBoxContents[ix][jx])}" />{$listBoxContents[ix][jx].products_name}</a></h3>
		{if $listBoxContents[ix][jx].user_id!=$smarty.const.ROOT_USER_ID}{tr}by{/tr} {displayname hash=$listBoxContents[ix][jx]}{/if}
		<div class="details">
			{if $listBoxContents[ix][jx].products_model}<span class="model">{$listBoxContents[ix][jx].products_model}</span><br/>{/if}
			{if $listBoxContents[ix][jx].lowest_purchase_price}
				{tr}Starting at:{/tr}<span class="price">{$listBoxContents[ix][jx].lowest_purchase_price}</span>
			{/if}
		</div>
		<div class="buynow"><a class="btn btn-small" href="{CommerceProduct::getDisplayUrlFromHash($listBoxContents[ix][jx])}">{tr}Buy Now{/tr}</a></div>
		</div>
    </td>
	{/section}
</tr>
{/section}
</table>

</div>
{/if}

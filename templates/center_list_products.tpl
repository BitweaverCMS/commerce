{if $listBoxContents}
<div class="listproducts" id="module{$moduleParams.module_id}">
<h2 class="centerboxheading">{$productListTitle|default:"Products"}</h2>


<div class="row">
{foreach name=listedProducts from=$listedProducts item=prod key=productId}
    <div class="col-sm-4 col-xs-6">
		<span class="productimage"><a href="{CommerceProduct::getDisplayUrlFromHash($prod)}" /><img class="img-responsive" src="{$prod.products_image_url}"/></a></span>
		<div class="productinfo">
		<h3><a href="{CommerceProduct::getDisplayUrlFromHash($prod)}" />{$prod.products_name}</a></h3>
		{if $prod.user_id!=$smarty.const.ROOT_USER_ID}{tr}by{/tr} {displayname hash=$prod}{/if}
		<div class="details">
			{if $prod.products_model}<span class="model">{$prod.products_model}</span><br/>{/if}
			{if $prod.lowest_purchase_price}
				{tr}Starting at:{/tr}<span class="price">{$prod.lowest_purchase_price}</span>
			{/if}
		</div>
		<div class="buynow"><a class="btn btn-default btn-sm" href="{CommerceProduct::getDisplayUrlFromHash($prod)}">{tr}Buy Now{/tr}</a></div>
		</div>
    </div>
{if $smarty.foreach.listedProducts.iteration%3==0}
	<!-- Add the extra clearfix for only the required viewport -->
	<div class="clearfix visible-sm"></div>
	<div class="clearfix visible-md"></div>
	<div class="clearfix visible-lg"></div>
{/if}
{if $smarty.foreach.listedProducts.iteration%2==0}
	<!-- Add the extra clearfix for only the required viewport -->
	<div class="clearfix visible-xs"></div>
{/if}
{/foreach}
</div>

{/if}

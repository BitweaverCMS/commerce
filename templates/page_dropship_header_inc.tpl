{* Progress bar for the ship-to-multiple-addresses path, the checkout bar's component. step: 1 addresses and shipping, 2 finalize, 3 orders created.
   The cart is the step before this path, as in checkout. Only step 1 is a link: the confirmation page needs the Finalize post. *}
<div class="row">
	<div class="col-sm-12 pv-1">
		<ul class="checkout-progressbar drop-ship-progressbar">
			<li class="side-style-layout active"><div class="drop-ship-step-label">{if $step > 1}<a href="{$smarty.const.BITCOMMERCE_PKG_URL}dropship">{tr}Addresses{/tr}</a>{else}{tr}Addresses{/tr}{/if}</div></li>
			<li class="side-style-layout {if $step > 1}active{/if}"><i class="fa fal fa-chevron-right fa-2x inline-block"></i><div class="drop-ship-step-label">{tr}Review{/tr}</div></li>
			<li class="side-style-layout {if $step > 2}active{/if}"><i class="fa fal fa-chevron-right fa-2x inline-block"></i><div class="drop-ship-step-label">{tr}Orders{/tr} <span class="hidden-xs">{tr}Created{/tr}</span></div></li>
		</ul>
	</div>
</div>

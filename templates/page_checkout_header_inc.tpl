<header class="page-header">
	<div class="row">
		<div class="col-sm-12 pv-1">
			<ul class="checkout-progressbar">
				<li class="side-style-layout active"><div><a href="{"checkout_shipping"|zen_href_link}">{tr}Delivery{/tr}</a></div></li>
				<li class="side-style-layout {if $step>1}active{/if}"><i class="fa fal fa-chevron-right fa-2x inline-block"></i><div>{if $step>1}<a href="{"checkout_payment"|zen_href_link}">{tr}Payment{/tr}</a>{else}{tr}Payment{/tr}{/if}</div></li>
				<li class="side-style-layout {if $step>2}active{/if}"><i class="fa fal fa-chevron-right fa-2x inline-block"></i><div>{if $step>2}<a href="{"checkout_confirmation"|zen_href_link}">{tr}Confirm{/tr}</a>{else}{tr}Confirm{/tr}{/if}</div></li>
			</ul>
		</div>
	</div>
</header>

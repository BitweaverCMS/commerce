<header class="page-header">
	<div class="row">
		<div class="col-sm-12 pv-1">
			<ul class="checkout-progressbar">
				<li class="side-style-layout active"><div><a href="/bookstore/index.php?main_page=checkout_shipping">{tr}Delivery{/tr}</a></div></li>
				<li class="side-style-layout {if $step>1}active{/if}"><i class="icon-chevron-right icon-2x inline-block"></i><div>{if $step>1}<a href="/bookstore/index.php?main_page=checkout_payment">{tr}Payment{/tr}</a>{else}{tr}Payment{/tr}{/if}</div></li>
				<li class="side-style-layout {if $step>2}active{/if}"><i class="icon-chevron-right icon-2x inline-block"></i><div>{if $step>2}<a href="/bookstore/index.php?main_page=checkout_confirmation">{tr}Confirm{/tr}</a>{else}{tr}Confirm{/tr}{/if}</div></li>
			</ul>
		
	
</header>


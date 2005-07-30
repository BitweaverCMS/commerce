{if $sideboxShoppingCartContent}
{bitmodule title=$moduleTitle name="shoppingcart"}
{literal}
<script language="javascript" type="text/javascript"><!--
function couponpopupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
{/literal}

{$sideboxShoppingCartContent}

{/bitmodule}

{/if}
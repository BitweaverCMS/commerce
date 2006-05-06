{if $manufacturers}
	{bitmodule title=$moduleTitle name="bc_manufacturersselect"}
	<form name="manufacturers">
			{html_options options=$manufacturers selected=$smarty.get.manufacturers_id name="manufacturers_id" onchange="this.form.submit();"}
			{$manufacturers}
	</form>
	{/bitmodule}
{/if}

{* from mod_manufacturers_list.tpl
	{if $manufacturersPulldown}
		{bitmodule title=$moduleTitle name="manufacturerslist"}
		$content = "";
		$content = "<ul>";
		for ($i=0;$i<sizeof($manufacturers_array);$i++) {
		$manufacturers_name = $manufacturers_array[ix].name;
		if (isset($_GET['manufacturers_id']) && ($_GET['manufacturers_id'] == $manufacturers['manufacturers_id'])) $manufacturers_name = '<strong>' . $manufacturers_name .'</strong>
		<li><a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=default&manufacturers_id={$manufacturers_array[ix].id}">' . $manufacturers_name . '</a></li>
	{/if}
	$content = "<ul>";
	{/bitmodule}
{/if}
*}

{bitmodule title=$moduleTitle|default:'Search' name="search"}
{form ipackage=bitcommerce iname="quick_find" action="`$smarty.const.BITCOMMERCE_PKG_URL`index.php?main_page=advanced_search_result"}
		<input type="hidden" name="search_in_description" value="1" />
		<input type="text" name="keyword" size="15" />
		<input type="submit" name="search" value="{tr}Search{/tr}" />

	<div class="row">
  <a href="{$smarty.const.HTTP_SERVER}{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=advanced_search">{tr}Advanced Search{/tr}</a>
	</div>
{/form}

{/bitmodule}

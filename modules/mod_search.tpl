{bitmodule title=$moduleTitle|default:'Search' name="search"}
{form ipackage=bitcommerce iname="quick_find" ifile="index.php?main_page=advanced_search_result"}
	<div class="row">
		<input type="hidden" name="search_in_description" value="1" />
		<input type="text" name="keyword" />
	</div>
	<div class="row submit">
		<input type="submit" name="search" {tr}Search{/tr} />
	</div>

	<div class="row">
  <a href="{$smarty.const.BITCOMMERCE_PKG_URL}index.php?main_page=advanced_search">{tr}Advanced Search{/tr}</a>
	</div>
{/form}

{/bitmodule}
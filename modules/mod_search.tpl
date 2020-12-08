{strip}
{bitmodule title=$moduleTitle|default:'Search' name="bc_search"}
	{form ipackage=bitcommerce action="index.php?main_page=advanced_search_result"}
		<input type="hidden" name="search_in_description" value="1" />
		<input type="text" name="keyword" size="15" />
		<input type="submit" name="search" value="{tr}Search{/tr}" />
		<div class="row">
			<a href="{'advanced_search'|zen_get_page_url}">{tr}Advanced Search{/tr}</a>
		</div>
	{/form}
{/bitmodule}
{/strip}

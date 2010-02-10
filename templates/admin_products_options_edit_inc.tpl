
<input type="hidden" name="option_id" value="{$smarty.request.products_option_id}" />

<div class="row">
	<div class="formlabel">{tr}Option Id{/tr}</div>
	{forminput}
{if $editOption.products_options_id}
		<input type="hidden" name="products_options_id" value="{$editOption.products_options_id}">{$editOption.products_options_id}
{else}
		<em>{tr}New{/tr}</em>
{/if}
	{/forminput}
</div>

<div class="row">
	{formlabel label="Title"}
	{forminput}
		<input type="text" name="products_options_name" value="{$editOption.products_options_name|escape:html}" />
	{/forminput}
</div>

<div class="row">
	{formlabel label="Type"}
	{forminput}
		{html_options name="products_options_type" options=$optionsTypes selected=$editOption.products_options_type}
	{/forminput}
</div>

<div class="row">
	{formlabel label="HTML Attributes"}
	{forminput}
		<input type="text" name="products_options_html_attrib" value="{$editOption.products_options_html_attrib|escape:html}" />
		{formhelp note="Use this for extra HTML elements, such as Javascript onchange or a custom 'id'."}
	{/forminput}
</div>

<div class="row">
	{formlabel label="Comment"}
	{forminput}
		<input type="text" name="products_options_comment" value="{$editOption.products_options_comment|escape:html}" />
	{/forminput}
</div>
<div class="row">
	{formlabel label="Sort Order"}
	{forminput}
		<input type="text" name="products_options_sort_order" value="{$editOption.products_options_sort_order}" />
	{/forminput}
</div>
<div class="row">
	{formlabel label="Display Size"}
	{forminput}
		<input type="text" name="products_options_size" value="{$editOption.products_options_size}" />
	{/forminput}
</div>
<div class="row">
	{formlabel label="Maximum Length"}
	{forminput}
		<input type="text" name="products_options_length" value="{$editOption.products_options_length}" />
	{/forminput}
</div>
<div class="row">
	{formlabel label="Attribute Images per Row"}
	{forminput}
		<input type="text" name="products_options_images_per_row" value="{$editOption.products_options_images_per_row}" />
	{/forminput}
</div>
<div class="row">
	{formlabel label="Attribute Style for Radio Buttons/Checkbox"}
	{forminput}
		<select name="products_options_images_style">
			<option value="" ></option>
			<option value="0" {if $editOption.products_options_images_style===0}selected="selected"{/if}>{tr}Images Below Option Names{/tr}</option>
			<option value="1" {if $editOption.products_options_images_style==1}selected="selected"{/if}>{tr}Element, Image and Option Value{/tr}</option>
			<option value="2" {if $editOption.products_options_images_style==2}selected="selected"{/if}>{tr}Element, Image and Option Name Below{/tr}</option>
			<option value="3" {if $editOption.products_options_images_style==3}selected="selected"{/if}>{tr}Option Name Below Element and Image{/tr}</option>
			<option value="4" {if $editOption.products_options_images_style==4}selected="selected"{/if}>{tr}Element Below Image and Option Name{/tr}</option>
			<option value="5" {if $editOption.products_options_images_style==5}selected="selected"{/if}>{tr}Element Above Image and Option Name{/tr}</option>
		</select>
	{/forminput}
</div>

<p>&nbsp;</p>

<div class="row">
	{formlabel label="Option Values"}
	{forminput}
		<ul class="data" id="optval_sortable">
		{foreach from=$editOption.values item=optionsValue key=optionsValueId}
			<li class="item {cycle values="odd,even"}" id="optval{$optionsValueId}" >
				<div id="optval{$optionsValueId}display">
					<div class="floaticon">
						<a href="#" onclick="editOptionsValue('{$optionsValueId}')">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="edit" iforce="icon"}</a>
						<a href="{$smarty.server.PHP_SELF}?action=delete&amp;option_id={$optionId}">{biticon ipackage="icons" iname="edit-delete" iexplain="delete" iforce="icon"}</a>
					</div>
					<span id="optval{$optionsValueId}title">{$optionsValue.products_options_values_name}</span>
				</div>
				<div id="optval{$optionsValueId}edit" style="display:none">
					<input type="text" name="products_options_values_name{$optionsValueId}" id="products_options_values_name{$optionsValueId}" value="{$optionsValue.products_options_values_name}" />
					<input type="submit" value="save" name="save_options_value" onclick="return saveOptionsValue('{$optionsValueId}');" />
					<input type="submit" value="cancel" name="cancel_options_value" onclick="return editOptionsValue('{$optionsValueId}');" />
				</div>
			</li>
		{/foreach}
		</ul>
{literal}
<script type="text/javascript">
function editOptionsValue( pOptValId ) {
	BitBase.toggleElementDisplay( 'optval'+pOptValId+'edit', 'block' );
	BitBase.toggleElementDisplay( 'optval'+pOptValId+'display', 'block' );
	return false;
}
function saveOptionsValue( pOptValId ) {
	document.getElementById('optval'+pOptValId+'title').innerHTML=document.getElementById('products_options_value_name'+pOptValId).value;
	editOptionsValue( pOptValId );
	return false;
}

  MochiKit.Sortable.Sortable.create('optval_sortable',{onUpdate:function(){alert('drop!')}});
</script>
{/literal}
	{/forminput}
</div>

<div class="row submit">
	<input type="submit" name="save_option" value="{tr}Save{/tr}" />
	<input type="submit" name="cancel" value="{tr}Cancel{/tr}">
</div>


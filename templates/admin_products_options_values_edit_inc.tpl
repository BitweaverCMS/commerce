{form method="post" enctype="multipart/form-data"}

	<div class="control-group">
		<div class="formlabel">{tr}Option Value Id{/tr}</div>
		{forminput}
{if $editValue.products_options_values_id}
			<input type="hidden" name="products_options_values_id" value="{$editValue.products_options_values_id}">{$editValue.products_options_values_id}
{else}
			<em>{tr}New{/tr}</em>
{/if}
		{/forminput}
	</div>

	<div class="control-group">
		<div class="formlabel">{tr}Options Name{/tr}</div>
		<div class="forminput"><input type="text" name="products_options_values_name" value="{$editValue.products_options_values_name|escape:html}">&nbsp;</div>
	</div>
	
	<div class="control-group">
		<div class="formlabel">{tr}Option{/tr}</div>
		<div class="forminput">
			<select name="products_options_id">
				{foreach from=$optionsList item=option key=optionsId}
				<option value="{$option.products_options_id}" 
				{if $editValue.products_options_id == $option.products_options_id}selected="selected"{/if}
				>[{$option.products_options_types_name|strtoupper|escape:html}] {$option.products_options_name|escape:html}</option>
				{/foreach}
			</select>
		</div>
	</div>

	<div class="control-group">
		<div class="formlabel">{tr}Sort Order{/tr}</div>
		<div class="forminput"><input type="text" name="products_options_sort_order" value="{$editValue.products_options_sort_order}" size="4">&nbsp;</div>
	</div>

	<div class="control-group">
		<div class="formlabel">{tr}Related Group ID{/tr}</div>
		<div class="forminput">{html_options name="purchase_group_id" options=$groupList selected=$editValue.purchase_group_id}
			{formhelp note="User will be added to this group upon successful purchase"}
		</div>
	</div>


	<h2>{tr}Attribute Pricing{/tr}</h2>
	<div class="control-group">
		<div class="formlabel">{tr}Fixed Attribute Price{/tr}</div>
		<div class="forminput"><input style="width:auto;" type="text" name="price_prefix" size="1" maxlength="1" value="{$editValue.price_prefix|default:'+'}" />&nbsp;<input type="text" name="options_values_price" value="{$editValue.options_values_price}" size="6" />&nbsp;</div>
	</div>
	<div class="control-group">
		<div class="formlabel">{tr}One Time{/tr}</div>
		<div class="forminput"><input type="text" name="attributes_price_onetime" value="{$editValue.attributes_price_onetime}" size="6" />&nbsp;</div>
	</div>

{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_PRICE_FACTOR') == 'true'}
	<div class="control-group">
		<div class="formlabel">{tr}% Attribute Price{/tr}</div>
		<div class="forminput"><input type="text" name="attributes_price_factor" value="{$editValue.attributes_price_factor}" size="6" /></div>
	</div>
	<div class="control-group">
		<div class="formlabel">{tr}Offset{/tr}</div>
		<div class="forminput"><input type="text" name="attributes_pf_offset" size="6" value="{$editValue.attributes_pf_offset}" />&nbsp;</div>
	</div>
	<div class="control-group">
		<div class="formlabel">{tr}One Time Factor{/tr}</div>
		<div class="forminput"><input type="text" name="attributes_pf_onetime" value="{$editValue.attributes_pf_onetime}" size="6" /></div>
	</div>
	<div class="control-group">
		<div class="formlabel">{tr}Offset{/tr}</div>
		<div class="forminput"><input type="text" name="attributes_pf_onetime_offset" value="{$editValue.attributes_pf_onetime_offset}" size="6" /></div>
	</div>
{/if}

{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_QTY_PRICES') == 'true'}
			<h2>{tr}Attribute Quantity Pricing{/tr}</h2>
			<div class="control-group">
				<div class="formlabel">{tr}Option Qty Price Discount{/tr}</div>
				<div class="forminput"><input type="text" name="attributes_qty_prices" value="{$editValue.attributes_qty_prices}" size="60"></div>
			</div>
			<div class="control-group">
				<div class="formlabel">{tr}Onetime Option Qty Price Discount{/tr}</div>
				<div class="forminput"><input type="text" name="attributes_qty_prices_onetime" value="{$editValue.attributes_qty_prices_onetime}" size="60"></div>
			</div>
{/if}

{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_TEXT_PRICES') == 'true'}
			<h2>{tr}Attribute Text Pricing{/tr}</h2>
			<div class="control-group">
				<div class="formlabel">{tr}Price Per Word{/tr}</div>
				<div class="forminput"><input type="text" name="attributes_price_words" value="{$editValue.attributes_price_words}" size="6" /></div>
			</div>
			<div class="control-group">
				<div class="formlabel">{tr}- Free Words{/tr}</div>
				<div class="forminput"><input type="text" name="attributes_price_words_free" value="{$editValue.attributes_price_words_free}" size="6" /></div>
			</div>
			<div class="control-group">
				<div class="formlabel">{tr}Price Per Letter{/tr}</div>
				<div class="forminput"><input type="text" name="attributes_price_letters" value="{$editValue.attributes_price_letters}" size="6" /></div>
			</div>
			<div class="control-group">
				<div class="formlabel">{tr}- Free Letters{/tr}</div>
				<div class="forminput"><input type="text" name="attributes_price_letters_free" value="{$editValue.attributes_price_letters_free}" size="6" /></div>
			</div>
{/if}

			<h2>{tr}Attribute Weights{/tr}</h2>
			<div class="control-group">
				<div class="formlabel">{tr}Weight{/tr}</div>
				<div class="forminput"><input style="width:auto;" type="text" name="products_attributes_wt_pfix" size="1" maxlength="1"  value="{$editValue.products_attributes_wt_pfix|default:'+'}" />&nbsp;<input type="text" name="products_attributes_wt" value="{$editValue.products_attributes_wt}" size="6" />&nbsp;</div>
			</div>

			<h2>{tr}Attribute Flags{/tr}</h2>
			
			<div class="control-group">
				{forminput}
					<span style="background-color:#ffff00; padding:4px"><input type="checkbox" name="attributes_display_only" value="1" {if $editValue.attributes_display_only==1}checked="checked"{/if} /></span>{tr}Used For Display Purposes Only{/tr}<br/>
					<span style="background-color:#0000ff; padding:4px"><input type="checkbox" name="product_attribute_is_free" value="1" {if $editValue.product_attribute_is_free==1}checked="checked"{/if} /></span>{tr}Attribute is Free When Product is Free{/tr}<br/>
					<span style="background-color:#ffa346; padding:4px"><input type="checkbox" name="attributes_default" value="1" {if $editValue.attributes_default==1}checked="checked"{/if} /></span>{tr}Default Attribute to be Marked Selected{/tr}<br/>
					<span style="background-color:#ff00ff; padding:4px"><input type="checkbox" name="attributes_discounted" value="1" {if !isset($editValue.attributes_discounted) || $editValue.attributes_discounted}checked="checked"{/if} /></span>{tr}Apply Discounts Used by Product Special/Sale{/tr}<br/>
					<span style="background-color:#d200f0; padding:4px"><input type="checkbox" name="attributes_price_base_inc" value="1" {if !isset($editValue.attributes_price_base_inc) || $editValue.attributes_price_base_inc}checked="checked"{/if}/></span>{tr}Include in Base Price When Priced by Options{/tr}<br/>
					<span style="background-color:#fd0000; padding:4px"><input type="checkbox" name="attributes_required" value="1" {if $editValue.attributes_required==1}checked="checked"{/if} /></span>{tr}Attribute Required for Text{/tr}<br/>
				{/forminput}
			</div>


{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_IMAGES') == 'true'}
	<h2>{tr}Attribute Image{/tr}</h2>
	
	<div class="control-group">
		{formlabel label="Options Image Swatch"}
		{forminput}<input name="attributes_image" size="50" type="file">{/forminput}
	</div>
	
	<div class="control-group">
		{formlabel label="Option Image Directory"}
		{forminput}
			<select name="img_dir">
				<option value="">Main Directory</option>
			</select>
		{/forminput}
	</div>
	
	<div class="control-group">
		{formlabel label="Overwrite Existing Image?"}
		{forminput}
			<input name="overwrite" value="0" type="radio">&nbsp;No <input name="overwrite" value="1" checked="checked" type="radio">&nbsp;Yes
			{formhelp note="Use No for manually typed names"}
		{/forminput}
	</div>
{else}
	<div class="control-group">
		{forminput}{tr}Disabled{/tr}{/forminput}
	</div>
{/if}

	<h2>{tr}Downloadable Products{/tr}</h2>
	
{if $gCommerceSystem->getConfig('DOWNLOAD_ENABLED') == 'true'}
	<div class="control-group">
		{formlabel label="Filename"}
		{forminput}
			<input name="products_attributes_filename|escape:html" size="1" maxlength="" type="text">
		{/forminput}
	</div>
	<div class="control-group">
		{formlabel label="Expiry days"}
		{forminput}
			<input name="products_attributes_maxdays" value="{$gCommerceSystem->getConfig('DOWNLOAD_MAX_DAYS')}" size="5" type="text">
		{/forminput}
	</div>
	<div class="control-group">
		{formlabel label="Maximum download count"}
		{forminput}
			<input name="products_attributes_maxcount" value="{$gCommerceSystem->getConfig('DOWNLOAD_MAX_COUNT')}" size="5" type="text">
		{/forminput}
	</div>
{else}
	<div class="control-group">
		{forminput}{tr}Disabled{/tr}e{/forminput}
	</div>
{/if}

	<div class="control-group submit">
			<input type="submit" name="save_attribute" value="{tr}Save{/tr}" />
			<input type="submit" name="cancel" value="{tr}Cancel{/tr}" />
	</div>

{/form}

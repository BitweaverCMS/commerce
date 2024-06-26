
	<div class="form-group">
		{formlabel label="Option Value Id"}
		{forminput}
		{if $editValue.products_options_values_id}
			{$editValue.products_options_values_id}
		{else}
			{booticon iname="fa-input-numeric" class="link" iexplain="Edit" onclick="$('.advanced-input').toggle()"} <em class="advanced-input">{tr}New{/tr}</em>
		{/if}
			<input style="display:none" class="advanced-input" type="number" min="0" step="0" id="products_options_values_id" name="products_options_values_id" value="{$editValue.products_options_values_id}">
			{formhelp style="display:none" class="advanced-input" note="ADVANCED: Set Option Value Id manually. DO NOT USE this unless you know why it is necessary."}
		{/forminput}
	</div>

	<div class="form-group">
		{formlabel label="Options Name"}
		<div class="forminput"><input type="text" class="form-control" name="products_options_values_name" value="{$editValue.products_options_values_name|escape:html}">&nbsp;</div>
	</div>

	<div class="form-group">
		{formlabel label="Comment"}
		{forminput}
			<input type="text" class="form-control" name="products_options_values_comment" value="{$editValue.products_options_values_comment|escape:html}" />
			{formhelp note="A small help-block will be shown below this selection"}
		{/forminput}
	</div>
	
	<div class="form-group">
		{formlabel label="Option"}
		<div class="forminput">
			<select class="form-control" name="products_options_id">
				{foreach from=$optionsList item=option key=optionsId}
				<option value="{$option.products_options_id}" 
				{if $editValue.products_options_id == $option.products_options_id}selected="selected"{/if}
				>[{$option.products_options_types_name|strtoupper|escape:html}] {$option.products_options_name|escape:html}</option>
				{/foreach}
			</select>
		</div>
	</div>

	<div class="form-group">
		{formlabel label="Sort Order"}
		<div class="forminput"><input type="text" class="form-control" name="products_options_sort_order" value="{$editValue.products_options_sort_order}" size="4">&nbsp;</div>
	</div>

	<div class="form-group">
		{formlabel label="Related Group ID"}
		<div class="forminput">{html_options class="form-control" name="purchase_group_id" options=$groupList selected=$editValue.purchase_group_id}
			{formhelp note="User will be added to this group upon successful purchase"}
		</div>
	</div>


	{legend legend="Attribute Pricing"}
		<div class="form-group">
			{formlabel label="Fixed Attribute Price"}
			<div class="input-group">
				<span class="input-group-addon pt-0" style="width:75px">
					<select class="form-control input-xs" name="price_prefix">
						<option value="+"> + </option>
						<option value="-" {if $editValue.price_prefix == '-'}selected{/if}> - </option>
					</select>
				</span>
				<input type="text" class="form-control" name="options_values_price" value="{$editValue.options_values_price}" />
			</div>

		</div>
		<div class="form-group">
			{formlabel label="One Time"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_price_onetime" value="{$editValue.attributes_price_onetime}" size="6" />&nbsp;</div>
		</div>

	{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_PRICE_FACTOR') == 'true'}
		<div class="form-group">
			{formlabel label="% Attribute Price"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_price_factor" value="{$editValue.attributes_price_factor}" size="6" /></div>
		</div>
		<div class="form-group">
			{formlabel label="Offset"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_pf_offset" size="6" value="{$editValue.attributes_pf_offset}" />&nbsp;</div>
		</div>
		<div class="form-group">
			{formlabel label="One Time Factor"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_pf_onetime" value="{$editValue.attributes_pf_onetime}" size="6" /></div>
		</div>
		<div class="form-group">
			{formlabel label="Offset"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_pf_onetime_offset" value="{$editValue.attributes_pf_onetime_offset}" size="6" /></div>
		</div>
	{/if}
	{/legend}

{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_QTY_PRICES') == 'true'}
	{legend legend="Attribute Quantity Pricing"}
		<div class="form-group">
			{formlabel label="Option Qty Price Discount"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_qty_prices" value="{$editValue.attributes_qty_prices}" size="60"></div>
		</div>
		<div class="form-group">
			{formlabel label="Onetime Option Qty Price Discount"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_qty_prices_onetime" value="{$editValue.attributes_qty_prices_onetime}" size="60"></div>
		</div>
	{/legend}
{/if}

{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_TEXT_PRICES') == 'true'}
	{legend legend="Attribute Text Pricing"}
		<div class="form-group">
			{formlabel label="Price Per Word"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_price_words" value="{$editValue.attributes_price_words}" size="6" /></div>
		</div>
		<div class="form-group">
			{formlabel label="- Free Words"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_price_words_free" value="{$editValue.attributes_price_words_free}" size="6" /></div>
		</div>
		<div class="form-group">
			{formlabel label="Price Per Letter"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_price_letters" value="{$editValue.attributes_price_letters}" size="6" /></div>
		</div>
		<div class="form-group">
			{formlabel label="- Free Letters"}
			<div class="forminput"><input type="text" class="form-control" name="attributes_price_letters_free" value="{$editValue.attributes_price_letters_free}" size="6" /></div>
		</div>
	{/legend}
{/if}

	{legend legend="Attribute Weights"}
		<div class="form-group">
			{formlabel label="Weight"}
			<div class="form-input"><input type="text" class="form-control" name="products_attributes_wt" value="{$editValue.products_attributes_wt}"/></div>
		</div>
	{/legend}

	{legend legend="Attribute Flags"}
		<div class="checkbox">
			<label><input type="checkbox" name="attributes_display_only" value="1" {if $editValue.attributes_display_only==1}checked="checked"{/if} /> {tr}Used For Display Purposes Only{/tr}</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" name="product_attribute_is_free" value="1" {if $editValue.product_attribute_is_free==1}checked="checked"{/if} /> {tr}Attribute is Free When Product is Free{/tr}</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" name="attributes_default" value="1" {if $editValue.attributes_default==1}checked="checked"{/if} /> {tr}Default Attribute to be Marked Selected{/tr}</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" name="attributes_discounted" value="1" {if !isset($editValue.attributes_discounted) || $editValue.attributes_discounted}checked="checked"{/if} /> {tr}Apply Discounts Used by Product Special/Sale{/tr}</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" name="attributes_price_base_inc" value="1" {if !isset($editValue.attributes_price_base_inc) || $editValue.attributes_price_base_inc}checked="checked"{/if}/> {tr}Include in Base Price When Priced by Options{/tr}</label>
		</div>
		<div class="checkbox">
			<label><input type="checkbox" name="attributes_required" value="1" {if $editValue.attributes_required==1}checked="checked"{/if} /> {tr}Attribute Required for Text{/tr}</label>
		</div>
	{/legend}


	{legend legend="Attribute Image"}
	{if $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_IMAGES') == 'true'}
		<div class="form-group">
			{formlabel label="Options Image Swatch"}
			{forminput}<input name="attributes_image" size="50" type="file">{/forminput}
		</div>
		
		<div class="form-group">
			{formlabel label="Overwrite Existing Image?"}
			{forminput}
				<input name="overwrite" value="0" type="radio">&nbsp;No <input name="overwrite" value="1" checked="checked" type="radio">&nbsp;Yes
				{formhelp note="Use No for manually typed names"}
			{/forminput}
		</div>
	{else}
		<div class="form-group">
			{forminput}{tr}Disabled{/tr}{/forminput}
		</div>
	{/if}
	{/legend}

	{legend legend="Downloadable Products"}
	{if $gCommerceSystem->getConfig('DOWNLOAD_ENABLED') == 'true'}
		<div class="form-group">
			{formlabel label="Filename"}
			{forminput}
				<input name="products_attributes_filename|escape:html" size="1" maxlength="" type="text">
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Expiry days"}
			{forminput}
				<input name="products_attributes_maxdays" value="{$gCommerceSystem->getConfig('DOWNLOAD_MAX_DAYS')}" size="5" type="text">
			{/forminput}
		</div>
		<div class="form-group">
			{formlabel label="Maximum download count"}
			{forminput}
				<input name="products_attributes_maxcount" value="{$gCommerceSystem->getConfig('DOWNLOAD_MAX_COUNT')}" size="5" type="text">
			{/forminput}
		</div>
	{else}
		<div class="form-group">
			{forminput}{tr}Disabled{/tr}e{/forminput}
		</div>
	{/if}
	{/legend}

	<div class="form-group submit">
			<input type="submit" class="btn btn-default" name="save_attribute" value="{tr}Save{/tr}" />
			<input type="submit" class="btn btn-default" name="cancel" value="{tr}Cancel{/tr}" />
	</div>


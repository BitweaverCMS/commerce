<?php
//
// +------------------------------------------------------------------------+
// |zen-cart Open Source E-commerce											|
// +------------------------------------------------------------------------+
// | Copyright (c) 2004 The zen-cart developers								|
// |																		|
// | http://www.zen-cart.com/index.php										|
// |																		|
// | Portions Copyright (c) 2003 osCommerce									|
// +------------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			|
// | that is bundled with this package in the file LICENSE, and is			|
// | available through the world-wide-web at the following url:				|
// | http://www.zen-cart.com/license/2_0.txt.								|
// | If you did not receive a copy of the zen-cart license and are unable	|
// | to obtain it through the world-wide-web, please send a note to			|
// | license@zen-cart.com so we can mail you a copy immediately.			|
// +------------------------------------------------------------------------+
//  $Id$
//


		$manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
		$manufacturers = $gBitDb->Execute("select `manufacturers_id`, `manufacturers_name` from " . TABLE_MANUFACTURERS . " order by `manufacturers_name`");
		while (!$manufacturers->EOF) {
			$manufacturers_array[] = array('id' => $manufacturers->fields['manufacturers_id'], 'text' => $manufacturers->fields['manufacturers_name']);
			$manufacturers->MoveNext();
		}

		$suppliers_array = array(array('id' => '', 'text' => TEXT_NONE));
		$suppliers = $gBitDb->Execute("select `suppliers_id`, `suppliers_name` from " . TABLE_SUPPLIERS . " order by `suppliers_name`");
		while (!$suppliers->EOF) {
			$suppliers_array[] = array('id' => $suppliers->fields['suppliers_id'], 'text' => $suppliers->fields['suppliers_name']);
			$suppliers->MoveNext();
		}

		$tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
		$tax_class = $gBitDb->Execute("select `tax_class_id`, `tax_class_title` from " . TABLE_TAX_CLASS . " order by `tax_class_title`");
		while (!$tax_class->EOF) {
			$tax_class_array[] = array('id' => $tax_class->fields['tax_class_id'], 'text' => $tax_class->fields['tax_class_title']);
			$tax_class->MoveNext();
		}

		$languages = zen_get_languages();

?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script type="text/javascript"><!--
	var dateAvailable = new ctlSpiffyCalendarBox("dateAvailable", "new_product", "products_date_available","btnDate1","<?php echo $gBitProduct->getField( 'products_date_available' ); ?>",scBTNMODE_CUSTOMBLUE);
//--></script>
<script type="text/javascript"><!--
var tax_rates = new Array();
<?php
		for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
			if ($tax_class_array[$i]['id'] > 0) {
				echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . zen_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
			}
		}
?>
function doRound(x, places) {
	return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
	var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
	var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

	if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
		return tax_rates[parameterVal];
	} else {
		return 0;
	}
}

function updateGross() {
	var taxRate = getTaxRate();
	var grossValue = document.forms["new_product"].products_price.value;

	if (taxRate > 0) {
		grossValue = grossValue * ((taxRate / 100) + 1);
	}

	document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
			updateProfit();
			updateMargin();	 
			updateRounding();		
}

function updateNet() {
	var taxRate = getTaxRate();
	var netValue = document.forms["new_product"].products_price_gross.value;

	if (taxRate > 0) {
		netValue = netValue / ((taxRate / 100) + 1);
	}

	document.forms["new_product"].products_price.value = doRound(netValue, 4);
			updateProfit();
			updateMargin();
			updateRounding();		
}
function updateFromMargin() {
	 document.forms["new_product"].products_price.value = (document.forms["new_product"].products_cogs.value/100)*document.forms["new_product"].products_margin.value + parseFloat(document.forms["new_product"].products_cogs.value);
	var taxRate = getTaxRate();
	var grossValue = document.forms["new_product"].products_price.value;
	if (taxRate > 0) {
		grossValue = grossValue * ((taxRate / 100) + 1);
	}
	document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
	updateProfit();
	RoundingHold();
}
function updateFromProfit() {
	document.forms["new_product"].products_price.value = parseFloat(document.forms["new_product"].products_cogs.value) + parseFloat(document.forms["new_product"].products_profit.value);
	var taxRate = getTaxRate();
	var grossValue = document.forms["new_product"].products_price.value;
	if (taxRate > 0) {
		grossValue = grossValue * ((taxRate / 100) + 1);
	}
	document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);	 
	updateMargin();
}
function updateFromCost() {	 
	 updateProfit();	 
	 updateMargin();	 
}
function updateProfit() {
	 var profit;
	 profit = (document.forms["new_product"].products_price.value)-(document.forms["new_product"].products_cogs.value);
	 document.forms["new_product"].products_profit.value = doRound(profit, 2);
}
function updateMargin() {	 
	 var margin;
	 margin = (document.forms["new_product"].products_price.value / (document.forms["new_product"].products_cogs.value / 100)) - 100;	
	 document.forms["new_product"].products_margin.value = doRound(margin, 2);
}
function updateRounding() {	
	var someStr;
	var someArray
	someStr = document.forms["new_product"].products_price_gross.value;
	someArray = someStr.split('.');
	rounding = someArray[1];
	document.forms["new_product"].products_rounding.value = doRound(rounding, 2);
}
function updateFromRounding() {	
	var grossStr;
	var grossArray
	grossStr = document.forms["new_product"].products_price_gross.value;
	grossArray = grossStr.split('.');
	grossStr = grossArray[0];
	grossStr += "." + document.forms["new_product"].products_rounding.value;
	document.forms["new_product"].products_price_gross.value = grossStr;
	updateNet();
}
function RoundingHold() {	
	var grossStr;
	var grossArray
	grossStr = document.forms["new_product"].products_price_gross.value;
	grossArray = grossStr.split('.');
	grossStr = grossArray[0];
	grossStr += "." + document.forms["new_product"].products_rounding.value;
	document.forms["new_product"].products_price_gross.value = grossStr;
}

//--></script>
<?php
//	echo $type_admin_handler;
echo zen_draw_form_admin('new_product', $type_admin_handler , 'cPath=' . $cPath . (isset($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset($_GET['products_id']) ? '&products_id=' . $_GET['products_id'] : '') . '&action=update_product' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"'); ?>

	<?php echo zen_output_generated_category_path($current_category_id); ?>
<?php
// show when product is linked
if( !empty( $_GET['products_id'] ) && zen_get_product_is_linked($_GET['products_id']) == 'true') {
?>
	<div class="form-group">
		<label><?php echo TEXT_MASTER_CATEGORIES_ID; ?></label>
		<div><?php
				// echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $gBitProduct->getField( 'products_tax_class_id' ));
				echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '&nbsp;&nbsp;';
				echo zen_draw_pull_down_menu('master_categories_id', zen_get_master_categories_pulldown($_GET['products_id']), $gBitProduct->getField( 'master_categories_id' )); ?>
		</div>
		<div><?php echo TEXT_INFO_MASTER_CATEGORIES_ID; ?></div>
	</div>
<?php } ?>

<?php
echo ($gBitProduct->isValid() && !$gBitProduct->getField( 'products_status' ) ? '<div class="alert alert-danger">' . tra( 'This product is disabled and cannot be purchased. ' ) . '</div>' : ''); 

// hidden fields not changeable on products page
echo zen_draw_hidden_field('master_categories_id', $gBitProduct->getField('master_categories_id', $current_category_id ) );
echo zen_draw_hidden_field('products_discount_type', $gBitProduct->getField( 'products_discount_type' ));
echo zen_draw_hidden_field('products_discount_type_from', $gBitProduct->getField( 'products_discount_type_from' ));
echo zen_draw_hidden_field('lowest_purchase_price', $gBitProduct->getField( 'lowest_purchase_price' ));

	for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
<fieldset><legend><?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?> <?php echo tra( 'Title and Description' ); ?> (<?php echo $languages[$i]['name']; ?>)</legend>
	<div class="form-group">
		<label><?php if ($i == 0) echo TEXT_PRODUCTS_NAME; ?></label>
		<?php echo zen_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (isset($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : zen_get_products_name($gBitProduct->getField( 'products_id' ), $languages[$i]['id'])), zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_name')) ?>
	</div>
	<div class="form-group">
		<label><?php if ($i == 0) echo TEXT_PRODUCTS_DESCRIPTION; ?></label>
				<?php if (is_null($_SESSION['html_editor_preference_status'])) echo TEXT_HTML_EDITOR_NOT_DEFINED; ?>
				<?php if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") {
//					if ($_SESSION['html_editor_preference_status']=="FCKEDITOR") require(DIR_FS_ADMIN_INCLUDES.'fckeditor.php');
					$oFCKeditor = new FCKeditor ;
					$oFCKeditor->Value = (isset($products_description[$languages[$i]['id']])) ? stripslashes($products_description[$languages[$i]['id']]) : zen_get_products_description($gBitProduct->getField( 'products_id' ), $languages[$i]['id']) ;
					$oFCKeditor->CreateFCKeditor( 'products_description[' . $languages[$i]['id'] . ']', NULL, '230' ) ;	//instanceName, width, height (px or %)
				} else { // using HTMLAREA or just raw "source"
					echo zen_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', NULL, '10', (isset($products_description[$languages[$i]['id']])) ? stripslashes($products_description[$languages[$i]['id']]) : zen_get_products_description($gBitProduct->getField( 'products_id' ), $languages[$i]['id'])); //,'id="'.'products_description' . $languages[$i]['id'] . '"');
		} ?>
<?php } ?>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_IMAGE; ?></label>
		<?php echo zen_draw_file_field('products_image') . '<br />' . $gBitProduct->getField( 'products_image' ) . zen_draw_hidden_field('products_previous_image', $gBitProduct->getField( 'products_image' )); ?>
	</div>
<?php 
	if( $gBitProduct->isValid() ) {
		echo $gBitProduct->getField( 'products_id' );
	} else { ?>
	<div class="form-group">
		<label><?php echo tra( 'Products ID' ); ?></label>
		<?php echo zen_draw_input_field('products_id_req'); ?>
		<div class="help-block"><?php echo tra( 'This is an advanced option to request the exact ID of the product you are creating. If left blank, an ID will be auto-generated for you. However, if you choose your own, choose wisely because future product creation may fail.' ); ?></div>
	</div>
<?php } ?>
</fieldset>
<fieldset>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_SORT_ORDER; ?></label>
		<?php echo zen_draw_input_field('products_sort_order', $gBitProduct->getField( 'products_sort_order' )); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?><br /><small>(YYYY-MM-DD)</small></label>
		<script type="text/javascript">dateAvailable.writeControl(); dateAvailable.dateFormat="yyyy-MM-dd";</script>
	</div>
	<div class="form-group">
		<label><?=tra("Related Group ID")?></label>
		<select class="form-control" name="purchase_group_id">
			<option value=""></value>
<?php
	global $gBitUser;
	$listHash = array();
	$groups = $gBitUser->getAllGroups( $listHash );

	foreach( $groups as $group ) {
		print '<option value="'.$group['group_id'].'" '.($gBitProduct->getField( 'purchase_group_id' ) == $group['group_id'] ? 'selected="selected"': '') .' >'.$group['group_name']."</option>\n";
	}
?>
		</select>
		<div class="help-block"><?php echo tra('User will be added to this group upon successful purchase. We recommend "Paying Customers" or similar.' ); ?></div>
	</div>
</fieldset>

<fieldset><legend><?php echo tra( 'Pricing' ); ?></legend>
<script type="text/javascript"><!--
updateGross();
//--></script>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('product_is_free', '1', $gBitProduct->getField( 'product_is_free' ), NULL, TEXT_PRODUCT_IS_FREE ); ?>
		<?php echo ($gBitProduct->getField( 'product_is_free' ) == 1 ? '<div class="alert alert-warning">' . TEXT_PRODUCTS_IS_FREE_EDIT . '</div>' : ''); ?>
	</div>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('product_is_call', '1', $gBitProduct->getField( 'product_is_call' ), NULL, TEXT_PRODUCT_IS_CALL ); ?>
		<?php echo ($gBitProduct->getField( 'product_is_call' ) == 1 ? '<div class="alert alert-warning">' . TEXT_PRODUCTS_IS_CALL_EDIT . '</div>' : ''); ?>
	</div>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('products_priced_by_attribute', '1', $gBitProduct->getField( 'products_priced_by_attribute' ), NULL, TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES ); ?>
		<?php echo ($gBitProduct->getField( 'products_priced_by_attribute' ) == 1 ? '<div class="alert alert-warning">' . TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT . '</div>' : ''); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></label>
		<?php echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $gBitProduct->getField( 'products_tax_class_id' ), 'onchange="updateGross()"'); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_COGS_NET; ?></label>
		<?php echo zen_draw_input_field('products_cogs', $gBitProduct->getField( 'products_cogs' ) , 'onKeyUp="updateFromCost()"'); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_MARGIN; ?></label>
		<?php echo zen_draw_input_field('products_margin', isset($products_margin) ? $products_margin : 0 , 'onKeyUp="updateFromMargin()"'); ?>
	</div> 
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_PROFIT; ?></label>
		<?php echo zen_draw_input_field('products_profit', isset($products_profit) ? $products_profit : 0 , 'onKeyUp="updateFromProfit()"'); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_ROUNDING; ?></label>
		<?php echo zen_draw_input_field('products_rounding', isset($products_rounding) ? $products_rounding : 0 , 'onKeyUp="updateFromRounding()"'); ?>
	</div>																													
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_PRICE_NET; ?></label>
		<?php echo zen_draw_input_field('products_price', $gBitProduct->getField( 'products_price' ), 'onKeyUp="updateGross()"'); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></label>
		<?php echo zen_draw_input_field('products_price_gross', $gBitProduct->getField( 'products_price' ), 'OnKeyUp="updateNet()"'); ?>
	</div>
	<div class="form-group">
		<label><?php echo tra( 'Products Commission' ); ?></label>
		<?php echo zen_draw_input_field( 'products_commission', $gBitProduct->getField( 'products_commission' ) ); ?>
	</div>
</fieldset>			
		 
<fieldset><legend><?php echo tra( 'Shipping and Quantity' );?></legend>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('products_status', '1', ($gBitProduct->isValid() ? $gBitProduct->getField( 'products_status' ) : '1'), NULL, tra( 'Enabled, Available For Purchase' ) ); ?>
		<?php echo ($gBitProduct->isValid() && !$gBitProduct->getField( 'products_status' ) ? '<div class="alert alert-danger">' . tra( 'This product is disabled and cannot be purchased. ' ) . '</div>' : ''); ?>
	</div>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('products_virtual', '1', $gBitProduct->getField( 'products_virtual' ), NULL, tra( 'Virtual Product, Skip Shipping Address' )); ?>
		<?php echo ($gBitProduct->getField( 'products_virtual' ) == 1 ? '<div class="alert alert-warning">' . TEXT_VIRTUAL_EDIT . '</div>' : ''); ?>
	</div>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('product_is_always_free_ship', '1', $gBitProduct->getField( 'product_is_always_free_ship' ), NULL, TEXT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING ); ?>
		<?php echo ($gBitProduct->getField( 'product_is_always_free_ship' ) == 1 ? '<div class="alert alert-warning">' . TEXT_FREE_SHIPPING_EDIT . '</div>' : ''); ?>
	</div>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('products_qty_box_status', '1', $gBitProduct->getField( 'products_qty_box_status', 1 ), NULL, TEXT_PRODUCTS_QTY_BOX_STATUS ); ?>
		<?php echo ($gBitProduct->isValid() && $gBitProduct->getField( 'products_qty_box_status' ) == 0 ? '<div class="alert alert-warning">' . TEXT_PRODUCTS_QTY_BOX_STATUS_EDIT . '</div>' : ''); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_WEIGHT; ?></label>
		<?php echo zen_draw_input_field('products_weight', $gBitProduct->getField( 'products_weight' ) ); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_QUANTITY; ?></label>
		<?php echo zen_draw_input_field('products_quantity', $gBitProduct->getField( 'products_quantity' )); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_QUANTITY_MIN_RETAIL; ?></label>
		<?php echo zen_draw_input_field('products_quantity_order_min', ($gBitProduct->getField( 'products_quantity_order_min' ) == 0 ? 1 : $gBitProduct->getField( 'products_quantity_order_min' ))); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL; ?></label>
		<?php echo zen_draw_input_field('products_quantity_order_max', $gBitProduct->getField( 'products_quantity_order_max' )); ?>
		<div class="help-block"><?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT; ?></div>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL; ?></label>
		<?php echo zen_draw_input_field('products_quantity_order_units', ($gBitProduct->getField( 'products_quantity_order_units' ) == 0 ? 1 : $gBitProduct->getField( 'products_quantity_order_units' ))); ?>
	</div>
	<div class="form-group">
		<?php echo zen_draw_checkbox_field('products_quantity_mixed', '1', $gBitProduct->getField( 'products_quantity_mixed' ), NULL, TEXT_PRODUCTS_MIXED ); ?>
	</div>
</fieldset>			

<?php
	$dir = dir(DIR_FS_CATALOG_IMAGES);
	$dir_info[] = array('id' => '', 'text' => "Main Directory");
	while ($file = $dir->read()) {
		if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
			$dir_info[] = array('id' => $file . '/', 'text' => $file);
		}
	}

	$default_directory = substr( $gBitProduct->getField( 'products_image' ), 0,strpos( $gBitProduct->getField( 'products_image' ), '/')+1);
?>

<fieldset><legend><?php echo tra( 'Subscription' );?></legend>
	<div class="form-group">
		<label><?php echo tra( 'Subscription Frequency' ); ?>:</label>
		<div class="row">
			<div class="col-xs-6">
				<select class="form-control" name="reorders_interval_number">
					<option value="">None</option>
						<?php for( $i=1; $i<=12; $i++ ) { print "<option value=\"$i\" ".($gBitProduct->getField( 'reorders_interval' ) == $i ? 'selected="selected"' : '' ).">$i</option>\n"; } ?>
				</select>
			</div>
			<div class="col-xs-6">
				<select class="form-control" name="reorders_interval">
					<option value="Years">Years</option>
					<option value="Months">Months</option>
					<option value="Weeks">Weeks</option>
					<option value="Days">Days</option>
				</select>
			</div>
		</div>
	</div>
	<div class="form-group">
		<label><?php echo tra( 'Subscription Repeats' ); ?>:</label>
		<input type="text" name="reorders_pending" value="<?php echo $gBitProduct->getField( 'reorders_pending' ) ? $gBitProduct->getField( 'reorders_pending' ) : 999; ?>" />
	</div>
</fieldset>
<fieldset><legend><?php echo tra( 'Manufacturer Details' ); ?></legend>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></label>
		<?php echo zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $gBitProduct->getField( 'manufacturers_id' )); ?>
	</div>					
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_MANUFACTURERS_MODEL; ?></label>
		<?php echo zen_draw_input_field('products_manufacturers_model', $gBitProduct->getField( 'products_manufacturers_model' ), zen_set_field_length(TABLE_PRODUCTS, 'products_manufacturers_model')); ?>
	</div>					 
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_MODEL; ?></label>
		<?php echo zen_draw_input_field('products_model', $gBitProduct->getField( 'products_model' ), zen_set_field_length(TABLE_PRODUCTS, 'products_model')); ?>
	</div>
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_SUPPLIER; ?></label>
		<?php echo zen_draw_pull_down_menu('suppliers_id', $suppliers_array, $gBitProduct->getField( 'suppliers_id' )); ?>
	</div>					
	<div class="form-group">
		<label><?php echo TEXT_PRODUCTS_BARCODE; ?></label>
		<?php echo zen_draw_input_field('products_barcode', $gBitProduct->getField( 'products_barcode' )); ?>
	</div>					
</fieldset>
	<input type="submit" class="btn btn-primary" value="<?php echo tra( 'Save' ); ?>" name="store_product" /> <a class="btn btn-default" href="<?php echo zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['products_id']) ? '&products_id=' . $_GET['products_id'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>"><?php echo tra( 'Cancel' ); ?></a>
</form>

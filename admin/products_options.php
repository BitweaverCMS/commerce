<?php

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceProductManager.php' );

// $gBitSmarty->assign( 'loadAjax', 'mochikit' );
// $gBitSmarty->assign( 'mochikitLibs', array( 'DOM.js', 'Iter.js', 'Style.js', 'Signal.js', 'Color.js', 'Position.js', 'Visual.js', 'DragAndDrop.js', 'Sortable.js' ) );

$productManager = new CommerceProductManager();


if( $gCommerceSystem->getConfig('ATTRIBUTES_ENABLED_IMAGES') == 'true' ) {
  $dir = @dir(DIR_FS_CATALOG_IMAGES);
  $dir_info[] = array('id' => '', 'text' => "Main Directory");
  while ($file = $dir->read()) {
    if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
      $dir_info[] = array('id' => $file . '/', 'text' => $file);
    }
  }
  sort($dir_info);
  $default_directory = 'options/';
}

if( $gBitProduct->isValid() ) {
	$gBitSmarty->assign_by_ref( 'optionsList', $productManager->getOptionsList() );
	$editTpl = 'bitpackage:bitcommerce/admin_products_options_map_inc.tpl';
} elseif( empty( $_REQUEST['cancel'] ) && !empty( $_REQUEST['products_options_id'] ) ) {
	if( BitBase::verifyId( $_REQUEST['products_options_id'] ) && $editOption = current( $productManager->getOptionsList( array( 'products_options_id' => $_REQUEST['products_options_id'] ) ) ) ) {
		$gBitSmarty->assign_by_ref( 'editOption', $editOption );
	}
	$gBitSmarty->assign_by_ref( 'optionsTypes', $productManager->getOptionsTypes() );
	$editTpl = 'bitpackage:bitcommerce/admin_products_options_edit_inc.tpl';
} elseif( empty( $_REQUEST['cancel'] ) && !empty( $_REQUEST['products_options_values_id'] ) ) {
	if( BitBase::verifyId( $_REQUEST['products_options_values_id'] ) && $editOptionsValue = current( $productManager->getOptionsList( array( 'products_options_values_id' => $_REQUEST['products_options_values_id'] ) ) ) ) {
		$gBitSmarty->assign_by_ref( 'editValue', $editOptionsValue );
	}
	$gBitSmarty->assign_by_ref( 'optionsList', $productManager->getOptionsList() );
	$editTpl = 'bitpackage:bitcommerce/admin_products_options_values_edit_inc.tpl';
} else {
	$gBitSmarty->assign_by_ref( 'optionsList', $productManager->getOptionsList() );
}

if( !empty( $_REQUEST['delete_attribute'] ) && !empty( $editOptionValue ) ) {
	if( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete_attribute'] = TRUE;
		$formHash['attributes_id'] = $_REQUEST['attributes_id'];
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete this option value (#'.$_REQUEST['attributes_id'].') and remove  assignments to products?', 'error' => 'This cannot be undone!' ) );
	} else {
		$tempProduct->expungeAttribute( $_REQUEST['attributes_id'] );
		bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php' );
	}
} elseif( !empty( $_REQUEST['save_attribute'] ) ) {
	$productManager->storeOptionsValue( $_REQUEST );
	bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php' );
} elseif( !empty( $_REQUEST['save_attribute_map'] ) ) {
	$gBitProduct->expungeAllAttributes();
	if( !empty( $_REQUEST['products_options'] ) ) {
		foreach( $_REQUEST['products_options'] as $optionId ) {
			$gBitProduct->storeAttributeMap( $optionId );
		}
	}
	bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php?products_id='.$gBitProduct->getField( 'products_id' ) );
} elseif( !empty( $_REQUEST['save_option'] ) ) {
	if( $productManager->storeOption( $_REQUEST ) ) {
		bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php' );
	} else {
bit_log_error( 'option store failed' );
bit_log_error( $_REQUEST );
	}
} 
 
if( !empty( $editTpl ) ) {
	$gBitSmarty->assign_by_ref( 'editTpl', $editTpl );
}

$gBitSystem->display( 'bitpackage:bitcommerce/admin_products_options.tpl', 'Product Options' );
?>

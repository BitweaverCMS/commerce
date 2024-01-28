<?php

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_CLASS_PATH.'CommerceProductManager.php' );

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
	$gBitSmarty->assign( 'optionsList', $productManager->getOptionsList() );
	$editTpl = 'bitpackage:bitcommerce/admin_products_options_map_inc.tpl';
} elseif( empty( $_REQUEST['cancel'] ) && !empty( $_REQUEST['products_options_id'] ) ) {
	if( BitBase::verifyId( $_REQUEST['products_options_id'] ) && $editOption = current( $productManager->getOptionsList( array( 'products_options_id' => $_REQUEST['products_options_id'] ) ) ) ) {
		$gBitSmarty->assign_by_ref( 'editOption', $editOption );
	}
	$gBitSmarty->assign( 'optionsTypes', $productManager->getOptionsTypes() );
	$editTpl = 'bitpackage:bitcommerce/admin_products_options_edit_inc.tpl';
} elseif( empty( $_REQUEST['cancel'] ) && !empty( $_REQUEST['products_options_values_id'] ) ) {
	if( BitBase::verifyId( $_REQUEST['products_options_values_id'] ) && $editOptionsValue = current( $productManager->getOptionsList( array( 'products_options_values_id' => $_REQUEST['products_options_values_id'] ) ) ) ) {
		$gBitSmarty->assign_by_ref( 'editValue', $editOptionsValue['values'][$_REQUEST['products_options_values_id']] );
	}
	$gBitSmarty->assign( 'optionsList', $productManager->getOptionsList() );
	$editTpl = 'bitpackage:bitcommerce/admin_products_options_values_edit_inc.tpl';
} else {
	$gBitSmarty->assign( 'optionsList', $productManager->getOptionsList() );
}

if( !empty( $_REQUEST['delete_attribute'] ) && $productManager->verifyIdParameter( $_REQUEST, 'products_options_values_id' ) ) {
	if( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete_attribute'] = TRUE;
		$formHash['products_options_values_id'] = $_REQUEST['products_options_values_id'];
		$gBitSystem->confirmDialog( $formHash, array( 'confirm_item' => 'Are you sure you want to delete this option value (#'.$_REQUEST['products_options_values_id'].') and remove assignments to products?', 'warning' => 'This cannot be undone!' ) );
	} else {
		$productManager->expungeOptionsValue( $_REQUEST['products_options_values_id'] );
		bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php' );
	}
} elseif( !empty( $_REQUEST['save_attribute'] ) ) {
	$productManager->storeOptionsValue( $_REQUEST, $_FILES );
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
	if( $productManager->storeOption( $_REQUEST, $_FILES ) ) {
		bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php' );
	} else {
bit_error_log( 'option store failed' );
bit_error_log( $_REQUEST );
	}
} 

$listHash = array();
$groups = $gBitUser->getAllGroups( $listHash );
$groupList[] = '';
foreach( $groups as $group ) {
	$groupList[$group['group_id']] = $group['group_name'];
}
$gBitSmarty->assign_by_ref( 'groupList', $groupList );

if( !empty( $editTpl ) ) {
	$gBitSmarty->assign_by_ref( 'editTpl', $editTpl );
}

$gBitSystem->display( 'bitpackage:bitcommerce/admin_products_options.tpl', 'Product Options' , array( 'display_mode' => 'admin' ));
?>

<?php

require('includes/application_top.php');

require_once( BITCOMMERCE_PKG_PATH.'classes/CommerceProductManager.php' );

$gBitSmarty->assign( 'loadAjax', 'mochikit' );
$gBitSmarty->assign( 'mochikitLibs', array( 'DOM.js', 'Iter.js', 'Style.js', 'Signal.js', 'Color.js', 'Position.js', 'Visual.js', 'DragAndDrop.js', 'Sortable.js' ) );

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

$tempProduct = new CommerceProduct();
$optionsList = CommerceProduct::getAllOptions();
$gBitSmarty->assign_by_ref( 'optionsList', $optionsList );

if( @BitBase::verifyId( $_REQUEST['attributes_id'] ) ) {
	$editAttribute = CommerceProduct::getAllAttributes( $_REQUEST['attributes_id'] );
	$gBitSmarty->assign_by_ref( 'editAttribute', current( $editAttribute ) );
}
//vd( $editAttribute );
$attributesList = CommerceProduct::getAllAttributes();
$gBitSmarty->assign_by_ref( 'attributesList', $attributesList );

if( !empty( $_REQUEST['delete_attribute'] ) && !empty( $editAttribute ) ) {
	if( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete_attribute'] = TRUE;
		$formHash['attributes_id'] = $_REQUEST['attributes_id'];
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete this attribute (#'.$_REQUEST['attributes_id'].') and all attribute assignments?', 'error' => 'This cannot be undone!' ) );
	} else {
		$tempProduct->expungeAttribute( $_REQUEST['attributes_id'] );
		bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php' );
	}

} elseif( !empty( $_REQUEST['save_attribute'] ) ) {
	list( $_REQUEST['options_id'], $_REQUEST['options_values_id'] ) = split( ':', $_REQUEST['options'] );
	$tempProduct->storeAttributes( $_REQUEST );
	bit_redirect( BITCOMMERCE_PKG_URL.'admin/products_options.php' );
} 

$gBitSystem->display( 'bitpackage:bitcommerce/admin_products_options.tpl', 'Product Options' );

?>

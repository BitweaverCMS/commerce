<?php
// +----------------------------------------------------------------------+
// | bitcommerce,	http://www.bitcommerce.org                            |
// +----------------------------------------------------------------------+
// | Copyright (c) 2013 bitcommerce.org                                   |
// | This source file is subject to version 3.0 of the GPL license        |
// | Portions Copyrigth (c) 2005 http://www.zen-cart.com                  |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+

// These classes need to be included first so they get written to the session properly
require_once('includes/classes/navigation_history.php');
require_once('../kernel/setup_inc.php');
require_once('includes/application_top.php');

global $gBitUser, $gBitCustomer, $gBitSmarty, $gCommercePopupTemplate;

// Maybe customer registered with an inline form
if( !$gBitUser->isRegistered() && !empty( $_REQUEST['inline_registration'] ) ) {
	if( $gBitCustomer->register( $_REQUEST ) ) {
	} else {
		$gBitSmarty->assign( 'userErrors', $gBitUser->mErrors );
	}
}

// determine the page directory
if( empty( $_REQUEST['main_page'] ) ) {
	if( @BitBase::verifyId( $_REQUEST['user_id'] ) ) {
		$_REQUEST['main_page'] = 'user_products';
	} elseif( $infoPage = $gBitProduct->getInfoPage() ) {
		$_REQUEST['main_page'] = $infoPage;
		global $gContent, $gBitSmarty;
		// we are viewing a product, assume it is gContent if nothing else was created so services work
		if( empty( $gContent ) ) {
			$gContent = &$gBitProduct;
			$gBitSmarty->assign_by_ref( 'gContent', $gContent );
		}
	} else {
		$_REQUEST['main_page'] = 'index';
	}
	// compatibility for shite code
	$_GET['main_page'] = $_REQUEST['main_page'];
}

$current_page = $_REQUEST['main_page'];
$current_page_base = $current_page;
$code_page_directory = DIR_FS_PAGES . $current_page_base;
$page_directory = $code_page_directory;
$gBitSmarty->assign_by_ref( 'current_page_base', $current_page_base );


$language_page_directory = DIR_WS_LANGUAGES . $gBitCustomer->getLanguage() . '/';

// We need to buffer output
ob_start();

// load all files in the page directory starting with 'header_php'

$directory_array = $gCommerceSystem->get_template_part($code_page_directory, '/^header_php/');

while(list ($key, $value) = each($directory_array)) {
	require($code_page_directory . '/' . $value);
}

//new smarty based pages are doing away with this call
require_once( DIR_FS_MODULES . 'require_languages.php' );

// 	require($gCommerceSystem->get_template_dir('html_header.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/html_header.php');

// Define Template Variables picked up from includes/main_template_vars.php unless a file exists in the
// includes/pages/{page_name}/directory to overide. Allowing different pages to have different overall
//templates.

require($gCommerceSystem->get_template_dir('main_template_vars.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/main_template_vars.php');

// Read the "on_load" scripts for the individual page, and from the site-wide template settings
// NOTE: on_load_*.js files must contain just the raw code to be inserted in the <body> tag in the on_load="" parameter.
// Looking in "/includes/modules/pages" for files named "on_load_*.js"
$directory_array = $gCommerceSystem->get_template_part(DIR_FS_PAGES . $current_page_base, '/^on_load_/', '.js');
while(list ($key, $value) = each($directory_array)) {
	$onload_file = DIR_FS_PAGES . $current_page_base . '/' . $value;
	$read_contents='';
	$lines = @file($onload_file);
	foreach($lines as $line) {
		$read_contents.=$line;
	}
	$za_onload_array[]=$read_contents;
}
//now read "includes/templates/TEMPLATE/jscript/on_load/on_load_*.js", which would be site-wide settings
$directory_array=array();
$tpl_dir=$gCommerceSystem->get_template_dir('.js', DIR_WS_TEMPLATE, 'jscript/on_load', 'jscript/on_load_');
$directory_array = $gCommerceSystem->get_template_part($tpl_dir ,'/^on_load_/', '.js');
while(list ($key, $value) = each($directory_array)) {
	$onload_file = $tpl_dir . '/' . $value;
	$read_contents='';
	$lines = @file($onload_file);
	foreach($lines as $line) {
		$read_contents.=$line;
	}
	$za_onload_array[]=$read_contents;
}
if( !empty( $zc_first_field ) ) $za_onload_array[] = $zc_first_field; // for backwards compatibility with previous $zc_first_field usage
$zv_onload = '';
if( !empty( $za_onload_array ) ) $zv_onload=implode(';',$za_onload_array);
$zv_onload = str_replace(';;',';',$zv_onload.';'); //ensure we have just one ';' between each, and at the end
if (trim($zv_onload) == ';') $zv_onload='';  // ensure that a blank list is truly blank and thus ignored.

// Define the template that will govern the overall page layout, can be done on a page by page basis
// or using a default template. The default template installed will be a standard 3 column layout. This
// template also loads the page body code based on the variable $body_code.

require($gCommerceSystem->get_template_dir('tpl_main_page.php',DIR_WS_TEMPLATE, $current_page_base,'common'). '/tpl_main_page.php');

require(DIR_FS_INCLUDES . 'application_bottom.php');
$content = ob_get_contents();
$gBitSmarty->assign_by_ref( 'bitcommerceCenter', $content );
ob_end_clean();

if( !empty( $gCommercePopupTemplate ) ) {
	$gBitSmarty->display( $gCommercePopupTemplate );
} else {
	if( BitBase::getParameter( $_REQUEST, 'content-type' ) == 'json' ) {
		if( $gBitProduct->isValid() ) {
			$gBitSystem->outputJson( $gBitProduct->exportHash(), HttpStatusCodes::HTTP_OK );
		}
	} elseif( $current_page_base == 'index' && empty( $_REQUEST['cPath'] ) ) {
		// Display the template
		$gDefaultCenter = 'bitpackage:bitcommerce/default_index.tpl';
		$gBitSmarty->assign_by_ref( 'gDefaultCenter', $gDefaultCenter );
		if( !empty( $_REQUEST['products_id'] ) && !$gBitProduct->isValid() ) {
			$gBitSystem->setHttpStatus( HttpStatusCodes::HTTP_NOT_FOUND );
		}
		// Display the template
		$gBitSystem->display( 'bitpackage:kernel/dynamic.tpl', HEADING_TITLE , array( 'display_mode' => 'display' ));
	} else {
		$gBitSystem->display( 'bitpackage:bitcommerce/view_bitcommerce.tpl', HEADING_TITLE , array( 'display_mode' => 'display' ));
	}
}

?>

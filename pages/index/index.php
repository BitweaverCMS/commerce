<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id$

// release manufactures_id when nothing is there so a blank filter is not setup.
// this will result in the home page, if used
if( !empty( $_REQUEST['manufacturers_id'] ) && ($_REQUEST['manufacturers_id'] <= 0) ) {
	unset($_REQUEST['manufacturers_id']);
	unset($manufacturers_id);
}

if ($category_depth == 'nested') {
	$sql = "select cd.`categories_name`, c.categories_image
			from   " . TABLE_CATEGORIES . " c, " .
					 TABLE_CATEGORIES_DESCRIPTION . " cd
			where      c.`categories_id` = '" . (int)$current_category_id . "'
			and        cd.`categories_id` = '" . (int)$current_category_id . "'
			and        cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
			and        c.`categories_status`= '1'";

	$category = $gBitDb->Execute($sql);

	if (isset($cPath) && strpos($cPath, '_')) {
	// check to see if there are deeper categories within the current category
		$category_links = array_reverse($cPath_array);
		for($i=0, $n=sizeof($category_links); $i<$n; $i++)
		{
			$subcatCount = $gBitDb->getOne( "select count(*) as `total` from   " . TABLE_CATEGORIES . " c INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON ( c.`categories_id` = cd.`categories_id` )
					where      c.`parent_id` =? AND cd.`language_id` = ? AND c.`categories_status`= '1'", array( (int)$category_links[$i], (int)$_SESSION['languages_id']) );

			if( $subcatCount ) {
			$categories_query = "select c.`categories_id`, cd.`categories_name`, c.categories_image, c.`parent_id`
					FROM   " . TABLE_CATEGORIES . " c INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.`categories_id` = cd.`categories_id`)
					WHERE c.`parent_id` = '" . (int)$category_links[$i] . "' AND cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
					AND        c.`categories_status`= '1'
					ORDER BY   `sort_order`, cd.`categories_name`";

			break; // we've found the deepest category the customer is in
			}
		}
	} else {
		$categories_query = "SELECT c.`categories_id`, cd.`categories_name`, c.categories_image, c.`parent_id`
							 FROM   " . TABLE_CATEGORIES . " c INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON( c.`categories_id` = cd.`categories_id` )
							 WHERE      c.`parent_id` = '" . (int)$current_category_id . "'
							 AND        cd.`language_id` = '" . (int)$_SESSION['languages_id'] . "'
							 AND        c.`categories_status`= '1'
							 ORDER BY   `sort_order`, cd.`categories_name`";
	}
	$categories = $gBitDb->Execute($categories_query);
	$number_of_categories = $categories->RecordCount();
	$new_products_category_id = $current_category_id;

/////////////////////////////////////////////////////////////////////////////////////////////////////
?>

<table border="0" width="100%" cellspacing="2" cellpadding="2">
<?php
if( !empty( $show_welcome ) && $show_welcome == 'true') {
?>
  <tr>
    <td class="pageHeading"><h1><?php echo HEADING_TITLE; ?></h1></td>
  </tr>
  <tr>
    <td class="greetUser"><?php echo zen_customer_greeting(); ?></td>
  </tr>
<?php if (TEXT_MAIN) { ?>
  <tr>
    <td class="main"><?php echo TEXT_MAIN; ?></td>
  </tr>
<?php } ?>
<?php if (TEXT_INFORMATION) { ?>
  <tr>
    <td class="plainBox"><?php echo TEXT_INFORMATION; ?></td>
  </tr>
<?php } ?>
<?php if (DEFINE_MAIN_PAGE_STATUS == '1') { ?>
  <tr>
    <td class="plainBox"><?php require($define_main_page); ?><br /></td>
  </tr>
<?php } ?>

<?php } else { ?>
  <tr>
  </tr>
  <tr>
    <td class="pageHeading"><h1><?php echo $breadcrumb->last(); ?></h1></td>
  </tr>
<?php } ?>
</table><br />
<table border="0" width="100%" cellspacing="2" cellpadding="2">
<?php
// categories_description
    if ($current_categories_description != '') {
?>
  <tr>
    <td colspan="4">
      <table border="0" width="100%" cellspacing="2" cellpadding="2" class="categoriesdescription">
        <tr class="categoriesdescription">
          <td class="categoriesdescription"><?php echo $current_categories_description;  ?></td>
        </tr>
      </table>
    </td>
  </tr>
<?php } // categories_description ?>
<tr>
   <td align="center" class="smallText" width="<?php echo $width; ?> " valign="top"><a href="<?php echo zen_href_link(FILENAME_DEFAULT, 'cPath='.$categories->fields['categories_id']); ?>"><?php echo zen_image(DIR_WS_IMAGES . $categories->fields['categories_image'], $categories->fields['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT); ?><br /><?php echo $categories->fields['categories_name']; ?></a></td>
</tr>
</table>

<?php
$show_display_category = $gBitDb->Execute(SQL_SHOW_PRODUCT_INFO_CATEGORY);

while (!$show_display_category->EOF) {
  // //  echo 'I found ' . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS);

?>

<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_FEATURED_PRODUCTS') { ?>
<?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_FEATURED_PRODUCTS_MODULE)); ?><?php } ?>

<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_SPECIALS_PRODUCTS') { ?>
<?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_SPECIALS_INDEX)); ?><?php } ?>
<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_NEW_PRODUCTS') { ?>
<?php require(DIR_FS_MODULES . zen_get_module_directory(FILENAME_NEW_PRODUCTS)); ?><?php } ?>
<?php if ($show_display_category->fields['configuration_key'] == 'SHOW_PRODUCT_INFO_CATEGORY_UPCOMING') { ?>
<?php include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_UPCOMING_PRODUCTS)); ?><?php } ?>
<?php
  $show_display_category->MoveNext();
} 

/////////////////////////////////////////////////////////////////////////////////////////////////////

} elseif ($category_depth == 'products' || !empty( $_GET['user_id'] ) || zen_check_url_get_terms() ) {
	global $gBitProduct;

	$listProducts = $gBitProduct->getList( $_REQUEST );
	$gBitSmarty->assign( 'listInfo', $_REQUEST );
	$gBitSmarty->assign_by_ref( 'listProducts', $listProducts );
	$gBitSmarty->display( 'bitpackage:bitcommerce/list_products_inc.tpl' );


} else {

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$gBitSmarty->assign( 'mainDisplayBlocks', $gBitDb->getAll(SQL_SHOW_PRODUCT_INFO_MAIN) );

}


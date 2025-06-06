<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 
// |																																		
// | http://www.zen-cart.com/index.php																	
// |																																		
// | Portions Copyright (c) 2003 osCommerce																 
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 
// | that is bundled with this package in the file LICENSE, and is			
// | available through the world-wide-web at the following url:					 
// | http://www.zen-cart.com/license/2_0.txt.														 
// | If you did not receive a copy of the zen-cart license and are unable 
// | to obtain it through the world-wide-web, please send a note to			 
// | license@zen-cart.com so we can mail you a copy immediately.				
// +----------------------------------------------------------------------+
//	$Id$
//

require('includes/application_top.php');

require(DIR_WS_MODULES . 'prod_cat_header_code.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (!isset($_SESSION['categories_products_sort_order'])) {
	$_SESSION['categories_products_sort_order'] = CATEGORIES_PRODUCTS_SORT_ORDER;
}

if (!isset($_GET['reset_categories_products_sort_order'])) {
	$reset_categories_products_sort_order = $_SESSION['categories_products_sort_order'];
}

if( !empty( $_REQUEST['cID'] ) ) {
	$category = new CommerceCategory( $_REQUEST['cID'] );
}

$languages = zen_get_languages();
$gBitSmarty->assign( 'languages', $languages ); 
$gBitSmarty->assign( 'currentCategoryId', $current_category_id );

if (zen_not_null($action)) {
	switch ($action) {
		case 'set_categories_products_sort_order':
			$_SESSION['categories_products_sort_order'] = $_GET['reset_categories_products_sort_order'];
			$action='';
			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES,	'cPath=' . $_GET['cPath'] . ((isset($_GET['products_id']) and !empty($_GET['products_id'])) ? '&products_id=' . $_GET['products_id'] : '') . ((isset($_GET['page']) and !empty($_GET['page'])) ? '&page=' . $_GET['page'] : '')));
			break;
		case 'set_editor':
			if ($_GET['reset_editor'] == '0') {
				$_SESSION['html_editor_preference_status'] = 'NONE';
			} else {
				$_SESSION['html_editor_preference_status'] = 'HTMLAREA';
			}
			$action='';
			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES,	'cPath=' . $_GET['cPath'] . ((isset($_GET['products_id']) and !empty($_GET['products_id'])) ? '&products_id=' . $_GET['products_id'] : '') . ((isset($_GET['page']) and !empty($_GET['page'])) ? '&page=' . $_GET['page'] : '')));
			break;

		case 'update_category_status':
			// disable category and products including subcategories
			if (isset($_POST['categories_id'])) {
				$categories_id = zen_db_prepare_input($_POST['categories_id']);

				$categories = zen_get_category_tree($categories_id, '', '0', '', true);

				for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
					$product_ids = $gBitDb->Execute("select `products_id`
																			 from " . TABLE_PRODUCTS_TO_CATEGORIES . "
																			 where `categories_id` = '" . (int)$categories[$i]['id'] . "'");

					while (!$product_ids->EOF) {
						$products[$product_ids->fields['products_id']]['categories'][] = $categories[$i]['id'];
						$product_ids->MoveNext();
					}
				}

// change the status of categories and products
				zen_set_time_limit(600);
				for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
					if ($_POST['categories_status'] == '1') {
						$categories_status = '0';
						$products_status = '0';
					} else {
						$categories_status = '1';
						$products_status = '1';
					}

						$sql = "update " . TABLE_CATEGORIES . " set `categories_status` ='" . $categories_status . "'
										where `categories_id` ='" . $categories[$i]['id'] . "'";
						$gBitDb->Execute($sql);

					// set products_status based on selection
					if ($_POST['set_products_status'] == 'set_products_status_nochange') {
						// do not change current product status
					} else {
						if ($_POST['set_products_status'] == 'set_products_status_on') {
							$products_status = '1';
						} else {
							$products_status = '0';
						}

						$sql = "select `products_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `categories_id` ='" . $categories[$i]['id'] . "'";
						$category_products = $gBitDb->Execute($sql);

						while (!$category_products->EOF) {
							$sql = "update " . TABLE_PRODUCTS . " set `products_status` ='" . $products_status . "' where `products_id` ='" . $category_products->fields['products_id'] . "'";
							$gBitDb->Execute($sql);
							$category_products->MoveNext();
						}
					}
				} // for

			}
			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&cID=' . $_GET['cID']));
			break;

		case 'remove_type':
				$sql = "delete from " .	TABLE_PRODUCT_TYPES_TO_CATEGORY . "
								where category_id = '" . zen_db_prepare_input($_GET['cID']) . "'
								and product_type_id = '" . zen_db_prepare_input($_GET['type_id']) . "'";

				$gBitDb->Execute($sql);

				zen_remove_restrict_sub_categories($_GET['cID'], $_GET['type_id']);

				$action = "edit";
				zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'action=edit_category&cPath=' . $_GET['cPath'] . '&cID=' . zen_db_prepare_input($_GET['cID'])));
		break;
		case 'setflag':
			if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
				if (isset($_GET['products_id'])) {
					zen_set_product_status($_GET['products_id'], $_GET['flag']);
				}

			}

			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&products_id=' . $_GET['products_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
			break;
		case 'insert_category':
		case 'update_category':
			if ( isset($_POST['add_type']) or isset($_POST['add_type_all']) ) {
				// check if it is already restricted
				$sql = "select * from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
												 where `category_id` = '" . zen_db_prepare_input($_POST['categories_id']) . "'
												 and `product_type_id` = '" . zen_db_prepare_input($_POST['restrict_type']) . "'";

				$type_to_cat = $gBitDb->Execute($sql);
				if ($type_to_cat->RecordCount() < 1) {
//@@TODO find all sub-categories and restrict them as well.

					$insert_sql_data = array('category_id' => zen_db_prepare_input($_POST['categories_id']),
																	 'product_type_id' => zen_db_prepare_input($_POST['restrict_type']));

					$gBitDb->associateInsert(TABLE_PRODUCT_TYPES_TO_CATEGORY, $insert_sql_data);
/*
// moved below so evaluated separately from current category
					if (isset($_POST['add_type_all'])) {
						zen_restrict_sub_categories($_POST['categories_id'], $_POST['restrict_type']);
					}
*/
				}
// add product type restrictions to subcategories if not already set
				if (isset($_POST['add_type_all'])) {
					zen_restrict_sub_categories($_POST['categories_id'], $_POST['restrict_type']);
				}
				$action = "edit";
				zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'action=edit_category&cPath=' . $cPath . '&cID=' . zen_db_prepare_input($_POST['categories_id'])));
			}
			if (isset($_POST['categories_id'])) $categories_id = zen_db_prepare_input($_POST['categories_id']);
			$sort_order = zen_db_prepare_input($_POST['sort_order']);

			$sql_data_array = array('sort_order' => (int)$sort_order);

			if ($action == 'insert_category') {
				$insert_sql_data = array('parent_id' => $current_category_id, 'date_added' => $gBitDb->NOW() );

				$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

				$gBitDb->associateInsert(TABLE_CATEGORIES, $sql_data_array);

				$categories_id = zen_db_insert_id( TABLE_CATEGORIES, 'categories_id' );
// check if [arent is restricted
				$sql = "select `parent_id` from " . TABLE_CATEGORIES . "
								where `categories_id` = '" . $categories_id . "'";

				$parent_cat = $gBitDb->Execute($sql);
				if ($parent_cat->fields['parent_id'] != '0') {
					$sql = "select `product_type_id` from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
									 where `category_id` = '" . $parent_cat->fields['parent_id'] . "'";
					$parent_product_type = $gBitDb->Execute($sql);

					if ($parent_product_type->RecordCount() > 0) {
						$sql = "select * from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . "
													 where `category_id` = '" . $parent_cat->fields['parent_id'] . "'
													 and `product_type_id` = '" . $parent_product_type->fields['product_type_id'] . "'";
						$has_type = $gBitDb->Execute($sql);

						if ($has_type->RecordCount() < 1) {
							$insert_sql_data = array('category_id' => $categories_id,
																			 'product_type_id' => $parent_product_type->fields['product_type_id']);

							$gBitDb->associateInsert(TABLE_PRODUCT_TYPES_TO_CATEGORY, $insert_sql_data);

						}
			}
				}
			} elseif ($action == 'update_category') {
				$update_sql_data = array('last_modified' => $gBitDb->NOW());
				$sql_data_array = array_merge($sql_data_array, $update_sql_data);
				$gBitDb->associateUpdate( TABLE_CATEGORIES, $sql_data_array, array( 'categories_id' => $categories_id ) );
			}

			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$categories_name_array = $_POST['categories_name'];
				$categories_description_array = $_POST['categories_description'];
				$language_id = $languages[$i]['id'];

// clean $categories_description when blank or just <p /> left behind
				$sql_data_array = array('categories_name' => substr( $categories_name_array[$language_id], 0, 32 ),
																'categories_description' => ($categories_description_array[$language_id] == '<p />' ? '' : zen_db_prepare_input($categories_description_array[$language_id])));

		if( empty( $sql_data_array['categories_name'] ) ) {
						$messageStack->add_session(tra( 'You must enter a category name' ), 'error');
		} else {
			if ($action == 'insert_category') {
				$insert_sql_data = array('categories_id' => $categories_id,
										'language_id' => $languages[$i]['id']);

				$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

				$gBitDb->associateInsert(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
			} elseif ($action == 'update_category') {
				$setSql = ( '`'.implode( array_keys( $sql_data_array ), '`=?, `' ).'`=?' );
				$bindVars = array_values( $sql_data_array );
				array_push( $bindVars, $categories_id );
				array_push( $bindVars, $languages[$i]['id'] );

				$query = "UPDATE " . TABLE_CATEGORIES_DESCRIPTION . " SET $setSql WHERE `categories_id`=? AND `language_id`=? ";
				$gBitDb->query( $query, $bindVars );
			}
		}
			}

			if ($categories_image = new upload('categories_image')) {
				$categories_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
				if ($categories_image->parse() && $categories_image->save()) {
					$categories_image_name = $_POST['img_dir'] . $categories_image->filename;
				}
				if (($categories_image->filename != 'none' && $categories_image->filename != '') && (!is_numeric(strpos($categories_image->filename,'none')))) {
					$gBitDb->Execute("update " . TABLE_CATEGORIES . "
												set `categories_image` = '" . $categories_image_name . "'
												where `categories_id` = '" . (int)$categories_id . "'");
				} else {
					// remove when set to none
//						if ($categories_image->filename == 'none' or (!is_numeric(strpos($categories_image->filename,'none'))) ) {
					if (($categories_image->filename != '') && ($categories_image->filename == 'none' or (!is_numeric(strpos($categories_image->filename,'none')))) ) {
						$gBitDb->Execute("update " . TABLE_CATEGORIES . "
													set `categories_image` = ''
													where `categories_id` = '" . (int)$categories_id . "'");
					}
				}
			}


			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id));
			break;
		case 'delete_category_confirm_old':
			// demo active test
			if (zen_admin_demo()) {
				$_GET['action']= '';
				$messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
				zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath));
			}
			if (isset($_POST['categories_id'])) {
				$categories_id = zen_db_prepare_input($_POST['categories_id']);

				$categories = zen_get_category_tree($categories_id, '', '0', '', true);
				$products = array();
				$products_delete = array();

				for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
					$product_ids = $gBitDb->Execute("select `products_id`
																			 from " . TABLE_PRODUCTS_TO_CATEGORIES . "
																			 where `categories_id` = '" . (int)$categories[$i]['id'] . "'");

					while (!$product_ids->EOF) {
						$products[$product_ids->fields['products_id']]['categories'][] = $categories[$i]['id'];
						$product_ids->MoveNext();
					}
				}

				reset($products);
				while (list($key, $value) = each($products)) {
					$category_ids = '';

					for ($i=0, $n=sizeof($value['categories']); $i<$n; $i++) {
						$category_ids .= "'" . (int)$value['categories'][$i] . "', ";
					}
					$category_ids = substr($category_ids, 0, -2);

					$check = $gBitDb->Execute("select count(*) as `total`
																			 from " . TABLE_PRODUCTS_TO_CATEGORIES . "
																			 where `products_id` = '" . (int)$key . "'
																			 and `categories_id` not in (" . $category_ids . ")");
					if ($check->fields['total'] < '1') {
						$products_delete[$key] = $key;
					}
				}

// removing categories can be a lengthy process
				zen_set_time_limit(600);
				for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
					zen_remove_category($categories[$i]['id']);
				}

				reset($products_delete);
				while (list($key) = each($products_delete)) {
					zen_remove_product($key);
				}
			}


			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath));
			break;

//////////////////////////////////
// delete new

		case 'delete_category_confirm':
			// demo active test
			if (zen_admin_demo()) {
				$_GET['action']= '';
				$messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
				zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath));
			}

// future cat specific deletion
			$delete_linked = 'true';
			if ($_POST['delete_linked'] == 'delete_linked_no') {
				$delete_linked = 'false';
			} else {
				$delete_linked = 'true';
			}

			// delete category and products
			if (isset($_POST['categories_id'])) {
				$categories_id = zen_db_prepare_input($_POST['categories_id']);

				$categories = zen_get_category_tree($categories_id, '', '0', '', true);

				for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
					$product_ids = $gBitDb->Execute("select `products_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `categories_id` = '" . (int)$categories[$i]['id'] . "'");

					while (!$product_ids->EOF) {
						$products[$product_ids->fields['products_id']]['categories'][] = $categories[$i]['id'];
						$product_ids->MoveNext();
					}
				}

// change the status of categories and products
				zen_set_time_limit(600);
				for ($i=0, $n=sizeof($categories); $i<$n; $i++) {

					// set products_status based on selection

						$sql = "select `products_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `categories_id` ='" . $categories[$i]['id'] . "'";
						$category_products = $gBitDb->Execute($sql);

						while (!$category_products->EOF) {
							// future cat specific use for
							zen_remove_product($category_products->fields['products_id'], $delete_linked);
							$category_products->MoveNext();
						}

					zen_remove_category($categories[$i]['id']);

				} // for
			}
			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath));
			break;

// eof delete new
/////////////////////////////////
// @@TODO where is delete_product_confirm

		case 'move_category_confirm':
			if (isset($_POST['categories_id']) && ($_POST['categories_id'] != $_POST['move_to_category_id'])) {
				$categories_id = zen_db_prepare_input($_POST['categories_id']);
				$new_parent_id = zen_db_prepare_input($_POST['move_to_category_id']);

				$path = explode('_', zen_get_generated_category_path_ids($new_parent_id));

				if (in_array($categories_id, $path)) {
					$messageStack->add_session(ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');

					zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id));
				} else {
					$gBitDb->Execute("update " . TABLE_CATEGORIES . "
												set `parent_id` = '" . (int)$new_parent_id . "', `last_modified` = ".$gBitDb->qtNOW()."
												where `categories_id` = '" . (int)$categories_id . "'");

// fix here - if this is a category with subcats it needs to know to loop through
					// reset all lowest_purchase_price for moved category products
					$reset_price_sorter = $gBitDb->Execute("select `products_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `categories_id` ='" . (int)$categories_id . "'");
					while (!$reset_price_sorter->EOF) {
						zen_update_lowest_purchase_price($reset_price_sorter->fields['products_id']);
						$reset_price_sorter->MoveNext();
					}

					zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&cID=' . $categories_id));
				}
			}

			break;
// @@TODO where is move_product_confirm
// @@TODO where is insert_product
// @@TODO where is update_product

// attribute features
		case 'delete_attributes':
			zen_delete_products_attributes($_GET['products_id']);
			$messageStack->add_session(SUCCESS_ATTRIBUTES_DELETED . ' ID#' . $_GET['products_id'], 'success');
			$action='';

			// reset lowest_purchase_price for searches etc.
			zen_update_lowest_purchase_price($_GET['products_id']);

			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $_GET['products_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
			break;

		case 'update_attributes_sort_order':
			zen_update_attributes_products_option_values_sort_order($_GET['products_id']);
			$messageStack->add_session(SUCCESS_ATTRIBUTES_UPDATE . ' ID#' . $_GET['products_id'], 'success');
			$action='';
			zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $_GET['products_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
			break;

// attributes copy to product
	case 'update_attributes_copy_to_product':
		$copy_attributes_delete_first = ($_POST['copy_attributes'] == 'copy_attributes_delete' ? '1' : '0');
		$copy_attributes_duplicates_skipped = ($_POST['copy_attributes'] == 'copy_attributes_ignore' ? '1' : '0');
		$copy_attributes_duplicates_overwrite = ($_POST['copy_attributes'] == 'copy_attributes_update' ? '1' : '0');
		zen_copy_products_attributes($_POST['products_id'], $_POST['products_update_id']);
//			die('I would copy Product ID#' . $_POST['products_id'] . ' to a Product ID#' . $_POST['products_update_id'] . ' - Existing attributes ' . $_POST['copy_attributes']);
		$_GET['action']= '';
		zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $_GET['products_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
		break;

// attributes copy to category
	case 'update_attributes_copy_to_category':
		$copy_attributes_delete_first = ($_POST['copy_attributes'] == 'copy_attributes_delete' ? '1' : '0');
		$copy_attributes_duplicates_skipped = ($_POST['copy_attributes'] == 'copy_attributes_ignore' ? '1' : '0');
		$copy_attributes_duplicates_overwrite = ($_POST['copy_attributes'] == 'copy_attributes_update' ? '1' : '0');
		$copy_to_category = $gBitDb->Execute("select `products_id` from " . TABLE_PRODUCTS_TO_CATEGORIES . " where `categories_id` ='" . $_POST['categories_update_id'] . "'");
		while (!$copy_to_category->EOF) {
			zen_copy_products_attributes($_POST['products_id'], $copy_to_category->fields['products_id']);
			$copy_to_category->MoveNext();
		}
//			die('CATEGORIES - I would copy Product ID#' . $_POST['products_id'] . ' to a Category ID#' . $_POST['categories_update_id']	. ' - Existing attributes ' . $_POST['copy_attributes'] . ' Total Products ' . $copy_to_category->RecordCount());

		$_GET['action']= '';
		zen_redirect(zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $_GET['products_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
		break;
	case 'new_product':
		if (isset($_REQUEST['product_type'])) {
		// see if this category is restricted
			$pieces = explode('_',$_GET['cPath']);
			$cat_id = $pieces[sizeof($pieces)-1];
//	echo $cat_id;
			$sql = "select `product_type_id` from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " where `category_id` = '" . (int)$cat_id . "'";
			$product_type_list = $gBitDb->Execute($sql);
			$sql = "select `product_type_id` from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " where `category_id` = '" . (int)$cat_id . "' and `product_type_id` = '" . $_GET['product_type'] . "'";
			$product_type_good = $gBitDb->Execute($sql);
			if ($product_type_list->RecordCount() < 1 || $product_type_good->RecordCount() > 0) {
				$url = zen_get_all_get_params();
				$sql = "select `type_handler` from " . TABLE_PRODUCT_TYPES . " where `type_id` = '" . $_GET['product_type'] . "'";
				$handler = $gBitDb->Execute($sql);
				zen_redirect(zen_href_link_admin($handler->fields['type_handler'] . '.php', zen_get_all_get_params()));
			} else {
				$messageStack->add(ERROR_CANNOT_ADD_PRODUCT_TYPE, 'error');
			}
		}
		break;
	}
}

// check if the catalog image directory exists
if (is_dir(DIR_FS_CATALOG_IMAGES)) {
	if (!is_writeable(DIR_FS_CATALOG_IMAGES)) $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
} else {
	$messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
}

	$mid = 'bitpackage:bitcommerce/admin_categories.tpl';

$gBitSmarty->assignByRef( 'feedback', $feedback );
$gBitSmarty->display( $mid );


?>
<!-- body //-->
<div class="clear">
<?php
	require(DIR_WS_MODULES . 'category_product_listing.php');
	$heading = array();
	$contents = array();
// Make an array of product types
	$sql = "select `type_id`, `type_name` from " . TABLE_PRODUCT_TYPES;
	$product_types = $gBitDb->Execute($sql);
	while (!$product_types->EOF) {
		$type_array[] = array('id' => $product_types->fields['type_id'], 'text' => $product_types->fields['type_name']);
		$product_types->MoveNext();
	}
	switch ($action) {
		case 'setflag_categories':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_STATUS_CATEGORY . '</b>');
			$contents = array('form' => zen_draw_form_admin('categories', FILENAME_CATEGORIES, 'action=update_category_status&cPath=' . $_GET['cPath'] . '&cID=' . $_GET['cID'], 'post', 'enctype="multipart/form-data"') . zen_draw_hidden_field('categories_id', $category->getField('categories_id')) . zen_draw_hidden_field('categories_status', $category->getField('categories_status')));
			$contents[] = array('text' => zen_get_category_name($category->getField('categories_id'), $_SESSION['languages_id']));
			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES_STATUS_WARNING . '<br /><br />');
			$contents[] = array('text' => TEXT_CATEGORIES_STATUS_INTRO . ' ' . ($category->getField('categories_status') == '1' ? TEXT_CATEGORIES_STATUS_OFF : TEXT_CATEGORIES_STATUS_ON));
			if ($category->getField('categories_status') == '1') {
				$contents[] = array('text' => '<br />' . TEXT_PRODUCTS_STATUS_INFO . ' ' . TEXT_PRODUCTS_STATUS_OFF . zen_draw_hidden_field('set_products_status_off', true));
			} else {
				$contents[] = array('text' => '<br />' . TEXT_PRODUCTS_STATUS_INFO . '<br />' .
				zen_draw_radio_field('set_products_status', 'set_products_status_on', true) . ' ' . TEXT_PRODUCTS_STATUS_ON . '<br />' .
				zen_draw_radio_field('set_products_status', 'set_products_status_off') . ' ' . TEXT_PRODUCTS_STATUS_OFF . '<br />' .
				zen_draw_radio_field('set_products_status', 'set_products_status_nochange') . ' ' . TEXT_PRODUCTS_STATUS_NOCHANGE);
			}


//				$contents[] = array('text' => '<br />' . TEXT_PRODUCTS_STATUS_INFO . '<br />' . zen_draw_radio_field('set_products_status', 'set_products_status_off', true) . ' ' . TEXT_PRODUCTS_STATUS_OFF . '<br />' . zen_draw_radio_field('set_products_status', 'set_products_status_on') . ' ' . TEXT_PRODUCTS_STATUS_ON);

			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;

		case 'new_category':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CATEGORY . '</b>');

			$contents = array('form' => zen_draw_form_admin('newcategory', FILENAME_CATEGORIES, 'action=insert_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"'));
			$contents[] = array('text' => TEXT_NEW_CATEGORY_INTRO);

			$category_inputs_string = '';
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$category_inputs_string .= '<br />' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', '', zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name'));
			}

			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES_NAME . $category_inputs_string);
			$category_inputs_string = '';

			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$category_inputs_string .= '<br />' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;';
				if( !empty( $_REQUEST['cID'] ) ) {
					$category_inputs_string .= zen_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '20', zen_get_category_description($_REQUEST['cID'], $languages[$i]['id']));
				}
			}
			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES_DESCRIPTION . $category_inputs_string);
			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES_IMAGE . '<br />' . zen_draw_file_field('categories_image'));
			if( $dir = bit_get_images_dir( DIR_FS_CATALOG_IMAGES ) ) {
		$dir_info[] = array('id' => '', 'text' => "Main Directory");
		while ($file = $dir->read()) {
			if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
			$dir_info[] = array('id' => $file . '/', 'text' => $file);
			}
		}

		if( !empty( $cInfo ) ) {
			$default_directory = substr( $category->getField('categories_image'), 0,strpos( $category->getField('categories_image'), '/')+1);
			$contents[] = array('text' => TEXT_CATEGORIES_IMAGE_DIR . ' ' . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));
		}

		$contents[] = array('text' => '<br />' . TEXT_SORT_ORDER . '<br />' . zen_draw_input_field('sort_order', '', 'size="6"'));
		$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
	}
			break;
		case 'edit_category':
// echo 'I SEE ' . $_SESSION['html_editor_preference_status'];
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CATEGORY . '</b>');

			$contents = array('form' => zen_draw_form_admin('categories', FILENAME_CATEGORIES, 'action=update_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"') . zen_draw_hidden_field('categories_id', $_REQUEST['cID']));
			$contents[] = array('text' => TEXT_EDIT_INTRO);


			$category_inputs_string = '';
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$category_inputs_string .= '<br />' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', zen_get_category_name($_REQUEST['cID'], $languages[$i]['id']), zen_set_field_length(TABLE_CATEGORIES_DESCRIPTION, 'categories_name'));
			}
			$contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_NAME . $category_inputs_string);
			$category_inputs_string = '';
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$category_inputs_string .= '<br />' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' ;
				if ($_SESSION['html_editor_preference_status']=='FCKEDITOR') {
					$category_inputs_string .= '<br />';
					$category_inputs_string .= '<IFRAME src= "' . DIR_WS_CATALOG . 'FCKeditor/fckeditor.html?FieldName=categories_description[' . $languages[$i]['id']	. ']&Upload=false&Browse=false&Toolbar=Short" width="97%" height="200" frameborder="no" scrolling="yes"></IFRAME>';
					$category_inputs_string .= '<INPUT type="hidden" name="categories_description[' . $languages[$i]['id']	. ']" ' . 'value=' . "'" . zen_get_category_description($_REQUEST['cID'], $languages[$i]['id']) . "'>";
				} else {
					$category_inputs_string .= zen_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '20', zen_get_category_description($_REQUEST['cID'], $languages[$i]['id']));
				}
			}
			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES_DESCRIPTION . $category_inputs_string);
			$contents[] = array('text' => '<br />' . TEXT_EDIT_CATEGORIES_IMAGE . '<br />' . zen_draw_file_field('categories_image'));

			$dir = @dir(DIR_FS_CATALOG_IMAGES);
			$dir_info[] = array('id' => '', 'text' => "Main Directory");
			while ($file = $dir->read()) {
				if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
					$dir_info[] = array('id' => $file . '/', 'text' => $file);
				}
			}

			$default_directory = substr( $category->getField('categories_image'), 0,strpos( $category->getField('categories_image'), '/')+1);
			$contents[] = array('text' => TEXT_CATEGORIES_IMAGE_DIR . ' ' . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory));
			$contents[] = array('text' => '<br>' . zen_info_image($category->getField('categories_image'), $category->getField('categories_name')));
			$contents[] = array('text' => '<br>' . $category->getField('categories_image'));

			$contents[] = array('text' => '<br />' . TEXT_EDIT_SORT_ORDER . '<br />' . zen_draw_input_field('sort_order', $category->getField('sort_order'), 'size="6"'));
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $category->getField('categories_id')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			$contents[] = array('text' => TEXT_RESTRICT_PRODUCT_TYPE . ' ' . zen_draw_pull_down_menu('restrict_type', $type_array) . '&nbsp<input type="submit" name="add_type_all" value="' . BUTTON_ADD_PRODUCT_TYPES_SUBCATEGORIES_ON . '">' . '&nbsp<input type="submit" name="add_type" value="' . BUTTON_ADD_PRODUCT_TYPES_SUBCATEGORIES_OFF . '">');
			$sql = "select * from " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " where `category_id` = '" . $category->getField('categories_id') . "'";

			$restrict_types = $gBitDb->Execute($sql);
			if ($restrict_types->RecordCount() > 0 ) {
				$contents[] = array('text' => '<br />' . TEXT_CATEGORY_HAS_RESTRICTIONS . '<br />');
				while (!$restrict_types->EOF) {
					$sql = "select `type_name` from " . TABLE_PRODUCT_TYPES . " where `type_id` = '" . $restrict_types->fields['product_type_id'] . "'";
					$type = $gBitDb->Execute($sql);
					$contents[] = array('text' => '<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'action=remove_type&cPath=' . $cPath . '&cID='.$category->getField('categories_id').'&type_id='.$restrict_types->fields['product_type_id']) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>&nbsp;' . $type->fields['type_name'] . '<br />');
					$restrict_types->MoveNext();
				}
			}
			break;
		case 'delete_category':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</b>');

			$contents = array('form' => zen_draw_form_admin('categories', FILENAME_CATEGORIES, 'action=delete_category_confirm&cPath=' . $cPath) . zen_draw_hidden_field('categories_id', $category->getField('categories_id')));
			$contents[] = array('text' => TEXT_DELETE_CATEGORY_INTRO);
			$contents[] = array('text' => '<br /><b>' . $category->getField('categories_name') . '</b>');
			if ($category->getField('childs_count') > 0) $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_CHILDS, $category->getField('childs_count')));
			if ($category->getField('products_count') > 0) $contents[] = array('text' => '<br />' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $category->getField('products_count')));
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $category->getField('categories_id')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'move_category':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_MOVE_CATEGORY . '</b>');

			$contents = array('form' => zen_draw_form_admin('categories', FILENAME_CATEGORIES, 'action=move_category_confirm&cPath=' . $cPath) . zen_draw_hidden_field('categories_id', $category->getField('categories_id')));
			$contents[] = array('text' => sprintf(TEXT_MOVE_CATEGORIES_INTRO, $category->getField('categories_name')));
			$contents[] = array('text' => '<br />' . sprintf(TEXT_MOVE, $category->getField('categories_name')) . '<br />' . zen_draw_pull_down_menu('move_to_category_id', zen_get_category_tree(), $current_category_id));
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $category->getField('categories_id')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'delete_product':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</b>');

			$contents = array('form' => zen_draw_form_admin('products', FILENAME_CATEGORIES, 'action=delete_product_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id));
			$contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
			$contents[] = array('text' => '<br /><b>' . $pInfo->products_name . ' ID#' . $pInfo->products_id . '</b>');

			$product_categories_string = '';
			$product_categories = zen_generate_category_path($pInfo->products_id, 'product');
			for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
				$category_path = '';
				for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
					$category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
				}
				$category_path = substr($category_path, 0, -16);
				$product_categories_string .= zen_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '<br />';
			}
			$product_categories_string = substr($product_categories_string, 0, -4);

			$contents[] = array('text' => '<br />' . $product_categories_string);
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'move_product':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_MOVE_PRODUCT . '</b>');

			$contents = array('form' => zen_draw_form_admin('products', FILENAME_CATEGORIES, 'action=move_product_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id));
			$contents[] = array('text' => sprintf(TEXT_MOVE_PRODUCTS_INTRO, $pInfo->products_name));
			$contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_CATEGORIES . '<br /><b>' . zen_output_generated_category_path($pInfo->products_id, 'product') . '</b>');
			$contents[] = array('text' => '<br />' . sprintf(TEXT_MOVE, $pInfo->products_name) . '<br />' . zen_draw_pull_down_menu('move_to_category_id', zen_get_category_tree(), $current_category_id));
			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'copy_to':
			$copy_attributes_delete_first = '0';
			$copy_attributes_duplicates_skipped = '0';
			$copy_attributes_duplicates_overwrite = '0';
			$copy_attributes_include_downloads = '1';
			$copy_attributes_include_filename = '1';

			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_TO . '</b>');
// WebMakers.com Added: Split Page
			if (empty($pInfo->products_id)) {
				$pInfo->products_id= $products_id;
			}

			$contents = array('form' => zen_draw_form_admin('copy_to', FILENAME_CATEGORIES, 'action=copy_to_confirm&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id));
			$contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
			$contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_PRODUCT . '<br /><b>' . $pInfo->products_name	. ' ID#' . $pInfo->products_id . '</b>');
			$contents[] = array('text' => '<br />' . TEXT_INFO_CURRENT_CATEGORIES . '<br /><b>' . zen_output_generated_category_path($pInfo->products_id, 'product') . '</b>');
			$contents[] = array('text' => '<br />' . TEXT_CATEGORIES . '<br />' . zen_draw_pull_down_menu('categories_id', zen_get_category_tree(), $current_category_id));
			$contents[] = array('text' => '<br />' . TEXT_HOW_TO_COPY . '<br />' . zen_draw_radio_field('copy_as', 'link', true) . ' ' . TEXT_COPY_AS_LINK . '<br />' . zen_draw_radio_field('copy_as', 'duplicate') . ' ' . TEXT_COPY_AS_DUPLICATE);

			// only ask about attributes if they exist
			if (zen_has_product_attributes($pInfo->products_id, 'false')) {
				$contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
				$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES_ONLY);
				$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_yes', true) . ' ' . TEXT_COPY_ATTRIBUTES_YES . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_no') . ' ' . TEXT_COPY_ATTRIBUTES_NO);
// future					$contents[] = array('align' => 'center', 'text' => '<br />' . ATTRIBUTES_NAMES_HELPER . '<br />' . zen_draw_separator('pixel_trans.gif', '1', '10'));
				$contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
			}

			$contents[] = array('align' => 'center', 'text' => '<br />' . zen_image_submit('button_copy.gif', IMAGE_COPY) . ' <a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');

			break;
// attribute features
	case 'attribute_features':
			$copy_attributes_delete_first = '0';
			$copy_attributes_duplicates_skipped = '0';
			$copy_attributes_duplicates_overwrite = '0';
			$copy_attributes_include_downloads = '1';
			$copy_attributes_include_filename = '1';
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_ATTRIBUTE_FEATURES . $pInfo->products_id . '</b>');

			$contents[] = array('align' => 'center', 'text' => '<br />' . '<strong>' . TEXT_PRODUCTS_ATTRIBUTES_INFO . '</strong>' . '<br />');

			$contents[] = array('align' => 'center', 'text' => '<br />' . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><br />' .
																												 (zen_has_product_attributes($pInfo->products_id, 'false') ? '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, '&action=attributes_preview' . '&products_id=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a>' . '&nbsp;&nbsp;' : '') .
																												 '<a href="' . zen_href_link_admin(FILENAME_ATTRIBUTES_CONTROLLER, 'products_id=' . $pInfo->products_id . '&current_category_id=' . $current_category_id) . '">' . zen_image_button('button_edit_attribs.gif', IMAGE_EDIT_ATTRIBUTES) . '</a>' .
																												 '<br /><br />');
// only if attributes
if (zen_has_product_attributes($pInfo->products_id, 'false')) {
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_DELETE . '<strong>' . zen_get_products_name($pInfo->products_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . '&action=delete_attributes' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_UPDATES . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . '&action=update_attributes_sort_order' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_update.gif', IMAGE_UPDATE) . '</a>');
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_PRODUCT . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . '&action=attribute_features_copy_to_product' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');
			$contents[] = array('align' => 'left', 'text' => '<br />' . TEXT_INFO_ATTRIBUTES_FEATURES_COPY_TO_CATEGORY . '<strong>' . zen_get_products_name($pInfo->products_id, $languages_id) . ' ID# ' . $pInfo->products_id . '</strong><br /><a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . '&action=attribute_features_copy_to_category' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . '&products_id=' . $pInfo->products_id) . '">' . zen_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');
}
			$contents[] = array('align' => 'center', 'text' => '<br /><a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;

// attribute copier to product
	case 'attribute_features_copy_to_product':
		$_GET['products_update_id'] = '';
		// excluded current product from the pull down menu of products
		$products_exclude_array = array();
		$products_exclude_array[] = $pInfo->products_id;

		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_ATTRIBUTE_FEATURES . $pInfo->products_id . '</b>');
		$contents = array('form' => zen_draw_form_admin('products', FILENAME_CATEGORIES, 'action=update_attributes_copy_to_product&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id) . zen_draw_hidden_field('products_update_id', $_GET['products_update_id']) . zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']));
		$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES_CONDITIONS . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . ' ' . TEXT_COPY_ATTRIBUTES_DELETE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . ' ' . TEXT_COPY_ATTRIBUTES_UPDATE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . ' ' . TEXT_COPY_ATTRIBUTES_IGNORE);
		$contents[] = array('align' => 'center', 'text' => '<br />' . zen_draw_products_pull_down('products_update_id', '', $products_exclude_array, true) . '<br /><br />' . zen_image_submit('button_copy_to.gif', IMAGE_COPY_TO). '&nbsp;&nbsp;<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;

// attribute copier to product
	case 'attribute_features_copy_to_category':
		$_GET['categories_update_id'] = '';

		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_ATTRIBUTE_FEATURES . $pInfo->products_id . '</b>');
		$contents = array('form' => zen_draw_form_admin('products', FILENAME_CATEGORIES, 'action=update_attributes_copy_to_category&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . zen_draw_hidden_field('products_id', $pInfo->products_id) . zen_draw_hidden_field('categories_update_id', $_GET['categories_update_id']) . zen_draw_hidden_field('copy_attributes', $_GET['copy_attributes']));
		$contents[] = array('text' => '<br />' . TEXT_COPY_ATTRIBUTES_CONDITIONS . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_delete', true) . ' ' . TEXT_COPY_ATTRIBUTES_DELETE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_update') . ' ' . TEXT_COPY_ATTRIBUTES_UPDATE . '<br />' . zen_draw_radio_field('copy_attributes', 'copy_attributes_ignore') . ' ' . TEXT_COPY_ATTRIBUTES_IGNORE);
		$contents[] = array('align' => 'center', 'text' => '<br />' . zen_draw_products_pull_down_categories('categories_update_id', '', '', true) . '<br /><br />' . zen_image_submit('button_copy_to.gif', IMAGE_COPY_TO) . '&nbsp;&nbsp;<a href="' . zen_href_link_admin(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&products_id=' . $pInfo->products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;

	} // switch

	if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
		echo '						<td valign="top">' . "\n";

		$box = new box;
		echo $box->infoBox($heading, $contents);

		echo '						</td>' . "\n";
	}
?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

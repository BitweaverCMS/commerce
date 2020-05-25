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
//

class category_tree {

	private $tree = array();

	function zen_category_tree($product_type = "all") {
		global $gBitDb, $cPath, $cPath_array;
		if ($product_type != 'all') {
			$sql = "select type_master_type from " . TABLE_PRODUCT_TYPES . "
							where type_master_type = '" . $product_type . "'";
			$master_type_result = $gBitDb->Execute($sql);
			$master_type = $master_type_result->fields['type_master_type'];
		}
		$this->tree = array();
		$bindVars = array( $_SESSION['languages_id'] );
		if ($product_type == 'all') {
			$categories_query = "SELECT c.`categories_id`, cd.`categories_name`, c.`parent_id`
								 FROM " . TABLE_CATEGORIES . " c
									INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.`categories_id` = cd.`categories_id`)
								 WHERE c.`parent_id` = '0' AND c.`categories_status`= '1' AND cd.`language_id`=?
								 ORDER BY `sort_order`, cd.`categories_name`";
		} else {
			$categories_query = "SELECT ptc.`category_id` as `categories_id`, cd.`categories_name`, c.`parent_id`
								 FROM " . TABLE_CATEGORIES . " c
									  INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (ptc.`category_id` = cd.`categories_id`)
									  INNER JOIN " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc ON (c.`categories_id` = ptc.`category_id`)
								 WHERE c.`parent_id` = '0' AND c.`categories_status`= '1' AND cd.`language_id`=?  AND ptc.`product_type_id`=?
								 ORDER BY `sort_order`, cd.`categories_name`";
			$bindVars[] = $master_type;
		}
		$categories = $gBitDb->query($categories_query, $bindVars, true);

		while (!$categories->EOF) {
			$this->tree[$categories->fields['categories_id']] = array('name' => $categories->fields['categories_name'],
																		'parent' => $categories->fields['parent_id'],
																		'level' => 0,
																		'path' => $categories->fields['categories_id'],
																		'next_id' => false);

			if (isset($parent_id)) {
				$this->tree[$parent_id]['next_id'] = $categories->fields['categories_id'];
			}

			$parent_id = $categories->fields['categories_id'];

			if (!isset($first_element)) {
				$first_element = $categories->fields['categories_id'];
			}
			$categories->MoveNext();
		}
		if( !empty( $cPath ) ) {
			$new_path = '';
			reset($cPath_array);
			while (list($key, $value) = each($cPath_array)) {
				unset($parent_id);
				unset($first_id);
				$bindVars = array( (int)$value, (int)$_SESSION['languages_id'] );
				if ($product_type == 'all') {
					$categories_query = "SELECT c.`categories_id`, cd.`categories_name`, c.`parent_id`
										FROM " . TABLE_CATEGORIES . " c 
											INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.`categories_id` = cd.`categories_id`) 
										WHERE c.`parent_id` = ? AND cd.`language_id`= ? AND c.`categories_status`= '1'
										ORDER BY `sort_order`, cd.`categories_name`";
				} else {
					$categories_query = "SELECT ptc.`category_id` as categories_id, cd.`categories_name`, c.`parent_id`
										FROM " . TABLE_CATEGORIES . " c
											INNER JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (ptc.`category_id` = cd.`categories_id`)
											INNER JOIN " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc ON (c.`categories_id` = ptc.`category_id`)
										WHERE c.`parent_id` = ? AND cd.`language_id` = ? AND ptc.`product_type_id` = ? AND c.`categories_status`= '1'
										ORDER BY `sort_order`, cd.`categories_name`";
					$bindVars[] = $master_type;
				}

				$rows = $gBitDb->query($categories_query, $bindVars);

				if ($rows->RecordCount()>0) {
					$new_path .= $value;
					while (!$rows->EOF) {
						$this->tree[$rows->fields['categories_id']] = array('name' => $rows->fields['categories_name'],
																			'parent' => $rows->fields['parent_id'],
																			'level' => $key+1,
																			'path' => $new_path . '_' . $rows->fields['categories_id'],
																			'next_id' => false);

						if (isset($parent_id)) {
							$this->tree[$parent_id]['next_id'] = $rows->fields['categories_id'];
						}

						$parent_id = $rows->fields['categories_id'];
						if (!isset($first_id)) {
							$first_id = $rows->fields['categories_id'];
						}

						$last_id = $rows->fields['categories_id'];
						$rows->MoveNext();
					}
					$this->tree[$last_id]['next_id'] = $this->tree[$value]['next_id'];
					$this->tree[$value]['next_id'] = $first_id;
					$new_path .= '_';
				} else {
					break;
				}
			}
		}

		return $this->zen_show_category($first_element, 0);
	}

	function zen_show_category($counter,$ii) {
		global $cPath_array;

		$this->categories_string = "";

		for ($i=0; $i<$this->tree[$counter]['level']; $i++) {
			if ($this->tree[$counter]['parent'] != 0) {
				$this->categories_string .= CATEGORIES_SUBCATEGORIES_INDENT;
			}
		}


		if( empty( $this->tree[$counter]['parent'] ) ) {
			$cPath_new = 'cPath=' . $counter;
			$this->box_categories_array[$ii]['top'] = 'true';
		} else {
			$this->box_categories_array[$ii]['top'] = 'false';
			$cPath_new = 'cPath=' . $this->tree[$counter]['path'];
			$this->categories_string .= CATEGORIES_SEPARATOR_SUBS;
		}
		$this->box_categories_array[$ii]['path'] = $cPath_new;

		if (isset($cPath_array) && in_array($counter, $cPath_array)) {
			$this->box_categories_array[$ii]['current'] = true;
		}

		// display category name
		$this->box_categories_array[$ii]['name'] = $this->categories_string . $this->tree[$counter]['name'];


		if (zen_has_category_subcategories($counter)) {
			$this->box_categories_array[$ii]['has_sub_cat'] = true;
		}

		if (SHOW_COUNTS == 'true') {
			$products_in_category = zen_count_products_in_category($counter);
			if ($products_in_category > 0) {
				$this->box_categories_array[$ii]['count'] = $products_in_category;
			}
		}

		if( !empty( $this->tree[$counter]['next_id'] ) ) {
			$ii++;
			$this->zen_show_category($this->tree[$counter]['next_id'], $ii);
		}
		return $this->box_categories_array;
	}
}

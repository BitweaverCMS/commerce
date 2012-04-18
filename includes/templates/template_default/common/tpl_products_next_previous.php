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
  /*

  WebMakers.com Added: Previous/Next through categories products
  Thanks to Nirvana, Yoja and Joachim de Boer
  Modifications: Linda McGrath osCommerce@WebMakers.com


  Can now work with categories at any depth

  */

	// bof: previous next
	if (PRODUCT_INFO_PREVIOUS_NEXT != 0 && !empty( $_REQUEST['products_id'] ) ) {
		// calculate the previous and next
		if ( empty( $prev_next_list ) ) {

			// sort order
			switch(PRODUCT_INFO_PREVIOUS_NEXT_SORT) {
			case (0):
				$prev_next_order= ' order by LPAD(p.`products_id`,11,"0")';
				break;
			case (1):
				$prev_next_order= " order by pd.`products_name`";
				break;
			case (2):
				$prev_next_order= " order by p.`products_model`";
				break;
			case (3):
				$prev_next_order= " order by p.`lowest_purchase_price`, pd.`products_name`";
				break;
			case (4):
				$prev_next_order= " order by p.`lowest_purchase_price`, p.`products_model`";
				break;
			case (5):
				$prev_next_order= " order by pd.`products_name`, p.`products_model`";
				break;
			case (6):
				$prev_next_order= ' order by LPAD(p.`products_sort_order`,11,"0"), pd.`products_name`';
				break;
			default:
				$prev_next_order= " order by pd.`products_name`";
				break;
			}

			if( empty( $current_category_id ) ) {
				$current_category_id = $gBitDb->getOne( "SELECT `categories_id` from   " . TABLE_PRODUCTS_TO_CATEGORIES . " where  `products_id` =?", array( (int)$_GET['products_id'] ) );
			}

			$sql = "select p.`products_id`, p.`products_model`, p.`lowest_purchase_price`, pd.`products_name`, p.`products_sort_order`
					from   " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
					where  p.`products_status` = '1' and p.`products_id` = pd.`products_id` and pd.`language_id`= ? and p.`products_id` = ptc.`products_id` and ptc.`categories_id` = ?
					$prev_next_order ";

			$products_ids = $gBitDb->query( $sql, array( $_SESSION['languages_id'], $current_category_id ) );
		}

		while (!$products_ids->EOF) {
			$id_array[] = $products_ids->fields['products_id'];
			$products_ids->MoveNext();
		}

		$previous = NULL;
		$next_item = NULL;
		$position = NULL;
		// if invalid product id skip
		if( !empty( $id_array ) ) {
			reset ($id_array);
			$counter = 0;
			while (list($key, $value) = each ($id_array)) {
			if ($value == (int)$_GET['products_id']) {
				$position = $counter + 1;
				if ($key == 0) {
					$previous = -1; // it was the first to be found
				} else {
					$previous = $id_array[$key - 1];
				}
				if( !empty( $id_array[$key + 1] ) ) {
					$next_item = $id_array[$key + 1];
				} else {
					$next_item = $id_array[0];
				}
			}
			$last = $value;
			$counter++;
			}

			if ($previous == -1) $previous = $last;

			$sql = "select `categories_name`
					from   " . TABLE_CATEGORIES_DESCRIPTION . "
					where `categories_id` =? AND `language_id` =?";

			$category_name_row = $gBitDb->query( $sql, array( $current_category_id, $_SESSION['languages_id'] ) );
		} // if is_array

		// previous_next button and product image settings
		// include products_image status 0 = off 1= on
		// 0 = button only 1= button and product image 2= product image only
		$previous_button = zen_image_button(BUTTON_IMAGE_PREVIOUS, BUTTON_PREVIOUS_ALT);
		$next_item_button = zen_image_button(BUTTON_IMAGE_NEXT, BUTTON_NEXT_ALT);
		$previous_image = zen_get_products_image($previous, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT) . '<br />';
		$next_item_image = zen_get_products_image($next_item, PREVIOUS_NEXT_IMAGE_WIDTH, PREVIOUS_NEXT_IMAGE_HEIGHT) . '<br />';
		if (SHOW_PREVIOUS_NEXT_STATUS == 0) {
			$previous_image = '';
			$next_item_image = '';
		} else {
			if (SHOW_PREVIOUS_NEXT_IMAGES >= 1) {
			if (SHOW_PREVIOUS_NEXT_IMAGES == 2) {
				$previous_button = '';
				$next_item_button = '';
			}
			if ($previous == $next_item) {
				$previous_image = '';
				$next_item_image = '';
			}
			} else {
			$previous_image = '';
			$next_item_image = '';
			}
		}

		// only display when more than 1
		if ($products_ids->RecordCount() > 1) {
			$gBitSmarty->assign( 'navPosition', $position );
			$gBitSmarty->assign( 'navCounter', $counter );
			if( !empty( $previous ) ) {
				$gBitSmarty->assign( 'navPreviousUrl', CommerceProduct::getDisplayUrlFromHash( $previous ) );
			}
			if( !empty( $next_item ) ) {
				$gBitSmarty->assign( 'navNextUrl', CommerceProduct::getDisplayUrlFromHash( $next_item ) );
			}

/*
?>
<table border="1" align="left" width="100%">
<?php
    if ( empty( $cPath ) ) {
      $cPath= zen_get_product_path((int)$_GET['products_id']);
    }

    if ($module_show_categories != '0') {
      $cPath_new = zen_get_path(zen_get_products_category_id((int)$_GET['products_id']));
      if ((zen_get_categories_image(zen_get_products_category_id((int)$_GET['products_id']))) !='') {
        switch(true) {
          case ($module_show_categories=='1'):
          $align='left';
          break;
          case ($module_show_categories=='2'):
          $align='center';
          break;
          case ($module_show_categories=='3'):
          $align='right';
          break;
        }
?>
<?php
        if ($module_next_previous !='3') {
?>
  <tr>
    <td colspan="3" align="<?php echo $align; ?>">
      <?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT, $cPath_new, 'NONSSL') . '">' . zen_image( DIR_WS_CATALOG_IMAGES . zen_get_categories_image(zen_get_products_category_id((int)$_GET['products_id'])),zen_get_categories_name(zen_get_products_category_id((int)$_GET['products_id']), $_SESSION['languages_id']),HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, ' align="middle"') . '</a>'; ?>
      <?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT, $cPath_new, 'NONSSL') . '">' . zen_get_categories_name(zen_get_products_category_id((int)$_GET['products_id']), $_SESSION['languages_id']) . '</a>'; ?>
    </td>
  </tr>
<?php
        } // don't show when top and bottom
?>
<?php
      }
    }
?>
</table>
<?php
*/
		}
// 		print $gBitSmarty->fetch( 'bitpackage:bitcommerce/commerce_nav.tpl' );
	}
	// eof: previous next

?>

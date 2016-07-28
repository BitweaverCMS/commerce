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
//  $Id$
//

require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$languages = zen_get_languages();

if (zen_not_null($action)) {
	switch ($action) {
		case 'insert':
		case 'save':
			$orders_status_id = (int)BitBase::getParameter( $_REQUEST, 'orders_status_id' );

			for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
				$orders_status_name_array = $_POST['orders_status_name'];
				$language_id = $languages[$i]['id'];

				$sql_data_array = array('orders_status_name' => zen_db_prepare_input($orders_status_name_array[$language_id]));

				if ($action == 'insert') {
					if (empty($orders_status_id)) {
						$orders_status_id	= $gBitDb->GetOne("select max(orders_status_id) as orders_status_id from " . TABLE_ORDERS_STATUS . "");
					}

					$insert_sql_data = array('orders_status_id' => $orders_status_id, 'language_id' => $language_id);
					$sql_data_array = array_merge($sql_data_array, $insert_sql_data);

					$gBitDb->associateInsert(TABLE_ORDERS_STATUS, $sql_data_array);
				} elseif ($action == 'save') {
					$gBitDb->query( "UPDATE " . TABLE_ORDERS_STATUS . " SET `orders_status_name`=? WHERE `orders_status_id`=? AND `language_id`=? ", array( $orders_status_name_array[$language_id], (int)$orders_status_id, $language_id ) );
				}
			}

			if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
				$gBitDb->query( "UPDATE " . TABLE_CONFIGURATION . " set `configuration_value` = ? where `configuration_key` = 'DEFAULT_ORDERS_STATUS_ID'", array( $orders_status_id ) );
			}

			zen_redirect(zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $orders_status_id));
			break;
		case 'deleteconfirm':
			// demo active test
			if (zen_admin_demo()) {
				$_GET['action']= '';
				$messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
				zen_redirect(zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']));
			}
			$orders_status_id = zen_db_prepare_input($_GET['orders_status_id']);

			$orders_status = $gBitDb->Execute("select `configuration_value` from " . TABLE_CONFIGURATION . " where `configuration_key` = 'DEFAULT_ORDERS_STATUS_ID'");

			if ($orders_status->fields['configuration_value'] == $orders_status_id) {
				$gBitDb->Execute("update " . TABLE_CONFIGURATION . " set `configuration_value` = '' where `configuration_key` = 'DEFAULT_ORDERS_STATUS_ID'");
			}

			$gBitDb->Execute("delete from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . zen_db_input($orders_status_id) . "'");

			zen_redirect(zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']));
			break;
		case 'delete':
			$orders_status_id = zen_db_prepare_input($_GET['orders_status_id']);

			$status = $gBitDb->Execute("select count(*) as `ocount` from " . TABLE_ORDERS . " where orders_status = '" . (int)$orders_status_id . "'");

			$remove_status = true;
			if ($orders_status_id == DEFAULT_ORDERS_STATUS_ID) {
				$remove_status = false;
				$messageStack->add(ERROR_REMOVE_DEFAULT_ORDER_STATUS, 'error');
			} elseif ($status->fields['ocount'] > 0) {
				$remove_status = false;
				$messageStack->add(ERROR_STATUS_USED_IN_ORDERS, 'error');
			} else {
				$history = $gBitDb->Execute("select count(*) as `oscount`
																 from " . TABLE_ORDERS_STATUS_HISTORY . "
																 where orders_status_id = '" . (int)$orders_status_id . "'");

				if ($history->fields['oscount'] > 0) {
					$remove_status = false;
					$messageStack->add(ERROR_STATUS_USED_IN_HISTORY, 'error');
				}
			}
			break;
	}
}
?>
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<div class="row">
<div class="col-md-8">
	<table class="table table-hover">
						<tr class="dataTableHeadingRow">
							<td class="text-right"><?php echo tra( 'ID' ); ?></td>
							<td><?php echo tra( 'Status Name' ); ?></td>
							<td class="text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
						</tr>
<?php
$orders_status_query_raw = "select `orders_status_id`, `orders_status_name` from " . TABLE_ORDERS_STATUS . " where `language_id` = '" . (int)$_SESSION['languages_id'] . "' order by `orders_status_id`";
$orders_status_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_status_query_raw, $orders_status_query_numrows);
$orders_status = $gBitDb->Execute($orders_status_query_raw);
while (!$orders_status->EOF) {
	if ((!isset($_GET['orders_status_id']) || (isset($_GET['orders_status_id']) && ($_GET['orders_status_id'] == $orders_status->fields['orders_status_id']))) && !isset($oInfo) && (substr($action, 0, 3) != 'new')) {
		$oInfo = new objectInfo($orders_status->fields);
	}

	if (isset($oInfo) && is_object($oInfo) && ($orders_status->fields['orders_status_id'] == $oInfo->orders_status_id)) {
		echo '									<tr id="defaultSelected" class="info" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $oInfo->orders_status_id . '&action=edit') . '\'">' . "\n";
	} else {
		echo '									<tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $orders_status->fields['orders_status_id']) . '\'">' . "\n";
	}

	echo '<td class="dataTableContent currency">' . '#' . $orders_status->fields['orders_status_id'] . '</td><td class="dataTableContent">' . $orders_status->fields['orders_status_name'];
	if (DEFAULT_ORDERS_STATUS_ID == $orders_status->fields['orders_status_id']) {
		echo '<strong>(' . tra( 'Default' ) . ')</strong>';
	}
	echo "</td>\n";
?>
							<td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders_status->fields['orders_status_id'] == $oInfo->orders_status_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $orders_status->fields['orders_status_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
						</tr>
<?php
	$orders_status->MoveNext();
}
?>
						</table>
		<div class="row">
			<div class="col-sm-6 text-left"><?php echo $orders_status_split->display_count($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS); ?></div>
							<div class="col-sm-6 text-right"><?php echo $orders_status_split->display_links($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
					</div>
<?php
if (empty($action)) {
?>
									<?php echo '<a class="btn btn-default" href="' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=new') . '">' . tra( 'Add New Status' ) . '</a>'; ?>
<?php
}
?>
</div>
<div class="col-md-4">
	<div class="panel-group">
<?php
$heading = array();
$contents = array();

switch ($action) {
	case 'new':
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS . '</b>');

		$contents = array('form' => zen_draw_form_admin('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=insert'));
		$contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

		$orders_status_inputs_string = '';
		$languages = zen_get_languages();
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$orders_status_inputs_string .= '
			<div class="input-group">
				'.zen_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']').'
				<span class="input-group-addon">'.zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']).'</span>
			</div>';
			;
		}

		$contents[] = array('text' => '<label>'.tra( TEXT_INFO_ORDERS_STATUS_NAME ).'</label>' . $orders_status_inputs_string );
		$contents[] = array('text' => '<label>'.tra( 'Status ID' ).'</label>'.zen_draw_input_field( 'orders_status_id', (int)BitBase::getParameter( $_REQUEST, 'orders_status_id' ), NULL, 'number' ) );
		$contents[] = array('text' => zen_draw_selection_field( array( 'type' => 'checkbox', 'name'=>'default', 'label' => TEXT_SET_DEFAULT ) ) );
		$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	case 'edit':
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS . '</b>');

		$contents = array('form' => zen_draw_form_admin('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $oInfo->orders_status_id	. '&action=save'));
		$contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

		$orders_status_inputs_string = '';
		$languages = zen_get_languages();
		for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
			$orders_status_inputs_string .= 
				'<div class="input-group">
					'.zen_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']', zen_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id'])).'
					<span class="input-group-addon">'.zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']).'</span>
				</div>';
		}

		$contents[] = array('text' => '<br>' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string);
		if (DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id) $contents[] = array('text' => '<br>' . zen_draw_selection_field( array( 'type' => 'checkbox', 'name'=>'default', 'label' => TEXT_SET_DEFAULT ) ));
		$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $oInfo->orders_status_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	case 'delete':
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ORDERS_STATUS . '</b>');

		$contents = array('form' => zen_draw_form_admin('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $oInfo->orders_status_id	. '&action=deleteconfirm'));
		$contents[] = array('text' => 'Are you sure you want to delete this order status?');
		$contents[] = array('text' => '<br><b>' . $oInfo->orders_status_name . '</b>');
		if ($remove_status) $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $oInfo->orders_status_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	default:
		if (isset($oInfo) && is_object($oInfo)) {
			$heading[] = array('text' => '<b>' . $oInfo->orders_status_name . '</b>');

			$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $oInfo->orders_status_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&orders_status_id=' . $oInfo->orders_status_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');

			$orders_status_inputs_string = '';
			$languages = zen_get_languages();
			for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
				$orders_status_inputs_string .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']);
			}

			$contents[] = array('text' => $orders_status_inputs_string);
		}
		break;
}

if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
	$box = new box;
	echo $box->infoBox($heading, $contents);
}
?>
			</div>
		</div>
	</div>
</div>

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

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

if (zen_not_null($action)) {
	switch ($action) {
		case 'insert':
			$countries_name = zen_db_prepare_input($_POST['countries_name']);
			$countries_iso_code_2 = zen_db_prepare_input($_POST['countries_iso_code_2']);
			$countries_iso_code_3 = zen_db_prepare_input($_POST['countries_iso_code_3']);
			$address_format_id = zen_db_prepare_input($_POST['address_format_id']);

			$gBitDb->Execute("insert into " . TABLE_COUNTRIES . "
									(`countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id`)
									values ('" . zen_db_input($countries_name) . "',
													'" . zen_db_input($countries_iso_code_2) . "',
													'" . zen_db_input($countries_iso_code_3) . "',
													'" . (int)$address_format_id . "')");

			zen_redirect(zen_href_link_admin(FILENAME_COUNTRIES));
			break;
		case 'save':
			$countries_id = zen_db_prepare_input($_GET['cID']);
			$countries_name = zen_db_prepare_input($_POST['countries_name']);
			$countries_iso_code_2 = zen_db_prepare_input($_POST['countries_iso_code_2']);
			$countries_iso_code_3 = zen_db_prepare_input($_POST['countries_iso_code_3']);
			$address_format_id = zen_db_prepare_input($_POST['address_format_id']);

			$gBitDb->Execute("update " . TABLE_COUNTRIES . "
										set `countries_name` = '" . zen_db_input($countries_name) . "',
												`countries_iso_code_2` = '" . zen_db_input($countries_iso_code_2) . "',
												`countries_iso_code_3` = '" . zen_db_input($countries_iso_code_3) . "',
												`address_format_id` = '" . (int)$address_format_id . "'
										where `countries_id` = '" . (int)$countries_id . "'");

			zen_redirect(zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $countries_id));
			break;
		case 'deleteconfirm':
			// demo active test
			if (zen_admin_demo()) {
				$_GET['action']= '';
				$messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
				zen_redirect(zen_href_link_admin(FILENAME_COUNTRIES));
			}
			$countries_id = zen_db_prepare_input($_GET['cID']);

			$gBitDb->Execute("delete from " . TABLE_COUNTRIES . "
										where `countries_id` = '" . (int)$countries_id . "'");

			zen_redirect(zen_href_link_admin(FILENAME_COUNTRIES));
			break;
	}
}

require(DIR_FS_ADMIN_INCLUDES . 'header.php');

?>

<div class="row">
	<div class="col-md-8">
		<table class="table table-hover">
			<tr class="dataTableHeadingRow">
				<th class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></th>
				<th class="dataTableHeadingContent" align="center" colspan="2"><?php echo TABLE_HEADING_COUNTRY_CODES; ?></th>
				<th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
			</tr>
<?php
	$countries_query_numrows = 20;
	$countries_query_raw = "select `countries_id`, `countries_name`, `countries_iso_code_2`, `countries_iso_code_3`, `address_format_id` from " . TABLE_COUNTRIES . " order by `countries_name`";
	$countries = $gBitDb->query( $countries_query_raw );
	while (!$countries->EOF) {
		if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $countries->fields['countries_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
			$cInfo = new objectInfo($countries->fields);
		}

		if (isset($cInfo) && is_object($cInfo) && ($countries->fields['countries_id'] == $cInfo->countries_id)) {
			echo '									<tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $cInfo->countries_id . '&action=edit') . '\'">' . "\n";
		} else {
			echo '									<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $countries->fields['countries_id']) . '\'">' . "\n";
		}
?>
							<td class="dataTableContent"><?php echo $countries->fields['countries_name']; ?></td>
							<td class="dataTableContent" align="center" width="40"><?php echo $countries->fields['countries_iso_code_2']; ?></td>
							<td class="dataTableContent" align="center" width="40"><?php echo $countries->fields['countries_iso_code_3']; ?></td>
							<td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($countries->fields['countries_id'] == $cInfo->countries_id) ) { echo '<i class="fa fal fa-circle-arrow-right"></i>'; } else { echo '<a href="' . zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $countries->fields['countries_id']) . '"><i class="fa fal fa-info-circle"></i></a>'; } ?>&nbsp;</td>
						</tr>
<?php
		$countries->MoveNext();
	}
?>
							</td>
						</tr>
					</table>
	</div>
	<div class="col-md-4">
<?php
$heading = array();
$contents = array();

switch ($action) {
	case 'new':
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_COUNTRY . '</b>');

		$contents = array('form' => zen_draw_form_admin('countries', FILENAME_COUNTRIES, 'action=insert'));
		$contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
		$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . '<br>' . zen_draw_input_field('countries_name'));
		$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_2 . '<br>' . zen_draw_input_field('countries_iso_code_2'));
		$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_3 . '<br>' . zen_draw_input_field('countries_iso_code_3'));
		$contents[] = array('text' => '<br>' . TEXT_INFO_ADDRESS_FORMAT . '<br>' . zen_draw_pull_down_menu('address_format_id', zen_get_address_formats()));
		$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_COUNTRIES) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	case 'edit':
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_COUNTRY . '</b>');

		$contents = array('form' => zen_draw_form_admin('countries', FILENAME_COUNTRIES, 'cID=' . $cInfo->countries_id . '&action=save'));
		$contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
		$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . '<br>' . zen_draw_input_field('countries_name', $cInfo->countries_name));
		$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_2 . '<br>' . zen_draw_input_field('countries_iso_code_2', $cInfo->countries_iso_code_2));
		$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_3 . '<br>' . zen_draw_input_field('countries_iso_code_3', $cInfo->countries_iso_code_3));
		$contents[] = array('text' => '<br>' . TEXT_INFO_ADDRESS_FORMAT . '<br>' . zen_draw_pull_down_menu('address_format_id', zen_get_address_formats(), $cInfo->address_format_id));
		$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $cInfo->countries_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	case 'delete':
		$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_COUNTRY . '</b>');

		$contents = array('form' => zen_draw_form_admin('countries', FILENAME_COUNTRIES, 'cID=' . $cInfo->countries_id . '&action=deleteconfirm'));
		$contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
		$contents[] = array('text' => '<br><b>' . $cInfo->countries_name . '</b>');
		$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $cInfo->countries_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
		break;
	default:
		if (is_object($cInfo)) {
			$heading[] = array('text' => '<b>' . $cInfo->countries_name . '</b>');

			$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $cInfo->countries_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_COUNTRIES, 'cID=' . $cInfo->countries_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
			$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . '<br>' . $cInfo->countries_name);
			$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_2 . ' ' . $cInfo->countries_iso_code_2);
			$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_CODE_3 . ' ' . $cInfo->countries_iso_code_3);
			$contents[] = array('text' => '<br>' . TEXT_INFO_ADDRESS_FORMAT . ' ' . $cInfo->address_format_id);
		}
		break;
}

if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
	echo '						<td width="25%" valign="top">' . "\n";

	$box = new box;
	echo $box->infoBox($heading, $contents);

	echo '						</td>' . "\n";
}
?>
				</tr>
				</table>
	</div>
</div>

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

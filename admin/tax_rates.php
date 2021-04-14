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

if( $action = BitBase::getParameter( $_GET, 'action' ) ) {
	switch ($action) {
		case 'insert':
			$tax_zone_id = zen_db_prepare_input($_POST['tax_zone_id']);
			$tax_class_id = zen_db_prepare_input($_POST['tax_class_id']);
			$tax_rate = zen_db_prepare_input($_POST['tax_rate']);
			$tax_description = zen_db_prepare_input($_POST['tax_description']);
			$tax_priority = zen_db_prepare_input($_POST['tax_priority']);

			$gBitDb->Execute("INSERT INTO " . TABLE_TAX_RATES . " (`tax_zone_id`, `tax_class_id`, `tax_rate`, `tax_description`, `tax_priority`, `date_added`)
							VALUES ('" . (int)$tax_zone_id . "',
									'" . (int)$tax_class_id . "',
									'" . zen_db_input($tax_rate) . "',
									'" . zen_db_input($tax_description) . "',
									'" . (int)zen_db_input($tax_priority) . "',
									". $gBitDb->mDb->sysTimeStamp .")");

			zen_redirect(zen_href_link_admin(FILENAME_TAX_RATES));
			break;
		case 'save':
			$tax_rates_id = zen_db_prepare_input($_GET['tID']);
			$tax_zone_id = zen_db_prepare_input($_POST['tax_zone_id']);
			$tax_class_id = zen_db_prepare_input($_POST['tax_class_id']);
			$tax_rate = zen_db_prepare_input($_POST['tax_rate']);
			$tax_description = zen_db_prepare_input($_POST['tax_description']);
			$tax_priority = zen_db_prepare_input($_POST['tax_priority']);
			$gBitDb->query("UPDATE " . TABLE_TAX_RATES . " SET `tax_zone_id` = ?, `tax_class_id` = ?, `tax_rate`= ?, `tax_description` = ?, `tax_priority` = ?,
								`last_modified` = ".$gBitDb->mDb->sysTimeStamp."	WHERE `tax_rates_id` = ?", array( $tax_zone_id, $tax_class_id, $tax_rate, $tax_description, $tax_priority, $tax_rates_id ) );
			zen_redirect(zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $tax_rates_id));
			break;
		case 'deleteconfirm':
			// demo active test
			if (zen_admin_demo()) {
				$_GET['action']= '';
				$messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
				zen_redirect(zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page']));
			}
			$tax_rates_id = zen_db_prepare_input($_GET['tID']);

			$gBitDb->Execute("DELETE FROM " . TABLE_TAX_RATES . "
							WHERE `tax_rates_id` = '" . (int)$tax_rates_id . "'");

			zen_redirect(zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page']));
			break;
	}
}

require(DIR_FS_ADMIN_INCLUDES . 'header.php');

?>

<div class="row">
	<div class="col-md-8">
		<table class="table table-hover">
			<tr class="dataTableHeadingRow">
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE_PRIORITY; ?></td>
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASS_TITLE; ?></td>
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE; ?></td>
				<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE; ?></td>
				<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
			</tr>
<?php
	$rates_query_raw = "select r.`tax_rates_id`, z.`geo_zone_id`, z.`geo_zone_name`, tc.`tax_class_title`, tc.`tax_class_id`, r.`tax_priority`, r.`tax_rate`, r.`tax_description`, r.`date_added`, r.`last_modified` from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_GEO_ZONES . " z on r.`tax_zone_id` = z.`geo_zone_id` where r.`tax_class_id` = tc.`tax_class_id`";
	$rates_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $rates_query_raw, $rates_query_numrows);
	$rates = $gBitDb->Execute($rates_query_raw);
	while (!$rates->EOF) {
		if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $rates->fields['tax_rates_id']))) && !isset($trInfo) && (substr($action, 0, 3) != 'new')) {
			$trInfo = new objectInfo($rates->fields);
		}

		if (isset($trInfo) && is_object($trInfo) && ($rates->fields['tax_rates_id'] == $trInfo->tax_rates_id)) {
			echo '							<tr id="defaultSelected" class="info" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '\'">' . "\n";
		} else {
			echo '							<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $rates->fields['tax_rates_id']) . '\'">' . "\n";
		}
?>
								<td class="dataTableContent"><?php echo $rates->fields['tax_priority']; ?></td>
								<td class="dataTableContent"><?php echo $rates->fields['tax_class_title']; ?></td>
								<td class="dataTableContent"><?php echo $rates->fields['geo_zone_name']; ?></td>
								<td class="dataTableContent"><?php echo zen_display_tax_value($rates->fields['tax_rate']); ?>%</td>
								<td class="dataTableContent" align="right"><?php if (isset($trInfo) && is_object($trInfo) && ($rates->fields['tax_rates_id'] == $trInfo->tax_rates_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $rates->fields['tax_rates_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
							</tr>
<?php
		$rates->MoveNext();
	}
?>
		</table>
			<div class="pull-left smallText"><?php echo $rates_split->display_count($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_TAX_RATES); ?></div>
			<div class="pull-right"><?php echo $rates_split->display_links($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>

<?php
	if (empty($action)) {
?>
		<div class="clear">
			<?php echo '<a href="' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=new') . '">' . zen_image_button('button_new_tax_rate.gif', IMAGE_NEW_TAX_RATE) . '</a>'; ?>
		</div>
<?php
	}
?>

	</div>
	<div class="col-md-4">

<?php
	$heading = array();
	$contents = array();

	switch ($action) {
		case 'new':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_TAX_RATE . '</b>');

			$contents = array('form' => zen_draw_form_admin('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=insert'));
			$contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
			$contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . zen_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . zen_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE . '<br>' . zen_draw_input_field('tax_rate'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . zen_draw_input_field('tax_description'));
			$contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE_PRIORITY . '<br>' . zen_draw_input_field('tax_priority'));
			$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'edit':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</b>');

			$contents = array('form' => zen_draw_form_admin('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id	. '&action=save'));
			$contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
			$contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . zen_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"', $trInfo->tax_class_id));
			$contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . zen_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"', $trInfo->geo_zone_id));
			$contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE . '<br>' . zen_draw_input_field('tax_rate', $trInfo->tax_rate));
			$contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . zen_draw_input_field('tax_description', $trInfo->tax_description));
			$contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE_PRIORITY . '<br>' . zen_draw_input_field('tax_priority', $trInfo->tax_priority));
			$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		case 'delete':
			$heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TAX_RATE . '</b>');

			$contents = array('form' => zen_draw_form_admin('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id	. '&action=deleteconfirm'));
			$contents[] = array('text' => tra( 'Are you sure you want to delete this tax rate?' ) );
			$contents[] = array('text' => '<br><b>' . $trInfo->tax_class_title . ' ' . number_format($trInfo->tax_rate, TAX_DECIMAL_PLACES) . '%</b>');
			$contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
			break;
		default:
			if (is_object($trInfo)) {
				$heading[] = array('text' => '<b>' . $trInfo->tax_class_title . '</b>');
				$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link_admin(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
				$contents[] = array('align' => 'center', 'text' =>	'<a href="' . zen_href_link_admin(FILENAME_GEO_ZONES, '', 'NONSSL') . '">' . zen_image_button('button_define_zones.gif', IMAGE_DEFINE_ZONES) . '</a>');
				$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($trInfo->date_added));
				$contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($trInfo->last_modified));
				$contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . $trInfo->tax_description);
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

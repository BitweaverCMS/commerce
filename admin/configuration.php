<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce																			 |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers													 |
// |																																			|
// | http://www.zen-cart.com/index.php																		|
// |																																			|
// | Portions Copyright (c) 2003 osCommerce															 |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,			 |
// | that is bundled with this package in the file LICENSE, and is				|
// | available through the world-wide-web at the following url:					 |
// | http://www.zen-cart.com/license/2_0.txt.														 |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to			 |
// | license@zen-cart.com so we can mail you a copy immediately.					|
// +----------------------------------------------------------------------+
//	$Id$
//

	$pageTitle = 'Configuration Settings';
	require('includes/application_top.php');

	$action = (isset($_GET['action']) ? $_GET['action'] : '');

	if( !empty( $action ) ) {
		switch ($action) {
			case 'save':
				$cID = zen_db_prepare_input($_GET['cID']);
				$gCommerceSystem->storeConfigId( $cID, $_POST['configuration_value'] );
				zen_redirect(zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cID));
				break;
		}
	}

	$gID = BitBase::getParameter( $_GET, 'gID', 1 );
	$_GET['gID'] = $gID;
	if( $cfgGroupTitle = $gBitDb->getOne( "SELECT `configuration_group_title` FROM " . TABLE_CONFIGURATION_GROUP . " WHERE `configuration_group_id` = ?", (int)$gID ) ) {
		$pageTitle .= ': '.tra( $cfgGroupTitle );
	}

	define( 'HEADING_TITLE', $pageTitle );
	if ($gID == 7) {
		$shipping_errors = '';
		if (zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == 'NONE' or zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == '') {
			$shipping_errors .= '<br />' . ERROR_SHIPPING_ORIGIN_ZIP;
		}
		if (zen_get_configuration_key_value('ORDER_WEIGHT_ZERO_STATUS') == '1' and !defined('MODULE_SHIPPING_FREESHIPPER_STATUS')) {
			$shipping_errors .= '<br />' . ERROR_ORDER_WEIGHT_ZERO_STATUS;
		}
		if ($shipping_errors != '') {
			$messageStack->add(ERROR_SHIPPING_CONFIGURATION . $shipping_errors, 'caution');
		}
	}

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<div class="row">
	<div class="col-md-8">
		<table class="table table-hover">
							<tr class="dataTableHeadingRow">
								<td class="dataTableHeadingContent" width="55%"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
								<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
								<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
							</tr>
<?php
	$configuration = $gBitDb->query("SELECT `configuration_id`, `configuration_title`, `configuration_value`, `configuration_key`,
																				`use_function` FROM " . TABLE_CONFIGURATION . "
																				WHERE `configuration_group_id` = ?
																				ORDER BY `sort_order`", array( $gID ) );
	while (!$configuration->EOF) {
		if (zen_not_null($configuration->fields['use_function'])) {
			$use_function = $configuration->fields['use_function'];
			if ( strpos( $use_function, '->' ) !== FALSE ) {
				$class_method = explode('->', $use_function);
				if (!is_object(${$class_method[0]})) {
					include(DIR_WS_CLASSES . $class_method[0] . '.php');
					${$class_method[0]} = new $class_method[0]();
				}
				$cfgValue = zen_call_function($class_method[1], $configuration->fields['configuration_value'], ${$class_method[0]});
			} else {
				$cfgValue = zen_call_function($use_function, $configuration->fields['configuration_value']);
			}
		} else {
			$cfgValue = $configuration->fields['configuration_value'];
		}

		if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $configuration->fields['configuration_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
			$cfg_extra = $gBitDb->query("SELECT `configuration_key`, `configuration_description`, `date_added`, `last_modified`, `use_function`, `set_function` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_id` = ?", array( (int)$configuration->fields['configuration_id'] ) );
			$cInfo_array = array_merge($configuration->fields, $cfg_extra->fields);
			$cInfo = new objectInfo($cInfo_array);
		}

		if ( (isset($cInfo) && is_object($cInfo)) && ($configuration->fields['configuration_id'] == $cInfo->configuration_id) ) {
			echo '									<tr id="defaultSelected" class="info" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
		} else {
			echo '									<tr class="dataTableRow" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $configuration->fields['configuration_id'] . '&action=edit') . '\'">' . "\n";
		}
?>
								<td class="dataTableContent"><?php echo $configuration->fields['configuration_title']; ?></td>
								<td class="dataTableContent"><?php echo htmlspecialchars($cfgValue); ?></td>
								<td class="dataTableContent" align="right"><?php if ( (isset($cInfo) && is_object($cInfo)) && ($configuration->fields['configuration_id'] == $cInfo->configuration_id) ) { echo '<i class="fa fal fa-circle-arrow-right"></i>'; } else { echo '<a href="' . zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $configuration->fields['configuration_id']) . '"><i class="fa fal fa-info-circle"></i></a>'; } ?>&nbsp;</td>
							</tr>
<?php
		$configuration->MoveNext();
	}
?>
						</table>
	</div>
	<div class="col-md-4">
<?php
	$heading = array();
	$contents = array();

	switch ($action) {
		case 'edit':
			$heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');

			if ($cInfo->set_function) {
				eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value) . '");');
			} else {
				$value_field = zen_draw_input_field('configuration_value', $cInfo->configuration_value, 'size="60"');
			}

			$contents = array('form' => zen_draw_form_admin('configuration', FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=save'));
			if (ADMIN_CONFIGURATION_KEY_ON == 1) {
				$contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
			}
			$contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
			$contents[] = array('text' => '<br><b>' . $cInfo->configuration_title . '</b><br>' . $cInfo->configuration_description . '<br>' . $value_field);
			$contents[] = array('align' => 'center', 'text' => zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a class="btn" href="' . zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id) . '">' . tra( 'Cancel' ) . '</a>');
			break;
		default:
			if (isset($cInfo) && is_object($cInfo)) {
				$heading[] = array('text' => '<b>' . $cInfo->configuration_title . '</b>');
				if (ADMIN_CONFIGURATION_KEY_ON == 1) {
					$contents[] = array('text' => '<strong>Key: ' . $cInfo->configuration_key . '</strong><br />');
				}

				$contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $_GET['gID'] . '&cID=' . $cInfo->configuration_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
				$contents[] = array('text' => '<br>' . $cInfo->configuration_description);
				$contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($cInfo->date_added));
				if (zen_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($cInfo->last_modified));
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

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

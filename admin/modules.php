<?php
/**
 * @package bitcommerce
 * @author spiderr <spiderr@bitweaver.org>
 * Copyright (c) 2020 bitweaver.org, All Rights Reserved
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 * This source file is subject to the 2.0 GNU GENERAL PUBLIC LICENSE. 
 *
 * Logic layer that handles module installation, removal, and configuration.
 *
 */

require('includes/application_top.php');

$moduleType = (isset($_GET['set']) ? $_GET['set'] : '');

// Fulfillment and Payment need full admin permissions
if ($moduleType == 'payment' || $moduleType == 'fulfillment' || $moduleType == 'shipping' ) {
	$gBitUser->verifyPermission( 'p_admin' );
}

if( $moduleType == 'shipping' ) {
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

define('HEADING_TITLE', tra( ucfirst( str_replace( '_', ' ', $moduleType ) ) ) );

require(DIR_FS_ADMIN_INCLUDES . 'header.php'); 

?>

<div class="row">
	<div class="col-md-8">
		<table class="table table-hover">
						<tr class="dataTableHeadingRow">
							<th colspan="2"><?php echo TABLE_HEADING_MODULES; ?></th>
							<th align="right"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
<?php
if ($moduleType == 'payment') {
?>
							<th align="center"><?php echo TABLE_HEADING_ORDERS_STATUS; ?></th>
<?php } else { ?>
							<th align="center"></th>
<?php }?>

							<th align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
						</tr>
<?php

if( !empty( $moduleType ) ) {
	$modules = CommerceSystem::scanModules( $moduleType, FALSE );

	foreach ( $modules as $class => $module ) {
		$rowClass = ($module->isEnabled() ? 'success' : ($module->isInstalled() ? 'warning' : 'default'));

		echo '<tr class="'. $rowClass . '" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . '&module=' . $class, 'NONSSL') . '\'">' . "\n";
?>
							<td class="dataTableContent"><?php echo $module->getAdminTitle(); ?></td>
							<td class="dataTableContent"><?php echo $module->code; ?></td>
							<td class="dataTableContent" align="right"><?php echo $module->getSortOrder(); ?></td>
<?php
if ($moduleType == 'payment' and !empty($module->order_status)) {
		$orders_status_name = $gBitDb->query("select `orders_status_id`, `orders_status_name` from " . TABLE_ORDERS_STATUS . " where `orders_status_id` = ? and `language_id` = ? ", array( $module->order_status, $_SESSION['languages_id'] ) );
?>
							<td class="dataTableContent" align="left">&nbsp;&nbsp;&nbsp;<?php echo (is_numeric($module->getSortOrder()) ? (($orders_status_name->fields['orders_status_id'] < 1) ? TEXT_DEFAULT : $orders_status_name->fields['orders_status_name']) : ''); ?>&nbsp;&nbsp;&nbsp;</td>
<?php
	} else {
?>
				<td class="dataTableContent" align="left">&nbsp;</td>
<?php
	}
?>
							<td class="dataTableContent" align="right"><?php if (isset($moduleInfo) && is_object($moduleInfo) && ($class == $moduleInfo->code) ) { echo '<i class="fa fal fa-circle-arrow-right"></i>'; } else { echo '<a href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . '&module=' . $class, 'NONSSL') . '"><i class="fa fal fa-info-sign"></i></a>'; } ?>&nbsp;</td>
						</tr>
<?php
	}
}
?>
					</table>
					<p><?php echo TEXT_MODULE_DIRECTORY . ' ' . DIR_FS_CATALOG_MODULES . "$moduleType/"; ?></p>
	</div>
	<div class="col-md-4">
		<div class="panel-group">
<?php

reset( $modules );
$activeModule = !empty($_GET['module'] ) && !empty( $modules[$_GET['module']] ) ? $modules[$_GET['module']] : current( $modules );
if( is_object( $activeModule ) ) {
	$pageAction = BitBase::getParameter( $_REQUEST, 'action' );
	if( !empty( $pageAction ) ) {
		switch( $pageAction ) {
			case 'save':
				foreach( $_POST['configuration'] as $key => $value ) {
					global $gCommerceSystem;
					$gCommerceSystem->storeConfig( $key, $value );
				}
				break;
			case 'fix':
				$activeModule->fixConfig();
				break;
			case 'install':
				$activeModule->install();
				break;
			case 'remove':
				$activeModule->remove();
				break;
		}
		if( $pageAction != 'edit' ) {
			$gCommerceSystem->clearFromCache();
			zen_redirect(zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . '&module=' . $activeModule->code, 'NONSSL'));
		}
	}

	$heading[] = array( 'text' => $activeModule->getAdminTitle() );
	$contents = array();
	$moduleKeys = $activeModule->keys();

	$activeConfig = $activeModule->getActiveConfig();
	$defaultConfig = $activeModule->getDefaultConfig();

	$missingConfig = array();
	$unusedConfig = array();
	if( $activeModule->isInstalled() ) {
		if( $diffConfig = array_diff( $activeModule->keys(), array_keys( $activeConfig ) ) ) {
			foreach( $diffConfig as $configKey ) {
//				if( !empty( $defaultConfig[$configKey]['configuration_value'] ) ) {
					$missingConfig[] = $configKey;
//				}
			}
			if( !empty( $missingConfig ) ) {
				$contents[] = array( 'text' => 'Module is missing the following configurations: <ol><li>'.implode( '</li><li>', $missingConfig ).'</li></ol>' );
			}
		}
		if( $unusedConfig = array_diff( array_keys( $activeConfig ), $activeModule->keys() ) ) {
			$contents[] = array( 'text' => 'Module has the following unused configurations: <ol><li>'.implode( '</li><li>', $unusedConfig ).'</li></ol>' );
		}
	}

	$fixConfig = count( $missingConfig ) + count( $unusedConfig );

	if( $fixConfig ) {
		$panelClass = 'panel-danger';
	} elseif( $activeModule->isEnabled() ) {
		$panelClass = 'panel-success';
	} elseif( $activeModule->isInstalled() ) {
		$panelClass = 'panel-warning';
	} else {
		$panelClass = 'panel-default';
	}

	switch( $pageAction ) {
		case 'edit':
			$keys = '';
			foreach( $activeModule->keys() as $configKey ) {
				if( $value = BitBase::getParameter( $activeConfig, $configKey ) ) {
					$keys .= '<b>' . BitBase::getParameter( $value, 'configuration_title', $configKey ) . '</b><br>' . $value['configuration_description'] . '<br>';
					if( !empty( $value['set_function'] ) ) {
						eval('$keys .= ' . $value['set_function'] . "'" . $value['configuration_value'] . "', '" . $configKey . "');");
					} else {
						$keys .= zen_draw_input_field('configuration[' . $configKey . ']', $value['configuration_value']);
					}
					$keys .= '<br><br>';
				}
			}
			$keys = substr($keys, 0, strrpos($keys, '<br><br>'));
			$contents = array('form' => zen_draw_form_admin('modules', FILENAME_MODULES, 'set=' . $moduleType . (!empty($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=save', 'post', '', true));
			if (ADMIN_CONFIGURATION_KEY_ON == 1) {
				$contents[] = array('text' => '<strong>Key: ' . $activeModule->code . '</strong><br />');
			}
			$contents[] = array('text' => $keys);
			$contents[] = array('align' => 'center', 'text' =>  '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a class="btn btn-default" href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . (!empty( $_GET['module'] ) ? '&module=' . $_GET['module'] : ''), 'NONSSL') . '">' . tra( 'Cancel' ) . '</a>');
			break;
		default:
			if( $activeModule->isInstalled() ) {
				$keys = '';
				foreach( $activeConfig as $configKey=>$value ) {
					$keys .= '<b>' . $value['configuration_title'] . '</b><br>';
					if ($value['use_function']) {
						$use_function = $value['use_function'];
						if( strpos( $use_function, '->' ) !== FALSE ) {
							$class_method = explode('->', $use_function);
							if (!is_object(${$class_method[0]})) {
								include(DIR_WS_CLASSES . $class_method[0] . '.php');
								${$class_method[0]} = new $class_method[0]();
							}
							$keys .= zen_call_function($class_method[1], $value['configuration_value'], ${$class_method[0]});
						} else {
							$keys .= zen_call_function($use_function, $value['configuration_value']);
						}
					} else {
						$keys .= $value['configuration_value'];
					}
					$keys .= '<br><br>';
				}

				$keys = substr($keys, 0, strrpos($keys, '<br><br>'));
				$buttonContents = '';
				if( $fixConfig ) {
					$buttonContents = '<a class="btn btn-danger" href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . '&module=' . $activeModule->code . '&action=fix', 'NONSSL') . '">' . tra( 'Fix' ) . '</a> ';
				}
				$buttonContents .= '<a class="btn btn-default" href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . (isset($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=edit', 'NONSSL') . '">' . tra( 'Edit' ) . '</a> <a class="btn btn-default" href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . '&module=' . $activeModule->code . '&action=remove', 'NONSSL') . '" onclick="return confirm(\''.$activeModule->getAdminTitle().': '.tra('Are you sure you want to delete this module?').' '.tra('This will delete all module data and cannot be undone.').'\')">' . tra( 'Remove' ) . '</a>';
				$contents[] = array('text' => $buttonContents );
				$contents[] = array('text' => '<br>' . $activeModule->description);
				$contents[] = array('text' => '<br>' . $keys);
			} else {
				$contents[] = array('align' => 'center', 'text' => '<a class="btn btn-default" href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $moduleType . '&module=' . $activeModule->code . '&action=install', 'NONSSL') . '">' . tra( 'Install Module' ) . '</a>');
				$contents[] = array('text' => '<br>' . $activeModule->description);
			}
			break;
	}
	$box = new box;
	echo $box->infoBox($heading, $contents, $panelClass);
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

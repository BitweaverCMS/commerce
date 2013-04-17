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

  $set = (isset($_GET['set']) ? $_GET['set'] : '');

	// Fulfillment and Payment need full admin permissions
	if ($set == 'payment' || $set == 'fulfillment' || $set == 'shipping' ) {
		$gBitUser->verifyPermission( 'p_admin' );
	}

  if (zen_not_null($set)) {
    switch ($set) {
      case 'shipping':
        $shipping_errors = '';
        if (zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == 'NONE' or zen_get_configuration_key_value('SHIPPING_ORIGIN_ZIP') == '') {
          $shipping_errors .= '<br />' . ERROR_SHIPPING_ORIGIN_ZIP;
        }
        if (zen_get_configuration_key_value('ORDER_WEIGHT_ZERO_STATUS') == '1' and !defined('MODULE_SHIPPING_FREESHIPPER_STATUS')) {
          $shipping_errors .= '<br />' . ERROR_ORDER_WEIGHT_ZERO_STATUS;
        }
        if( defined('MODULE_SHIPPING_USPS_STATUS') and (MODULE_SHIPPING_USPS_USERID=='NONE' or MODULE_SHIPPING_USPS_SERVER == 'test')) {
          $shipping_errors .= '<br />' . ERROR_USPS_STATUS;
        }
        if ($shipping_errors != '') {
          $messageStack->add(ERROR_SHIPPING_CONFIGURATION . $shipping_errors, 'caution');
        }
      default:
		$module_type = $set;
		$module_directory = DIR_FS_CATALOG_MODULES . "$set/";
		$module_key = 'MODULE_'.strtoupper($set).'_INSTALLED';
		define('HEADING_TITLE', tra( ucfirst( str_replace( '_', ' ', $set ) ) ) );
        break;
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    switch ($action) {
      case 'save':
        while (list($key, $value) = each($_POST['configuration'])) {
// BOF: UPS USPS
          if( is_array( $value ) ){
            $value = implode( ", ", $value);
            $value = str_replace (", --none--", "", $value);
          }
// EOF: UPS USPS
          $gBitDb->query("update " . TABLE_CONFIGURATION . " set `configuration_value` = ? where `configuration_key` = ?", array( $value, $key ) );
        }
        $configuration_query = 'select `configuration_key` as `cfgkey`, `configuration_value` as `cfgvalue`
                          from ' . TABLE_CONFIGURATION;

        $configuration = $gBitDb->Execute($configuration_query);

        zen_redirect(zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'NONSSL'));
        break;
      case 'install':
      case 'remove':
        $file_extension = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '.'));
        $class = basename($_GET['module']);
        if( file_exists( $module_directory . $class . $file_extension ) ) {
	        $moduleFile = $module_directory . $class . $file_extension;
	    } elseif( file_exists($module_directory . $class . '/' . $class . $file_extension) ) {
	        $moduleFile = $module_directory . $class . '/' . $class . $file_extension;
	    }

        if ( !empty( $moduleFile ) ) {
    $configuration_query = 'select `configuration_key` as `cfgkey`, `configuration_value` as `cfgvalue`
                                from ' . TABLE_CONFIGURATION;

          $configuration = $gBitDb->Execute($configuration_query);
          include( $moduleFile );
          $module = new $class;
          if ($action == 'install') {
            $module->install();
          } elseif ($action == 'remove') {
            $module->remove();
          }
        }
        zen_redirect(zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'NONSSL'));
        break;
    }
  }
?>
<script type="text/javascript" src="includes/menu.js"></script>
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
<!-- header //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<div class="page-header">
<h1><?php echo HEADING_TITLE; ?></h1>
</div>

<div class="row">
	<div class="span8">
		<table class="table table-hover">
              <tr class="dataTableHeadingRow">
                <th colspan="2"><?php echo TABLE_HEADING_MODULES; ?></th>
                <th align="right"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
<?php
  if ($set == 'payment') {
?>
                <th align="center"><?php echo TABLE_HEADING_ORDERS_STATUS; ?></th>
<?php } else { ?>
                <th align="center"></th>
<?php }?>

                <th align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
<?php
  $file_extension = substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '.'));
  $directory_array = array();
  if ($dir = @dir($module_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($module_directory . $file) && (substr($file, strrpos($file, '.')) == $file_extension) ) {
	   	$class = substr( $file, 0, strrpos($file, '.') );
		$directory_array[$class] = $file;
      } elseif( file_exists( $module_directory.$file.'/'.$file.'.php' )  ) {
          $directory_array[$file] = $file.'/'.$file.'.php';
      }
    }
    asort($directory_array);
    $dir->close();
  }

  $installed_modules = array();
  foreach ( $directory_array as $class=>$file ) {
	$langFile = DIR_FS_CATALOG_LANGUAGES . $gBitCustomer->getLanguage() . '/modules/' . $module_type . '/' . $file;
	if( file_exists( $langFile ) ) {
	    include( $langFile );
	}
    include($module_directory . $file);

    if (zen_class_exists($class)) {
      $module = new $class;
      if ($module->check() > 0) {
        if( !empty( $module->sort_order ) && empty( $installed_modules[$module->sort_order] ) ) {
          $installed_modules[$module->sort_order] = $file;
        } else {
          $installed_modules[] = $file;
        }
      }

      if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $class))) && !isset($mInfo)) {
        $module_info = array('code' => $module->code,
                             'title' => $module->title,
                             'description' => $module->description,
                             'status' => $module->check());
        $module_keys = $module->keys();
        $keys_extra = array();
        for ($j=0, $k=sizeof($module_keys); $j<$k; $j++) {
          $key_value = $gBitDb->Execute("select `configuration_title`, `configuration_value`, `configuration_key`,
                                        `configuration_description`, `use_function`, `set_function`
                     from " . TABLE_CONFIGURATION . "
                   where `configuration_key` = '" . $module_keys[$j] . "'");

          $keys_extra[$module_keys[$j]]['title'] = $key_value->fields['configuration_title'];
          $keys_extra[$module_keys[$j]]['value'] = $key_value->fields['configuration_value'];
          $keys_extra[$module_keys[$j]]['description'] = $key_value->fields['configuration_description'];
          $keys_extra[$module_keys[$j]]['use_function'] = $key_value->fields['use_function'];
          $keys_extra[$module_keys[$j]]['set_function'] = $key_value->fields['set_function'];
        }
        $module_info['keys'] = $keys_extra;
        $mInfo = new objectInfo($module_info);
      }
      echo '<tr '.(isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ? 'class="info"' : ''). ' onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'NONSSL') . '\'">' . "\n";
?>
                <td class="dataTableContent"><?php echo $module->title; ?></td>
                <td class="dataTableContent"><?php echo $module->code; ?></td>
                <td class="dataTableContent" align="right"><?php if( !empty( $module->sort_order ) && is_numeric($module->sort_order)) echo $module->sort_order; ?></td>
<?php
  if ($set == 'payment' and !empty($module->order_status)) {
		$orders_status_name = $gBitDb->query("select `orders_status_id`, `orders_status_name` from " . TABLE_ORDERS_STATUS . " where `orders_status_id` = ? and `language_id` = ? ", array( $module->order_status, $_SESSION['languages_id'] ) );
?>
                <td class="dataTableContent" align="left">&nbsp;&nbsp;&nbsp;<?php echo (is_numeric($module->sort_order) ? (($orders_status_name->fields['orders_status_id'] < 1) ? TEXT_DEFAULT : $orders_status_name->fields['orders_status_name']) : ''); ?>&nbsp;&nbsp;&nbsp;</td>
<?php
	} else {
?>
				<td class="dataTableContent" align="left">&nbsp;</td>
<?php
	}
?>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) { echo '<i class="icon-circle-arrow-right"></i>'; } else { echo '<a href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . '&module=' . $class, 'NONSSL') . '"><i class="icon-info-sign"></i></a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
  }
  ksort($installed_modules);
  $check = $gBitDb->query("select `configuration_value` FROM " . TABLE_CONFIGURATION . " WHERE `configuration_key` = ?", array( $module_key ) );
  if ($check->RecordCount() > 0) {
    if ($check->fields['configuration_value'] != implode(';', $installed_modules)) {
      $gBitDb->Execute("update " . TABLE_CONFIGURATION . "
                  set `configuration_value` = '" . implode(';', $installed_modules) . "', `last_modified` = ".$gBitDb->qtNOW()."
          where `configuration_key` = '" . $module_key . "'");
    }
  } else {
    $gBitDb->Execute("insert into " . TABLE_CONFIGURATION . "
                (`configuration_title`, `configuration_key`, `configuration_value`,
                 `configuration_description`, `configuration_group_id`, `sort_order`, `date_added`)
                values ('Installed Modules', '" . $module_key . "', '" . implode(';', $installed_modules) . "',
                        'This is automatically updated. No need to edit.', '6', '0', ".$gBitDb->NOW().")");
  }
?>
              <tr>
              </tr>
            </table>
            <p><?php echo TEXT_MODULE_DIRECTORY . ' ' . $module_directory; ?></p>
	</div>
	<div class="span4">
		<div class="well">
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'edit':
      $keys = '';
      reset($mInfo->keys);
      while (list($key, $value) = each($mInfo->keys)) {
        $keys .= '<b>' . $value['title'] . '</b><br>' . $value['description'] . '<br>';
        if ($value['set_function']) {
          eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
        } else {
          $keys .= zen_draw_input_field('configuration[' . $key . ']', $value['value']);
        }
        $keys .= '<br><br>';
      }
      $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
      $contents = array('form' => zen_draw_form_admin('modules', FILENAME_MODULES, 'set=' . $set . (!empty($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=save', 'post', '', true));
      if (ADMIN_CONFIGURATION_KEY_ON == 1) {
        $contents[] = array('text' => '<strong>Key: ' . $mInfo->code . '</strong><br />');
      }
      $contents[] = array('text' => $keys);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . (!empty( $_GET['module'] ) ? '&module=' . $_GET['module'] : ''), 'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
      if( !empty( $mInfo->status ) && $mInfo->status == '1') {
        $keys = '';
        reset($mInfo->keys);
        while (list(, $value) = each($mInfo->keys)) {
          $keys .= '<b>' . $value['title'] . '</b><br>';
          if ($value['use_function']) {
            $use_function = $value['use_function'];
			if( strpos( $use_function, '->' ) !== FALSE ) {
              $class_method = explode('->', $use_function);
              if (!is_object(${$class_method[0]})) {
                include(DIR_WS_CLASSES . $class_method[0] . '.php');
                ${$class_method[0]} = new $class_method[0]();
              }
              $keys .= zen_call_function($class_method[1], $value['value'], ${$class_method[0]});
            } else {
              $keys .= zen_call_function($use_function, $value['value']);
            }
          } else {
            $keys .= $value['value'];
          }
          $keys .= '<br><br>';
        }

        if (ADMIN_CONFIGURATION_KEY_ON == 1) {
          $contents[] = array('text' => '<strong>Key: ' . $mInfo->code . '</strong><br />');
        }
        $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
        $contents[] = array('text' => '<a class="btn" href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . (isset($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=edit', 'NONSSL') . '">' . tra( 'Edit' ) . '</a> <a class="btn" href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove', 'NONSSL') . '">' . tra( 'Remove' ) . '</a>');
        $contents[] = array('text' => '<br>' . $mInfo->description);
        $contents[] = array('text' => '<br>' . $keys);
      } else {
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link_admin(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=install', 'NONSSL') . '">' . zen_image_button('button_module_install.gif', IMAGE_MODULE_INSTALL) . '</a>');
        $contents[] = array('text' => '<br>' . $mInfo->description);
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

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>

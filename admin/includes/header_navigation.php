<nav class="navbar navbar-default" role="navigation">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#commerce-admin-menu"><i class="fa fal fa-bars"></i></button>
		<a class="navbar-brand" href="<?php echo DIR_WS_HTTPS_ADMIN;?>"><span class="hidden-sm hidden-md"><?php echo ucwords( BITCOMMERCE_PKG_DIR )?></span><span class="hidden-lg hidden-xl"><i class="fa fal fa-home"></i></span></a>
	</div>
	<div class="collapse navbar-collapse" id="commerce-admin-menu">
	<ul class="nav navbar-nav">
		<li class="<?php if( $_SERVER['SCRIPT_URL'] == DIR_WS_HTTPS_ADMIN ) { echo 'active'; } ?>"></li>

<?php
// ### CONFIGRUATION MENU
global $gBitDb;
$heading = array();
$contents = array();
if( $rs = $gBitDb->query( "SELECT `configuration_group_id` as `cg_id`, `configuration_group_title` as `cg_title` from " . TABLE_CONFIGURATION_GROUP . " where `visible` = '1' order by `sort_order`" ) ) {
?>
		<li class="<?php if( $_SERVER['SCRIPT_URL'] == DIR_WS_HTTPS_ADMIN.'configuration.php') { echo 'active '; } ?>dropdown">
			<a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php print tra('Config');?> <b class="caret"></b></a>
			<ul class="dropdown-menu">
<?php
	while( $configuration_groups = $rs->fetchRow() ) {
		print '<li><a href="' . zen_href_link_admin(FILENAME_CONFIGURATION, 'gID=' . $configuration_groups['cg_id'], 'NONSSL') . '">' . $configuration_groups['cg_title'] . '</a></li>';
	}
?>
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>template_select.php">Template Selection</a></li>
				<li><a href="/kernel/admin/index.php?page=layout&amp;module_package=bitcommerce">Layout Boxes Controller</a></li>
				<li><a href="layout_controller.php?action=reset_defaults">RESET LAYOUT</a></li>
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>banner_manager.php">Banner Manager</a></li>
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>admin.php">Admin Settings</a></li>
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>store_manager.php">Store Manager</a></li>
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>define_pages_editor.php">Define Pages Editor</a></li>
			</ul>
		</li>
<?php
}

// ### FIXED Menus
?>
		<li class="<?php if( $_SERVER['SCRIPT_URL'] == DIR_WS_HTTPS_ADMIN.'modules.php') { echo 'active '; } ?>dropdown">
			<a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php print tra('Modules');?> <b class="caret"></b></a>
			<ul class="dropdown-menu">
<?php

// #### MODULES Menu

$dir = opendir( DIR_FS_MODULES );
global $gBitUser;
while( $file = readdir( $dir ) ) {
	if( is_dir( DIR_FS_MODULES.$file ) && $file[0] != '.' && (($file != 'payment' && $file != 'fulfillment' && $file != 'shipping')  || $gBitUser->hasPermission( 'p_bitcommerce_root' )) ) {
		echo '<li class="dropdown-submenu"><a tabindex="-1" href="'.zen_href_link_admin(FILENAME_MODULES, 'set='.$file, 'NONSSL').'">'.tra( ucwords( str_replace( '_', ' ', $file ) ) ).'</a><ul class="dropdown-menu">';
		$subdir = opendir( DIR_FS_MODULES.$file );
		while( $subfile = readdir( $subdir ) ) {
			$moduleName = basename( $subfile, '.php' );
			if( $subfile[0] != '.' ) {
				echo '<li><a href="'.DIR_WS_HTTPS_ADMIN.'modules.php?set='.$file.'&amp;module='.$moduleName.'">'.htmlspecialchars( tra( ucwords( str_replace( '_', ' ', $moduleName ) ) ) ).'</a></li>';
			}
		}
		echo '</ul></li>';
	}
}
?>
			</ul>
		</li>
<?php
		global $gBitSmarty;
		print $gBitSmarty->fetch('bitpackage:bitcommerce/admin_header_menu_inc.tpl');
?>
	</ul>

	<div class="navbar-right hidden-sm">
		<form action="<?=BITCOMMERCE_PKG_URL?>admin/index.php" class="navbar-form form-search" role="search">
			<div class="input-group input-group-sm">
				<input type="text" name="lookup_order_id" class="form-control" placeholder="Order #" name="srch-term" id="srch-term">
				<div class="input-group-btn input-group-sm">
					<button class="btn btn-default" type="submit"><i class="fa fal fa-search"></i></button>
				</div>
			</div>
		</form>
	</div>
	</div>
</nav><!-- /navbar-inner -->

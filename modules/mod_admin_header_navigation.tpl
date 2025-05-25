<nav class="navbar navbar-default" role="navigation">
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#commerce-admin-menu"><i class="fa fal fa-bars"></i></button>
		<a class="navbar-brand" href="{$smarty.const.DIR_WS_HTTPS_ADMIN}"><span class="hidden-sm hidden-md">{$smarty.const.BITCOMMERCE_PKG_DIR|ucwords}</span><span class="hidden-lg hidden-xl"><i class="fa fal fa-home"></i></span></a>
	</div>
	<div class="collapse navbar-collapse" id="commerce-admin-menu">
	<ul class="nav navbar-nav">
		{**** CONFIG MENU from mod_admin_header_navigation.php ****}
		<li class="{if $smarty.server.SCRIPT_URL == "`$smarty.const.DIR_WS_HTTPS_ADMIN`configuration.php"}active {/if}dropdown">
			<a data-toggle="dropdown" class="dropdown-toggle" href="#">{tr}Config{/tr} <b class="caret"></b></a>
			<ul class="dropdown-menu">
				{foreach from=$configMenu item=$menuTitle key=$configId}
				<li><a href="{$smarty.const.FILENAME_CONFIGURATION|zen_href_link_admin:"gID=`$configId`"}">{$menuTitle}</a></li>
				{/foreach}
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>template_select.php">Template Selection</a></li>
				<li><a href="/kernel/admin/index.php?page=layout&amp;module_package=bitcommerce">Layout Boxes Controller</a></li>
				<li><a href="layout_controller.php?action=reset_defaults">RESET LAYOUT</a></li>
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>banner_manager.php">Banner Manager</a></li>
				<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>define_pages_editor.php">Define Pages Editor</a></li>
			</ul>
		</li>

		{**** MODULE MENU from mod_admin_header_navigation.php ****}
		<li class="{if $smarty.server.SCRIPT_URL == "`$smarty.const.DIR_WS_HTTPS_ADMIN`modules.php"}active {/if}dropdown">
			<a data-toggle="dropdown" class="dropdown-toggle" href="#">{tr}Modules{/tr} <b class="caret"></b></a>
			<ul class="dropdown-menu">
				{foreach from=$moduleMenu item=$moduleSubMenu key=$moduleClass}
				<li class="dropdown-submenu"><a tabindex="-1" href="{$smarty.const.FILENAME_MODULES|zen_href_link_admin:"set=`$file`"}">{tr}{"_"|str_replace:' ':$moduleClass|ucwords}{/tr}</a><ul class="dropdown-menu">
					{foreach from=$moduleSubMenu item=$subMenu key=$moduleName}
						<li><a href="{$smarty.const.DIR_WS_HTTPS_ADMIN}modules.php?set={$moduleClass}&amp;module={$subMenu.module_name}">{$subMenu.menu_title}</a></li>
					{/foreach}
				</ul></li>
				{/foreach}
			</ul>
		</li>


		<li class="{if $smarty.server.SCRIPT_URL == $smarty.const.DIR_WS_HTTPS_ADMIN}active{/if}"></li>
		{include file="bitpackage:bitcommerce/admin_header_menu_inc.tpl"}
	</ul>

	<div class="navbar-right hidden-sm">
		<form action="{$smarty.const.BITCOMMERCE_PKG_ADMIN_URI}admin/index.php" class="navbar-form form-search" role="search">
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

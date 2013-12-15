<div class="navbar">
<div class="navbar-inner">
<div class="container">
	<div class="nav-collapse collapse navbar-responsive-collapse">
		<ul class="nav width100p">
			<li class="<?php if( $_SERVER['SCRIPT_URL'] == DIR_WS_HTTPS_ADMIN ) { echo 'active'; } ?>"><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>">Summary</a></li>

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
		if( is_dir( DIR_FS_MODULES.$file ) && $file[0] != '.' && (($file != 'payment' && $file != 'fulfillment' && $file != 'shipping')  || $gBitUser->hasPermission( 'p_admin' )) ) {
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
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php print tra('Catalog');?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>categories.php">Categories/Products</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>product_types.php">Product Types</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>products_options.php">Product Options</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>products_price_manager.php">Products Price Manager</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>downloads_manager.php">Downloads Manager</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>manufacturers.php">Manufacturers</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>suppliers.php">Suppliers</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>reviews.php">Reviews</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>specials.php">Specials</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>featured.php">Featured Products</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>salemaker.php">SaleMaker</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>products_expected.php">Products Expected</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>record_artists.php">Record Artists</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>record_company.php">Record Companies</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>music_genre.php">Music Genre</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>media_manager.php">Media Manager</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>media_types.php">Media Types</a></li>
				</ul>
			</li>
            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php print tra('Customer');?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>customers.php">Customers</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>orders.php">Orders</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>includes/modules/amazonmws/index.php">Amazon Orders</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>interests.php">Interests</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>commissions.php">Commissions</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>orders_status.php">Orders Status</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>group_pricing.php">Group Pricing</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>paypal.php">PayPal IPN</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>coupon_admin.php">Coupon Admin</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>gv_queue.php">Gift Certificates Queue</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>gv_mail.php">Mail Gift Certificate</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>gv_sent.php">Gift Certificates sent</a></li>
				</ul>
			</li>

            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php print tra('Locations');?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>countries.php">Countries</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>currencies.php">Currencies</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>languages.php">Languages</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>zones.php">Zones</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>geo_zones.php">Zones Definitions</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>tax_classes.php">Tax Classes</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>tax_rates.php">Tax Rates</a></li>
				</ul>
			</li>

            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php print tra('Reports');?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>sales_and_income.php">Sales and Income</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>stats_products_viewed.php">Products Viewed</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>stats_products_types.php">Products Sales By Type</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>stats_products_purchased.php">Products Purchased</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>stats_customers.php">Customer Orders</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>stats_products_lowstock.php">Products Low Stock</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>stats_customers_referrals.php">Customers Referral</a></li>
				</ul>
			</li>

            <li class="dropdown">
                <a data-toggle="dropdown" class="dropdown-toggle" href="#"><?php print tra('Tools');?> <b class="caret"></b></a>
                <ul class="dropdown-menu">
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>template_select.php">Template Selection</a></li>
					<li><a href="/kernel/admin/index.php?page=layout&amp;module_package=bitcommerce">Layout Boxes Controller</a></li>
					<li><a href="layout_controller.php?action=reset_defaults">RESET LAYOUT</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>banner_manager.php">Banner Manager</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>mail.php">Send Email</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>newsletters.php">Newsletter Manager</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>server_info.php">Server Info</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>whos_online.php">Who's Online</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>admin.php">Admin Settings</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>email_welcome.php">Email Welcome</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>store_manager.php">Store Manager</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>developers_tool_kit.php">Developers Tool Kit</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>define_pages_editor.php">Define Pages Editor</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>easypopulate.php">Easy Populate</a></li>
					<li><a href="<?php echo DIR_WS_HTTPS_ADMIN;?>sqlpatch.php">Install SQL Patches</a></li>
				</ul>
			</li>



		<form class="navbar-search pull-right" method="get" action="<?=BITCOMMERCE_PKG_URL?>admin/index.php">
			<input type="text" name="lookup_order_id" class="search-query span1" placeholder="Order #"/>
		</form>
	</div><!-- /.nav-collapse -->
</div>
</div><!-- /navbar-inner -->
</div>

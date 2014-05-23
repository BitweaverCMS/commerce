{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

{formfeedback hash=$feedback}
{strip}
<div class="admin bitcommerce coupons">
	<header>
		<div class="pull-right">
			<a class="btn btn-small btn-info" href="export_users.php">Export as CSV</a> &nbsp;
			<form class="form-search form-inline">
			  <div class="col-md-2 input-append">
				<input type="text" class="search-query">
				<button type="submit" class="btn btn-default">Search</button>
				<button type="submit" class="btn">Search</button>
			  </div>
			</form>
		</div>
		<h1>{tr}Customers{/tr}</h1>
	</header>

	<div class="body">
		<table class="table table-hover">
			<tr>
				<th>{smartlink ititle="ID" isort="user_id" icontrol=$listInfo }</th>
				<th>{smartlink ititle="Customer" isort="email" icontrol=$listInfo }</th>
				<th class="text-right">{smartlink ititle="# of Orders" isort="orders" icontrol=$listInfo }</th>
				<th class="text-right">{smartlink ititle="Revenue" isort="revenue" icontrol=$listInfo }</th>
				<th colspan="2" class="text-center">{smartlink ititle="Last Purchase" isort="last_purchase" icontrol=$listInfo }</th>
				<th colspan="2" class="text-center">{smartlink ititle="First Purchase" isort="first_purchase" icontrol=$listInfo }</th>
				<th>{smartlink ititle="Age" isort="age" icontrol=$listInfo }</th>
				<th></th>
			</tr>
			<tr class="info">
				<td></td>
				<td>{$customers.totals.customers} {tr}Customers{/tr}</td>
				<td class="text-right">{$customers.totals.orders}</td>
				<td class="text-right">{$gCommerceCurrencies->format($customers.totals.revenue)}</td>
				<td colspan="6"></td>
			</tr>
			{foreach from=$customers.customers key=customerId item=customerHash}
			<tr>
				<td>{$customerHash.user_id}</td>
				<td>{BitUser::getDisplayLink(1,$customerHash)}</td>
				<td class="text-right">{$customerHash.orders}</td>
				<td class="text-right">{$gCommerceCurrencies->format($customerHash.revenue)}</td>
				<td class="text-right">{$customerHash.last_purchase|zen_date_short}</td>
				<td class="text-right"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$customerHash.last_orders_id}">#{$customerHash.last_orders_id}</td>
				<td class="text-right">{$customerHash.first_purchase|zen_date_short}</td>
				<td class="text-right"><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/orders.php?oID={$customerHash.first_orders_id}">#{$customerHash.first_orders_id}</td>
				<td class="text-right">{$customerHash.age|regex_replace:"/ [0-9].*/":""}</td>
				<td><a href="{$smarty.const.BITCOMMERCE_PKG_URL}admin/customers.php?user_id={$customerHash.user_id}"><i class="icon-edit"></i></a></td>
			</tr>
			{/foreach}
		</table>

{*
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
					<tr><?php echo zen_draw_form_admin('search', FILENAME_CUSTOMERS, '', 'get', '', true); ?>
						<td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
						<td class="pageHeading" align="right"></td>
						<td class="smallText" align="right">
<?php
// show reset search
		if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
			echo '<a href="' . zen_href_link_admin(FILENAME_CUSTOMERS, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
		}
		echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search');
		if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
			$keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
			echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
		}
?>
						</td>
					</form></tr>
				</table></td>
			</tr>
			<tr>
				<td><table border="0" width="100%" cellspacing="0" cellpadding="0">
					<tr>
<?php
// Sort Listing
					switch ($_GET['list_order']) {
						case "id-asc":
							$disp_order = "ci.`date_account_created`";
							break;
						case "firstname":
							$disp_order = "c.`customers_firstname`";
							break;
						case "firstname-desc":
							$disp_order = "c.`customers_firstname` DESC";
							break;
						case "group-asc":
							$disp_order = "c.`customers_group_pricing`";
							break;
						case "group-desc":
							$disp_order = "c.`customers_group_pricing` DESC";
							break;
						case "lastname":
							$disp_order = "c.`customers_lastname`, c.`customers_firstname`";
							break;
						case "lastname-desc":
							$disp_order = "c.`customers_lastname` DESC, c.`customers_firstname`";
							break;
						case "company":
							$disp_order = "a.`entry_company`";
							break;
						case "company-desc":
							$disp_order = "a.`entry_company` DESC";
							break;
						case "login-asc":
							$disp_order = "ci.`date_of_last_logon`";
							break;
						case "login-desc":
							$disp_order = "ci.`date_of_last_logon` DESC";
							break;
						case "approval-asc":
							$disp_order = "c.`customers_authorization`";
							break;
						case "approval-desc":
							$disp_order = "c.`customers_authorization` DESC";
							break;
						default:
							$disp_order = "c.`customers_id` DESC";
					}
?>


					<table class="table">
							<tr class="dataTableHeadingRow">
								<td class="dataTableHeadingContent" align="center" valign="top">
									<?php echo TABLE_HEADING_ID; ?>
								</td>
								<td class="dataTableHeadingContent" align="left">
									<?php echo (($_GET['list_order']=='lastname' or $_GET['list_order']=='lastname-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_LASTNAME . '</span>' : TABLE_HEADING_LASTNAME); ?>
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=lastname', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='lastname' ? '<i class="icon-sort-by-order"></i>' : '<i class="icon-sort-by-order bold"></i>'); ?></a>&nbsp;
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=lastname-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='lastname-desc' ? '<i class="icon-sort-by-order-alt"></i>' : '<i class="icon-sort-by-order-alt bold"></i>'); ?></a>
								</td>
								<td class="dataTableHeadingContent" align="left">
									<?php echo (($_GET['list_order']=='firstname' or $_GET['list_order']=='firstname-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_FIRSTNAME . '</span>' : TABLE_HEADING_FIRSTNAME); ?>
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=firstname', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='firstname' ? '<i class="icon-sort-by-order"></i>' : '<i class="icon-sort-by-order bold"></i>'); ?></a>&nbsp;
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=firstname-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='firstname-desc' ? '<i class="icon-sort-by-order-alt"></i>' : '<i class="icon-sort-by-order-alt bold"></i>'); ?></a>
								</td>
								<td class="dataTableHeadingContent" align="left">
									<?php echo (($_GET['list_order']=='company' or $_GET['list_order']=='company-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_COMPANY . '</span>' : TABLE_HEADING_COMPANY); ?>
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=company', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='company' ? '<i class="icon-sort-by-order"></i>' : '<i class="icon-sort-by-order bold"></i>'); ?></a>&nbsp;
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=company-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='company-desc' ? '<i class="icon-sort-by-order-alt"></i>' : '<i class="icon-sort-by-order-alt bold"></i>'); ?></a>
								</td>
								<td class="dataTableHeadingContent" align="left">
									<?php echo (($_GET['list_order']=='id-asc' or $_GET['list_order']=='id-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_ACCOUNT_CREATED . '</span>' : TABLE_HEADING_ACCOUNT_CREATED); ?>
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=id-asc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='id-asc' ? '<i class="icon-sort-by-order"></i>' : '<i class="icon-sort-by-order bold"></i>'); ?></a>&nbsp;
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=id-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='id-desc' ? '<i class="icon-sort-by-order-alt"></i>' : '<i class="icon-sort-by-order-alt bold"></i>'); ?></a>
								</td>

								<td class="dataTableHeadingContent" align="left">
									<?php echo (($_GET['list_order']=='login-asc' or $_GET['list_order']=='login-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_LOGIN . '</span>' : TABLE_HEADING_LOGIN); ?>
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=login-asc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='login-asc' ? '<i class="icon-sort-by-order"></i>' : '<i class="icon-sort-by-order bold"></i>'); ?></a>&nbsp;
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=login-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='login-desc' ? '<i class="icon-sort-by-order-alt"></i>' : '<i class="icon-sort-by-order-alt bold"></i>'); ?></a>
								</td>

								<td class="dataTableHeadingContent" align="left">
									<?php echo (($_GET['list_order']=='group-asc' or $_GET['list_order']=='group-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_PRICING_GROUP . '</span>' : TABLE_HEADING_PRICING_GROUP); ?>
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=group-asc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='group-asc' ? '<i class="icon-sort-by-order"></i>' : '<i class="icon-sort-by-order bold"></i>'); ?></a>&nbsp;
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=group-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='group-desc' ? '<i class="icon-sort-by-order-alt"></i>' : '<i class="icon-sort-by-order-alt bold"></i>'); ?></a>
								</td>

								<td class="dataTableHeadingContent" align="center">
									<?php echo (($_GET['list_order']=='approval-asc' or $_GET['list_order']=='approval-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_AUTHORIZATION_APPROVAL . '</span>' : TABLE_HEADING_AUTHORIZATION_APPROVAL); ?>
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=approval-asc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='approval-asc' ? '<i class="icon-sort-by-order"></i>' : '<i class="icon-sort-by-order bold"></i>'); ?></a>&nbsp;
									<a href="<?php echo zen_href_link_admin(basename($_SERVER['SCRIPT_NAME']) . '?list_order=approval-desc', '', 'NONSSL'); ?>"><?php echo ($_GET['list_order']=='approval-desc' ? '<i class="icon-sort-by-order-alt"></i>' : '<i class="icon-sort-by-order-alt bold"></i>'); ?></a>
								</td>

								<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
							</tr>
<?php
		$search = '';
		if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
			$keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
//			$search = "where c.customers_lastname like '%" . $keywords . "%' or c.`customers_firstname` like '%" . $keywords . "%' or c.`customers_email_address` like '%" . $keywords . "%'";
			$search = "where c.`customers_lastname` like '%" . $keywords . "%' or c.`customers_firstname` like '%" . $keywords . "%' or c.`customers_email_address` like '%" . $keywords . "%' or c.`customers_telephone` like '%" . $keywords . "%' or a.`entry_company` like '%" . $keywords . "%' or a.`entry_street_address` like '%" . $keywords . "%' or a.`entry_city` like '%" . $keywords . "%' or a.`entry_postcode` like '%" . $keywords . "%'";
		}
		$new_fields=', c.`customers_telephone`, a.`entry_company`, a.`entry_street_address`, a.`entry_city`, a.`entry_postcode`, c.`customers_authorization`, c.`customers_referral`';
		$customers_query_raw = "select c.`customers_id`, c.`customers_lastname`, c.`customers_firstname`, c.`customers_email_address`, c.`customers_group_pricing`, a.`entry_country_id`, a.`entry_company`, ci.`date_of_last_logon`, ci.`date_account_created` " . $new_fields . " from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.`customers_id`= ci.`customers_info_id` left join " . TABLE_ADDRESS_BOOK . " a on c.`customers_id` = a.`customers_id` and c.`customers_default_address_id` = a.`address_book_id` " . $search . " order by $disp_order LIMIT 100";

// Split Page
// reset page when page is unknown
if ( empty( $_GET['page'] ) && !empty( $_GET['cID'] ) ) {
	$check_page = $gBitDb->Execute($customers_query_raw);
	$check_count=1;
	if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) {
		while (!$check_page->EOF) {
			if ($check_page->fields['customers_id'] == $_GET['cID']) {
				break;
			}
			$check_count++;
			$check_page->MoveNext();
		}
		$_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER)+(fmod($check_count,MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER) !=0 ? .5 : 0)),0);
//		zen_redirect(zen_href_link_admin(FILENAME_CUSTOMERS, 'cID=' . $_GET['cID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'NONSSL'));
	} else {
		$_GET['page'] = 1;
	}
}

		$customers_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, $customers_query_raw, $customers_query_numrows);
		$customers = $gBitDb->Execute($customers_query_raw);
		while (!$customers->EOF) {
			$info = $gBitDb->Execute("select `date_account_created`, `date_account_last_modified`, `date_of_last_logon` as `date_last_logon`, `number_of_logons`
														from " . TABLE_CUSTOMERS_INFO . "
														where `customers_info_id` = '" . $customers->fields['customers_id'] . "'");

			if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $customers->fields['customers_id']))) && !isset($cInfo)) {
				$country = $gBitDb->Execute("select `countries_name` from " . TABLE_COUNTRIES . " where `countries_id` = '" . (int)$customers->fields['entry_country_id'] . "'");
				$reviews = $gBitDb->Execute("select count(*) as `number_of_reviews` from " . TABLE_REVIEWS . " where `customers_id` = '" . (int)$customers->fields['customers_id'] . "'");

				$cInfo_array = $customers->fields;
				if ( !empty( $country->fields ) )
					$cInfo_array = array_merge($cInfo_array, $country->fields);
				if ( !empty( $info->fields ) )
					$cInfo_array = array_merge($cInfo_array, $info->fields);
				if ( !empty( $reviews->fields ) )
					$cInfo_array = array_merge($cInfo_array, $reviews->fields);

				$cInfo = new objectInfo($cInfo_array);
			}

		if ( !empty($customers->fields['customers_group_pricing']) ) {
			$group_query = $gBitDb->query( "select `group_name`, `group_percentage` from " . TABLE_GROUP_PRICING . " where `group_id` = ?", array( $customers->fields['customers_group_pricing'] ) );

			if ($group_query->RecordCount() < 1) {
				$group_name_entry = TEXT_NONE;
			} else {
				$group_name_entry = $group_query->fields['group_name'];
			}
		} else $group_name_entry = TEXT_NONE;

			if (isset($cInfo) && is_object($cInfo) && ($customers->fields['customers_id'] == $cInfo->customers_id)) {
				echo '					<tr id="defaultSelected" class="info"  onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $cInfo->customers_id . '&action=edit', 'NONSSL') . '\'">' . "\n";
			} else {
				echo '					<tr class="dataTableRow"  onclick="document.location.href=\'' . zen_href_link_admin(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID')) . 'cID=' . $customers->fields['customers_id'], 'NONSSL') . '\'">' . "\n";
			}
?>
								<td class="dataTableContent" align="right"><?php echo $customers->fields['customers_id']; ?></td>
								<td class="dataTableContent"><?php echo $customers->fields['customers_lastname']; ?></td>
								<td class="dataTableContent"><?php echo $customers->fields['customers_firstname']; ?></td>
								<td class="dataTableContent"><?php echo $customers->fields['entry_company']; ?></td>
								<td class="dataTableContent"><?php echo zen_date_short($info->fields['date_account_created']); ?></td>
								<td class="dataTableContent"><?php echo zen_date_short($customers->fields['date_of_last_logon']); ?></td>
								<td class="dataTableContent"><?php echo $group_name_entry; ?></td>
								<td class="dataTableContent" align="center"><?php echo ($customers->fields['customers_authorization'] == 0 ? '<a href="' . zen_href_link_admin(FILENAME_CUSTOMERS, 'action=status&current=' . $customers->fields['customers_authorization'] . '&cID=' . $customers->fields['customers_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_green_on.gif', IMAGE_ICON_STATUS_ON) . '</a>' : '<a href="' . zen_href_link_admin(FILENAME_CUSTOMERS, 'action=status&current=' . $customers->fields['customers_authorization'] . '&cID=' . $customers->fields['customers_id'] . ($_GET['page'] > 0 ? '&page=' . $_GET['page'] : ''), 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_red_on.gif', IMAGE_ICON_STATUS_OFF) . '</a>'); ?></td>
								<td class="dataTableContent" align="right"><?php echo '<a href="' . zen_href_link_admin(FILENAME_CUSTOMERS, zen_get_all_get_params(array('cID', 'action')) . 'cID=' . $customers->fields['customers_id'] . '&action=edit', 'NONSSL') . '"><i class="icon-edit"></i></a>'; ?></td>
							</tr>
<?php
			$customers->MoveNext();
		}
?>
							<tr>
								<td colspan="5"><table>
									<tr>
										<td class="smallText" valign="top"><?php echo $customers_split->display_count($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
										<td class="smallText" align="right"><?php echo $customers_split->display_links($customers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_CUSTOMER, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'cID'))); ?></td>
									</tr>
<?php
		if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
?>
									<tr>
										<td align="right" colspan="2"><?php echo '<a href="' . zen_href_link_admin(FILENAME_CUSTOMERS, '', 'NONSSL') . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
									</tr>
<?php
		}
?>
								</table></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
<?php
	}
?>
		</table></td>
<!-- body_text_eof //-->
	</tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_FS_ADMIN_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_FS_ADMIN_INCLUDES . 'application_bottom.php'); ?>
*}

		{include file="bitpackage:bitcommerce/commerce_pagination.tpl"}
	</div>
</div>
{/strip}

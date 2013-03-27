{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<style type="text/css">@import url({$smarty.const.UTIL_PKG_URL}javascript/libs/dynarch/jscalendar/calendar-win2k-1.css);</style>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/dynarch/jscalendar/calendar.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/dynarch/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/libs/dynarch/jscalendar/calendar-setup.js"></script>

<div class="admin bitcommerce">
	<div class="page-header">
		<h1 class="header">{tr}Commissions{/tr}</h1>
	</div>
	<div class="body">

		{formfeedback error=$errors}

		{include file="bitpackage:bitcommerce/admin_commissions_list_inc.tpl"}

	</div><!-- end .body -->
</div><!-- end .bitcommerce -->

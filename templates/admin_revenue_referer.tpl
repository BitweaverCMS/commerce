{include file="bitpackage:bitcommerce/admin_header_inc.tpl"}
{include_php file="`$smarty.const.BITCOMMERCE_PKG_PATH`admin/includes/header_navigation.php"}

<div class="admin bitcommerce">
	<div class="page-header">
		<h1 class="header">{tr}Referer Revenue{/tr} : {$smarty.request.timeframe|escape} :  {$referer|escape}</h1>
	</div>

	<div class="body">
		{form method="get"}
		<input type="hidden" name="referer" value="{$smarty.request.referer}"/>
		<input type="hidden" name="period" value="{$smarty.request.period}"/>
		<input type="hidden" name="timeframe" value="{$smarty.request.timeframe}"/>
		<input type="hidden" name="sort_mode" value="{$smarty.request.sort_mode}"/>
		{legend legend="Search Referer URL"}
		<div class="row">
			{formlabel label="Include"}
			{forminput}
				<input type="text" name="include" value="{$smarty.request.include|escape}"/>
			{/forminput}
		</div>
		<div class="row">
			{formlabel label="Exclude"}
			{forminput}
				<input type="text" name="exclude" value="{$smarty.request.exclude|escape}"/>
				{formhelp note="Separate multiple terms with a comma. Search is not case-sensitive"}
			{/forminput}
		</div>
		<div class="row">
			{formlabel label="New Registrations"}
			{forminput}
				<input type="checkbox" name="new_reg" value="y" {if $smarty.request.new_reg}checked="checked"{/if}/>
				{formhelp note="Only include new registrations that occurred during this timeframe."}
			{/forminput}
		</div>
		<div class="row submit">
			{forminput}
				<input type="submit" name="Search" value="Search"/>
			{/forminput}
		</div>
		{/legend}
		{/form}

		<table class="table data stats">
			<caption>{tr}Revenue By Referer{/tr}</caption>
			<thead>
			<tr>
				<th>{tr}Host{/tr}</th>
				<th>{smartlink ititle="Revenue" isort="revenue" icontrol=$listInfo iorder="desc"}</th>
				<th>{smartlink ititle="# Orders" isort="orders" icontrol=$listInfo iorder="desc"}</th>
				<th>{smartlink ititle="# Units" isort="units" icontrol=$listInfo iorder="desc"}</th>
				<th>{smartlink ititle="# Customers" isort="customers" icontrol=$listInfo iorder="desc"}</th>
			</tr>
			<tr>
				<td><em>{tr}Totals{/tr}</em></th>
				<td class="item currency">${$statsByReferer.totals.revenue|round:2}</td>
				<td class="item">{$statsByReferer.totals.orders}</td>
				<td class="item">{$statsByReferer.totals.units}</td>
				<td class="item">{$statsByReferer.totals.customers}</td>
			</tr>
			</thead>
			<tbody>
			{foreach from=$statsByReferer.hosts item=hostStat key=host}
			<tr>
				<th><em>{$host}</em></th>
				<th class="item currency">${$hostStat.revenue|round:2}</th>
				<th class="item">{$hostStat.orders}</th>
				<th class="item">{$hostStat.units}</th>
				<th class="item">{$hostStat.customers}</th>
			</tr>
			{if $hostStat.refs}
			<tbody>
			{foreach from=$hostStat.refs item=refStats key=ref}
			<tr>
				<td>{$ref|truncate:80|escape}</td>
				<td class="item currency">${$refStats.revenue|round:2}</td>
				<td class="item">{$refStats.orders}</td>
				<td class="item">{$refStats.units}</td>
				<td class="item">{$refStats.customers}</td>
			</tr>
			{/foreach}
			</tbody>
			{/if}
			{/foreach}
			</tbody>
		</table>


	</div><!-- end .body -->
</div><!-- end .bitcommerce -->
